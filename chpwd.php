<?php
/*
 *  ybot.php - a plurk bot use php-plurk-api
 *
 *  Copyright (C) 2012 Wei-Chen Lai <abev66@gmail.com>
 *                2012 Zheng-Yen Hong
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

session_start();
if(!isset($_SESSION['_ybot_uid'])):
  header("Location: login.php");
else:
  $session_uid=$_SESSION["_ybot_uid"];
  $session_name=$_SESSION["_ybot_account"];
  $session_type=$_SESSION["_ybot_type"];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>ybot - Change Password</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
     <!--
      .login:hover{
	opacity: 1;
      }
      
      .login {
	background: #FFF;
	opacity: 0.7;
	border-radius: 5px;
	display: block;
	margin: 2em auto;
	padding: 2em 2.5em;
	max-width: 250px;
	text-align: left;
	vertical-align: middle;
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.2);
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
      }
      
      
      input[type='password'] {
	width: 100%;
	margin-left: auto;
	display:block;
	margin-bottom: 10px;
	border: 1px solid #999;
	border-radius: 3px;
      }
      
      input[type='submit']{
	display: block;
	margin: 0.8em auto 0;
      }
     -->
    </style>
  </head>
  <body>
    <div class='container'>
<?php include('header.inc'); ?>
<?php include('navbar.inc'); ?>
<?php 
  if(isset($_POST['upd'])){
      require('db_port.php');
      $dblink=db_init($_SESSION[_ybot_uid]);
      $pass=$_POST['new'];
      $repass=$_POST['con'];
      $oldpassword=sha1($_POST['cur']);
      $tmp=get_user_data($dblink ,$session_name);
      $currentpassword=$tmp['password'];
      if ($oldpassword!=$currentpassword)
	echo "<div class='notice-red'>Wrong Password.</div>";
      else if($pass!=$repass)
	echo "<div class='notice-red'>Not matched.</div>";
      else if(strlen($pass)<6)
	echo "<div class='notice-red'>Password should be longer than 6 chars.</div>";
      else{
	update_user_passwd($dblink, $session_name, $pass);
	echo "<div class='notice-green'>Finish!</div>";
	header("Refresh:2;url=index.php");
      }
      db_close($dblink);
  }
?>
    <!-- Change Password -->
    <div class='login'>
      <form action='' method="POST">
	Current Password <input type='password' name='cur' />
	New Password <input type='password' name='new' />
	Confirm <input type='password' name='con' />
	<input type='submit' name='upd' value='Update'>
      </form>
    </div>
    </div>
  </body>
</html>
<?php endif; ?>