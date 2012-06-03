<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php //session check
session_start();
if(!isset($_SESSION['uid']) || $_SESSION['type']!='a'){
	header("location: index.php");
	}
else{
	require('db_port.php');
	$dblink=db_init();
	$session_uid=$_SESSION["uid"];
	$session_name=$_SESSION["name"];
	$session_type=$_SESSION["type"];
?>
<html>
<head>
<title>botsetting</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password</a>, <a href='logout.php'>logout</a>"?>
	</div>
	<div class='banner'>
	<img src="http://i.imgur.com/wvLcp.png" alt="設定" width="500" height="100" />
	</div>
	<div class='link'><ul>
		<?php 
		if($session_type=='a'){
			echo "<li><a class='links' href='tableedit.php'>table edit</a></li> ";
			echo "<li><a class='links' href='botcontrol.php'>robot control</a></li>";
			}
		if($session_uid==1){
			echo "<li><a class='links' href='accountmanage.php'>account manage</a><li>";
			}
		?>
		</ul>
	</div>
	<div class='botlink'><ul><li>
	<a class='links' href='botcontrol.php'>botcontrol</a></li><li>
	<a class='links' href='botsetting.php'>botsetting</a></li></ul>
	</div>
	<div class='content'><br />
<?php if(!isset($_POST['update'])): ?>
      <form action='' method='POST'>
	<table border='1' id='fortable'>
	  <thead>
	    <tr>
	      <th>SETTING</th>
	      <th>VALUE</th>
	    </tr>
	  </thead>
	  <tbody>
<?php
	$settings=dump_settings($dblink);
	
	foreach( $settings as $key => $value ){
	  echo "<tr>\n";
	  echo "<td>$key</td>\n";
	  echo '<td>';
	  if($key == 'RESPONSE_MODE'){
	      foreach(array('MENTION','ANYWAY','DISABLED') as $set)
		echo "<input type='radio' name='newsets[$key]' value='$set' ".(($set == strtoupper($value))?'checked':'')." />$set";
	  } else if($key == 'PLURK_PASSWORD') {
	      echo "<input type='password' name='newsets[$key]' value='' />";
	  } else if(strtolower($value) == 'true' || strtolower($value) == 'false') {
	      if(strtolower($value)=='true'){
		echo "<input type='radio' name='newsets[$key]' value='true' checked /> On";
		echo "<input type='radio' name='newsets[$key]' value='false' /> Off";
	      } else {
		echo "<input type='radio' name='newsets[$key]' value='true' /> On";
		echo "<input type='radio' name='newsets[$key]' value='false' checked /> Off";
	      }
	  } else
	    echo "<input type='text' name='newsets[$key]' value='$value' />";
	    
	  if($key != 'PLURK_PASSWORD')
	    echo "<input type='hidden' name='oldsets[$key]' value='$value' />";
	  echo '</td>';
	  echo "\n</tr>\n";
}
?>
	  </tbody>
	</table>
	<input type='submit' name='update' value='儲存設定' />
	<input type='button' value='取消修改' onclick='javascript: location.href="botsetting.php";' />
    </form>
<?php
 else:
    
    foreach($_POST['newsets'] as $key => $value) {
      if( $value != $_POST['oldsets'][$key] ){
	update_setting($dblink, $key, $value);
      }
    }
     header('Location: botsetting.php');
 endif;
?>
	</div>
<?php
db_close($dblink);
} 
?>
</body>
</html>