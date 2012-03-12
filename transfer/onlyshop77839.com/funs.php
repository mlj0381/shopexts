<?php

//加载ShopEx的配置文件
include_once("../include/mall_config.php");

mysql_connect($dbHost, $dbUser, $dbPass);
mysql_select_db($dbName);
mysql_query("set names utf8");


function export_cat(){	
		//清空商品分类表
		$sql = "TRUNCATE sdb_mall_offer_pcat";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());		
		$data = array();		
		//修改prop_cat_id可以为null
		$sql = "ALTER TABLE `sdb_mall_offer_pcat` CHANGE `prop_cat_id` `prop_cat_id` MEDIUMINT( 6 ) UNSIGNED NULL ";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());		
		//迁移商品分类数据
		$sql = "insert into sdb_mall_offer_pcat (catid,offerid,pid,cat,catord,catiffb) select classid,1,parentid,classname,orders,1  from sw_class  ";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
		//统一处理catpath
		$sql = "select * from sdb_mall_offer_pcat order by catid";
		$rs = mysql_query($sql) or  err(__LINE__." ".mysql_error());			
		while ($row = mysql_fetch_array($rs)){
			$data['catpath'][$row['catid']] = ','.intString($row['catord'], 5).','.intString($row['catid'],6);
			if ($row['pid']!=0){	
				$data['catpath'][$row['catid']] = $data['catpath'][$row['pid']].$data['catpath'][$row['catid']];
			}			
			$sql = "update sdb_mall_offer_pcat set catpath='{$data['catpath'][$row['catid']]}' where catid='{$row['catid']}'";
			mysql_query($sql) or  err(__LINE__." ".mysql_error());		
		}		
		unset($data);
}
	
function export_prop_category(){
	//清空商品分类表
	$sql = "TRUNCATE sdb_mall_prop_category";
	mysql_query($sql) or  err(__LINE__." ".mysql_error());
	
	$sql = "insert into sdb_mall_prop_category (prop_cat_id,offerid,cat_name,ordnum) select cat_id,1,cat_name,1 from ecs_goods_type ";
	mysql_query($sql) or  err(__LINE__." ".mysql_error());
		
}
	
function export_brand(){
	//清空商品品牌表
	$sql = "TRUNCATE sdb_mall_brand";
	mysql_query($sql) or  err(__LINE__." ".mysql_error());
	
	$sql = "insert into sdb_mall_brand (brand_id,offerid,sbid,brand_name) select markid,1,0,markname from sw_trademark";
	mysql_query($sql) or  err(__LINE__." ".mysql_error());
}
	
function export_goods(){
		//清空商品表
		$sql = "TRUNCATE sdb_mall_goods";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());		
		//清空相关商品表
		$sql = "TRUNCATE sdb_mall_offer_linkgoods";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());		

		//修改onsale字段的属性,必须要这么做否则后台商品列表不显示
		$sql = "ALTER TABLE `sdb_mall_goods` CHANGE `onsale` `onsale` TINYINT( 1 ) NOT NULL DEFAULT '1'";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
	
		//导商品数据
		$sql = "Insert into sdb_mall_goods(gid,offerid,catid,goods,brand,bn,storage,priceintro,price,danwei,intro,memo,smallimgremote,bigimgremote,onsale,uptime,meta_keywords) select id,1,classid1,name,brand,ProNo,stock,price0,price1,unit,introduce,detail,picture,picture,1,unix_timestamp(joindate),ProKey from sw_product order by joindate DESC";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
		
		//生成品牌=>品牌id映射表
		$sql = "select brand_id,brand_name from sdb_mall_brand";
		$rs = mysql_query($sql) or  err(__LINE__." ".mysql_error());	
		while($row = mysql_fetch_array($rs)){
			$map[$row['brand_name']] = $row['brand_id'];
		}

		$sql = "select gid,brand,smallimgremote from sdb_mall_goods";
		$outrs = mysql_query($sql) or  err(__LINE__." ".mysql_error());	
		while($row = mysql_fetch_array($outrs)){
		
			$aRnt = explode("|||",$row['smallimgremote']);
			$small_pic = array_shift($aRnt);
			$big_pic = array_shift($aRnt);
			$multi_pic = implode("&&",$aRnt);

			$sql = "update sdb_mall_goods set smallimgremote='".$small_pic."',bigimgremote='".$big_pic."',multi_image='".$multi_pic."',brand_id='".$map[$row['brand']]."'  where gid='".$row['gid']."'";
			mysql_query($sql) or  err(__LINE__." ".mysql_error());	
		}

		//将onsale字段改回枚举类型
		$sql = "ALTER TABLE `sdb_mall_goods` CHANGE `onsale` `onsale` ENUM( '0', '1' )  NOT NULL DEFAULT '1'";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());	

}

function export_review(){
		//清空商品评论表
		$sql = "TRUNCATE sdb_mall_comment";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());	
}

function export_users() {
		//清空 用户表
		$sql = "TRUNCATE sdb_mall_member";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
		
		//清空 用户表积分历史表
		$sql = "TRUNCATE sdb_mall_point_history";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
		
		//处理用户
		$sql = "Insert into sdb_mall_member(userid,offerid,user,password,name,regtime,email,oicq,sex,birthday,province,city,ip,point,point_history,advance,pw_question,pw_answer,level) select userid,1,username,userpassword,realname,unix_timestamp(joindate),useremail,userim, usersex,userbirthday,province,city,regip,userpoint,userpoint,score,userquestion,useranswer,UserGRPid  from sw_user  order by joindate DESC";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());

}

function export_article(){		
		//清空文章列表
		$sql = "TRUNCATE sdb_mall_offer_ncon";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
}


//导友情链接
function export_friendlink(){
		//清空友情链接表
		$sql = "TRUNCATE sdb_mall_offer_link";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
}


function export_guestbook(){
		//清空友情链接表
		$sql = "TRUNCATE sdb_mall_offer_bbs";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
}


function export_orders(){
		//清空订单列表
		$sql = "TRUNCATE sdb_mall_orders";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
}

function export_order_items(){
		//清空订单商品列表
		$sql = "TRUNCATE sdb_mall_items";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());
}



	
function db_fields_local2utf($key, $fields, $table){
	$str_fields = $key.','.implode(',', $fields);

	$sql = "select {$str_fields} from {$table}";
	$rs = mysql_query($sql) or  err(__LINE__." ".mysql_error());
	
	$i=0;		
	while($row = mysql_fetch_array($rs)){
		unset($arr_fields);
		foreach ($fields as $v) {
			//$arr_fields[$v] = addslashes(stripslashes(local2utf($row[$v], 'zh')));
			$arr_fields[$v] = addslashes(stripslashes(iconv("GB2312","UTF-8",$row[$v])));
		}
		$db_string = compile_db_update_string($arr_fields);
		$sql = "UPDATE {$table} SET $db_string WHERE {$key}='".$row[$key]."'";
		mysql_query($sql) or  err(__LINE__." ".mysql_error());	
	}
}	

	
function compile_db_update_string($data) {
	$return_string = "";		
	foreach ($data as $k => $v){
		if($return_string=="")
		$return_string  = $k . "='".$v."'";
		else
		$return_string .= ",".$k . "='".$v."'";

	}		
	return $return_string;
}
	
function local2utf($string,$encoding){
	$lencodingtable = array();
	
	if(!trim($string)) return $string;

 	if(!isset($lencodingtable[$encoding]))
	{
	    $filename=realpath(dirname(__FILE__)."/encode/".$encoding.".txt"); 

		if(!file_exists($filename)||$filename=="")
		{

		   return $string;
		}
		$tmp=file($filename);
		$codetable=array();
		while(list($key,$value)=each($tmp))
			$codetable[hexdec(substr($value,0,6))]=hexdec(substr($value,7,6));
		$lencodingtable[$encoding] = $codetable;
	}
	else
	{
		$codetable = $lencodingtable[$encoding];
	}

	$ret="";
	while(strlen($string)>0) {
	    if( ord(substr($string,0,1)) > 127 ) {
			$t=substr($string,0,2);
			$string=substr($string,2);
			$ret .= u2utf8($codetable[hexdec(bin2hex($t))]);
	    }
	    else 
		{ 
			$t=substr($string,0,1);
			$string=substr($string,1);
			$ret .= u2utf8($t);
	    }
	}
	return $ret;
}

function u2utf8($c) {
	$str='';
	if ($c < 0x80) {
	    $str.=$c;
	    }
	else if ($c < 0x800) {
	    $str.=chr(0xC0 | $c>>6);
	    $str.=chr(0x80 | $c & 0x3F);
	    }
	else if ($c < 0x10000) {
	    $str.=chr(0xE0 | $c>>12);
	    $str.=chr(0x80 | $c>>6 & 0x3F);
		$str.=chr(0x80 | $c & 0x3F);
	}
	else if ($c < 0x200000) {
	    $str.=chr(0xF0 | $c>>18);
	    $str.=chr(0x80 | $c>>12 & 0x3F);
	    $str.=chr(0x80 | $c>>6 & 0x3F);
	    $str.=chr(0x80 | $c & 0x3F);
	}
	return $str;
}

function intString($intvalue,$len){
	$intstr=strval($intvalue);
	//echo strlen($intstr);
	for ($i=1;$i<=$len-strlen($intstr);$i++){
		$tmpstr .= "0";
	}
	return $tmpstr.$intstr;
}


function ubbtohtml(&$string) { 
	$string = preg_replace("/\[(\/?b)\]/i","<\\1>",$string);
	$string = preg_replace("/\[(\/?u)\]/i","<\\1>",$string);
	$string = preg_replace("/\[(\/?i)\]/i","<\\1>",$string);
	$string = preg_replace("/\[align=([a-zA-Z]+)\]/i","<p align=\\1>",$string);
  $string = preg_replace("/\[(\/align)\]/i","<\\1>",$string);
	$string = preg_replace("/\[url=(.+)\](.+)\[\/url\]/i","<a href=\\1>\\2</a>",$string);
	$string = preg_replace("/\[img\](.+)\[\/img\]/i","<img src=\\1 />",$string);
	$string = preg_replace("/\[color=(.+)\](.+)\[\/color\]/i","<font color=\\1>\\2</font>",$string);
	$string = preg_replace("/\[size=([0-9]{1})\](.+)\[\/size\]/i","<font size=\\1>\\2</font>",$string);
	$string = nl2br($string);
} 
	
function scroll($str,$state){
	if($state=="ok"){
		$str="<font color=\"green\">".$str."</font><br>";
	}
	if($state=="fail"){
		$str="<font color=\"red\" >".$str."</font><br>";
	}
	$str.=<<<MSG
	<script language="JavaScript">
		if(document.body.scrollHeight>document.body.clientHeight-30){
			 scroll(0,document.body.scrollHeight-document.body.clientHeight+30);
		}
	</script>
MSG;
	echo $str;
	flush();
}	

function err($imsg){
	$msg="<span style='color:red;font-size:12px'>".$imsg."</span><br>";
	die($msg);
}

?>