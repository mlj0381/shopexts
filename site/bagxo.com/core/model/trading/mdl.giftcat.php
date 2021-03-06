<?php
include_once('shopObject.php');
class mdl_giftcat extends shopObject{
    var $idColumn = 'giftcat_id'; //表示id的列 
    var $textColumn = 'cat';
    var $defaultCols = 'cat,shop_iffb,orderlist';
    var $adminCtl = 'sale/giftcat';
    var $defaultOrder = array('orderlist','desc');
    var $tableName = 'sdb_gift_cat';

    function getColumns(){
        return array(
                        'giftcat_id'=>array('label'=>'分类ID','class'=>'span-3'),    /* 赠品分类ID */
                                'cat'=>array('label'=>'分类名称','class'=>'span-5'),    /* 赠品分类名称 */
                                'shop_iffb'=>array('label'=>'发布','class'=>'span-3','type'=>'bool'),    /* 是否发布 */
                                'orderlist'=>array('label'=>'排序','class'=>'span-3'),    /* 排序权值 */
                        );
    }    

    function _filter($filter) {
        $where=array(1);
        if(is_array($filter['giftcat_id'])){
            foreach($filter['gcat'] as $giftcat_id){
                if($giftcat_id!='_ANY_'){
                    $cats[] = $giftcat_id;
                }
                if(count($cats)>0){
                    $where[] = 'giftcat_id in ('.implode($cats,',').')';
                }
            }
        }
        
        if ($filter['cat']) {
            $where[] = 'cat like\'%'.$filter['cat'].'%\'';
        }

        if (isset($filter['shop_iffb'])) {
            if ($filter['shop_iffb']===1) {
                $where[] = 'shop_iffb=\'1\'';
            }
        }

        return parent::_filter($filter).' and '.implode($where,' and ');
    }

    function modifier_bool(&$rows,$options=array()){
        foreach($rows as $i=>$publish){
            $rows[$i] = $publish?'是':'否';
        }
    }

    function searchOptions(){
        return array(
                'cat'=>'赠品分类名称',
            );
    }

    //----------------------------------------------------------

    function getTypeById($catid) {
        $sql = 'SELECT * FROM sdb_gift_cat WHERE giftcat_id='.$catid;
        return $this->db->selectRow($sql);
    }

    function addType($aData) {
        if ($aData['giftcat_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_gift_cat WHERE giftcat_id='.$aData['giftcat_id']);
            $sSql = $this->db->getUpdateSql($aRs,$aData);
            return (!$sSql || $this->db->exec($sSql));
        }else{
            $aRs = $this->db->query('SELECT * FROM sdb_gift_cat WHERE 0');
            $sSql = $this->db->getInsertSql($aRs,$aData);

            if ($this->db->exec($sSql)){
                return $this->db->lastInsertId();
            }else{
                return false;
            }            
        }
    }

    function getInitOrder() {
        $aTemp = $this->db->selectRow('select max(orderlist) as orderlist from sdb_gift_cat');
        return $aTemp['orderlist']+1;
    }


    //+
    function getTypeArr() {
        $aTemp = $this->db->select('SELECT giftcat_id,cat FROM sdb_gift_cat WHERE 1    ORDER BY orderlist desc');
        return $aTemp;
    }
}
?>