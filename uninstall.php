<?php
/**
 * Uninstall handler — runs when the plugin is deleted from the Plugins screen.
 *
 * CONSERVATIVE uninstall policy:
 * - Only the plugin's internal marker options are removed.
 * - The commitment PAGE is NOT deleted (content created by/for the site).
 * - ACF field data is NOT deleted (admin-authored content).
 * - The ACF field group definitions are local (PHP-registered) so they vanish
 *   automatically when the plugin is removed.
 *
 * @package Nera_Voluntary_Code
 */

// Guard: must be called via WP uninstall pipeline, not directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'nera_vc_page_id' );
delete_option( 'nera_vc_seeded' );
