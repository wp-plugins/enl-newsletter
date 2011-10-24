<?php
/**
 * Plugin Name: ENL Newsletter
 * Plugin URI: http://wp-coder.net
 * Description: Easy to create multiple newsletters containing the blog latest posts.
 * Version: 1.0.1
 * Author: Darell Sun
 * Author URI:  http://wp-coder.net
 *
 * @package ENL
 */

require_once('include/newsletter_config.php');
require_once('include/widget.php');
require_once('include/tools.php');
/* Set up the plugin. */
add_action('plugins_loaded', 'enl_newsletter_setup');  
//add cron intervals
add_filter('cron_schedules', 'enl_newsletter_intervals');
//Actions for Cron job
add_action('enl_newsletter_cron', 'enl_newsletter_cron_hook');
/* Create table when admin active this plugin*/
register_activation_hook(__FILE__,'enl_newsletter_activation');
register_deactivation_hook(__FILE__, 'enl_newsletter_deactivation');

function enl_newsletter_activation()
{
	$enl_opts = get_option(ENL_OPTIONS);
	if(!empty($enl_opts)){
	   $enl_opts['version'] = ENL_VERSION;
	   update_option(ENL_OPTIONS, $enl_opts); 	
	}else{
	   $opts = array(
		'version' => ENL_VERSION,
		'import' => 'off'				
	  );
	  // add the configuration options
	  add_option(ENL_OPTIONS, $opts);   	
	}	
	
	
	//test if cron active
	//if (!(wp_next_scheduled('enl_newsletter_cron')))
	   wp_schedule_event(time(), 'enl_intervals', 'enl_newsletter_cron');
	
	enl_create_table();
}

function enl_newsletter_deactivation(){
    wp_clear_scheduled_hook('enl_newsletter_cron');	
}

function enl_newsletter_cron_hook(){
    global $wpdb;
    $newsletter_table = $wpdb->prefix.'enl_newsletter';
	$query = "SELECT * FROM $newsletter_table";
    $entrys = $wpdb->get_results($query);
    
    foreach($entrys as $entry){
	   $next_run = $entry->next_run;
	   $current_time = time();
	   if (!empty($next_run)&&($next_run<=$current_time)) {
		enl_newsletter_run_campaigns($entry);
		switch ($entry->send_mode){
		   	case 'weekly':
		   	   $next_week = time() + (7 * 24 * 60 * 60);
		   	   $settings['next_run'] = $next_week;
		   	   $settings['last_run'] = time();
		   	   $where = array('id' => $entry->id); 
               $wpdb->update($wpdb->prefix.'enl_newsletter', $settings, $where);
		   	case 'monthly':
		   	   $next_month = time() + (7 * 24 * 60 * 60);
		   	   $settings['next_run'] = $next_month;
		   	   $settings['last_run'] = time();
		   	   $where = array('id' => $entry->id); 
               $wpdb->update($wpdb->prefix.'enl_newsletter', $settings, $where);
		 }
	   }	
	}	
}

function enl_newsletter_intervals($schedules){
   $intervals['enl_intervals']=array('interval' => '300', 'display' => 'enl_newsletter');
   $schedules=array_merge($intervals,$schedules);
   return $schedules;	
}

/*
 *Create database table for this plugin
*/
function enl_create_table(){
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  global $wpdb;
  $newsletter_table = $wpdb->prefix . 'enl_newsletter';    
  $newsletter_users = $wpdb->prefix . 'enl_users';
  
  if( $wpdb->get_var( "SHOW TABLES LIKE '$newsletter_table'" ) != $newsletter_table ){
         $sql = "CREATE TABLE " . $newsletter_table . " (
         id       bigint(20) auto_increment primary key,
         name varchar(100),
         category text,
         email_subject varchar(100),
         email_header text,
         email_footer text,
         email_template text,
         send_mode varchar(10),
         number int(11),
         last_run varchar(20),
         next_run varchar(20)       
         );";
         dbDelta($sql);  	  
         $h = fopen(dirname(__FILE__).'/log.txt', 'w'); fwrite($h, $sql); fclose($h);
  }
  
  if( $wpdb->get_var( "SHOW TABLES LIKE '$newsletter_users'" ) != $newsletter_users ){
         $sql = "CREATE TABLE " . $newsletter_users . " (
         id       bigint(20) auto_increment primary key,
         email    varchar(100),
         time timestamp default CURRENT_TIMESTAMP,
         ip       varchar(32)         
         );";
         dbDelta($sql);  	  
         $h = fopen(dirname(__FILE__).'/log.txt', 'w'); fwrite($h, $sql); fclose($h);
  }  	
}

/* 
 * Set up the social server plugin and load files at appropriate time. 
*/
function enl_newsletter_setup(){
   /* Set constant path for the plugin directory */
   define('ENL_DIR', plugin_dir_path(__FILE__));
   define('ENL_ADMIN', ENL_DIR.'/admin/');
   define('ENL_INC', ENL_DIR.'/include/');

   /* Set constant path for the plugin url */
   define('ENL_URL', plugin_dir_url(__FILE__));
   define('ENL_CSS', ENL_URL.'css/');
   define('ENL_JS', ENL_URL.'js/');

   if(is_admin())
      require_once(ENL_ADMIN.'admin.php');

   /*Print style */
   add_action('wp_print_styles', 'enl_newsletter_style');
 
   /* print script */
   add_action('wp_print_scripts', 'enl_newsletter_script');
   
   /* widget */
   add_action('widgets_init', 'enl_newsletter_widget_init');
   add_action('init', 'enl_newsletter_check_submit');
   
   /* display as text/html format */
   add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

   
	

   //$cron = wp_get_schedules();
   //error_log( "CRON jobs: " . print_r( $cron, true ) );
}

function enl_newsletter_check_submit(){
   if(isset($_POST['enl_submit'])){
	  $email = $_POST['enl_email'];
	  $ip = $_SERVER['REMOTE_ADDR'];
	  if(!is_email($email)) {
		 wp_die( __('Error: please enter a valid email address.') );
	  }			
	  enl_newsletter_send_email($email); 
	  enl_newsletter_add_subscriber($email, $ip);   
   }
   return;	
}

function enl_newsletter_widget_init() {
	register_widget('Enl_Newsletter_Widget');
}

function enl_newsletter_style(){
  
}
function enl_newsletter_script(){
 
}
?>
