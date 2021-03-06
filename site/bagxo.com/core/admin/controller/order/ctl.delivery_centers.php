<?php
include_once('objectPage.php');
class ctl_delivery_centers extends objectPage{

    var $name = '发货信息';
    var $workground = 'order';
    var $object='trading/dly_centers';
    var $actionView = 'order/dly_center_action.html';
    var $actions= array(
        'editor'=>'编辑',
    );

    function add_center(){
        $this->page('order/dly_center_editor.html');
    }

    function save_data(){
        $this->begin('index.php?ctl=order/delivery_centers&act=index');
        if($_POST['dly_center_id']){
            if($_POST['is_default']){
                $this->system->setConf('system.default_dc',$_POST['dly_center_id']);
            }
            $this->end( $this->model->update($_POST,array('dly_center_id'=>$_POST['dly_center_id'])),'发货信息保存成功');
        }else{
            $dly_center_id = $this->model->insert($_POST);
            if($dly_center_id && $_POST['is_default']){
                $this->system->setConf('system.default_dc',$dly_center_id);
            }
            $this->end( $dly_center_id,'发货信息添加成功');
        }
    }

    function instance($dly_center_id){
        $this->pagedata['the_dly_center'] = $this->model->instance($dly_center_id);
        $this->setView('order/dly_center.html');
        $this->output();
    }

    function editor($dly_center_id){
        $this->pagedata['default_dc'] = $dly_center_id == $this->system->getConf('system.default_dc');
        $this->pagedata['dly_center'] = $this->model->instance($dly_center_id);
        $this->page('order/dly_center_editor.html');
    }
}
?>