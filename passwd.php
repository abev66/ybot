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
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>change password</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password<a>, <a href='logout.php'>logout</a>"?>
	</div>
	<div class='link'>
		<a href='tableedit.php'>table edit</a>
		<?php 
		if($session_type=='a'){
			echo "<a href='botcontrol.php'>robot control</a>";
			}
		if($session_uid==1){
			echo "<a href='accountmanage.php'>account manage</a>";
			}
		?>
Hey! wanna change password?<br />
<form name='passwd' action='' method='POST'>
<br />
Your current password:<input type='password' name='oldpassword'><br />
Your new password:<input type='password' name='pass'><br />
retype new password:<input type='password' name='repass'><br />
<input type='submit' name='passwd' value='set'></form>
<?php
if(isset($_POST['passwd'])){
	$pass=$_POST['pass'];
	$repass=$_POST['repass'];
	$oldpassword=sha1($_POST['oldpassword']);
	$tmp=get_user_data($dblink ,$session_name);
	$currentpassword=$tmp['password'];
	if ($oldpassword!=$currentpassword)
		echo "invalid act!!<br />";
	elseif($pass!=$repass)
		echo "invalid act!!<br />";
	else{
		update_user_passwd($dblink, $session_name, $pass);
		echo 'you did it!!';
		}
	}
	db_close($dblink);
?>
</body>
</html>