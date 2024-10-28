<?php

add_shortcode('actt_time_page', 'register_actt_time_page_shortcode');
function register_actt_time_page_shortcode($atts, $content){
    $atts = shortcode_atts(array(
        'id'=>NULL,
        'limit'=>5,
    ), $atts);
    
    global $Winter_MVC;

    $page = 'actt_shortcodes';
    $function = 'actt_time_page';

    static $widget_id;

    $widget_id++;

    $atts['widget_id'] = 'actt_'.$widget_id;

    $Winter_MVC = new MVC_Loader(plugin_dir_path( __FILE__ ).'../');
    $Winter_MVC->load_helper('basic');
    $output = $Winter_MVC->load_controller($page, $function, array($atts, $content));
    
    return $output;
}

?>