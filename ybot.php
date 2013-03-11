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
    define('VERNUM','0.3.0');
    define('SUBVERNUM','20130310');
    define('OTHERMSG','DB-PHP-yaoming');
    define('SOCKET_ADDR','sockets/ybot-socket');
    $randomd=(string)rand();
    define('PROCESS_SOCKET_ADDR','sockets/ybot-process-'.$randomd);
    
    // load php-plurk-api and database port.
    require('plurk_api.php');
    require('db_port.php');
    
    // hash file ver
    $db_port_hash = md5_file('db_port.php');
    $this_hash = md5_file($_SERVER['SCRIPT_NAME']);

    // include flags
    include('command_flags.inc');
    include('plurk_lang_flags.inc');

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
	  } else if($item == '*') {
	    $sentences_generic[] = array(
	      "qualifier" => $table[$key]['qualifier'],
	      "content" => $table[$key]['response']
	      );
	  }
	  
      $sentences_generic_length = count($sentences_generic);
      $sentences_matched_length = count($sentences_matched);
      
      if(empty($sentences_matched)){
      
	if(empty($sentences_generic))
	  return NULL;
	else
	  $ret = $sentences_generic[ rand(0, $sentences_generic_length-1 ) ];

      } else if($sentences_matched_length < $sentences_generic_length) {
	  $ret = (rand(0,6)==0) ? $sentences_generic[rand(0,$sentences_generic_length-1)] : $sentences_matched[rand(0,$sentences_matched_length-1)];
	  
      } else if($sentences_matched_length >= $sentences_generic_length*5 ) {
	  $temp = array_merge( $sentences_generic, $sentences_matched );
	  $ret = $temp[ rand( 0, count($temp)-1 ) ];
	  
      } else {
	  $ret = (rand(0,4)==0) ? $sentences_generic[rand(0,$sentences_generic_length-1)] : $sentences_matched[rand(0,$sentences_matched_length-1)];
	  
      }
      
      return $ret;
    }
    
    function is_mention ( $message, $myname ) {
      if(strpos($message, "@$myname")!==false)
	return true;
      return false;
    }
    
    function is_friend ( $did , $flist ){
      foreach( $flist as $item )
	if($item->uid == $did)
	  return true;
      return false;
    }
    
    function get_friends( $plurk, $uid ){
      $offset = 0;
      static $ret = array();
      static $last_ret;
      static $last_friendcount = 0;
      $friendcount = $plurk->get_own_profile()->friends_count;
      
      if($last_friendcount == $friendcount && is_array($ret))
	return $ret;
	
      $ret = array();
      $last_friendcount = $friendcount;

      do {
	do {
	  $loop_count = 0;
	  $temp = $plurk->get_friends($uid, $offset);
	  if($loop_count > 0) {
	    sleep(1);
	    if($loop_count >= 5) {
	      echo 'Get friend list failed!';
	      return $last_ret;
	    }
	  }
	  $loop_count++;
	} while(!is_array($temp));
	
	$ret = array_merge($ret, $temp);
	$offset +=10;
      } while(count($ret) <= $friendcount && !empty($temp) );
      
      $last_ret = $ret;
      
      return $ret;
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
      $command = json_decode($str_command, true);
      
      switch($command['command']){
      
	case CMD_PING:
	  socket_sendto($socket_server_side, FB_ECHO, strlen(FB_ECHO), 0, $socket_client_side);
	  break;
	  
	case CMD_GET_PAUSE_STATUS:
	  $msg = (!$control_vars['pause']) ? FB_RUNNING : FB_PAUSED ;
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
	  
	// FIXME: Relogin feature temporary unavailable.
/*	case CMD_RELOGIN:
	  $control_vars['relogin'] = true;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
*/
	  
	case CMD_EXIT:
	  $control_vars['runbot'] = false;
	  socket_sendto($socket_server_side, FB_OK, strlen(FB_OK), 0, $socket_client_side);
	  break;
	  
	case CMD_VERSION:
	  global $this_hash, $db_port_hash;
	  $ver_info = array(
	    'NAME' => APPNAME,
	    'VERNUM' => VERNUM,
	    'SUBVERSION' => SUBVERNUM,
	    'COMMENTS' => OTHERMSG,
	    'HASH VER' => substr($this_hash, 0, 9) ,
	    'DBPORT VER' => substr($db_port_hash, 0, 9)
	  );
	  $content = json_encode($ver_info);
	  socket_sendto($socket_server_side, $content, strlen($content), 0, $socket_client_side);
	  break;
	  
	default:
	  socket_sendto($socket_server_side, FB_ERROR_INVALID_COMMAND, strlen(FB_ERROR_INVALID_COMMAND), 0, $socket_client_side);
	  
      }
      
      return $control_vars;
    }
 
//---------- Main ------------

    //  Initialize Data Source
    $data_source = db_init('ybot');

    //  Read configure
    $config = dump_settings($data_source);
    
    // Create Socket
    $bot_socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($bot_socket, SOCKET_ADDR, 0);
    socket_set_block($bot_socket);
    chmod(SOCKET_ADDR, 0777);
    
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
    
    $loop_count = 0;
    
    // Fork
    $process_id = pcntl_fork();
    
    if($process_id == -1) {    
      socket_set_nonblock($bot_socket);
      socket_close($bot_socket);
      unlink(SOCKET_ADDR);
      die('Could not fork.');
    } else if (!$process_id) {
      $first_run = true;
      
      // Create Child Socket
      $process_socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
      socket_bind($process_socket, PROCESS_SOCKET_ADDR.'-child');
      socket_set_block($process_socket);
      chmod(PROCESS_SOCKET_ADDR.'-child', 0777);
      
      // Get Realtime user channel
      $channel = $plurk->realtime_get_user_channel();
      
      while($control_vars['runbot']) {
	if($first_run){	  
	  $raw_result = $plurk->get_plurks(date('c'), 30);
	  $ret_plurks = $raw_result->plurks;
	  $first_run = false;
	} else {
	  $offset = -1;
	  $raw_result = $plurk->realtime_get_commet_channel($channel->comet_server);
	  $result_str = substr($raw_result,28,-2);
	  $result = json_decode($result_str);
	  $plurk_ids = array();
	  $ret_plurks = array();
	  
	  if(isset($result->data)){
	      foreach($result->data as $item)
		$plurk_ids[] = $item->plurk_id;
		
	      foreach($plurk_ids as $id){
		$temp = $plurk->get_plurk($id);
		$ret_plurks[] = $temp->plurk;
		}
		
	      $offset = $result->new_offset;
	    }
	}
	
	$buffer = '';
	
	if(!empty($ret_plurks)){
	  $return_raw = json_encode($ret_plurks);
	
	  socket_sendto($process_socket, $return_raw, strlen($return_raw), 0, PROCESS_SOCKET_ADDR );
	  socket_recv($process_socket, $buffer, 1048576, 0);
	  
	  unset($ret_plurks);
	} else {
	  @socket_recv($process_socket, $buffer, 1048576, MSG_DONTWAIT);
	}
	
	if(substr($buffer,0,3) == '999')
	  $control_vars['runbot'] = false;
      }      
      socket_set_nonblock($process_socket);
      socket_close($process_socket);
      unlink(PROCESS_SOCKET_ADDR.'-child');
      
    } else {
    // Create parent side socket.
    $process_socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($process_socket, PROCESS_SOCKET_ADDR, 0);
    socket_set_block($process_socket);
    chmod(PROCESS_SOCKET_ADDR, 0777);
    
    //  Outter loop
    while ($control_vars['runbot']) {
      $control_vars['count']++;
      
      // Get command from socket
      $cmd_buffer = '';
      $cmd_source = '';
      @socket_recvfrom($bot_socket, $cmd_buffer, 65536, MSG_DONTWAIT, $cmd_source);
      
      while(!empty($cmd_buffer)){
	$control_vars = run_command($cmd_buffer, $bot_socket, $cmd_source, $control_vars);
	$cmd_buffer = '';
	@socket_recvfrom($bot_socket, $cmd_buffer, 65536, MSG_DONTWAIT, $cmd_source);
      }

      if( !$control_vars['pause'] ) {
	$buffer = '';
	$msg_source = '';
	// Get message from child.
	@socket_recvfrom($process_socket, $buffer, 1048576, MSG_DONTWAIT, $msg_source);
	
	// FIXME: It should only accept messages from child process.
	if(!empty($buffer)) {
	  $pu = json_decode($buffer);
	  
	  $return_code = ($control_vars['runbot'] == true) ? '200' : '999';
	  socket_sendto($process_socket, $return_code, strlen($return_code), 0, PROCESS_SOCKET_ADDR."-child");
	  
	  //  Get Plurks and friends list
	  $friend_list = get_friends($plurk, $uid);
	  
	  //  Apply all friend requests
	  if( ($config['AUTO_ACCEPT_FRIENDS']=='true'))
	    $plurk->add_all_as_friends();
	    
	  $msg = array();
	  
	  // Check each plurks.
	  foreach( $pu as $item ) {
	    // dont reply by default.
	    $do_reply = false;
	    
	    // Only Check if the plurk can add response.
	    if($item->no_comments != 1 && (is_friend($item->owner_id, $friend_list) || ($config['SKIP_REPLURKS'] == 'false'))) {
		if ( ( !replied($item->response_count, $item->plurk_id, $uid)) && ( (is_mention($item->content_raw, $config['PLURK_ACCOUNT']) || $config['RESPONSE_MODE'] == 'ANYWAY') && ($config['RESPONSE_MODE'] != 'DISABLED') ) && ($item->owner_id != $uid) ) {
		    $do_reply = true; 
		    
		    $msg = array(
		      'qualifier' => $item->qualifier,
		      'content' => $item->content_raw,
		      'id' => $item->owner_id,
		      'nick_name' => get_nickname($item->owner_id, $pu->plurk_users),
		      'mention' => false
		    );
		} else if ( ($config['CHECK_RESPONSES'] == 'true') ) {
		    $responses = $plurk->get_responses($item->plurk_id);
			    
		    foreach( $responses->responses as $i=>$resItem){
		      
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
	      if($config['MUTE_AFTER_RESPONSE'] == 'true')
		$plurk->mute_plurks(array($item->plurk_id));
	      else
		$plurk->mark_plurk_as_read(array($item->plurk_id));
	    } else
	    // Mute plurks which cannot add responses.
	      $plurk->mute_plurks(array($item->plurk_id));
	  }

	  // empty after finish
	  $read_plurks = array();
	  $mute_plurks = array();
	} else
	  usleep(500000);
      }
    
   $loop_count++;
   }
   
   // Close child.
   socket_sendto($process_socket, '999', strlen('999') , 0, PROCESS_SOCKET_ADDR."-child");
   pcntl_wait($status);
   
   socket_set_nonblock($process_socket);
   socket_close($process_socket);
   unlink(PROCESS_SOCKET_ADDR);
   
   // Close socket
   socket_set_nonblock($bot_socket);
   socket_close($bot_socket);
   unlink(SOCKET_ADDR);
   }
?>
