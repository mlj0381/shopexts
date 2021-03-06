<?php
if(!function_exists('file_put_contents')){
    define('FILE_APPEND', 1);
    function file_put_contents($n, $d, $flag = false) {
        $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'wb';
        $f = @fopen($n, $mode);
        if ($f === false) {
            return 0;
        } else {
            if (is_array($d)) $d = implode($d);
            flock($f, LOCK_EX);
            $bytes_written = fwrite($f, $d);
            flock($f, LOCK_UN);
            fclose($f);
            return $bytes_written;
        }
    }
}

if(!function_exists('str_ireplace')) {
    function str_ireplace($search, $replacement, $string){
        $delimiters = array(1,2,3,4,5,6,7,8,14,15,16,17,18,19,20,21,22,23,24,25,
            26,27,28,29,30,31,33,247,215,191,190,189,188,187,186,
            185,184,183,182,180,177,176,175,174,173,172,171,169,
            168,167,166,165,164,163,162,161,157,155,153,152,151,
            150,149,148,147,146,145,144,143,141,139,137,136,135,
            134,133,132,130,129,128,127,126,125,124,123,96,95,94,
            63,62,61,60,59,58,47,46,45,44,38,37,36,35,34);
        foreach ($delimiters as $d) {
            if (strpos($string, chr($d))===false){
                $delimiter = chr($d);
                break;
            }
        }
        if (!empty($delimiter)) {
            return preg_replace($delimiter.quotemeta($search).$delimiter.'i', $replacement, $string);
        }
        else {
            trigger_error('Homemade str_ireplace could not find a proper delimiter.', E_USER_ERROR);
        }
    }
}

function timezone_list(){
    return array(
        '-12'=>'(标准时-12) 日界线西',
        '-11'=>'(标准时-11) 中途岛、萨摩亚群岛',
        '-10'=>'(标准时-10) 夏威夷',
        '-9'=>'(标准时-9) 阿拉斯加',
        '-8'=>'(标准时-8) 太平洋时间(美国和加拿大)',
        '-7'=>'(标准时-7) 山地时间(美国和加拿大)',
        '-6'=>'(标准时-6) 中部时间(美国和加拿大)、墨西哥城',
        '-5'=>'(标准时-5) 东部时间(美国和加拿大)、波哥大',
        '-4'=>'(标准时-4) 大西洋时间(加拿大)、加拉加斯',
        '-3'=>'(标准时-3) 巴西、布宜诺斯艾利斯、乔治敦',
        '-2'=>'(标准时-2) 中大西洋',
        '-1'=>'(标准时-1) 亚速尔群岛、佛得角群岛',
        '0'=>'(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡',
        '1'=>'(标准时+1) 中欧时间、安哥拉、利比亚',
        '2'=>'(标准时+2) 东欧时间、开罗，雅典',
        '3'=>'(标准时+3) 巴格达、科威特、莫斯科',
        '4'=>'(标准时+4) 阿布扎比、马斯喀特、巴库',
        '5'=>'(标准时+5) 叶卡捷琳堡、伊斯兰堡、卡拉奇',
        '6'=>'(标准时+6) 阿拉木图、 达卡、新亚伯利亚',
        '7'=>'(标准时+7) 曼谷、河内、雅加达',
        '8'=>'(北京时间) 北京、重庆、香港、新加坡',
        '9'=>'(标准时+9) 东京、汉城、大阪、雅库茨克',
        '10'=>'(标准时+10) 悉尼、关岛',
        '11'=>'(标准时+11) 马加丹、索罗门群岛',
        '12'=>'(标准时+12) 奥克兰、惠灵顿、堪察加半岛',
        );
}

if(!function_exists('json_encode')){
    define('EMU_JSON',true);
    function json_encode($value){
        return jsValue($value);
    }
}

if(!function_exists('json_decode')){
    function json_decode($json,$assoc){
        include_once(dirname(__FILE__).'/lib/json.php');
        $o = new Services_JSON();
        return $o->decode($json,$assoc);
    }
}

if (!function_exists('ftp_chmod')){
    function ftp_chmod($ftp_stream, $mode, $filename){
        return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
    }
}

if(function_exists("ftp_connect")){
    //To connect the FTP Server
    function ftpConnect(){
        global $_FTP_CONNECTION;
        //Try to connect
        if(is_resource($_FTP_CONNECTION))return $_FTP_CONNECTION;
        else{
            $_FTP_CONNECTION = @ftp_connect(FTP_SERVER,FTP_PORT);
            if(!is_resource($_FTP_CONNECTION)) die('Error: FTP connect failed');
        }
        //Try to login
        if(!@ftp_login($_FTP_CONNECTION, FTP_USER, FTP_PASS)){
            die("Couldn't connect as ".FTP_USER."\n");
        }
        else return $_FTP_CONNECTION;
    }

    function mkdir_ftp($dir,$mode){
        global $_FTP_CONNECTION;
        //Try to connect
        if(defined("FTP_ROOT")){
            $script_path = substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],'/')+1);
            dealFTPDir($script_path);
            $dir = FTP_ROOT.'/'.$script_path.$dir;
            $dir = remove_dots($dir);
            if(!is_resource($_FTP_CONNECTION)) $_FTP_CONNECTION = ftpConnect();
            ftp_chdir($_FTP_CONNECTION,substr($dir,0,strrpos($dir,'/')));
            $ret = ftp_mkdir($_FTP_CONNECTION,$dir);
            ftp_chmod($_FTP_CONNECTION,$mode,$dir);
            return $ret;
        }
        else return mkdir($dir,$mode);
    }

    function close_ftp(){
        if(is_resource($_FTP_CONNECTION)) ftp_close($_FTP_CONNECTION);
    }

}else{
    include_once(dirname(__FILE__)."/include/phpFtp.php");
    $FTP_CONNECT = 0;
    $phpFtp = '';
    function ftpConnect(){
        global $phpFtp,$FTP_CONNECT;
        $phpFtp = new phpFtp();
        if($FTP_CONNECT) return ;
        else{
            $phpFtp->connect(FTP_SERVER,FTP_PORT);
            if(!$phpFtp->isConnected()) die('Error: FTP connect failed');
        }
        if(!$phpFtp->login(FTP_USER, FTP_PASS)){
            die("Couldn't connect as ".FTP_USER."\n");
        }
        $FTP_CONNECT = 1;
        return $FTP_CONNECT;
    }

    function mkdir_ftp($dir,$mode){
        global $phpFtp,$FTP_CONNECT;
        if(defined("FTP_ROOT")){
            $script_path = substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],'/')+1);
            dealFTPDir($script_path);
            $dir = FTP_ROOT.'/'.$script_path.$dir;
            $dir = remove_dots($dir);
            if(!$FTP_CONNECT) ftpConnect();
            $phpFtp->chdir(substr($dir,0,strrpos($dir,'/')));
            $ret = $phpFtp->mkdir($dir);
            $mode = DecOct($mode);
            $phpFtp->chmod($mode,$dir);
            return $ret;
        }
        else return mkdir($dir,$mode);
    }

    function close_ftp(){
        global $phpFtp,$FTP_CONNECT;
        if($FTP_CONNECT ==1){
            $phpFtp->disconnect();
            $FTP_CONNECT = 0;
        }
    }

}

if (!function_exists('http_build_query')) {
    function http_build_query($formdata, $numeric_prefix = null)
    {
        // If $formdata is an object, convert it to an array
        if (is_object($formdata)) {
            $formdata = get_object_vars($formdata);
        }

        // Check we have an array to work with
        if (!is_array($formdata)) {
            user_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.',
                E_USER_WARNING);
            return false;
        }

        // If the array is empty, return null
        if (empty($formdata)) {
            return;
        }

        // Argument seperator
        $separator = ini_get('arg_separator.output');

        // Start building the query
        $tmp = array ();
        foreach ($formdata as $key => $val) {
            if (is_integer($key) && $numeric_prefix != null) {
                $key = $numeric_prefix . $key;
            }

            if (is_scalar($val)) {
                array_push($tmp, urlencode($key).'='.urlencode($val));
                continue;
            }

            // If the value is an array, recursively parse it
            if (is_array($val)) {
                array_push($tmp, __http_build_query($val, urlencode($key)));
                continue;
            }
        }

        return implode($separator, $tmp);
    }

    // Helper function
    function __http_build_query ($array, $name)
    {
        $tmp = array ();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_push($tmp, __http_build_query($value, sprintf('%s[%s]', $name, $key)));
            } elseif (is_scalar($value)) {
                array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
            } elseif (is_object($value)) {
                array_push($tmp, __http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
            }
        }

        // Argument seperator
        $separator = ini_get('arg_separator.output');

        return implode($separator, $tmp);
    }
}

//wildsetup
function txtDecode($str) {
    $ret = array();
    $str = str_replace("\r","",$str);
    $linearr = explode("\n",$str);
    while(list($k,$v)=each($linearr)) {
        if(trim($v)!="") {
            $arr = explode(":",trim($v),2);
            if(count($arr)==2)
                if($arr[0]!=""&&trim($arr[1])!="") $ret[$arr[0]] = trim($arr[1]);
        }
    }
    return $ret;
}

function txtEncode($arr) {
    $str = "";
    if(is_array($arr)) {
        while(list($k,$v)=each($arr)) {
            $str .= $k.":".trim($v)."\r\n";
        }
        return trim($str);
    }
    else return "";
}

function fgetline($handle){
    $buffer = fgets($handle, 4096);
    if (!$buffer){
        return false;
    }
    if(( 4095 > strlen($buffer)) || ( 4095 == strlen($buffer) && "\n" == $buffer{4094} )){
        $line = $buffer;
    }else{
        $line = $buffer;
        while( 4095 == strlen($buffer) && "\n" != $buffer{4094} ){
            $buffer = fgets($handle,4096);
            $line.=$buffer;
        }
    }
    return $line;
}

function ext_name($file){
    return substr($file,strrpos($file,'.'));
}

//ext_valid($filename,$type)  检查上传源文件名是否合法
function ext_valid($filename,$type)
{
    $extarr = array();
    $filename = strtolower($filename);
    $extarr[0]= array(".gif",".jpg",".jpeg",".png");
    if(!isset($extarr[$type])) return false;
    if($ext = strrchr($filename,"."))
    {
        if(in_array($ext,$extarr[$type]))
        {
            return true;
        }else return false;
    }
    else return false;
}

function dec2b36($int)
{
    $b36 = array(0=>"0",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"A",11=>"B",12=>"C",13=>"D",14=>"E",15=>"F",16=>"G",17=>"H",18=>"I",19=>"J",20=>"K",21=>"L",22=>"M",23=>"N",24=>"O",25=>"P",26=>"Q",27=>"R",28=>"S",29=>"T",30=>"U",31=>"V",32=>"W",33=>"X",34=>"Y",35=>"Z");
    $retstr = "";
    if($int>0)
    {
        while($int>0)
        {
            $retstr = $b36[($int % 36)].$retstr;
            $int = floor($int/36);
        }
    }
    else
    {
        $retstr = "0";
    }

    return $retstr;
}

function local_emu(){
}

function echox() {
    if (defined('SHOP_DEVELOPER') && SHOP_DEVELOPER) {
        echo '<pre>',var_export($v,1),'</ pre>';
    }
}

function echoxx($con)
{
    if(SHOP_DEVELOPER){
        error_log(date('Y-m-d H:i:s').' '.__FILE__.' '.$_SERVER['PHP_SELF']."\n".$con."\n", 3, 'error1.log');
    }
}

function base_url($with_file=false){

    if(defined('BASE_URL') && strlen(BASE_URL)>1){
        return BASE_URL;
    }

    if(isset($_SERVER['HTTPS']) && strpos('on',$_SERVER['HTTPS'])){
        $baseurl = 'https://'.$_SERVER['SERVER_NAME'];
        if($_SERVER['SERVER_PORT']!=443)$baseurl.=':'.$_SERVER['SERVER_PORT'];
    }else{
        $baseurl = 'http://'.$_SERVER['SERVER_NAME'];
        if($_SERVER['SERVER_PORT']!=80)$baseurl.=':'.$_SERVER['SERVER_PORT'];
    }
    if($with_file)
        $baseurl.=$_SERVER['SCRIPT_NAME'];
    else{
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        $baseurl.=($baseDir == '\\' ? '' : $baseDir).'/';
    }
    return $baseurl;
}

function dateFormat($time){
    return date($GLOBALS['system']->getconf('admin.dateFormat','Y-m-d'),$time);
}
function timeFormat($time){
    return date($GLOBALS['system']->getconf('admin.timeFormat','Y-m-d H:i:s'),$time);
}
function array_merge_recursive2($paArray1, $paArray2)
{
    if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
    foreach ($paArray2 AS $sKey2 => $sValue2)
    {
        $paArray1[$sKey2] = array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
    }
    return $paArray1;
}

function array_merge2($paArray1, $paArray2){
    foreach ($paArray1 AS $sKey1 => $sValue1){
        $newArray[$sKey1] = $sValue1;
    }
    foreach ($paArray2 AS $sKey2 => $sValue2){
        $newArray[$sKey2] = $sValue2;
    }
    return $newArray;
}

function array_item($arr, $item) {
    if (is_array($arr)){
        if (empty($arr)||!is_string($item)) {
            return false;
        }
        $res = array();
        foreach($arr as $k=>$v){
            if ($v[$item]) {
                array_push($res, $v[$item]);
            }
        }
        return $res;
    }else{
        return false;
    }

    $container = array();
    if (is_array($arr) && !empty($arr)) {
    }
}

function getini($assoc_array){
    $content = '';
    $sections = '';

    foreach ($assoc_array as $key => $item)
    {
        if (is_array($item))
        {
            $sections .= "\r\n[{$key}]\r\n";
            foreach ($item as $key2 => $item2)
            {
                if (is_numeric($item2) || is_bool($item2))
                    $sections .= "{$key2} = {$item2}\r\n";
                else
                    $sections .= "{$key2} = \"{$item2}\"\r\n";
            }
        }
        else
        {
            if(is_numeric($item) || is_bool($item))
                $content .= "{$key} = {$item}\r\n";
            else
                $content .= "{$key} = \"{$item}\"\r\n";
        }
    }
    return $content.$sections;
}

function steprange($start,$end,$step){
    if($end-$start){
        if($step<2)$step=2;
        $s = ($end - $start)/$step;
        $r=array(floor($start)-1);

        for($i=1;$i<$step;$i++){
            $n = $start+$i*$s;
            $f=pow(10,floor(log10($n-$r[$i-1])));
            $r[$i] = round($n/$f)*$f;
            $q[$i] = array($r[$i-1]+1,$r[$i]);
        }
        $q[$i] = array($r[$step-1]+1,ceil($end));
        return $q;
    }else{
        if(!$end)$end = $start;
        return array(array($start,$end));
    }
}

function file_rename($source,$dest){
    if(PHP_OS=='WINNT'){
        @copy($source,$dest);
        @unlink($source);
        if(file_exists($dest)) return true;
        else return false;
    }else{
        return rename($source,$dest);
    }
}

function secret_name($file){
    return str_replace(realpath(CORE_DIR),'%CORE_DIR%',$file);
}


function find($dir,$ext=null,$path=null){
    $return=array();
    $sub = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if($file{0}!='.'){
                    if(is_dir($dir.'/'.$file)){
                        $sub = array_merge($sub,find($dir.'/'.$file,$ext,$path.'/'.$file));
                    }elseif(!$ext || (($p = strrpos($file,'.')) && substr($file,$p+1)==$ext)){
                        $return[] = $path.'/'.$file;
                    }
                }
            }
            closedir($dh);
        }
    }
    sort($return);
    return array_merge($return,$sub);
}

function buildTag($params,$tag,$finish=true){
    foreach($params as $k=>$v){
        if(!is_null($v) && !is_array($v)){
            if($k=='value'){
                $v=htmlspecialchars($v);
            }
            $ret[]=$k.'="'.$v.'"';
        }
    }
    return '<'.$tag.' '.implode(' ',$ret).($finish?' /':'').'>';
}



function formatBytes($val, $digits = 3, $mode = "SI", $bB = "B"){ //$mode == "SI"|"IEC", $bB == "b"|"B"
    $si = array("", "K", "M", "G", "T", "P", "E", "Z", "Y");
    $iec = array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
    switch(strtoupper($mode)) {
    case "SI" : $factor = 1000; $symbols = $si; break;
    case "IEC" : $factor = 1024; $symbols = $iec; break;
    default : $factor = 1000; $symbols = $si; break;
    }
    switch($bB) {
    case "b" : $val *= 8; break;
    default : $bB = "B"; break;
    }
    for($i=0;$i<count($symbols)-1 && $val>=$factor;$i++)
        $val /= $factor;
    $p = strpos($val, ".");
    if($p !== false && $p > $digits) $val = round($val);
    elseif($p !== false) $val = round($val, $digits-$p);
    return round($val, $digits) . $symbols[$i] . $bB;
}

function timeLength($time){
    if($day = floor($time/(24*3600))){
        $length .= $day.'天';
    }
    if($hour = floor($time % (24*3600)/3600)){
        $length .= $hour.'小时';
    }
    if($day==0 && $hour==0){
        $length = floor($time/60).'分';
    }
    return $length;
}

function getRefer(&$data){
    if($_COOKIE['REFER']){
        $pos = strpos($_COOKIE['REFER'],':');
        if($pos !== false){
            $data['refer_id'] = substr($_COOKIE['REFER'], 0, $pos);
            $data['refer_url'] = substr($_COOKIE['REFER'], $pos+1);
        }
    }
}

function day($time=null){
    if(!isset($GLOBALS['_day'][$time])){
        return $GLOBALS['_day'][$time] = floor($time/86400);
    }else{
        return $GLOBALS['_day'][$time];
    }
}


function array_slice_preserve_keys($array, $offset, $length = null)
{
    if (version_compare(phpversion(), '5.0.2', ">=")) {
        return(array_slice($array, $offset, $length, true));
    } else {
        $result = array();
        $i = 0;
        if($offset < 0)
            $offset = count($array) + $offset;
        if($length > 0)
            $endOffset = $offset + $length;
        else if($length < 0)
            $endOffset = count($array) + $length;
        else
            $endOffset = count($array);

        // collect elements
        foreach($array as $key=>$value)
        {
            if($i >= $offset && $i < $endOffset)
                $result[$key] = $value;
            $i++;
        }
        return($result);
    }
}

function safeHtml($var){
    return preg_replace(array(
        '/<([\\s\\/]*script)(|\\s.*?)>/is',
        '/<([\\s\\/]*object)(|\\s.*?)>/is',
        '/<([\\s\\/]*iframe)(|\\s.*?)>/is',
        '/<([\\s\\/]*embed)(|\\s.*?)>/is'
    ),'&lt;$1$2$3&gt;',$var);
}

function unSafeVar(&$data)
{
    if (is_array($data))
    {
        foreach ($data as $key => $value)
        {
            unSafeVar($data[$key]);
        }
    }else{
        $data = stripslashes($data);
    }
}

//utftrim() 用于截断utf8尾部乱码
function utftrim($str)
{
    $found = false;
    for($i=0;$i<4&&$i<strlen($str);$i++)
    {
        $ord = ord(substr($str,strlen($str)-$i-1,1));
        if($ord> 192)
        {
            $found = true;
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
            if($i==2) return $str;
            else return substr($str,0,strlen($str)-$i-1);
        }
        else
        {
            if($i==1) return $str;
            else return substr($str,0,strlen($str)-$i-1);
        }
    }
    else return $str;
}

function mydate($f,$d=null){
    global $_dateCache;
    if(!$d)$d=time();
    if(!isset($_dateCache[$d][$f])){
        $_dateCache[$d][$f] = date($f,$d);
    }
    return $_dateCache[$d][$f];
}

function jsValue(&$value) {
    switch(gettype($value)) {
    case 'double':
    case 'integer':
        return $value>0?$value:'"'.$value.'"';
    case 'boolean':
        return $value?'true':'false';
    case 'string':
        return '"'.str_replace(
            array("\n","\b","\t","\f","\r"),
            array('\n','\b','\t','\f','\r'),
            addslashes($value)
        ).'"';
    case 'NULL':
        return 'null';
    case 'object':
        return '"Object '.addslashes(get_class($value)).'"';
    case 'array':
        if (isVector($value)){
            foreach($value as $v){
                $result[] = jsValue($v);
            }
            return '['.implode(',',$result).']';
        }else {
            $result = '{';
            foreach ($value as $k=>$v) {
                if ($result != '{') $result .= ',';
                $result .= jsValue($k).':'.jsValue($v);
            }
            return $result.'}';
        }
    default:
        return '"'.addslashes($value).'"';
    }
}

function isVector (&$array) {
    $next = 0;
    foreach ($array as $k=>$v) {
        if ($k !== $next)
            return false;
        $next++;
    }
    return true;
}

function copy_tree($source,$target){
    $dirhandle=@opendir($source);
    while($file_name=@readdir($dirhandle)){
        if ($file_name!="." && $file_name!=".."){
            if (is_dir("$source/$file_name")){
                mkdir("$target/$file_name",0755);
                copy_tree($source."/".$file_name,$target."/".$file_name);
            }else{
                copy("$source/$file_name","$target/$file_name");
                @chmod("$target/$file_name",0644);
            }
        }
    }
    @closedir($dirhandle);
}

function deltree($dir) {

    $dirhandle=@opendir($dir);
    while($file_name=@readdir($dirhandle)){
        if ($file_name!="." && $file_name!=".."){
            if (is_dir("$dir/$file_name")){
                deltree($dir."/".$file_name);
                @rmdir("$dir/$file_name");
            }else{
                @unlink("$dir/$file_name");
            }
        }
    }
    @closedir($dirhandle);
}

function code2utf($num)
{
    if($num<128)return chr($num);
    if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
    if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
    if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
    return '';
}

function decode_jsescape($string){
    preg_match_all('/%u[0-9A-F]{4}/',$string,$u_codes);
    foreach($u_codes[0] as $code){
        $string=str_replace($code,code2utf(hexdec(substr($code,2,4))),$string);
    }
    return $string;
}


function remote_addr(){
    $addrs = array();

    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        foreach( array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
        {
            $x_f = trim($x_f);

            if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
            {
                $addrs[] = $x_f;
            }
        }
    }

    return isset($addrs[0])?$addrs[0]:$_SERVER['REMOTE_ADDR'];
}

function hostname(){
    $addrs = array();
    if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])){
        $addrs = array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_HOST'] ) );
    }
    return isset($addrs[0])?trim($addrs[0]):$_SERVER['HTTP_HOST'];
}

/**
 * Compare an IP address to network(s)
 *
 * The network(s) argument may be a string or an array. A negative network
 * match must start with a "!". Depending on the 3rd parameter, it will
 * return true or false on the first match, or any negative rule will have
 * absolute priority (default).
 *
 * Samples:
 * match_network ("192.168.1.0/24", "192.168.1.1") -> true
 *
 * match_network (array ("192.168.1.0/24",  "!192.168.1.1"), "192.168.1.1")      -> false
 * match_network (array ("192.168.1.0/24",  "!192.168.1.1"), "192.168.1.1", true) -> true
 * match_network (array ("!192.168.1.0/24", "192.168.1.1"),  "192.168.1.1")      -> false
 * match_network (array ("!192.168.1.0/24", "192.168.1.1"),  "192.168.1.1", true) -> false
 *
 * @param mixed  Network to match
 * @param string IP address
 * @param bool  true: first match will return / false: priority to negative rules (default)
 * @see http://php.benscom.com/manual/en/function.ip2long.php#56373
 */
function match_network ($nets, $ip, $first=false) {
    $return = false;
    if (!is_array ($nets)) $nets = array ($nets);

    foreach ($nets as $net) {
        $rev = (preg_match ("/^\!/", $net)) ? true : false;
        $net = preg_replace ("/^\!/", "", $net);

        $ip_arr  = explode('/', $net);
        $net_long = ip2long($ip_arr[0]);
        $x        = ip2long($ip_arr[1]);
        $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
        $ip_long  = ip2long($ip);

        if ($rev) {
            if (($ip_long & $mask) == ($net_long & $mask)) return false;
        } else {
            if (($ip_long & $mask) == ($net_long & $mask)) $return = true;
            if ($first && $return) return true;
        }
    }
    return $return;
}

define('TIME_SHORT',0);
define('TIME_LANG',2);

function time_format($timestamp,$format = TIME_SHORT){
    switch($format){
    case TIME_SHORT:
        return date('n/j H:m',$timestamp);
    case TIME_LANG:
        return date('D M j',$timestamp);
    default:
        return date('j/n/Y',$timestamp);
    }
}

//将日期格式年-月-日转成时间戳
function dateToTimestamp($date=''){
    if($date == '') return time();
    $aDate = explode("-", $date);
    return mktime(0, 0, 0, $aDate[1], $aDate[2], $aDate[0]);
}

//取毫秒
function getMicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function mkdir_p($dir,$dirmode=0755){
    $path = explode('/',str_replace('\\','/',$dir));
    $depth = count($path);
    for($i=$depth;$i>0;$i--){
        if(file_exists(implode('/',array_slice($path,0,$i)))){
            break;
        }
    }
    for($i;$i<$depth;$i++){
        if($d= implode('/',array_slice($path,0,$i+1))){
            mkdir($d,$dirmode);
        }
    }
    return is_dir($dir);
}

function remove_dots($str){
    global $real_path;
    $path_arr = explode("/",$str);
    $num = count($path_arr)-1;
    for($i=0;$i<$num;$i++){
        $j = $k = 0;
        if($path_arr[$i] == '.') $path_arr[$i]='';
        if($path_arr[$i] == ".."){
            $tmp = $k = $i;
            if($path_arr[$k+1] != '..' && !empty($path_arr[$k])){
                unset($path_arr[$k-1]);
            }else{
                while($path_arr[$k] == '..'){
                    $j++;
                    $k++;
                }
            }
            for($t=1;$t<=$j;$t++){
                unset($path_arr[$tmp]);
                unset($path_arr[$tmp-$j]);
                $tmp--;
            }
            if($j == 0)    unset($path_arr[$i]);
        }
    }
    return $real_path = implode("/",$path_arr);
}

function dealFTPDir(&$dir){
    $arr = explode('/', $dir);
    foreach($arr as $k=>$v){
        if($v != 'syssite'){
            unset($arr[$k]);
        }else{
            break;
        }
    }
    $dir = implode('/',$arr);
}

function __($str){
    if(!isset($GLOBALS['_lang_tools'])){
        $system = &$GLOBALS['system'];
        $GLOBALS['_lang_tools'] = &$system->loadModel('utility/language');
    }
    $lang_tools = &$GLOBALS['_lang_tools'];
    return $lang_tools->translate($str);
}

//配送公式验算function
function cal_fee($exp,$weight,$totalmoney,$defPrice=0){
    if($str=trim($exp)){
        $dprice = 0;
        $weight = $weight + 0;
        $totalmoney = $totalmoney + 0;
        $str = str_replace("[", "getceil(", $str);
        $str = str_replace("]", ")", $str);
        $str = str_replace("{", "getval(", $str);
        $str = str_replace("}", ")", $str);

        $str = str_replace("w", $weight, $str);
        $str = str_replace("W", $weight, $str);
        $str = str_replace("p", $totalmoney, $str);
        $str = str_replace("P", $totalmoney, $str);
        eval("\$dprice = $str;");
        if($dprice === 'failed'){
            return $defPrice;
        }else{
            return $dprice;
        }
    }else{
        return $defPrice;
    }
}
function getval($expval){
    $expval = trim($expval);
    if($expval !== ''){
        eval("\$expval = $expval;");
        if ($expval > 0){
            return 1;
        }else if ($expval == 0){
            return 1/2;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}
function getceil($expval){
    if($expval = trim($expval)){
        eval("\$expval = $expval;");
        if ($expval > 0){
            return ceil($expval);
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function space_split($var){
    return preg_split('/\\s*[["\']([^"\']+)["\']|\s]*/',$var,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
}

function sendfile($file){
    $handle = fopen($file, "r");
    while($buffer = fread($handle,102400)){
        echo $buffer;
        flush();
    }
    fclose($handle);
}

function linechart($chardata){

    $max = max(10,intval(max($chardata)*1.25));

    $w = 300;
    $y = array();
    $xday = array();
    $i=0;
    $xmonty = array();
    $lastMonth = null;
    $count = count($chardata);
    $xmark=array('B,76A4FB,0,0,0');
    $color_1 = '000099';

    foreach($chardata as $d=>$v){

        $d = $d*3600*24;

        if($lastMonth!=($month=date('Y.m',$d))){
            $lastMonth = $month;
            $xmonth[intval($i*(100/$count))] = $month;
            if($i>0)$xmark[] = 'V,'.$color_1.',0,'.$i.',1';
        }
        $i++;
        $xday[] = date('d',$d);
    }
    return 'chs='.$w.'x100&chd=t:'.implode(',',$chardata).
    '&chxt=x,y,x,r&chco=224499&chxl=0:|'.implode('|',$xday).'|1:|0|'.round($max/2).'|'.$max.'|2:|'.implode('|',$xmonth).
    '|3:|0|'.round($max/2).'|'.$max.'&cht=lc&chds=0,'.$max.'&chxp=2,'.implode(',',array_keys($xmonth)).'&chxs=2,'.$color_1.',13&chm='.implode('|',$xmark).'&chg=5,25,1';
}

function download($fname='data',$data=null,$mimeType='application/force-download'){

    if(headers_sent($file,$line)){
        echo 'Header already sent @ '.$file.':'.$line;
        exit();
    }

    //header('Cache-Control: no-cache;must-revalidate'); //fix ie download bug
    header('Pragma: no-cache, no-store');
    header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");

    if(strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')){
        $fname = urlencode($fname);
        header('Content-type: '.$mimeType);
    }else{
        header('Content-type: '.$mimeType.';charset=utf-8');
    }
    header("Content-Disposition: attachment; filename=\"".$fname.'"');
    //header( "Content-Description: File Transfer");

    if($data){
        header('Content-Length: '.strlen($data));
        echo $data;
        exit();
    }
}

function value(){
    foreach(func_get_args() as $v){
        if($v)return $v;
    }
}

function array_key_filter(&$array,$keys){
    $return = array();
    foreach(explode(',',$keys) as $k){
        if(isset($array[$k])){
            $return[$k] = &$array[$k];
        }
    }
    return $array = $return;
}

function has_unsafeword($str){
    return preg_match('/~!@#\\$%\\^&\\*\\(\\)\\+=\\|\\}]{\\[":><\\?;\'\/\\.,/', $str);
}

/**
 * 上传文件后，将目标文件的权限设置为0644，避免有些服务器丢失读去权限
 */
function move_chmod_uploaded_file($filename,$destination,$mod=0644){
    if(move_uploaded_file($filename,$destination)){
        chmod($destination,$mod);
        return true;
    }else{
        return false;
    }
}
