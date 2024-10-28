<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://swit.hr/
 * @since      1.0.0
 *
 * @package    Activitytime
 * @subpackage Activitytime/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Activitytime
 * @subpackage Activitytime/includes
 * @author     SWIT <sandi@swit.hr>
 */
class Activitytime {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Activitytime_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ACTIVITYTIME_VERSION' ) ) {
			$this->version = ACTIVITYTIME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'activitytime';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
        $this->define_public_hooks();
        
        $this->define_plugins_upgrade_hooks();
        $this->define_shortcode_hooks();
        $this->define_widget_hooks();
        $this->define_logging_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Activitytime_Loader. Orchestrates the hooks of the plugin.
	 * - Activitytime_i18n. Defines internationalization functionality.
	 * - Activitytime_Admin. Defines all hooks for the admin area.
	 * - Activitytime_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-activitytime-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-activitytime-i18n.php';
        
        /**
		 * Contains helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helper-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-activitytime-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-activitytime-public.php';

		/**
		 * The class responsible for defining all actions for logging
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-activitytime-tracker.php';

        // Load Winter MVC core
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/Winter_MVC/init.php';

		$this->loader = new Activitytime_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Activitytime_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Activitytime_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Activitytime_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Adding Plugin Admin Menu
		 */
		$this->loader->add_action(
			'admin_menu',
			$plugin_admin,
			'plugin_menu'
        );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Activitytime_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Activitytime_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
    }
    
    public function define_plugins_upgrade_hooks()
	{
		require_once  plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-activitytime-activator.php';

        $this->loader->add_action( 'plugins_loaded', 'Activitytime_Activator', 'plugins_loaded' );
        
        $this->loader->add_action(
			'wp_ajax_nopriv_activitytime_action',
			$this,
			'activitytime_action'
        );
        
        $this->loader->add_action(
			'wp_ajax_activitytime_action',
			$this,
			'activitytime_action'
        );
        
        $this->loader->add_action(
			'wp_ajax_activitytime_mvc_action',
			$this,
			'activitytime_mvc_action'
        );
    }
    
    public function define_shortcode_hooks()
    {
        require(plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/actt_time_page.php');

    }

    public function define_widget_hooks()
    {
        //require(plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/xxx.php');

    }

	/**
	 * Defining all action and filter hooks for logging
	 */
	public function define_logging_hooks() {

		$logging_hooks = new Activitytime_Tracker( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_loaded', $logging_hooks, 'activity_wp_loaded' );

        $this->loader->add_action( 'wp_head', $logging_hooks, 'init' );
        $this->loader->add_action( 'admin_head', $logging_hooks, 'init' );

        $this->loader->add_action( 'wp_head', $this, 'activitytime_tracker' );
        $this->loader->add_action( 'admin_head', $this, 'activitytime_tracker' );


		add_action( 'rest_api_init', function() {
			register_rest_route( 'activitytime/v1', '/action', [
			'methods'  => 'POST',
			'callback' => [ $this, 'activitytime_action' ],
			] );
		} );
    }
    
    public function activitytime_tracker() {
            ?>
                <script>
                    
                    activitytime_callajax();

                    // call ajax each 5 sec

                    var activitytime_tracker;

                    activitytime_tracker = setInterval(activitytime_trackerFunc, 30000);

                    function activitytime_trackerFunc() {
                        activitytime_callajax();
                    }

                    function activitytime_callajax()
                    {
                        // call ajax
                        var xhttp = new XMLHttpRequest();
						
						/*
                        xhttp.open("POST", "<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>", true);
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send("uri=\"<?php echo urlencode(actt_get_uri()); ?>\"&page=activitytime&function=ajax_time_end&action=activitytime_action"); 
						*/
                        xhttp.open("POST", "<?php echo esc_url(get_rest_url(null, 'activitytime/v1/action'));?>", true);
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send("uri=\"<?php echo urlencode(actt_get_uri()); ?>\""); 
						
                    }

                </script>
            <?php
     }

    /**
	 * AJAX
    */
    
    public function activitytime_mvc_action()
    {



        global $Winter_MVC;

		$page = '';
		$function = '';

		if(isset($_POST['page']))$page = sanitize_text_field($_POST['page']);
		if(isset($_POST['function']))$function = sanitize_text_field($_POST['function']);

		$Winter_MVC = new MVC_Loader(plugin_dir_path( __FILE__ ).'../');
		$Winter_MVC->load_helper('basic');
		$Winter_MVC->load_controller($page, $function, array());
    }

	public function activitytime_action()
	{
        if(!isset($_POST['uri']))exit('ERROR');
        $_POST['uri'] = substr($_POST['uri'], 2, -2);

        $_POST['uri'] = urldecode(esc_attr($_POST['uri']));
        $uri_prepared = str_replace('&amp;', '&', $_POST['uri']);
        
		if(!defined('SAVEQUERIES'))
			define('SAVEQUERIES', true);
		add_action('admin_footer', 'plg_name_show_debug_queries', PHP_INT_MAX);
		add_action('wp_footer', 'plg_name_show_debug_queries', PHP_INT_MAX);

        global $wpdb;


		$wmvc_xss_clean_uri = function($uri_prepared) {
			
			$uri_prepared = str_replace(array('SELECT ', '"',"'",'%2527','%27','UPDATE ','SLEEP('), '', $uri_prepared);

			$dangerous_schemes = ['javascript:', 'data:', 'vbscript:', 'alert(','OR 1=1'];
			foreach ($dangerous_schemes as $scheme) {
				if (stripos($uri_prepared, $scheme) === 0) {
					return '';
				}
			}
		
			return $uri_prepared;
		};
		
        // regular update time_end
        $query = 'UPDATE '.$wpdb->prefix.'actt_visited_pages SET time_end=\''.current_time( 'mysql' ).
                 '\' WHERE request_uri = \''.esc_sql($wmvc_xss_clean_uri(sanitize_text_field(wmvc_xss_clean($uri_prepared)))).'\'';
    
		if(!empty(get_current_user_id()))
        {
            $query .= ' AND user_id='.get_current_user_id();
        }
        else
        {
            $query .= ' AND ip=\''.esc_sql(sanitize_text_field(actt_get_the_user_ip())).'\'';
        }

        $query .= ' AND is_visit_end = 0';

        //echo $query;

        $wpdb->query($query);

        // delete all old visits

        $query = 'DELETE FROM '.$wpdb->prefix.'actt_visited_pages '.
        ' WHERE time_end = \'0000-00-00 00:00:00\' ';

        // time calculation, if older then half hour and not refreshed

        $query .= ' AND time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -10*60).'\'';

        //echo $query;

        $wpdb->query($query);

        // update is_visit_end

        $query = 'UPDATE '.$wpdb->prefix.'actt_visited_pages SET is_visit_end=1, time_sec_total = '.
                 'TIME_TO_SEC(TIMEDIFF(time_end, time_start))'.   
                 ' WHERE is_visit_end = 0 AND time_end != \'0000-00-00 00:00:00\' ';

        // time calculation, if time_end and time_start older then 5min

        $query .= ' AND time_end < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -5*60).'\'';
        $query .= ' AND time_start < \''.date("Y-m-d H:i:s", current_time( 'timestamp' ) -5*60).'\'';

        //echo $query;

        $wpdb->query($query);
        

		$total_time=0;
		if (is_array($wpdb->queries)) foreach ($wpdb->queries as $key => $q) {
			list($query, $elapsed, $debug) = $q;
			$time = number_format(($elapsed * 1000), 3);
			$count = $key + 1;
			$total_time += $elapsed;

			$color='black';
			if($time > 50)$color='red';

			echo "
			<div style=\"position: relative; z-index: 9999    ; background: $color; color: white; padding:10px\">
				$count - Query: $query <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Time: $time ms
			</div>";
		}
		echo "
		<div style=\"position: relative; z-index: 9999    ; background: black; color: white; padding:10px\">
			Total Queries: " . count($wpdb->queries) . "<br>Total Time: " . number_format(($total_time * 1000), 3) . " ms
		</div>";


		if(isset($_SERVER["REQUEST_TIME_FLOAT"]))
			echo 'Execution_time: '.number_format((microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"])*1000,3).' ms';


       // exit('SUCCESS');
	}

}
