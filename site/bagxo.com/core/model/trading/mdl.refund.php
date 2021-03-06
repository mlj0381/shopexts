<?php

require_once('shopObject.php');

class mdl_refund extends shopObject{

//START
    var $adminCtl='order/refund';
    var $idColumn='refund_id';
    var $textColumn = 'refund_id';
    var $defaultCols = 'refund_id,money,currency,order_id,paymethod,member_id,account,bank,pay_account,status';
    var $defaultOrder = array('refund_id','desc');
    var $tableName = 'sdb_refunds';

    function getColumns(){
        return array(
                        'refund_id'=>array('label'=>'退款单号','class'=>'span-3'),    /* 汇款单id */
                                'order_id'=>array('label'=>'订单号','class'=>'span-3'),    /* 订单id */
                                'member_id'=>array('label'=>'会员用户名','class'=>'span-2','type'=>'object:member'),    /* 会员id */
                                'account'=>array('label'=>'退款帐号','class'=>'span-3'),    /* 银行帐号 */
                                'bank'=>array('label'=>'退款银行','class'=>'span-3'),    /* 退款银行 */
                                'pay_account'=>array('label'=>'收款人','class'=>'span-2'),    /* 收款人 */
                                'currency'=>array('label'=>'货币','class'=>'span-2','type'=>'object:currency'),    /* 货币 */
                                'money'=>array('label'=>'金额','class'=>'span-2','type'=>'money'),    /* 金额 */
                                'pay_type'=>array('label'=>'退款方式','class'=>'span-3','type'=>'ptype'),    /* 在线/线下/预存款 */
                                //'payment'=>array('label'=>'支付方式id','class'=>'span-3'),    /* 支付方式id */
                                'paymethod'=>array('label'=>'支付方式','class'=>'span-3'),    /* 支付方式名称冗余 */
                                'send_op_id'=>array('label'=>'操作员','class'=>'span-3','type'=>'object:operator'),    /* 创建的操作者 */
                                //'ip'=>array('label'=>'','class'=>'span-3'),    /*  */
                                't_ready'=>array('label'=>'单据创建时间','class'=>'span-3','type'=>'time'),    /* 汇款单创建时间 */
                                't_sent'=>array('label'=>'退款时间','class'=>'span-3','type'=>'time'),    /* 发款时间 */
                                //'t_received'=>array('label'=>'用户确认收款时间','class'=>'span-3'),    /* 用户确认收款时间 */
                                'status'=>array('label'=>'状态','class'=>'span-2','type'=>'status'),    /* 状态 ready: 钱在商店，progress:钱正在付款，sent:钱已经发送，received:用户确认收到款 */
                                //'title'=>array('label'=>'主题','class'=>'span-4'),    /* 主题 */
                        );
    }

    function modifier_status(&$rows){
        $type = array(
                    'ready'=>'准备中',
                    'progress'=>'正在退款',
                    'sent'=>'款项已退',
                    'received'=>'用户收到退款',
                    );
        foreach($rows as $k=>$v){
            $rows[$k] = $type[$v];
        }
    }
    
    function modifier_ptype(&$rows){
        $status = array('online'=>'在线支付',
                    'offline'=>'线下支付',
                    'deposit'=>'预存款支付',
                    'recharge'=>'预存款充值',
                    );
        foreach($rows as $k=>$v){
            $rows[$k] = $status[$v];
        }
    }

    function getFilter($p){
        $oPayment = $this->system->loadModel('trading/payment');
        $return['payment']=$oPayment->getMethods();
        return $return;
    }
    function detail($nRefundID){
        return $this->db->selectrow('select * from sdb_refunds where refund_id='.$nRefundID);
    }
    function edit($aDetail){
        $rRefund=$this->db->query('select * from sdb_refunds where refund_id='.$aDetail['refund_id']);
        unset($aDetail['refund_id']);
        $sSQL=$this->db->GetUpdateSQL($rRefund,$aDetail);
        if (!$sSQL || $this->db->exec($sSQL)) {
            return true;
        } else {
            return false;
        }
    }
    
    function getOrderBillList($orderid){
        return $this->db->select('SELECT * FROM sdb_refunds WHERE order_id = '.$orderid);
    }
//END

    function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $refund_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select refund_id from sdb_refunds where refund_id =\''.$refund_id.'\'');
        }while($row);
        return $refund_id;
    }

    function create($data){
        $data['refund_id'] = $this->gen_id();
        $data['t_ready'] = time();
        $data['t_sent'] = time();
        $data['ip'] = remote_addr();
        if($data['pay_type'] == 'deposit'){    //todo预存款
//            $this->money=$money;
        }
        
        if($payCfg = $this->db->selectrow('SELECT pay_type,fee,custom_name FROM sdb_payment_cfg WHERE id='.intval($data['payment']))){
            $data['paycost'] = $payCfg['fee'] * $data['money'];
            //$this->bank = $payCfg['pay_type'];
            $data['paymethod'] = $payCfg['custom_name']; 
        }
        
        $rs = $this->db->query('select * from sdb_refunds where 0=1');
        $sql = $this->db->getInsertSQL($rs,$data);
        if($this->db->exec($sql)){
            return $data['refund_id'];
        }else{
            return false;
        }
    }
    
    function setRefundStatus($refundId,$status){
        $aTemp = array();
        $aTemp['t_end'] = time();
        switch($status){
            case PAY_FAILED:
                $aTemp['status'] = 'failed';    //支付网关传回的状态为支付失败状态
                break;
            case PAY_TIMEOUT:
                $aTemp['status'] = 'timeout';    //
                break;
            case 'succ':
                $aTemp['status'] = 'succ';        //支付网关返回支付成功标识
                break;
        }

        $aRs = $this->db->query('SELECT * FROM sdb_refunds WHERE refund_id=\''.$refundId.'\'');
//todo:暂时屏蔽掉预存款,需要验证是否有足够的预存款(先锁定,应该在单据成功后扣除)
        if(!$aRs || !($rows = $this->db->getRows($aRs))){
            echo '错误:退款记录不存在或已过期!';
            exit();
            //todo 交易记录不存在
            return false;
        }

        $sSql = $this->db->GetUpdateSql($aRs,$aTemp);
        return (!$sSql || $this->db->exec($sSql));
    }
}
?>