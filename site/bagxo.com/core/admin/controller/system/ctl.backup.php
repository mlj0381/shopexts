<?php
class ctl_backup extends adminPage{

    var $workground ='tools';

    function index(){

        $this->path[] = array('text'=>'数据备份');
        $this->page('system/backup/backup.html');
    }
    function pTest(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $dir = HOME_DIR.'/backup';
            $tar = $this->system->loadModel('utility/tar');
            chdir($dir);
            $tar->addFile('multibak_20081117171948_1.sql');
            $tar->addFile('multibak_20081117171948_2.sql');
            $tar->addFile('multibak_20081117171948_4.sql');
            $tar->addFile('multibak_20081117171948_5.sql');
            $tar->addFile('multibak_20081117171948_6.sql');

            $verInfo = $this->system->version();
            $backupdata['app'] = $verInfo['app'];
            $backupdata['rev'] = $verInfo['rev'];
            $backupdata['vols'] = $fileid;
            $xml = $this->system->loadModel('utility/xml');
            $xmldata = $xml->array2xml($backupdata,'backup');
            file_put_contents('archive.xml',$xmldata);
            $tar->addFile('archive.xml');
            $tar->filename = 'multibak_aaaa.tgz';
            $tar->saveTar();
            echo 'ok';
            /*
            for($i=1;$i<=$fileid;$i++){
                @unlink('multibak_'.$filename.'_'.$i.'.sql');
            }
            */
           // @unlink('archive.xml');

    }
    function backup(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        header("Content-type:text/html;charset=utf-8");
        $params['sizelimit'] = 1024;
        $params['filename'] = ($_GET["filename"]=="")?date("YmdHis", time()):$_GET["filename"];
        $params['fileid'] = ($_GET["fileid"]=="")?"0":intval($_GET["fileid"]);
        $params['tableid'] = ($_GET["tableid"]=="")?"0":intval($_GET["tableid"]);
        $params['startid'] = ($_GET["startid"]=="")?"-1":intval($_GET["startid"]);
        if ($params['sizelimit']!="")
        {
            $oBackup=$this->system->loadModel('system/backup');
            if(!$oBackup->startBackup($params,$nexturl)){
                echo '正在备份第'.($params['fileid']+2).'卷，请勿进行其他页面操作。'.'<script>runbackup("'.$nexturl.'")</script>';
            }
            else{
                echo "<a href='index.php?ctl=sfile&act=getDB&p[0]=multibak_{$params['filename']}.tgz'>备份完毕，请点击本处下载</a>";
            }
        }
    }

}
?>