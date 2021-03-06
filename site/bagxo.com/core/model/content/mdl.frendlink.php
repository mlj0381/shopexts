<?php
include_once('shopObject.php');
/**
 * mdl_frendlink 
 * 
 * @uses shopObject
 * @package 
 * @version $Id: mdl.frendlink.php 1867 2008-04-23 04:00:24Z hujianxin $
 * @copyright 2003-2007 ShopEx
 * @license Commercial
 */
class mdl_frendlink extends shopObject{

    var $idColumn = 'link_id';
    var $textColumn = 'link_name';
    var $adminCtl = 'content/frendlink';
    var $defaultCols = 'link_name,href,image_url,orderlist';
    var $defaultOrder = array('orderlist','desc');
    var $tableName = 'sdb_link';

    function getColumns(){
        return array(
                        'link_id'=>array('label'=>'友情链接id','class'=>'span-4'),    /* 友情链接id */
                        'link_name'=>array('label'=>'友情链接名称','class'=>'span-5','required'=>1),    /* 友情链接名称 */
                        'href'=>array('label'=>'友情链接地址','class'=>'span-6','required'=>1),    /* 友情链接地址 */
                        'orderlist'=>array('label'=>'排序','class'=>'span-7','required'=>1,'type'=>'number')
        );
    }

    function getFieldById($link_id, $aPara){
        $sqlString = 'SELECT '.implode(',', $aPara).' FROM sdb_link WHERE link_id = '.intval($link_id);
        return $this->db->selectrow($sqlString);
    }
    
    function save($aData,&$msg){
        $storager = $this->system->loadModel('system/storager');
        if($_FILES){
            $aData['image_url'] = $storager->save_upload($_FILES['link_logo'],'link');
            if(!$aData['image_url'])unset($aData['image_url']);
        }

        if($aData['link_id']){
            $rs = $this->db->query("SELECT * FROM " . $this->tableName . " WHERE link_id=" . intval($aData['link_id']));
            $sql = $this->db->getUpdateSql($rs,$aData);
        }else{
            $rs = $this->db->query("SELECT * FROM " . $this->tableName . " WHERE 0=1");
            $sql = $this->db->getInsertSql($rs,$aData);
        }
        
        if($this->db->exec($sql)){
            $msg = "保存成功";
            return true;
        }else{
            $msg = "保存失败";
            return false;
        }
    }
}