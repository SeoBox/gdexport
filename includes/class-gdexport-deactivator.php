<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://seobox.io
 * @since      1.0.0
 *
 * @package    GDExport
 * @subpackage GDExport/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GDExport
 * @subpackage GDExport/includes
 * @author     SEOBox
 */
class GDExport_Deactivator {

	public static function deactivate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdexport';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( "DROP TABLE $table_name;" );
	}
}
