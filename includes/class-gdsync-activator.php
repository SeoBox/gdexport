<?php

/**
 * Fired during plugin activation
 *
 * @link       http://seobox.io
 * @since      1.0.0
 *
 * @package    GDSync
 * @subpackage GDSync/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GDSync
 * @subpackage GDSync/includes
 * @author     SEOBox
 */
class GDSync_Activator {

	public static function activate() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'gdsync';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`secret` TINYTEXT NOT NULL,
		`version` mediumint(9),
		PRIMARY KEY	(id)
	) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		set_transient( 'gdsync-admin-notice', true, 10 );
	}

}
