<?php
/**
 * mdl_finderPdt 
 * 
 * @uses modelFactory
 * @package 
 * @version $Id: mdl.finderPdt.php 1974 2008-04-28 03:07:16Z ever $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com> 
 * @license Commercial
 */
include_once('shopObject.php');
class mdl_finderPdt extends shopObject{
    var $idColumn = 'product_id';
    var    $adminCtl = 'goods/items';
    var $textColumn = 'name';
    var $appendCols = 'pdt_desc';
    var $defaultCols = 'bn,name,price,store,pdt_desc';
    var $defaultOrder = array('uptime','DESC');
    var $tableName = 'sdb_products';

    function getColumns(){
        return array(
                        'product_id'=>array('label'=>'货品ID','class'=>'span-3'),    /* 货品ID */
                        'goods_id'=>array('label'=>'商品ID','class'=>'span-3'),    /* 商品ID */
                        'barcode'=>array('label'=>'条码','class'=>'span-3'),    /* 条码 */
                        'title'=>array('label'=>'','class'=>'span-3'),    /*  */
                        'bn'=>array('label'=>'货号','class'=>'span-2','fuzzySearch'=>1),    /* 货号 */
                        'price'=>array('label'=>'销售价格','class'=>'span-2'),    /* 销售价格 */
                        'cost'=>array('label'=>'成本价','class'=>'span-3'),    /* 成本价 */
                        'name'=>array('label'=>'商品名称','class'=>'span-5','fuzzySearch'=>1),    /* 货品名 */
                        'weight'=>array('label'=>'单位重量','class'=>'span-3'),    /* 单位重量 */
                        'unit'=>array('label'=>'单位','class'=>'span-3'),    /* 单位 */
                        'store'=>array('label'=>'库存','class'=>'span-1'),    /* 库存 */
                        'freez'=>array('label'=>'冻结库存','class'=>'span-3'),    /* 冻结库存 */
                        'pdt_desc'=>array('label'=>'物品描述','class'=>'span-3'),    /* 规格描述,如:
                                                                        红色 27码 */
                        'props'=>array('label'=>'规格值,序列化','class'=>'span-3'),    /* 规格值,序列化
                                                                        array('spec'=>array('属性(颜色)序号'=>'属性值(红色)'))
                                                                         */
                        'uptime'=>array('label'=>'录入时间','class'=>'span-3'),    /* 录入时间 */
                        'last_modify'=>array('label'=>'最后修改时间','class'=>'span-3'),    /* 最后修改时间 */
                );
    }
    
    function searchOptions(){
        return array(
            'bn'=>'货号',
            'name'=>'商品名称',
        );
    }    
    
    function _filter($filter){
        $where = array(1);
        $aId = array(0);
        $aBrand = array();
        if(isset($filter['brand_id']) && is_array($filter['brand_id'])){
            foreach($filter['brand_id'] as $brand_id){
                if($brand_id!='_ANY_'){
                    $aBrand[] = intval($brand_id);
                }
            }
            if(count($aBrand)>0){
                foreach($this->db->select('SELECT goods_id FROM sdb_goods WHERE marketable = \'true\' AND brand_id IN('.implode(',', $aBrand).')') as $rows){
                    $aId[] = $rows['goods_id'];
                }
            }
            unset($filter['brand_id']);
        }

        $aTag = array();
        if(isset($filter['tag']) && is_array($filter['tag'])){
            foreach($filter['tag'] as $tag){
                if($tag!='_ANY_'){
                    $aTag[] = intval($tag);
                }
            }
            if(count($aTag)>0){
                $tagId = array(0);
                foreach($this->db->select('SELECT rel_id FROM sdb_tag_rel r LEFT JOIN sdb_tags t ON r.tag_id=t.tag_id WHERE t.tag_type = \'goods\' AND r.tag_id IN('.implode(',', $aTag).')') as $rows){
                    $tagId[] = $rows['rel_id'];
                }
                if(count($aBrand)>0){
                    $aId = array_intersect($aId, $tagId);
                }else{
                    $aId = $tagId;
                }
            }
            unset($filter['tag']);
        }
        if(count($aTag) || count($aBrand)){
            $where[] = 'goods_id IN ('.implode(',', $aId).')';
        }
        
        if(isset($filter['product_id']) && is_array($filter['product_id'])){
            foreach($filter['product_id'] as $goods_id){
                if($goods_id!='_ANY_'){
                    $goods[] = intval($goods_id);
                }
            }
            if(count($goods)>0){
                $where[] = 'product_id IN ('.implode(',', $goods).')';
            }
        }
        if(isset($filter['notifytime']) && is_array($filter['notifytime'])){
                $where[] = 'creat_time > '.$filter['notifytime'];
        }
        if(isset($filter['price']) && is_array($filter['price'])){
            foreach($filter['price'] as $price){
                if($price != '_ANY_'){
                    $aPrice = explode('-', $price);
                    $aWhere[] = '(price >= '.$aPrice[0].' AND price <= '.$aPrice[1].')';
                }
            }
            if(!empty($aWhere)) $where[] = '('.implode(' OR ', $aWhere).')';
            unset($filter['price']);
        }
        return parent::_filter($filter).' AND goods_id IS NOT NULL AND '.implode(' AND ', $where).(isset($filter['store_alarm']) ? ' AND store <='.intval($filter['store_alarm']) : '');
    }
    
    function getFilter($p){
        $row = $this->db->selectrow('SELECT max(price) as max,min(price) as min FROM sdb_products ');
        $brand = $this->system->loadModel('goods/brand');
        $return['brands'] = $brand->getAll();

        $modTag = $this->system->loadModel('system/tag');
        $return['tags'] = $modTag->tagList('goods');
        $return['prices'] = steprange($row['min'],$row['max'],5);

        return $return;
    }
}
?>