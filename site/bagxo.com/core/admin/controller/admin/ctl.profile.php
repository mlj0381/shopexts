<?php
class ctl_profile extends adminPage{

    /**
     * index
     *
     * @access public
     * @return void
     */
    //修改个人信息
    function operator(){
        if(defined('SAAS_MODE')&&SAAS_MODE){
            $this->pagedata['saas_mode'] = true;
        }
        $this->path[] = array('text'=>'帐号设置');
        $oOpt = $this->system->loadModel('admin/operator');
        $data = $oOpt->instance($this->op->opid);

        $this->pagedata['op_id'] = $this->op->opid;
        $this->pagedata['data'] = $data;
        $data['config'] = unserialize($data['config']);
        $this->pagedata['timezone_value'] = $GLOBALS['user_timezone'];

        $zones=array();
        $realtime = time() - SERVER_TIMEZONE*3600;
        $tzs = timezone_list();
        foreach($tzs as $i=>$tz){
            $zones[$i] = mydate('H:i',$realtime+$i*3600).' - '.$tz;
        }
        
        $this->pagedata['timezones'] = $zones;
        $this->pagedata['server_tz'] = $tzs[SERVER_TIMEZONE];
        $this->pagedata['tzlist'] = $tzs;
        $this->setView('admin/self.html');
        $this->output();
    }

    /**
     * saveSelf
     *
     * @access public
     * @return void
     */
    //保存自身信息（修改后保存）
    function saveSelf(){
        $this->begin('index.php?ctl=admin/profile&act=operator');
        $oOpt = $this->system->loadModel('admin/operator');
        if($_POST['changepwd']){
            $row = $oOpt->instance($this->op->opid,'userpass');
            if(md5($_POST['oldpass'])!=$row['userpass']){
                trigger_error('请输入正确的当前密码',E_USER_ERROR);
            }
            if($_POST['userpass']!=$_POST['passowrd_again']){
                trigger_error('两次密码输入不一致',E_USER_ERROR);
            }
        }else{
            unset($_POST['userpass']);
        }
        array_key_filter($_POST,'userpass,timezone');
        $oOpt->update($_POST,array('op_id'=>$this->op->opid));
        $_POST['op_id'] = $this->op->opid;
        $oProfile = $this->system->loadModel('adminProfile');
        $oProfile->load($this->op->opid);
        $this->end($oOpt->toUpdateSelf($_POST,$oProfile->setting()),'信息保存成功');
    }
}
?>
