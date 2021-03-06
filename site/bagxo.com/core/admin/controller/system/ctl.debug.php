<?php
class ctl_debug extends adminPage{
    var $workground ='tools';

    function index(){
        $this->path[] = array('text'=>'网店暂停营业');
        $oDebug=$this->system->loadModel('system/debug');
        $this->pagedata['shopurl']=$this->system->base_url();
        $this->pagedata['systemShopMode']=!is_file(HOME_DIR.'/notice.html');
        if(!$this->pagedata['systemShopMode']){
            $this->pagedata['announcement'] = file_get_contents(HOME_DIR.'/notice.html');
        }
        $this->pagedata['shop_mode']=$systemShopMode;
        $this->page('system/debug/debug.html');
    }

    function editShopMode(){
        $this->begin('index.php?ctl=system/debug&act=index');
        $oDebug=$this->system->loadModel('system/debug');

        if($_POST['shop_mode']){
            $this->end($oDebug->stopShopMode($_POST['announcement']));
        }else{
            $this->end($oDebug->startShopMode());
        }
    }

    function detectFlash(){
        $this->page('system/debug/flash.html');
    }
    function updateSql(){
        $this->begin('index.php?ctl=system/debug&act=check_database');
        if($_POST['sql']){
            $dbcheck=$this->system->loadModel('utility/databasecheck');
            $this->end($dbcheck->updateSql(),__('数据修复成功'));
        }else{
            $this->end(false,__('数据修复失败'));
        }
    }
    function check_database(){
        $dbcheck=$this->system->loadModel('utility/databasecheck');
        $this->pagedata['lack']=$dbcheck->doDatabaseCheck();
        $this->page('system/debug/databasecheck.html');
    }
    function clear(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $this->page('system/debug/clear.html');
    }

    function cleardata(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            exit;
        }
        $this->begin('index.php?ctl=system/debug&act=clear');
        $op = $this->system->loadModel('admin/operator');
        if($op->tryLogin($_POST)){
            $clr = $this->system->loadModel('system/debug');
            $clr->clearData();
        }else{
            trigger_error('录入的用户名或密码不正确',E_USER_ERROR);
        }
        $this->end(true,__('数据成功清除'));
    }
}
?>
