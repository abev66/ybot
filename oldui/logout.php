<?php
  session_start();
  
  if(isset($_SESSION['_ybot_uid']))
    unset($_SESSION["_ybot_uid"], $_SESSION["_ybot_name"], $_SESSION["_ybot_type"]); 
  header('Location: index.php') 
?>