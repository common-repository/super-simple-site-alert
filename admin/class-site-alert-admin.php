<?php

class Site_Alert_Admin {

	private $site_alert;
	private $version;

	public function __construct( $site_alert, $version ) {

		$this->site_alert = $site_alert;
		$this->version = $version;

	}

	public function enqueue_styles() {
		wp_enqueue_style( 'sssa-admin', plugin_dir_url( __FILE__ ) . 'css/site-alert-admin.css', array(), null, 'all' );
		wp_enqueue_style( 'sssa-air-datepicker', plugin_dir_url( __FILE__ ) . 'js/air-datepicker/css/datepicker.css', array(), null, false );
		wp_enqueue_style( 'sssa-dataTables', plugin_dir_url( __FILE__ ) . 'js/DataTables-1.10.24/css/jquery.dataTables.min.css', array(), null, false );
 	  wp_enqueue_style( 'wp-color-picker');
		//public site alert css
		wp_enqueue_style( 'sssa-public', plugin_dir_url( __FILE__ ) . '../public/css/site-alert-public.css', array(), null, false );
		wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__) . 'js/jquery-ui/jquery-ui-selectable.min.css', array(), null, false );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'sssa-air-datepicker', plugin_dir_url( __FILE__ ) . 'js/air-datepicker/js/datepicker.js', array(), null, false );
		wp_enqueue_script( 'sssa-air-datepicker-lang', plugin_dir_url( __FILE__ ) . 'js/air-datepicker/js/i18n/datepicker.en.js', array(), null, false );
		wp_enqueue_script( 'sssa-date-format', plugin_dir_url(__DIR__) . 'public/js/date-format/date-format.js', array(), null, false );
		wp_enqueue_script( 'sssa-dataTables', plugin_dir_url( __FILE__) . 'js/DataTables-1.10.24/js/jquery.dataTables.min.js', array(), null, false );
		wp_enqueue_script( 'sssa-list-js', plugin_dir_url( __FILE__) . 'js/listjs/listjs.min.js', array(), null, false );
		wp_enqueue_script( 'sssa-utilities', plugin_dir_url( __FILE__ ) . 'js/utilities.js', array(), null, false );
		wp_enqueue_script( 'jquery-ui-selectable' );
		wp_enqueue_script( 'wp-color-picker');

	}

	public function my_admin_menu() {
		$title = 'Site Alert';
  	add_menu_page('Site Alert', $title, 'edit_posts', 'site-alert', array($this, 'site_alert_page'), 'dashicons-warning', 200);
		add_submenu_page('site-alert', $title.' Add/Edit', 'Add/Edit', 'edit_posts', 'site-alert-manage', array($this, 'site_alert_manage'));
		if(is_network_admin()){
			add_submenu_page('site-alert', $title.' Site Groups', 'Site Groups', 'edit_posts', 'site-groups', array($this, 'site_alert_groups'));
		}
	}

	public function site_alert_page() {
		//return views
		require_once plugin_dir_path(__DIR__) . '/includes/utilities.php';
		require_once 'partials/site-alert-admin-display.php';
	}

	public function site_alert_manage() {
		//return views

		print "<script>var site_alert_path = '".plugins_url()."/".basename(plugin_dir_path( __DIR__ ))."/'</script>";
		require_once 'partials/site-alert-admin-manage.php';
	}

	public function site_alert_groups() {
		//return views
		require_once 'partials/site-alert-admin-groups.php';
	}

	public function site_alert() {
		$labels = array(
			'name' => _x('Site Alert', 'Post Type General Name'),
		);

		$args = array(
			'label' => __('site_alert'),
			'description' => __('Site Alert'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'author'),
			'hierarchical' => false,
			'public' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_nav_menus' => false,
			'menu_position' => 5,
			'can_export' => false,
			'has_archive' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
			'show_in_rest' => false,
		);

		//register
		register_post_type('site_alert', $args);
	}

	public function update_site_alert()
	{
		// nonce check for an extra layer of security, the function will exit if it fails
	   if ( !wp_verify_nonce( $_REQUEST['_ajax_nonce'], "update_site_alert_nonce")) {
	      exit("error-nonce");
	   }
	   $blog_id = intval($_POST['blog_id']);
	   switch_to_blog($blog_id);

	   $action = sanitize_text_field($_POST['user_action']);
	   $post_id = intval($_POST['post_id']);
	   if($action == "edit"){
		   	$post_status = sanitize_text_field($_POST['status']);
		   	$data = array(
	           'ID' => $post_id,
	           'post_status' => $post_status,
	           'post_type' => 'site_alert'
	       	);
			$result = wp_update_post($data);
			restore_current_blog();
	        if($result && ! is_wp_error($result)){
				exit("success");
			}else{
				exit("error");
			}
	   }elseif($action == "delete"){
		   $result = wp_delete_post( $post_id, $force_delete = true);
		   restore_current_blog();
		   if($result && !is_wp_error($result)){
			   exit("success");
		   }else{
			   exit("error");
		   }
	   }
	}

	public function create_group() {
		if ( !wp_verify_nonce( $_REQUEST['_ajax_nonce'], "create_group_nonce")) {
		   exit("error-nonce");
		}

		$data = array(
	        'post_title' => sanitize_text_field($_POST['title']),
					'post_status' => 'publish',
	        'post_type' => 'site_group',
	    );

		$result = wp_insert_post($data);

		if($result && ! is_wp_error($result)){
			$post_id = $result;
			$data['post_id'] = $post_id;
			exit(json_encode($data));
		}
	}

	public function update_group_prop() {
		if ( !wp_verify_nonce( $_REQUEST['_ajax_nonce'], "update_group_prop_nonce")) {
		   exit("error-nonce");
		}

		$event = sanitize_text_field($_POST['event_action']);
		$id = intval($_POST['id']);
		$val = sanitize_text_field($_POST['val']);

		if($event == 'update_title'){
			$data['ID'] = $id;
			$data['post_title'] = $val;
			$result = wp_update_post($data);
			if($result && ! is_wp_error($result)){
				exit("success");
			}
		}elseif($event == 'delete'){
			$result = wp_delete_post($id);
			if($result && ! is_wp_error($result)){
				exit("success");
			}
		}
	}

	public function poll_group() {
		if ( !wp_verify_nonce( $_REQUEST['_ajax_nonce'], "poll_group_nonce")) {
		   exit("error-nonce");
		}
		$id = intval($_POST['id']);
		$sites = get_post_meta($id, 'sites', true);
		exit($sites);
	}

	public function update_group_select_site() {
		if ( !wp_verify_nonce( $_REQUEST['_ajax_nonce'], "update_group_group_select_site_nonce")) {
 	      exit("error-nonce");
 	   	}
	   	$id = intval($_POST['id']);
		$sites = sanitize_text_field($_POST['sites']);
		if(metadata_exists('post', $id, 'sites')){
			$res = update_post_meta($id, 'sites', $sites);
		}else{
			$res = add_post_meta($id, 'sites', $sites);
		}
		if($res){
			$sites = get_post_meta($id, 'sites', true);
			$count = strlen($sites);
			if($count > 0){
				$sitesArr = explode(',', $sites);
				$count = count($sitesArr);
			}
			exit("".$count."");
		}
	}

}
