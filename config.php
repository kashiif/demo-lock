<?php
/**
Copyright 2013 Colin Hunt (Colin@arcsec.ca)

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

global $demovars;

//Global variables for easy customization
/**Set Plugin Name*/
$demovars['plugin_title'] = 'Affiliate Links';

/**Set the file name(s) that are used to directly access your plugin */
$demovars['plugin_file'] = array('abz-affiliate-links');

/**If you are restricting users to a page with a specific argument, such as themes.php?page=plugin_name,
set the variable below in the format ['getvar']['page'] = 'plugin_name'; set as many getvars as you need */
$demovars['getvar']['page'] = 'abz-affiliate-links-options';
$demovars['getvar']['post_type'] = 'affiliatelink';

/**Add user roles to set restricted capabilities for*/
$demovars['role'] = array('affiliate_links_admin');

/**Add Capabilities that will be applied to each of the above specified roles*/
$demovars['allow_capabilites'] = array( 'manage_options_al',
                                        'read_post_al',
                                        'edit_post_al',
                                        'delete_post_al',
                                        'edit_post_als',
                                        'edit_others_post_als',
                                        'publish_post_als',
                                        'delete_post_als',
                                        'delete_private_post_als',
                                        'delete_published_post_als',
                                        'delete_others_post_als',
                                        'edit_private_post_als',
                                        'edit_published_post_als');

/**Set demo login username*/
$demovars['username'] = 'AffiliateLinksDemo';

/**Set demo login password*/
$demovars['password'] = 'demo123';

/**Insert HTML markup to include on main dashboard page*/
$demovars['dashboard_text'] = <<<DASH
<p style="font-size: 15px; font-weight: bold; line-height: 22px;">Within this demo area, you have access to all the capabilites available in My Plugin. Just follow the simple instructions below to get started:</p>
<ol style="font-size: 13px">
<li>Navigate to My Plugin</li>
<li>Select My Option from the dropdown box.</li>
<li>Give your selection a title and then click ok.</li>
</ol>
DASH;

/**Set footer text*/
$demovars['footer'] = sprintf( __( 'You are currently using a demo of My Plugin. Like what you see? <a href="%s" title="Click here to download!" target="_blank">Download My Plugin today!</a>' ), 'http://mypluginurl.com' );

/**Set which php files you want your demo users to be allowed to access*/
$demovars['allow_pages'] = array('index.php', 'wp-login.php', 'options-general.php', 'admin-ajax.php', 'edit.php', 'post.php', 'post-new.php', 'options.php');


/**filters for saving options*/
$demovars['option_page_filters'] = array(
  // filter_name => capability (options.php:43)
  'abz-affiliate-links-general-settings-group' => 'manage_options_al',
  'abz-affiliate-links-default-settings-group' => 'manage_options_al'
);

/**
 * Modify admin menu. Following will change 'Settings' menu capability to 'manage_options_gps'
 * Read it as
 *  $menu_cfg = $menu[80];
 *  $menucfg[1] = 'manage_options_gps';
 **/
$demovars['menu_modifications'] = array(
    //'80' => array(1, 'manage_options_al')
  );

/**Set which admin bar items you want to remove*/
$demovars['remove_node'] = array(
                                  //'wp-logo',
                                  //'site-name',
                                  'new-content',
                                  'comments',
                                  'user-info',
                                  'edit-profile'
                                );

/**Set any additional options you want to add to the admin bar. add internal array for each additional node*/
$demovars['add_node'] = array(
	//array('id' => 'demo_site', 'title' => 'Demo Site', 'href' => home_url())
);

/**Specify which menu items you want to remove*/
$demovars['remove_menu'] = array(
                                  __( 'Posts' ),
                                  //__( 'Pages' ),
                                  //__( 'Media' ),
                                  __( 'Comments' ),
                                  __( 'Profile' ),
                                  __( 'Tools' ),
                                  //__( 'Appearance' )
                                );

/**Specify which submenu items you want to remove, in the format: [(string) menu] => [(array || string) submenus]*/
$demovars['remove_submenu'] = array(
	'themes.php' => array('widgets.php', 'nav-menus.php', 'themes.php'), 
	'profile.php' => 'profile.php' 
);

/**Specify which meta boxes you want to remove from the dashboard; add an internal array with 3 values for each item in the order: $id, $page, $context */
$demovars['remove_meta_box'] = array(
  //*
	array('dashboard_right_now', 'dashboard', 'normal'), 
	array('dashboard_recent_comments', 'dashboard', 'normal'), 
	array('dashboard_incoming_links', 'dashboard', 'normal'), 
	array('dashboard_plugins', 'dashboard', 'normal'), 
	array('dashboard_quick_press', 'dashboard', 'side'), 
	array('dashboard_recent_drafts', 'dashboard', 'side'), 
	array('dashboard_primary', 'dashboard', 'side'), 
	array('dashboard_secondary', 'dashboard', 'side')
  //*/
);

/** Plugin will by default deactivate the demo user. If you don't want so, uncomment the following line.*/
//$demovars["keepuserenabled"]

?>