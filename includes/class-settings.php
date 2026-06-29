<?php
/**
 * Settings — ACF options page and field registration.
 *
 * @package Nera_Voluntary_Code
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF options page and field group for Voluntary Code settings.
 */
class Nera_VC_Settings {

	const OPTIONS_SLUG    = 'nera-features';
	const OPTIONS_POST_ID = 'nera-features';
	const FIELD_GROUP_KEY = 'group_nera_vc';
	const DEFAULT_GOVUK_URL   = 'https://www.gov.uk/government/publications/voluntary-code-of-good-practice-for-prize-draw-operators/voluntary-code-of-good-practice-for-prize-draw-operators';
	const DEFAULT_FOOTER_TEXT = 'We follow the Voluntary Code for Prize Draw Operators';
	const DEFAULT_INTRO_COPY  = '<p><strong>[Business Name]</strong> follows the <strong>Voluntary Code of Good Practice for Prize Draw Operators</strong>, published by the Department for Culture, Media &amp; Sport (DCMS). We are committed to running our prize draws fairly, transparently and with strong protections for our players. The measures below set out exactly what we have in place.</p>';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'acf/init', array( __CLASS__, 'register_options_page' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_fields' ) );
	}

	/**
	 * Idempotently ensure the shared Theme Settings parent and the Nera Features sub-page.
	 */
	public static function register_options_page() {
		if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
			return;
		}

		// Ensure the shared Theme Settings parent exists (the theme normally creates it, but
		// guard so this plugin can function standalone).
		if ( function_exists( 'acf_add_options_page' ) &&
			( ! function_exists( 'acf_get_options_page' ) || ! acf_get_options_page( 'theme-settings' ) ) ) {
			acf_add_options_page(
				array(
					'page_title' => 'Theme Settings',
					'menu_title' => 'Theme Settings',
					'menu_slug'  => 'theme-settings',
					'capability' => 'manage_options',
					'redirect'   => false,
				)
			);
		}

		// Register (or no-op if already registered by the sibling plugin) the Nera Features sub-page.
		acf_add_options_sub_page(
			array(
				'page_title'  => __( 'Nera Features', 'nera-voluntary-code-plugin' ),
				'menu_title'  => __( 'Nera Features', 'nera-voluntary-code-plugin' ),
				'menu_slug'   => self::OPTIONS_SLUG,
				'parent_slug' => 'theme-settings',
				'capability'  => 'manage_options',
				'post_id'     => self::OPTIONS_POST_ID,
			)
		);
	}

	/**
	 * Register the Voluntary Code field group, appending to the Nera Features page.
	 */
	public static function register_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'    => self::FIELD_GROUP_KEY,
				'title'  => __( 'Voluntary Code Commitment', 'nera-voluntary-code-plugin' ),
				'fields' => array(

					// ── Section intro message ──────────────────────────────────────
					array(
						'key'       => 'field_nera_vc_section',
						'label'     => __( 'Voluntary Code Commitment', 'nera-voluntary-code-plugin' ),
						'name'      => '',
						'type'      => 'message',
						'message'   => __( 'This configures the public "Our commitment to player protection" page (Voluntary Code clause 3.4) and the sitewide footer badge. Measures are pre-filled and fully editable.', 'nera-voluntary-code-plugin' ),
						'new_lines' => 'wpautop',
						'esc_html'  => 0,
					),

					// ── Intro copy ─────────────────────────────────────────────────
					array(
						'key'           => 'field_nera_vc_intro_copy',
						'label'         => __( 'Intro Copy', 'nera-voluntary-code-plugin' ),
						'name'          => 'vc_intro_copy',
						'type'          => 'wysiwyg',
						'instructions'  => __( 'Adherence statement shown at the top of the page. The tokens [Business Name], [Business Address] and [Email] are replaced automatically from Theme Settings → Legal Placeholders.', 'nera-voluntary-code-plugin' ),
						'toolbar'       => 'full',
						'media_upload'  => 0,
						'default_value' => self::DEFAULT_INTRO_COPY,
						'wrapper'       => array( 'width' => '100' ),
					),

					// ── GOV.UK URL ─────────────────────────────────────────────────
					array(
						'key'           => 'field_nera_vc_govuk_url',
						'label'         => __( 'GOV.UK Voluntary Code URL', 'nera-voluntary-code-plugin' ),
						'name'          => 'vc_govuk_url',
						'type'          => 'url',
						'instructions'  => __( 'Link to the official Code on GOV.UK.', 'nera-voluntary-code-plugin' ),
						'default_value' => self::DEFAULT_GOVUK_URL,
						'wrapper'       => array( 'width' => '100' ),
					),

					// ── Player protections repeater ────────────────────────────────
					array(
						'key'          => 'field_nera_vc_prot',
						'label'        => __( 'Player protections', 'nera-voluntary-code-plugin' ),
						'name'         => 'vc_measures_protections',
						'type'         => 'repeater',
						'instructions' => __( 'Plain-English measures shown under the Player protections heading.', 'nera-voluntary-code-plugin' ),
						'button_label' => __( 'Add measure', 'nera-voluntary-code-plugin' ),
						'layout'       => 'block',
						'min'          => 0,
						'max'          => 0,
						'sub_fields'   => array(
							array(
								'key'      => 'field_nera_vc_prot_title',
								'label'    => __( 'Title', 'nera-voluntary-code-plugin' ),
								'name'     => 'title',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => array( 'width' => '30' ),
							),
							array(
								'key'     => 'field_nera_vc_prot_body',
								'label'   => __( 'Description', 'nera-voluntary-code-plugin' ),
								'name'    => 'body',
								'type'    => 'textarea',
								'rows'    => 3,
								'wrapper' => array( 'width' => '70' ),
							),
						),
					),

					// ── Transparency repeater ──────────────────────────────────────
					array(
						'key'          => 'field_nera_vc_trans',
						'label'        => __( 'Transparency', 'nera-voluntary-code-plugin' ),
						'name'         => 'vc_measures_transparency',
						'type'         => 'repeater',
						'instructions' => __( 'Plain-English measures shown under the Transparency heading.', 'nera-voluntary-code-plugin' ),
						'button_label' => __( 'Add measure', 'nera-voluntary-code-plugin' ),
						'layout'       => 'block',
						'min'          => 0,
						'max'          => 0,
						'sub_fields'   => array(
							array(
								'key'      => 'field_nera_vc_trans_title',
								'label'    => __( 'Title', 'nera-voluntary-code-plugin' ),
								'name'     => 'title',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => array( 'width' => '30' ),
							),
							array(
								'key'     => 'field_nera_vc_trans_body',
								'label'   => __( 'Description', 'nera-voluntary-code-plugin' ),
								'name'    => 'body',
								'type'    => 'textarea',
								'rows'    => 3,
								'wrapper' => array( 'width' => '70' ),
							),
						),
					),

					// ── Accountability repeater ────────────────────────────────────
					array(
						'key'          => 'field_nera_vc_acct',
						'label'        => __( 'Accountability', 'nera-voluntary-code-plugin' ),
						'name'         => 'vc_measures_accountability',
						'type'         => 'repeater',
						'instructions' => __( 'Plain-English measures shown under the Accountability heading.', 'nera-voluntary-code-plugin' ),
						'button_label' => __( 'Add measure', 'nera-voluntary-code-plugin' ),
						'layout'       => 'block',
						'min'          => 0,
						'max'          => 0,
						'sub_fields'   => array(
							array(
								'key'      => 'field_nera_vc_acct_title',
								'label'    => __( 'Title', 'nera-voluntary-code-plugin' ),
								'name'     => 'title',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => array( 'width' => '30' ),
							),
							array(
								'key'     => 'field_nera_vc_acct_body',
								'label'   => __( 'Description', 'nera-voluntary-code-plugin' ),
								'name'    => 'body',
								'type'    => 'textarea',
								'rows'    => 3,
								'wrapper' => array( 'width' => '70' ),
							),
						),
					),

					// ── ADR provider ───────────────────────────────────────────────
					array(
						'key'          => 'field_nera_vc_adr_provider',
						'label'        => __( 'ADR provider', 'nera-voluntary-code-plugin' ),
						'name'         => 'vc_adr_provider',
						'type'         => 'text',
						'instructions' => __( 'Name (and optionally URL) of your independent Alternative Dispute Resolution provider. Shown in the Accountability section. Leave blank if not yet appointed.', 'nera-voluntary-code-plugin' ),
						'wrapper'      => array( 'width' => '100' ),
					),

					// ── Footer badge toggle ────────────────────────────────────────
					array(
						'key'           => 'field_nera_vc_sp_footer',
						'label'         => __( 'Footer badge', 'nera-voluntary-code-plugin' ),
						'name'          => 'vc_sp_footer',
						'type'          => 'true_false',
						'instructions'  => __( 'Show the "We follow the Voluntary Code" badge at the bottom of every page.', 'nera-voluntary-code-plugin' ),
						'ui'            => 1,
						'ui_on_text'    => __( 'Yes', 'nera-voluntary-code-plugin' ),
						'ui_off_text'   => __( 'No', 'nera-voluntary-code-plugin' ),
						'default_value' => 1,
						'wrapper'       => array( 'width' => '50' ),
					),

					// ── Footer badge text ──────────────────────────────────────────
					array(
						'key'           => 'field_nera_vc_footer_text',
						'label'         => __( 'Footer badge text', 'nera-voluntary-code-plugin' ),
						'name'          => 'vc_footer_text',
						'type'          => 'text',
						'default_value' => self::DEFAULT_FOOTER_TEXT,
						'wrapper'       => array( 'width' => '50' ),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => self::OPTIONS_SLUG,
						),
					),
				),
				'menu_order'            => 20,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'description'           => '',
			)
		);
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Typed getters
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Whether the footer badge is enabled.
	 *
	 * Defaults to TRUE when ACF is absent or the field has never been saved.
	 *
	 * @return bool
	 */
	public static function is_footer_enabled() {
		if ( ! function_exists( 'get_field' ) ) {
			return true;
		}
		$value = get_field( 'vc_sp_footer', self::OPTIONS_POST_ID );
		if ( null === $value ) {
			return true;
		}
		return (bool) $value;
	}

	/**
	 * Footer badge text (falls back to built-in default).
	 *
	 * @return string
	 */
	public static function footer_text() {
		if ( ! function_exists( 'get_field' ) ) {
			return self::DEFAULT_FOOTER_TEXT;
		}
		$text = get_field( 'vc_footer_text', self::OPTIONS_POST_ID );
		if ( empty( $text ) || ! is_string( $text ) ) {
			return self::DEFAULT_FOOTER_TEXT;
		}
		return sanitize_text_field( $text );
	}

	/**
	 * Intro copy for the commitment page (falls back to built-in default).
	 *
	 * @return string HTML — already safe for wp_kses_post output.
	 */
	public static function intro_copy() {
		if ( ! function_exists( 'get_field' ) ) {
			return self::DEFAULT_INTRO_COPY;
		}
		$copy = get_field( 'vc_intro_copy', self::OPTIONS_POST_ID );
		if ( empty( $copy ) || ! is_string( $copy ) ) {
			return self::DEFAULT_INTRO_COPY;
		}
		return $copy;
	}

	/**
	 * GOV.UK Voluntary Code URL (falls back to built-in default).
	 *
	 * @return string
	 */
	public static function govuk_url() {
		if ( ! function_exists( 'get_field' ) ) {
			return self::DEFAULT_GOVUK_URL;
		}
		$url = get_field( 'vc_govuk_url', self::OPTIONS_POST_ID );
		if ( empty( $url ) || ! is_string( $url ) ) {
			return self::DEFAULT_GOVUK_URL;
		}
		return esc_url_raw( $url );
	}

	/**
	 * ADR provider name/details (empty string when not set).
	 *
	 * @return string
	 */
	public static function adr_provider() {
		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}
		$provider = get_field( 'vc_adr_provider', self::OPTIONS_POST_ID );
		if ( empty( $provider ) || ! is_string( $provider ) ) {
			return '';
		}
		return sanitize_text_field( $provider );
	}

	/**
	 * ID of the auto-created commitment page (0 when not yet created).
	 *
	 * @return int
	 */
	public static function page_id() {
		return (int) get_option( 'nera_vc_page_id', 0 );
	}
}
