<?php
class mdl_pointHistory extends modelFactory{
    //type: 1.订单得积分,2.消费积分,3.无分类
    function getHistoryReason() {

        $aHistoryReason = array(
                            'order_pay_use' => array(
                                                    'describe' => __('订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_pay_get' => array(
                                                    'describe' => __('订单获得积分.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund_use' => array(
                                                    'describe' => __('退还订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund_get' => array(
                                                    'describe' => __('扣掉订单所得积分'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_cancel_refund_consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts refunded for order cancelling.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'exchange_coupon' => array(
                                                    'describe' => __('兑换优惠券'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'operator_adjust' => array(
                                                    'describe' => __('管理员改变积分.'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'consume_gift' => array(
                                                    'describe' => __('积分换赠品.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_mall_orders',
                                                )
            );
                            
//------------------------------------------------------------------------------------------------


/*
                            'consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'change_goods' => array(
                                                    'describe' => __('Score deduction for consume.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'buy_product' => array(
                                                    'describe' => __('Getting score for shopping.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_pay_consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts with receipt.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_pay_change_goods' => array(
                                                    'describe' => __('Score deduction for consume with receipt.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_pay_buy_product' => array(
                                                    'describe' => __('Getting score for shopping with receipt.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),


                            'order_refund_consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts refunded for refundment.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund_change_goods' => array(
                                                    'describe' => __('Score deduction for consume refunded for refundment.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),

                            'order_refund_buy_product' => array(
                                                    'describe' => __('Getting score for shopping refunded for refundment.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_cancel_refund_consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts refunded for order cancelling.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_cancel_refund_change_goods' => array(
                                                    'describe' => __('Score deduction for consume refunded for order cancelling.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),

                            'register' => array(
                                                    'describe' => __('Getting score for reigistered.'),
                                                    'type' => 0,
                                                    'related_id' => '',
                                                ),
                            'register_basic' => array(
                                                    'describe' => __('Basic Score.'),
                                                    'type' => 0,
                                                    'related_id' => '',
                                                ),
                            'commend' => array(
                                                    'describe' => __('Getting score for comment.'),
                                                    'type' => 0,
                                                    'related_id' => 'sdb_mall_goods',
                                                ),
                            'commend_help' => array(
                                                    'describe' => __('Getting score for comment useful.'),
                                                    'type' => 0,
                                                    'related_id' => 'sdb_mall_comment',
                                                ),
                            'initial_point' => array(
                                                    'describe' => __('Getting score before updating.'),
                                                    'type' => 0,
                                                    'related_id' => '',
                                                ),
                            'operator_adjust' => array(
                                                    'describe' => __('Score changing by administrator.'),
                                                    'type' => 0,
                                                    'related_id' => '',
                                                ),
                            'order_refund_method_adjust' => array(
                                                    'describe' => __('Score deduction for refund method.'),
                                                    'type' => 0,
                                                    'related_id' => '',
                                                ),

                            //+历史遗留
                            'order_bill' => array(
                                                    'describe' => __('Getting score for receipt.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund' => array(
                                                    'describe' => __('Score refunded for refundment.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            //-历史遗留

                        );*/
        return $aHistoryReason;
    }

    function getPointHistory($userId) {
        $userId = intval($userId);
        $sSql = 'SELECT `time`,`reason`,`point` from sdb_point_history where userid='.$userId;
        return $this->db->select($sSql);
    }
    
    function isAddPointHistory($nPoint, $sReason) {
        $nPoint = trim($sReason);
        return (($nPoint > 0 || $sReason == 'order_refund_get')&&$sReason != 'order_refund_use');

    }


    function addHistory($aData) {
        $aHistoryReason = $this->getHistoryReason();
        $aData['time'] = time();
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $aData['describe'] = $aHistoryReason[$aData['reason']]['describe'];
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $rRs = $this->db->query('SELECT * FROM sdb_point_history WHERE 0=1');
        $sSql = $this->db->GetInsertSQL($rRs, $aData);
        $this->db->query($sSql);
    }

    function getGainedPoint($userId) {
        $aPoint = $this->db->select('SELECT SUM(point) AS point FROM sdb_point_history WHERE member_id='.$userId.' AND point>0');
        return intval($aPoint[0]['point']);
    }

    
    function getConsumePoint($userId)
    {
        $aPoint = $this->db->select('SELECT sum(point) AS point FROM sdb_point_history WHERE member_id='.$userId.' AND point<0');
        return intval($aPoint[0]['point']);
    }

    function getOrderHistory(){
        
    }

    function removeHistory(){
    }


    //订单所有已得积分
    function getOrderGainedPoint($userId, $orderId){
        $aPoint = $this->db->select('SELECT sum(point) AS point FROM sdb_point_history 
                                            WHERE offerid='.$this->shopId.' AND userid='.$userId.' AND point>0 AND
                                                
                                            ');
        return intval($aPoint[0]['point']);
    }

    //2---------------------------------
    function getOrderConsumePoint($orderId) {
        $sSql = 'select score_u from sdb_orders where and orderid=\''.$orderId.'\'';
        $aData = $this->db->selectrow($sSql);
        return intval($aData['score_u']);
    }

    function getOrderHistoryConsumePoint($orderId) {
        $sSql = 'select sum(point) as point from sdb_point_history where related_id=\''.$orderId.'\' and type=2';
        $aData = $this->db->selectrow($sSql);
        return intval($aData['point']);
    }
    //1---------------------------------
    function getOrderGetPoint($orderId) {
        $sSql = 'select score_g from sdb_orders where orderid=\''.$orderId.'\'';
        $aData = $this->db->selectrow($sSql);
        return intval($aData['score_g']);

    }

    function getOrderHistoryGetPoint($orderId) {
        $sSql = 'select sum(point) as point from sdb_point_history where offerid='.$this->shopId.' and related_id=\''.$orderId.'\' and type=1';
        $aData = $this->db->selectrow($sSql);
        return intval($aData['point']);
    }
    //---------------------------------


    function getPointHistoryList($userId) {
        $aData = $this->db->select('SELECT time, reason, point FROM sdb_point_history WHERE member_id='.$userId.' ORDER BY time DESC');
        $aHistoryReason = $this->getHistoryReason();
        if ($aData) {
            foreach ($aData as $k => $aItem) {
                $aData[$k]['describe'] = $aHistoryReason[$aItem['reason']]['describe'];
            }
        }
        return $aData;

    }



    //todo 挪到shop/model
    function getFrontPointHistoryList($userId,$nPage){
        $aData = $this->db->select_f('SELECT time, reason, point FROM sdb_point_history WHERE member_id='.$userId.' ORDER BY time DESC',$nPage,PERPAGE);
        $aHistoryReason = $this->getHistoryReason();
        if ($aData['data']) {
            foreach ($aData['data'] as $k => $aItem) {
                $aData['data'][$k]['describe'] = $aHistoryReason[$aItem['reason']]['describe'];
            }
        }
        return $aData;
    }
}
?>