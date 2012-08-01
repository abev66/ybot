<?
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
?>
<!-- Navigation bar -->
  <div class='nav'>
    <ul>
      <li><a href="">Table</a>
	<ul>
	  <li><a href="responseedit.php">Responses</a></li>
	  <li><a href="keywordedit.php">Keywords</a></li>
<?php if($_SESSION['_ybot_type'] == 'b'): ?>
	  <li><a href="reloadtable.php">Reload Table</a></li>
<?php endif; ?>
      </ul>
      </li>
<?php if($_SESSION['_ybot_type'] == 'a'): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
<?php endif; ?>
<?php if( $_SESSION['_ybot_uid'] == 1 ): ?>
      <li><a href="accountmanage.php">Accounts</a></li>
<?php endif; ?>
      <li class="user_menu"><a href="">Hi, <?php echo $_SESSION['_ybot_account'];?></a>
	<ul>
	  <li><a href="chpwd.php">Mod. password</a></li>
	  <li><a href="logout.php">Logout</a></li>
	</ul>
      </li>
    </ul>
  </div>