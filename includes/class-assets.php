<?php
/**
 * Assets — enqueue front-end stylesheet.
 *
 * The voluntary-code.css is intentionally small (footer badge, page layout,
 * measure cards) and is loaded on every front-end page so the footer badge
 * renders without a conditional check here.
 *
 * @package Nera_Voluntary_Code
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and enqueues the plugin's front-end stylesheet.
 */
class Nera_VC_Assets {

	const STYLE_HANDLE = 'nera-vc';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
	}

	/**
	 * Register and enqueue the plugin stylesheet on all front-end pages.
	 * Admin pages are skipped (is_admin() is true during wp_enqueue_scripts only
	 * on block-editor iframes; the action itself does not fire for wp-admin).
	 */
	public static function enqueue() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'nera-vc',
			NERA_VC_PLUGIN_URL . 'assets/css/voluntary-code.css',
			array(),
			NERA_VC_VERSION
		);
	}
}
