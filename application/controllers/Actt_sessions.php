<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Actt_sessions extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{

        // Load view
        $this->load->view('actt_sessions/index', $this->data);
    }

	// Called from ajax
	// json for datatables
	public function datatable()
	{
        //$this->enable_error_reporting();
        remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

        // configuration
        $columns = array('iduser_sessions', 'user_info', 'time_start', 'time_end', 'time_sec_total');
        $controller = 'usersessions';
        
        // Fetch parameters
        $parameters = $this->input->post();
        $draw = $this->input->post_get('draw');
        $start = $this->input->post_get('start');
        $length = $this->input->post_get('length');
		$search = $this->input->post_get('search');

        if(isset($search['value']))
			$parameters['searck_tag'] = $search['value'];
			
		$this->load->model($controller.'_m');

        $recordsTotal = $this->{$controller.'_m'}->total_lang(array('time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        actt_prepare_search_query_GET($columns, $controller.'_m');
        $recordsFiltered = $this->{$controller.'_m'}->total_lang(array('time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        actt_prepare_search_query_GET($columns, $controller.'_m');
        $data = $this->{$controller.'_m'}->get_pagination_lang($length, $start, array('time_end !='=>'0000-00-00 00:00:00'));

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

            if($row->is_visit_end == 0)
            {
                $row->time_sec_total = intval(strtotime($row->time_end) - strtotime($row->time_start));

                $row->time_start = '<span style="color:green">'.esc_html($row->time_start).'</span>';
                $row->time_end = '<span style="color:green">'.esc_html($row->time_end).'</span>';
            }

            if(empty($row->time_sec_total))
            {
                $row->time_sec_total = '-';
            }
            else
            {
                $init = $row->time_sec_total;
                $minutes = floor(($init / 60));
                $seconds = $init % 60;

                $row->time_sec_total = "$minutes:$seconds";
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
        $this->load->model('usersessions_m');

        // Get parameters
        $user_sessions_ids = $this->input->post('user_sessions_ids');

        $json = array(
            "user_sessions_ids" => $user_sessions_ids,
            );

        foreach($user_sessions_ids as $id)
        {
            if(is_numeric($id))
                $this->usersessions_m->delete($id);
        }

        echo json_encode($json);
        
        exit();
    }  

    /*
    *
    * ? extra_user_meta=display_name or extra_user_meta=display_name,email - user meta fields
    */
    public function export_csv_sessions()
    {
        ob_clean();

        $controller = 'usersessions';
            
        $this->load->model($controller.'_m');

        $where = array('time_end !='=>'0000-00-00 00:00:00');
        
        $field_user = sanitize_text_field($this->input->get('filter_user'));

        $extra_user_meta_get = sanitize_text_field($this->input->get('extra_user_meta'));
        $extra_user_meta = array();
        if($extra_user_meta_get)
            $extra_user_meta = explode(',',$extra_user_meta_get);

        if(!empty($field_user))
            $where['(user_info LIKE "%'.esc_html($field_user).'%")'] = NULL;

        $filter_request_uri = sanitize_text_field($this->input->get('filter_request_uri'));
            
        if(!empty($filter_request_uri))
        {
            if(strpos($filter_request_uri, ',') === FALSE)
            {
                $where['(request_uri LIKE "%'.esc_html($filter_request_uri).'%")'] = NULL;
            }
            else
            {
                $parts = explode(',', $filter_request_uri);
                $or_s = array();
                foreach($parts as $part)
                {
                    $part = trim($part);
                    $or_s[] = ' request_uri LIKE "%'.esc_html($part).'%" ';
                }

                $where['( '.join(' OR ', $or_s).') '] = NULL;
            }
        }   

        $filter_title = sanitize_text_field($this->input->get('filter_title'));
            
        if(!empty($filter_title))
            $where['(title LIKE "%'.esc_html($filter_title).'%")'] = NULL;

        $filter_time_start = sanitize_text_field($this->input->get('filter_time_start'));
        
        if(!empty($filter_time_start))
            $where['(time_start >= "'.date('Y-m-d H:i:s', strtotime(esc_html($filter_time_start))).'")'] = NULL;

        $filter_time_end = sanitize_text_field($this->input->get('filter_time_end'));
        
        if(!empty($filter_time_end))
            $where['(time_end <= "'.date('Y-m-d H:i:s', strtotime(esc_html($filter_time_end))).'")'] = NULL;
            
        $data = $this->{$controller.'_m'}->get_pagination_lang(NULL, NULL, $where);

        $gmt_offset = get_option('gmt_offset');
        $sum_time_sec_total = 0;
        foreach($data as $key=>$row)
        {
            if(empty($row->time_sec_total))
                $row->time_sec_total = (string) (strtotime($row->time_end) - strtotime($row->time_start));

            $row->user_info = strip_tags($row->user_info);
            $sum_time_sec_total +=$row->time_sec_total;

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
            'iduser_sessions' => __('Summ','winter-activity-log'),
            'time_start' => '',
            'time_end' => '',
            'time_sec_total' => (string) $sum_time_sec_total,
            'user_id' => '',
            'user_info' => '',
            'ip' => '',
            'is_visit_end' => '',
            'other_data' => '',
            'login' => '',
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'hms' => $hms,
            'bio' => ''
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
        header("Content-Disposition: attachment; filename=csv_sessions_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        
        exit();
    }

    
}
