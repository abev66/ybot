<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<html>
<?php
session_start();
if(!isset($_SESSION['uid'])){
	header("location: index.php");
	}
else{
	session_destroy();
	echo "<head><title>see ya!</title></head>logout sucessed!!<br /><a href='login.php'>relogin with another account<a>";
	}
?>
</html>