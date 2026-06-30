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

		$url  = esc_url( $url );
		$text = esc_html( $text );
		?>
		<div class="nera-vc-footer-badge" role="contentinfo" aria-label="<?php esc_attr_e( 'Voluntary Code commitment', 'nera-voluntary-code-plugin' ); ?>">
			<div class="nera-vc-footer-badge__inner">
				<a class="nera-vc-footer-badge__link" href="<?php echo $url; ?>">
					<svg class="nera-vc-footer-badge__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
						<polyline points="9 12 11 14 15 10"/>
					</svg>
					<span class="nera-vc-footer-badge__text"><?php echo $text; ?></span>
					<svg class="nera-vc-footer-badge__chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="9 18 15 12 9 6"/>
					</svg>
				</a>
			</div>
		</div>
		<?php
	}
}
