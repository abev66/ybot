<?php
session_start(); 
if(!isset($_SESSION['_ybot_uid'])):
  header('Location: login.php');
else:
  require('db_port.php');
  $db_port = db_init();
  include('command_flags.inc');
  $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
  $randomd=(string)rand();
  $socketname="sockets/socket_ybot-client".$randomd; 
  socket_bind($socket, $socketname);
  socket_set_block($socket);
  chmod($socketname, 0777);
  $msg=json_encode(array( 'command' => CMD_RELOAD_TABLE ));
  $bytes_sent = socket_sendto($socket, $msg, strlen($msg), 0, 'sockets/ybot-socket' );
  header('Location: index.php');
  socket_set_nonblock($socket);
  socket_close($socket);
  unlink($socketname);
endif;
?>