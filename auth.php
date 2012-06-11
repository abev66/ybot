<?php 
require('db_port.php');
$db_port=db_init();

if(isset($_POST['account'])){
  $result = get_user_data( $db_port, $_POST['account'] );
  db_close($db_port);
  
  if( !$result )
    header( "Location: login.php?error=" );
  else if( sha1($_POST['password']) == $result['password'] ) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['_ybot_uid'] = $result['uid'];
    $_SESSION['_ybot_account'] = $result['account'];
    $_SESSION['_ybot_type'] = $result['type'];
    header('Location: responseedit.php');
  } else 
    header( "Location: login.php?error=" );
}
?>
