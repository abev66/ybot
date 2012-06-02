<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
session_start();
if(!isset($_SESSION['uid'])){
	header("location: login.php");
	}
else{
	header("location: tableedit.php");
	}
?>
<html>
<head>
<title>index</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password<a>, <a href='logout.php'>logout</a>"?>
	</div>
	我是首頁，已廢棄
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
</body>
</html>