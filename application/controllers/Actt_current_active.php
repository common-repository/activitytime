<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Actt_current_active extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{

        // Load view
        $this->load->view('actt_current_active/index', $this->data);
    }

	// Called from ajax
	// json for datatables
	public function datatable()
	{
        //$this->enable_error_reporting();
        remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

        // configuration
        $columns = array('idvisited_pages', 'request_uri', 'title', 'user_info', 'time_start', 'time_end');
        $controller = 'visitedpages';
        
        // Fetch parameters
        $parameters = $this->input->post();
        $draw = $this->input->post_get('draw');
        $start = $this->input->post_get('start');
        $length = $this->input->post_get('length');
		$search = $this->input->post_get('search');

        if(isset($search['value']))
			$parameters['searck_tag'] = $search['value'];
			
		$this->load->model($controller.'_m');

        $recordsTotal = $this->{$controller.'_m'}->total_lang(array('is_visit_end = 0'=>NULL, 'time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        actt_prepare_search_query_GET($columns, $controller.'_m');
        $recordsFiltered = $this->{$controller.'_m'}->total_lang(array('is_visit_end = 0'=>NULL, 'time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        actt_prepare_search_query_GET($columns, $controller.'_m');
        $data = $this->{$controller.'_m'}->get_pagination_lang($length, $start, array('is_visit_end = 0'=>NULL, 'time_end !='=>'0000-00-00 00:00:00'));

        $query = $this->db->last_query();

        // Add buttons
        foreach($data as $key=>$row)
        {
            foreach($columns as $val)
            {
                if(isset($row->$val))
                {
                    
                }
                elseif(isset($row->json_object))
                {
                    $json = json_decode($row->json_object);
                    if(isset($json->$val))
                    {
                        $row->$val = $json->$val;
                    }
                    else
                    {
                        $row->$val = '-';
                    }
                }
                else
                {
                    $row->$val = '-';
                }
            }

            if(current_time( 'timestamp' ) - strtotime( $row->time_end ) > 60)
            {
                $row->time_end = '<span style="color:red">'.$row->time_end.'</span>';
            }

            $options = '';//btn_edit(admin_url("admin.php?page=actt_add_graph&id=".$row->{"id$controller"})).' ';

            $row->edit = $options;

            $row->checkbox = '';
        }

        //format array is optional
        $json = array(
                "parameters" => $parameters,
                "query" => $query,
                "draw" => $draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $data
                );

        //$length = strlen(json_encode($data));
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache');
        //header('Content-Type: application/json; charset=utf8');
        //header('Content-Length: '.$length);
        echo json_encode($json);
        
        exit();
    }
    
    public function bulk_remove($id = NULL, $redirect='1')
	{   
        $this->load->model('visitedpages_m');

        // Get parameters
        $visited_pages_ids = $this->input->post('visited_pages_ids');

        $json = array(
            "visited_pages_ids" => $visited_pages_ids,
            );

        foreach($visited_pages_ids as $id)
        {
            if(is_numeric($id))
                $this->visitedpages_m->delete($id);
        }

        echo json_encode($json);
        
        exit();
    }


    /*
    *
    * ? extra_user_meta=display_name or extra_user_meta=display_name,email - user meta fields
    */

    public function export_csv_current()
    {
        ob_clean();

        $controller = 'visitedpages';
            
        $this->load->model($controller.'_m');
        
        // configuration
        $field_user = sanitize_text_field($this->input->get('filter_user'));
        $where = array('is_visit_end = 0'=>NULL, 'time_end !='=>'0000-00-00 00:00:00');

        if(!empty($field_user))
            $where['(user_info LIKE "%'.esc_html($field_user).'%")'] = NULL;

        $data = $this->{$controller.'_m'}->get_pagination_lang(NULL, NULL, $where);

        $gmt_offset = get_option('gmt_offset');
        $sum_time_sec_total = 0;

        $extra_user_meta_get = sanitize_text_field($this->input->get('extra_user_meta'));
        $extra_user_meta = array();
        if($extra_user_meta_get)
            $extra_user_meta = explode(',',$extra_user_meta_get);

        foreach($data as $key=>$row)
        {
            if(empty($row->time_sec_total))
                $row->time_sec_total = (string) (strtotime($row->time_end) - strtotime($row->time_start));

            $sum_time_sec_total +=$row->time_sec_total;

            $row->user_info = strip_tags($row->user_info);
            
            $user_info = actt_get_user_data( $row->user_id );
            $row->login = '';
            $row->name = '';
            $row->first_name = '';
            $row->last_name = '';
            $row->email = '';
            $row->bio = '';
            if($extra_user_meta) {
                foreach ($extra_user_meta as $extra_meta_field) {
                    $row->$extra_meta_field = '';
                }
            }
            if($user_info) {
                $row->login = strip_tags($user_info['userdata']->user_login);
                $row->name = strip_tags($user_info['userdata']->user_nicename);
                $row->first_name = strip_tags($user_info['userdata']->first_name);
                $row->last_name = strip_tags($user_info['userdata']->last_name);
                $row->email = strip_tags($user_info['userdata']->data->user_email);
                $row->bio = strip_tags(get_the_author_meta( 'description', $row->user_id));
                if($extra_user_meta) {
                    foreach ($extra_user_meta as $extra_meta_field) {
                        if(!isset($user_info['userdata']->$extra_meta_field)) continue;
                        $row->$extra_meta_field = $user_info['userdata']->$extra_meta_field;
                    }
                }
            }

            if(!empty($row->time_sec_total)) {
                $init = $row->time_sec_total;
                $hours = floor($init / 3600);
                $minutes = floor(($init / 60) % 60);
                $seconds = $init % 60;
                $row->hms = wp_kses_post("$hours:$minutes:$seconds");
            } else {
                $row->hms = '0';
            }
            $data[$key] = $row;
        }

        if(!empty($sum_time_sec_total)) {
            $init = $sum_time_sec_total;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $hms = wp_kses_post("$hours:$minutes:$seconds");
        } else {
            $hms = '0';
        }

        $data_bottom = array (
            'idvisited_pages' => __('Summ','winter-activity-log'),
            'request_uri' => '',
            'title' => '',
            'time_start' => '',
            'time_end' => '',
            'time_sec_total' => (string) $sum_time_sec_total,
            'user_id' => '',
            'user_info' => '',
            'ip' => '',
            'is_visit_end' => '',
            'login' => '',
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'hms' => $hms
        ); 

        if($extra_user_meta) {
            foreach ($extra_user_meta as $extra_meta_field) {
                $data_bottom[] = $extra_meta_field;
            }
        }

        $data[]=$data_bottom;
        
        $skip_cols = array('other_data');
        
        if(!function_exists('actt_prepare_export'))
            exit('Missing addon');

        $print_data = actt_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_current_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        exit();
    }
    
    
    public function filter_save ($id = NULL, $redirect='1') {
        $this->enable_error_reporting();

        $ajax_output = array();
        $ajax_output['message'] = '';
        $ajax_output['success'] = false;

        $name_val = 'activitytime_list_save_search_filter_Userid'.get_current_user_id();
        $options = get_option( $name_val );
        $filter_name = sanitize_text_field($_POST['filter_name']);
        $filter_par = sanitize_text_field($_POST['filter_param']);
        
        $json_string = $filter_par;

        $json_string = stripslashes($json_string); 
        $filter_par = json_decode($json_string);
        
        $options[] = [
            'name'=> $filter_name,
            'filter_par'=> serialize($filter_par)
        ];
        
        update_option($name_val, $options);
        
        $ajax_output['success'] = true;
        $json_output = json_encode($ajax_output);
        //$length = mb_strlen($json_output);
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache');
        header('Content-Type: application/json; charset=utf8');
        //header('Content-Length: '.$length); // special characters causing troubles

        echo $json_output;
        exit();
    }
    
    
    public function filter_get ($id = NULL, $redirect='1') {
        $this->enable_error_reporting();
    
        $ajax_output = array();
        $ajax_output['message'] = '';
        $ajax_output['success'] = false;
        
        $name_val = 'activitytime_list_save_search_filter_Userid'.get_current_user_id();
        $options = get_option( $name_val );
        
        $results = [];
        if(!empty($options))
        foreach ($options as $key => $filter) {
            $results[] = [
                'filterid'=> $key,
                'name'=> $filter['name'],
                'filter_par'=> json_encode(unserialize($filter['filter_par']))
            ];
            
        }
        
        $ajax_output['results'] = $results;
        $ajax_output['success'] = true;
        $json_output = json_encode($ajax_output);
        //$length = mb_strlen($json_output);
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache');
        header('Content-Type: application/json; charset=utf8');
        //header('Content-Length: '.$length); // special characters causing troubles

        echo $json_output;
        exit();
    }
    
    public function filter_remove ($id = NULL, $redirect='1') {
        $this->enable_error_reporting();
        
        $ajax_output = array();
        $ajax_output['message'] = '';
        $ajax_output['success'] = false;
        

        $name_val = 'activitytime_list_save_search_filter_Userid'.get_current_user_id();
        $options = get_option( $name_val );
        
        if(!empty($options) && isset($options[$_POST['filter_id']])) {
            unset($options[$_POST['filter_id']]);
            update_option($name_val, $options);
        }
        
        $ajax_output['success'] = true;
        $json_output = json_encode($ajax_output);
        //$length = mb_strlen($json_output);
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache');
        header('Content-Type: application/json; charset=utf8');
        //header('Content-Length: '.$length); // special characters causing troubles

        echo $json_output;
        exit();
    }
    

    
}
