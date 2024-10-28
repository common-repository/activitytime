<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Visitedpagesacc_m extends Winter_MVC_Model {

	public $_table_name = 'actt_visited_pages';
	public $_order_by = 'idvisited_pages DESC';
    public $_primary_key = 'idvisited_pages';
    public $_own_columns = array();
    public $_timestamps = TRUE;
    protected $_primary_filter = 'intval';

    public $form_admin = array();

    public $fields_list = null;
    
	public function __construct(){
        parent::__construct();
 
        $this->form_admin = array(
            'listing_id' => array('field'=>'listing_id', 'label'=>__('Listing', 'sw_win'), 'design'=>'dropdown_listing', 'rules'=>'trim|callback__calendar_exists|required')
        );
	}

    /* [START] For dinamic data table */
    
    public function get_available_fields()
    {      
        $fields = $this->db->list_fields($this->_table_name);

        return $fields;
    }
    
    public function total_lang($where = array())
    {
        $this->db->select('COUNT(*) as total_count');
        $this->db->from($this->_table_name);
        $this->db->where($where);
        $this->db->group_by('request_uri');
        $this->db->group_by('user_id');
        $this->db->order_by($this->_order_by);
        
        $query = $this->db->get();

        $res = $this->db->results();

        if(isset($res[0]->total_count))
            return $res[0]->total_count;

        return 0;
    }
    
    public function get_pagination_lang($limit, $offset, $where = array())
    {
        // check if table uwp_usermeta exists and join
        global $wpdb;
        $table_name = $wpdb->base_prefix.'uwp_usermeta';
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
        
        $uwp_table_exists = false;
        $uwp_select = '';
        if ( $wpdb->get_var( $query ) == $table_name ) {
            $uwp_table_exists = true;
            $uwp_select = ', uwp_country, your_payment_option, mobile_number';
        }

        $this->db->select($this->_table_name.'.*, SUM(time_sec_total) as time_sec_total_acc, MIN(time_start) as time_start_min, MAX(time_end) as time_end_max, MIN(is_visit_end) as is_visit_end_min, SEC_TO_TIME(SUM(time_sec_total)) as time_h '.esc_sql($uwp_select));
        $this->db->from($this->_table_name);

        if ($uwp_table_exists) {
            $this->db->join($table_name.' ON '.$this->_table_name.'.user_id = '.$table_name.'.user_id', TRUE, 'LEFT');
        }

        $this->db->where($where);
        $this->db->group_by('request_uri');
        $this->db->group_by('user_id');
        $this->db->limit($limit);
        $this->db->offset($offset);
        $this->db->order_by($this->_order_by);
        
        $query = $this->db->get();

        if ($this->db->num_rows() > 0)
            return $this->db->results();
        
        return array();
    }
    
    public function check_deletable($id)
    {
        return true;
    }
    
    
    /* [END] For dinamic data table */





}













?>