<?php
include_once('shopObject.php');
class mdl_couponGenerate extends shopObject{
    var $actionView = 'sale/coupon/generate/finder_action.html'; //默认的动作html模板,可以为null
    var $idColumn = 'cpns_id'; //表示id的列 
    var $textColumn = 'pmt_name';
    var $defaultCols = 'pmt_describe';
    var $defaultOrder = array('pmt_update_time','desc');
    var $tableName = 'sdb_pmt_gen_coupon';

    function getColumns(){
        return array(
                        'cpns_id'=>array('label'=>'促销ID','class'=>'span-3'),    /* 促销ID */
                        'pmt_describe'=>array('label'=>'优惠券发放途径'),    /* 规则描述 */
                        );
    }

    function getList($cols,$filter,$start=0,$limit=20,&$count,$orderType=null){
        $sql = 'SELECT '.$cols.' FROM sdb_pmt_gen_coupon
                        LEFT JOIN sdb_promotion ON sdb_pmt_gen_coupon.pmt_id = sdb_promotion.pmt_id
                        WHERE '.$this->_filter($filter);

        if($orderType)$sql.=' order by '.implode($orderType,' ');
        $count = $this->db->_count($sql);
        return $this->db->selectLimit($sql,$limit,$start);
    }

    function _filter($filter){
        $where=array(1);
        if ($filter['cpns_id']) {
            $where[] = 'sdb_pmt_gen_coupon.cpns_id='.intval($filter['cpns_id']);
        }
        return parent::_filter($filter).' AND '.implode($where,' AND ');
    }

    function searchOptions(){
        return array();
    }

    function getFilter() {
      return;
    }
}
?>