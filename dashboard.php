<?php 
session_start(); 
if($_SESSION['_ybot_type']=='b' || !isset($_SESSION['_ybot_uid'])):
  header('Location: login.php');
else:

require('db_port.php');
$db_port = db_init();
include('command_flags.inc');

?>
<!DOCTYPE html>
<html>
  <head>
    <title>ybot - Dashboard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
     <!--
      .controlpanel {
	background: #FFF;
	border-radius: 5px;
	display: block;
	margin: 1em auto;
	padding: 16px;
	max-width: 460px;
	text-align: center;
	vertical-align: middle;
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.2);
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
	opacity: 0.7;
      }
      
      .controlpanel h3 {
	margin-top: 0;
      }
      
      h3 {
	margin: 12px auto;
	font-weight: bold;
	font-size: 133%;
	font-weight: normal;
	text-align:center;
      }
      
      .plurk_box {
	background: #FFF;
	opacity: 0.7;
	border-radius: 5px;
	display: block;
	margin: 1em auto;
	padding: 0.7em 2.5em;
	max-width: 580px;
	vertical-align: middle;
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.2);
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
      }
      
      .plurk_box p, .plurk_box h3 {
	text-align: center;
      }
      
      .plurk_box:hover, .controlpanel:hover {
	opacity: 1;
      }
      #send-btn {
	width: 81px;
	height: 38px;
	background-image: url('images/plurkbuttom.png');
	background-position: center center;
	background-repeat: no-repeat;
	text-indent: -9999px;
	border-radius: 5px;
	margin: 0 auto 0;
	border: 0;
      }
      .setval {
	background: #FFF;
	opacity: 0.7;
	border-radius: 5px;
	display: block;
	margin: 2em auto;
	padding: 2em 2.5em;
	max-width: 580px;
	text-align: center;
	vertical-align: middle;
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.2);
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
      }
      .setval h3 {
	margin-top: 0;
      }
      .setval:hover {
	opacity: 1;
      }
     -->
    </style>
  </head>
  <body>
    <div class='container'>

<?php include('header.inc'); ?>
<?php include('navbar.inc'); ?>

<?php
  $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
  $randomd=(string)rand();
  $socketname="sockets/socket_ybot-client".$randomd; 
  socket_bind($socket, $socketname);
  socket_set_block($socket);
  chmod($socketname, 0777);
  $msg=json_encode(array( 'command' => CMD_GET_PAUSE_STATUS ));
  $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
  if($bytes_sent){
	  $bfr='';
	  $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
	  if($bfr == FB_RUNNING)
	    echo "<div class='notice-green'>Status: Running</div>";
	  else if($bfr == FB_PAUSED)
	    echo "<div class='notice-green'>Status: Pause</div>";
	  else
	    echo "<div class='notice-red'>Status: Unexpect response.</div>";
	  $botstatus = $bfr;
	  
	  $msg=json_encode(array( 'command' => CMD_VERSION ));
	  $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
	  
	  if($bytes_sent){
	    $bfr='';
	    $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
	    
	    if(!empty($bfr) && $bfr != FB_ERROR_INVALID_COMMAND && $bfr != FB_ERROR){
	      $ver_info = json_decode($bfr);
	    }
	  }
  } else {
    echo "<div class='notice-red'>Couldn't connect to bot.</div>";
    $botstatus = 'dead';
  }
    
  if( isset( $_POST['ps']) ) {
    $msg=json_encode(array( 'command' => CMD_PAUSE ));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if($bytes_sent){
    $bfr='';
    $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
    if($bfr==FB_OK)
      header("Location: dashboard.php");
    }
  } else if ( isset( $_POST['co']) ){
    $msg=json_encode(array( 'command' => CMD_CONTINUE ));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if($bytes_sent){
    $bfr='';
    $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
    if($bfr==FB_OK)
      header("Location: dashboard.php");
    }
  } else if ( isset( $_POST['rs']) ) {
    $msg=json_encode(array( 'command' => CMD_RELOAD_SETTINGS ));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if ($bytes_sent)
	    echo "<div class='notice-green'>Command Sent.</div>";
    else
	    echo "<div class='notice-red'>Command not sent</div>";
  } else if ( isset( $_POST['rt']) ) {
    $msg=json_encode(array( 'command' => CMD_RELOAD_TABLE ));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if ($bytes_sent)
	    echo "<div class='notice-green'>Command Sent.</div>";
    else
	    echo "<div class='notice-red'>Command not sent</div>";  
  } else if ( isset( $_POST['rl']) ) {
    $msg=json_encode(array( 'command' => CMD_RELOGIN));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if ($bytes_sent)
      echo "<div class='notice-green'>Command Sent.</div>";
    else
      echo "<div class='notice-red'>Command not sent</div>";
  } else if ( isset($_POST['nplurk']) ) {
    include('plurk_lang_flags.inc');
    $no_comments = ($_POST['no_comments'] == 1)? 1 : 0 ;
    $msg=json_encode(array("command" => CMD_SEND_PLURK, "content" => $_POST['plurk'], "lang" => PLURK_LANG_CHINESE_TRADITIONAL, "qualifier" => $_POST['qualifier'], "no_comments" => $no_comments ));
    $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
    if ($bytes_sent)
      echo "<div class='notice-green'>Sent</div>";
    else
      echo "<div class='notice-red'>Not Sent</div>";
  }
  socket_set_nonblock($socket);
  socket_close($socket);
  unlink($socketname);
?>

<?php if(isset($_POST['update'])): ?> 
<div class="notice-green">Update: 
<?php
    foreach($_POST['newsets'] as $key => $value) {
      if( $value != $_POST['oldsets'][$key] ){
	echo $key.'&nbsp;';
	update_setting($db_port, $key, $value);
      }
    }
?>
</div>
<? endif; ?>

<?php if($botstatus != 'dead'): ?>
<!-- Control Panel -->
      <div class='controlpanel'>
      <h3>Control Panel</h3>
	<form action='' method='POST'>
	  <?php if($botstatus == FB_RUNNING): ?>
	    <input type='submit' name='ps' value='Pause' />
	  <?php else:?>
	    <input type='submit' name='co' value='Continue' />
	  <?php endif;?>
	  <input type='submit' name='rs' value='Reload Setting' />
	  <input type='submit' name='rt' value='Reload Table' />
	  <input type='submit' name='rl' value='Relogin' />
	</form>
      <?php if(isset($ver_info)):?>
	<table>
	  <tr><th colspan='2'>Bot Info</th></tr>
	<?php foreach( $ver_info as $key => $value):?>
	  <tr><td><?php echo $key; ?></td><td><?php echo $value; ?></td></tr>
	<?php endforeach;?>
	</table>
      <?php endif;?>
      </div>
      <div class='plurk_box'>
      <h3>Send a Plurk</h3>
	<form action='' method='POST'>
	  <p>
	    <select name='qualifier'>
	      <option value=":">:</option>
	      <option value='says' selected='selected'>says</option>
	      <option value='likes' >likes</option>
	      <option value='shares' >shares</option>
	      <option value='gives' >gives</option>
	      <option value='hates' >hates</option>
	      <option value='wants' >wants</option>
	      <option value='has' >has</option>
	      <option value='will' >will</option>
	      <option value='asks' >asks</option>
	      <option value='wishs' >wishs</option>
	      <option value='was' >was</option>
	      <option value='feels' >feels</option>
	      <option value='thinks' >thinks</option>
	      <option value='is' >is</option>
	      <option value='hopes' >hopes</option>
	      <option value='needs' >needs</option>
	      <option value='wonders' >wonders</option>
	    </select>
	    <input type='text' name='plurk' maxlength='140' size='35' />
	    <div style="text-align: right;"><input type='checkbox' name='no_comments' value='1' />No comments</div>
	  </p>
	  <p>
	    <input type='submit' id='send-btn' name='nplurk' value='Plurk' />
	  </p>
	</form>
      </div>
<?php endif;?>
<!-- Setting Values -->
      <div class='setval'>
      <h3>Settings</h3>
	<form action='' method='POST'>
	  <table>
	    <thead>
	      <tr>
		<th>Setting</th>
		<th>Value</th>
	      </tr>
	    </thead>
	    <tbody>
<?php
  $settings=dump_settings($db_port);
  foreach( $settings as $key => $value ){
    echo "<tr>\n";
    echo "<td>$key</td>\n";
    echo '<td>';
    if($key == 'RESPONSE_MODE'){
	foreach(array('MENTION','ANYWAY','DISABLED') as $set)
	  echo "<input type='radio' name='newsets[$key]' value='$set' ".(($set == strtoupper($value))?'checked':'')." />$set";
    } else if($key == 'PLURK_PASSWORD') {
	echo "<input type='password' name='newsets[$key]' value='' />";
    } else if(strtolower($value) == 'true' || strtolower($value) == 'false') {
	if(strtolower($value)=='true'){
	  echo "<input type='radio' name='newsets[$key]' value='true' checked /> On";
	  echo "<input type='radio' name='newsets[$key]' value='false' /> Off";
	} else {
	  echo "<input type='radio' name='newsets[$key]' value='true' /> On";
	  echo "<input type='radio' name='newsets[$key]' value='false' checked /> Off";
	}
    } else
      echo "<input type='text' name='newsets[$key]' value='$value' />";
      
    if($key != 'PLURK_PASSWORD')
      echo "<input type='hidden' name='oldsets[$key]' value='$value' />";
    echo '</td>';
    echo "\n</tr>\n";
}
?>
	  </table>
	  <input type='submit' name='update' value='Save' />
	  <input type='reset' value='Cancel' />
	</form>	
      </div>
    </div>
  </body>
</html>
<?php 
db_close($db_port);
endif; ?>