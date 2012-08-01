<?php
/*
 *  ybot.php - a plurk bot use php-plurk-api
 *
 *  Copyright (C) 2012 Wei-Chen Lai <abev66@gmail.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

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
$cmd_to_send = json_encode(array( 'command' => CMD_EXIT ));

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