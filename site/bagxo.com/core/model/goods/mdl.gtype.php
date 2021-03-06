<?php
include_once('shopObject.php');
/**
 * mdl_goods_type 
 * 
 * @uses shopObject
 * @package goods
 * @version $Id: mdl.gtype.php 1985 2008-04-28 06:36:02Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @license Commercial
 */
class mdl_gtype extends shopObject{

    var $idColumn = 'type_id';
    var $textColumn = 'name';
    var $defaultCols = 'name,is_physical';
    var $adminCtl = 'goods/gtype';
    var $defaultOrder = array('type_id','desc');
    var $tableName = 'sdb_goods_type';

    function getColumns(){
        return array(
                        'type_id'=>array('label'=>'类型序号','class'=>'span-3'),    /* 商品类型ID */
                        'name'=>array('label'=>'类型名称','class'=>'span-4'),    /* 类型名称 */
                        'is_physical'=>array('label'=>'实体商品','class'=>'span-2','type'=>'bool'),    /* 是否实体商品 */
                        'schema_id'=>array('label'=>'类型标识','class'=>'span-3'),    /* 商品插件 */
                        'is_def'=>array('label'=>'系统默认','class'=>'span-3','type'=>'bool'),    /* 系统默认 */
                        'setting'=>array('label'=>'类型设置','class'=>'span-3'),    /* 类型设置 */
//            'dly_func'=>array('label'=>'是否包含发货函数','class'=>'span-3'),    /* 是否包含发货函数 */
//            'ret_func'=>array('label'=>'是否包含退货函数','class'=>'span-3'),    /* 是否包含退货函数 */
//            'reship'=>array('label'=>'退货方式 disabled:不允许退货   func:函数式','class'=>'span-3'),    /* 退货方式 disabled:不允许退货   func:函数式 */
                );
    }
    
   
    function toRemove($type_id){
        if($this->checkDelete($type_id, $result)){
            $sql = 'DELETE FROM sdb_goods_type WHERE type_id = '.intval($type_id);
            $this->db->exec($sql);
            $sql = 'DELETE FROM sdb_type_brand WHERE type_id = '.intval($type_id);
            $this->db->exec($sql);
            $sql = 'DELETE FROM sdb_goods_type_spec WHERE type_id = '.intval($type_id);
            $this->db->exec($sql);
            return true;
        }else{
            if($result == 1){
                trigger_error(__('系统默认通用类型不允许删除'),E_USER_ERROR);
                return false;
            }
            if($result == 2){
                trigger_error(__('类型下存在与之关联的商品，无法删除'),E_USER_ERROR);
                return false;
            }
        }
    }

    function checkDelete($type_id, &$result){
        $aType = $this->getDefault();
        if($aType[0]['type_id'] == $type_id){
            $result = 1;
            return false;
        }
        $row = $this->db->selectrow('SELECT count(*) AS has_goods FROM sdb_goods WHERE type_id ='.intval($type_id));
        if($row['has_goods']>0){
            $result = 2;
            return false;
        }else{
            return true;
        }
    }

    function find($type_id,$props,$filter=null){
        $props_map = $this->_props_map($type_id);

        $cell=array('product_id'=>'product_id','name'=>'name');

        foreach($props as $p){
            if($props_map[$p]['cell']){
                $cell['P_'.$props_map[$p]['cell']] = $p;
                $columnsType['P_'.$props_map[$p]['cell']] = array($props_map[$p]['type'],$props_map[$p]['cell']);
            }
        }

        $where = ' ';
        if(is_array($filter))
            foreach($filter as $f=>$v){
                if($props_map[$f]['type']==P_ENUM){
                    $where.=' and '.$props_map[$f]['cell'].'='.intval($v);
                }elseif($props_map[$f]['type']==P_SHORT){
                    $where.=' and '.$props_map[$f]['cell'].'='.$this->db->quote($v);
                }
            }

        $rows = $this->db->select('select '.implode(',',array_keys($cell)).' from sdb_products where type_id='.intval($type_id).$where);

        $result = array();
        foreach($rows as $i=>$row){
            foreach($row as $k=>$v){
                if(substr($k,0,2)=='P_'){
                    $result[$i][$cell[$k]] = $v;
                    if($columnsType[$k][0]==P_ENUM){
                        $columnValues[$v] = $v;
                        $result[$i][$cell[$k]] = &$columnValues[$v];
                    }
                }else{
                    $result[$i][$k] = $v;
                }
            }
            $result[$i]['id'] = $row['product_id'];
        }
        if(count($columnValues)>0)
            foreach($this->db->select('select value_id,p_value from sdb_param_values where value_id in ('.implode(',',$columnValues).')') as $row){
                $columnValues[$row['value_id']] = $row['p_value'];
            }

        return $result;
    }

    function getTypeDetail($typeid, $str_tag=false){
        $sqlString = 'SELECT * FROM sdb_goods_type WHERE type_id = '.intval($typeid);
        $row = $this->db->selectrow($sqlString);
        $row['props'] = unserialize($row['props']);
        $row['ordernum'] = $this->propsort($row['props']);
        $row['setting'] = unserialize($row['setting']);
        $row['spec'] = unserialize($row['spec']);
        $row['setting']['use_spec'] = true;
        $row['minfo'] = unserialize($row['minfo']);
        $row['params'] = unserialize($row['params']);
        $s = 0;
        foreach($row['params'] as $g){
            foreach($g as $p){
                $s = 1;
            }
        }
        if($s==0){
            unset($row['params']);
        }
        
        if($str_tag){
            $this->arrToStr($row);
        }
        
        $brand = $this->system->loadModel('goods/brand');
        $aBrand = $brand->getTypeBrands($typeid);
        $aTmpBrands = array();
        foreach($aBrand as $v){
            $aTmpBrands[] = $v['brand_id'];
        }
        $row['brands'] = $aTmpBrands;
        
        $row['spec'] = $this->getSpec($typeid);

        return $row;
    }
    
    function arrToStr(&$data){
        foreach($data['props'] as $k => $row){
            $aTmp = array();
            foreach($row['options'] as $i => $v){
                if($row['optionAlias'][$i]){
                    $aTmp[] = $v.'|'.$row['optionAlias'][$i];
                }else{
                    $aTmp[] = $v;
                }
            }
            $data['props'][$k]['s_props'] = implode(',', $aTmp);
        }
        if($data['params']){
            $i = 0;
            foreach($data['params'] as $gname => $row){
                $i++;
                $aGroup['group'][$i] = $gname;
                foreach($row as $item => $alias){
                    $aGroup['name'][$i][] = $item;
                    $aGroup['alias'][$i][] = $alias;
                }
            }
            $data['a_params'] = $aGroup;
        }
        foreach($data['minfo'] as $k => $row){
            if($row['options']){
                $data['minfo'][$k]['s_minfo'] = implode(',', $row['options']);
            }
        }
    }

    function get($type_id){
        $row = $this->db->selectrow('select type_id,schema_id,setting from sdb_goods_type where type_id='.intval($type_id));
        $row['setting'] = unserialize($row['setting']);

        $props_map = $this->_props_map($type_id);

        $valueMap=array();
        if($row['setting']['inSearch'])
            foreach($row['setting']['inSearch'] as $k=>$v){
                if($props_map[$v['attr']]['type']==P_ENUM){
                    $valueMap[$props_map[$v['attr']]['cell']] = array();
                    $row['setting']['inSearch'][$k]['items'] = &$valueMap[$props_map[$v['attr']]['cell']];
                }
            }
        if($row['setting']['inSelector'])
            foreach($row['setting']['inSelector'] as $k=>$v){
                if($props_map[$v['attr']]['type']==P_ENUM){
                    $valueMap[$props_map[$v['attr']]['cell']] = array();
                    $row['setting']['inSelector'][$k]['column'] = $props_map[$v['attr']]['cell'];
                    $row['setting']['inSelector'][$k]['items'] = &$valueMap[$props_map[$v['attr']]['cell']];
                }
            }
        if(count($valueMap)>1)
            foreach($this->db->select('select value_id,p_value,p_column from sdb_param_values where p_column in('.implode(',',array_keys($valueMap)).') and type_id='.intval($type_id)) as $v){
                $valueMap[$v['p_column']][] = array('label'=>$v['p_value'],'value'=>$v['value_id']);
            }

        return $row;
    }

    function _props_map($type_id){
        $row = $this->db->selectrow('select props from sdb_goods_type where type_id='.intval($type_id));
        return unserialize($row['props']);
    }

    function getWannaColumns($file){
        preg_match_all('/[^a-z0-9]\$product\.([a-z0-9\_\-]+)[^a-z0-9\_\-]/is',file_get_contents($file),$matchs);
        return $matchs[1];
    }

    function deliveryInfo($products){
        $info = array('custom'=>array());
        if(is_array($products) && count($products))
        foreach($this->db->select('SELECT c.type_id,c.schema_id,c.minfo,c.is_physical FROM sdb_goods p LEFT JOIN sdb_goods_type c ON c.type_id = p.type_id WHERE p.goods_id IN ('.implode(',',$products).') GROUP BY c.type_id') as $type){
            if($req = unserialize($type['minfo'])){
                    $info['custom'][$type['type_id']] = $req;
            }
            if($type['is_physical'])
                $info['physical'] = true;
        }
        return $info;
    }

    function filterUri($array){
        $a = array();
        $actmapper = $this->system->actmapper();

        foreach($array as $column_id=>$values){
            ksort($values);
            $arr = array_keys($values);
            if($arr[0]){
                array_unshift($arr,$column_id);
                $a[$column_id] = implode(',',$arr);
            }
        }
        ksort($a);
        return $actmapper->getLink('gallery',$this->system->getConf('gallery.default_view'),array('2',implode('_',$a)));
    }

    function getTypeObj($id,&$name){
        $type = $this->instance($id);
        
        $brand = $this->system->loadModel('goods/brand');
        $typeBrands = $brand->getTypeBrands($id);
        
        $return = array();
        $name = $type['name'];
        $return['name'] = $type['name'];
        $return['alias'] = $type['alias'];
        $return['schema_id'] = $type['schema_id'];
        $return['is_physical'] = $type['is_physical'];
        $return['props'] = unserialize($type['props']);
        $return['setting'] = unserialize($type['setting']);
        if($return['setting']['use_brand']==1&&$typeBrands){
            $return['brands'] = array();
            foreach($typeBrands as $v){
                if($v['brand_name']!=''){
                    $arr['brand_name'] = $v['brand_name'];
                    $arr['brand_keywords'] = $v['brand_keywords'];
                    $arr['s_brand_id'] = $v['s_brand_id'];
                    $return['brands'][] = $arr;
                }
            }
        }
        $return['spec'] = $this->getSpec($id);
        $return['minfo'] = unserialize($type['minfo']);
        $return['params'] = $this->params_modifier(unserialize($type['params']));
        return $return;
    }
    
    function params_modifier($data,$forxml = true){
        $return = array();
        if(is_array($data)){
            if($forxml){
                $i = 0;
                foreach($data as $group=>$cont){
                    $return[$i] = array('groupname'=>$group);
                    if(is_array($cont)){
                        foreach($cont as $k=>$v){
                            $item['itemname'] = $k;
                            $item['itemalias'] = explode('|',$v);
                            $return[$i]['groupitems'][] = $item;
                        }
                    }
                    $i++;
                }
            }else{
                foreach($data as $k=>$group){
                    $return[$group['groupname']] = array();
                    if($group['groupitems']&&is_array($group['groupitems'])){
                        foreach($group['groupitems'] as $k1=>$v1){
                            $return[$group['groupname']][$v1['itemname']] = implode('|',$v1['itemalias']);
                        }
                    }
                }
            }
        }
        return $return;
    }

    function fetchSave($data){
        if ($data['props']){
            foreach($data['props'] as $key => $val){
                $data['props'][$key]['show']=1;
            }
        }
        $data['props'] = addslashes(serialize($data['props']));
        $data['params'] = addslashes(serialize($this->params_modifier($data['params'],false)));
        $data['member_req'] = serialize($data['member_req']);
        $data['setting'] = serialize($data['setting']);
        if($this->db->selectrow('select * from sdb_goods_type where name=\''.$data['name'].'\'')){
            $this->setError(300001);
            trigger_error(__('对不起，本类型名已存在，请重新输入。'),E_USER_ERROR);
        }
        
        $rs = $this->db->exec('select * from sdb_goods_type where 0=1');
        $sql = $this->db->getInsertSQL($rs,$data);
        if($this->db->exec($sql)){
            $type_id = $this->db->lastInsertId();
            
            foreach($data['brands'] as $v){
                if(trim($v['brand_name'])){
                    $aBrands[] = $v['brand_name'];
                }
            }
            if($aBrands){
                $brand = $this->system->loadModel('goods/brand');
                $aBrands = $brand->getBrandsByNames($aBrands);
            }
            
            $type_brand = array();
            $i = 0;
            $type_brand['type_id'] = $type_id;
            
            foreach($data['brands'] as $v){
                if($aBrands[$v['brand_name']]){
                    $type_brand['brand_order'] = $i;
                    $type_brand['brand_id'] = $aBrands[$v['brand_name']];
                    $rs_type_brand = $this->db->exec('select * from sdb_type_brand where 0=1');
                    $sql = $this->db->getInsertSQL($rs_type_brand,$type_brand);
                    $this->db->exec($sql);
                    $i++;
                }
            }
            $brand = array();
            
            foreach($_POST['importbrands'] as $v){
                $brand['s_brand_id'] = $v;
                $brand['brand_name'] = $data['brands'][$v]['brand_name'];
                $brand['brand_keywords'] = $data['brands'][$v]['brand_keywords'];
                $rs_brand = $this->db->exec('select * from sdb_brand where 0=1');
                $sql = $this->db->getInsertSQL($rs_brand,$brand);
                $this->db->exec($sql);
                $brand_id = $this->db->lastInsertId();
                $type_brand['brand_order'] = $i;
                $type_brand['brand_id'] = $brand_id;
                $rs_type_brand = $this->db->exec('select * from sdb_type_brand where 0=1');
                $sql = $this->db->getInsertSQL($rs_type_brand,$type_brand);
                $this->db->exec($sql);
            }
            
            foreach($data['spec'] as $spec_id => $v){
                if($spec_id){
                    $type_spec['spec_id'] = $spec_id;
                    $type_spec['type_id'] = $type_id;
                    $type_spec['spec_style'] = $v['spec_style'];
                    $rs_type_spec = $this->db->exec('select * from sdb_goods_type_spec where 0=1');
                    $sql = $this->db->getInsertSQL($rs_type_spec,$type_spec);
                    $this->db->exec($sql);
                }
            }

            $this->checkDefined();
            return true;
        }else{
            return false;
        }
    }
    
    function toSave($data){
        if(!class_exists('schema_'.$data['schema_id'])){
            require(SCHEMA_DIR.$data['schema_id'].'/schema.'.$data['schema_id'].'.php');
        }
        $type = 'schema_'.$data['schema_id'];
        
        $schema = $this->system->loadModel('goods/schema');
        return $schema->save($data['schema_id'], $data, $message, get_class_methods($type));
    }

    function getTypebyAlias($cols='*',$name){
        return $this->db->selectrow('select '.$cols.' from sdb_goods_type where name=\''.$name.'\' or alias like \'%|'.$name.'|%\'');
    }
    
    function getEnum(){
        return $this->db->select('select type_id,name from sdb_goods_type');
    }
    
    function checkDefined(){
        $filter['is_def'] = 'false';
        $this->getList('',$filter,0,1,$count);
        return $count;
//        if($count) $this->system->setConf('store.gtype.status', 1);
//        else $this->system->setConf('store.gtype.status', 0);
    }
    
    function getDefault(){
        $filter['is_def'] = 'true';
        return $this->getList('*',$filter);
    }
    
    function saveSpec($specid, $aData){
        if($specid){
            //todo
            //$this->updateIndex();
            
            $aUpdate = array('spec' => serialize($aData));
            $rs = $this->db->exec("SELECT * FROM sdb_goods_type WHERE type_id=".$specid);
            $sSql = $this->db->getUpdateSql($rs,$aUpdate);
            if(!$sSql || $this->db->exec($sSql)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    function getSpec($id,$fm=0){
        $sql="select spec_id,spec_style from sdb_goods_type_spec where type_id=".intval($id);
        $row = $this->db->select($sql);
        if ($row){
            foreach($row as $key => $val){
                if ($fm){
                    if($val['spec_style']<>'disabled'){
                        $attachment=array(
                            "spec_style"=>$val['spec_style']
                        );
                        $tmpRow[$val['spec_id']]=$this->getSpecName($val['spec_id'],$attachment);
                    }
                }
                else{
                    $attachment=array(
                        "spec_style"=>$val['spec_style']
                    );
                    $tmpRow[$val['spec_id']]=$this->getSpecName($val['spec_id'],$attachment);
                }
            }

            return $tmpRow;
        }
        else
            return false;
    }
    function getSpecName($spec_id,$args){
        $sql="select spec_name,spec_type from sdb_specification where spec_id=".intval($spec_id);
        $snRow=$this->db->selectrow($sql);
        $tmpRow['name']=$snRow['spec_name'];
        $tmpRow['spec_type'] = $snRow['spec_type'];
        $tmpRow['spec_memo'] = $snRow['spec_memo'];
        if (is_array($args)){
            foreach($args as $k => $v){
                $tmpRow[$k] = $v;
            }
        }
        $row=$this->getSpecValue($spec_id);
        $tmpRow['spec_value']=$row;
        $tmpRow['type'] = 'spec';
        return $tmpRow;
    }
    function getSpecValue($spec_id){
        $sql="select spec_value,spec_value_id,spec_image from sdb_spec_values where spec_id=".intval($spec_id)." order by p_order,spec_value_id";
        $svRow=$this->db->select($sql);
        if ($svRow){
            foreach($svRow as $key => $val){
                $tmpRow[$val['spec_value_id']]=array(
                        "spec_value"=>$val['spec_value'],
                        "spec_image"=>$val['spec_image']
                );
            }
        }
        return $tmpRow;
    }
    function propsort($prop=array()){
        if (is_array($prop)){
            foreach($prop as $key => $val){
                $tmpP[]=array('ord'=>$val['ordernum'],'key'=>$key);
            }
            for($i=0;$i<count($tmpP);$i++){
                for($j=$i;$j<count($tmpP);$j++){
                    if (intval($tmpP[$i]['ord'])>intval($tmpP[$j]['ord'])){
                        $t=$tmpP[$i];
                        $tmpP[$i]=$tmpP[$j];
                        $tmpP[$j]=$t;
                    }
                }
            }
            if ($tmpP){
                foreach($tmpP as $key => $val){
                    $tmpC[]=$val['key'];
                }
            }
            return $tmpC;
        }
    }
}
?>