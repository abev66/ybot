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
?>
<!DOCTYPE html>
<html>
  <head>
    <title>ybot - login</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
     <!--
      input[type='text'],input[type='password'] {
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
      
      /* Login Window */
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
      
      .login:hover{
	opacity: 1;
      }
     -->
    </style>
  </head>
  <body>
    <div class='container'>

    <div class='header'>ybot</div>
    <div class='subtitle'>The Control Panel of a Little Plurk Bot!</div>
<?php if(isset($_GET['error'])): ?>
    <!-- Show Notice Message If Login Failed -->
    <div class='notice-red'> Login Failed.</div>
<?php endif ?>
    
    <!-- Login -->
    <div class='login'>
      <form action='auth.php' method="POST">
	ID <input type='text' name='account' />
	Password <input type='password' name='password' />
	<input type='submit' value='Login'>
      </form>
    </div>
    </div>
  </body>
</html>
<?php else: 
  header('Location: responseedit.php');
endif; ?>
