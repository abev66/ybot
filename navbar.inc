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