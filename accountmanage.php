<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php //session check
session_start();
if(!isset($_SESSION['uid'])){
	header("location: index.php");
	}
elseif($_SESSION['uid']!=1){
	header("location: index.php");
	}
else{
	$session_uid=$_SESSION["uid"];
	$session_name=$_SESSION["name"];
	$session_type=$_SESSION["type"];
	require('db_port.php');
	$dblink=db_init();
	}
?>

<html>
<head><title>account manage</title>
<link rel="stylesheet" href="style.css" type="text/css" /></head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name." <a href='passwd.php'>change password<a> | <a href='logout.php'>logout</a>"?>
	</div>
	<div class="banner">
	<img src="http://i.imgur.com/WvfBK.png" width="500" height="100" alt="人員管理" />
	</div>
	<div class='link'><ul>
		<?php 
		if($session_type=='a'){
			echo "<li><a class='links' href='tableedit.php'>table edit</a></li> ";
			echo "<li><a class='links' href='botcontrol.php'>robot control</a></li>";
			}
		if($session_uid==1){
			echo "<li/><a class='links' href='accountmanage.php'>account manage</a><li>";
			}
		?>
		</ul>
	</div>
	<div class='content' align='center'>
		<?php //view all manager
			if(isset($_POST['viewallmanager'])){
				$result=mysqli_query($dblink,"SELECT * FROM administers");
				echo "<table border='1'>";
				while($record=mysqli_fetch_assoc($result)){
					$record['type']=='a' ? $record['type']='可修改設定及詞彙' : $record['type']='可修改詞彙';
					echo "<tr><td>".$record['account']."</td><td>".$record['type'],"</td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='delete' value='".$record['account']."'><input type='submit' value='刪除'></form></td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='update' value='".$record['account']."'><input type='submit' value='修改'></form></td></tr>";
					}
				echo "</table>";
				}
			if (isset($_POST['delete'])){
				remove_user($dblink,$_POST['delete']);
				echo "remove sucessed!!";
				}
			if (isset($_POST['update'])){
				echo "<form name='update' action='' method='POST'><input type='hidden' name='updatet' value='".$_POST['update']."'><input type='radio' name='uauth' value='a' /> 可控制機器人跟修改詞彙";
				echo "<input type='radio' name='uauth' value='b' CHECKED>僅可修改詞彙<br /><input type='submit' value='修改'></form>";

				}
			if (isset($_POST['updatet'])){
				update_user_type($dblink,$_POST['updatet'],$_POST['uauth']);
				echo "update sucessed!!";
				}
		?>
		<form action='' method='post'><input type='hidden' name='viewallmanager'><input type='submit' value='click to view/refresh maneger list'></form>
		<br /><img src="http://i.imgur.com/OczEo.png" alt="新增人員" width="200" height="70" align="absbottom" /><br />
		<form action='' method='post'><input type='hidden' name='account_create'>
			<img src="http://i.imgur.com/w3ouy.png" alt="ID" width="200" height="70" align="absbottom" /><input type='text' name='name'><br />
			密碼：<input type='password' name='pass'><br />
			密碼確認：<input type='password' name='repass'><br />
			<img src="http://i.imgur.com/ZtirX.png" alt="權限" width="200" height="70" align="absbottom" /><br />
			<input type="radio" name="auth" value="a" /> 可控制機器人跟修改詞彙<input type="radio" name="auth" value="b" CHECKED/>僅可修改詞彙
			<br /><input type='submit' value='新增'>
		</form>
		<?php //add manager 
		if(isset($_POST['account_create'])){
			$pass=$_POST['pass'];
			$account=$_POST['name'];
			$type=$_POST['auth'];
			if($pass==$_POST['repass']){
				if (!get_user_data($dblink,$account)){	
					add_user($dblink,$account,$pass,$type);
					}
				else
					echo 'user already exists!!';
				}
			else
				echo "please recheck password.";
			}
		db_close($dblink);
		?>
	</div>
</body>
</html>