<?php
/**
 * ctl_delivery
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.delivery.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Likunpeng <leoleegood@zovatech.com>
 * @license Commercial
 */

include_once('objectPage.php');
class ctl_deliveryarea extends objectPage {
    var $workground ='setting';
    var $object = 'trading/deliveryarea';
    var $actionView = 'delivery/area_finder_action.html'; //默认的动作html模板,可以为null
    var $editMode = true;
    
    var $disableGridEditCols = "area_id";
    var $disableColumnEditCols = "area_id";    
    var $disableGridShowCols = "area_id";    
    function detail($aAreaId){
        $this->path[] = array('text'=>'配送地区编辑');
        $oObj = $this->system->loadModel('trading/deliveryarea');
        $this->pagedata['area'] = $oObj->getDlAreaById($aAreaId);
        $this->setView('delivery/area_edit.html');
        $this->output();
    }
    function index(){
        $dArea = $this->system->loadModel('trading/deliveryarea');
        $this->path[]=array('text'=>'配送地区列表');
        if ($dArea->getTreeSize()){//超过100条 
            $this->pagedata['area'] = $dArea->getRegionById();
            $this->page('delivery/area_treeList.html');
        }else{
            $dArea->getMap();
            $this->pagedata['area']=$dArea->regions;
            $this->page('delivery/area_map.html');
        }
            
    }
    function getChildNode($regionId){
        $dArea = $this->system->loadModel('trading/deliveryarea');
        $this->pagedata['area'] = $dArea->getRegionById($_POST['regionId']);
        $this->setView('delivery/area_sub_treeList.html');
        $this->output();
    }
    function showNewArea($pRegionId){
        if ($pRegionId){
            $dArea = $this->system->loadModel('trading/deliveryarea');
            $this->pagedata['parent'] = $dArea->getRegionByParentId($pRegionId);
        }
        $this->path[] = array('text'=>'添加配送地区');
        $this->page('delivery/area_new.html');
    }
    function addDlArea(){
        $oObj = $this->system->loadModel('trading/deliveryarea');
        if(!$oObj->insertDlArea($this->in,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/deliveryarea&act=index',$this->message['string']);
        }else
            $this->splash('success','index.php?ctl=trading/deliveryarea&act=index');

    }
     function saveDlArea(){
        $oObj = $this->system->loadModel('trading/deliveryarea');
        if(!$oObj->updateDlArea($this->in,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/deliveryarea&act=detailDlArea&p[0]='.$_POST['region_id'],$this->message['string']);
        }else
            $this->splash('success','index.php?ctl=trading/deliveryarea&act=detailDlArea&p[0]='.$_POST['region_id']);

    }
    function detailDlArea($aRegionId){
        $this->path[] = array('text'=>'配送地区编辑');
        $oObj = $this->system->loadModel('trading/deliveryarea');
        $this->pagedata['area'] = $oObj->getDlAreaById($aRegionId);
        $this->page('delivery/area_edit.html');
    }
    function toRemoveArea($regionId){
        $this->begin('index.php?ctl=trading/deliveryarea&act=index');
        $dArea = $this->system->loadModel('trading/deliveryarea');
        $this->end($dArea->toRemoveArea($regionId),__('删除成功！'));
    }
    function updateOrderNum(){
        $this->begin('index.php?ctl=trading/deliveryarea&act=index');
        $dArea = $this->system->loadModel('trading/deliveryarea');
        $this->end($dArea->updateOrderNum($_POST['p_order']),__('排序成功！'));
    }        
}
?>