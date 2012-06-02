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

//---------- DEFINE ------------    
    // Information about this bot.
    define('APPNAME','ybot');
    define('VERNUM','0.2.1');
    define('SUBVERNUM','20120603');
    define('OTHERMSG','DB-PHP-yaoming');
    define('SOCKET_ADDR','sockets/ybot-socket');
    
    // load php-plurk-api and database port.
    require('plurk_api.php');
    require('db_port.php');

    // command flags
    include('command_flags.inc');

//---------- Functions ------------
    
    function pick_sentence( $qualifier , $content_raw, $table ) {
      $sentences_matched = array();
      $sentences_generic = array();
      foreach ( $table as $key => $value ) 
	foreach( $value['keywords'] as $item ) 
	  if( (strpos(strtolower($qualifier.$content_raw), strtolower($item)) !== false)) {
	    $sentences_matched[] = array(
	      "qualifier" => $table[$key]['qualifier'],
	      "content" => $table[$key]['response']
	      );
	    break;
	  } else if($item == '*') {
	    $sentences_generic[] = array(
	      "qualifier" => $table[$key]['qualifier'],
	      "content" => $table[$key]['response']
	      );
	    break;
	  }
	  
      $sentences_generic_length = count($sentences_generic);
      $sentences_matched_length = count($sentences_matched);
      
      if(empty($sentences_matched)){
      
	if(empty($sentences_generic))
	  return NULL;
	else
	  $ret = $sentences_generic[ rand(0, $sentences_generic_length-1 ) ];

      } else if($sentences_matched_length < $sentences_generic_length) {
	  $ret = (rand(0,1)==0) ? $sentences_generic[rand(0,$sentences_generic_length-1)] : $sentences_matched[rand(0,$sentences_matched_length-1)];
	  
      } else if($sentences_matched_length >= $sentences_generic_length*2 ) {
	  $temp = array_merge( $sentences_generic, $sentences_matched );
	  $ret = $temp[ rand( 0, count($temp)-1 ) ];
	  
      } else {
	  $ret = (rand(0,2)==0) ? $sentences_generic[rand(0,$sentences_generic_length-1)] : $sentences_matched[rand(0,$sentences_matched_length-1)];
	  
      }
      
      return $ret;
    }
    
    function is_mention ( $message, $myname ) {
      if(strpos($message, "@$myname")!==false)
	return true;
      return false;
    }
    
    function replied ( $response_count , $pid, $uid ) {
      global $plurk;
      if($response_count == 0)
	return false;
      else {
	$responses = $plurk->get_responses($pid);
	if(!empty($responses->responses)) {
	foreach($responses->responses as $element) {
	  if($element->user_id == $uid)
	    return true;
	}
      }
      return false;
      }
    }
    
    
    // Convert UID to Plurk Nickname
    function get_nickname($uid, $data) {
      foreach( $data as $key => $item ) {
	  if(intval($key) == $uid)
	    return $item->nick_name;
      }
    }
    
    // Run command recived from client
    function run_command($str_command, $socket_server_side, $socket_client_side, $control_vars){
    
      // Decode command
      $command = json_decode($str_command);
      
      switch($command['command']){
      
	case CMD_PING:
	  socket_sendto($socket_server_side, FB_ECHO, strlen(FB_ECHO), 0, $socket_client_side);
	  break;
	  
	case CMD_GET_PAUSE_STATUS:
	  $msg = json_encode( (!$control_vars['pause']) ? FB_RUNNING : FB_PAUSED );
	  socket_sendto($socket_server_side, $msg, strlen($msg), 0, $socket_client_side);
	  break;
	  
	case CMD_PAUSE:
	  $control_vars['pause'] = true;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
	  
	case CMD_CONTINUE:
	  $control_vars['pause'] = false;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
	  
	case CMD_SEND_PLURK:
	  global $plurk;
	  
	  // Fill data
	  include('plurk_lang_flags.inc');
	  $lang = isset($command['lang']) ? $command['lang'] : PLURK_LANG_CHINESE_TRADITIONAL;
	  $qualifier = isset($command['qualifier']) ? $command['qualifier'] : 'says';
	  $no_comments = isset($command['no_comments']) ? $command['no_comments'] : 0;
	  $content = isset($command['content']) ? $command['content'] : NULL ;
	  
	  if( $content !== NULL && mb_strlen($content, 'UTF-8') <= 140 ){
	    $plurk->add_plurk($lang, $qualifier, $content, NULL, $no_comments);
	    socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  } else 
	    socket_sendto($socket_server_side, FB_ERROR, strlen(FB_ERROR), 0, $socket_client_side);

	  break;
	  
	case CMD_RELOAD_SETTINGS:
	  global $config, $data_source;
	  $new_config = dump_settings($data_source);
	  
	  if($new_config == NULL)
	    socket_sendto($socket_server_side, FB_ERROR, strlen(FB_ERROR), 0, $socket_client_side);
	  else {
	    socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	    $config = $new_config;
	  }  
	  break;
	  
	case CMD_RELOAD_TABLE:
	  global $speech_table, $data_source;
	  $new_table = dump_table($data_source);
	  
	  if($new_table == NULL)
	    socket_sendto($socket_server_side, FB_ERROR, strlen(FB_ERROR), 0, $socket_client_side);
	  else {
	    socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	    $speech_table = $new_table;
	  }
	  break;
	  
	case CMD_RELOGIN:
	  $control_vars['relogin'] = true;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
	  
	case CMD_EXIT:
	  $control_vars['runbot'] = false;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
	  
	default:
	  socket_sendto($socket_server_side, FB_ERROR_INVALID_COMMAND, strlen(FB_ERROR_INVALID_COMMAND), 0, $socket_client_side);
	  
      }
      
      return $control_vars;
    }
 
//---------- Main ------------

    //  Initialize Data Source
    $data_source = db_init();

    //  Read configure
    $config = dump_settings($data_source);
    
    // Create Socket
    $bot_socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($bot_socket, SOCKET_ADDR, 0);
    socket_set_block($bot_socket);
    
    //  Login Plurk
    $plurk = new plurk_api();
    $plurk->login($config['API_KEY'], $config['PLURK_ACCOUNT'], $config['PLURK_PASSWORD']); 
    
    //  Get Own UID
    $own_profile = $plurk->get_own_profile();
    $uid = $own_profile->user_info->id;
    
    //  Reading speech table.
    $speech_table = dump_table($data_source);
    
    // Initialize Control Variables
    $control_vars = array(
      'pause' => false,
      'runbot' => true,
      'relogin' => false,
      'count' => 0
    );
    
    
    //  Outter loop
    while ($control_vars['runbot']) {
      $control_vars['count'] += 1;
      
      //  Get Plurks
      $pu = $plurk->get_plurks(date('c'), 30);
      
      //  Apply all friend requests
      if( ($config['AUTO_ACCEPT_FRIENDS']=='true') && !$control_vars['pause'])
	$plurk->add_all_as_friends();
	
      // Get command from socket
      $cmd_buffer = '';
      $cmd_source = '';
      @socket_recvfrom($socket, $cmd_buffer, 65536, MSG_DONTWAIT, $cmd_source);
      
      
      if(!empty($cmd_buffer))
	$control_vars = run_command($cmd_buffer, $bot_socket, $cmd_source, $control_vars);
      
      $msg = array();
      
      if(!$control_vars['pause']){
	// Check each plurks.
	foreach( $pu->plurks as $item ) {
	  // dont reply by default.
	  $do_reply = false;
	  
	  // Only Check if the plurk can add response.
	  if($item->no_comments != 1) {
	      if ( ( !replied($item->response_count, $item->plurk_id, $uid)) && ( (is_mention($item->content_raw, $config['PLURK_ACCOUNT']) || $config['RESPONSE_MODE'] == 'ANYWAY') && ($config['RESPONSE_MODE'] != 'DISABLED') ) && ($item->owner_id != $uid) ) {
		  $do_reply = true; 
		  
		  $msg = array(
		    'qualifier' => $item->qualifier,
		    'content' => $item->content_raw,
		    'id' => $item->owner_id,
		    'nick_name' => get_nickname($item->owner_id, $pu->plurk_users),
		    'mention' => false
		  );
	      } else if ( ($config['CHECK_RESPONSES'] == 'true') && ($item->is_unread == 1)) {
		  $responses = $plurk->get_responses($item->plurk_id);
			  
		  for ($i=0;$i<count($responses->responses);$i++) {
		    $resItem = $responses->responses[$i];
		    
		    if(is_mention($resItem->content_raw, $config['PLURK_ACCOUNT']) && ($resItem->user_id != 	$uid) ) {
		      $do_reply = true;
		      
		      $msg = array (
			'qualifier' => $resItem->qualifier,
			'content' => $resItem->content_raw,
			'id' => $resItem->user_id,
			'nick_name' => get_nickname($resItem->user_id, $responses->friends),
			'mention' => true
		      );
		    }
		    else if( $resItem->user_id == $uid )
		      $do_reply = false;
		  }
		  
	      } 
	  
	      
	    if($do_reply) {
	      // do reply
	      if(($sentence = pick_sentence( $msg['qualifier'] , $msg['content'], $speech_table )) !== NULL) {
		if($msg['mention'])
		  $sentence['content'] = '@'.$msg['nick_name'].': '.$sentence['content'];
		
		$plurk->add_response( $item->plurk_id , $sentence['content'], $sentence['qualifier'] );
		echo "\n[".$msg['qualifier'].']'.$msg['content']."->\n\t[".$sentence['qualifier'].']'.$sentence['content']."\n";
	      }
	    }
	    
	    // Record finish plurks
	    $read_plurks[] = $item->plurk_id ;
	  } else
	  // Mute plurks which cannot add responses.
	    $mute_plurks[] = $item->plurk_id;
	}

      // Mark as Read or Mute 
      if($config['MUTE_AFTER_RESPONSE'] == 'true') {
	$read_plurks = array_merge($mute_plurks, $read_plurks);
	$plurk->mute_plurks($read_plurks);
      }
      else if( empty($mute_plurks) )
	$plurk->mark_plurk_as_read($read_plurks);
      else {
	$plurk->mark_plurk_as_read($read_plurks);
	$plurk->mute_plurks($mute_plurks);
      }
      // empty after finish
      $read_plurks = array();
      $mute_plurks = array();
    }
    
    // relogin every 4 hours
    if ( ( !$control_vars['pause'] && (($control_vars['count']*$config['CHECK_INTERVAL'])%14400) == 0 ) || $control_vars['relogin'] ) {
      $plurk = new plurk_api();
      $plurk->login($config['API_KEY'], $config['PLURK_ACCOUNT'], $config['PLURK_PASSWORD']); 

      $own_profile = $plurk->get_own_profile();
      $uid = $own_profile->user_info->id;
      
      $control_vars['count'] = 0;
      $control_vars['relogin'] = false;
    }
    
    // Reload configure
    $config = dump_settings($data_source);
        
    // Sleep for a while
    sleep($config['CHECK_INTERVAL']);
    
   }
   
   // Close socket
   socket_set_nonblock($bot_socket);
   socket_close($bot_socket);
   unlink(SOCKET_ADDR);
?>
