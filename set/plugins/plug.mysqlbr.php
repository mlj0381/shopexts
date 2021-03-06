<?php

class mysqlbr {
	var $label = 'ShopEx数据库备份恢复';

	
	var $backupdir;
	var $backupfileprefix;

	function mysqlbr(){
		$this->backupdir = VARDIR.'/backup';
		if(!file_exists($this->backupdir)){
			mkdir($this->backupdir);
			chmod($this->backupdir,0666);
		}
	}

	function run(){
		switch($_REQUEST['action']){

			case "backup":
				$this->backup();
			break;
			
			case "restore":
				$this->restore();
			break;
			
			case "delete":
				$this->delete();
			break;
	
		}

		$this->index();
	}

	function index() {
		$dbname = DB_NAME;
		echo "<h3>".$this->label."</h3>";
		echo <<<EOF
<script>

	function restore(filename){
		action_elm=document.getElementById('action');
		action_elm.value='restore';
		filename_elm=document.getElementById('filename'); 
		filename_elm.value=filename;
		frm_elm = document.getElementById('mysqlbr');
		frm_elm.submit();
	}

	function delfile(filename){		
		action_elm=document.getElementById('action');
		action_elm.value='delete';
		filename_elm=document.getElementById('filename'); 
		filename_elm.value=filename;
		frm_elm = document.getElementById('mysqlbr');
		frm_elm.submit();
	}
</script>
<form name='hello' id='hello' method=post>
<input type='hidden' name=module value=mysqlbr>
<input type='hidden' name=action value="backup">
备份全部的表<input type=radio name=type value=all checked>
仅备份网店的表<input type=radio name=type value=shoponly>
<input type='submit' value='备份数据库({$dbname})'>
</form>
<br>
EOF;
		$this->backupSelector();
	}

	function delete(){
		$identifer = $_POST['filename'];
		$aInfo = $this->getBackupFileInfo();
		if(count($aInfo))
		foreach($aInfo[$identifer]['filename'] as $filename){
			$file = "$this->backupdir/$filename";
			if(file_exists($file)) unlink($file);
		}

		return;
	}

	function backupSelector(){
		echo "<form name='mysqlbr' id='mysqlbr' method=post>";
		echo "<input type='hidden' name=module value=mysqlbr>";
		echo "<input type='hidden' id=action name=action value=restore>";
		echo "<input type='hidden' id=filename name=filename value=>";
		echo "<table class='ae-table'>";
		echo "<thead>";
		echo "<th>序号</th><th>文件名</th><th>大小(byte)</th><th>卷数</th><th>操作</th>";
		echo "</thead>";
		$aInfo = $this->getBackupFileInfo();
		$i = 1;
		if(count($aInfo))
		foreach($aInfo as $key=>$iterator){
			echo "<tr><td>".$i."</td><td>".$key."</td><td>".number_format($iterator['filesize'],0,',',',')."</td><td>".count($iterator['filename'])."</td><td><input type=button value='恢复' onclick=restore('".$key."');>&nbsp;&nbsp;&nbsp;<input type=button value='删除' onclick=delfile('".$key."');></td><tr>";
			$i++;
		}
		echo "</table>";
	}

	function getBackupFileInfo(){
		$retn = array();
		$aInfo = scandir($this->backupdir);
		array_shift($aInfo);
		array_shift($aInfo);	
		if(count($aInfo))
		foreach($aInfo as $filename){
			if(@preg_match("/^\d{14}_\d{1,4}\.sql$/",$filename)){
				$identifer = substr($filename,0,14);
				$retn[$identifer]['filename'][] = $filename;
				$retn[$identifer]['filesize'] += filesize("$this->backupdir/$filename");		
			}
		}

		return $retn;					
	}

	function backup(){
		$dumper = new Mysqldumper(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$dumper->setDroptables(true);
		$dumper->nodata = array();
		#
		if($_POST['type'] == 'shoponly'){
			$dumper->backuptype = 'shoponly';
		}
		#
		$fileid = 0;
		$this->backupfileprefix = date("YmdHis",time());
		msg("开始数据库备份...\n");		
		do{
			$backupfilename = $this->backupfileprefix."_".($fileid + 1).".sql";
			$bakfile = "$this->backupdir/$backupfilename";
			$finished = $dumper->multiDump($bakfile,$fileid,1024);
			$fileid++;
			msg("数据库备份文件 $backupfilename 已生成\n");	
		}while(!$finished);
		msg("<hr><font color=green>数据库备份完毕</font>\n");
	}

	function restore(){
		$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die("无法连接到数据库，错误原因是: " . mysql_error($link)); 
		mysql_select_db(DB_NAME,$link) or die("无法使用数据库".DB_NAME);
		if(mysql_get_server_info()>'5.0.1') mysql_query("SET sql_mode=''",$link);
		if(defined("DB_CHARSET"))	mysql_query("SET NAMES '".DB_CHARSET."'",$link);
		#获取sql文件
		$identifer = $_POST['filename'];
		$aInfo = $this->getBackupFileInfo();
		$aFileList = $aInfo[$identifer]['filename'];
		if(count($aFileList))
		foreach($aFileList as $filename){
			$bakfile = "$this->backupdir/$filename";
			if(!file_exists($bakfile)){			
				break;
			}
			$fp = fopen($bakfile, "r");
			$i = 0;
			while ($this->fgetline($fp,$buffer)!==false) {
				if (trim($buffer) != "" && substr($buffer, 0, 1) != "#") {
					$buffer = trim($buffer);
					if(substr(trim($buffer),-1)==";") $buffer = substr(trim($buffer),0,strlen($buffer)-1);
					//替换前缀定义
					$buffer = str_replace('{shopexdump_table_prefix}',DB_PREFIX,$buffer);
					if(defined("DB_CHARSET"))
						$buffer = str_replace('{shopexdump_create_specification}',' DEFAULT CHARACTER SET '.DB_CHARSET,$buffer);
					else
						$buffer = str_replace('{shopexdump_create_specification}','',$buffer);

					if(!mysql_query($buffer,$link)) echo mysql_error($link)."<br>";
					usleep(5);
					$i++;
				}
			}
			fclose($fp);
			msg("数据文件".$filename."恢复成功!\n");

		}
		#
		msg($identifer.'全部还原完毕');	
		mysql_close($link);
	}

	function fgetline($handle,&$line) {
		$buffer = fgets($handle, 4096);
		if (!$buffer)	{
			return false;
		}

		if(( 4095 > strlen($buffer)) || ( 4095 == strlen($buffer) && '\n' == $buffer{4094} ))	{
			$line = $buffer;
		} else {
			$line = $buffer;
			while( 4095 == strlen($buffer) && '\n' != $buffer{4094} ) {
				$buffer = fgets($handle,4096);
				$line.=$buffer;
			}
		}
	}
}

//mysqldump
class Mysqldumper {
	var $_host;
	var $_dbuser;
	var $_dbpassword;
	var $_dbname;
	var $_isDroptables;
	var $tableid;	//数据表ID
	var $startid;
	var $tablearr;
	var $nodata;
	var $backuptype;
	
	function Mysqldumper($host = "localhost", $dbuser = "", $dbpassword = "", $dbname = "") {
		$this->setHost($host);
		$this->setDBuser($dbuser);
		$this->setDBpassword($dbpassword);
		$this->setDBname($dbname);
		// Don't drop tables by default.
		$this->setDroptables(false);
		$this->startid = -1;
		$this->tableid = 0;
		$this->backuptype = 'all';
	}
	
	function setHost($host) {
		$this->_host = $host;
	}
	
	function getHost() {
		return $this->_host;
	}
	
	function setDBname($dbname) {
		$this->_dbname = $dbname;
	}
	
	function getDBname() {
		return $this->_dbname;
	}
	
	function getDBuser() {
		return $this->_dbuser;
	}
	
	function setDBpassword($dbpassword) {
		$this->_dbpassword = $dbpassword;
	}
	
	function getDBpassword() {
		return $this->_dbpassword;
	}
	
	function setDBuser($dbuser) {
		$this->_dbuser = $dbuser;
	}
	
	// If set to true, it will generate 'DROP TABLE IF EXISTS'-statements for each table.
	function setDroptables($state) {
		$this->_isDroptables = $state;
	}
	
	function isDroptables() {
		return $this->_isDroptables;
	}

	function _getBackupTable(){
		$result = mysql_query("SHOW TABLES");
		$tables = $this->result2Array(0, $result);
		#过滤掉
		if($this->backuptype == 'shoponly'){
			$tables = array_filter($tables,array($this,'isShopTable'));
		}

		return $tables;
	}

	function isShopTable($tablename){
		return strstr($tablename,DB_PREFIX);
	}


	function multiDump($bakfile,$fileid,$sizelimit) {
		// Set line feed
		$ret = true;
		$lf = "\r\n";
		$lencount = 0;
		$fw = @fopen($bakfile, "wb+");
		if(!$fw) exit("export file write failed");
		$resource = mysql_connect($this->getHost(), $this->getDBuser(), $this->getDBpassword(),true);
		mysql_select_db($this->getDbname(), $resource);
		if(defined("DB_CHARSET"))
				mysql_query("SET NAMES '".DB_CHARSET."'",$resource);
	
		if(!$this->tablearr){
			$this->tablearr = $this->_getBackupTable();
		}	
		// Set header
		fwrite($fw, "#". $lf);
		fwrite($fw,  "# SHOPEX SQL MultiVolumn Dump ID:".($fileid+1) . $lf);
		fwrite($fw,  "# Version ". $GLOBALS['SHOPEX_THIS_VERSION']. $lf);
		fwrite($fw,  "# ". $lf);
		fwrite($fw,  "# Host: " . $this->getHost() . $lf);
		fwrite($fw,  "# Server version: ". mysql_get_server_info() . $lf);
		fwrite($fw,  "# PHP Version: " . phpversion() . $lf);
		fwrite($fw,  "# Database : `" . $this->getDBname() . "`" . $lf);
		fwrite($fw,  "#");

		// Generate dumptext for the tables.
		$i=0;
		for($j=$this->tableid;$j<count($this->tablearr);$j++){
			$tblval = $this->tablearr[$j];
			$table_prefix = DB_PREFIX ? DB_PREFIX : '';
			if($this->isShopTable($tblval)){
				$written_tbname = '{shopexdump_table_prefix}'.substr($tblval,strlen($table_prefix));
			}else{
				$written_tbname = $tblval;
			}
			if($this->startid == -1)
			{
				fwrite($fw,  $lf . $lf . "# --------------------------------------------------------" . $lf . $lf);
				$lencount += strlen($lf . $lf . "# --------------------------------------------------------" . $lf . $lf);
				fwrite($fw,  "#". $lf . "# Table structure for table `$tblval`" . $lf);
				$lencount += strlen("#". $lf . "# Table structure for table `$tblval`" . $lf);
				fwrite($fw,  "#" . $lf . $lf);
				$lencount += strlen("#". $lf . "# Table structure for table `$tblval`" . $lf);
				// Generate DROP TABLE statement when client wants it to.
				mysql_query("ALTER TABLE `$tblval` comment ''");
				if($this->isDroptables()) {
					fwrite($fw,  "DROP TABLE IF EXISTS `$written_tbname`;" . $lf);
					$lencount += strlen("DROP TABLE IF EXISTS `$written_tbname`;" . $lf);
				}
				$result = mysql_query("SHOW CREATE TABLE `$tblval`");
				$createtable = $this->result2Array(1, $result);
				$tmp_value = str_replace("\n", '', $this->formatcreate($createtable[0]));
				$pos = strpos($tmp_value,$tblval);
				$tmp_value = substr($tmp_value,0,$pos).$written_tbname.substr($tmp_value,$pos+strlen($tblval));
				fwrite($fw,  $tmp_value. $lf.$lf);
				$lencount += strlen($tmp_value. $lf.$lf);
				$this->startid = 0;
			}
			if($lencount>$sizelimit*1000)
			{
				$this->tableid = $j;
				$this->startid = 0;
				$ret = false;
				break;
			}
			fwrite($fw,  "#". $lf . "# Dumping data for table `$tblval`". $lf . "#" . $lf);
			$lencount += strlen("#". $lf . "# Dumping data for table `$tblval`". $lf . "#" . $lf);
			$tbr_name = substr($tblval,strlen($table_prefix));
			if(in_array($tbr_name,$this->nodata))
				$result = mysql_query("SELECT * FROM `$tblval` where 1<>1");
			else
				$result = mysql_query("SELECT * FROM `$tblval`");
			if(!@mysql_data_seek($result,$this->startid))
			{
				$this->startid = -1;
				continue;
			}

			while ($row = mysql_fetch_object($result)) {
					$insertdump = $lf;
					$insertdump .= "INSERT INTO `$written_tbname` VALUES (";
					$arr = $this->object2Array($row);

					foreach($arr as $key => $value) {
						if(!is_null($value))
						{
							$value = $this->utftrim(mysql_escape_string($value));
							$insertdump .= "'$value',";
						}
						else
							$insertdump .= "NULL,";
					}
					$insertline = rtrim($insertdump,',') . ");";
					fwrite($fw,  $insertline);
					usleep(5);
					$lencount += strlen($insertline);
					$this->startid++;
					if($lencount>$sizelimit*1000)
					{
						$ret = false;
						$this->tableid = $j;
						break 2;
					}
			}
			$this->startid = -1;
			$i++;
//			if ($i== 5) break;
		}
		mysql_close($resource);
		fclose($fw);
		chmod($bakfile, 0666);
		return $ret;
	}


	// Private function object2Array.
	function object2Array($obj) {
		$array = null;
		if(is_object($obj)) {
			$array = array();
			foreach (get_object_vars($obj) as $key => $value) {
				if(is_object($value))
					$array[$key] = $this->object2Array($value);
				else
					$array[$key] = $value;
			}
		}
		return $array;
	}
	
	// Private function loadObjectList.
	function loadObjectList($key='', $resource) {
		$array = array();
		while ($row = mysql_fetch_object($resource)) {
			if ($key)
				$array[$row->$key] = $row;
			else
				$array[] = $row;
		}
		mysql_free_result($resource);
		return $array;
	}
	
	// Private function result2Array.
	function result2Array($numinarray = 0, $resource) {
		$array = array();
		while ($row = mysql_fetch_row($resource)) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result($resource);
		return $array;
	}

	function formatcreate($str)
	{
			return substr($str,0,strrpos($str,")")+1)." TYPE=MyISAM{shopexdump_create_specification};";
	}

	//截最后一个是否是半个UTF-8中文
	function utftrim($str)
	{
		$found = false;
		for($i=0;$i<4&&$i<strlen($str);$i++)
		{
			$ord = ord(substr($str,strlen($str)-$i-1,1));
			//UTF-8中文分{四/三/二字节码},第一位分别为11110xxx(>192),1110xxxx(>192),110xxxxx(>192);接下去的位数都是10xxxxxx(<192)
			//其他ASCII码都是0xxxxxxx
			if($ord> 192)
			{
				$found = true;
				break;
			}
			if ($i==0 && $ord < 128){
				break;
			}
		}
		
		if($found)
		{
			if($ord>240)
			{
				if($i==3) return $str;
				else return substr($str,0,strlen($str)-$i-1);
			}
			elseif($ord>224)
			{
				if($i>=2) return $str;
				else return substr($str,0,strlen($str)-$i-1);
			}
			else
			{
				if($i>=1) return $str;
				else return substr($str,0,strlen($str)-$i-1);
			}
		}
		else return $str;
	}
}



?>