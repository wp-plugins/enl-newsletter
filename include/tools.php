<?php
function enl_newsletter_post_excerpt() {
    global $post;
    $output = '';
    $output = $post->post_excerpt;
    if ( !empty($post->post_password) ) { // if there's a password
           $output = 'There is no excerpt because this is a protected post.';     
    }
    return $output;
}

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
    //$post_excerpt = enl_newsletter_post_excerpt();
    $post_date = get_the_date();
    $post_url = apply_filters('the_permalink', get_permalink());
    $tags = array('{TITLE}', '{AUTHOR}', '{EXCERPT}', '{DATE}', '{URL}');
    $replace = array($post_title, $post_author, $post_excerpt, $post_date, $post_url); 
    $content .= str_replace($tags, $replace, $template);
   endwhile;   
   
   // Reset Post Data
   wp_reset_postdata();
   
   $message = $data->email_header . $content . $data->email_footer;   
   $message = stripslashes($message);
     	
   $users_table = $wpdb->prefix.'enl_users';	
   $users = $wpdb->get_results("SELECT * FROM $users_table");
   
   foreach($users as $user){
	  $email = $user->email; 
	  $result = wp_mail($email, $subject, $message);
   }
   
}
?>
