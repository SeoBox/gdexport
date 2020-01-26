<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://seobox.io
 * @since      1.0.0
 *
 * @package    GDSync
 * @subpackage GDSync/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GDSync
 * @subpackage GDSync/includes
 * @author     SEOBox
 */
class GDSync_Deactivator {

	public static function deactivate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdsync';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( "DROP TABLE $table_name;" );
	}
}
