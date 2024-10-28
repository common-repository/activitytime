<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Actt_time_by_postacc extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{

        // Load view
        $this->load->view('actt_time_by_post_pageacc/index', $this->data);
    }

	// Called from ajax
	// json for datatables
	public function datatable()
	{
        //$this->enable_error_reporting();
        remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

        // configuration
        $columns = array('idvisited_pages', 'request_uri', 'title', 'user_info', 'time_start', 'time_end', 'time_start_min', 'time_end_max', 'time_sec_total', 'time_sec_total_acc');
        $controller = 'visitedpagesacc';
        
        // Fetch parameters
        $parameters = $this->input->post();
        $draw = $this->input->post_get('draw');
        $start = $this->input->post_get('start');
        $length = $this->input->post_get('length');
		$search = $this->input->post_get('search');


        if(isset($search['value']))
			$parameters['searck_tag'] = $search['value'];

        $where = array();
        if(isset($parameters['post_type']) && !empty($parameters['post_type'])) {

            if($parameters['post_type'] == 'admin_pages') {
                $where['request_uri LIKE "%wp-admin%" '] = NULL;  
            } elseif($parameters['post_type'] == 'categories'){
                $where['request_uri LIKE "%cat=%" '] = NULL;  
            } elseif($parameters['post_type'] == 'user'){
                $where['request_uri LIKE "%author=%" '] = NULL;  
            } elseif($parameters['post_type'] == 'page'){
                $where['posts.post_type = "'.esc_sql($parameters['post_type']).'" '] = NULL;  
            } else {
                $where['request_uri NOT LIKE "%'.esc_sql(substr(site_url(), strripos(site_url(), '/'))).'/"'] = NULL;  
                $where['posts.post_type = "'.esc_sql($parameters['post_type']).'" '] = NULL;  
            }
        }
		$this->load->model($controller.'_m');

        $recordsTotal = $this->{$controller.'_m'}->total_lang(array('time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        /* join post table, detect post_type */
        $this->db->join($this->db->prefix.'posts as posts ON posts.guid LIKE CONCAT("%", request_uri, "%")', NULL, 'LEFT');
        actt_prepare_search_query_GET($columns, $controller.'_m');
        if(count($where)>0)
            $this->db->where($where);
        $recordsFiltered = $this->{$controller.'_m'}->total_lang(array('time_end !='=>'0000-00-00 00:00:00'), NULL);
        
        /* join post table, detect post_type */
        $this->db->join($this->db->prefix.'posts as posts ON posts.guid LIKE CONCAT("%", request_uri, "%") ', NULL, 'LEFT');
        actt_prepare_search_query_GET($columns, $controller.'_m');
        if(count($where)>0)
            $this->db->where($where);
        $data = $this->{$controller.'_m'}->get_pagination_lang($length, $start, array('time_end !='=>'0000-00-00 00:00:00'));

        $query = $this->db->last_query();

        $data_listings = array();
        // Add buttons
        foreach($data as $key=>$row)
        {
            $listing = array();
            $listing = (array)$row;
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
                        $listing[$val] = $json->$val;
                    }
                    else
                    {
                        $listing[$val] = '-';
                    }
                }
                else
                {
                    $listing[$val] = '-';
                }
            }

            $listing['time_start'] = esc_html($row->time_start_min);
            $listing['time_end'] = esc_html($row->time_end_max);

            if($row->is_visit_end_min == 0)
            {
                //$row->time_sec_total = intval(strtotime($row->time_end) - strtotime($row->time_start));

                $listing['time_start'] = '<span style="color:green">'.esc_html($listing['time_start_min']).'</span>';
                $listing['time_end'] = '<span style="color:green">'.esc_html($listing['time_end_max']).'</span>';
            }
            
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$row->request_uri";
            $row->request_uri = '<a href="'.esc_url($actual_link).'" target="_blank">'.esc_html($row->request_uri).'</a>';

            if(empty($row->time_sec_total_acc))
            {
                $listing['time_sec_total'] = '-';
            }
            else
            {
                $init = $listing['time_sec_total_acc'];
                $minutes = floor(($init / 60));
                $seconds = $init % 60;

                $listing['time_sec_total'] = "$minutes:$seconds";
            }

            if($row->is_visit_end_min == 0)
            {
                $listing['time_sec_total'].=' <span class="see_label see_label-success puls">'.esc_html__('active','winter-activity-log').'</span>';
            }

            $options = '';//btn_edit(admin_url("admin.php?page=actt_add_graph&id=".$row->{"id$controller"})).' ';

            $listing['edit'] = $options;
            $listing['checkbox'] = '';
            
            $data_listings[] = $listing;
        }

        ob_clean();
        //format array is optional
        $json = array(
                "parameters" => $parameters,
                "query" => $query,
                "draw" => $draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $data_listings
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
        $this->load->model('visitedpagesacc_m');

        // Get parameters
        $visited_pages_ids = $this->input->post('visited_pages_ids');

        $json = array(
            "visited_pages_ids" => $visited_pages_ids,
            );

        foreach($visited_pages_ids as $id)
        {
            if(is_numeric($id))
                $this->visitedpagesacc_m->delete($id);
        }

        echo json_encode($json);
        
        exit();
    }

    /*
    *
    * ? extra_user_meta=display_name or extra_user_meta=display_name,email - user meta fields
    */
    public function export_csv_per_page()
    {
        ob_clean();
        $columns = array('idvisited_pages', 'request_uri', 'title', 'user_info', 'time_start', 'time_end', 'time_start_min', 'time_end_max', 'time_sec_total', 'time_sec_total_acc');
        $controller = 'visitedpagesacc';
            
        $this->load->model($controller.'_m');

        $extra_user_meta_get = sanitize_text_field($this->input->get('extra_user_meta'));
        $extra_user_meta = array();
        if($extra_user_meta_get)
            $extra_user_meta = explode(',',$extra_user_meta_get);
            
        // configuration
        $where = array();
        $post_type = sanitize_text_field($this->input->get('type'));
        if(!empty($post_type)) {

            $post_type = str_replace(array('+hyphens+',' hyphens '),'-',$post_type);
            if($post_type == 'admin_pages') {
                $where['request_uri LIKE "%wp-admin%" '] = NULL;  
            } elseif($post_type == 'categories'){
                $where['request_uri LIKE "%cat=%" '] = NULL;  
            } elseif($post_type == 'user'){
                $where['request_uri LIKE "%author=%" '] = NULL;  
            } elseif($post_type == 'page'){
                $where['posts.post_type = "'.esc_sql($post_type).'" '] = NULL;  
            } else {
                $where['request_uri NOT LIKE "%'.esc_sql(substr(site_url(), strripos(site_url(), '/'))).'/"'] = NULL;  
                $where['posts.post_type = "'.esc_sql($post_type).'" '] = NULL;  
            }
        }

        $field_user = sanitize_text_field($this->input->get('filter_user'));
        if(!empty($field_user))
            $where['(user_info LIKE "%'.esc_html($field_user).'%")'] = NULL;
        
        /* modify GET for actt_prepare_search_query_GET */
        global $_GET;
        $old_GET = $_GET;
        $search_text = sanitize_text_field($this->input->get('sw_log_search'));
        $_GET = array();
        $_GET['columns'] = array();
        foreach ($old_GET as $key => $value) {
            $key_field = substr(sanitize_text_field($key),7);
            if(in_array($key_field, $columns)) {
                $_GET['columns'][array_search($key_field, $columns)] = array(
                    'data'=>$key_field,
                    'search'=>array('value' => str_replace(array('+hyphens+',' hyphens '),'-',sanitize_text_field($value)))
                );
            }
        }
        $_GET['search']['value'] = $search_text;

        /* join post table, detect post_type */
        $this->db->join($this->db->prefix.'posts as posts ON posts.guid LIKE CONCAT("%", request_uri, "%") ', NULL, 'LEFT');
        
        actt_prepare_search_query_GET($columns, $controller.'_m');
        if(count($where)>0)
            $this->db->where($where);
        $data = $this->{$controller.'_m'}->get_pagination_lang(NULL, NULL, $where);

        $gmt_offset = get_option('gmt_offset');

        $sum_time_sec_total = 0;
        foreach($data as $key=>$row)
        {
            if(empty($row->time_sec_total))
                $row->time_sec_total = (string) (strtotime($row->time_end) - strtotime($row->time_start));

            $sum_time_sec_total +=$row->time_sec_total;

            $user_info = actt_get_user_data( $row->user_id );
            $row->post_id = url_to_postid(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$row->request_uri"));
            
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
            'other_data' => '',
            'post_id' => '',
            'login' => '',
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'bio' => ''
        ); 
        
        if($extra_user_meta) {
            foreach ($extra_user_meta as $extra_meta_field) {
                $data_bottom[] = $extra_meta_field;
            }
        }
        $data[]=$data_bottom;

        $skip_cols = array('other_data', 'time_start', 'time_end', 'time_sec_total', 'is_visit_end');
        
        if(!function_exists('actt_prepare_export'))
            exit('Missing addon');

        $print_data = actt_prepare_export($data, $skip_cols);

        $lang = get_bloginfo("language"); 
        if ($lang == 'ru-RU') 
            $print_data = mb_convert_encoding($print_data, "Windows-1251", "UTF-8");

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_per_page_acc_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");
        echo $print_data;
        
        exit();
    }
    

    
}
