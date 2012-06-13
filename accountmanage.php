<?php 
session_start(); 
if($_SESSION['_ybot_uid']!=1 || !isset($_SESSION['_ybot_uid'])){
	header('Location: login.php');
}
else{
	require('db_port.php');
	$dblink = db_init();
?>
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ybot - Account manage</title>
    <link rel='stylesheet' href='style.css' type='text/css'>
	    <style type='text/css'>
     <!--
      .add input[type='text'],.add input[type='password'] {
	width: 100%;
	margin-left: auto;
	display:block;
	margin-bottom: 10px;
	border: 1px solid #999;
	border-radius: 3px;
      }
    .add input[type='submit']{
	display: block;
	margin: 0.8em auto 0;
      }
      
      /* Login Window */
      .add {
	background: #FFF;
	opacity: 0.7;
	border-radius: 5px;
	display: block;
	margin: 2em auto;
	padding: 2em 2.5em;
	max-width: 460px;
	text-align: left;
	vertical-align: middle;
	box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.2);
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
      }
	
      h3 {
	margin: 12px auto;
	font-weight: bold;
	font-size: 133%;
	display:block;
	font-weight: normal;
	text-align: center;
      }
      
      .add:hover{
	opacity: 1;
      }
     -->
    </style>
  </head>
  <body>
    <div class='container'>
	<?php include('header.inc'); ?>
	<?php include('navbar.inc'); ?>
		<form class='search' action='' method='GET'>
			<input type='text' name='searchw'>
			<input id='search-btn' type='submit' value='search'>
		</form>
		<h3>Account List</h3>
<?php
  if(isset($_GET['searchw']) && !empty($_GET['searchw'])) {
    $result=get_user_data($dblink);
    $tmp=array();
    foreach($result as $i)
	    if (strpos($i['account'], trim($_GET['searchw'])) !== false){
		    $tmp[]=$i;
	    }
    $result=$tmp;
    echo "<div class='notice-green'>Search result of ".$_GET['searchw']." . <a href='accountmanage.php'>Display All</a></div>";
  }
?>
		<table><th>Account</th><th>Type</th><th colspan='2'>Action</th>
		<?php //view all manager
			if(!isset($_GET['searchw']) || empty($_GET['searchw'])){
				$result=get_user_data($dblink);
				foreach($result as $record){
					if ($record['uid']==1)
						continue;
					$record['type']=='a'? $record['type']='A' : $record['type']='B';
					echo "<tr><td>".$record['account']."</td><td>".$record['type'],"</td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='delete' value='".$record['account']."'><input type='button' value='Delete' onclick='javascript: if(confirm(\"Are you sure?\")) this.form.submit();' title='Destroy this account!!'></form></td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='update' value='".$record['account']."'><input type='hidden' name='updatet' value='".$record['type']."'><input type='submit' value='Switch' title='Switch Account Type'></form></td></tr>";
					}
			}else {
				foreach($result as $record){
					if ($record['uid']==1)
						continue;
					$record['type']=='a'? $record['type']='A' : $record['type']='B';
					echo "<tr><td>".$record['account']."</td><td>".$record['type'],"</td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='delete' value='".$record['account']."'><input type='submit' value='Delete' title='Destroy this account!!'></form></td>";
					echo "<td><form action='' method='POST'><input type='hidden' name='update' value='".$record['account']."'><input type='hidden' name='updatet' value='".$record['type']."'><input type='submit' value='Switch' title='Switch Account Type'></form></td></tr>";
					}
				}
		?>
		</table>
		<?php
			if (isset($_POST['delete'])){
				remove_user($dblink,$_POST['delete']);
				db_close($dblink);
				header('location: accountmanage.php');
				}
			if (isset($_POST['updatet'])){
				$type = ($_POST['updatet']=='A' ? 'b' : 'a');
				if (!update_user_type($dblink,$_POST['update'],$type)){
					db_close($dblink);
					header('Location: accountmanage.php');
					}
				}
		?>
		<div style='font-size: 90%; text-align: center;'>a:can control robot and edit keywords and responses, b:can only edit keywords and responses</div>
		<div class='add'>
		<h3>Create new user</h3>
		<form action='' method='post'><input type='hidden' name='account_create'>
			Account name<input type='text' name='name'><br />
			Password<input type='password' name='pass'><br />
			Confirm password<input type='password' name='repass'><br />
			<div style='text-align: center'>Type
			<input type="radio" name="auth" value="a" />A<input type="radio" name="auth" value="b" CHECKED/>B</div>
			<br /><input type='submit' value='Create'>
		</form>
		</div>
		<?php //add manager 
		if(isset($_POST['account_create'])){
			$pass=$_POST['pass'];
			$account=$_POST['name'];
			$type=$_POST['auth'];
			if(strlen($account) < 3){
			  echo "<div class='notice-red'>please make account name longer</div>";
			} else	if($pass==$_POST['repass'] && strlen($pass)>6){
				if (!get_user_data($dblink,$account)){	
					add_user($dblink,$account,$pass,$type);
					db_close($dblink);
					header('Location: accountmanage.php');
					}
				else
					echo "<div class='notice-red'>user already exists!!</div>";
				}
			else
				echo "<div class='notice-red'>please recheck password.</div>";
			}
		?>
    </div>
  </body>
</html>
<?php
db_close($dblink);
 }?>