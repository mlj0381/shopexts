<?php
class ctl_comeback extends adminPage{

    var $workground = 'tools';

    function index(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $this->path[] = array('text'=>'数据恢复');
        $pkg = $this->system->loadModel('system/backup');
        $this->pagedata['archive']=$pkg->getList(HOME_DIR.'/backup');
        $this->pagedata['appver']=$this->system->version();
        $this->page('system/comeback/tgzFileList.html');
    }
    function comeback($filename,$mtime,$vols){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $this->pagedata['filename']=$filename;
        $this->pagedata['mtime']=$mtime;
        $this->pagedata['vols']=$vols;
        $this->setView('system/comeback/comeback.html');
        $this->output();
    }
    function recover($filename,$vols,$fileid){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $recoverProgress = $this->system->loadModel('system/backup');
        $recoverProgress->recover($filename,$vols,$fileid);
        if($vols==$fileid){
            echo '数据库已恢复完毕 <script>$("btnarea").innerHTML = \'<b class="submitBtn"><input type="button" value="确定" onclick="sqlcomeback.close()"></b>\';</script>';
        }
        else{
            echo '正在恢复第'.($fileid+1).'卷 共'.$vols.'卷<script>dorecover("index.php?ctl=system/comeback&act=recover&p[0]='.$filename.'&p[1]='.$vols.'&p[2]='.($fileid+1).'");</script>';
        }
        $this->system->cache->clear();
    }

    function removeTgz(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $backup= &$this->system->loadModel('system/backup');
        if(count($this->in['tgz'])>0){
            $backup->removeTgz($this->in['tgz']);
            $this->splash('success','index.php?ctl=system/comeback&act=index');
        }else{
            $this->splash('failed','index.php?ctl=system/comeback&act=index','删除失败：请选择操作的记录');
        }
        
    }
}
?>