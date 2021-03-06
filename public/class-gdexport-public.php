<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    GDExport
 * @subpackage GDExport/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    GDExport
 * @subpackage GDExport/public
 * @author     SEOBox
 */
class GDExport_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GDExport_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GDExport_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gdexport-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in GDExport_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The GDExport_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gdexport-public.js', array( 'jquery' ), $this->version, false );

	}

	function encode_result( $result = array(), $die = true ) {
		$result = array_merge( $result, array(
			"version"           => GDEXPORT_VERSION,
			"wordpress_version" => get_bloginfo( 'version' ),
		) );

		$error = $result['error_object'];
		if (isset($error) && is_wp_error( $error )) {
			$result['error'] = $error->get_error_message();
			unset($result['error_object']);
		}

		echo json_encode( $result );

		if ( $die ) {
			wp_die();
		}
	}

	function gdexport_receive_post() {
		$this->set_current_user();

		$post_data = json_decode( file_get_contents( 'php://input' ), true );
		$post      = array(
			'post_type'    => $post_data['type'],
			'post_title'   => $post_data['title'],
			'post_content' => $post_data['content'],
			'post_status'  => 'draft',
		);

		$id = wp_insert_post( $post, true );

		if ( is_wp_error( $id ) ) {
			$this->encode_result( array( "error" => $id->get_error_message() ) );
		} else {
			$this->gdexport_segmented_post_hook( $id );
		}
		wp_die();
	}

	function gdexport_receive_image() {
		$this->set_current_user();

		$upload_overrides = array( 'test_form' => false );
		$attachment_id    = media_handle_upload( 'file', 0, array(), $upload_overrides );

		$this->encode_result( array(
			'url'   => wp_get_attachment_url( $attachment_id ),
			'id'    => $attachment_id,
			'error_object' => $attachment_id
		) );
	}

	function gdexport_set_featured_image() {
		$this->set_current_user();

		$post_data    = json_decode( file_get_contents( 'php://input' ), true );
		$thumbnail_id = $post_data['thumbnail_id'];
		$post_id      = $post_data['post_id'];

		$result = array();
		// if there is already a thumbnail, set_post_thumbnail returns false
		delete_post_thumbnail( $post_id );
		if ( ! set_post_thumbnail( $post_id, $thumbnail_id ) ) {
			$result = array( "error" => "Unable to set a featured image" );
		}

		$this->encode_result( $result );
	}

	function gdexport_version() {
		$this->encode_result();
	}

	function set_current_user() {
		global $wpdb;

		if ( ! isset( $_SERVER['HTTP_X_GDEXPORT_SIGNATURE'] ) ) {
			wp_die( "{\"error\": \"HTTP header 'X-GDExport-Signature' is missing.\"}" );
		}

		list( $algo, $hash ) = explode( '=', $_SERVER['HTTP_X_GDEXPORT_SIGNATURE'], 2 ) + array( '', '' );
		$raw_post = file_get_contents( 'php://input' );
		if ( strlen( $raw_post ) == 0 ) {
			$raw_post = file_get_contents( $_FILES['file']['tmp_name'] );
		}

		$table_name = $wpdb->prefix . 'gdexport';
		$users      = $wpdb->get_results( "SELECT * FROM `$table_name` WHERE version=1" );
		$match      = false;
		foreach ( $users as $user ) {
			$hmac = hash_hmac( $algo, $raw_post, $user->secret );
			if ( ! empty( $hash ) && ! empty( $hmac ) && $hash == $hmac ) {
				$match = $user->user_id;
				break;
			}
		}
		if ( $match ) {
			wp_set_current_user( $match );

			return $match;
		} else {
			wp_die( '{"error": "Secret hash does not match."}' );
		}
	}

	function gdexport_aggregate_post( $unique_identifier, $final_number, $real_title, $final_post ) {
		$final_content = '';
		for ( $i = 0; $i < $final_number; $i ++ ) {
			$post          = get_page_by_title( "$i::$unique_identifier", OBJECT, 'post' );
			$final_content = $final_content . $post->post_content;
			wp_delete_post( $post->ID, true );
		}
		$final_content = $final_content . $final_post->post_content;
		wp_delete_post( $final_post->ID, true );

		$post          = array(
			'post_type'    => $final_post->post_type,
			'post_title'   => $real_title,
			'post_content' => $final_content,
			'post_status'  => 'draft',
		);
		$final_content = null;


		$id     = wp_insert_post( $post, true );
		$result = array(
			'url'   => get_edit_post_link( $id ),
			'id'    => $id,
			"error_object" => $id
		);

		$this->encode_result( $result, $die = false );
	}

	function gdexport_segmented_post_hook( $post_id ) {
		$post = get_post( $post_id );
		list( $final, $num, $unique_identifier, $real_title ) = explode( '::', $post->post_title );

		if ( ! empty( $real_title ) && ( $final == 'final' ) ) {
			$this->gdexport_aggregate_post( $unique_identifier, $num, $real_title, $post );
		} else {
			$this->encode_result( array(
				'url' => get_edit_post_link( $post_id ),
				'id'  => $post_id
			), $die = false );
		}
	}

}
