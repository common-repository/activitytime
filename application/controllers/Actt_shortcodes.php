<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

class Actt_shortcodes extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
    }

	public function actt_time_page($atts, $content)
	{
        $this->data['atts'] = $atts;
        $this->data['content'] = $content;

        // configuration
        $controller = 'visitedpagesacc';
        $this->load->model($controller.'_m');

        $where = array('request_uri' => actt_get_uri());
        if(count($where)>0)
            $this->db->where($where);

        $this->data['db_data'] = $this->{$controller.'_m'}->get_pagination_lang($this->data['atts']['limit'], 0, array('time_end !='=>'0000-00-00 00:00:00'));

        // Load view
        if(!empty($this->data['db_data']))
            return $this->load->view('acct_shortcodes/actt_time_page', $this->data, FALSE);
        return '';
    }
    
}
