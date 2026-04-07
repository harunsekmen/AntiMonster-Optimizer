<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package AntiMonster Optimizer
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the plugin options from the database.
delete_option( 'antimonster_optimizer_settings' );
