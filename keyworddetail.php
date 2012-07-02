<?php 
session_start(); 
if(!isset($_SESSION['_ybot_uid'])):
  header('Location: login.php');
else:

require('db_port.php');
$dblink = db_init();

if (isset($_POST['delres'])){
	$key=$_POST['delres'];
	remove_relation($dblink, $key, $_GET['keyword']);
	}
if (isset($_POST['delkey'])){
	remove_keywords($dblink, $_GET['keyword']);
	header('Location: keywordedit.php');
	}

if (isset($_POST['newres'])){
  $reply = trim($_POST['reply']);
  if(!empty($reply)) {
    add_sentence($dblink, $_POST['qualifier'], trim($_POST['reply']));
    create_relation($dblink, trim($_POST['reply']), array(trim($_GET['keyword'])));
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - <?php echo htmlspecialchars($_GET['keyword'])?> detail</title>
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
      h1 {
	text-align: left;
      }
      form {
	text-align: center;
      }
      #newres {
	display: block;
	margin: 0 auto;
      }
      -->
    </style>
  </head>
  <body>
    <div class='container'>

	<?php include('header.inc'); ?>
	<?php include('navbar.inc'); ?>
	  <div>
		<h1>Keyword: <?php echo htmlspecialchars($_GET['keyword']);?>
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
		echo "<tr><td>".$i['qualifier']."</td><td><a href='responsedetail.php?response=".urlencode($i['response'])."'>".$i['response']."</a></td>";
		echo "<td><form action='' method='POST'><input type='hidden' name='delres' value='".$i['response']."'><input class='unlink-btn' type='button' title='Unlink' value='Unlink' onclick='javascript: if(confirm(\"Are you sure?\")) this.form.submit();'></form></td></tr>";
		}
		?>
		  <tr>
		    <form action='' method='POST'>
		      <td>
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
			  <option value="wonders" >好奇</option>
			</select>	
		      </td>
		      <td>
			<input type='text' name='reply' style='width: 95%; display: block; margin: auto' maxlength='140' />
		      </td>
		      <td>
			<input type='submit' name='newres' value='+' id='newres' class='newkey-btn' />
		      </td>
		    </tr>
		  </tr>
		</table>
		<a href='keywordedit.php' class='back-btn'><img src='images/back.png'> Back to keywords list</a>
	  </div>
  </body>
</html>
<?php endif; ?>
