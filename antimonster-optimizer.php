<?php
/**
 * Plugin Name: AntiMonster Optimizer
 * Plugin URI:  https://www.linkedin.com/in/harunsekmen
 * Description: Tame the WordPress Frankenstein! A premium, lightweight optimization plugin to slice off monstrous bloat and unused core features.
 * Version:     1.0.1
 * Author:      Harun Sekmen
 * Author URI:  https://www.linkedin.com/in/harunsekmen
 * License:     GPLv2 or later
 * Text Domain: antimonster-optimizer
 * Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ANTIMONSTER_OPTIMIZER_VERSION', '1.0.1' );
define( 'ANTIMONSTER_OPTIMIZER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANTIMONSTER_OPTIMIZER_URL', plugin_dir_url( __FILE__ ) );

/**
 * 2 & 3. Safe Defaults on Activation
 */
function antimonster_optimizer_activate() {
	$options = get_option( 'antimonster_optimizer_settings' );
	if ( false === $options ) {
		// Define safe defaults: these don't break typical sites but add performance
		$safe_defaults = array(
			'disable_emojis'          => '1',
			'disable_embeds'          => '1',
			'hide_wp_version'         => '1',
			'remove_wlwmanifest_link' => '1',
			'remove_rsd_link'         => '1',
			'remove_shortlink'        => '1',
			'disable_dashicons'       => '1',
		);
		update_option( 'antimonster_optimizer_settings', $safe_defaults );
	}
}
register_activation_hook( __FILE__, 'antimonster_optimizer_activate' );

/**
 * 4. Load plugin textdomain for Translations
 */
function antimonster_optimizer_load_textdomain() {
	load_plugin_textdomain( 'antimonster-optimizer', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'antimonster_optimizer_load_textdomain' );

/**
 * Settings Link on Plugins Page
 */
function antimonster_optimizer_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=antimonster-optimizer">' . __( 'Settings', 'antimonster-optimizer' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'antimonster_optimizer_settings_link' );

/**
 * Initialize the plugin classes.
 */
function run_antimonster_optimizer() {
	require_once ANTIMONSTER_OPTIMIZER_PATH . 'includes/class-settings.php';
	require_once ANTIMONSTER_OPTIMIZER_PATH . 'includes/class-optimizer.php';

	if ( is_admin() ) {
		$antimonster_settings = new AntiMonster_Optimizer_Settings();
	}
	$antimonster_optimizer = new AntiMonster_Optimizer_Engine();
}

run_antimonster_optimizer();
