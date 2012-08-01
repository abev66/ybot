<?php 
/*
 *  ybot.php - a plurk bot use php-plurk-api
 *
 *  Copyright (C) 2012 Zheng-Yen Hong
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

require('db_port.php');
$db_port=db_init();

if(isset($_POST['account'])){
  $result = get_user_data( $db_port, $_POST['account'] );
  db_close($db_port);
  
  if( !$result )
    header( "Location: login.php?error=" );
  else if( sha1($_POST['password']) == $result['password'] ) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['_ybot_uid'] = $result['uid'];
    $_SESSION['_ybot_account'] = $result['account'];
    $_SESSION['_ybot_type'] = $result['type'];
    header('Location: responseedit.php');
  } else 
    header( "Location: login.php?error=" );
}
?>
