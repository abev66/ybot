<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 
require('db_port.php');
$dblink=db_init();
session_start();
?>
<html>
<head>
	<title>login</title>
	<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
	<div class='banner' align='center'><img src="http://i.imgur.com/xQgmj.png" width="500" height="200" alt="歡迎來到登入頁面" /></div>
	<div class='content' align='center'>
		<form name="logconfirm" action=""  method="post">
			name:<input type="text" name="name"><br />
			pass: <input type="password" name="passw"><br />
			<input type="submit" value="登入">
		</form>
	<?php //form check
	if (isset($_POST["name"])){
		$name=$_POST["name"];
		if(!$result=get_user_data($dblink, $name))
			echo "login failed!!<br />the user doesn't exist!!";
		elseif(sha1($_POST['passw'])!=$result['password'])
			echo "login failed!!<br />wrong password";
		else{
			db_close($dblink);
			session_regenerate_id(true);
			$_SESSION["uid"] = $result['uid'];
			$_SESSION["name"] = $result['account'];
			$_SESSION["type"] = $result['type'];
			echo "welcom back, ",$result['account'],"<br /><br />redirecting....";
			header("Refresh:1;url=index.php");
			}
		}
	
	?>
	</div>
</body>
</html>
