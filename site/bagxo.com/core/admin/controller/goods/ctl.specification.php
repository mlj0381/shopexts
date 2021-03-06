<?php
/**
 * ctl_specification 
 * 
 * @uses adminPage
 * @package 
 * @version $Id: ctl.specification.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Liujy <ever@zovatech.com> 
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_specification extends objectPage{
    
    var $name='规格';
    var $workground = 'goods';
    var $object = 'goods/specification';
    var $editMode = false;
    var $actions= array(
        'edit'=>'编辑'
    );
    var $actionView = 'product/spec/finder_action.html';   
    var $disableGridEditCols = "spec_id";
    var $disableColumnEditCols = "spec_id";
    var $disableGridShowCols = "spec_id,spec_memo";
    var $noRecycle = true;
    
    function edit($specid=0){
        $storager = $this->system->loadModel("system/storager");
        $this->path[] = array('text'=>'规格编辑');
        if($specid){
            $objSpec = $this->system->loadModel('goods/specification');
            $aSpec = $objSpec->getFieldById($specid,array('*'));
            $aVal = $objSpec->getValueList($specid);
            $this->pagedata['spec'] = $aSpec;
            $this->pagedata['width'] = $this->system->getConf('spec.image.width');
            $this->pagedata['height'] = $this->system->getConf('spec.image.height');
            $this->pagedata['img_path'] = $storager->getUrl($this->system->getConf('spec.default.pic'));
            $this->pagedata['spec']['vals'] = $aVal;
        }else{
            $this->addspec();
        }
        $this->page('product/spec/detail.html');
    }
    
    function addspec($specid=0){
        $storager = $this->system->loadModel("system/storager");
        $this->path[] = array('text'=>'规格新增');
        $this->pagedata['width'] = $this->system->getConf('spec.image.width');
        $this->pagedata['height'] = $this->system->getConf('spec.image.height');
        $this->pagedata['img_path'] = $storager->getUrl($this->system->getConf('spec.default.pic'));
        $this->page('product/spec/detail.html');
    }

    function save(){
        $this->begin('index.php?ctl=goods/specification&act=index');
        $objSpec = $this->system->loadModel('goods/specification');
        $this->end($objSpec->toSave($_POST), __('保存成功!'));
    }
    
    function toRemoveSpec($id , $gid){
        $objSpec = $this->system->loadModel('goods/specification');
        $objSpec->toRemove($id);
        $this->splash('success', 'index.php?ctl=goods/product&act=addSpec&p[0]='.$gid, 1);
    }
    
    function toRemoveValue($valueid, $specid){
        $objSpec = $this->system->loadModel('goods/specification');
        if($objSpec->toRemoveValue($valueid, $specid)){
            //$this->message = array('string'=>__('保存成功！'),'type'=>MSG_OK);
        }else{
            //$this->message = array('string'=>__('保存失败！'),'type'=>MSG_ERROR);
        }
        $this->output();
    }

    function selSpec(){
        $objSpec = $this->system->loadModel('goods/specification');
        $this->pagedata['spec'] = $objSpec->getFieldById( $_POST['spec_id'], array('*') );
        $this->pagedata['spec_value'] = $objSpec->getValueList( $_POST['spec_id'] );
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->setView('product/sel_spec_value.html');
        $this->output();
    }
    
    function check_spec_value_id(){
        $objSpec = $this->system->loadModel('goods/specification');
        echo $objSpec->check_spec_value_id($_POST['spec_value_id']);
    }
}
