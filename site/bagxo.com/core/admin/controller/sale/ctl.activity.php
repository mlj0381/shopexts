<?php
include_once('objectPage.php');
class ctl_activity extends objectPage{

    var $name = '促销活动';
    var $workground = 'sale';
    var $object = 'trading/promotionActivity';
    var $actionView = 'sale/activity/finder_action.html'; //默认的动作html模板,可以为null
    //var $filterView = 'sale/activity/finder_filter.html'; //默认的过滤器html,可以为null
    var $actions= array(
                'activityInfo'=>'编辑',
                'addPromotion'=>'添加促销规则'
            );
    var $allowImport = false;
    var $allowExport = false;
    var $noRecycle = true;
    function activityInfo($pmtaId=NULL){
        $this->path[] = array('text'=>'促销活动内容');
        $_SESSION['SWP_ACTIVITY'] = null;
        if (!empty($pmtaId)&&intval($pmtaId)!=0) {

            $oPromotionActivity = $this->system->loadModel('trading/promotionActivity');
            $this->pagedata['pmta']= $oPromotionActivity->getActivityById($pmtaId);
            $this->pagedata['pmta']['pmta_time_begin'] = dateFormat($this->pagedata['pmta']['pmta_time_begin']);
            $this->pagedata['pmta']['pmta_time_end'] = dateFormat($this->pagedata['pmta']['pmta_time_end']);
            $this->pagedata['_S']['act'] = 'edit';
        } else {
            $this->pagedata['pmta']['pmta_enabled'] = 'true';
            $this->pagedata['_S']['act'] = 'add';
        }
        $this->page('sale/activity/activityInfo.html');
    }
    function addPromotion($pmtaId=NULL){
        $this->jumpTo('addPromotion','sale/promotion',array('0',$pmtaId));
    }
    function jumpTo($act='index',$ctl=null,$args=null){
        $_GET['act'] = $act;
        if($ctl) $_GET['ctl'] = $ctl;
        if($args) $_GET['p'] = $args;

        if(!is_null($ctl)){

            if($pos=strpos($_GET['ctl'],'/')){
                $domain = substr($_GET['ctl'],0,$pos);
            }else{
                $domain = $_GET['ctl'];
            }
            $ctl = &$this->system->getController($ctl);
            $ctl->message = $this->message;
            $ctl->pagedata = &$this->pagedata;
            $ctl->ajaxdata = &$this->ajaxdata;
            $this->system->callAction($ctl,$act,$args);
        }else{
            $this->system->callAction($this,$act,$args);
        }
    }
    function doActivityInfo($action) {        
        $this->path[] = array('text'=>'促销活动配置完成');
        if ($action=='add') {
            $oPromotionActivity = $this->system->loadModel('trading/promotionActivity');
            $oPromotion = $this->system->loadModel('trading/promotion');
            //保存活动信息
            $nPmtaId = $oPromotionActivity->saveActivity($this->in);            
            $_SESSION['SWP_ACTIVITY']['pmta_id'] = $nPmtaId;
            $_SESSION['SWP_ACTIVITY']['pmta_name'] = $this->in['pmta_name'];
            $_SESSION['SWP_PROMOTION'] = NULL;
        }else if ($action='edit'){
            $oPromotionActivity = $this->system->loadModel('trading/promotionActivity');
            $oPromotionActivity->saveActivity($this->in);
        }
        $this->completeActivity($action);
    }

    function completeActivity($action) {
        $this->pagedata['pmta'] = $_SESSION['SWP_ACTIVITY'];
        $this->pagedata['action'] = $action;
        $this->page('sale/activity/completeActivity.html');
    }

    function delete() {
        $oPromotionActivity = $this->system->loadModel('trading/promotionActivity');
        $activityIds = $_POST['pmta_id'];
        if ($oPromotionActivity->delActivity($activityIds,$msg)) {
            echo '删除成功';
            //        $this->splash('success', 'index.php?ctl=sale/activity&act=index');
        } else {
            echo '删除失败';            
//            $this->splash('failed', 'index.php?ctl=sale/activity&act=index',  $msg);
        }
    }

    
    function detail($active_id) {
      header('Location: index.php?ctl=sale/promotion&act=index&compact=true&p[0]='.$active_id);
    }
}
?>
