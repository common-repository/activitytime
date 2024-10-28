<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

class Actt_widgets extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
    }

	public function see_graph($params)
	{
        $this->load->model('graph_m');

        $this->data['atts'] = $params;

        if(isset($this->data['atts']['idgraph']))
            $this->data['atts']['id'] = $this->data['atts']['idgraph'];

        if(!empty($this->data['atts']['id']) && is_numeric($this->data['atts']['id']))
            $this->data['db_data'] = $this->graph_m->get($this->data['atts']['id'], TRUE);

        if(isset($this->data['db_data']) && is_object($this->data['db_data']))
            return $this->load->view('layouts/basic', $this->data, FALSE);

        return '';
    }
    
}
