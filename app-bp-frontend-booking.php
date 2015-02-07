<?php
/**
 * Plugin Name: BuddyPress + Appointments Booking Integration
 * Version: 1.0.0
 * Author: Jude Rosario (WPMU DEV)
 * Author URI: http://premium.wpmudev.org/
 */

add_action('plugins_loaded', 'init_addon') ;

// Entry Point to the addon 
	function init_addon() {
		if (class_exists('Appointments')): 			
			global $appointments;
				// Admin hooks
				add_action('app-settings-display_settings','inject_admin_settings');
				add_filter('app-options-before_save','save_bp_tab_name');


				// Front end hooks
				$appointments->load_scripts_styles();

				if($appointments->options['separate_bp_tab'] == 'yes')
					add_action('bp_setup_nav', 'inject_nav');
				else	
					add_action('bp_after_profile_loop_content','inject_booking_html');
		endif; 
	}

	function inject_nav() {
		global $bp;
		global $appointments;
			bp_core_new_subnav_item(array(
				'name' => $appointments->options['tab_title'] ? $appointments->options["tab_title"] : "Book an Appointment",
				'slug' => 'book-appointment',
				'parent_url' => trailingslashit( bp_displayed_user_domain() . 'profile' ),
				'parent_slug' => 'profile',
				'screen_function' => 'add_bookings_tab',
				'position' => 50
				));
	}

	function add_bookings_tab() {
		add_action( 'bp_template_content', 'inject_booking_html' );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}


// Displays the Standard Appointments + Workflow
	function inject_booking_html() {
		global $appointments;
		$worker = bp_displayed_user_id() ; 
		
		if($appointments->is_worker($worker)) : 
		?>
	     	<h3><?php echo $appointments->options["tab_title"];  ?></h3>
				<table>
					<tbody>
					<tr>
						<td> <?php echo do_shortcode('[app_services worker= "'.$worker.'"]');?>  </td>
					</tr>
					<tr>
						<td colspan="2"><?php echo do_shortcode('[app_monthly_schedule worker= "'.$worker.'"]');?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo do_shortcode('[app_pagination month="1"]');?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo do_shortcode('[app_login]');?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo do_shortcode('[app_confirmation]');?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo do_shortcode('[app_paypal]');?></td>
					</tr>
					</tbody>
				</table>
		<?
		endif ; 

	}

	function inject_admin_settings () {
		global $appointments;
	?>
			<tr>
				<th scope="row" ><?php _e('BuddyPress Profile Page Options', 'appointments')?></th>
			</tr>
			<tr valign="top">
				<th scope="row" ><?php _e('Display as a separate tab ? ', 'appointments')?></th>
				<td colspan="2">
				<select name="separate_bp_tab">
				<option value="no" <?php if ( @$appointments->options['separate_bp_tab'] <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'appointments')?></option>
				<option value="yes" <?php if ( @$appointments->options['separate_bp_tab'] == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'appointments')?></option>
				</select>
				<span class="description"><?php _e('Whether to send an email after confirmation of the appointment. Note: Admin and service provider will also get a copy as separate emails.', 'appointments') ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e('Tab title in BP profile page', 'appointments') ?></th>
				<td colspan="2">
				<input type="text" style="width:250px" name="tab_title" value="<?php if ( isset($appointments->options["tab_title"]) ) echo $appointments->options["tab_title"] ?>" />
				<br />
				<span class="description"><?php _e('You can add put the title here', 'appointments') ?></span>
				</td>
			</tr>
	<?
	}

	function save_bp_tab_name( $options ) {	
		global $appointments;

		$separate_bp_tab = isset($_POST["separate_bp_tab"]) ? 
			$_POST["separate_bp_tab"] : $appointments->options["separate_bp_tab"];	


		$tab_title = isset($_POST["tab_title"]) ? 
			$_POST["tab_title"] : $appointments->options["tab_title"];	

		// Perform any charset related opertations here
		$options = array_merge($options, array('tab_title' => $tab_title, 'separate_bp_tab' => $separate_bp_tab));	
		return $options;
	}

?>