<?php
include_once('shopObject.php');
class mdl_exchangeCoupon extends shopObject{
    var $idColumn = 'cpns_id'; //表示id的列 
    var $textColumn = 'cpns_name';
    var $defaultCols = 'cpns_name,cpns_point';
    var $adminCtl = 'sale/exchangeCoupon';
    var $defaultOrder = array('cpns_id','desc');
    var $tableName = 'sdb_coupons';

    function getColumns(){
        return array(
                        'cpns_id'=>array('label'=>'优惠券方案id','class'=>'span-3'),    /* 优惠券方案id */
                                'cpns_name'=>array('label'=>'优惠券名称','class'=>'span-3'),    /* 优惠券名称 */
                                'cpns_prefix'=>array('label'=>'生成优惠券号码','class'=>'span-3'),    /* 生成优惠券前缀 */
                                'cpns_gen_quantity'=>array('label'=>'优惠券已经生成的总数量(原cpns_use_quantity)','class'=>'span-3'),    /* 优惠券已经生成的总数量(原cpns_use_quantity) */
                                'cpns_key'=>array('label'=>'优惠券生成的key','class'=>'span-3'),    /* 优惠券生成的key */
                                'cpns_status'=>array('label'=>'优惠券方案状态','class'=>'span-3'),    /* 优惠券方案状态 */
                                'cpns_type'=>array('label'=>'优惠券类型','class'=>'span-3','type'=>'cpns_type'),    /* 优惠券类型 0全局 1用户 2外部优惠券 */
                                'cpns_point'=>array('label'=>'兑换优惠券积分','class'=>'span-3'),    /* 兑换优惠券积分 */
                        );
    }

    function getList($cols,$filter,$start=0,$limit=20,&$count,$orderType=null){
        $sql = 'select '.$cols.',c.pmt_id as pmt_id from sdb_coupons as c
                        left join sdb_promotion as p on c.pmt_id=p.pmt_id
                        where '.$this->_filter($filter);
        

        if($orderType)$sql.=' order by '.implode($orderType,' ');
        $count = $this->db->_count($sql);
        return $this->db->selectLimit($sql,$limit,$start);
    }
/*
    function getList($cols,$filter='',$start=0,$limit=20,&$count,$orderType=null){
        $sql = 'SELECT '.$cols.' FROM '.$this->tableName.' WHERE '.$this->_filter($filter);
        if($orderType)$sql.=' order by '.implode($orderType,' ');
        $count = $this->db->_count($sql);
        return $this->db->selectLimit($sql,$limit,$start);
    }
*/

    function _filter($filter) {
        $where=array(1);
        $where[] = 'cpns_type=\'1\'';
        $where[] = 'cpns_point is not null';
        if ($filter['cpns_name']) {
            $where[] = 'cpns_name like\'%'.$filter['cpns_name'].'%\'';
        }

        if (isset($filter['ifvalid'])) {
            if ($filter['ifvalid']===1){
                $curTime = time();
                $where[] = 'cpns_status=\'1\' AND pmt_time_begin <= '.$curTime.' and pmt_time_end >'.$curTime;
            }
        }
        return parent::_filter($filter,'c').' AND cpns_point > 0 AND '.implode($where,' and ');
    }

    function recycle($filter) {
        $arrId = $filter['cpns_id'];
        if ($arrId) {
            $strId = substr($strId,-1)==','?substr($strId,0,-1):$strId;
            $sSql = 'update sdb_coupons set cpns_point=null WHERE cpns_id in ('.implode(',', $arrId).')';
            if ($this->db->exec($sSql)) {
                return true;
            } else {
                $msg = __('数据删除失败！');
                return false;
            }
        }else{            
            $msg = 'no select';
            return false;
        }
    }

    function saveExchange($aData) {
        $aRs = $this->db->query('SELECT * FROM sdb_coupons WHERE cpns_id='.$aData['cpns_id']);
        $sSql = $this->db->getUpdateSql($aRs,$aData);
        return (!$sSql || $this->db->exec($sSql));
    
    }

    function modifier_cpns_type(&$rows){
        $array = array('A类优惠券','B类优惠券');
        foreach($rows as $k => $v){
            $rows[$k] = $array[$v];
        }
    }


}
?>
