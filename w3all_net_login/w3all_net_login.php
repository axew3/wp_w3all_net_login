<?php
/**
 * @package w3all_net_login
 * @version 1.0.0
 */
/*
Plugin Name: w3all net Login
Plugin URI: http://wordpress.org/plugins/w3all-net-login/
Description: Autologin and/or autoregister users into a WordPress (or many) linked with a main WordPress
Author: axew3
Version: 1.0.0
Author URI: http://www.axew3.com/w3
License: GPLv2 or later
Text Domain: w3all-net-login
*/

defined( 'ABSPATH' ) or die( 'forbidden' );
 if ( !function_exists( 'add_action' ) ) {
  die( 'forbidden' );
 }

define( 'WPW3ALLNETLOGIN_VERSION', '1.0.0' );
define( 'WPW3ALLNETLOGIN_MINIMUM_WP_VERSION', '6.0' );
define( 'WPW3ALLNETLOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPW3ALLNETLOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

  ob_start();
   include( WPW3ALLNETLOGIN_PLUGIN_DIR.'config.php' );
  ob_end_clean();
   require_once( WPW3ALLNETLOGIN_PLUGIN_DIR . 'class.wp.w3all-netlogin.php' );

 function w3all_init_net_slave(){
  $w3all_net = new WP_w3all_net_login();
  $w3all_net->w3all_net_db_tab_create();
  $w3all_net->net_get_master_response(); // before this
  if( !isset($_GET['w3allNetCookieId']) && !isset($_GET['w3allNetUlog']) ){
   $w3all_net->net_setcookie(); // then this
  }
 }

 function w3all_init_net_master(){
  $w3all_net = new WP_w3all_net_login();
  $w3all_net->net_get_slave_request();
 }

 /* function w3all_net_out_onfooter(){

 } */

 function w3all_wp_login_net_redir( $user_login, $user ) {
   if( isset($_COOKIE["w3all_netBackTo"]) )
   { // get the cookie val -> remove -> redirect
     $r = trim(str_replace(chr(0), '', base64_decode($_COOKIE["w3all_netBackTo"])));
     if( filter_var($r, FILTER_VALIDATE_URL) )
     {
      $d = ($_SERVER['HTTP_HOST'] != 'localhost' OR $_SERVER['HTTP_HOST'] != '::1' OR $_SERVER['HTTP_HOST'] != '127.0.0.1') ? $_SERVER['HTTP_HOST'] : false;
      setcookie ("w3all_netBackTo", "", 1, "/", $d);
      setcookie("w3all_netBackTo", "", [ "expires" => 1, "path" => "/", "domain" => $d, "secure" => 1, "httponly" => false, "samesite" => 'None' ]);
      wp_redirect( $r );
      exit;
     }
   }
 }


 // Slave
if( THIS_WPW3NET_IS_SLAVE === true ){
 add_action( 'init', 'w3all_init_net_slave' );
 //add_action( 'wp_footer', 'w3all_net_out_onfooter' );
}

// Master
if( THIS_WPW3NET_IS_SLAVE === false ){
 add_action( 'init', 'w3all_init_net_master' );
 add_action( 'wp_login', 'w3all_wp_login_net_redir', 10, 2 );
}
