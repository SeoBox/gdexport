<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name:       GDExport
 * Plugin URI:        http://seobox.io/
 * Description:       Export Google Docs into Wordpress as posts and pages
 * Version:           1.0.4
 * Author:            SEOBox
 * Author URI:        http://seobox.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       GDExport
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GDEXPORT_VERSION', '1.0.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gdexport-activator.php
 */
function activate_gdexport() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdexport-activator.php';
	GDExport_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gdexport-deactivator.php
 */
function deactivate_gdexport() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdexport-deactivator.php';
	GDExport_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gdexport' );
register_deactivation_hook( __FILE__, 'deactivate_gdexport' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gdexport.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_gdexport() {

	$plugin = new GDExport();
	$plugin->run();

}
run_gdexport();
