<?php
/**
 * Plugin Name: CAHNRS Last Login
 * Plugin URI:  http://cahnrs.wsu.edu/communications
 * Description: Save Last Login adapted from http://wpdailybits.com/blog/capture-user-last-login-time/705 
 * Version:     0.1
 * Author:      Don Pierce
 * Author URI:  http://cahnrs.wsu.edu/communications
 * Text Domain:
 * Domain Path:
 * Network:
 * License:     GPLv3
 */

/*
* Create a page that shows all the users
* IMPORTANT: check for logged in and that they are an admin!
* Create a php file called users.php and add the same code used to generate the
* shortcode results. Again make sure to check for logged in and is admin. 
* use add_filter | template_include and $_GET params ( getusers & global ).
* in the filter function check for $_GET[getusers] if true set $template to your users.php file
* ( you have to pass a fully qualified path ). if global is set to true show all users for the network.
*/

add_action('wp_login','wpdb_capture_user_last_login', 10, 2);
function wpdb_capture_user_last_login($user_login){
	$user = get_user_by('login',$user_login);
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
}


add_filter( 'manage_users_columns', 'wpdb_user_last_login_column');
function wpdb_user_last_login_column($columns){
    $columns['lastlogin'] = __('Last Login', 'lastlogin');
    return $columns;
}

add_action( 'manage_users_custom_column',  'wpdb_add_user_last_login_column', 10, 3); 
function wpdb_add_user_last_login_column($value, $column_name, $user_id ) {
    if ( 'lastlogin' != $column_name )
        return $value;
 
    return get_user_last_login($user_id,false);
}
 
function get_user_last_login($user_id,$echo = true){
    $date_format = get_option('date_format') . ' ' . get_option('time_format');
    $last_login = get_user_meta($user_id, 'last_login', true);
    $login_time = 'Never logged in';
    if(!empty($last_login)){
       if(is_array($last_login)){
            $login_time = mysql2date($date_format, array_pop($last_login), false);
        }
        else{
            $login_time = mysql2date($date_format, $last_login, false);
        }
    }
    if($echo){
        echo $login_time;
    }
    else{
        return $login_time;
    }
}

function lastloginusers()
{
$args = array(
'meta_key' => 'last_login',
'orderby' => 'meta_value',
'order'=> 'DESC' );

/* 
* To Do: add shortcode attribute "global" to get all users
* in the network. Create an array of the current site's id. If global
* is set to true use wp get sites to add all site ids to your array.
* don't forget to do a check so you don't add the current twice ( in_array ).
* Loop ( foreach ) through all of the ids and set up the value of $args with the site id
* and then the following foreach loop on the users.
*/

$blogusers = get_users( $args ); // this is site specific - you can pass site id for multisite
// Array of stdClass objects.
$content = "<table class='authors-list'>";
foreach ( $blogusers as $user ) {
	$content .= "<tr><td>";
	$content .= esc_html( $user->display_name );
	$content .= '</td><td>';
	$content .= esc_html( get_user_last_login($user->ID, false));
	$content .= "</td></tr>";
	 		
//	echo '<span>' . esc_html( $user->display_name ) . ' - '. esc_html( get_user_last_login($user->ID, false)) .'</span><br />';
  }
  $content .= "</table>";
  return $content;
}
add_shortcode('lastlogusers', 'lastloginusers');
