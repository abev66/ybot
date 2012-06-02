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
		<?php echo "HI! ".$session_name." | <a href='passwd.php'>change password<a> | <a href='logout.php'>logout</a>"?>
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
		<form>
			<input type='submit' name='pause' value='暫停機器人'>
			<input type='submit' name='stop' value='停止機器人'>
			<input type='submit' name='check' value='檢視狀態'>
			<input type='submit' name='say' value='發噗'>
		</form>
		
	</div>
	<?php db_close($dblink);?>
</body>
</html>
<?php 
if (isset($_POST['say'])){
	
	}
?>