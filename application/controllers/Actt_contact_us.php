<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Actt_contact_us extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        // Load view
        $this->load->view('acct_contact/index', $this->data);
    }
    
}
