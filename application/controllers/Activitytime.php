<?php
defined('WINTER_MVC_PATH') OR exit('No direct script access allowed');

class Activitytime_index extends Winter_MVC_Controller {

	public function __construct(){
		parent::__construct();
	}
    
	public function index()
	{
        global $wpdb;

        $this->data['date_from'] = date('Y-m-d', current_time( 'timestamp' )-30*24*60*60);
        $this->data['date_to'] = date('Y-m-d', current_time( 'timestamp' ));
        $this->data['uri_part'] = '';

        if(isset($_POST['history_date_from']))
        {
            $this->data['date_from'] = sanitize_text_field(wmvc_xss_clean($_POST['history_date_from']));
        }

        if(isset($_POST['history_date_to']))
        {
            $this->data['date_to'] = sanitize_text_field(wmvc_xss_clean($_POST['history_date_to']));
        }

        if(isset($_POST['uri_part']))
        {
            $this->data['uri_part'] = sanitize_text_field(wmvc_xss_clean($_POST['uri_part']));
        }

        // Get 20 most active users
        $table_name = $wpdb->prefix . 'actt_user_sessions';
        
        $join_part = '';

        if(isset($_POST['account_type']) && !empty($_POST['account_type']))
        {
            $join_part = ' JOIN '.$wpdb->prefix.'usermeta ON '.$wpdb->prefix.'usermeta.user_id = '.$table_name.'.user_id AND meta_value LIKE \'%'.sanitize_text_field(wmvc_xss_clean($_POST['account_type'])).'%\' ';
        }

        $query  = 'SELECT SUM(time_sec_total) as total_time, user_info, '.$table_name.'.user_id FROM '.$table_name.' '.$join_part.' WHERE is_visit_end = 1 ';
        $query .= 'AND time_start >= \''.$this->data['date_from'].' 00:00\' AND time_start <= \''.$this->data['date_to'].' 23:59\' ';
        $query .= 'GROUP BY '.$table_name.'.user_id ORDER BY total_time DESC LIMIT 20';

        //echo $query;

        $this->data['most_active_users'] = $wpdb->get_results($query);

        $this->data['most_active_users_data'] = '';
        $this->data['most_active_users_labels'] = '';
        $this->data['most_active_users_colors'] = '';

        if(is_array($this->data['most_active_users']))
        {
            $data_labels = array();
            $data_colors = array();
            foreach($this->data['most_active_users'] as $key=>$row)
            {
                // strip username from info
                $label_prepare = $row->user_info;
                if(strpos($row->user_info, '>') !== FALSE)
                {
                    $label_prepare = trim(substr($label_prepare, strpos($label_prepare, '>')+1));
                    $label_prepare = trim(substr($label_prepare, 0, strpos($label_prepare, '<')));
                }

                $data_labels[] = ' \''.$label_prepare.'\' ';

                $data_colors[] = ' \''.$this->getColor($key+1).'\' ';

                if($key > 9)break;
            }
            $this->data['most_active_users_labels'] = join(',', $data_labels);
            $this->data['most_active_users_colors'] = join(',', $data_colors);

            //dump($this->data['most_active_users_colors']);
            //exit();

            $data_data = array();
            foreach($this->data['most_active_users'] as $key=>$row)
            {
                $data_data[] = $row->total_time;

                if($key > 9)break;
            }
            $this->data['most_active_users_data'] = join(',', $data_data);

        }

        // Get 20 most active pages
        $table_name = $wpdb->prefix . 'actt_visited_pages';
        
        $join_part = '';

        if(isset($_POST['account_type']) && !empty($_POST['account_type']))
        {
            $join_part = ' JOIN '.$wpdb->prefix.'usermeta ON '.$wpdb->prefix.'usermeta.user_id = '.$table_name.'.user_id AND meta_value LIKE \'%'.sanitize_text_field(wmvc_xss_clean($_POST['account_type'])).'%\' ';
        }

        $query  = 'SELECT SUM(time_sec_total) as total_time, user_info, request_uri, title, '.$table_name.'.user_id FROM '.$table_name.' '.$join_part.' WHERE is_visit_end = 1 ';
        $query .= 'AND time_start >= \''.$this->data['date_from'].' 00:00\' AND time_start <= \''.$this->data['date_to'].' 23:59\' ';
        if(!empty($this->data['uri_part']))
            $query .= 'AND request_uri LIKE "%'.sanitize_text_field($this->data['uri_part']).'%"';
        $query .= 'GROUP BY title ORDER BY total_time DESC LIMIT 20';

        $this->data['most_active_pages'] = $wpdb->get_results($query);

        $this->data['most_active_pages_data'] = '';
        $this->data['most_active_pages_labels'] = '';
        $this->data['most_active_pages_colors'] = '';

        if(is_array($this->data['most_active_pages']))
        {
            $data_labels = array();
            $data_colors = array();
            foreach($this->data['most_active_pages'] as $key=>$row)
            {
                $label_prepare = strip_tags($row->title);

                $data_labels[] = ' \''.$label_prepare.'\' ';

                $data_colors[] = ' \''.$this->getColor($key+1).'\' ';

                if($key > 9)break;
            }
            $this->data['most_active_pages_labels'] = join(',', $data_labels);
            $this->data['most_active_pages_colors'] = join(',', $data_colors);

            //dump($this->data['most_active_pages_colors']);
            //exit();

            $data_data = array();
            foreach($this->data['most_active_pages'] as $key=>$row)
            {
                $data_data[] = $row->total_time;

                if($key > 9)break;
            }
            $this->data['most_active_pages_data'] = join(',', $data_data);

        }

        $all_roles = wmvc_roles_array();

        $roles_prepare = array(''=>__('Any', 'activitytime'));

        foreach($all_roles as $row)
        {
            $roles_prepare[$row['role']] = $row['role'].', '.$row['name'];
        }

        $this->data['roles_prepare'] = $roles_prepare;

        $this->data['dbdata'] = array();

        // Load view
        $this->load->view('activitytime/index', $this->data);
    }

    public function csv_export_users()
    {
        ob_clean();

        global $wpdb;

        $table_name = $wpdb->prefix . 'actt_user_sessions';
        $table_users_name = $wpdb->prefix . 'users';

        if(defined('CUSTOM_USER_TABLE'))
            $table_users_name= '`'.CUSTOM_USER_TABLE.'`';
        if( defined('MULTISITE') && MULTISITE && !defined('CUSTOM_USER_TABLE'))
        {
            $main_blog_prefix = $wpdb->get_blog_prefix(get_main_site_id());
            $table_users_name = '`'.$main_blog_prefix.'users`';
        }
        
        $query  = 'SELECT SUM(time_sec_total) as total_time, user_info, user_id, 
                   '.$table_users_name.'.user_login as login,
                   '.$table_users_name.'.user_nicename as name,
                   '.$table_users_name.'.user_email as email
                   FROM '.$table_name.'
                   LEFT JOIN '.$table_users_name.' ON '.$table_users_name.'.ID = user_id  WHERE is_visit_end = 1 ';

        $query .= 'GROUP BY user_id ORDER BY total_time DESC';

        $data = $wpdb->get_results($query);


        $gmt_offset = get_option('gmt_offset');

        foreach($data as $key=>$row)
        {
            $row->user_info = strip_tags($row->user_info);

            if(!empty($row->total_time)) {
                $init = $row->total_time;
                $hours = floor($init / 3600);
                $minutes = floor(($init / 60) % 60);
                $seconds = $init % 60;
                $row->hms = wp_kses_post("$hours:$minutes:$seconds");
            } else {
                $row->hms = '0';
            }
           
            $data[$key] = $row;
        }
        
        $skip_cols = array('other_data');

        if(!function_exists('actt_prepare_export'))
            exit('Missing addon');
        
        $print_data = actt_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_most_users_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        
        exit();
    }

    public function csv_export_pages()
    {
        ob_clean();

        global $wpdb;

        $table_name = $wpdb->prefix . 'actt_visited_pages';
        
        $query  = 'SELECT SUM(time_sec_total) as total_time, user_info, request_uri, title, user_id FROM '.$table_name.' WHERE is_visit_end = 1 ';
        $query .= 'GROUP BY title ORDER BY total_time DESC';

        $data = $wpdb->get_results($query);


        $gmt_offset = get_option('gmt_offset');

        foreach($data as $key=>$row)
        {
            $row->user_info = strip_tags($row->user_info);
            
            if(!empty($row->total_time)) {
                $init = $row->total_time;
                $hours = floor($init / 3600);
                $minutes = floor(($init / 60) % 60);
                $seconds = $init % 60;
                $row->hms = wp_kses_post("$hours:$minutes:$seconds");
            } else {
                $row->hms = '0';
            }

            $data[$key] = $row;
        }

        $skip_cols = array('other_data');
        
        if(!function_exists('actt_prepare_export'))
            exit('Missing addon');

        $print_data = actt_prepare_export($data, $skip_cols);

        header('Content-Type: application/csv');
        header("Content-Length:".strlen($print_data));
        header("Content-Disposition: attachment; filename=csv_most_pages_".date('Y-m-d-H-i-s', time()+$gmt_offset*60*60).".csv");

        echo $print_data;
        
        exit();
    }

    private function getColor($num) {
        $hash = md5('color' . $num); // modify 'color' to get a different palette
        return 'rgb('.hexdec(substr($hash, 0, 2)).', '.hexdec(substr($hash, 2, 2)).', 100)';
    }

    
}
