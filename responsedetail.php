<?php 
session_start(); 
if(!isset($_SESSION['_ybot_uid']))
  header('Location: login.php');
else{

require('db_port.php');
$dblink = db_init();

if (isset($_POST['delkey'])){
	$key=$_POST['delkey'];
	if(!remove_relation($dblink, $_GET['response'],$key))
echo "<script type='text/javascript'>alert('delete keyword ".$key." sucess!');</script>";
	}
if (isset($_POST['delres'])){
	if (!remove_sentence($dblink, $_GET['response'])){
		header("location: responseedit.php");
		}
	}
?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - <?php echo $_GET['response']?>_detail</title>
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
		<h1>Response: <?php echo $_GET['response'];?>
	  	<form action='' method='POST' id='delete-form'>
		  <input type='hidden' name='delres' value='1' />
		  <input id='delete-btn' type='button' title='Destroy this Response!' value='Delete this Response' onclick='javascript:if(confirm("Are you sure?"))this.form.submit();'>
		</form>
		</h1>
	  </div>
		<div class='keyword-box'>
		  <div>Keywords</div>
		<?php
		$result=list_relation_response($dblink, $_GET['response']);
		foreach ($result as $i){
		echo "<span><a href='keyworddetail.php?keyword=".urlencode($i['keyword'])."'>".$i['keyword'].'</a>';
		echo "<form action='' method='POST'><input type='hidden' name='delkey' value='".$i['keyword']."'><input class='unlink-btn' type='submit' title='Unlink' value='Unlink'></form></span>";
		}
		?>
		</div>
		<a href='javascript:history.back()'>Back</a>

  </body>
</html>
<?php } ?>
