<?php
/**
 * Page — auto-create and maintain the commitment page, shortcode, and block.
 *
 * @package Nera_Voluntary_Code
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages the auto-created commitment page, shortcode registration, and block registration.
 */
class Nera_VC_Page {

	const PAGE_SLUG  = 'our-commitment-to-player-protection';
	const PAGE_TITLE = 'Our commitment to player protection';
	const SHORTCODE  = 'nera_voluntary_code';
	const BLOCK_NAME = 'nera/voluntary-code-commitment';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		// Shortcode (fallback for classic editor pages).
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render_shortcode' ) );

		// Dynamic block registration (requires WP 5.9+ / block API v3).
		add_action( 'init', array( __CLASS__, 'register_block' ) );

		// Admin self-heal: recreate the commitment page if it was trashed.
		add_action( 'admin_init', array( __CLASS__, 'maybe_heal_page' ) );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Shortcode
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Render the commitment page shortcode output.
	 *
	 * @return string
	 */
	public static function render_shortcode() {
		return Nera_VC_Content::render_page();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Dynamic block
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Register the nera/voluntary-code-commitment dynamic block.
	 */
	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			self::BLOCK_NAME,
			array(
				'api_version'     => 3,
				'title'           => __( 'Voluntary Code Commitment', 'nera-voluntary-code-plugin' ),
				'description'     => __( 'Renders the operator\'s commitment-to-player-protection content.', 'nera-voluntary-code-plugin' ),
				'category'        => 'widgets',
				'render_callback' => array( __CLASS__, 'render_block' ),
				'supports'        => array(
					'html' => false,
				),
			)
		);
	}

	/**
	 * Block render callback — same output as the shortcode.
	 *
	 * @return string
	 */
	public static function render_block() {
		return Nera_VC_Content::render_page();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Activation
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Activation hook callback — idempotent page creation and content seeding.
	 *
	 * Call order:
	 *   1. Check if stored page ID still points to a live published page → skip.
	 *   2. Adopt an existing published page with the canonical slug if found.
	 *   3. Otherwise create a new page with the dynamic block in its content.
	 *   4. Seed default repeater data into ACF if ACF is available and not yet seeded.
	 *      If ACF is not yet available at activation, defer seeding to acf/init.
	 */
	public static function activate() {
		// ── Page creation / adoption ───────────────────────────────────────
		$stored_id = (int) get_option( 'nera_vc_page_id', 0 );

		if ( $stored_id > 0 ) {
			$page = get_post( $stored_id );
			if ( $page && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
				// Existing live page — skip creation, proceed to seed check.
				self::maybe_seed();
				return;
			}
		}

		// Try to adopt an existing page with the canonical slug.
		$existing = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'name'           => self::PAGE_SLUG,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $existing ) ) {
			$page_id = (int) $existing[0];
		} else {
			// Create a new page with the dynamic block as its content.
			$page_id = wp_insert_post(
				array(
					'post_title'   => self::PAGE_TITLE,
					'post_name'    => self::PAGE_SLUG,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '<!-- wp:' . self::BLOCK_NAME . ' /-->',
				),
				true // Return WP_Error on failure.
			);

			if ( is_wp_error( $page_id ) ) {
				// Log but do not fatal — plugin still activates.
				error_log( '[nera-voluntary-code-plugin] Failed to create commitment page: ' . $page_id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				self::maybe_seed();
				return;
			}
		}

		update_option( 'nera_vc_page_id', (int) $page_id, false );

		self::maybe_seed();
	}

	/**
	 * Seed default repeater data into ACF if not yet seeded.
	 *
	 * Called from activate() (synchronous path) and from acf/init (deferred path
	 * for environments where ACF loads after the activation hook).
	 */
	public static function maybe_seed() {
		if ( '1' === get_option( 'nera_vc_seeded', '' ) ) {
			return; // Already seeded.
		}

		// Need ACF's read/write API.
		if ( ! function_exists( 'update_field' ) ) {
			// Defer to acf/init so update_field() is available on the next request.
			add_action(
				'acf/init',
				function() {
					Nera_VC_Page::maybe_seed();
				},
				20
			);
			return;
		}

		// Ensure our local ACF field definitions are registered THIS request before
		// writing. On the activation request, acf/init has already fired before this
		// plugin booted, so its field group is otherwise absent and update_field()
		// cannot resolve the repeater name → key mapping (rows would not persist).
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			Nera_VC_Settings::register_options_page();
			Nera_VC_Settings::register_fields();
		}

		// Seed all three repeaters.
		update_field( 'vc_measures_protections',    Nera_VC_Content::default_protections(),    Nera_VC_Settings::OPTIONS_POST_ID );
		update_field( 'vc_measures_transparency',   Nera_VC_Content::default_transparency(),   Nera_VC_Settings::OPTIONS_POST_ID );
		update_field( 'vc_measures_accountability', Nera_VC_Content::default_accountability(), Nera_VC_Settings::OPTIONS_POST_ID );

		update_option( 'nera_vc_seeded', '1', false );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Self-heal
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Recreate the commitment page on admin_init if it was trashed or deleted.
	 * Silent no-op when the page is healthy.
	 */
	public static function maybe_heal_page() {
		$stored_id = Nera_VC_Settings::page_id();

		if ( $stored_id > 0 ) {
			$page = get_post( $stored_id );
			if ( $page && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
				return; // Healthy.
			}
		}

		// Page is missing or trashed — recreate (same logic as activate).
		self::activate();
	}
}
