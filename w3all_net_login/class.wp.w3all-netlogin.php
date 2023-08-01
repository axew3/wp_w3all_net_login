<?php
# Copyright (C) 2023 - axew3.com
 defined( 'ABSPATH' ) or die( 'forbidden' );

class WP_w3all_net_login {

 protected $wp_db_conn_val;
 protected $wp_db_conn;
 protected $wp_user_session;
 protected $netUrl;
 protected $netTokenFull;
 protected $netTokenByte;
 protected $netCookieId;
 protected $netCookieDomain;
 protected $netUserAgent;
 protected $netUserIp;
 protected $netdbtab;

function __construct() {
 global $wpdb;
 $this->netTokenFull = str_shuffle( strtoupper(bin2hex(random_bytes(mt_rand(50,70)))).bin2hex(random_bytes(mt_rand(70,90))) );
 $this->netdbtab = $wpdb->prefix . 'w3all_netlogin';
 $this->wp_db_conn_val = '';
 $this->wp_db_conn = '';
 $this->wp_user_session = '';
 $this->netUrl = WPW3NET_0_URL;
 $this->netTokenFull = $this->netTokenFull;
 $this->netTokenByte = substr($this->netTokenFull, 20,40);
 $this->netCookieId = str_replace('.','',uniqid('', true));
 $this->netCookieDomain = ($_SERVER['HTTP_HOST'] != 'localhost' OR $_SERVER['HTTP_HOST'] != '::1' OR $_SERVER['HTTP_HOST'] != '127.0.0.1') ? $_SERVER['HTTP_HOST'] : false;
 $this->netUserAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : 'unknown';
 $this->netUserIp = !filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ? '127.0.0.1' : trim($_SERVER['REMOTE_ADDR']);
}

  public function net_setcookie(){
    global $wpdb;

      if( isset($_GET['w3allNetCookieId']) OR isset($_GET['w3allNetUlog']) )
       {
        return;
       }

     if( isset($_COOKIE["w3all_netTokenFull"]) && preg_match('/[^0-9A-Za-z]/',$_COOKIE["w3all_netTokenFull"])
         OR isset($_COOKIE["w3all_netCookieId"]) && preg_match('/[^0-9A-Za-z]/',$_COOKIE["w3all_netCookieId"]) )
      { return false; }

    if( ! is_user_logged_in() ){

      if( !isset($_COOKIE["w3all_netTokenFull"]) OR !isset($_COOKIE["w3all_netCookieId"]) )
      {
        // (random) clean up, remove unwanted generated (wp) tokens and real expired tokens
         $exp = time()-30;
         $wpdb->query("DELETE FROM $this->netdbtab WHERE time < $exp");

         $wpdb->query("INSERT INTO $this->netdbtab (netTokenId,netTokenFull,userdata,time,browser,ip,netBackTo) VALUES ('$this->netCookieId', '$this->netTokenFull', '', '".time()."', '".esc_sql($this->netUserAgent)."', '".esc_sql($this->netUserIp)."', '".esc_sql(home_url())."')");

         setcookie("w3all_netTokenFull", $this->netTokenFull, [ 'expires' => 0, 'path' => '/', 'domain' => $this->netCookieDomain, 'secure' => 1, 'httponly' => false, 'samesite' => 'None' ]);
         setcookie("w3all_netCookieId", $this->netCookieId, [ 'expires' => 0, 'path' => '/', 'domain' => $this->netCookieDomain, 'secure' => 1, 'httponly' => false, 'samesite' => 'None' ]);

      }

        //$nt = stripslashes(htmlspecialchars($this->netTokenByte, ENT_COMPAT));
        //$ntbc = password_hash($nt, PASSWORD_BCRYPT,['cost' => 12]);
        //$data = array( 'w3allNetCookieId' => $this->netCookieId, 'w3allNetTokenByte' => $ntbc );
        //return WP_w3all_net_login::net_curl($data);

       if( isset($_COOKIE["w3all_netTokenFull"]) && isset($_COOKIE["w3all_netCookieId"]) )
       {
        $this->netCookieId = $_COOKIE["w3all_netCookieId"];
        $this->netTokenByte = substr($_COOKIE["w3all_netTokenFull"], 20,40);
        $nt = stripslashes(htmlspecialchars($this->netTokenByte, ENT_COMPAT));
        $ntbc = password_hash($nt, PASSWORD_BCRYPT,['cost' => 12]);
       }

        if( $this->netUrl[-1] != '/' ){ $this->netUrl = $this->netUrl.'/'; }
        $toM = '?w3allNetCookieId='.$this->netCookieId.'&w3allNetTokenByte='.base64_encode($ntbc);
        //header("Location: $this->netUrl$toM");
        wp_redirect( $this->netUrl.$toM );
        exit;

     } # END if( ! is_user_logged_in() )

    if( is_user_logged_in() ){

      if(isset($_GET["w3allNetCookieId"])){
        $dBy = $_GET["w3allNetCookieId"];
        } elseif(isset($_COOKIE["w3all_netCookieId"])){
          $dBy = $_COOKIE["w3all_netCookieId"];
        }

     if(isset($dBy)){
      $wpdb->query("DELETE FROM $this->netdbtab WHERE netTokenId = '".$dBy."'");
     }

        setcookie ("w3all_netTokenFull", "", 1, "/", $this->netCookieDomain);
        setcookie ("w3all_netCookieId", "", 1, "/", $this->netCookieDomain);
        setcookie("w3all_netTokenFull", "", [ "expires" => 1, "path" => "/", "domain" => $this->netCookieDomain, "secure" => 1, "httponly" => false, "samesite" => 'None' ]);
        setcookie("w3all_netCookieId", "", [ "expires" => 1, "path" => "/", "domain" => $this->netCookieDomain, "secure" => 1, "httponly" => false, "samesite" => 'None' ]);
     }

  }


  public function net_get_master_response() {
    global $wpdb;

   if( !isset($_GET['w3allNetCookieId']) ){ return; }

      $_COOKIE = array_map("trim", $_COOKIE);
      $_GET = array_map("trim", $_GET);

   if(    isset($_COOKIE["w3all_netTokenFull"]) && preg_match('/[^0-9A-Za-z]/',$_COOKIE["w3all_netTokenFull"])
       OR isset($_COOKIE["w3all_netCookieId"]) && preg_match('/[^0-9A-Za-z]/',$_COOKIE["w3all_netCookieId"])
       OR isset($_GET['w3allNetCookieId']) && preg_match('/[^0-9A-Za-z]/', $_GET['w3allNetCookieId']) )
     { return false; }

  if( ! is_user_logged_in() )
  {
     // Setup an anti bruteforce
     $net_ck = $wpdb->get_row("SELECT * FROM $this->netdbtab WHERE netTokenId = '".$_GET['w3allNetCookieId']."'");

     if( empty($net_ck) ){ return; }

     // Security check: the 'userdata' field contains the user's data inserted by the master, because the user was found logged in during the redirect to the master site?
     // Seem that it is time to add (if not exist) and login this user, if the case
   if( $_COOKIE["w3all_netTokenFull"] === $net_ck->netTokenFull && !empty($net_ck->userdata) )
   {
      $net_ck->userdata = unserialize($net_ck->userdata);

      if( isset($net_ck->userdata->data->user_email) && is_email($net_ck->userdata->data->user_email) )
       {

        $user_id = email_exists( $net_ck->userdata->data->user_email );
        $userid_uname = username_exists( $net_ck->userdata->data->user_login );

      // If email or username do not exists, add the user into this SLAVE WP
        if ( ! $user_id && ! $userid_uname )
        {
           $role = !empty($net_ck->userdata->roles[0]) ? $net_ck->userdata->roles[0] : 'subscriber';
           $userdata = array(
               'user_login'       =>  $net_ck->userdata->data->user_login,
               'user_pass'        =>  $net_ck->userdata->data->user_pass,
               'user_email'       =>  $net_ck->userdata->data->user_email,
               'user_registered'  =>  $net_ck->userdata->data->user_registered,
               'role'             =>  $role,
               'display_name'     =>  $net_ck->userdata->data->display_name,
               'user_url'         =>  $net_ck->userdata->data->user_url,
               );

           $user_id = wp_insert_user( $userdata );

          if ( is_wp_error( $user_id ) ) {
           return; // ADD: throw message error here
          }

        }

         if( ! is_user_logged_in() && ! is_wp_error( $user_id ) && $user_id > 0 )
         {
           $user = get_user_by('ID', $user_id);
           wp_set_current_user($user->ID, $user->user_login);
           wp_set_auth_cookie($user->ID, true);
           do_action('wp_login', $user->user_login, $user);

           $wpdb->query("DELETE FROM $this->netdbtab WHERE netTokenId = '".$_COOKIE["w3all_netCookieId"]."'");
           setcookie ("w3all_netTokenFull", "", 1, "/", $this->netCookieDomain);
           setcookie ("w3all_netCookieId", "", 1, "/", $this->netCookieDomain);

             wp_redirect( esc_url(home_url( '/?w3allNetUlog=1')) ); exit();
             wp_redirect( home_url() ); exit();
         }
     }
    }
   }
 }


 function net_get_slave_request(){

   if( !empty($_GET['w3allNetCookieId']) && !empty($_GET['w3allNetTokenByte']) )
   {
      $_GET = array_map("trim", $_GET);

     //if ( preg_match('/[^0-9A-Za-z]/', $_GET['w3allNetCookieId']) OR preg_match('/[^0-9A-Za-z]/', base64_decode($_GET['w3allNetTokenByte'])) ){
     if ( preg_match('/[^0-9A-Za-z]/', $_GET['w3allNetCookieId']) ){
        //header("Location: $this->netUrl");
        echo 'The w3allNetCookieId do not match!'; //
        exit;
     }

    // connect and update the user db row state into Slave
    $w3all_query = WP_w3all_net_login::w3all_net_wpdb();
    $net_ck = $w3all_query->get_row("SELECT * FROM ".WPW3NET_0_DB_TABPREFIX."w3all_netlogin WHERE netTokenId = '".$_GET['w3allNetCookieId']."'");

    if( is_user_logged_in() )
    {
         if( !empty($net_ck) )
         {

           if( $net_ck->browser != $this->netUserAgent OR $net_ck->ip != $this->netUserIp )
           {
             echo 'Useragent or Ip do not match!'; //
             exit; // provide a link or a redirect, with warning
           }

            $netTokenByteDb  = substr($net_ck->netTokenFull, 20,40);
            $netTokenByteGet = trim(base64_decode($_GET['w3allNetTokenByte']));
            $netTokenByteGet = str_replace(chr(0), '', $netTokenByteGet);

            if( ! password_verify($netTokenByteDb, $netTokenByteGet) )
             { echo 'The netTokenByte DO NOT MATCH!'; exit; }  // provide a link or a redirect, with warning
              else
             {
              $current_user = serialize(wp_get_current_user());
              $w3all_query->query("UPDATE ".WPW3NET_0_DB_TABPREFIX."w3all_netlogin SET userdata = '$current_user' WHERE netTokenId = '".$_GET['w3allNetCookieId']."'");

              $redir = $net_ck->netBackTo;
              if( $redir[-1] == '/' ){ $redir = substr($redir, 0, -1); }
              $vars = '/?w3allNetCookieId='.$_GET['w3allNetCookieId'];
              $rv = $redir.$vars;
               // header( "refresh:3;url=$rv" );
              header( "Location: $rv" ); exit;
               // echo "<br />The Token match! <h3>You'll be redirected in about 3 secs. If not, please click <a href=$rv>here</a></h3>";
               // exit;
             }

           return;
         }
     } # END if( is_user_logged_in() )


     if( !is_user_logged_in() )
     {

        if( !empty($net_ck) )
         {
           $redir = $net_ck->netBackTo;
             if( $redir[-1] == '/' ){ $redir = substr($redir, 0, -1); }
           setcookie("w3all_netBackTo", base64_encode($redir), [ 'expires' => time()+3600, 'path' => '/', 'domain' => $this->netCookieDomain, 'secure' => 1, 'httponly' => false, 'samesite' => 'None' ]);
         }

       wp_redirect( wp_login_url() ); exit;

     } # END if( !is_user_logged_in() )


   } # END if( !empty($_GET['w3allNetCookieId']) && !empty($_GET['w3allNetTokenByte']) )
 } # END function net_get_slave_request(){


  public function net_curl($data){

      $data = http_build_query($data);
      $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$this->netUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

       if(curl_exec($ch) === false){
         curl_close($ch);
        return false;
       } else {
           curl_close($ch);
          return true;
         }
  }


 public function w3all_net_wpdb($w3all_net_dbconn = 0) {

   $w3all_net_dbconn = intval($w3all_net_dbconn);

   $w3all_dbconn_array = array();
   //for($i=0;$i<10;$i++){
    if(defined('WPW3NET_0_URL'))          $w3all_dbconn_array[0]['URL'] = WPW3NET_0_URL;
    if(defined('WPW3NET_0_DB_NAME'))      $w3all_dbconn_array[0]['DB_NAME'] = WPW3NET_0_DB_NAME;
    if(defined('WPW3NET_0_DB_USER'))      $w3all_dbconn_array[0]['DB_USER'] = WPW3NET_0_DB_USER;
    if(defined('WPW3NET_0_DB_PASSWORD'))  $w3all_dbconn_array[0]['DB_PASSWORD'] = WPW3NET_0_DB_PASSWORD;
    if(defined('WPW3NET_0_DB_HOST'))      $w3all_dbconn_array[0]['DB_HOST'] = WPW3NET_0_DB_HOST;
    if(defined('WPW3NET_0_DB_PORT'))      $w3all_dbconn_array[0]['DB_PORT'] = WPW3NET_0_DB_PORT;
    if(defined('WPW3NET_0_DB_TABPREFIX')) $w3all_dbconn_array[0]['DB_TABPREFIX'] = WPW3NET_0_DB_TABPREFIX;

    if(defined('WPW3NET_0_DB_CHARSET')) $w3all_dbconn_array[0]['DB_CHARSET'] = constant('WPW3NET_0_DB_CHARSET');
    if(defined('WPW3NET_0_DB_COLLATE')) $w3all_dbconn_array[0]['DB_COLLATE'] = constant('WPW3NET_0_DB_COLLATE');
   //}

   $w3all_dbconn_array[0]['DB_HOST'] = empty($w3all_dbconn_array[0]['DB_PORT']) ? $w3all_dbconn_array[0]['DB_HOST'] : $w3all_dbconn_array[0]['DB_HOST'] . ':' . $w3all_dbconn_array[0]['DB_PORT'];
   $w3all_net_dbconn = new wpdb($w3all_dbconn_array[0]['DB_USER'], $w3all_dbconn_array[0]['DB_PASSWORD'], $w3all_dbconn_array[0]['DB_NAME'], $w3all_dbconn_array[0]['DB_HOST']);

  if(!empty($w3all_net_dbconn->error)){
    $dberror='';
    if(!empty($w3all_net_dbconn->error->errors)){
     foreach($w3all_net_dbconn->error->errors as $k => $v){
      foreach($v as $vv){
       $dberror = $vv;
      }
     }
    }

     if(current_user_can('manage_options')){
       echo __('<div class="" style="font-size:0.9em;margin:30px;width:40%;background-color:#F1F1F1;border:3px solid red;position:fixed;top:120;right:0;text-align:center;z-index:99999999;padding:20px;max-height:140px;overflow:scroll"><h3 style="margin:0 10px 10px 10px"><span style="color:#FF0000;">WARNING</span></h3><strong>Database connection error.</strong><br /><br />Double check the db connection values <span style="color:#FF0000">into the w3all_netame_login plugin folder, file <b>config.php</b></span> (and not where may suggested on the warning message below).'.$dberror.'</div><br />', 'w3all-netame-login');
      }
    return false;
  }

  return $w3all_net_dbconn;

 }

# Create the db main table 'w3all_netlogin' into this SLAVE
 public function w3all_net_db_tab_create(){
     global $wpdb;
     # for mutisite? Think it is not needed if i am not wrong (but i will test)
     # $wpdb_tab = (is_multisite()) ? WPW3ALL_MAIN_DBPREFIX . 'signups' : $wpdb->prefix . 'signups';
     $netdbtab = $wpdb->prefix . 'w3all_netlogin';
     $wpdb->query("SHOW TABLES LIKE '$this->netdbtab'");
      if($wpdb->num_rows < 1){
       $charset_collate = $wpdb->get_charset_collate();
       $wpdb->query("CREATE TABLE $netdbtab (netTokenId varchar(60) NOT NULL, netTokenFull varchar(380) NOT NULL, userdata text, time int NOT NULL, browser varchar(250) NOT NULL, ip varchar(60) NOT NULL, netBackTo varchar(380) NOT NULL, PRIMARY KEY(netTokenId), INDEX(time)) $charset_collate;");
      }
 }


} # /> class WP_w3all_net_login {
