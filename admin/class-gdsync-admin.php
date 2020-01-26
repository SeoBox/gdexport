<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://seobox.io
 * @since      1.0.0
 *
 * @package    GDSync
 * @subpackage GDSync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    GDSync
 * @subpackage GDSync/admin
 * @author     SEOBox
 */
class GDSync_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GDSync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GDSync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gdsync-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GDSync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GDSync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gdsync-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_notices() {
		if ( get_transient( 'gdsync-admin-notice' ) ) {
			echo '<div class="notice notice-warning"><p>GDSync Activated! Next, connect to GDSync in your <a href="' . admin_url() . 'options-general.php?page=gdsync-plugin">Settings</a>.</p></div>';
			delete_transient( 'gdsync-admin-notice' );
		}
	}

	public function admin_menu() {
		add_options_page( 'GDSync', 'GDSync', 'manage_options', 'gdsync-plugin', array( $this, "settings_page" ) );
	}

	public function add_action_links( $links_array, $plugin_file ) {
		array_unshift( $links_array, '<a href="options-general.php?page=gdsync-plugin">' . __( 'Settings', 'General' ) . '</a>' );

		return $links_array;
	}

	function gdsync_load_users() {
		$users  = get_users( 'who=authors' );
		$result = array();
		foreach ( $users as $user ) {
			array_push( $result, $this->gdsync_load_or_select_user( $user->ID ) );
		}

		return $result;
	}


	function gdsync_load_or_select_user( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gdsync';

		$result = $this->gdsync_select_user( $user_id );
		if ( sizeof( $result ) === 0 ) {
			$token = $this->gdsync_uuidv4();
			$wpdb->insert(
				$table_name,
				array(
					'user_id' => $user_id,
					'secret'  => $token,
					'version' => 1,
				)
			);

			return $this->gdsync_select_user( $user_id )[0];
		} else {
			return $result[0];
		}
	}

	/**
	 * Return a UUID (version 4) using random bytes
	 * Note that version 4 follows the format:
	 *     xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
	 * where y is one of: [8, 9, A, B]
	 *
	 * We use (random_bytes(1) & 0x0F) | 0x40 to force
	 * the first character of hex value to always be 4
	 * in the appropriate position.
	 *
	 * For 4: http://3v4l.org/q2JN9
	 * For Y: http://3v4l.org/EsGSU
	 * For the whole shebang: https://3v4l.org/LNgJb
	 *
	 * @ref https://stackoverflow.com/a/31460273/2224584
	 * @ref https://paragonie.com/b/JvICXzh_jhLyt4y3
	 *
	 * @return string
	 */
	function gdsync_uuidv4() {
		return implode( '-', [
			bin2hex( random_bytes( 4 ) ),
			bin2hex( random_bytes( 2 ) ),
			bin2hex( chr( ( ord( random_bytes( 1 ) ) & 0x0F ) | 0x40 ) ) . bin2hex( random_bytes( 1 ) ),
			bin2hex( chr( ( ord( random_bytes( 1 ) ) & 0x3F ) | 0x80 ) ) . bin2hex( random_bytes( 1 ) ),
			bin2hex( random_bytes( 6 ) )
		] );
	}

	function gdsync_select_user( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gdsync';

		return $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT * FROM `$table_name`
			 WHERE user_id = %d
			  AND version = %d
			",
				$user_id, 1
			)
		);
	}

	function gdsync_connect_to_gdsync_url() {
		$user = wp_get_current_user();

		return $this->gdsync_connect_user_url( $this->gdsync_load_or_select_user( $user->ID ) );
	}

	function gdsync_connect_user_url( $user ) {
		$path = '/user/sites/new';
		if ( file_exists( __DIR__ . '/host' ) ) {
			$host = file_get_contents( __DIR__ . '/host' );
			$url  = $host . $path;
		} else {
			$url = "https://app.seobox.io$path";
		}

		$username = urlencode( get_userdata( $user->user_id )->user_login );
		$email = urlencode( get_userdata( $user->user_id )->user_email );
		return "$url?site[user][external_id]=$user->secret&site[user][user_id]=$user->user_id&site[user][username]=" . $username . '&role_user[user_email]=' . $email . '&site[user][connect_method]=plugin&site[url]=' . urlencode( get_site_url() ) . '&site[admin_url]=' . urlencode( admin_url() );
	}



	public function settings_page() {
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		$users = $this->gdsync_load_users();
		$url   = $this->gdsync_connect_to_gdsync_url();
		include_once 'partials/gdsync-admin-display.php';
	}


}
