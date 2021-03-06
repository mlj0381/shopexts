<?php
/********************************************************************************************
退货管理

COOKIE Array
[goods][cart][gid-pid-adj]=num
[goods][pmt][gid] = pmt
@g.gid-pid-adj-num,gid-pid-adj-num;gid-pmtid,gid-pmtid@f.@p.@c.
//说明:@g.(购物车内容)gid-pid-adj-num,gid-pid-adj-num;(PMT内容)gid-pmtid,gid-pmtid
//adj = 配件id_配件组_配件数量|
['gift']
g.gift_id-na-num_pmtid

['c'] pmt_id type:o订单/g商品

**********************************************************************************************/

include_once('shopObject.php');
class mdl_return_product extends shopObject{
    
    var $idColumn = 'return_id';
    var $textColumn = 'title';
    var $tableName = 'sdb_return_product';
    var $defaultCols = 'order_id,title,member_id,status,add_time';
    var $defaultOrder = array('return_id',' DESC',',add_time',' DESC');
    
    function searchOptions(){
        return array(
                'order_id'=>'订单号',
                'member_name'=>'申请人'
            );
    }

    function getColumns(){
        return array(
            'return_id'=>array('label'=>'ID','class'=>'span-4'),
            'order_id'=>array('label'=>'订单号','class'=>'span-3'),
            'member_id'=>array('label'=>'申请人','class'=>'span-3','type'=>'object:member'),
            'title'=>array('label'=>'售后服务标题','class'=>'span-8','type'=>'ship_name','fuzzySearch'=>1),
            'status'=>array('label'=>'处理状态','class'=>'span-2','type'=>'ship_status'),
            'add_time'=>array('label'=>'售后处理时间','class'=>'span-3','type'=>'time:FDATE_STIME')
        );
    }
    function _filter($filter){
        $where=array(1);
        if($filter['no_handle']){
            $where[]=' (status!=4 and  status!=5) ';
        }
        if(isset($filter['member_name']) && $filter['member_name'] !== ''){
            $aId = array(0);
            foreach($this->db->select('SELECT member_id FROM sdb_members WHERE uname = \''.$filter['member_name'].'\'') as $rows){
                $aId[] = $rows['member_id'];
            }
            $where[] = 'member_id IN ('.implode(',', $aId).')';
            unset($filter['member_name']);
        }
        return parent::_filter($filter).' and '.implode($where,' AND ');
    }
    
    function modifier_ship_status(&$rows){
        foreach($rows as $k=>$v){
            $rows[$k] = $this->get_status($v);
        }
    }
    
    function get_status($status){
        switch($status){
                case 1:
                    $status = "申请中";
                    break;
                case 2:
                    $status = "审核中";
                    break;
                case 3:
                    $status = "接受申请";
                    break;
                case 4:
                    $status = "完成";
                    break;
                case 5:
                    $status = "拒绝";
                    break;
            }
        return $status;
    }
    function count_return_product(){
        $result=$this->db->selectrow('SELECT count(*) as counts from sdb_return_product where disabled="false" and status!="4" and status!="5" ');
        return $result['counts'];
    }
    function load($return_id){
        if($row = $this->db->selectrow('SELECT * from sdb_return_product where return_id ='.$return_id)){
            $this->_info["return_id"] = $row["return_id"];
            $this->_info["order_id"] = $row["order_id"];
            $this->_info["member_id"] = $row["member_id"];
            $this->_info["order_id"] = $row["order_id"];
            $this->_info["title"] = $row["title"];
            $this->_info["status"] = $this->get_status($row["status"]);
            $this->_info["status_int"] = $row["status"];
            $this->_info["content"] = $row["content"];
            $this->_info["add_time"] = $row["add_time"];
            $this->_info["disabled"] = $row["disabled"];
            if( $row["image_file"] ){
                $this->_info["image_file"] = $row["image_file"];
            }
            if( $row["product_data"] ){
                $this->_info["product_data"] = unserialize($row["product_data"]);
            }
            if( $row["comment"] ){
                $this->_info["comment"] = unserialize($row["comment"]);
            }
            return $this->_info;
        }else{
            return false;
        }
    }
        
    function change_status($return_id,$status){
        $data = array(
                      'status' => $status
                      );
        $filter = array(
                        'return_id' => $return_id
                        );
        
        if($this->update($data,$filter)){
            return $this->get_status($status);
        }
    }
    
    function modifier_ship_name(&$rows){
        foreach($rows as $k => $v){
            $rows[$k] = htmlspecialchars($rows[$k],ENT_QUOTES);
        }
    }

    function send_comment($return_id,$comment){
        $info = $this->load($return_id);
        $old_comment = $info['comment'];
        
        $new_comment = array(
            array(
                'time' => time(),
                'content' => $comment
            )
        );
        
        if(is_array($old_comment)){
            $new_comment = array_merge($new_comment,$old_comment);
        }
        
        $data = array(
            'comment' => serialize($new_comment)
        );
        $filter = array(
            'return_id' => $return_id
        );
        
        return $this->update($data,$filter);
    }
    
    function save($aData){
        $rs = $this->db->query('select * from sdb_return_product where 0=1');
        $sqlString = $this->db->GetInsertSQL($rs, $aData);
        if($this->db->exec($sqlString)){
            return true;
        }
        return false;
    }
    
    function file_download($filename){
        $file = fopen($filename,"r");
        Header("Content-type: application/octet-stream");   
        Header("Accept-Ranges: bytes");   
        Header("Accept-Length: ".filesize($filename));   
        Header("Content-Disposition: attachment; filename=".basename($filename));   
        echo fread($file,filesize($filename));   
        fclose($file); 
    }
}
?>