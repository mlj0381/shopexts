<?php
/**
 * ctl_return_product
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.return_product.php 2009 2008-11-21 11:27:56Z hujianxin $
 * @copyright 2003-2007 ShopEx
 * @author hujianxin <hjx@shopex.cn>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_return_product extends objectPage{

    var $name='售后申请';
    var $filterView = 'order/return_product/filter.html';
    var $workground = 'order';
    var $object = 'trading/return_product';
    function new_msg(){
        $result=array('no_handle'=>1);
        parent::index(array('params'=>$result));
    }
    function detail($return_id){
        $rp = $this->system->loadModel('trading/return_product');
        
        $info = $rp->load($return_id);
        $this->pagedata['info'] = $info;
        
        $this->setView('order/return_product/detail.html');
        $this->output();
    }    
    
    function save(){
        $rp = $this->system->loadModel('trading/return_product');
        
        $return_id = $_POST['return_id'];
        $status = $_POST['status'];
        $this->pagedata['return_status'] = $rp->change_status($return_id,$status);
        $this->setView('order/return_product/return_status.html');
        $this->output();
    }
    
    
    function send_comment(){
        $rp = $this->system->loadModel('trading/return_product');
    
        $return_id = $_POST['return_id'];
        $comment = $_POST['comment'];
        
        $this->begin('index.php?ctl=order/return_product&act=detail&p[0]='.$return_id);
        
        if($rp->send_comment($return_id,$comment)){
            trigger_error('发送成功',E_USER_NOTICE);
        }else{
            trigger_error('发送失败',E_USER_ERROR);
        }
        
        $this->end();
    }
    
    function file_download($return_id){
        $rp = $this->system->loadModel('trading/return_product');
        
        $info = $rp->load($return_id);
        $filename = $info['image_file'];
        
        $rp->file_download($filename);
    }


    function string(){
        $oPage = $this->system->loadModel('content/page');
        unset($this->path);
        $this->path[] = array('text'=>'功能配置');
        $this->pagedata['is_open'] = $this->system->getConf('site.is_open_return_product');
        $this->pagedata['data'] = $oPage->get_tpl_content('return_policy');
        $this->pagedata['enable_purview_options'] = array('true'=>'开启','false'=>'关闭');
        $this->page('setting/return_product.html');
    }
    
    function string_save(){
        $obj = $this->system->loadModel('trading/return_product');
        $oPage = $this->system->loadModel('content/page');
        if( $_POST['return_is_open'] == "true" ){
            $this->system->setConf('site.is_open_return_product',true);
        }
        else if( $_POST['return_is_open'] == "false" ){
            $this->system->setConf('site.is_open_return_product',false); 
        }
        if( $_POST['conmment'] ){
            $aData['content'] = $_POST['conmment'];
            $aData['tmpl_name'] = 'return_policy';
            $oPage->set_tpl_content($aData);
        }else{
            $aData['content'] = "";
            $aData['tmpl_name'] = 'return_policy';
            $oPage->set_tpl_content($aData);
        }
        $this->begin('index.php?ctl=order/return_product&act=string');
        $this->end();
    }
}
