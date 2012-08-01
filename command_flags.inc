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

  define('CMD_PING',0);
  define('CMD_PAUSE',1);
  define('CMD_CONTINUE',2);
  define('CMD_SEND_PLURK', 3);
  define('CMD_RELOAD_SETTINGS', 4);
  define('CMD_RELOAD_TABLE',5);
  define('CMD_GET_PAUSE_STATUS',7);
  define('CMD_RELOGIN',6);
  define('CMD_VERSION',8);
  define('CMD_EXIT',225);
  
  define('FB_RUNNING', 'running');
  define('FB_PAUSED', 'paused');
  define('FB_ECHO', 'echo');
  define('FB_OK', 'okay');
  define('FB_ERROR_INVALID_COMMAND', 'invalid_command');
  define('FB_ERROR', 'error');
?>