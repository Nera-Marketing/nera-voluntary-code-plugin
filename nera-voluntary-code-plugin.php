<?php
/**
 * Plugin Name: nera-voluntary-code-plugin
 * Plugin URI: https://github.com/Nera-Marketing/nera-voluntary-code-plugin
 * Description: Publishes the operator's "Our commitment to player protection" page (UK Voluntary Code of Good Practice for Prize Draw Operators, clause 3.4 – Public Disclosure). Auto-creates an editable page listing the measures in place across Player Protections, Transparency and Accountability, with an adherence statement, a GOV.UK link, and a sitewide footer badge. Content is admin-editable via ACF under Theme Settings → Nera Features.
 * Version: 1.0.0
 * Author: Nera
 * Text Domain: nera-voluntary-code-plugin
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 *
 * @package Nera_Voluntary_Code
 */

use YahnisElsts\PluginUpdateChecker\v5p5\Vcs\GitHubApi;

defined( 'ABSPATH' ) || exit;

define( 'NERA_VC_VERSION', '1.0.0' );
define( 'NERA_VC_PLUGIN_FILE', __FILE__ );
define( 'NERA_VC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NERA_VC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * GitHub updates (Plugin Update Checker v5.5). On by default when `lib/plugin-update-checker/load-v5p5.php` exists.
 * Parity with nera-spending-amount-limit-plugin / nera-instant-win-threshold / nera-spin-to-win.
 *
 * Disable:      define( 'NERA_VC_DISABLE_GITHUB_UPDATES', true );
 * Private repo: define( 'NERA_VC_GITHUB_TOKEN', 'ghp_...' );
 * Custom URL:   define( 'NERA_VC_GITHUB_REPO_URL', 'https://github.com/Owner/repo/' );  (or filter nera_vc_github_repo_url)
 *
 * PUC reads the `Version` header from the GitHub ref it selects. Bump `Version` + `NERA_VC_VERSION` for every
 * release, then tag/push to match. A custom setReleaseFilter (always true) + maxReleases > 1 makes GitHubApi
 * use the paginated /releases endpoint instead of /latest (which 404s without a GitHub "latest" release).
 * enableReleaseAssets() prefers the attached zip over the tag tarball.
 *
 * @link https://github.com/YahnisElsts/plugin-update-checker
 */
if ( ! defined( 'NERA_VC_DISABLE_GITHUB_UPDATES' ) || ! NERA_VC_DISABLE_GITHUB_UPDATES ) {
	$nera_vc_github_repo_default = 'https://github.com/Nera-Marketing/nera-voluntary-code-plugin/';
	if ( defined( 'NERA_VC_GITHUB_REPO_URL' ) && is_string( NERA_VC_GITHUB_REPO_URL ) && NERA_VC_GITHUB_REPO_URL !== '' ) {
		$nera_vc_github_repo_default = NERA_VC_GITHUB_REPO_URL;
	}
	$nera_vc_github_repo = apply_filters( 'nera_vc_github_repo_url', $nera_vc_github_repo_default );

	$nera_vc_puc_loader = NERA_VC_PLUGIN_DIR . 'lib/plugin-update-checker/load-v5p5.php';
	if ( is_readable( $nera_vc_puc_loader ) ) {
		require_once $nera_vc_puc_loader;
		// Fourth argument: check period in hours (PUC default is 12).
		$nera_vc_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			$nera_vc_github_repo,
			__FILE__,
			'nera-voluntary-code-plugin',
			6
		);
		$nera_vc_update_checker->setBranch( 'main' );

		if ( defined( 'NERA_VC_GITHUB_TOKEN' ) && is_string( NERA_VC_GITHUB_TOKEN ) && NERA_VC_GITHUB_TOKEN !== '' ) {
			$nera_vc_update_checker->setAuthentication( NERA_VC_GITHUB_TOKEN );
		}

		// GitHub-hosted updates carry no plugin icon, so the Dashboard → Updates and
		// Plugins screens show a blank logo. Inject the bundled logo.png as the icon.
		$nera_vc_update_checker->addResultFilter(
			static function ( $plugin_info ) {
				if ( is_object( $plugin_info ) && is_readable( NERA_VC_PLUGIN_DIR . 'logo.png' ) ) {
					$logo                = NERA_VC_PLUGIN_URL . 'logo.png';
					$plugin_info->icons = array(
						'1x'      => $logo,
						'2x'      => $logo,
						'default' => $logo,
					);
				}
				return $plugin_info;
			}
		);

		$nera_vc_puc_vcs = $nera_vc_update_checker->getVcsApi();
		if ( $nera_vc_puc_vcs instanceof GitHubApi ) {
			$nera_vc_puc_vcs->setReleaseFilter(
				static function ( $version_number, $release_object ) {
					unset( $version_number, $release_object );
					return true;
				},
				\YahnisElsts\PluginUpdateChecker\v5p5\Vcs\Api::RELEASE_FILTER_SKIP_PRERELEASE,
				20
			);
			$nera_vc_puc_vcs->enableReleaseAssets();
		}
	}
}

require_once NERA_VC_PLUGIN_DIR . 'includes/class-settings.php';
require_once NERA_VC_PLUGIN_DIR . 'includes/class-content.php';
require_once NERA_VC_PLUGIN_DIR . 'includes/class-page.php';
require_once NERA_VC_PLUGIN_DIR . 'includes/class-assets.php';
require_once NERA_VC_PLUGIN_DIR . 'includes/class-footer.php';

/**
 * Bootstrap plugin.
 */
function nera_vc_init() {
	load_plugin_textdomain( 'nera-voluntary-code-plugin', false, dirname( plugin_basename( NERA_VC_PLUGIN_FILE ) ) . '/languages' );

	Nera_VC_Settings::init();
	Nera_VC_Content::init();
	Nera_VC_Page::init();
	Nera_VC_Assets::init();
	Nera_VC_Footer::init();
}
add_action( 'plugins_loaded', 'nera_vc_init', 20 );

/**
 * WooCommerce HPOS (custom order tables) compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Activation: create/adopt commitment page and seed default content.
 */
register_activation_hook( NERA_VC_PLUGIN_FILE, array( 'Nera_VC_Page', 'activate' ) );
