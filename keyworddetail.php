<?php 
session_start(); 
if(!isset($_SESSION['_ybot_uid']))
  header('Location: login.php');
else{

require('db_port.php');
$dblink = db_init();

?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - <?php echo $_GET['keyword']?> detail</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
    <style type='text/css'>
      <!--
      .response-box {
	text-align: left;
	width: 90%;
	margin: 1em auto;
      }
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
	  <div>
		<h1>Keyword: <?php echo $_GET['keyword'];?>
	  	<form action='' method='POST' id='delete-form'>
		  <input type='hidden' name='delkey' value='1' />
		  <input id='delete-btn' type='button' title='Destroy this Keyword!' value='Delete this keyword' onclick='javascript:if(confirm("Are you sure?"))this.form.submit();'>
		</form>
		</h1>
	  </div>
	  <div class='response-box'>
	  <div style='text-align: center;'>Responses</div>
		<table>
			<tr>
				<th>Qualifier</th>
				<th>Response</th>
				<th>Action</th>
			</tr>
		<?php
		$result=list_relation_keyword($dblink, $_GET['keyword']);
		foreach ($result as $i){
		echo "<tr><td>".$i['qualifier']."</td><td>".$i['response']."</td>";
		echo "<td><form action='' method='POST'><input type='hidden' name='delres' value='".$i['response']."'><input type='submit' value='Delete'></form></td></tr>";
		}
		?>
		</table>
		<?php
		if (isset($_POST['delres'])){
			$key=$_POST['delres'];
			if(!remove_relation($dblink, $key, $_GET['keyword']))
                echo "<script type='text/javascript'>alert('delete response ".$key." sucess!');</script>";
			}
		if (isset($_POST['delkey'])){
			if (!remove_keywords($dblink, $_GET['keyword'])){
				header("location: keywordedit.php");
				}
			}
		?>
		<a href='javascript:history.back()'>Back</a>
	  </div>
  </body>
</html>
<?php } ?>
