<?php
/**
 * Footer — sitewide commitment badge injected via wp_footer.
 *
 * @package Nera_Voluntary_Code
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the sitewide footer badge linking to the commitment page.
 */
class Nera_VC_Footer {

	/**
	 * Hook into WordPress.
	 *
	 * Priority 50 — after theme footer, before Alpine/toast output at 999.
	 */
	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 50 );
	}

	/**
	 * Render and output the footer badge HTML.
	 *
	 * Guards (bail if any fails):
	 * - Not in wp-admin.
	 * - Footer badge enabled in settings.
	 * - Commitment page ID is set and published.
	 * - Permalink resolves.
	 *
	 * @return void
	 */
	public static function render() {
		if ( is_admin() ) {
			return;
		}

		if ( ! Nera_VC_Settings::is_footer_enabled() ) {
			return;
		}

		$page_id = Nera_VC_Settings::page_id();

		if ( $page_id <= 0 ) {
			return;
		}

		$status = get_post_status( $page_id );

		if ( 'publish' !== $status ) {
			return;
		}

		$url  = get_permalink( $page_id );
		$text = Nera_VC_Settings::footer_text();

		if ( ! $url ) {
			return;
		}

		printf(
			'<div class="nera-vc-footer-badge"><a class="nera-vc-footer-badge__link" href="%1$s"><span class="nera-vc-footer-badge__icon" aria-hidden="true">🛡</span> %2$s</a></div>',
			esc_url( $url ),
			esc_html( $text )
		);
	}
}
