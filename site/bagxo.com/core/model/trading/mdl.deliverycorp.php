<?php
require_once('shopObject.php');

class mdl_deliverycorp extends shopObject{
    var $idColumn = 'corp_id'; //表示id的列 
    var $textColumn = 'corp_id';
    var $defaultCols = 'name,website,ordernum';
    var $adminCtl = 'trading/deliverycorp';
    var $defaultOrder = array('ordernum','desc');
    var $tableName = 'sdb_dly_corp';

    function getColumns(){
        return array(
            'corp_id'=>array('label'=>'物流公司ID','class'=>'span-3'),
            'name'=>array('label'=>'物流公司','class'=>'span-5'),
            'website'=>array('label'=>'网址','class'=>'span-5'),
            'ordernum'=>array('label'=>'排序','class'=>'span-5'),
        );
    }
    function getCorpList(){
        $sql="select corp_id,name from sdb_dly_corp where disabled='false' order by ordernum desc";
        return $this->db->select($sql);
    }
}
?>