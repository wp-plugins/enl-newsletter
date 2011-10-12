<?php
function enl_newsletter_add_subscriber($email, $ip){
   global $wpdb;
   $settings = array('email' => $email, 'ip' => $ip);  
   $wpdb->insert( $wpdb->prefix.'enl_users', $settings);
   return;	
}

function enl_newsletter_send_email($email){
   $blogname = get_option('blogname');
   $subject = $blogname.' Newsletter';
   $message = "Thank you for subscribing. We hope you'll enjoy our newsletter.";
   wp_mail($email, $subject, $message);	
}

function enl_newsletter_run_campaigns($data){
   global $wpdb;
   $subject = $data->email_subject;
   $template = $data->email_template;
   $cats = json_decode($data->category);
   $post_count = $data->number;
   $selected_cats = implode(",", $cats);
   
   
   $args = array('cat' => $selected_cats, 'posts_per_page' => $post_count);
   // The Query
   $the_query = new WP_Query( $args );
   
   // The Loop   
   while ( $the_query->have_posts() ) : $the_query->the_post();
    //get the post excerpt   
    $post_excerpt = apply_filters('the_excerpt', get_the_excerpt());    
    $post_title = the_title('', '', false);
    $post_author = the_author('', false);
    $post_date = get_the_date();
    $post_url = apply_filters('the_permalink', get_permalink());
    $tags = array('{TITLE}', '{AUTHOR}', '{EXCERPT}', '{DATE}', '{URL}');
    $replace = array($post_title, $post_author, $post_excerpt, $post_date, $post_url); 
    $content .= str_replace($tags, $replace, $template);
   endwhile;   
   
   // Reset Post Data
   wp_reset_postdata();
   $brand = '<br /><br />----<br />Powder by <a href="http://wp-coder.net">WP-Coder.net</a>';
   $message = $data->email_header . $content . $data->email_footer . $brand;   
   $message = stripslashes($message);
     	
   $users_table = $wpdb->prefix.'enl_users';	
   $users = $wpdb->get_results("SELECT * FROM $users_table");

   //send email to subscribers
   foreach($users as $user){
	  $email = $user->email; 
	  $result = wp_mail($email, $subject, $message);
   }
   
   //also send to wordpress users
   $enl_opts = get_option(ENL_OPTIONS);
   if($enl_opts['import'] == 'on'){
	  /*
	  $users_id = $wpdb->get_col("SELECT $wpdb->users.ID FROM $wpdb->users");
	  //get all users email list
	  $email_list = array();
	  foreach($users_id as $id){
		$user = get_userdata($id);
		$email_list[] = $user->user_email;  
	  }	     
	  */
	  
	  //get only subscriber
	  $blogusers = get_users('role=subscriber');
	  $email_list = array();
	  foreach($blogusers as $user){
		$email_list[] = $user->user_email;  
	  }	
	  
	  //send email to users
	  foreach($email_list as $wp_email){
	    $result = wp_mail($wp_email, $subject, $message);
      }	  
   }
   
}
?>
