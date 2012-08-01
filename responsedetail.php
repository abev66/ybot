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
if(!isset($_SESSION['_ybot_uid']))
  header('Location: login.php');
else{

require('db_port.php');
$dblink = db_init();

if (isset($_POST['delkey'])){
	$key=$_POST['delkey'];
	remove_relation($dblink, $_GET['response'],$key);
	}
if (isset($_POST['delres'])){
	remove_sentence($dblink, $_GET['response']);
	header('Location: responseedit.php');
	}

if(isset($_POST['newkey'])) {
  $newkey = trim($_POST['newkey']);
  if($newkey){
    add_keywords($dblink, trim($_POST['newkey']));
    create_relation($dblink, $_GET['response'], array(trim($_POST['newkey'])));
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - <?php echo htmlspecialchars($_GET['response'])?>_detail</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
      <!--
      .keyword-box {
	text-align: left;
	width: 90%;
	margin: 1em auto;
      }
      h1 {
	text-align: left;
      }
      form {
	text-align: center;
      }      -->
    </style>
    </head>
  <body>
    <div class='container'>
	<?php include('header.inc'); ?>
	<?php include('navbar.inc'); ?>
	  <div>
		<h1>Response: <?php echo htmlspecialchars($_GET['response']);?>
	  	<form action='' method='POST' id='delete-form'>
		  <input type='hidden' name='delres' value='1' />
		  <input id='delete-btn' type='button' title='Destroy this Response!' value='Delete this Response' onclick='javascript:if(confirm("Are you sure?"))this.form.submit();'>
		</form>
		</h1>
	  </div>
		<div class='keyword-box'>
		  <div>Keywords</div>
		<div style='display:block;'>
		<?php
		$result=list_relation_response($dblink, $_GET['response']);
		foreach ($result as $i){
		echo "<span><a href='keyworddetail.php?keyword=".urlencode($i['keyword'])."'>".htmlspecialchars($i['keyword']).'</a>';
		echo "<form action='' method='POST'><input type='hidden' name='delkey' value='".htmlspecialchars($i['keyword'])."'><input class='unlink-btn' type='button' title='Unlink' value='Unlink' onclick='javascript: if(confirm(\"Are you sure?\")) this.form.submit();'></form></span>";
		}
		?>
		<span class='newkey'>
		  <form action='' method='POST'>
		    <input type='text' name='newkey' class='newkey-input' />
		    <input type='submit' class='newkey-btn' value='+' title='Add new keyword' />
		  </form>
		</span>
		</div>
		</div>
		<a href='responseedit.php' class='back-btn'><img src='images/back.png'> Back to responses list</a>

  </body>
</html>
<?php } ?>
