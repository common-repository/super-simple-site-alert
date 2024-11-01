<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Super Simple Site Alert
 * Plugin URI:
 * Description:       Broadcast simple, important alerts across your multisite network. Also great for single sites.
 * Version:           1.3.1
 * Requires at least: 5.7
 * Requires PHP:			7.3
 * Author:            Keng Her
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SITE_ALERT_VERSION', '1.3.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-site-alert-activator.php
 */
function activate_site_alert() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-site-alert-activator.php';
	Site_Alert_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-site-alert-deactivator.php
 */
function deactivate_site_alert() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-site-alert-deactivator.php';
	Site_Alert_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_site_alert' );
register_deactivation_hook( __FILE__, 'deactivate_site_alert' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-site-alert.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_site_alert() {

	$plugin = new Site_Alert();
	$plugin->run();

}
run_site_alert();
