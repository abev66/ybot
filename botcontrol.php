<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php //session check
session_start();
if(!isset($_SESSION['uid'])){
	header("location: index.php");
	}
	if($_SESSION['type']!='a'){
	header("location: index.php");
	}
else{
	require('db_port.php');
	$dblink=db_init();
	$session_uid=$_SESSION["uid"];
	$session_name=$_SESSION["name"];
	$session_type=$_SESSION["type"];
	}
?>
<html>
  <head>
    <title>botcontrol</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
    <style type="text/css">
      <!--
	.plurk_box {
	  display:block;
	  margin: auto;
	  background-color: #ADD8E6;
	  background-image: url('images/plurk-logo.png');
	  background-position: right bottom;
	}
	#send-btn {
	  width: 81px;
	  height: 38px;
	  background-image: url('images/plurkbuttom.png');
	  background-position: center center;
	}
      -->
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name." | <a href='passwd.php'>change password</a> | <a href='logout.php'>logout</a>"?>
	</div>
	<div class='banner'>
		<img src="http://i.imgur.com/kMqvd.png" width="500" height="100" alt="控制" />
	</div>
	<div class='link'><ul>
		<?php 
		if($session_type=='a'){
			echo "<li><a class='links' href='tableedit.php'>table edit</a></li> ";
			echo "<li><a class='links' href='botcontrol.php'>robot control</a></li>";
			}
		if($session_uid==1){
			echo "<li/><a class='links' href='accountmanage.php'>account manage</a><li>";
			}
		?>
		</ul>
	</div>
	<div class='botlink'><ul><li>
	<a class='links' href='botcontrol.php'>botcontrol</a></li><li>
	<a class='links' href='botsetting.php'>botsetting</a></li></ul>
	</div>
	<div class='content'>
		控制機器人吧!
		<form name='control' action='' method='POST'><input type='hidden' name='gotaction'>
		<?php  //check puuse info. 
						include("command_flags.inc");
                        $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
                        $randomd=(string)rand();
                        $socketname="sockets/socket_ybot-client".$randomd; //genrate socket in random in case of conflict.
                        socket_bind($socket, $socketname);
                        socket_set_block($socket);
                        chmod($socketname, 0777);
                        $msg=json_encode(array( 'command' => CMD_GET_PAUSE_STATUS ));
                        $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
                        if($bytes_sent){
                                $bfr='';
                                $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
                                }
						if ($bfr=='running')
							echo "機器人正在跑!!<br /><input type='submit' name='pause' value='暫停機器人'>";
						elseif($bfr=='paused')
							echo "機器人停下來了!!<br /><input type='submit' name='continue' value='繼續跑!'>";
                        socket_set_nonblock($socket);
                        socket_close($socket);
                        unlink($socketname);

		?>

			<input type='submit' name='poke' value='戳一下'>
			<input type='submit' name='reloadset' value='重載設定'>
			<input type='submit' name='reloadres' value='重載詞彙庫'>
			<input type='submit' name='relogin' value='重新登入'>			
		</form>
		<br /><br />說點什麼吧!
		<p class='plurk_box'>
		<form action='' method='POST'>
		<input type='hidden' name='say'>
		<input type='hidden' name='gotaction'>
		  要說的話：
		    <select name='qualifier'>
		      <option value='says' selected='selected'>說</option>
		      <option value='likes' >喜歡</option>
		      <option value='shares' >分享</option>
		      <option value='gives' >給</option>
		      <option value='hates' >討厭</option>
		      <option value='wants' >想要</option>
		      <option value='has' >已經</option>
		      <option value='will' >打算</option>
		      <option value='asks' >問</option>
		      <option value='wishs' >期待</option>
		      <option value='was' >曾經</option>
		      <option value='feels' >覺得</option>
		      <option value='thinks' >想</option>
		      <option value='is' >正在</option>
		      <option value='hopes' >希望</option>
		      <option value='needs' >需要</option>
		      <option value='wonders' >好奇</option>
		    </select>
		<input type='text' name='plurk'><input type='submit' id='send-btn' value='發噗'>
		</p>

	</div>
</body>
</html>
<?php 
if (isset($_POST['gotaction'])){
                        $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
                        $randomd=(string)rand();
                        $socketname="sockets/socket_ybot-client".$randomd; //genrate socket in random in case of conflict.
                        socket_bind($socket, $socketname);
                        socket_set_block($socket);
                        chmod($socketname, 0777);
	if (isset($_POST['pause'])){
		$msg=json_encode(array( 'command' => CMD_PAUSE ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
        if($bytes_sent){
            $bfr='';
            $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
			if($bfr=='okay')
				header("location: botcontrol.php");
            }
		}
	if (isset($_POST['continue'])){
		$msg=json_encode(array( 'command' => CMD_CONTINUE ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
        if($bytes_sent){
            $bfr='';
            $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
			if($bfr=='okay')
				header("location: botcontrol.php");
            }
		}
	if (isset($_POST['poke'])){
		$msg=json_encode(array( 'command' => CMD_PING ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
        if($bytes_sent){
            $bfr='';
            $bytes_received = @socket_recv($socket, $bfr, 65536, 0);
			if ($bfr=='echo')
				echo "<br />效果十分顯著!!";
            }
		else	
			echo "<br />毫無反應，就只是個屍體。";
		}
	if (isset($_POST['reloadset'])){
		$msg=json_encode(array( 'command' => CMD_RELOAD_SETTINGS ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
		if ($bytes_sent)
			echo"指令送出了!!";
		}
	if (isset($_POST['reloadres'])){
		$msg=json_encode(array( 'command' => CMD_RELOAD_TABLE ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
		if ($bytes_sent)
			echo"指令送出了!!";
		}
	if (isset($_POST['relogin'])){
		$msg=json_encode(array( 'command' => CMD_RELOGIN ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
		if ($bytes_sent)
			echo"指令送出了!!";
		}
	if (isset($_POST['say'])){
		$msg=json_encode(array("command" => CMD_SEND_PLURK, "content" => $_POST['plurk'], "lang" => "ch_tr", "qualifier" => $_POST['qualifier'], "no_comments" => 0 ));
		$bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
		if ($bytes_sent)
			echo"訊息送出了!!";
		}
    socket_set_nonblock($socket);
    socket_close($socket);
    unlink($socketname);
	}
?>