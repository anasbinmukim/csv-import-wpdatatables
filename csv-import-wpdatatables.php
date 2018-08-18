<?php
/*
Plugin Name: CSV import wpdatatables
Plugin URI: http://plugins.rmweblab.com/
Description: Import CSV to wp datatables
Author: Anas
Version: 1.0.0
Author URI: http://rmweblab.com
Copyright: © 2018 RMWebLab.
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: csv-import-wpdatables
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Main CSVImportWPDatables clas set up for us
 */
class CSVImportWPDatables {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'CSVIWPTABLES', __FILE__ );
		define('CSVIWPTABLES_ROOT', dirname(__FILE__));
		define( 'CSVIWPTABLES_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'CSVIWPTABLESHTTP', 'https' );
		// Actions
    add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
    add_action( 'admin_menu', array( $this, 'admin_settings_menu' ) );
    // add_action( 'save_post', array( $this, 'save_latest_updated_time' ), 11, 3 );
		// add_action( 'wp', array( $this, 'process_redirect' ) );
		// add_action( 'init', array( $this, 'process_agree_link' ) );
		// add_action( 'wp_footer', array( $this, 'display_forcettnc_notice' ) );
		// add_action( 'wp_head', array( $this, 'add_script_forcettnc_head' ) );
		// add_action( 'manage_users_columns', array( $this, 'fttnc_modify_user_columns' ) );
		// add_action( 'manage_users_custom_column', array( $this, 'fttnc_user_column_content' ), 10, 3 );

	}

	/**
	 * Init localisations and hook
	 */
	public function init() {

		ini_set('max_execution_time', 300); //300 seconds = 5 minutes

		// Includes
		//https://github.com/parsecsv/parsecsv-for-php
		require_once( 'parsecsv-for-php/parsecsv.lib.php' );

		require_once( 'inc/csv-functions.php' );

		//execute data
		require_once( 'inc/data-import.php' );



		// Localisation
		load_plugin_textdomain( 'csv-import-wpdatables', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

  /**
	 * Add admin settings menu
	 */
	public function admin_settings_menu() {
    add_submenu_page('tools.php', 'CSV Import', 'CSV Import', 'manage_options', 'csv-import-wpdatables', array(	$this,	'csvimport_settings_page'));
	}

  public function csvimport_settings_page(){
    // Admin Seettings page
		require_once( 'inc/csv-import.php' );

  }



}

new CSVImportWPDatables();
