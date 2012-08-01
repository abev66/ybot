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
require('db_port.php');
$dblink = db_init();
?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - Responedit</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
      <!--
      tr td:first-child {
	text-align: center;
      }
      -->
    </style>
  </head>
  <body>
    <div class='container'>

	<?php include('header.inc'); ?>
	<?php include('navbar.inc'); ?>
	  <div class='search'>
		<form action='' method='GET'>
			<input type='text' name='searchw'>
			<input id='search-btn' type='submit' value='search'>
		</form>
	  </div>
	  <div class='add-box'>
	  <h3>Add new response and keywords</h3>
		<form name='tableadd' action='' method='POST'>
		Keywords <input type='text' name='keyword'><div style='font-size: 90%; display: block;'>separate each keywords with "|"</div>
		Response 
					<select name="qualifier">
					<option value=":">:(自由發揮)</option>
					<option value="says" selected="selected">說</option>
					<option value="likes" >喜歡</option>
					<option value="shares" >分享</option>
					<option value="gives" >給</option>
					<option value="hates" >討厭</option>
					<option value="wants" >想要</option>
					<option value="has" >已經</option>
					<option value="will" >打算</option>
					<option value="asks" >問</option>
					<option value="wishs" >期待</option>
					<option value="was" >曾經</option>
					<option value="feels" >覺得</option>
					<option value="thinks" >想</option>
					<option value="is" >正在</option>
					<option value="hopes" >希望</option>
					<option value="needs" >需要</option>
					<option value="wonders" >好奇</option></select>	
					<input type='text' name='reply' maxlength='140'><br /><input type='submit' name='tableadd' value='Add'>
		</form>
		<?php
		if (isset($_POST['tableadd'])){
			if (!empty($_POST['keyword']) && !empty($_POST['reply'])){
				$keyword=$_POST['keyword'];
				$qualifier=$_POST['qualifier'];
				$reply=$_POST['reply'];
				$keyword=trim($keyword);
				$keyword=explode('|',$keyword);
				add_keywords($dblink,$keyword);
				add_sentence($dblink,$qualifier,$reply);
				create_relation($dblink,$reply,$keyword);
				}
			else
				echo "<script type='text/javascript'>alert('you must fill all the blanks!');</script>";
			}
	?>
	  </div>
	  <div>
<?php
if (isset($_GET['searchw'])){
  $key=trim($_GET['searchw']);
  $result=output_table_response($dblink, $key, true);
  $key_dis = htmlspecialchars($key);
  $count = count($result);
  echo "<div class='notice-green'> $count result(s) of $key_dis. <a href='responseedit.php'>Display all</a></div>";
}
?>
		<h1>Responses</h1>
		<table>
		<tr>
			<th>Qualifier</th>
			<th>Response</th>
		</tr>
		<?php
		if (!isset($_GET['searchw'])){
			$result=output_table_response($dblink);
			foreach($result as $i)
				echo "<tr><td>".$i['qualifier']."</td><td><a href='responsedetail.php?response=".urlencode($i['response'])."'>".htmlspecialchars($i['response'])."</a></td></tr>";
			}
		if (isset($_GET['searchw'])){
				foreach($result as $i)
					echo "<tr><td>".$i['qualifier']."</td><td><a href='responsedetail.php?response=".urlencode($i['response'])."'>".htmlspecialchars($i['response'])."</a></td></tr>";
				}
		?>
		</table>
		<div style='text-align: center'><a class='top-btn' href='#top'><img src='images/top.png' />Top</a></div>
	  </div>
    </div>
  </body>
</html>
