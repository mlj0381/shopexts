<?php 
include_once('shopObject.php');
/**
 * mdl_specification 
 * 
 * @uses modelFactory
 * @package 
 * @version $Id: mdl.specification.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Liujy <ever@zovatech.com> 
 * @license Commercial
 */

class mdl_specification extends shopObject{

    var $idColumn = 'spec_id';
    var $textColumn = 'spec_name';
    var $adminCtl = 'goods/specification';
    var $defaultCols = 'spec_name,spec_type,spec_show_type,spec_value';
    var $defaultOrder = array('spec_id','desc');
    var $tableName = 'sdb_specification';
    var $appendCols = 'spec_memo';
    
    function getColumns(){
        return array(
                    'spec_id'=>array('label'=>'规格id','class'=>'span-4'),    /* 规格id */
                    'spec_name'=>array('label'=>'规格名称','class'=>'span-5','required'=>1,'type'=>'spec_name','modifier'=>'row'),    /* 规格名称 */
                    'spec_memo'=>array('label'=>'规格备注','class'=>'span-9'),   /* 规格备注 */
                    'spec_type'=>array('label'=>'类型','class'=>'span-2','type'=>'spec_type'),  /* 规格类型 */
                    'spec_show_type'=>array('label'=>'显示方式','class'=>'span-2','type'=>'spec_show_type'),  /* 规格显示类型 */
                    'spec_value'=>array('label'=>'规格值','class'=>'span-9','type'=>'spec_value','sql'=>'spec_id')
        );
    }

    function modifier_spec_value(&$rows){
         foreach ($rows as $key => $va){
             $tmpvalue = $this->getValueList($va);
             if(count($tmpvalue)==0){
                 $rows[$key] = '无';
             }else{
                 foreach($tmpvalue as $k => $v){
                     $value[] = $tmpvalue[$k]['spec_value'];
                     $spec_value = implode(',',$value);
                     $rows[$key] = $spec_value;
                 }
             }
             unset($value);
         }     
    }

    function modifier_spec_name($row){
        $memo = "";
        if($row['spec_memo']!=""){
            $memo = "[".$row['spec_memo']."]";
        }
        return "<strong>".$row['spec_name']."</strong>&nbsp;".$memo;
    }

    function modifier_spec_type(&$rows){

        foreach($rows as $k=>$v){
            $rows[$k] = '图片';
            
            if($v=='text'){
                $rows[$k] = '文字';
            }
        }
    }
    function modifier_spec_show_type(&$rows){
        
        foreach($rows as $k=>$v){
            $rows[$k] = '下拉';
            if($v=='flat'){
                $rows[$k] = '平铺';
            }
        }    
    }

    function getArrayById($aId=array()){
        if($aId){
            $sqlWhere = ' WHERE spec_id IN('.implode(',', $aId).')';
        }else{
            $sqlWhere = '';
        }
        $aData = $this->db->select('SELECT * FROM sdb_specification'.$sqlWhere);
        foreach($aId as $k => $id){
            foreach($aData as $aRow){   //按照原数组顺序
                if($id == $aRow['spec_id']) $aTmp[$aRow['spec_id']] = $aRow['spec_name'];
            }
        }
        return $aTmp;
    }

    function getListByIdArray($aId = array()){
        $sql = 'SELECT * FROM sdb_specification WHERE disabled = "false" ';
        if($aId){
            $sql .= ' AND spec_id IN('.implode(',', $aId).')';
        }else{
            $sql .= '';
        }
        $rs = array_flip( $aId );
        foreach( $this->db->select($sql) as $v )
            $rs[$v['spec_id']] = $v;
        return $rs;
    }

    function getFieldById($spec_id, $aPara){
        $sqlString = 'SELECT '.implode(',', $aPara).' FROM sdb_specification WHERE disabled = "false" AND spec_id = '.intval($spec_id);
        return $this->db->selectrow($sqlString);
    }
    
    function getValueById($valid, $aPara){
        $sqlString = 'SELECT '.implode(',', $aPara).' FROM sdb_spec_values WHERE spec_value_id = '.intval($valid);
        return $this->db->selectrow($sqlString);
    }
    
    function getValueList($spec_id){
        $sqlString = "SELECT * FROM sdb_spec_values WHERE spec_id = ".intval($spec_id)." ORDER BY p_order ASC";
        $list = $this->db->select($sqlString);
        $rs = array();
        foreach( $list as $k => $v )
            $rs[$v['spec_value_id']] = $v;
        return $rs;
    }
    
    function getValueListByName($name){
        $sqlString = "SELECT v.* FROM sdb_spec_values v LEFT JOIN sdb_specification s ON v.spec_id = s.spec_id
                        WHERE spec_name = '".$name."'";
        $aData = $this->db->select($sqlString);
        return $aData;
    }
    
    function getSpecidListByName($data){
        $sqlString = "SELECT spec_id,spec_name FROM sdb_specification WHERE spec_name IN ('".implode('\',\'',$data)."')";
        $aData = $this->db->select($sqlString);
        return $aData;
    }

    function getListByTypeId($typeid) {
        return $this->db->select('SELECT s.* FROM sdb_goods_type_spec ts INNER JOIN sdb_specification s ON ts.spec_id = s.spec_id WHERE s.disabled = "false" AND ts.type_id = '.$typeid);
    }
    
    function getValueidByName($spec_id, $name){
        $sqlString = 'SELECT spec_value_id FROM sdb_spec_values Where spec_id = '.$spec_id.' AND spec_value =\''.$name.'\'';
        $aData = $this->db->selectrow($sqlString);
        return $aData['spec_value_id'];
    }
    
    function getSpecList($filter=array()){
        $where = array(1);
        if($filter['spec_id']){
            $where[] = 's.spec_id IN ('.implode(',', $filter['spec_id']).')';
        }
        $sqlString = "SELECT * FROM sdb_specification s LEFT JOIN sdb_spec_values v ON s.spec_id = v.spec_id
                        WHERE ".implode(' AND ', $where).' ORDER BY v.p_order';
        $aData = $this->db->select($sqlString);
        $aTmp = array();
        foreach($aData as $item){
            $aTmp[$item['spec_id']]['spec_name'] = $item['spec_name'];
            $aTmp[$item['spec_id']]['spec_memo'] = $item['spec_memo'];
            $aTmp[$item['spec_id']]['options'][$item['spec_value_id']] = $item['spec_value'];
        }
        return $aTmp;
    }
    
    function getSpecGoodsList(&$aSpec, $filter=array()){
        foreach($aSpec as $spec_id => $spec_item){
            foreach($spec_item['options'] as $id => $val){
                $where[$spec_id.'-'.$id] = '(spec_id = '.$spec_id.' AND spec_value_id = '.$id.')';
            }
        }
        
        $sqlString = "SELECT * FROM sdb_goods_spec_index WHERE ("
                .implode(' OR ', $where).') AND goods_id IN('.implode(',', $filter['goods_id']).')';
        $aData = $this->db->select($sqlString);
        foreach($aData as $k => $item){
            $ret[$item['spec_id'].'-'.$item['spec_value_id']][$item['goods_id']] = 1;
        }
        foreach($ret as $k => $row){
            $aTmp = explode('-', $k);
            $aRet[$aTmp[0]]['spec_name'] = $aSpec[$aTmp[0]]['spec_name'];
            $aRet[$aTmp[0]]['spec_memo'] = $aSpec[$aTmp[0]]['spec_memo'];
            $aRet[$aTmp[0]]['options'][$aTmp[1]] = $aSpec[$aTmp[0]]['options'][$aTmp[1]]."<span class='num'>(".count($row).")</span>";
        }
        return $aRet;
    }

    function toSave($aData){
        $aSpec['spec_id'] = $aData['spec_id'];
        $aSpec['spec_name'] = trim($aData['spec_name']);
        $aSpec['spec_memo'] = trim($aData['spec_memo']);
        $aSpec['spec_show_type'] = $aData['spec_show_type'];
        $aSpec['spec_type'] = $aData['spec_type'];
        if($aSpec['spec_name']==""){
             trigger_error(__('规格名称不能为空'),E_USER_ERROR);
             return false;
        }
        foreach($aData['spec_value'] as $k => $v){
            if($v==""){
                trigger_error(__('规格值名称不能为空'),E_USER_ERROR);
                return false;
                exit;
            }
        }
        if($aSpec['spec_id']){
            $tdata = $this->getFieldById($aSpec['spec_id'],array('spec_type'));
            //判断是否做过类型切换
            if($tdata['spec_type'] !=$aSpec['spec_type']){
                $this->db->exec("DELETE FROM sdb_spec_values WHERE spec_id =".intval($aData['spec_id']));
            }
            $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE spec_id=".intval($aData['spec_id']));
            $sSql = $this->db->getUpdateSql($aRs,$aSpec);
            if($sSql && !$this->db->exec($sSql)){
                trigger_error(__('保存规格数据库出错'),E_USER_ERROR);
                return false;
            }
        }else{
            $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE 0=1");
            $sSql = $this->db->getInsertSql($aRs,$aSpec);
            if($sSql && !$this->db->exec($sSql)){
                trigger_error(__('保存规格数据库出错'),E_USER_ERROR);
                return false;
            }else{
                $aSpec['spec_id'] = $this->db->lastInsertId();
            }
        }
        return $this->saveValue($aSpec['spec_id'],$aData['spec_value'],$aData['val'],$aData['spec_image']);
    }
    
    function saveValue($spec_id,$aVal,$spec_value_id,$spec_image){   
        if($spec_id==""){
            return true;
        }
        foreach($aVal as $id => $v){
            $data['spec_id'] = $spec_id;
            $data['spec_value'] = $aVal[$id];
            $data['p_order'] = $id;
            $data['spec_image'] = $spec_image[$id]?$spec_image[$id]:"";
            if($spec_value_id[$id]!=""){    
                $aRs = $this->db->query("SELECT * FROM sdb_spec_values WHERE spec_value_id=".$spec_value_id[$id]);
                $data['spec_value_id'] = $spec_value_id[$id];
                $sSql = $this->db->getUpdateSql($aRs,$data,true);
                unset($data['spec_value_id']);
                if($sSql){
                $this->db->exec($sSql);
                $aId[] = $spec_value_id[$id];
                }
            }else{
                $aRs = $this->db->query("SELECT * FROM sdb_spec_values WHERE 0=1");
                $sSql = $this->db->getInsertSql($aRs,$data);
                if($sSql){
                    $this->db->exec($sSql);
                    $aId[] = $this->db->lastInsertId();
                }
            }
        }
        if(count($aId)==0){
            if(count($spec_value_id)==0){
                $this->db->exec('DELETE FROM sdb_spec_values WHERE spec_id='.intval($spec_id).'');
            }else{
                $aId = $spec_value_id;
            };
        }
        if($aId[0]!=0){
            $aId = array_merge($aId,$spec_value_id);
            $filter = array_filter($aId);
            $this->db->exec('DELETE FROM sdb_spec_values WHERE spec_id='.intval($spec_id).' AND spec_value_id NOT IN('.implode(',',$filter).')');
        }
        return true;
    }
    
    function delete($filter){
        if(!$this->toSelectForType($filter['spec_id'])){
            echo '该规格已被现有商品类型使用,不能删除,请先取消类型绑定';
            exit;
        };
        if(!$this->toSelectForGoods($filter['spec_id'])){
            echo '该规格已被现有商品使用,不能删除,请先取消相关商品规格';
            exit;
        };
        $this->removeSpecValue($filter['spec_id']);
        return true;    
    }

    function removeSpecValue($specid){
        $sql1 = "DELETE FROM sdb_spec_values WHERE spec_id IN (".implode(',',$specid).")";
        $sql2 = "DELETE FROM sdb_specification WHERE spec_id IN (".implode(',',$specid).")";
        $this->db->exec($sql1);
        $this->db->exec($sql2);
    }

    function toSelectForType($spec_id){
        $sqlString = "SELECT spec_id FROM sdb_goods_type_spec WHERE spec_id IN (".implode(',',$spec_id).")";
        $tmpdata = $this->db->select($sqlString);
        if(count($tmpdata)!=0){
            return false;
        }else{
            return true;
        }
       
    }
    function toSelectForGoods($spec_id){
        $sqlString = "SELECT spec_id FROM sdb_goods_spec_index WHERE spec_id IN (".implode(',',$spec_id).")";
        $tmpdata = $this->db->select($sqlString);
        if(count($tmpdata)!=0){
            return false;
        }else{
            return true;
        }
       
    }

    function check_spec_value_id($spec_value_id){
        $data = $this->db->select("SELECT spec_id FROM sdb_goods_spec_index WHERE spec_value_id = '".$spec_value_id."'");
        if(count($data)==0){
            return "can";
        }else{
            return "该规格值已绑定商品";
        }
    }
}
?>