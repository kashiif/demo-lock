<?php
/*
Plugin Name: Demo Lock
Plugin URI: http://arcsec.ca/
Description: Provides a secure environment in the wp-admin interface enabling users to test plugins in live demos.
Author: Colin Hunt
Author URI: http://arcsec.ca/
Version: 1.0.0
License: GNU General Public License v2.0 or later
License URI: http://opensource.org/licenses/gpl-license.php
*/

/**
Copyright 2013 Colin Hunt (Colin@arcsec.ca)
Portions Copyright 2012  Thomas Griffin  (email : thomas@thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
 
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
 
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( plugin_dir_path(__FILE__) . 'config.php' );

/**
 * Demo class for the WordPress plugin.
 *
 * It is a final class so it cannot be extended.
 *
 * @since 1.0.0
 *
 * @package Demo_Lock
 */
final class Demo_Lock {

	/**
	 * Holds user roles information.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private $role;
	
	/**
	 * Holds config variable information from config.php.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Constructor. Loads the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $demovars;
		$this->config = $demovars;
		/** Load the class */
		$this->load();
	
	}
	
	/**
	 * Hooks all interactions into WordPress to kickstart the class.
	 *
	 * @since 1.0.0
	 */
	private function load() {
	
		/** Hook everything into plugins_loaded */
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	
	}
	
  public static function activate() {
    //require_once( plugin_dir_path(__FILE__) . 'config.php' );

    global $demovars;

    $all_role_names = $demovars['role'];

    foreach($all_role_names as $role_name ) {
      $role = get_role( $role_name );

      if (isset($role)) {
      }
      else {
        // role does not exist, create it
        // and assign the read capability to this role.
        add_role( $role_name, self::id_to_name( $role_name ), array('read'=> true));
      }
    }

    $username = $demovars['username'];
    if (username_exists($username)) {
      // get the userid of existing user
      $user = get_user_by('login', $username);
      $user_id = $user->ID;

      update_user_meta( $user_id, 'role',  $all_role_names[0]);
    }
    else {
      // create a new user
      $user_id = wp_insert_user( array(
            'user_login'  => $username,
            'user_pass'   => $demovars['password'],
            'role'        => $all_role_names[0]
          ) ) ;
    }
  }

  private static function id_to_name ( $id )
  {
    $id = str_replace('_', ' ', $id);
    $id = ucwords($id);

    return $id;
  }


  public static function deactivate() {
    global $demovars;

    if (isset($demovars["keepuserenabled"]) && $demovars["keepuserenabled"] === true) {
      // Deactivate demo user
    }
  }

	/**
	 * In this method, we set any filters or actions and start modifying
	 * our user to have the correct permissions for demo usage.
	 *
	 * @since 1.0.0
	 */
	public function init() {
    // Show only if demouser is active
    add_filter( 'login_message', array( $this, 'login_message' ) );
    add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ) );
    add_action( 'login_head', array( $this, 'login_head' ) );
    add_filter( 'login_errors', array( $this, 'login_errors' ) );

		/** Don't process anything unless the current user is a demo user */
		if ( !$this->is_demo_user() ) {
      return;
    }

    foreach ($this->config['role'] as $role) {
      /** Setup capabilities for user roles */
      $this->role = get_role( $role );

      /** Add capabilities to the user */
      foreach ( $this->config['allow_capabilites'] as $cap ) {
        if ( ! current_user_can( $cap ) ) {
          $this->role->add_cap( $cap );
        }
      }
    }

    /** Load hooks and filters */
    add_action( 'wp_loaded', array( $this, 'cheatin' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ), 11 );
    add_filter( 'login_redirect', array( $this, 'redirect' ) );
    add_action( 'wp_dashboard_setup', array( $this, 'dashboard' ), 100 );
    add_action( 'admin_menu', array( $this, 'remove_menu_items' ) );
    add_action( 'admin_menu', array( $this, 'adjust_menu_items' ), 9 );
    add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar' ) );
    add_action( 'admin_footer', array( $this, 'jquery' ) );
    add_filter( 'admin_footer_text', array( $this, 'footer' ) );
    add_action( 'wp_footer', array( $this, 'jquery' ), 1000 );

    /*
    TODOs:

    Disable wordpress version: When you login to admin area, you can see the wordpress version in the footer.
                               We do not want this for demo user.
    Disable wordpress notifications: When you login to admin area, you can see update wordpress notification
                                     if your wordpress version is not the latest. This notification should not be visible
                                     to demo user.

    */

	}

  /******************************************* Remove Reset Password Feature ******************************************/
  // From: http://wordpress.org/support/topic/how-to-disable-password-reset-feature
  function allow_password_reset() {
    return false;
  }

  function remove_password_reset_text ( $text ) {
    if ( $text == 'Lost your password?' ) {
      $text = '';
    }
    return $text;
  }

  function login_head() {
    add_filter( 'gettext', array( $this, 'remove_password_reset_text' ) );
  }

  function login_errors( $text ) {
    return str_replace( 'Lost your password</a>?', '</a>', $text );
  }
  /********************************************************************************************************************/


  /**
	 * Make sure users don't try to access an admin page that they shouldn't.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 */
	public function cheatin() {
	
		global $pagenow;

		/** Paranoia security to make sure the demo user cannot access any page other than what we specify */

		/** If we find a user is trying to access a forbidden page, redirect them back to the dashboard */
		if (!in_array( $pagenow, $this->config['allow_pages']) || in_array($pagenow, $this->config['plugin_file']) && !($this->is_demo_area()) ) {
			wp_safe_redirect( get_admin_url() );
			exit;
		}	
	}
	
	/**
	 * Remove the ability for users to mess with the screen options panel.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
			
		add_filter( 'screen_options_show_screen', '__return_false' );

    /** Set the filters for allow saving options page*/
    $target_filters = $this->config['option_page_filters'];

    foreach($target_filters as $option_page => $new_capability) {
      $handler = new Demo_Lock_Capability_Handler($option_page, $new_capability);
      add_filter( "option_page_capability_$option_page", array($handler, 'option_page_capability'));
    }
	}

	/**
	 * Redirect the user to the Dashboard page upon logging in.
	 *
	 * @since 1.0.0
	 *
	 * @param string $redirect_to Default redirect URL (profile page)
	 * @return string $redirect_to Amended redirect URL (dashboard)
	 */
	public function redirect( $redirect_to ) {
	
		return get_admin_url();
	
	}
	
	/**
	 * Customize the login message with the demo username and password.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The default login message
	 * @return string $message Amended login message
	 */
	public function login_message( $message ) {

		$message = '<div style="font-size: 15px; margin-left: 8px; text-align: center;">';
		$message .= '<p>In order to gain access to the demo area, use the login credentials below:</p><br />';
		$message .= '<strong>Username: </strong> <span style="color: #cc0000;">' . $this->config['username'] . '</span><br />';
		$message .= '<strong>Password: </strong> <span style="color: #cc0000;">' . $this->config['password'] . '</span><br /><br />';
		$message .= '</div>';
		
		return $message;
	
	}
	
	/**
	 * If the user is not an admin, set the dashboard screen to one column 
	 * and remove the default dashbord widgets.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 */
	public function dashboard() {
	
		global $pagenow;
		$layout = get_user_option( 'screen_layout_dashboard', get_current_user_id() );
		wp_add_dashboard_widget('dashboard_widget', $this->config['plugin_title'], array('Demo_Lock', 'dashboard_widget_function'));
		/** Set the screen layout to one column in the dashboard */
		if ( 'index.php' == $pagenow && 1 !== $layout )
			update_user_option( get_current_user_id(), 'screen_layout_dashboard', 1, true );
			
		/** Remove dashboard widgets from view */
		$meta_boxes = $this->config['remove_meta_box'];
		for ($i =0; $i < count($meta_boxes); $i++) {
      $mb = $this->config['remove_meta_box'][$i];
			remove_meta_box($mb[0], $mb[1], $mb[2]);
		}
	}
	
	/**
	 * Remove certain menu items from view so demo user cannot mess with them.
	 *
	 * @since 1.0.0
	 *
	 * @global array $menu Current array of menu items
	 */
	public function remove_menu_items() {
	
		global $menu;
		end( $menu );
		
		/** Remove the first menu separator */
		unset( $menu[4] );
		
		/** Now remove the menu items we don't want our user to see */
		$remove_menu_items = $this->config['remove_menu'];
	
		while ( prev( $menu ) ) {
			$item = explode( ' ', $menu[key( $menu )][0] );
			if ( in_array( $item[0] != null ? $item[0] : '', $remove_menu_items ) ) {
				unset( $menu[key( $menu )] );
      }
		}
		foreach($this->config['remove_submenu'] as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $subval) {
					remove_submenu_page($key, $subval);
				}
			} else {
        remove_submenu_page($key, $val);
			}
		}
	}

  /**
   * Adjust certain menu items.
   *
   * @since 1.0.0
   *
   * @global array $menu Current array of menu items
   */
  public function adjust_menu_items() {

    global $menu;

    /** Now remove the menu items we don't want our user to see */
    $target_menues = $this->config['menu_modifications'];

    foreach($target_menues as $key => $menu_config) {
      $parent_menu = &$menu[ $key ];

      if (!isset($parent_menu)) {
        error_log("menu for $key specified in menu_modifications does not exist.");
        continue;
      }

      $parent_menu[ $menu_config[0] ] = $menu_config[1];

    }
  }

	/**
	 * Modify the admin bar to remove unnecessary links.
	 *
	 * @since 1.0.0
	 *
	 * @global object $wp_admin_bar The admin bar object
	 */
	public function admin_bar() {
	
		global $wp_admin_bar;

		/** Remove admin bar menu items that demo users don't need to see or access */
		foreach ($this->config['remove_node'] as $node) {
			$wp_admin_bar->remove_node($node);
		}
		/**Add custom nodes to admin bar*/
		for ($i=0; $i < count($this->config['add_node']); $i++) {
			$add = $this->config['add_node'][$i];
			$wp_admin_bar->add_node($add);
		}
	}
	
	/**
	 * We can't filter the Profile URL for the main account link in the admin bar, so we
	 * replace it using jQuery instead. We also remove the "+ New" item from the admin bar.
	 *
	 * This method also adds some extra text to spice up the currently empty dashboard area.
	 * Call it plugin marketing if you will. :-)
	 *
	 * @since 1.0.0
	 */
	public function jquery() {
	
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				/** Remove items from the admin bar first */
				$('#wp-admin-bar-my-account a:first').attr('href', '<?php echo get_admin_url(); ?>');
				$('#wp-admin-bar-view').remove();
				
				/** Customize the Dashboard area */
				$('.index-php #normal-sortables').fadeIn('fast', function(){
					/** Change width of the container */
					$(this).css({ 'height' : 'auto' });
				});
			});
		</script>
		<?php

	}
	
	/**
	 * Modify the footer text for the demo area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The default footer text
	 * @return string $text Amended footer text
	 */
	public function footer( $text ) {
		return $this->config['footer'];
			
	}

	/**
	 * Helper function for determining whether the current user is a demo user or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether or not the user is a demo
	 */
	private function is_demo_user() {

    $current_user = wp_get_current_user();

    if ( empty( $current_user ) || empty( $current_user->data ))
      return false;

    return ($current_user->data->user_login == $this->config['username']);
	}
	
	/**
	 * Helper function for determining whether the current page is in the demo area.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Demo area or not
	 */
	private function is_demo_area() {
		$req = $this->config['getvar'];
		foreach ($req as $key => $val) {
			if (!empty($_REQUEST[$key]) && $_REQUEST[$key] === $val) {
				return true;
			}
		}
		return false;
	}


  // Function that outputs the contents of the dashboard widget
  public static function dashboard_widget_function() {
    global $demovars;
    echo $demovars['dashboard_text'];
  }

}

class Demo_Lock_Capability_Handler {
  private $page;
  private $capability;

  public function Demo_Lock_Capability_Handler($page, $capability) {
    $this->page = $page;
    $this->capability = $capability;
  }

  public function option_page_capability($capability) {
    return $this->capability;
  }

}



// Register hooks that are fired when the plugin is activated and deactivated.
register_activation_hook( __FILE__,  array( 'Demo_Lock', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Demo_Lock', 'deactivate' ) );


/** Instantiate the class */
$demolock = new Demo_Lock();