<?php
/**
 * ctl_sfile{ 
 * 
 * @package 
 * @version $Id: ctl.sfile.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com> 
 * @license Commercial
 */
error_reporting( E_ERROR | E_WARNING | E_PARSE );
class ctl_sfile{

    function ctl_sfile(&$system){
        $this->system = $system;

        $this->js_dir = CORE_DIR.'/'.__ADMIN__.'/client/js';
        $this->script_dir = CORE_DIR.'/'.__ADMIN__.'/client/scripts';

        if($_SESSION['profile']){
            $op = &$_SESSION['profile'];
            $this->theme = CORE_DIR.'/'.__ADMIN__.'/themes/'.$op->get('theme');
            $this->theme = file_exists($this->theme.'/style.css')?$this->theme:CORE_DIR.'/'.__ADMIN__.'/themes/SmartTime';
        }else{
            $this->theme = CORE_DIR.'/'.__ADMIN__.'/themes/SmartTime';
        }
    }

    function get($ident){
            $sfile = $this->system->loadModel('system/sfile');
            $sfile->output($ident);
    }

    function getDB($ident){
            $sfile = $this->system->loadModel('system/sfile');
            $sfile->outputDB($ident);
    }

    function upload(){
        $sfile = $this->system->loadModel('system/sfile');
        $finfo = $sfile->save($_FILES['Filedata'],array('usedby'=>$_POST['usedby']));
        if($_POST['handle'] && $p=strpos($_POST['handle'],':')){

            $cls = substr($_POST['handle'],0,$p);
            $act = substr($_POST['handle'],$p+1);

            if($cls  = $this->system->loadModel($cls)){
                $finfo = $cls->$act(
                    ($p=strpos($_POST['usedby'],':'))?substr($_POST['usedby'],$p+1):$_POST['usedby'],
                     $finfo);
            }
        }
        echo json_encode($finfo);
    }

    function goodsicon($schema_id){
        header('Content-type: image/png');
        $this->system->sfile(PLUGIN_DIR.'/schema/'.$schema_id.'/icon-48x48.png');
    }
}
?>
