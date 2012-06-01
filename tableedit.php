<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php //session check
session_start();
if(!isset($_SESSION['uid'])){
	header("location: index.php");
	}
else{
	require('db_port.php');
	$dblink=db_init();
	$session_uid=$_SESSION["uid"];
	$session_name=$_SESSION["name"];
	$session_type=$_SESSION["type"];
	}
?>
<html>
<head><title>table edit</title><link rel="stylesheet" href="style.css" type="text/css" /></head>
<body>
	<div class='status'>
		<?php echo "HI! ".$session_name.", <a href='passwd.php'>change password<a>, <a href='logout.php'>logout</a>"?>
	</div>
	<div class='banner' >
	<img src="http://i.imgur.com/AMZRm.png" width="500" height="100" alt="詞彙編輯" />
	</div>
	<div class='link'>
		<a href='tableedit.php'>table edit</a>
		<?php 
		if($session_type=='a'){
			echo "<a href='botcontrol.php'>robot control</a>";
			}
		if($session_uid==1){
			echo "<a href='accountmanage.php'>account manage</a>";
			}
		?>
	</div>
	<div class='content' >
	查詢關鍵字：<form name='keywordq' action='' method='POST'><input type='text' name='keykeyword'><input type='submit' value='查詢'></form><br />
	<?php
		if(isset($_POST['keykeyword'])){
		$result=output_table_keyword($dblink,$_POST['keykeyword']);
		echo "<table border='1'>";
		foreach($result as $i){
			echo "<tr><td>".$i['keyword']."</td>";
			echo "<td><form action='' method='POST'><input type='hidden' name='deletek' value='".$i['keyword']."'><input type='submit' value='刪除'></form></td>";
			echo "<td><form action='' method='POST'><input type='hidden' name='updatek' value='".$i['keyword']."'><input type='submit' value='修改'></form></td></tr>";
			}
		echo "</table>";
			}
		if (isset($_POST['updatek']))
			echo "<form action='' method='POST'><input type='hidden' name='updatekc' value='".$_POST['updatek']."'><input type='text' name='newword' value='".$_POST['updatek']."'><input type='submit' value='修改'></form>";
		if (isset($_POST['updatekc'])){
			if(!update_keyword($dblink, $_POST['updatekc'], $_POST['newword']))
				echo "update sucessed!!<br />";
				}
		if (isset($_POST['deletek'])){
			if(!remove_keywords($dblink,$_POST['deletek']))
				echo "remove sucessed!!<br />";
			}
	?>
	查詢回應句：<form name='replyq' action='' method='POST'><input type='text' name='keyreply'><input type='submit' value='查詢'></form>
	<?php
		if (isset($_POST['keyreply'])){
			$result=dump_table($dblink);
			if (!empty($_POST['keyreply'])){
				$tmp=array();
				foreach($result as $i)
					if (strpos($i['response'], trim($_POST['keyreply']))){
						$tmp[]=$i;
					}
				$result=$tmp;
				}
			echo "<table border='1'>";
			foreach($result as $i){
				echo "<tr><td>".$i['qualifier']."</td><td>".$i['response']."</td>";
				echo "<td><form action='' method='POST'><input type='hidden' name='deleter' value='".$i['response']."'><input type='submit' value='刪除'></form></td>";
				echo "<td><form action='' method='POST'><input type='hidden' name='updater' value='".$i['response']."'><input type='hidden' name='updaterq' value='".$i['qualifier']."'>";
				echo "<input type='submit' value='修改'></form></td><td><form action='' method='POST'><input type='hidden' name='resaddkey' value='".$i['response']."'><input type='submit' value='新增關鍵字'></form></td>";
				foreach($i['keywords'] as $va)
					echo "<td><a href='tableedit.php?responsea=".$i['response']."&keyword=".$va."'>".$va."</a></td>";
				}
			echo "</tr></table>";
			}
		if (isset($_GET['responsea'])){
			$key=array($_GET['keyword']);
			if(!delete_relation($dblink, $_GET['responsea'],$key))
				echo "relation deleted";
			}
		if (isset($_POST['resaddkey'])){
			echo "<form action='' method='POST'><input type='hidden' name='resaddkeyres' value='".$_POST['resaddkey']."'>要新增的關鍵字：";
			echo "<input type='text' name='resaddkeykey'><input type='submit' value='新增'>請用 | 來區別多個關鍵字</form>";
			}
		if (isset($_POST['resaddkeyres'])){
			$keyword=$_POST['resaddkeykey'];
			$keyword=trim($keyword);
			$keyword=explode('|',$keyword);
			add_keywords($dblink,$keyword);
			create_relation($dblink,$_POST['resaddkeyres'],$keyword);			
			}		
		if (isset($_POST['updater'])){
			echo "<form action='' method='POST'><input type='hidden' name='oldresponse' value='".$_POST['updater']."'><input type='hidden' name='oldqualifier' value='".$_POST['updaterq']."'>";
			echo "修改：<select name='newqualifier'><option value='says' selected='selected'>";
			echo "說</option><option value='likes' >喜歡</option><option value='shares' >分享</option>";
			echo "<option value='gives' >給</option><option value='hates' >討厭</option>";
			echo "<option value='wants' >想要</option><option value='has' >已經</option><option value='will' >打算</option>";
			echo "<option value='asks' >問</option><option value='wishs' >期待</option>";
			echo "<option value='was' >曾經</option><option value='feels' >覺得</option>";
			echo "<option value='thinks' >想</option><option value='is' >正在</option>";
			echo "<option value='hopes' >希望</option><option value='needs' >需要</option>option value='wonders' >好奇</option></select>";
			echo "<input type='text' name='newresponse' value='".$_POST['updater']."'><input type='submit' value='修改'><br /><br />";
			}
		if (isset($_POST['newresponse'])){
			if (!update_sentense($dblink, $_POST['oldqualifier'], $_POST['oldresponse'], $_POST['newqualifier'], $_POST['newresponse']))
				echo "update sucessed!!<br />";
			}
		if (isset($_POST['deleter'])){
			if(!remove_sentense($dblink,$_POST['deleter']))
				echo "remove sucessed!!<br />";
			}
	?>
	<br /><br />新增回應句/關鍵字
	<form name='tableadd' action='' method='POST'>
		關鍵字：<input type='text' name='keyword'>請用 | 來區別多個關鍵字<br />
		要回應的話：
					<select name="qualifier">
					<option value="says" selected="selected">說</option><option value="likes" >喜歡</option>
					<option value="shares" >分享</option>
					<option value="gives" >給</option>
					<option value="hates" >討厭</option>
					<option value="wants" >想要</option>
					<option value="has" >已經</option>
					<option value="will" >打算</option>
					<option value="asks" >問</option>
					<option value="wishs" >期待</option>
					<option value="was" >曾經</option>
					<option value="feels" >覺得</option>
					<option value="thinks" >想</option>
					<option value="is" >正在</option>
					<option value="hopes" >希望</option>
					<option value="needs" >需要</option>
					<option value="wonders" >好奇</option></select>	
					<input type='text' name='reply'><br /><input type='submit' name='tableadd' value='新增!!'>
	</form>
	<?php
	if (isset($_POST['tableadd'])){
		$keyword=$_POST['keyword'];
		$qualifier=$_POST['qualifier'];
		$reply=$_POST['reply'];
		$keyword=trim($keyword);
		$keyword=explode('|',$keyword);
		add_keywords($dblink,$keyword);
		add_sentense($dblink,$qualifier,$reply);
		create_relation($dblink,$reply,$keyword);
		}
	db_close($dblink);
	?>
	</div>
</body>
</html>