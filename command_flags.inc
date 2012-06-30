<?php
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