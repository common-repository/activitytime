<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    die;
}
/**
 * Functionality for Tracking, saving data about visited page
 *
 *
 * @package    Activitytime_Tracker
 * @subpackage Activitytime_Tracker/includes
 * @author     SWIT <sandi@swit.hr>
 */
if ( !class_exists( 'Activitytime_Tracker' ) ) {
    class Activitytime_Tracker
    {
        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string $plugin_name The ID of this plugin.
         */
        private  $plugin_name ;
        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string $version The current version of this plugin.
         */
        private  $version ;
        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         *
         * @param      string $plugin_name The name of the plugin.
         * @param      string $version The version of this plugin.
         */
        public function __construct( $plugin_name, $version )
        {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }
        
        /**
         * hooked into 'init' action hook
         */
        public function init()
        {
            $this->activity_log_request();
        }

        public function activity_wp_loaded()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'actt_user_sessions';

            if(isset($_GET['action']))
            if($_GET['action'] == 'logout')
            {
                $query = 'UPDATE '.esc_sql($table_name).' SET is_visit_end=1, time_sec_total = '.
                'TIME_TO_SEC(TIMEDIFF(time_end, time_start))'.   
                ' WHERE time_end != \'0000-00-00 00:00:00\' AND user_id='.get_current_user_id();

                //echo $query;

                $wpdb->query($query);
            }
        }
        
        public function activity_log_request()
        {
            // Insert into DB table actt_visited_pages with fastest possible way
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'actt_visited_pages';

            // check if log for this specific user type/role

            // TODO: problem, if user using server side caching this is not executed and no saved logs!, add this to ajax function also

            if(get_site_option( 'actt_monitor_roles' ) !== FALSE)
            {
                $monitor_roles = get_site_option( 'actt_monitor_roles' );

                //if(isset($_GET['test']))
                //  var_dump($monitor_roles);


                if(count($monitor_roles) > 0)
                {
                    $skip_log = TRUE;

                    foreach($monitor_roles as $key=>$role)
                    {
                        if($key == 'guest')
                        {
                            if(empty(get_current_user_id()))
                            {
                                $skip_log = FALSE;
                            }
                        }
                        elseif(wmvc_user_in_role($key))
                        {
                            $skip_log = FALSE;
                        }
                    }

                    if($skip_log == TRUE)
                    {
                        //dump($monitor_roles);
                        //exit();
                        return;
                    }
                }
            }

            $insert_array = array();

            $insert_array['request_uri'] = actt_get_uri();
            $insert_array['title'] = actt_get_title();
            $insert_array['time_start'] = current_time( 'mysql' );
            $insert_array['time_end'] = '';
            $insert_array['user_id'] = get_current_user_id();
            $insert_array['user_info'] = actt_user_info(get_current_user_id());
            $insert_array['ip'] = actt_get_the_user_ip();
            $insert_array['is_visit_end'] = 0;
            $insert_array['other_data'] = '';

            //dump($insert_array);
            //exit();

            // visit log
            if(strpos($insert_array['request_uri'], 'ajax') === FALSE && 
               strpos($insert_array['request_uri'], 'json') === FALSE && 
               strpos($insert_array['request_uri'], 'cron') === FALSE &&
               strpos($insert_array['request_uri'], 'actt') === FALSE &&
               strpos($insert_array['request_uri'], 'activitytime') === FALSE &&
               count($_POST) == 0)
            {
                $query = 'SELECT * FROM '.esc_sql($table_name).' WHERE request_uri = \''.esc_sql(actt_get_uri()).'\'';

                if(!empty(get_current_user_id()))
                {
                    $query .= ' AND user_id='.get_current_user_id();
                }
                else
                {
                    $query .= ' AND ip=\''.actt_get_the_user_ip().'\'';
                }

                $query .= ' AND is_visit_end = 0';

                $check_exists = $wpdb->get_row($query);

                if(empty($check_exists))
                {
                    $wpdb->insert( $table_name, $insert_array );
                    $id = $wpdb->insert_id;
                }
            }

            // sessions log
            if(!empty(get_current_user_id()))
            {
                $table_name = $wpdb->prefix . 'actt_user_sessions';

                unset($insert_array['request_uri'], $insert_array['title']);

                // update is_visit_end

                $query = 'UPDATE '.esc_sql($table_name).' SET is_visit_end=1, time_sec_total = '.
                        'TIME_TO_SEC(TIMEDIFF(time_end, time_start))'.   
                        ' WHERE is_visit_end = 0 AND time_end != \'0000-00-00 00:00:00\' ';

                // time calculation, if time_end and time_start older then 5min

                $query .= ' AND time_end < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -5*60).'\'';
                $query .= ' AND time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -5*60).'\'';

                //echo $query;

                $wpdb->query($query);

                $query = 'SELECT * FROM '.esc_sql($table_name).' WHERE is_visit_end = 0 ';
                $query .= ' AND user_id='.get_current_user_id();

                $check_exists = $wpdb->get_row($query);

                if(empty($check_exists))
                {
                    // insert
                    $wpdb->insert( $table_name, $insert_array );
                    $id = $wpdb->insert_id;
                }
                else
                {
                    // update

                    $query = 'UPDATE '.esc_sql($table_name).' SET time_end=\''.current_time( 'mysql' ).
                    '\' WHERE is_visit_end = 0 ';
   
                    $query .= ' AND user_id='.get_current_user_id();
   
                    //echo $query;
   
                    $wpdb->query($query);

                    // delete all old sessions

                    $query = 'DELETE FROM '.esc_sql($table_name).' '.
                    ' WHERE time_end = \'0000-00-00 00:00:00\' ';

                    // time calculation, if older then half hour and not refreshed

                    $query .= ' AND time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -10*60).'\'';

                    //echo $query;

                    $wpdb->query($query);
                }

            }

            if(get_site_option( 'actt_log_days' ) !== FALSE)
            {
                $table_name = $wpdb->prefix . 'actt_user_sessions';

                $query = 'DELETE FROM '.esc_sql($table_name).' '.
                ' WHERE time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) - 24*60*60*intval(get_site_option( 'actt_log_days' ))).'\'';
                $wpdb->query($query);
                $table_name = $wpdb->prefix . 'actt_visited_pages';

                $query = 'DELETE FROM '.esc_sql($table_name).' '.
                ' WHERE time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) - 24*60*60*intval(get_site_option( 'actt_log_days' ))).'\'';
                $wpdb->query($query);
            }

            return FALSE;
        }
        
    
    }
}