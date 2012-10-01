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

session_start(); 
if(!isset($_SESSION['_ybot_uid'])):
  header('Location: login.php');
else:
  require('db_port.php');
  $db_port = db_init($_SESSION[_ybot_uid]);
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