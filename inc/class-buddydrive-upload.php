<?php
/**
 * BuddyDrive Upload
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'BP_Attachment', false ) ) {

/**
 * The BuddyDrive Upload Class
 *
 * @package BuddyDrive
 * @since 3.0.0
 */
class BuddyDrive_Upload extends BP_Attachment {

	/**
	 * Construct method to add some settings and hooks
	 *
	 * @package BuddyDrive
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct( array(
			'action'               => 'buddydrive_upload',
			'file_input'           => 'buddyfile-upload',
			'base_dir'             => 'buddydrive',
			'upload_error_strings' => buddydrive_get_upload_error_strings(),
			'allowed_mime_types'   => buddydrive_get_allowed_upload_exts(),
		) );
	}

	public function set_upload_dir() {}

	/**
	 * Get BuddyDrive upload data
	 *
	 * @since 1.3.0
	 *
	 * @return array an associative array to inform about the upload base dir and url
	 */
	public function get_upload_data() {
		// Make sure to run this part once
		if ( empty( $this->upload_data['dir'] ) ) {
			/**
			 * In previous version it was possible to change
			 * the upload data before upload dir has been created
			 * using this filter. Keeping it for backcompat
			 *
			 * @since  1.2.0
			 *
			 * @param array an associative array to inform about the upload base dir and url
			 */
			$this->upload_data = apply_filters( 'buddydrive_get_upload_data', array(
				'dir'      => $this->upload_path,
				'url'      => $this->url,
				'thumbdir' => $this->upload_path . '-thumbnails',
				'thumburl' => $this->url . '-thumbnails',
			) );

			if ( $this->upload_data['dir'] != $this->upload_path ) {
				$this->upload_path = $this->upload_data['dir'];
			}

			if ( $this->upload_data['url'] != $this->url ) {
				$this->url = $this->upload_data['url'];
			}
		}

		return $this->upload_data;
	}

	/**
	 * Create the BuddyDrive dir and add an .htaccess in it
	 *
	 * @since 1.3.0
	 *
	 * @return bool true on dir created, false otherwise
	 */
	public function create_dir() {
		// Let's be sure old filter is fired
		$this->get_upload_data();

		// Create a public folder for thumbnails
		if ( ! empty( $this->upload_data['thumbdir'] ) && ! is_dir( $this->upload_data['thumbdir'] ) ) {
			wp_mkdir_p( $this->upload_data['thumbdir'] );
		}

		// Check if upload path already exists
		if ( ! is_dir( $this->upload_path ) ) {

			// If path does not exist, attempt to create it
			if ( ! wp_mkdir_p( $this->upload_path ) ) {
				return false;
			}

			// then we need to check for .htaccess and eventually create it
			if ( ! file_exists( $this->upload_path .'/.htaccess' ) ) {
				$this->required_wp_files['misc'] = 'misc';
				$this->includes();

				// Defining the rule, we need to make it unreachable and use php to reach it
				$rules = array( 'Order Allow,Deny','Deny from all' );

				// creating the .htaccess file
				insert_with_markers( $this->upload_path .'/.htaccess', 'Buddydrive', $rules );
				unset( $this->required_wp_files['misc'] );
			}
		}

		// Directory exists
		return true;
	}

	public function upload( $file, $upload_dir_filter = '' ) {}

	/**
	 * Set the directory when uploading a file
	 *
	 * @since 1.3.0
	 * @since 1.3.3 Add the $upload_dir parameter
	 *
	 * @return array upload data (path, url, basedir...)
	 */
	public function upload_dir_filter( $upload_dir = array() ) {
		$upload_data = parent::upload_dir_filter( $upload_dir );
		/**
		 * Filters BuddyDrive's upload data.
		 *
		 * If people used to filter 'buddydrive_upload_datas', we need
		 * to have it here
		 *
		 * @since 1.0
		 *
		 * @param array $value Array containing the path, URL, and other helpful settings.
		 */
		return apply_filters( 'buddydrive_upload_datas', $upload_data );
	}

	/**
	 * BuddyDrive specific upload rules
	 *
	 * @since 1.3.0
	 *
	 * @param  array $file the temporary file attributes (before it has been moved)
	 * @return array the file
	 */
	public function validate_upload( $file = array() ) {
		// there's already an error
		if ( ! empty( $file['error'] ) ) {
			return $file;
		}

		// This codes are restricted to BuddyDrive
		$buddydrive_errors = array(
			9  => 1,
			10 => 1,
			11 => 1,
		);

		// what's left in user's quota ?
		$space_left = buddydrive_get_user_space_left( 'diff' );
		$file_size = filesize( $file['tmp_name'] );

		// File is bigger than space left
		if ( $space_left < $file_size ) {
			$file['error'] = 9;
		}

		// File is bigger than the max allowed for BuddyDrive files
		if ( $file_size > buddydrive_max_upload_size( true ) ) {
			$file['error'] = 10;
		}

		// No more space left
		if ( $space_left <= 0 ) {
			$file['error'] = 11;
		}

		if ( ! isset( $buddydrive_errors[ $file['error'] ] ) ) {
			/**
			 * Validation for custom errors
			 *
			 * @since 1.2.2
			 *
			 * @param $file the file data
			 */
			return apply_filters( 'buddydrive_upload_errors', $file );
		}

		return $file;
	}

}

$buddydrive_upload = new BuddyDrive_Upload();

}
