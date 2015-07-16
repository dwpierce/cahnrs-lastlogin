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

$blogusers = get_users( $args );
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