<?php
class mdl_upgrade extends modelFactory{

    var $line=0;
    var $sql=null;
    var $succ=false;

    function exec($step){
        if(method_exists($this,$action = 'action_'.$step)){
            $this->$action();
        }else{
            $this->action_welcome();
        }
    }

    function action_welcome(){

        $versionTxt = $this->system->version();
        $dbver = $_GET['force_ver']?$_GET['force_ver']:$this->dbVersion();
        $this->system->cache->clear();
        $smarty = &$this->system->loadModel('system/frontend');
        $smarty->compile_dir = HOME_DIR.'/cache/admin_tmpl';
        $smarty->clear_compiled_tpl();

        $smarty->compile_dir = HOME_DIR.'/cache/front_tmpl';
        $smarty->clear_compiled_tpl();

        if($this->checkFileList(BASE_DIR.'/upgrade.php',$failedFiles,1) || $_GET['ignore_lost']){
            $scripts = $this->scripts($dbver,$versionTxt['rev']);
            $data['scripts'] = &$scripts;
            if($data['scripts_count'] = count($scripts)){
                $dbver['version']==$versionTxt['dbver'];
                $this->output('ready.html',$data);
            }else{
                $this->output('done.html',$data);
            }
        }else{
            $data['files'] = $failedFiles;
            $this->output('uploading.html',$data);
        }
    }

    function action_runscript(){

        header('Content-type: text/html;charset=utf-8');
        set_time_limit(0);

        if(!($file = $_POST['file']) || !file_exists(CORE_DIR.'/updatescripts/'.$file)){
            echo 'missing file'.$_POST['file'];
            return;
        }

        if(!file_exists(HOME_DIR.'/logs/upgrade_'.substr( $_POST['file'] ,0 ,-4 ).'_'.$_POST['timeline'].'.log.php')){
            error_log('#<?php exit()?>'." \n \n",3,HOME_DIR.'/logs/upgrade_'.substr( $_POST['file'] ,0 ,-4 ).'_'.$_POST['timeline'].'.log.php');
        }

        switch(ext_name($file)){

        case '.php':
            include(CORE_DIR.'/updatescripts/'.$file);
            if(class_exists('UpgradeScript')){
                $oUpgrade = new UpgradeScript();
                $oUpgrade->step=$_POST['step']?$_POST['step']:'1';
                $oUpgrade->runFunc = $_POST['runFunc']?$_POST['runFunc']:'first';
                $oUpgrade->status = $_POST['runStatus']?$_POST['runStatus']:'all-finish';
                $oUpgrade->version =  substr( $file ,0 ,-4 );

//                $oUpgrade->runFunc = $runFunc;
                $oUpgrade->__Upgrade();
            }

            break;

        case '.sql':
            $this->run_update_sql($file);
            break;
        }

        $this->setDbver(substr($file,0,-4));
    }

    function setDbver($ver){
        $this->db->exec("drop table if exists ".DB_PREFIX."dbver");
        $this->db->exec("create table ".DB_PREFIX."dbver(`".$ver."` varchar(255))");
    }

    function run_update_sql($file){

        foreach($this->db->splitSql(file_get_contents(CORE_DIR.'/updatescripts/'.$file)) as $runningSQL){
            if($this->db->exec($runningSQL)){
                $output.=update_message($runningSQL);
            }else{
                $errinfo = $this->db->errorInfo();
                $etype = E_ERROR;
                if(preg_match('/syntax to use near \'(.*?)\' at line/i',$errinfo,$match)){
                    $runningSQL = str_replace($match[1],'<b>'.$match[1].'</b>',$runningSQL);
                }elseif(preg_match('/Duplicate [a-z]+ name/',$errinfo)){
                    $etype = E_WARNING;
                }
                $output.=update_message($runningSQL,$etype);
            }
        }
        echo $output;
    }

    function output($output,$data){
        $data['page'] = $output;
        $smarty = &$this->system->loadModel('system/frontend');

        foreach($data as $k=>$v){
            $smarty->assign($k,$v);
        }

        
        $smarty->assign('version', $this->system->version());
        header('Content-Type: text/html;charset=utf-8');
        $smarty->display('admin:upgrade/page.html');
    }

    function checkFileList($file,&$list,$ignore_lines=0){
        $list = array();
        $handle = fopen($file,'r');
        while ($line = fgetcsv($handle, 1000, ',')) {
            if($ignore_lines > $i++){
                continue;
            }
            if(md5_file(BASE_DIR.'/'.$line[0]) != $line[1]){
                $list[] = $line[0];
            }
        }
        fclose($handle);
        return 0==count($list);
    }

    function scripts($from,$to){
        $dir = CORE_DIR.'/updatescripts';
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))){
                if (is_file($dir.'/'.$file) && $file{0}!='.'){
                    if(preg_match('/^([0-9]+)\.(sql|php)$/i',$file,$match)){
                        if($match[1]>$from && $match[1]<=$to){
                            $step[]=$file;
                            if($match[2]=='php'){
                                $order[] = $match[1]*2+1;
                            }else{
                                $order[] = $match[1]*2;
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        array_multisort($order,$step);
        return $step;
    }

    function dbVersion(){
        $rs = $this->db->exec('SHOW TABLES like "'.DB_PREFIX.'dbver"');
        if($rs->getArray(1)){
            $rs = $this->db->exec('select * from '.DB_PREFIX.'dbver');
            $col = $rs->FetchField(0);
            $rows = $rs->GetArray(1);
            $rs = null;
            return $col->name;
        }else{
            return 0;
        }
    }
}

function update_message($msg,$etype=0,$errinfo=null){
    $upgradeVersion = substr( $_POST['file'] ,0 ,-4 );
    $logFileName = HOME_DIR.'/logs/upgrade_'.$upgradeVersion.'_'.$_POST['timeline'].'.log.php';
    switch($etype){
    case E_WARNING:
    case E_USER_WARNING:
        error_log('出错 '.$msg.($errinfo?'->'.$errinfo:'')." \n \n",3,$logFileName);
        return '<div class="runsqldetail"><div class="sql-body">'.$msg.'</div><div class="error">[出错]'.$errinfo.'</div></div>';

    case E_ERROR:
    case E_USER_ERROR:
        error_log('警告 '.$msg.($errinfo?'->'.$errinfo:'')." \n \n",3,$logFileName);
        return '<div class="runsqldetail"><div class="sql-body">'.$msg.'</div><div class="warning">[警告]'.$errinfo.'</div></div>';

    default:
        error_log('成功 '.$msg.($errinfo?'->'.$errinfo:'')." \n \n",3,$logFileName);
        return '<div class="runsqldetail"><div class="succ">[成功]'.$msg.'</div></div>';
    }
}

class Upgrade {
    
    var $step;
    var $msg;
    var $status;
    var $title = '';
    var $funcList = array();
    var $runFunc = 'first';
    var $version;
    var $updateMsg;

    function Upgrade(){
        $this->system = &$GLOBALS['system'];
        $this->db = &$this->system->database();
        
        set_time_limit(0);
        foreach( array_diff( get_class_methods($this) , get_class_methods('Upgrade') ) as $func ){
            if( substr($func,0,8) != 'upgrade_' )
                continue;
            $this->funcList[] = substr($func,8);
        }
//        echo '<input type="hidden" class="allFunc" value="'.implode(',',$this->funcList).'"/>';
    }

    function upgrade_first(){
    }

    function upgrade_last(){
    }

    function __Upgrade(){
//        $this->status = $this->upgrade_.$this->runFunc();
        eval('$this->status = $this->upgrade_'.$this->runFunc.'();');
//        error_log("upgrade_".$this->runFunc."方法 ".( $this->status=='continue'? )." \n",3,.HOME_DIR.'/logs/upgrade_'.$this->version.'.log');

        echo $this->updateMsg;
        if( $this->runFunc == 'first' ){
            $this->title = $this->title?$this->title:'初始化升级';
            $this->runFunc = array_key_exists( 0, $this->funcList )?$this->funcList[0]:'last';
            $this->status = $this->status?$this->status:'finish';
        }else if( $this->runFunc == 'last' ){
            $this->status = 'all-finish';
            echo '<input type="hidden" class="upgrade-notice" value=\''.json_encode($this->noticeMsg).'\'/>';
        }else{
            switch($this->status){
                case 'continue':
                    $this->title = $this->title?'正在升级 '.$this->title:'升级中';
                    //to be continue...
                    break;

                case 'finish':
                    $this->title = $this->title?$this->title:'升级中';
                    $funcKey = array_search($this->runFunc, $this->funcList);
                    if(array_key_exists( $funcKey+1, $this->funcList )){
                        $this->runFunc = $this->funcList[$funcKey+1];
                    }else{
                        $this->title = '回收升级临时数据';
                        $this->runFunc = 'last';
                    }
                    break;

                case 'error':
                    echo '<input type="hidden" class="upgrade-notice" value=\''.json_encode($this->noticeMsg).'\'/>';
                    $this->title .= ' 升级出错';
                    break;
                default:
                    $this->status = 'all-finish';
                    break;
            }
        }
        echo '<input type="hidden" class="up-title" value="'.$this->title.'"/>';
        echo '<input type="hidden" class="runFunc" value="'.$this->runFunc.'" />';
        echo '<input type="hidden" class="run-status" value="'.$this->status.'"/>';
//        usleep(100000);
    }

}

?>
