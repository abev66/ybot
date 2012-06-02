<?php
// db_port.php - Database port for ybot using SQLite.
  
  // database info.
  define('DB_FILE','ybot.db');
  
  // Configures
  define('LOG_FILE', '');
  
  // table names
  define('TABLE_KEYWORDS','keywords');
  define('TABLE_RESPONSES','responses');
  define('TABLE_ADMIN', 'administers');
  define('TABLE_RELATIONS', 'relations');
  define('TABLE_SETTINGS', 'settings');
  
//--------------- functions ---------------

  // Database initialize
  function db_init($db_file = DB_FILE){
  
    $ret = sqlite_open($db_file);
    
    if($ret)
      sqlite_query($ret, "PRAGMA encoding = 'UTF-8';");
    
    return $ret;
  }
  
  function db_close($db){
    sqlite_close($db);
  }
  
// Keywords Managemant

  // Add 
  function add_keywords($db, $keywords){
    $ret = NULL;
    
    if(is_array($keywords)){
      $sql_command = 'INSERT INTO '.TABLE_KEYWORDS.'(keyword) VALUES(\'%s\');';
      
      foreach($keywords as $item){
	  if(!sqlite_query($db, sprintf($sql_command, command_escape($item))))
	    $ret += sqlite_error_string(sqlite_last_error($db)).'; ';
      }
    } else {
      $result = sqlite_query($db, 'INSERT INTO '.TABLE_KEYWORDS.'(keyword) VALUES(\''.command_escape($keywords).'\');');
      if(!$result)
	$ret = sqlite_error_string(sqlite_last_error($db));
    }
      
    return $ret;
  }

  // Remove
  function remove_keywords($db, $keywords){
    $ret = NULL;
    if(is_array($keywords)){
      $sql_command = 'DELETE FROM'.TABLE_KEYWORDS.' WHERE \'%s\';';
      foreach($keywords as $item){
	  if(!sqlite_query($db, sprintf($sql_command, command_escape($item)))){
	    $ret += sqlite_error_string(sqlite_last_error($db)).'; ';
	  }
      }
    } else {
      $result = sqlite_query($db, 'DELETE FROM '.TABLE_KEYWORDS.' WHERE \''.command_escape($keywords).'\';');
      
      if(!$result)
	$ret = sqlite_error_string(sqlite_last_error($db));
    }
    
    return $ret;
  }
	
  // Update
  function update_keyword($db, $from_keyword, $to_keyword){
    $result = sqlite_query($db,
      "SELECT * FROM ".TABLE_KEYWORDS." WHERE keyword='".command_escape($to_keyword)."';");
    if( sqlite_num_rows($result) <= 0 )
      $record = sqlite_query($db,"UPDATE ".TABLE_KEYWORDS." SET keyword='".command_escape($to_keyword)."' WHERE keyword='".command_escape($from_keyword)."';");
    else
      remove_keywords($db, $from_keyword);

    return $record ? NULL : sqlite_error_string(sqlite_last_error($db));
}

  // Query table using keyword, empty for return all keywords.
  function output_table_keyword($db, $keyword=''){
    $ret=array();
	if ($keyword == ''){
		$result = sqlite_query($db, "SELECT * FROM ".TABLE_KEYWORDS.";");
		while($record=sqlite_fetch_array($result))
			$ret[]=$record;
		}
	else{
		$result=sqlite_query($db, "SELECT b.keyword FROM ".TABLE_KEYWORDS." AS b WHERE b.keyword LIKE '%".command_escape($keyword)."%' ;");
		
		while ($record=sqlite_fetch_array($result))
			$ret[]=$record;
		}
    return $result ? $ret : sqlite_error_string(sqlite_last_error($db));
  }
  
  
// Sentenses Managemant

  // Add
  function add_sentence($db, $qualifier, $sentence){
    $result = sqlite_query($db, 
	"INSERT ".TABLE_RESPONSES."(qualifier, response) VALUES('".command_escape($qualifier)."', '".command_escape($sentence)."');"
    );
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }
  
  // Remove
  function remove_sentence($db, $sentence){
    $result = sqlite_query($db,
      command_escape(
	"DELETE FROM ".TABLE_RESPONSES." WHERE response='".command_escape($sentence)."';"
      )
    );
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }
  
  // Update
  function update_sentence($db, $from_qualifier, $from_sentence, $to_qualifier, $to_sentence){
    $result = sqlite_query($db,
	"SELECT * FROM ".TABLE_RESPONSES." WHERE qualifier='".command_escape($to_qualifier)."' AND response='".command_escape($to_sentence)."';"
      );
    
    if( sqlite_num_rows($result) <= 0 ) {
      $result = sqlite_query($db,
	"UPDATE ".TABLE_RESPONSES." SET qualifier='".command_escape($to_qualifier)."', response='".command_escape($to_sentence)."' WHERE qualifier='".command_escape($from_qualifier)."' AND response='".command_escape($from_sentence)."';"
      );
    } else
      remove_sentence($db, $from_qualifier, $from_sentence);
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }

  // Query table using response, empty for return all responses.
  function output_table_response($db, $reply=''){
    $ret=array();
	if ($reply == ''){
		$result = sqlite_query($db, "SELECT * FROM ".TABLE_RESPONSES.";");
		while($record=sqlite_fetch_array($result))
			$ret[]=$record;
		}
	else{
		$result=sqlite_query($db,"SELECT a.qualifier,a.response FROM ".TABLE_RESPONSES." as a WHERE a.response LIKE '%".command_escape($reply)."%' ;");
		while ($record=sqlite_fetch_array($result))
			$ret[]=$record;
		}
    return $result ? $ret : sqlite_error_string(sqlite_last_error($db));
  }

  
// Administer Managemant

  // Add
  function add_user($db, $username, $password, $type){
    $result = sqlite_query($db,
	"INSERT ".TABLE_ADMIN."(account, password, type) VALUES('$username', '".sha1($password)."', '$type');"
    );
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }

  // Remove
  function remove_user($db, $username){
    $userinfo = get_user_data($db, $username);
    if($userinfo['uid']==1){
      return false;
    } else {
      $result = sqlite_query($db,
	  "DELETE FROM ".TABLE_ADMIN." WHERE account='$username';"
      );
    }
  }
  
  // Modify user type
  function update_user_type($db, $account ,$type){
    $result=sqlite_query($db, 
      "UPDATE ".TABLE_ADMIN." SET type ='".$type."' WHERE account='".$account."'"
    );
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }
  
  // Get specific user (or all users) details
  function get_user_data($db, $username = '*'){
    if($username == '*'){
      $result = sqlite_query($db,
	  "SELECT * FROM ".TABLE_ADMIN.";"
      );
      
      $ret = array();
      
      while(($temp = sqlite_fetch_array($result)) != NULL){
	$ret[] = $temp;
      }
      
      return $ret;
      
    } else {
      $result = sqlite_query($db,
	  "SELECT * FROM ".TABLE_ADMIN." WHERE account='$username';"
      );
      if(sqlite_num_rows($result) <= 0)
	return NULL;
      else
	return sqlite_fetch_array($result);
    }
  }
  
  function update_user_passwd($db, $account, $password){
    $result=sqlite_query($db,"UPDATE ".TABLE_ADMIN." SET password='".sha1($password)."' WHERE account='".$account."';");
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }
  
  
// Table & relations
  
  // Dump Response Table
  function dump_table($db){
    $result = sqlite_query($db,
	"SELECT r.rid, r.qualifier, r.response, k.kid, k.keyword FROM ".TABLE_KEYWORDS." AS k, ".TABLE_RESPONSES." AS r,".TABLE_RELATIONS." AS l WHERE k.kid = l.kid AND r.rid = l.rid ORDER BY rid ;"
    );
    
    if($result) {
      $temp=sqlite_fetch_array($result);
      
      if($temp != NULL)
	$prid = array(
	  'rid' => $temp['rid'],
	  'qualifier' => $temp['qualifier'],
	  'response' => $temp['response'],
	  'keywords' => array($temp['keyword'])
	);
	
	$ret[] = $prid;
	
      while(($temp = sqlite_fetch_array($result)) != NULL){
	if($prid['rid'] != $temp['rid']){
	  $prid = array(
	    'rid' => $temp['rid'],
	    'qualifier' => $temp['qualifier'],
	    'response' => $temp['response'],
	    'keywords' => array($temp['keyword'])
	  );
	  $ret[] = $prid;
	}
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
	$result = sqlite_query($db,"SELECT kid FROM ".TABLE_KEYWORDS." WHERE keyword='".command_escape($item)."';");
	$record = sqlite_fetch_array($result);
	$kid[]=$record[0];
  }
	    
    $result = sqlite_query($db, 'SELECT rid FROM '.TABLE_RESPONSES.' WHERE response=\''.command_escape($sentence).'\';');
    $rid=sqlite_fetch_array($result);
    foreach($kid as $kids)
	sqlite_query($db,"INSERT INTO ".TABLE_RELATIONS."(rid, kid) VALUES($rid[0], $kids);");
      return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }

  
// Setting Managemant

  // Dump settings 
  function dump_settings($db){
    $ret = array();
    
    $result = sqlite_query($db, 'SELECT * FROM '.TABLE_SETTINGS.';');
    if($result){
      while( $record = sqlite_fetch_array($result) )
		$ret[$record['setting']] = $record['value']; 
    } else
      $ret = NULL;
      
    return $ret;
  }
  
  // Modify settings
  function update_setting($db, $setting, $value){
    $result = sqlite_query($db,
      'SELECT setting FROM '.TABLE_SETTINGS." WHERE setting='$setting';"
    );
    
    if(sqlite_num_rows($result) <= 0){
      $result = sqlite_query($db,
	'INSERT INTO '.TABLE_SETTINGS."(setting,value) VALUE('$setting','$value');"
      );
      
    } else {
      $result = sqlite_query($db,
	'UPDATE '.TABLE_SETTINGS." SET value='$value' WHERE setting='$setting';"
      );
    }
    
    return $result ? NULL : sqlite_error_string(sqlite_last_error($db));
  }


  function command_escape($cmd){
    $ret = $cmd;
    $words = array("'",'"');
    
    foreach( $words as $item )
      $ret = str_replace($item, ''.$item, $ret);
      
    return $ret;
  }

?>