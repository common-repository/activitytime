<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://swit.hr/
 * @since      1.0.0
 *
 * @package    Activitytime
 * @subpackage Activitytime/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Activitytime
 * @subpackage Activitytime/public
 * @author     SWIT <sandi@swit.hr>
 */
class Activitytime_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Activitytime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Activitytime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/activitytime-public.css', array(), $this->version, 'all' );

		
        wp_register_style( 'jquery-confirm', ACTIVITYTIME_URL . 'admin/js/jquery-confirm/jquery-confirm.min.css' );
		wp_enqueue_style( 'jquery-confirm' );

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
		 * defined in Activitytime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Activitytime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'actt_activation_link' => admin_url( 'admin.php?page=activitytime-addons' ),
            'text' =>array(
				'activation_popup_title' =>  __("Your version doesn't support this functionality, please upgrade",'activitytime'),
				'activation_popup_content' => esc_js (__('We constantly maintain compatibility and improving this plugin for living, please support us and purchase, we provide very reasonable prices and will always do our best to help you!','activitytime')),
			),
        );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/activitytime-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'actt_script_parameters', $params);

        wp_register_script( 'jquery-confirm', ACTIVITYTIME_URL . 'admin/js/jquery-confirm/jquery-confirm.min.js' );
		wp_enqueue_script( 'jquery-confirm' );
	}

}
