<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
<head><title>botcontrol</title><link rel="stylesheet" href="style.css" type="text/css" /></head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password<a>, <a href='logout.php'>logout</a>"?>
	</div>
	<div class=banner>
		<img src="http://i.imgur.com/kMqvd.png" width="500" height="100" alt="控制" />
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
	</div>
	<div class='botlink'>
	<a href='botcontrol.php'>botcontrol</a>
	<a href='botsetting.php'>botsetting</a>
	</div>
	<div class='content'>
	</div>
	<?php db_close($dblink);?>
</body>
</html>