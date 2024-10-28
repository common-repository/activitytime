<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://swit.hr/
 * @since      1.0.0
 *
 * @package    Activitytime
 * @subpackage Activitytime/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Activitytime
 * @subpackage Activitytime/admin
 * @author     SWIT <sandi@swit.hr>
 */
class Activitytime_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Activitytime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Activitytime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/activitytime-admin.css', array(), $this->version, 'all' );

        wp_enqueue_style( 'wp-color-picker' );

		wp_register_style('activitytime_basic_wrapper', plugin_dir_url( __FILE__ ).'css/basic.css', false, '1.0.0' );

		wp_register_style( 'dataTables-select', plugin_dir_url( __FILE__ ) . 'css/select.dataTables.min.css' );

		wp_register_style( 'font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', false, '1.0.0' );
		
		wp_enqueue_style( 'font-awesome' );

		wp_enqueue_style( 'activitytime-style', plugin_dir_url( __FILE__ ) . 'css/style.css', false, '1.0.0' );

        if(is_rtl()){
           wp_enqueue_style( 'activitytime-rtl',  plugin_dir_url( __FILE__ ) . 'css/style_rtl.css');
		}

                
        if(get_option('actt_checkbox_enable_winterlock_dash_styles') > 0){
            wp_enqueue_style('winter-activity-admin-ui-dashboard', plugin_dir_url( __FILE__ ) . 'css/frontend-dashboard.css', array(), '1.1' );
        }

        wp_register_style( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/jquery-confirm/jquery-confirm.min.css' );

		wp_enqueue_style( 'jquery-confirm' );

        //wp_enqueue_style( 'contact-admin', plugin_dir_url( __FILE__ ) . 'css/contact-admin.css', false, '1.0.0'  );

        wp_enqueue_style( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/chartjs/Chart.min.css', false, '1.0.0'  );

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
		 * defined in Activitytime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Activitytime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/activitytime-admin.js', array( 'jquery' ), $this->version, false );

		wp_dequeue_script('datatables');
		wp_deregister_script('datatables');

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/activitytime-admin.js', array( 'jquery' ), $this->version, false );

        wp_register_script( 'datatables', plugin_dir_url( __FILE__ ) . 'js/datatables.min.js', array( 'jquery' ), false, false );
        wp_register_script( 'dataTables-responsive', plugin_dir_url( __FILE__ ) . 'js/dataTables.responsive.js', array( 'jquery' ), false, false );
        wp_register_script( 'dataTables-select', plugin_dir_url( __FILE__ ) . 'js/dataTables.select.min.js', array( 'jquery' ), false, false );

        wp_register_script( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/jquery-confirm/jquery-confirm.min.js' );

		if(isset($_GET['page']) && $_GET['page'] == 'activitytime')
		{
			wp_enqueue_script( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/chartjs/Chart.bundle.min.js', array(), $this->version, false );
		}
        //wp_enqueue_script( 'chartjs-utils', plugin_dir_url( __FILE__ ) . 'js/chartjs/chartjs_utils.js', array(), $this->version, false );

		wp_enqueue_script( 'jquery-confirm' );

        wp_enqueue_script( 'wp-color-picker');

    }

    /**
	 * Admin Page Display
	 */
	public function admin_page_display() {
		global $Winter_MVC, $submenu, $menu;

		$page = '';
        $function = '';

		if(isset($_GET['page']))$page = wmvc_xss_clean($_GET['page']);
		if(isset($_GET['function']))$function = wmvc_xss_clean($_GET['function']);

		$Winter_MVC = new MVC_Loader(plugin_dir_path( __FILE__ ).'../');
		$Winter_MVC->load_helper('basic');
        $Winter_MVC->load_controller($page, $function, array());
        
        if(get_option( 'activitytime-menuitems' ) === FALSE)
        {
            add_option( 'activitytime-menuitems', $menu);
            add_option( 'activitytime-submenuitems', $submenu);
        }
        else
        {
            update_option( 'activitytime-menuitems', $menu);
            update_option( 'activitytime-submenuitems', $submenu);
        }
	}
    
    /**
     * To add Plugin Menu and Settings page
     */
    public function plugin_menu() {

        ob_start();

        // Show menu only for approved admins
        $allowed_admins = get_option('actt_allowed_admins');
        if(wmvc_user_in_role('administrator') || wmvc_user_in_role('super-admin'))
        if(is_array($allowed_admins) && count($allowed_admins) > 0)
        {
            if(!in_array(get_current_user_id(), $allowed_admins))
                return;
        }

        require_once ACTIVITYTIME_PATH . 'vendor/boo-settings-helper/class-boo-settings-helper.php';

        $users_admins = get_users([ 'role__in' => [ 'administrator', 'super-admin' ] ]);
        $users_prepare = array();
        foreach($users_admins as $row)
        {
            $users_prepare[$row->ID] = $row->display_name;
        }

        $roles_prepare = array();
        $roles_prepare_all = array();
        $all_roles = wmvc_roles_array();

        $roles_prepare_all['guest'] = 'guest, Not logged users';

        foreach($all_roles as $row)
        {
            $roles_prepare_all[$row['role']] = $row['role'].', '.$row['name'];

            if($row['role'] == 'administrator')continue;

            $roles_prepare[$row['role']] = $row['role'].', '.$row['name'];
        }

        add_menu_page(__('Activity Time','activitytime'), __('Activity Time','activitytime'), 
            'manage_options', 'activitytime', array($this, 'admin_page_display'),
            //plugin_dir_url( __FILE__ ) . 'resources/logo.png',
            'dashicons-clock',
            30 );
        
        add_submenu_page('activitytime', 
            __('Analytic Graphs','activitytime'), 
            __('Analytic Graphs','activitytime'),
            'manage_options', 'activitytime', array($this, 'admin_page_display'));

        add_submenu_page('activitytime', 
                        __('Current Active','activitytime'), 
                        __('Current Active','activitytime'),
                        'manage_options', 'actt_current_active', array($this, 'admin_page_display'));
        
                        
        add_submenu_page('activitytime', 
                        __('By Post Type','activitytime'), 
                        __('By Post Type','activitytime'),
                        'manage_options', 'actt_time_by_postacc', array($this, 'admin_page_display'));

        add_submenu_page('activitytime', 
                        __('Time per Visit','activitytime'), 
                        __('Time per Visit','activitytime'),
                        'manage_options', 'actt_time_per_page', array($this, 'admin_page_display'));


        add_submenu_page('activitytime', 
                        __('Time on page','activitytime'), 
                        __('Time on page','activitytime'),
                        'manage_options', 'actt_time_per_pageacc', array($this, 'admin_page_display'));
        
        add_submenu_page('activitytime', 
                        __('Sessions activity','activitytime'), 
                        __('Sessions activity','activitytime'),
                        'manage_options', 'actt_sessions', array($this, 'admin_page_display'));

        add_submenu_page('activitytime', 
                        __('Related plugins','activitytime'), 
                        __('Related plugins','activitytime'),
                        'manage_options', 'actt_related', array($this, 'admin_page_display'));

        /*
        add_submenu_page('activitytime', 
                        __('Reports','activitytime'), 
                        __('Reports','activitytime'),
                        'manage_options', 'actt_reports', array($this, 'admin_page_display'));
        */

        /*
        add_submenu_page('activitytime', 
                        __('Contact Us','activitytime'), 
                        __('Contact Us','activitytime'),
                        'manage_options', 'actt_contact_us', array($this, 'admin_page_display'));
        */

        $general_class = 'actt-pro';

        $winteractivitytime_settings = array(
            'tabs'     => true,
            'prefix'   => 'actt_',
            'menu'     => array(
                'slug'       => 'actt_settings',
                'page_title' => __( 'Activity Time Settings', 'activitytime' ),
                'menu_title' => __( 'Settings ', 'activitytime' ),
                'parent'     => 'activitytime',
                'submenu'    => true
            ),
            'sections' => array(
                //General Section
                array(
                    'id'    => 'actt_general_section',
                    'title' => __( 'General Section', 'activitytime' ),
                    'desc'  => __( 'These are general settings', 'activitytime' ),
                ),                
            ),
            'fields'   => array(
                // fields for General section
                'actt_general_section' => array(
                    array(
                        'id'    => 'log_days',
                        'label' => __( 'Delete time logs after', 'activitytime' ),
                        'desc'  => __( 'Days', 'activitytime' ),
                        'sanitize_callback' => 'absint',
                        'class'	=> '',
                    ),
                    /*
                    array(
                        'id'    => 'timeout_mins',
                        'label' => __( 'Timeout mins', 'activitytime' ),
                        'desc'  => __( 'Will close session if browser closed', 'activitytime' ),
                        'sanitize_callback' => 'absint',
                        'class'	=> '',
                    ),
                    */
                    array(
                        'id'    => 'allowed_admins',
                        'label' => __( 'Only this admins allowed', 'activitytime' ),
                        'desc'  => __( 'Allow only this specific admins to see monitoring, if unchecked then all', 'activitytime' ),
                        'type'  => 'multicheck',
                        'options' => $users_prepare
                    ),
                    /*
                    array(
                        'id'    => 'allowed_roles',
                        'label' => __( 'Allow ActivityTime access also to roles', 'activitytime' ),
                        'desc'  => __( 'Except administrators, also this roles will be able to access, if unchecked then no other', 'activitytime' ),
                        'type'  => 'multicheck',
                        'options' => $roles_prepare
                    ),
                    */
                    array(
                        'id'    => 'monitor_roles',
                        'label' => __( 'Log only this specific roles', 'activitytime' ),
                        'desc'  => __( 'Only this specific roles will be monitored, if unchecked then all', 'activitytime' ),
                        'type'  => 'multicheck',
                        'options' => $roles_prepare_all
                    ),
                    /*
                    array(
                        'id'    => 'checkbox_disable_hints',
                        'label' => __( 'Disable hints', 'activitytime' ),
                        'desc'  => __( 'Will hide questions and video guides in dashboard', 'activitytime' ),
                        'type'  => 'checkbox',
                        'class'	=> '',
                    ),
                    array(
                        'id'    => 'checkbox_disable_dashwidgets',
                        'label' => __( 'Disable Dash Widgets', 'activitytime' ),
                        'desc'  => __( 'Will hide all ActivityTime widgets visible when you logged in to Wordpress dashboard', 'activitytime' ),
                        'type'  => 'checkbox',
                        'class'	=> '',
                    ),
                    */
                ),
            )

        );

        new Boo_Settings_Helper2( $winteractivitytime_settings );

    }

}
