<?php
  session_start();
  
  if(!isset($_SESSION['uid']))
    header("location: index.php");
  else {
    unset($_SESSION["uid"], $_SESSION["name"], $_SESSION["type"]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>see ya!</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <style>
      <!--
	.container>div {
/*	  position: fixed;*/
	  text-align: center;
	  display:block;
	  margin: 150px auto 0;
	}
      -->
    </style>
  </head>
  <body>
    <div class='container'>
      <div>Logout successfully!!<br /> <a href='login.php'>Relogin with another account<a></div>
    </div>
  </body>
</html>
<?php } ?>