<?php
require('command_flags.inc');
define('SOCKET_PATH','sockets/');
define('SERVER_SOCKET', 'sockets/ybot-socket');

echo 'Creating socket...';
$socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
$randomd=(string)rand();
$socketname=SOCKET_PATH."socket_shutdown_ybot-client".$randomd;

echo "\nBind client side socket...";
socket_bind($socket, $socketname);
socket_set_block($socket);
chmod($socketname, 0777);
$cmd_to_send = json_encode(array( 'command' => CMD_CONTINUE ));

echo "\nTry to send command to server...";
if(socket_sendto($socket, $cmd_to_send, strlen($cmd_to_send), 0, SERVER_SOCKET )){
  echo "OK";
  echo "\nWaiting for feedback...";
  $bfr = '';
  socket_recv($socket, $bfr, 65536, 0);
  if($bfr == FB_OK)
    echo 'OK!';
  else
    echo 'Unexpect feedback.';
}
else
  echo "Failed!";

echo "\nClose Socket.\n";
socket_set_nonblock($socket);
socket_close($socket);
unlink($socketname);
?>