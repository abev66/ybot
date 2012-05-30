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
    define('VERNUM','0.2.0');
    define('SUBVERNUM','20120528');
    define('OTHERMSG','DB-PHP-yaoming');
    
    // load php-plurk-api and database port.
    require('plurk_api.php');
    require('db_port.php');
    
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
 
//---------- Main ------------

    //  Initialize Data Source
    $data_source = db_init();

    //  Read configure
    $config = dump_settings($data_source);
    
    //  Login Plurk
    $plurk = new plurk_api();
    $plurk->login($config['API_KEY'], $config['PLURK_ACCOUNT'], $config['PLURK_PASSWORD']); 
    
    //  Get Own UID
    $own_profile = $plurk->get_own_profile();
    $uid = $own_profile->user_info->id;
    
    //  Reading speech table.
    $speech_table = dump_table($data_source);
    
    $pause = false;
    $runbot = true;
    $relogin = false;
    $counter = 0;
    
    
    //  Outter loop
    while ($runbot) {
      $counter += 1;
      
      //  Get Plurks
      $pu = $plurk->get_plurks(date('c'), 30);
      
      //  Apply all friend requests
      if( ($config['AUTO_ACCEPT_FRIENDS']=='true') && !$pause)
	$plurk->add_all_as_friends();
      
      $msg = array();
      
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
      
    if(!$pause) {
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
    if ( ((($counter*$config['CHECK_INTERVAL'])%14400) == 0 ) || $relogin ) {
      $plurk = new plurk_api();
      $plurk->login($config['API_KEY'], $config['PLURK_ACCOUNT'], $config['PLURK_PASSWORD']); 

      $own_profile = $plurk->get_own_profile();
      $uid = $own_profile->user_info->id;
      
      $counter = 0;
      $relogin = false;
    }
    
    // Reload configure
    $config = dump_settings($data_source);
    
    
        
    // Sleep for a while
    sleep($config['CHECK_INTERVAL']);
    
   }
   
?>
