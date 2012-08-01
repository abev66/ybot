<?php
/*
 *  ybot.php - a plurk bot use php-plurk-api
 *
 *  Copyright (C) 2012 Wei-Chen Lai <abev66@gmail.com>
 *                2012 Zheng-Yen Hong
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

// db_port.php - Database port for ybot using MySQL.

  require('db_port_config.inc');
  
//--------------- functions ---------------

  // Database initialize
  function db_init($host = HOST, $user = USER, $password = PASSWORD, $dbname = DBNAME){
  
    $ret = @mysqli_connect($host, $user, $password, $dbname);
    mysqli_query($ret, 'SET CHARACTER SET utf8');
    mysqli_query($ret, "SET collation_connection = 'utf8_general_ci'");	// dirty works
    mysqli_query($ret,'SET NAMES utf8');
    mysqli_query($ret,'SET CHARACTER_SET_CLIENT=utf8');
    mysqli_query($ret,'SET CHARACTER_SET_RESULTS=utf8');
    
    return $ret;
  }
  
  function db_close($db){
    mysqli_close($db);
  }
  
// Keywords Managemant

  // Add 
  function add_keywords($db, $keywords){
    $ret = NULL;
    
    if(is_array($keywords)){
      $sql_command = 'INSERT INTO '.TABLE_KEYWORDS.'(keyword) VALUES(\'%s\');';
      
      foreach($keywords as $item){
	  if(!mysqli_query($db, sprintf($sql_command, command_escape($item))))
	    $ret += mysqli_error($db).'; ';
      }
    } else {
      $result = mysqli_query($db, 'INSERT INTO '.TABLE_KEYWORDS.'(keyword) VALUES(\''.command_escape($keywords).'\');');
      if(!$result)
	$ret = mysqli_error($db);
    }
      
    return $ret;
  }

  // Remove
  function remove_keywords($db, $keywords){
    $ret = NULL;
    if(is_array($keywords)){
      $sql_command = 'DELETE FROM'.TABLE_KEYWORDS.' WHERE \'%s\';';
      foreach($keywords as $item){
	  if(!mysqli_query($db, sprintf($sql_command, command_escape($item)))){
	    $ret += mysqli_error($db).'; ';
	  }
      }
    } else {
      $result = mysqli_query($db, 'DELETE FROM '.TABLE_KEYWORDS.' WHERE keyword=\''.command_escape($keywords).'\';');
      
      if(!$result)
	$ret = mysqli_error($db);
    }
    
    return $ret;
  }
	
  // Update
  function update_keyword($db, $from_keyword, $to_keyword){
    $result = mysqli_query($db,
      "SELECT * FROM ".TABLE_KEYWORDS." WHERE keyword='".command_escape($to_keyword)."';");
    if( mysqli_num_rows($result) <= 0 )
      $record = mysqli_query($db,"UPDATE ".TABLE_KEYWORDS." SET keyword='".command_escape($to_keyword)."' WHERE keyword='".command_escape($from_keyword)."';");
    else
      remove_keywords($db, $from_keyword);

    return $record ? NULL : mysqli_error($db);
}

  // Query table using keyword, empty for return all keywords.
  function output_table_keyword($db, $keyword='', $ignore_casing = false){
    $ret=array();
	if ($keyword == ''){
		$result = mysqli_query($db, "SELECT * FROM ".TABLE_KEYWORDS." ORDER BY keyword;");
		while($record=mysqli_fetch_assoc($result))
			$ret[]=$record;
		}
	else{
		$sql_command = $ignore_casing ? 
		  "SELECT b.keyword FROM ".TABLE_KEYWORDS." AS b WHERE UPPER(b.keyword) LIKE UPPER('%".command_escape($keyword)."%') ORDER BY b.keyword;" :
		  "SELECT b.keyword FROM ".TABLE_KEYWORDS." AS b WHERE b.keyword LIKE '%".command_escape($keyword)."%' ORDER BY b.keyword;" ;
		$result=mysqli_query($db, $sql_command);
		
		while ($record=mysqli_fetch_assoc($result))
			$ret[]=$record;
		}
    return $result ? $ret : mysqli_error($db);
  }
  
// list keyword's all responses.
  function list_relation_keyword($db, $keyword){
	$result=mysqli_query($db,"SELECT a.qualifier, a.response FROM ".TABLE_RESPONSES." as a, ".TABLE_KEYWORDS." as b, ".TABLE_RELATIONS." as c WHERE b.keyword = '".command_escape($keyword)."' AND a.rid=c.rid AND b.kid=c.kid ORDER BY a.response;");
	while ($record=mysqli_fetch_assoc($result))
		$ret[]=$record;	
    return $result ? $ret : mysqli_error($db);
	}
  
// Sentenses Managemant

  // Add
  function add_sentence($db, $qualifier, $sentence){
    $result = mysqli_query($db, 
	"INSERT ".TABLE_RESPONSES."(qualifier, response) VALUES('".command_escape($qualifier)."', '".command_escape($sentence)."');"
    );
    
    return $result ? NULL : mysqli_error($db);
  }
  
  // Remove
  function remove_sentence($db, $sentence){
    $result = mysqli_query($db,
      command_escape(
	"DELETE FROM ".TABLE_RESPONSES." WHERE response='".command_escape($sentence)."';"
      )
    );
    
    return $result ? NULL : mysqli_error($db);
  }
  
  // Update
  function update_sentence($db, $from_qualifier, $from_sentence, $to_qualifier, $to_sentence){
    $result = mysqli_query($db,
	"SELECT * FROM ".TABLE_RESPONSES." WHERE qualifier='".command_escape($to_qualifier)."' AND response='".command_escape($to_sentence)."';"
      );
    
    if( mysqli_num_rows($result) <= 0 ) {
      $result = mysqli_query($db,
	"UPDATE ".TABLE_RESPONSES." SET qualifier='".command_escape($to_qualifier)."', response='".command_escape($to_sentence)."' WHERE qualifier='".command_escape($from_qualifier)."' AND response='".command_escape($from_sentence)."';"
      );
    } else
      remove_sentence($db, $from_qualifier, $from_sentence);
    
    return $result ? NULL : mysqli_error($db);
  }

  // Query table using response, empty for return all responses.
  function output_table_response($db, $reply='', $ignore_casing = false){
    $ret=array();
	if ($reply == ''){
		$result = mysqli_query($db, "SELECT * FROM ".TABLE_RESPONSES." ORDER BY response;");
		while($record=mysqli_fetch_assoc($result))
			$ret[]=$record;
		}
	else{
		$sql_command = $ignore_casing ?
		  "SELECT a.qualifier,a.response FROM ".TABLE_RESPONSES." as a WHERE UPPER(a.response) LIKE UPPER('%".command_escape($reply)."%') ORDER BY a.response;" :
		  "SELECT a.qualifier,a.response FROM ".TABLE_RESPONSES." as a WHERE a.response LIKE '%".command_escape($reply)."%' ORDER BY a.response;" ;
		$result=mysqli_query($db, $sql_command);
		while ($record=mysqli_fetch_assoc($result))
			$ret[]=$record;
		}
    return $result ? $ret : mysqli_error($db);
  }

// list response's all keywords
  function list_relation_response($db, $response){
	$result=mysqli_query($db,"SELECT b.keyword FROM ".TABLE_RESPONSES." as a, ".TABLE_KEYWORDS." as b, ".TABLE_RELATIONS." as c WHERE a.response = '".command_escape($response)."' AND a.rid=c.rid AND b.kid=c.kid ORDER BY b.keyword;");
	while ($record=mysqli_fetch_assoc($result))
		$ret[]=$record;	
    return $result ? $ret : mysqli_error($db);
	}

  
// Administer Managemant

  // Add
  function add_user($db, $username, $password, $type){
    $result = mysqli_query($db,
	"INSERT ".TABLE_ADMIN."(account, password, type) VALUES('$username', '".sha1($password)."', '$type');"
    );
    
    return $result ? NULL : mysqli_error($db);
  }

  // Remove
  function remove_user($db, $username){
    $userinfo = get_user_data($db, $username);
    if($userinfo['uid']==1){
      return false;
    } else {
      $result = mysqli_query($db,
	  "DELETE FROM ".TABLE_ADMIN." WHERE account='$username';"
      );
    }
  }
  
  // Modify user type
  function update_user_type($db, $account ,$type){
    $result=mysqli_query($db, 
      "UPDATE ".TABLE_ADMIN." SET type ='".$type."' WHERE account='".$account."'"
    );
    
    return $result ? NULL : mysqli_error($db);
  }
  
  // Get specific user (or all users) details
  function get_user_data($db, $username = '*'){
    if($username == '*'){
      $result = mysqli_query($db,
	  "SELECT * FROM ".TABLE_ADMIN.";"
      );
      
      $ret = array();
      
      while(($temp = mysqli_fetch_assoc($result)) != NULL){
	$ret[] = $temp;
      }
      
      return $ret;
      
    } else {
      $result = mysqli_query($db,
	  "SELECT * FROM ".TABLE_ADMIN." WHERE account='$username';"
      );
      if(mysqli_num_rows($result) <= 0)
	return NULL;
      else
	return mysqli_fetch_assoc($result);
    }
  }
  
  function update_user_passwd($db, $account, $password){
    $result=mysqli_query($db,"UPDATE ".TABLE_ADMIN." SET password='".sha1($password)."' WHERE account='".$account."';");
    return $result ? NULL : mysqli_error($db);
  }
  
  
// Table & relations
  
  // Dump Response Table
  function dump_table($db){
    $general = false;
    $result = mysqli_query($db,
	"SELECT r.rid, r.qualifier, r.response, k.kid, k.keyword FROM ".TABLE_KEYWORDS." AS k, ".TABLE_RESPONSES." AS r,".TABLE_RELATIONS." AS l WHERE k.kid = l.kid AND r.rid = l.rid ORDER BY rid ;"
    );
    
    if($result) {
      $temp=mysqli_fetch_assoc($result);
      
      if($temp != NULL)
	$prid = array(
	  'rid' => $temp['rid'],
	  'qualifier' => $temp['qualifier'],
	  'response' => $temp['response'],
	  'keywords' => array($temp['keyword'])
	);
	
	$ret[] = $prid;
	
      while(($temp = mysqli_fetch_assoc($result)) != NULL){
	if($prid['rid'] != $temp['rid']){
	  if($general == true) {
	    $ret[count($ret)-1]['keywords'][] = '*';
	    $general = false;
	  }
	$prid = array(
	    'rid' => $temp['rid'],
	    'qualifier' => $temp['qualifier'],
	    'response' => $temp['response'],
	    'keywords' => array($temp['keyword'])
	  );
	  $ret[] = $prid;
	}
	else if(trim($temp['keyword']) == '*')
	    $general = true;
	else
	  $ret[count($ret)-1]['keywords'][] = $temp['keyword'];
      }
      
      return $ret; 
    } else
      return NULL;
  }

  // Build relation between sentence and keywords.
  function create_relation($db, $sentence, $keywords) {
    foreach($keywords as $item){
	$result = mysqli_query($db,"SELECT kid FROM ".TABLE_KEYWORDS." WHERE keyword='".command_escape($item)."';");
	$record = mysqli_fetch_row($result);
	$kid[]=$record[0];
  }
	    
    $result = mysqli_query($db, 'SELECT rid FROM '.TABLE_RESPONSES.' WHERE response=\''.command_escape($sentence).'\';');
    $rid=mysqli_fetch_row($result);
    foreach($kid as $kids)
	mysqli_query($db,"INSERT INTO ".TABLE_RELATIONS."(rid, kid) VALUES($rid[0], $kids);");
      return $result ? NULL : mysqli_error($db);
  }

  function remove_relation($db, $sentence, $keyword) {
    $kid = getkid($db, $keyword);
    $rid = getrid($db, $sentence);
    
    if(($kid != NULL) && ($rid != NULL)) {
      $result = mysqli_query($db, "DELETE FROM ".TABLE_RELATIONS." WHERE rid=$rid AND kid=$kid ;");
      return $result ? NULL : mysqli_error($db);
    } else
      return mysqli_error($db);
  }
  
  function getkid($db, $keyword) {
    $result = mysqli_query($db, "SELECT kid FROM ".TABLE_KEYWORDS." WHERE keyword='$keyword';");
    
    if($result) {
      $temp = mysqli_fetch_row($result);
      return $temp[0];
    } else
      return NULL;
  }
  
  function getrid($db, $response) {
    $result = mysqli_query($db, "SELECT rid FROM ".TABLE_RESPONSES." WHERE response='$response';");
    
    if($result) {
      $temp = mysqli_fetch_row($result);
      return $temp[0];
    } else
      return NULL;
  }
  
// Setting Managemant

  // Dump settings 
  function dump_settings($db){
    $ret = array();
    
    $result = mysqli_query($db, 'SELECT * FROM '.TABLE_SETTINGS.';');
    if($result){
      while( $record = mysqli_fetch_assoc($result) )
		$ret[$record['setting']] = $record['value']; 
    } else
      $ret = NULL;
      
    return $ret;
  }
  
  // Modify settings
  function update_setting($db, $setting, $value){
    $result = mysqli_query($db,
      'SELECT setting FROM '.TABLE_SETTINGS." WHERE setting='$setting';"
    );
    
    if(mysqli_num_rows($result) <= 0){
      $result = mysqli_query($db, 
	'INSERT INTO '.TABLE_SETTINGS."(setting,value) VALUE('$setting','$value');"
      );
      
    } else {
      $result = mysqli_query($db,
	'UPDATE '.TABLE_SETTINGS." SET value='$value' WHERE setting='$setting';"
      );
    }
    
    return $result ? NULL : mysqli_error($db);
  }


  function command_escape($cmd){
    $ret = $cmd;
    $words = array("'",'"');
    
    foreach( $words as $item )
      $ret = str_replace($item, '\\'.$item, $ret);
      
    return $ret;
  }

?>