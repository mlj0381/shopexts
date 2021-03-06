<?php
include_once('objectPage.php');
class ctl_refund extends objectPage{

    var $name = '退款单';
    var $workground = 'order';
    var $object = 'trading/refund';
    var $actionView='order/refund/finder_action.html';
    var $filterView='order/refund/finder_filter.html';

    function detail($nID){
        $oRefund=$this->system->loadModel('trading/refund');
        $aDetail=$oRefund->detail($nID);
        $oPayment=$this->system->loadModel('trading/payment');

        $o = $this->system->loadModel('admin/operator');
        $aOp = $o->instance($aDetail['send_op_id'],'username');
        $aDetail['send_op_id'] = $aOp['username'];
        
        $o = $this->system->loadModel('member/member');
        $aMember = $o->getFieldById($aDetail['member_id']);
        $aDetail['member_id'] = $aMember['uname'];
        $this->pagedata['detail']=$aDetail;

        $this->setView('order/refund/detail.html');
        $this->output();
    }

    function edit(){
        $oRefund=$this->system->loadModel('trading/refund');
        if($oRefund->edit($this->in)){
            $this->splash('success','index.php?ctl=order/refund&act=index',__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=order/refund&act=index',__('修改失败'));
        }
        
    }

}
?>
