<?php
function enl_newsletter_author_meta_box(){
  echo "<p><a href='http://wp-coder.net'>Custom WP plugin Services</a></p>";
  //echo "<p><a href='http://wp-coder.net/enl-newsletter'>You comments for this plugin</a></p>"; 
  echo "<p><a href='http://wordpress.org/extend/plugins/enl-newsletter/'>Give a good rating on WordPress.org</a></p>";	
}

function enl_newsletter_import_meta_box(){
   $enl_opts = get_option(ENL_OPTIONS);
   $import = $enl_opts['import'];
?>
   <table class="form-table">
		<tr>
			<th class='import-settings'>
            	<label for="import"><?php _e( 'Also send to wordpress subscribers:', 'newsletter' ); ?></label> 
            </th>
            <td>
                <input type="radio" name="import" value="on" <?php if(isset($import) && $import == 'on') echo 'checked="checked"'; ?> /> On
                <input type="radio" name="import" value="off" <?php if(isset($import) && $import == 'off') echo 'checked="checked"'; ?> /> Off
            </td>
		</tr>
   </table>		
<?php	
}

function enl_post_inner_meta_box(){
  $new = ( !isset($_GET['action']) || $_GET['action'] != 'edit' );  	
?>
  <input type="checkbox" name="enl" value="true" <?php if($new){echo 'checked="checked"'; }?> /> Send as newsletter
<?php  	
}

function enl_campagins_number_meta_box(){
  global $enl;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'enl_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
     $number = $data->number;
   }
?>
  Latest post number: <input type="text" name="number" value="<?php if(isset($number)){echo $number;}else{echo 5;} ?>" /> 
 
<?php
}

function enl_campagins_schedule_meta_box(){
global $enl;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'enl_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
     $send_mode = $data->send_mode;
   }
?>
   <input type="radio" name="mode" value="manual" <?php if(isset($send_mode) && $send_mode == 'manual') echo 'checked'; ?> /> Manual
   <input type="radio" name="mode" value="weekly" <?php if(isset($send_mode) && $send_mode == 'weekly') echo 'checked'; ?> /> Weekly
   <input type="radio" name="mode" value="monthly" <?php if(isset($send_mode) && $send_mode == 'monthly') echo 'checked'; ?> /> Monthly
<?php	
}

function enl_campagins_category_meta_box(){
   global $enl;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'enl_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
   }
?>
  <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
		<?php 
		 if(isset($data)){
		   $selected_cats = json_decode($data->category);	 
		   wp_category_checklist( 0, 0, $selected_cats);	 
		 }else{
		   wp_category_checklist(); 	 
		 }
		?>
  </ul>	
<?php
}

function enl_campagins_content_meta_box(){
   global $enl;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'enl_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
   }else{
	  $blogname = get_option('blogname'); 
	  $subject = 'Subject';
	  $header = "Welcome to $blogname newsletter!";
	  $template = '<h2><a href="{URL}">{TITLE}</a></h2><p><small>Posted By {AUTHOR}</small> on {DATE}</p><p>{EXCERPT}</p>';
	  $footer = "You are receiving this email because you subscribed to receive our e-newsletters.";                
   }
?>
   <table class="form-table">
		<tr>
			<th class='campaign'>
            	<label for="subject"><?php _e( 'Subject:', 'newsletter' ); ?></label> 
            </th>
            <td>
            	<input id="subject" name="subject" type="text" value="<?php if(isset($data)){echo $data->email_subject; }else{echo $subject;}?>" />
            </td>
		</tr>
		<tr>
			<th class='campaign'>
            	<label for="header"><?php _e( 'Header:', 'newsletter' ); ?></label> 
            </th>
            <td>
              <textarea rows="2" cols="65" name="header"><?php if(isset($data)){echo $data->email_header; }else{echo $header; }?></textarea> 
           </td>            
		</tr>
		<tr></tr>
		<tr>
		    <th class='campaign'>
            	<label for="template"><?php _e( 'Template:', 'newsletter' ); ?></label> 
             </th>
             <td>
		      <textarea rows="4" cols="65" name="template"><?php if(isset($data)){echo $data->email_template; }else{echo $template; }?></textarea>
		      <br />
		      <span>You can use the following tag on template:</span><br /><small>{TITLE}{DATE}{AUTHOR}{EXCERPT}{URL}</small>
		   </td> 
		</tr>
		<tr></tr>
		<tr>
		    <th class='campaign'>
            	<label for="footer"><?php _e( 'Footer:', 'newsletter' ); ?></label> 
          </th>
          <td>
		       <textarea rows="2" cols="65" name="footer"><?php if(isset($data)){echo $data->email_footer; }else{echo $footer; }?></textarea>
		   </td>
		</tr>
	</table><!-- .form-table -->
<?php	
}
?>
