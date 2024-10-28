<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Actt_related extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        $plugins_list = 
            array(
                'winterlock', 
                'elementinvader', 
                'sweet-energy-efficiency'
            );

        $this->data['plugins_list'] = array();

        foreach($plugins_list as $slug)
        {
            $request = wp_remote_get('https://api.wordpress.org/plugins/info/1.0/'.$slug.'.json'); 

            if(isset($request['body']))
                $this->data['plugins_list'][$slug] = json_decode($request['body']);
        }

        // Load view
        $this->load->view('actt_related/index', $this->data);
    }
    
}
