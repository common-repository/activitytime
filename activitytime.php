<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://swit.hr/
 * @since             1.0.0
 * @package           Activitytime
 *
 * @wordpress-plugin
 * Plugin Name:       WP Sessions Time Monitoring Full Automatic
 * Plugin URI:        https://swit.hr/
 * Description:       Plugin will accurately measure all activity time per page and user like working time, reading time, watching time, sessions time for specific user on specific page.
 * Version:           1.1.1
 * Author:            SWIT
 * Author URI:        https://swit.hr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       activitytime
 * Domain Path:       /languages
 * 
 * @fs_premium_only /premium_functions.php
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ACTIVITYTIME_VERSION', '1.1.1' );
define( 'ACTIVITYTIME_NAME', 'actt' );
define( 'ACTIVITYTIME_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACTIVITYTIME_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activitytime-activator.php
 */
function activate_activitytime() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activitytime-activator.php';
	Activitytime_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-activitytime-deactivator.php
 */
function deactivate_activitytime() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activitytime-deactivator.php';
	Activitytime_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_activitytime' );
register_deactivation_hook( __FILE__, 'deactivate_activitytime' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-activitytime.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

if ( ! function_exists( 'activitytime_fms' ) ) {
    // Create a helper function for easy SDK access.
    function activitytime_fms() {
        global $activitytime_fms;

        if ( ! isset( $activitytime_fms ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $activitytime_fms = fs_dynamic_init( array(
                'id'                  => '7430',
                'slug'                => 'activitytime',
                'type'                => 'plugin',
                'public_key'          => 'pk_3be2dd217906a35b5ac0f3f5fe678',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'activitytime',
                ),
                'anonymous_mode' => true,
            ) );
        }

        return $activitytime_fms;
    }

    // Init Freemius.
    activitytime_fms();
    // Signal that SDK was initiated.
    do_action( 'activitytime_fms_loaded' );
    
}


function run_activitytime() {

	$plugin = new Activitytime();
	$plugin->run();

}
run_activitytime();




add_action( 'after_setup_theme', function () {
    activity_time_csv_url();
} );

function activity_time_csv_url()
{
    if(!isset($_GET['url_export']))return;
    
    ob_clean();

    global $wpdb;

    $table_name = $wpdb->prefix . 'actt_visited_pages';

    $table_users_name = $wpdb->prefix . 'users';

    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

    if(defined('CUSTOM_USER_TABLE'))
        $table_users_name = '`'.CUSTOM_USER_TABLE.'`';
    
    $query  = 'SELECT SUM(time_sec_total) as total_time, user_info, request_uri, title, user_id, user_email FROM '.esc_sql($table_name).' LEFT JOIN '.esc_sql($table_users_name).' ON '.esc_sql($table_name).'.user_id = '.$table_users_name.'.ID WHERE is_visit_end = 1 ';
    $query .= 'GROUP BY title ORDER BY total_time DESC';

    $data = $wpdb->get_results($query);


    $gmt_offset = get_option('gmt_offset');

    foreach($data as $key=>$row)
    {
        $min = intval($row->total_time / 60);

        $row->total_min = $min . ':' . str_pad(($row->total_time % 60), 2, '0', STR_PAD_LEFT);
        $row->user_info = strip_tags($row->user_info);
        $row->url = $base_url.$row->request_uri;

        $data[$key] = $row;
    }

    $skip_cols = array('other_data');
    
    if(!function_exists('actt_prepare_export'))
        exit('Missing addon');

    $print_data = actt_prepare_export($data, $skip_cols);

    header('Content-Type: application/csv');
    header("Content-Length:".strlen($print_data));
    header("Content-Disposition: attachment; filename=csv_most_pages_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

    echo $print_data;
    
    exit();
}

