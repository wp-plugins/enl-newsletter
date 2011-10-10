<?php
/* Admin functions to set and save settings of the 
 * @package ENL
*/
require_once('pages.php');
require_once('meta_box.php');
require_once(ENL_INC.'tools.php');
require_once(ENL_INC.'list_table.php');
require_once(ENL_INC.'widget.php');
/* Initialize the theme admin functions */
add_action('init', 'enl_newsletter_admin_init');

function enl_newsletter_admin_init(){
			
    add_action('admin_menu', 'enl_newsletter_settings_init');
    add_action('admin_init', 'enl_newsletter_actions_handler');
    add_action('admin_init', 'enl_newsletter_admin_style');
    add_action('admin_init', 'enl_newsletter_admin_script');
    //add_action('add_meta_boxes', 'enl_newsletter_add_meta_box');
    //add_filter('admin_head','enl_newsletter_tinymce');
}

function enl_newsletter_add_meta_box(){
    add_meta_box( 
			'newsletter',
			__( 'Send as newsletter', 'newsletter' ),
			'enl_post_inner_meta_box',
			'post',
			'side',
			'core'
		);	
}

function enl_newsletter_tinymce(){
    wp_admin_css('thickbox');
	wp_print_scripts('jquery-ui-core');
	wp_print_scripts('jquery-ui-tabs');
	wp_print_scripts('post');
	wp_print_scripts('editor');
	add_thickbox();
	wp_print_scripts('media-upload');
	if (function_exists('wp_tiny_mce')) wp_tiny_mce(); 	
}

function enl_newsletter_settings_init(){
   global $enl;
   add_menu_page('Newsletter', 'Newsletter', 0, 'enl-campaigns', 'enl_newsletter_campaigns_page' ); 
   $enl->campaings = add_submenu_page('enl-campaigns', 'Campaigns', 'Campaigns', 0, 'enl-campaigns', 'enl_newsletter_campaigns_page' );
   $enl->addnew = add_submenu_page('enl-campaigns', 'Add Campaign', 'Add Campaign', 0, 'enl-add-new', 'enl_newsletter_add_new_page');
   $enl->subscribers = add_submenu_page('enl-campaigns', 'Subscribers', 'Subscribers', 0, 'enl-subscribers', 'enl_newsletter_subscribers_page' );
   //$enl->import = add_submenu_page('enl-campaigns', 'Import/Export', 'Import/Export', 0, 'enl-import', 'enl_newsletter_import_page' );
   $enl->settings = add_submenu_page('enl-campaigns', 'Settings', 'Settings', 0, 'enl-settings', 'enl_newsletter_configuration_page' );

   add_action( "load-{$enl->addnew}", 'enl_newsletter_add_new_settings');
   add_action( "load-{$enl->settings}", 'enl_newsletter_configuration_settings');
}

function enl_newsletter_admin_style(){
  $plugin_data = get_plugin_data( ENL_DIR . 'enl_newsletter.php' );
	
	wp_enqueue_style( 'enl-newsletter-admin', ENL_CSS . 'style.css', false, $plugin_data['Version'], 'screen' );	
    wp_enqueue_style( 'enl-newsletter-new', ENL_CSS . 'newsletter.css', false, $plugin_data['Version'], 'screen' );       
}
function enl_newsletter_admin_script(){}
function enl_newsletter_actions_handler(){
   global $wpdb;
   
   if(isset($_GET['action']) && $_GET['action']=='campaign-delete'){
	  $newsletter_table = $wpdb->prefix.'enl_newsletter';
	  $query = "DELETE FROM $newsletter_table WHERE id=".$_GET['id'];
	  $wpdb->query($query);
	  $redirect = admin_url( 'admin.php?page=enl-campaigns' ); 
      wp_redirect($redirect);
   }
   
   if(isset($_GET['action']) && $_GET['action']=='campaign-run'){
	  $newsletter_table = $wpdb->prefix.'enl_newsletter';
	  $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
      $data = $wpdb->get_row($query);                    
      enl_newsletter_run_campaigns($data);
	  //update the last_run field of this campaign
	  $settings['last_run'] = time();
	  $where = array('id' => $_GET['id']); 
      $wpdb->update($wpdb->prefix.'enl_newsletter', $settings, $where);
	  $redirect = admin_url( 'admin.php?page=enl-campaigns&success=true' ); 
      wp_redirect($redirect);
   }
      
   if(isset($_GET['action']) && $_GET['action']=='user-delete'){
	  $users_table = $wpdb->prefix.'enl_users';
	  $query = "DELETE FROM $users_table WHERE id=".$_GET['id'];
	  $wpdb->query($query);
	  $redirect = admin_url( 'admin.php?page=enl-subscribers' ); 
      wp_redirect($redirect);
   }   
      
   if(isset($_POST['enl-settings'])){
	   $enl_opts = get_option(ENL_OPTIONS);
	   $enl_opts['import'] = $_POST['import'];
	   update_option(ENL_OPTIONS, $enl_opts);
	   $redirect = admin_url( 'admin.php?page=enl-settings&updated=true' ); 
       wp_redirect($redirect);    
   }   
      
   if(isset($_POST['campaign'])){
	  //put the campaign info to settings array
	  $settings = array();
	  $settings['name'] = $_POST['name'];
	  $settings['category'] = json_encode($_POST['post_category']);
	  $settings['email_subject'] = $_POST['subject'];
	  $settings['email_header'] = $_POST['header'];
	  $settings['email_footer'] = $_POST['footer'];
	  $settings['email_template'] = stripslashes($_POST['template']);
	  $settings['send_mode'] = $_POST['mode'];
	  $settings['number'] = $_POST['number'];
      
      
      //get the nex run time according to send_mode 	  
	  switch ($_POST['mode']){
		 case 'manual':
		    $settings['next_run'] = ''; 
		 break;
		 case 'weekly':		    
		    $next_week = time() + (7 * 24 * 60 * 60);
		    $settings['next_run'] = $next_week;
		    
		 break;
		 case 'monthly':		    
		    $next_month = time() + (30 * 24 * 60 * 60);
		    $settings['next_run'] = $next_month;
		 break;
		   
	  }
	  
	  //process the settings info according to action value
	  switch ($_POST['action']) { 
		 case 'create':		       
	        $settings['last_run'] = '';
	        $wpdb->insert( $wpdb->prefix.'enl_newsletter', $settings);
            if($wpdb->insert_id != false){ 
               $redirect = admin_url( 'admin.php?page=enl-add-new&updated=true&id=' ).$wpdb->insert_id; 
               wp_redirect($redirect);
            }      
		 break;
		 case 'update':
		    $where = array('id' => $_GET['id']); 
            $wpdb->update($wpdb->prefix.'enl_newsletter', $settings, $where);
            $redirect = admin_url( 'admin.php?page=enl-add-new&updated=true&id=' ).$_GET['id'];
            wp_redirect($redirect);		 
		 break;  
	  }  
   }

}
function enl_newsletter_error_message(){
   echo '<div class="error">
		<p>API is wrong</p>
  </div>';  
}
function enl_newsletter_success_message(){
  echo '<div class="updated fade">
		<p>This campaign has done successfully.</p>
  </div>';  
}
function enl_newsletter_update_message(){
   echo '<div class="updated fade">
		<p>Settings Updated</p>
  </div>';  	
}
function enl_newsletter_create_message(){
   echo '<div class="updated fade">
		<p>Campaign Created</p>
  </div>';	
}
?>
