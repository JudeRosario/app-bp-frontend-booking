<?php
/**
 * Plugin Name: BuddyPress + Appointments Booking Integration
 * Version: 1.1.3
 * Author: Jude Rosario (WPMU DEV)
 * Description: Lets users book appointments from the BuddyPress Profile Page of a Service Provider. Others see the My Appointments table
 * Author URI: http://premium.wpmudev.org/
 */

if(!class_exists('BuddyPress_Front_End_Booking')):
	class BuddyPress_Front_End_Booking {

	
	var $appointments ; 

	// Constructor, binds a handle to global Appointments object and some init code.  
		function __construct ($appointments) { 
			$this->appointments = $appointments ; 
			$this->appointments->load_scripts_styles();
			$this->add_hooks() ; 
		}

	// Hooks into psts_setting_checkout_url and fixes the url
		private function add_hooks () {
	
		// Admin hooks
		
		add_action( 'app-settings-display_settings' , array( $this, 'inject_admin_settings' ) );
		add_filter( 'app-options-before_save' , array( $this, 'save_bp_tab_name' ) );


		// Front end hooks

		if($this->appointments->options['separate_bp_tab'] == 'yes')
			add_action('bp_setup_nav', array( $this,'inject_nav') );
		else	
			add_action('bp_after_profile_loop_content', array( $this,'inject_booking_html') );
		}

	function inject_nav() {
		global $bp;
			bp_core_new_nav_item(array(
				'name' => $this->appointments->options['tab_title'] ? 
						$this->appointments->options["tab_title"] : "Book an Appointment",
				'slug' => 'book-appointment',
				'default_subnav_slug' => 'book-appointment',
				'screen_function' => array( $this,  'add_bookings_tab'),
				'position' => 50
				));
	}

	function add_bookings_tab() {
		add_action( 'bp_template_content', array( $this,  'inject_booking_html' ) );
    	bp_core_load_template( 'template_content' );
	}


	// Displays the Standard Appointments + Workflow
	function inject_booking_html() {
		$worker = bp_displayed_user_id() ; 
		$user = get_current_user_id() ;
?>
		<h3><?php echo $this->appointments->options["tab_title"];  ?></h3>
<?
		if( appointments_is_worker($worker) ) : 
			if ( $worker != $user ):
?>
			<table>
				<tbody>
				<tr>
					<td> <?php echo do_shortcode('[app_my_appointments allow_cancel="1" status="paid, confirmed, pending, completed, removed, reserved" ]');?>  </td>				
				</tr>
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
			endif;

			if ( $worker == $user ):
?>
				<table>
					<tr>	
						<td> <?php echo do_shortcode('[app_my_appointments provider="1" _allow_confirm = "1"]');?>  </td>
					</tr>
				</table>
<?
			endif;
		endif; 


		if( ! appointments_is_worker($worker) ) : 
			if ( $worker == $user ):
?>
				<table>
					<tbody>
						<tr>
							<td> <?php echo do_shortcode('[app_my_appointments allow_cancel="1" ]');?>  </td>				
						</tr>			
					</tbody>
				</table>	
<?
			endif;

			if ( $worker != $user ):
				echo "Sorry this user does not provide any services on our site" ;
			endif;

		endif; 

	}
	// Injects settings into the admin end
		function inject_admin_settings () {
		?>
				<tr>
					<th scope="row" ><?php _e('BuddyPress Profile Page Options', 'appointments')?></th>
				</tr>
				<tr valign="top">
					<th scope="row" ><?php _e('Display as a separate tab ? ', 'appointments')?></th>
					<td colspan="2">
					<select name="separate_bp_tab">
					<option value="no" <?php if ( @$this->appointments->options['separate_bp_tab'] <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'appointments')?></option>
					<option value="yes" <?php if ( @$this->appointments->options['separate_bp_tab'] == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'appointments')?></option>
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

	// Saves the tab name specified in the admin. 

		function save_bp_tab_name( $options ) {

			$separate_bp_tab = isset($_POST["separate_bp_tab"]) ? 
				$_POST["separate_bp_tab"] : $this->appointments->options["separate_bp_tab"];	


			$tab_title = isset($_POST["tab_title"]) ? 
				$_POST["tab_title"] : $this->appointments->options["tab_title"];	

			// Perform any charset related opertations here
			$options = array_merge($options, array('tab_title' => $tab_title, 'separate_bp_tab' => $separate_bp_tab));	
			return $options;
		}


	}

endif;

// Check if the base plugin is installed before activating the addon 
add_action('plugins_loaded', 'init_bp_feb') ;

	function init_bp_feb () {
		if (class_exists('Appointments'))
		{	
			global $appointments ; 
			new BuddyPress_Front_End_Booking($appointments) ; 
		}
	}
?>
