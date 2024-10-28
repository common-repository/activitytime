<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}





// Display User IP in WordPress
 
if ( ! function_exists( 'actt_get_the_user_ip' ) ):
	function actt_get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return apply_filters( 'wpb_get_ip', $ip );
	}
endif;

if ( ! function_exists( 'actt_get_uri' ) ):
	function actt_get_uri($skip_query_string = FALSE)
	{
		$filename = sanitize_url($_SERVER['REQUEST_URI']);

		$filename = str_replace('\'', '', $filename);
        $filename = str_replace('`', '', $filename);
        $filename = str_replace('%', '', $filename);

		$ipos = strpos($filename, "?");
		if ( !($ipos === false) && $skip_query_string === TRUE)   $filename = substr($filename, 0, $ipos);
		return urldecode($filename);
	}
endif;

function actt_user_info($user_id)
{
	$user_info = get_userdata($user_id);
	$text = '';
	global $wp_roles;//global variable with roles, which contains the translation of roles

	if(isset($user_info->display_name)){
		$roles = '';
 
		foreach($user_info->roles as $role_key){
			if(isset($wp_roles->roles[$role_key]['name']))
				$roles .= $wp_roles->roles[$role_key]['name'];
		}
		$text = "#$user_info->ID <a target=\"_blank\" href=\"".admin_url('user-edit.php?user_id='.$user_info->ID)."\">".esc_html($user_info->display_name)."</a> <br /> ".esc_html($roles). " ";

	}

	if(empty($text))
	{
	$text = 'IP: '.actt_get_the_user_ip();
	}
	return $text;
	
}


function actt_user_info_bac($user_id)
{
	$user_info = get_userdata($user_id);

	$text = '';

	if(isset($user_info->user_login))
		$text = "#$user_info->ID <a target=\"_blank\" href=\"".admin_url('user-edit.php?user_id='.$user_info->ID)."\">$user_info->user_login</a> <br /> ".implode(', ', $user_info->roles) . " ";
	
	if(empty($text))
	{
		$text = 'IP: '.actt_get_the_user_ip();
	}

	return $text;
}

function &actt_get_instance()
{
	global $Winter_MVC;

	return $Winter_MVC;
}

function actt_get_title()
{
    $print = '';

    //$print.=get_admin_page_title();
    //$print.=wp_title('|',false,'right');
    //$print.=wp_get_document_title();
    //$print.=get_the_title();

    $print .= actt_generate_description();

	if(actt_get_uri() == '/') {
		$print = esc_html__('Home Page', 'activitytime');
	}

    return $print;
}

if ( ! function_exists('actt_resolve_wp_menu'))
{
    function actt_resolve_wp_menu()
    {
        global $submenu, $menu, $pagenow;
        
        $request_uri = actt_get_uri();

        $page = '';

        if(isset($_GET['page']))
            $page = wmvc_xss_clean($_GET['page']);

		$submenu_c = $submenu;
		$menu_c = $menu;
		$pagenow_c = $pagenow;
		
		if(empty($page) && empty($request_uri))return;

		if(!isset($submenu_c))
		{
			$menu_c = get_option( 'activitytime-menuitems' );
			$submenu_c = get_option( 'activitytime-submenuitems' );
		}
		
		$text = '';

        if(is_array($menu_c) && !empty($page))
        foreach($menu_c as $key=>$row)
        {
            if(in_array($page, $row))
            {
                $text.=$row[0];
            }
		}

        if(is_array($submenu_c) && !empty($page))
        foreach($submenu_c as $key=>$row)
        {
			foreach($row as $key2=>$row2)
			{
				if(in_array($page, $row2))
				{
					if(empty($text))
					{
						$text.=$row2[0];
					}
					else
					{
						$text.=' > '.$row2[0];
					}
				}
				
			}            
        }



		if(strpos($request_uri, 'wp-admin') !== FALSE)
			$request_uri = basename($request_uri);

		if(is_array($menu_c) && !empty($request_uri))
		foreach($menu_c as $key=>$row)
		{
			if(in_array($request_uri, $row))
			{
				if(empty($text))
				{
					$text.=$row[0];
				}
				else
				{
					$text.=' > '.$row[0];
				}
			}
		}

        if(is_array($submenu_c) && !empty($request_uri))
        foreach($submenu_c as $key=>$row)
        {
			foreach($row as $key2=>$row2)
			{
				if(in_array($request_uri, $row2))
				{
					if(empty($text))
					{
						$text.=$row2[0];
					}
					else
					{
						$text.=' > '.$row2[0];
					}
				}
				
			}            
		}

		if(strpos($request_uri, 'post.php') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Post';
			}
			else
			{
				$text.=' > Post';
			}
		}

		if(strpos($request_uri, 'plugins.php') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Plugins';
			}
			else
			{
				$text.=' > Plugins';
			}
        }
        
		if(strpos($request_uri, 'post-new.php') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Post Created';
			}
			else
			{
				$text.=' > Post Created';
			}
        }	
        
        if(strpos($request_uri, 'edit.php') !== FALSE && strpos($request_uri, 'trashed') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Post';
			}
			else
			{
				$text.=' > Post';
			}
        }	

        if(strpos($request_uri, 'edit.php') !== FALSE && strpos($request_uri, 'deleted') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Post';
			}
			else
			{
				$text.=' > Post';
			}
        }	

        if(strpos($request_uri, 'edit.php') !== FALSE && strpos($request_uri, 'post_status') !== FALSE)
		{
			if(empty($text))
			{
				$text.='Edit Post';
			}
			else
			{
				$text.=' > Edit Post';
			}
        }	
		
		if(!empty($text))return $text;

		
		if(strpos($request_uri, 'admin-ajax.php') !== FALSE)
		{
			return 'Ajax request';
		}

		if(strpos($request_uri, 'wp-cron.php') !== FALSE)
		{
			return 'WP Cron';
		}

		if(strpos($request_uri, 'wp_scrape_nonce') !== FALSE)
		{
			return 'WP Scraping';
		}

		if(strpos($request_uri, 'wp-json') !== FALSE)
		{
			return 'WP JSON';
		}
		
		if(strpos($request_uri, 'post.php?') !== FALSE)
		{
			return 'Edit post/page';
        }
        
		if(strpos($request_uri, 'wp-login.php') !== FALSE)
		{
			return 'wp-login.php';
        }

		if(strpos($request_uri, 'options.php') !== FALSE)
		{
			return 'WP Options';
		}

        if(!empty(single_cat_title('', false)))
        {
            return 'Category: '.single_cat_title('', false);
        }
		
        if(!empty(get_the_title()))
        {
            return 'Post/Page: '.get_the_title();
        }

		return $request_uri;
    }
}

function actt_generate_description()
{
    $desc = '';
    
	$desc .= actt_resolve_wp_menu();

	if(!empty($_GET['action']))
		$desc .= ' > '.wmvc_xss_clean($_GET['action']);


	if(isset($_GET['function']))
	{
		$desc .= ' > '.wmvc_xss_clean($_GET['function']);
    }
    
    if(isset($_GET['trashed']))
	{
		$desc .= ' > Trashed';
    }
    
    if(isset($_GET['deleted']))
	{
		$desc .= ' > Deleted';
	}

	if(isset($_POST['post_ID']))
	{
		$post_link = get_edit_post_link(intval($_POST['post_ID']));

		$post_title = '';
		if(isset($_POST['post_title']))
			$post_title = ' ('.wmvc_xss_clean($_POST['post_title']).')';

		$desc .= ' > '."Editing post with ID: <a target=\"_blank\" href=\"$post_link\">".intval($_POST['post_ID']).$post_title.'</a>';
	}

	if(isset($_GET['action']) && $_GET['action'] == 'edit-theme-plugin-file')
	{
		if(isset($_POST['file']))
		{
			$desc .= ' > '.'File: '.wmvc_xss_clean($_POST['file']);
		}
	}

	if(isset($_GET['plugin']))
	{
		$desc .= ' > Plugin: '.wmvc_xss_clean($_GET['plugin']);
	}
    
	if(isset($_GET['post_type']))
	{
		$desc .= ' > Post Type: '.wmvc_xss_clean($_GET['post_type']);
    }

    if(isset($_GET['post_status']))
	{
		$desc .= ' > Post status: '.wmvc_xss_clean($_GET['post_status']);
    }

    if(isset($_GET['ids']))
	{
		$desc .= ' > Ids: '.wmvc_xss_clean($_GET['ids']);
    }

    if(isset($_GET['post']) && is_numeric($_GET['post']))
	{
		$desc .= ' > Post ID: '.wmvc_xss_clean($_GET['post']);
    }

    if(isset($_GET['elementor-preview']) && is_numeric($_GET['elementor-preview']))
	{
		$desc .= ' > Elementor Editing Post/Page ID: '.wmvc_xss_clean($_GET['elementor-preview']);
    }

    if(isset($_GET['preview_id']) && is_numeric($_GET['preview_id']))
	{
		$desc .= ' > Elementor Preview Post/Page ID: '.intval($_GET['preview_id']);
    }

    if(isset($_GET['loggedout']))
	{
		$desc .= ' > Logged Out: '.wmvc_xss_clean($_GET['loggedout']);
    }

	return $desc;
}



if ( ! function_exists('actt_prepare_search_query_GET'))
{
	function actt_prepare_search_query_GET($columns = array(), $model_name = NULL, $external_columns = array())
	{
		$CI = &actt_get_instance();
		$_GET_clone = array_merge($_GET, $_POST);
		
		$_GET_clone = wmvc_xss_clean($_GET_clone);
        
        $smart_search = '';
        if(isset($_GET_clone['search']))
            $smart_search = wmvc_xss_clean($_GET_clone['search']['value']);
            
        $available_fields = $CI->$model_name->get_available_fields();
        
        //$table_name = substr($model_name, 0, -2);  
        
        $columns_original = array();
        foreach($columns as $key=>$val)
        {
            $columns_original[$val] = $val;
            
            // if column contain also "table_name.*"
            $splited = explode('.', $val);
            if(wmvc_count($splited) == 2)
                $val = $splited[1];
            
            if(isset($available_fields[$val]))
            {
                
            }
            else
            {
                if(!in_array($columns[$key], $external_columns))
                {
                    unset($columns[$key]);
                }
            }
        }

        if(wmvc_count($_GET_clone) > 0)
        {
            unset($_GET_clone['search']);
            
            // For quick/smart search
            if(wmvc_count($columns) > 0 && !empty($smart_search))
            {
                $gen_q = '';
                foreach($columns as $key=>$value)
                {
                    if(substr_count($value, 'id') > 0 && is_numeric($smart_search))
                    {
                        $gen_q.="$value = $smart_search OR ";
                    }
                    else if(substr_count($value, 'date') > 0)
                    {
						//$gen_search = wmvc_generate_slug($smart_search, ' ');
						
						$gen_search = $smart_search;
                        
                        $gen_q.="$value LIKE '%$gen_search%' OR ";
                    }
                    else
                    {
                        $gen_q.="$value LIKE '%$smart_search%' OR ";
                    }
                }
                $gen_q = substr($gen_q, 0, -4);
                
                if(!empty($gen_q))
                    $CI->db->where("($gen_q)");
            }
            
            // For column search
            if(isset($_GET_clone['columns'])) 
            {
                $gen_q = '';
                
                //var_dump($_GET_clone['columns']);
                
                foreach($_GET_clone['columns'] as $key=>$row)
                {
                    if(!empty($row['search']['value']))
                    if(isset($columns[$key]))
                    {
                        $col_name = $columns[$key];
                        
                        if(substr_count($row['data'], 'id') > 0 && is_numeric($row['search']['value']))
                        {
                            // ID is always numeric
                            
                            $gen_q.=$col_name." = ".$row['search']['value']." AND ";
                        }
                        else if(substr_count($row['data'], 'time_start') > 0)
                        {
                            // DATE VALUES
                            
							$gen_search = $row['search']['value'];
							
							$detect_date = strtotime($gen_search);

							if(is_numeric($detect_date) && $detect_date > 1000)
							{
								$gen_search = date('Y-m-d H:i:s', $detect_date);
								$gen_q.=$col_name." >= '".$gen_search."' AND ";
							}
                        }
                        else if(substr_count($row['data'], 'time_end') > 0)
                        {
                            // DATE VALUES
                            
							$gen_search = $row['search']['value'];
							
							$detect_date = strtotime($gen_search);

							if(is_numeric($detect_date) && $detect_date > 1000)
							{
								$gen_search = date('Y-m-d H:i:s', $detect_date);
								$gen_q.=$col_name." <= '".$gen_search."' AND ";
							}
                        }
                        else if(substr_count($row['data'], 'request_uri') > 0)
                        {
                            // DATE VALUES
                            
							$gen_search = $row['search']['value'];
                            $filter_request_uri = $gen_search;

                            if(!empty($filter_request_uri))
                            {
                                if(strpos($filter_request_uri, ',') === FALSE)
                                {
                                    $gen_q.= '(request_uri LIKE "%'.esc_html($filter_request_uri).'%") AND ';
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
                    
                                    $gen_q.= '( '.join(' OR ', $or_s).') AND ';
                                }
                            }   
                        }
                        else if(substr_count($row['data'], 'date') > 0)
                        {
                            // DATE VALUES
                            
							$gen_search = $row['search']['value'];
							
							$detect_date = strtotime($gen_search);

							if(is_numeric($detect_date) && $detect_date > 1000)
							{
								$gen_search = date('Y-m-d H:i:s', $detect_date);
								$gen_q.=$col_name." > '".$gen_search."' AND ";
							}
							else
							{
								$gen_q.=$col_name." LIKE '%".$gen_search."%' AND ";
							}
                        }
                        else if(substr_count($row['data'], 'is_') > 0)
                        {
                            // CHECKBOXES
                            
                            if($row['search']['value']=='on')
                            {
                                $gen_search = 1;
                                $gen_q.=$col_name." LIKE '%".$gen_search."%' AND ";
                            }
                            else if($row['search']['value']=='off')
                            {
                                $gen_q.=$col_name." IS NULL AND ";
                            }
                        }
                        else
                        {
                            $gen_q.=$col_name." LIKE '%".$row['search']['value']."%' AND ";
                        }
                    }

                }
                
                $gen_q = substr($gen_q, 0, -5);
                
                if(!empty($gen_q))
                    $CI->db->where("($gen_q)");
			}
			

			// order

			/*
			["order"]=>
			array(1) {
				[0]=>
				array(2) {
				["column"]=>
				string(1) "0"
				["dir"]=>
				string(4) "desc"
				}
			}
			*/

			if(isset($_GET_clone['order']))
			{
				foreach($_GET_clone['order'] as $order_row)
				{
					$CI->db->order_by($columns[$order_row['column']].' '.$order_row['dir']);
				}
			}

        }
	}
}

if(!function_exists('actt_get_user_data')) {
	/**
	* Get user data
	*
	* @param      int    $user id
	* @return     array  user data ('userdata'=>$userdata, 'avatar'=>get_avatar_url($user_id),'user_id'=>$user_id);
	*/
   function actt_get_user_data ($user_id='') {
	   static $users_cache = array();
	   
	   $user = array();
	   if(isset($users_cache[$user_id])) {
		   $user = $users_cache [$user_id];
	   } else {
		   $userdata = get_userdata($user_id);
		   if($userdata) {
			   $user = array('userdata'=>$userdata, 'avatar'=>get_avatar_url($user_id, array("size"=>300)),'user_id'=>$user_id);
		   }
		   if($user) {
			   $users_cache [$user_id] = $user;
		   } else {
			   $users_cache [$user_id] = $user;
		   }
	   }
	   if(!empty($user)) {
		   return $user;
	   }

	   return NULL;
   }
}

