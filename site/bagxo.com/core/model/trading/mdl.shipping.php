<?php
require_once(dirname(__FILE__).'/mdl.delivery.php');
class mdl_shipping extends mdl_delivery{
    //START
    var $idColumn='delivery_id';
    var $adminCtl='order/shipping';
    var $textColumn = 'delivery_id';
    var $defaultCols = 'delivery_id,order_id,t_begin,member_id,money,is_protect,ship_name,delivery,logi_name,logi_no';
    var $defaultOrder = array('t_begin','DESC');
    var $tableName = 'sdb_delivery';

    function getColumns(){
        return array(
                        'delivery_id'=>array('label'=>'发货单号','class'=>'span-3'),    /* 配送流水号 */
                        'order_id'=>array('label'=>'订单号','class'=>'span-3'),    /* 订单号 */
                        'member_id'=>array('label'=>'用户名','class'=>'span-2','type'=>'object:member'),    /* 会员id */
                        'money'=>array('label'=>'物流费用','class'=>'span-2','type'=>'money'),    /* 配送费用 */
                        'type'=>array('label'=>'配送类型','class'=>'span-2'),    /* 配送类型 */
                        'is_protect'=>array('label'=>'是否保价','class'=>'span-2','type'=>'is_protect'),    /* 是否保价 */
                        'delivery'=>array('label'=>'配送方式','class'=>'span-3'),    /* 配送方式(货到付款、EMS...) */
                        //'logi_id'=>array('label'=>'物流公司id','class'=>'span-3'),    /* 物流公司id */
                        'logi_name'=>array('label'=>'物流公司','class'=>'span-3'),    /* 物流公司 */
                        'logi_no'=>array('label'=>'物流单号','class'=>'span-3'),    /* 物流单号 */
                        'ship_name'=>array('label'=>'收货人','class'=>'span-3'),    /* 收货人姓名 */
                        'ship_area'=>array('label'=>'收货人地区','class'=>'span-3','type'=>'region'),    /* 收货人地区 */
                        'ship_addr'=>array('label'=>'收货人地址','class'=>'span-3'),    /* 收货人地址 */
                        'ship_zip'=>array('label'=>'收货人邮编','class'=>'span-3'),    /* 收货人邮编 */
                        'ship_tel'=>array('label'=>'收货人电话','class'=>'span-3'),    /* 收货人电话 */
                        'ship_mobile'=>array('label'=>'收货人手机','class'=>'span-3'),    /* 收货人手机 */
                        'ship_email'=>array('label'=>'收货人Email','class'=>'span-3'),    /* 收货人Email */
                        't_begin'=>array('label'=>'单据创建时间','class'=>'span-3','type'=>'time'),    /* 单据生成时间 */
                        //'t_end'=>array('label'=>'单据结束时间','class'=>'span-3','type'=>'time'),    /* 单据结束时间 */
                        'op_name'=>array('label'=>'管理员','class'=>'span-3'),    /* 操作者 */
                        //'status'=>array('label'=>'succ 配送成功','class'=>'span-3'),    /* succ 配送成功 failed 支付失败 cancel 未支付 error 参数异常 progress 处理中 timeout 超时 ready 准备中 */
                        'memo'=>array('label'=>'备注','class'=>'span-3'),    /* 备注 */
                );
    }

    function modifier_is_protect(&$rows){
        $status = array('true'=>'是',
                    'false'=>'否' );
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }

    function searchOptions(){
        return array(
                'logi_no'=>'物流单号',
                'order_id'=>'订单号'
            );
    }

    function getFilter($p){
        //$return['payment']=array_merge(array(array('id'=>0,'custom_name'=>__('线下支付'))),$this->getMethods());
        //$aDelivery=$this->getPlugins();
        return $return;
    }

    function _filter($filter){
        $filter['type'] = 'delivery';
        return parent::_filter($filter);
    }

    //按物理单号查询订单号
    function getOrdersByLogino($logino){
        $aRet = $this->db->select("SELECT order_id FROM sdb_delivery WHERE logi_no like '{$logino}%'");
        $aOrder = array();
        foreach($aRet as $row){
            $aOrder[] = $row['order_id'];
        }
        return $aOrder;
    }

    function getPlugins(){
        $dir = PLUGIN_DIR.'/shipping/';
        if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if(is_file($dir.'/'.$file) && substr($file,0,5)=='ship.' ){
                        include_once($dir.'/'.$file);
                        $payName = substr($file,5,-4);
                        $class_name='ship_'.$payName;
                        $o = new $class_name;
                        $return[$payName] = get_object_vars($o);
                    }
                }
                closedir($handle);
        }
        return $return;
    }

    function toRemove($id){
        $sqlString = "DELETE FROM sdb_delivery WHERE delivery_id='".$id."'";
        $this->db->exec($sqlString);

        $sqlString = "DELETE FROM sdb_delivery_item WHERE delivery_id='".$id."'";
        $this->db->exec($sqlString);
    }
}