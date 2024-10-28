<?php

/**
 * Fired during plugin activation
 *
 * @link       https://swit.hr/
 * @since      1.0.0
 *
 * @package    Activitytime
 * @subpackage Activitytime/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Activitytime
 * @subpackage Activitytime/includes
 * @author     SWIT <sandi@swit.hr>
 */
class Activitytime_Activator {

    public static $db_version = 1.0;

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$prefix = 'actt_';
		
        add_option($prefix.'log_days', '100');
        add_option($prefix.'timeout_mins', '10');

        
    }
    
    public static function plugins_loaded(){

		if ( get_site_option( 'actt_db_version' ) === false ||
		     get_site_option( 'actt_db_version' ) < self::$db_version ) {
			self::install();
		}

    }
    
    	// https://codex.wordpress.org/Creating_Tables_with_Plugins
	public static function install() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();
		// For init version 1.0
		if(get_site_option( 'actt_db_version' ) === false)
		{
			// Main table for visited pages

			$table_name = $wpdb->prefix . 'actt_visited_pages';

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				`idvisited_pages` int(11) NOT NULL AUTO_INCREMENT,
                `request_uri` text COLLATE utf8_unicode_ci,
				`title` varchar(160) COLLATE utf8_unicode_ci NULL,
                `time_start` datetime DEFAULT NULL,
                `time_end` datetime DEFAULT NULL,
                `time_sec_total` int(11) DEFAULT NULL,
				`user_id` int(11) DEFAULT NULL,
				`user_info`text COLLATE utf8_unicode_ci,
				`ip` varchar(160) COLLATE utf8_unicode_ci NULL,
                `is_visit_end` tinyint(1) DEFAULT NULL,
                `other_data` text COLLATE utf8_unicode_ci,
				PRIMARY KEY  (idvisited_pages)
			) $charset_collate COMMENT='Activity Time Plugin';";
		
            dbDelta( $sql );

            $table_name = $wpdb->prefix . 'actt_user_sessions';

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				`iduser_sessions` int(11) NOT NULL AUTO_INCREMENT,
                `time_start` datetime DEFAULT NULL,
                `time_end` datetime DEFAULT NULL,
                `time_sec_total` int(11) DEFAULT NULL,
				`user_id` int(11) DEFAULT NULL,
				`user_info`text COLLATE utf8_unicode_ci,
				`ip` varchar(160) COLLATE utf8_unicode_ci NULL,
                `is_visit_end` tinyint(1) DEFAULT NULL,
                `other_data` text COLLATE utf8_unicode_ci,
				PRIMARY KEY  (iduser_sessions)
			) $charset_collate;";
		
            dbDelta( $sql );

            $table_name = $wpdb->prefix . 'actt_report';

			$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
                `idreport` int(11) NOT NULL AUTO_INCREMENT,
                `date` datetime DEFAULT NULL,
                `report_name` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
                `description` text COLLATE utf8_unicode_ci,
                `report_email` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
                `scheduling_period` int(11) DEFAULT NULL COMMENT 'days',
                `format` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                `by_user` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
                `by_ip` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
                `request_uri` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
                `date_start` datetime DEFAULT NULL,
                `date_end` datetime DEFAULT NULL,
                `date_sent` datetime DEFAULT NULL,
                `other_data` text COLLATE utf8_unicode_ci,
                PRIMARY KEY  (idreport)
              ) $charset_collate;";
		
            dbDelta( $sql );

			update_option( 'actt_db_version', "1" );
		}
	
		update_option( 'actt_db_version', self::$db_version );
	}

}
