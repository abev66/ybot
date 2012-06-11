<?php
session_start(); 
if(!isset($_SESSION['_ybot_uid']))
  header('Location: login.php');
else
	header("location: responseedit.php")
?>