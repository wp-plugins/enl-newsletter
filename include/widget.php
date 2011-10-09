<?php
class Enl_Newsletter_Widget extends WP_Widget
{
	/**
	* Declares the Enl_Newsletter_Widget class.
	*
	*/
	function Enl_Newsletter_Widget(){
		$widget_ops = array('classname' => 'widget_enl_newsletter', 'description' => __( "Add a newsletter signup form") );
		$control_ops = array('width' => 250, 'height' => 250);
		$this->WP_Widget('newsletter', __('Enl Newsletter'), $widget_ops, $control_ops);
	}
	
	/**
	* Output the email form
	* 
	*/ 
	function output_form(){
   		 if(!isset($_POST['enl_submit'])) { //form has not been submitted yet 
			
			$output .= "<form class=\"enl-form\" id=\"enl-form\" action=\"\" method=\"post\">";
			/*	
			if(isset($options['subscribe_with_name']) && $options['subscribe_with_name'] == 1) {	
				$output .= "<p><label for=\"nsu-name\">name</label><input class=\"nsu-field\" id=\"nsu-name\" type=\"text\" name=\"nsu-name\" /></p>";		
			} 
			*/				
			$output .= "<p><label for=\"enl-email\">Email</label></p><p><input class=\"enl-field\" id=\"enl-email\" type=\"text\" name=\"enl_email\" /></p>";
			$output .= "<p><input type=\"submit\" id=\"enl-submit\" class=\"enl-submit\" name=\"enl_submit\" value=\"submit\" /></p>";
			$output .= "</form>";
				
   		} else { // form has been submitted
		
			$output = "<p id=\"enl-signed-up\">Thanks for your signup</p>";		
				
	   }
	  return $output;
	}
	
	/**
	* Displays the Widget
	*
	*/
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$lineOne = empty($instance['lineOne']) ? '' : $instance['lineOne'];
		$output = $this->output_form();
		# Before the widget
		echo $before_widget;
		
		# The title
		if ( $title )
			echo $before_title . $title . $after_title;
		
		# Make the Hello World Example widget
		echo '<div>' . $lineOne . '<br />' . $output . '</div>';
		
		# After the widget
		echo $after_widget;
	}
	
	/**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['lineOne'] = strip_tags(stripslashes($new_instance['lineOne']));
			
		return $instance;
	}
	
	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'', 'lineOne'=>'') );
		
		$title = htmlspecialchars($instance['title']);
		$lineOne = htmlspecialchars($instance['lineOne']);
				
		# Output the options
		echo '<p style="text-align:left;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <br /><input style="width: 220px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
		# Text line 1
		echo '<p style="text-align:left;"><label for="' . $this->get_field_name('lineOne') . '">' . __('Text:') . ' <br /><input style="width: 220px;" id="' . $this->get_field_id('lineOne') . '" name="' . $this->get_field_name('lineOne') . '" type="text" value="' . $lineOne . '" /></label></p>';
	}

}// END class
?>
