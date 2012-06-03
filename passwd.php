<?php //session check
session_start();
if(!isset($_SESSION['uid'])){
	header("location: login.php");
	}
else{
	require('db_port.php');
	$dblink=db_init();
	$session_uid=$_SESSION["uid"];
	$session_name=$_SESSION["name"];
	$session_type=$_SESSION["type"];
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>change password</title>
<link rel="stylesheet" href="style.css" type="text/css" />
    <style type="text/css">
      <!--
	.box {
	  display:block;
	  margin: 1.5em auto;
	  background-color: rgba(173,216,230,0.5);
	  background-position: right bottom;
	  padding: 1em;
	  border-radius: 10px;
	  opacity: 0.5px;
	  text-align: center;
	  width: 450px;
	  line-height: 2em;
	}

    </style>
</head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password</a>, <a href='logout.php'>logout</a>"?>
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
<div class='box'>
Hey! wanna change password?
<form name='passwd' action='' method='POST'>
Your current password:<input type='password' name='oldpassword'><br />
Your new password:<input type='password' name='pass'><br />
retype new password:<input type='password' name='repass'><br />
<input type='submit' name='passwd' value='set'></form>
</div>
<?php
if(isset($_POST['passwd'])){
	$pass=$_POST['pass'];
	$repass=$_POST['repass'];
	$oldpassword=sha1($_POST['oldpassword']);
	$tmp=get_user_data($dblink ,$session_name);
	$currentpassword=$tmp['password'];
	if ($oldpassword!=$currentpassword)
		echo "<div class='notice-red'>invalid act!!</div>";
	elseif($pass!=$repass)
		echo "<div class='notice-red'>invalid act!!</div>";
	elseif(strlen($pass)<6)
		echo "<div class='notice-red'>password should be longer than 6 chars.</div>";
	else{
		update_user_passwd($dblink, $session_name, $pass);
		echo "<div class='notice'>finish!</div>";
		}
	}
	db_close($dblink);
?>
</body>
</html>