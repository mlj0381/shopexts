<?php
/**
 * shopObject
 * 网店对象基类
 *
 * @uses modelFactory
 * @package
 * @version $Id$
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class shopObject extends modelFactory{
    var $disabledMark = 'normal';
    var $typeName = null;

    function shopObject(){
        parent::modelFactory();
        if(!$this->typeName) $this->typeName = substr(strstr(get_class($this),'_'),1);
    }

    function getFilter($params){;}

    function getByFilter($data,$start=0,$limit=null){
        parse_str($data,$data);
        $return=array();
        foreach($this->getList($this->idColumn,$data,$start,$limit,$count) as $row){
            $return[] = $row[$finder['id']];
        }
        return $return;
    }

    function modifier_tag(&$rows){
        foreach($rows as $r){
            $rows[$r] = null;
            if(is_array($this->tags[$r])){
                foreach($this->tags[$r] as $t){
                    $rows[$r] .= '<b class="tag">'.$t.'</b>';
                }
            }
        }
        unset($this->tags);
    }

    function modifier_region(&$rows){
        foreach($rows as $i=>$r){
            list($pkg,$regions,$region_id) = explode(':',$r);
            if(is_numeric($region_id)){
                $rows[$i] = str_replace('/','-',$regions);
            }
        }
    }

    function modifier_time(&$rows,$options=array()){
        if(count($options)>0){
            $formatId = $options[0];
            switch($formatId){
            case 'FDATE':
                $dateFormat = 'Y-m-d';
                break;
            case 'SDATE':
                $dateFormat = 'y-m-d';
                break;
            case 'DATE':
                $dateFormat = 'm-d';
                break;
            case 'FDATE_FTIME':
                $dateFormat = 'Y-m-d H:i:s';
                break;
            case 'FDATE_STIME':
                $dateFormat = 'Y-m-d H:i';
                break;
            case 'SDATE_FTIME':
                $dateFormat = 'y-m-d H:i:s';
                break;
            case 'SDATE_STIME':
                $dateFormat = 'y-m-d H:i';
                break;
            case 'DATE_FTIME':
                $dateFormat = 'm-d H:i:s';
                break;
            case 'DATE_STIME':
                $dateFormat = 'm-d H:i';
                break;
            default:
                if(!$dateFormat = &$this->system->getConf('admin.dateFormat')){
                    $dateFormat = 'm-d H:i:s';
                }
            }
        }else{
            if(!$dateFormat = &$this->system->getConf('admin.dateFormat')){
                $dateFormat = 'm-d H:i:s';
            }
        }
        foreach($rows as $i=>$date){
            if($date){
                $date+=($GLOBALS['user_timezone']-SERVER_TIMEZONE)*3600;
                $rows[$i] = ($date ? date($dateFormat,$date) : '');
            }
        }
    }

    function columnValue($column,$value){
        if(!isset($this->_columns)){
            $this->_columns = $this->getColumns();
        }

        switch($this->_columns[$column]['type']){

        case 'number':
            return intval($value);
            break;
        case 'date':
        case 'time':
            return strtotime($value);
        case 'bool':
            if($value === '1' || $value === '0' || $value === 'true' || $value === 'false') return $value;
            else{
                if($this->_columns[$column]['bool'] == 'number'){
                    return ($value)?'1':'0';
                }else{
                    return ($value)?'true':'false';
                }
            }
            break;
        case 'money':
            if($value{0}=='+' || $value{0}=='*' || $value{0}=='/'){
                return $column.$value{0}.floatval(substr($value,1));
            }
            if($value<0){
                return $column.'-'.floatval(substr($value,1));
            }else{
                return $value;
            }
            break;
        default:
            return $value;
            break;

        }
    }

    function modifier_money(&$rows,$options=array()){
        foreach($rows as $i=>$money){
            $rows[$i] = number_format($money,2,'.','');
        }
    }

    function modifier_bool(&$rows,$options=array()){
        $aBool = array(
            0=>'否',
            1=>'是',
            'false'=>'否',
            'true'=>'是' );
        foreach($rows as $i => $v){
            $rows[$i] = $aBool[$v];
        }
    }

    function modifier_enum(&$rows,$options=array()){
        $options = $options['options'];
        foreach($rows as $i => $v){
            $rows[$i] = $options[$v];
        }
    }

    function modifier_object(&$rows,$options=array()){
        if(count($options)==0||!is_array($options)){
            trigger_error('Undefined object params',E_USER_WARNING);
            return;
        }
        $objects = $this->objects();
        if($mod=$objects[$options[0]]){
            $o = &$this->system->loadModel($mod);
            $col = $options[2]?$options[2]:$o->textColumn;
            $aList = $o->getList($o->idColumn.','.$col,array($o->idColumn=>array_keys(array_flip($rows))),0,-1,$c);
            foreach($rows as $k => $v){
                $rows[$k] = '-';    //重置原始数据 Ever： 2008-07-23
            }
            foreach($aList as $r){
                if($r[$col]){
                    $rows[$r[$o->idColumn]] = $r[$col];
                }
            }
            $aList = null;
        }
    }

    function setFinderCols($options=null,$filter=null){
        $allCols = $this->getColumns($filter);
        $process = array('GridEdit','ColumnEdit','GridShow');
        foreach($process as $item){
            $colsAttr = 'cols'.$item;
            $enableAttr = 'enable'.$item.'Cols';
            $disableAttr = 'disable'.$item.'Cols';
            $this->$colsAttr = array_keys($allCols);
            $ret_arr=array();
            if($options[$enableAttr]){
                $arr = explode(',',$options[$enableAttr]);
                foreach($arr as $v){
                    if(in_array($v,$this->$colsAttr)){
                        $ret_arr[] = $v;
                    }
                }
                $this->$colsAttr = $ret_arr;
            }elseif($options[$disableAttr]){
                $arr = explode(',',$options[$disableAttr]);

                $ret_arr = $this->$colsAttr;
                foreach($ret_arr as $k=>$v){
                    if(in_array($v,$arr)){
                        unset($ret_arr[$k]);
                    }
                }
                $this->$colsAttr = $ret_arr;
            }else{
                if($item=='GridEdit'||$item=='ColumnEdit'){
                    $this->$colsAttr = array();
                }
            }
            if($item=='GridEdit'||$item=='ColumnEdit'){
                foreach($this->$colsAttr as $k=>$v){
                    if($this->idColumn == $v){
                        unset($this->$colsAttr[$k]);
                    }
                }
            }
        }
    }


    function getFinder($cols,$filter,$start=0,$limit=null,&$count,$orderby=null,$disabledCols=null,$editMode=false){
        $allCols = $this->getColumns($filter);
        if($this->hasTag){
            $allCols = array_merge(array('_tag_'=>array('label'=>'标签','colsGridShow'=>true,'type'=>'tag','class'=>'span-4','noOrder'=>true)),$allCols);
        }

        $useTools = false;
        $extraCols = '';
        $ActionsFilterFound = false;
        $modifiers = false;

        if(is_null($this->colsGridEdit)||is_null($this->colsColumnEdit)||is_null($this->colsGridShow)){
            $this->setFinderCols(null,$filter);
        }

        $disabledCols = array_flip(explode(',',$disabledCols));
        foreach(explode(',',$cols) as $col){
            if(!isset($disabledCols[$col])){
                if(isset($allCols[$col])){
                    $allCols[$col]['used'] = true;
                    $colArray[$col] = &$allCols[$col];
                    if(isset($allCols[$col]['sql'])){
                        $sql[] = $allCols[$col]['sql'].' as '.$col;
                    }elseif($col=='_tag_'){
                        $sql[] = $this->idColumn.' as _tag_';
                    }else{
                        $sql[] = $col;
                    }
                }else{
                    trigger_error('Undefined column "'.$col.'"',E_USER_WARNING);
                }
            }
        }

        if(!isset($colArray[$this->idColumn])) array_unshift($sql,$this->idColumn);
        if($filter===-1){
            $list = array();
        }else{
            $list = $this->getList(implode(',',$sql).$extraCols,$filter,$start,$limit,$count,$orderby);
            if($list===false){
                return false;
            }
            foreach($allCols as $k=>$v){
                if($k==$this->textColumn){
                    $allCols[$k]['required'] = 1;
                }
                $allCols[$k]['colsGridEdit']  = false;
                $allCols[$k]['colsColumnEdit']  = false;
                $allCols[$k]['colsGridShow']  = false;
                if(in_array($k,$this->colsGridEdit)){
                    $allCols[$k]['colsGridEdit']  = !$allCols[$k]['readonly'];
                }
                if(in_array($k,$this->colsColumnEdit)){
                    $allCols[$k]['colsColumnEdit']  = !$allCols[$k]['readonly'];
                }
                if(in_array($k,$this->colsGridShow)){
                    $allCols[$k]['colsGridShow']  = true;
                }
            }

            $id = array();
            $highlight = method_exists($this,'is_highlight');

            foreach($list as $i=>$row){
                foreach($row as $k=>$v){
                    if(!$editMode || !$colArray[$k]['colsGridEdit'] || $colArray[$k]['type']=='time'||substr($colArray[$k]['type'],0,5)=='time:'){
                        if(isset($colArray[$k]['modifier']) && $colArray[$k]['modifier']=='row'){
                            $func = 'modifier_'.$k;
                            $list[$i][$k] = $this->$func($row);
                        }else{
                            if($colArray[$k]['type'] && !is_null($v)){
                                $modifier_key = $colArray[$k]['options']?$colArray[$k]['type'].'|'.serialize($colArray[$k]['options']):$colArray[$k]['type'];
                                $modifiers[$modifier_key][$v] = $v;
                                $list[$i]['__'.$k] = $list[$i][$k];
                                $list[$i][$k] = &$modifiers[$modifier_key][$v];
                            }
                        }
                    }
                    if($highlight)$list[$i]['highlight'] = $this->is_highlight($row);
                }
                $id[] = $row[$this->idColumn];
            }

            if($this->hasTag && count($id)>0){
                $allCols['_tag_']['colsGridShow'] = true;
                foreach($this->db->select('select t.tag_name,rel_id from sdb_tag_rel r left join sdb_tags t on t.tag_id=r.tag_id where t.tag_type=\''.$this->typeName.'\' and r.rel_id in('.implode(',',$id).')') as $tag){
                    $this->tags[$tag['rel_id']][] = $tag['tag_name'];
                }
            }
            foreach($list as $i=>$row){
                $list[$i]['_tags'] = json_encode($this->tags[$row[$this->idColumn]]);
            }

            unset($ctlClassObj,$id);
            foreach($modifiers as $type=>$rows){
                $params = explode('|',$type,2);
                $options = explode(':',$params[0]);
                $func = 'modifier_'.array_shift($options);
                if(count($params)>1){
                    $options['options'] = unserialize($params[1]);
                }
                if(method_exists($this,$func)){
                    $this->$func($modifiers[$type],$options);
                }
            }
        }
        $return = array(
            'list'=>&$list,
            'cols'=>&$colArray,
            'allCols'=>&$allCols
        );
        return $return;
    }

    function getColumns($filter=null){
        trigger_error('Undefined method "getColumns" in '.get_class($this),E_USER_ERROR);
    }

    /**
     * batchEditCols
     * 批量编辑
     *
     * @param mixed $filter
     * @access public
     * @return void
     */
    function batchEditCols($filter){
        $ret = $this->getColumns($filter);
        if(is_null($this->colsGridEdit)||is_null($this->colsColumnEdit)||is_null($this->colsGridShow)){
            $this->setFinderCols(null,$filter);
        }

        foreach($ret as $k=>$col){
            if(in_array($k,$this->colsColumnEdit)){
                $c[] = "count(DISTINCT $k) as $k";
            }else{
                unset($ret[$k]);
            }
        }

        $r = $this->db->selectrow('select count('.$this->idColumn.') as count from '.$this->tableName.' where '.$this->_filter($filter));
        $rowCount = $r['count'];

        //如果所编辑的条目小于1000，则将获得相同值得列。
        if($rowCount<1000){
            $sql = 'select '.implode(',',$c).' from '.$this->tableName.' where '.$this->_filter($filter);
            $c = array();
            if($r = $this->db->selectrow($sql)){
                foreach($r as $col=>$count){
                    if($count<2){
                        $c[] = $col;
                    }
                }
                foreach($this->db->selectrow('select '.implode(',',$c).' from '.$this->tableName.' where '.$this->_filter($filter)) as $k=>$v){
                    if(substr($ret[$k]['type'],0,5)=='time:'||$ret[$k]['type']=='time'){
                        $options = explode(':',$ret[$k]['type']);
                        array_shift($options);
                        $rows = array($v);
                        $this->modifier_time($rows,$options);
                        $v = $rows[0];
                    }
                    $ret[$k]['value'] = $v;
                }
            }
        }

        return array('cols'=>$ret,'count'=>$rowCount);
    }

    function finderResult($data,$start=0,$limit=null){
        if($data['filter']){
            parse_str($data['filter'],$data);
            $finder = $data['_finder'];
            unset($data['_finder']);
            $return=array();
            foreach($this->getList($this->idColumn,$data,$start,$limit) as $row){
                $return[] = $row[$this->idColumn];
            }
            return $return;
        }else{
            return $data['items'];
        }
    }

    function allViews($objType=null){
        if(method_exists($this,'getViews')){
            $list = $this->getViews();
        }
        foreach($list as $k=>$v){
            $ret[json_encode($v)]=$k;
        }
        return $ret;
    }

    function searchOptions(){ return false; }

    function tofilter($d){
        parse_str($d['filter'],$filter);
        return $filter;
    }

    /**
     * inFilter 验证是否
     *
     * @param mixed $arr
     * @param mixed $filter
     * @access public
     * @return void
     */
    function inFilter($arr,$filter){
        $filter[$this->idColumn] = $arr;
        $row = $this->getList($this->idColumn,$filter,0,-1,$c);
        $ret = array();
        foreach($row as $v){
            $ret[] = $v[$this->idColumn];
        }
        return is_array($arr)?$ret:($ret[0]==$arr);
    }

    /**
     * addEvent 增加事件处理函数
     *
     * @param mixed $type 类型
     * @param mixed $filter 过滤条件
     * @param mixed $event hook事件名
     * @param mixed $func 动作函数
     * @access public
     * @return void
     */
    function addEvent($type,$filter,$event,$func){}

    /**
     * fireEvent 触发事件
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    function fireEvent($action , &$object, $member_id=0){
        $type = $this->typeName;
        $hooks_dir = BASE_DIR.'/plugins/hooks/';
        $this->system->messenger = $this->system->loadModel('system/messenger');
        $this->system->_msgList = $this->system->messenger->actions();
        if($this->system->_msgList[$type.'-'.$action]){
            $this->system->messenger->actionSend($type.'-'.$action,$object,$member_id);
        }

        ob_start();
        if(!isset($GLOBALS['eventHandles'][$type])){
            $handles = &$GLOBALS['eventHandles'][$type];
            include('handles.php');
            if($hooks[$type][$action]){
                foreach($hooks[$type][$action] as $i=>$h){
                    $r[$h['class'].'_'.$h['func']] = $i;
                    $o[$i]=10;
                }
            }

            foreach($this->db->select('select * from sdb_event_hdls where target="'.$type.'" order by orderby') as $hdls){
                $i = $r[$hdls['class'].'_'.$h['func']];
                if($hdls['disabled']==1 && $i){
                    unset($hooks[$type][$action][$i]);
                    unset($o[$i]);
                }else if($i){
                    $hooks[$type][$action][$i] = $hdls;
                    $o[$i]=intval($hdls['orderby']);
                }else{
                    $hdls['setting'] = unserialize($hdls['setting']);
                    $hooks[$type][$action][] = $hdls;
                }
            }

            array_multisort($o,$hooks[$type][$action]);
            foreach($hooks[$type][$action] as $hook){
                $hookTemp = explode('_',$hook['class'],2);
                $hook_file = $hooks_dir.'hook.'.$hookTemp[1].'.php';
                if($hook_file){
                    if(!include_once($hook_file)){
                        continue;
                    }
                }
                if($func = $hook['func']){
                    if($class = $hook['class']){
                        if(class_exists($class)){
                            $o = new $class;
                            if(is_callable(array($o,$func))) $o->$func($object,$hook['setting']);
                        }
                    }else{
                        if(function_exists($func)) $func($object,$hook['setting']);
                    }
                }
            }
        }
        ob_get_clean();
        return 1;
    }

    /**
     * addTag
     *
     * @param mixed $mix 支持三种参数 id,id的集合,filter
     * @param mixed $tagName
     * @access public
     * @return void
     */
    function addTag($mix,$tag_id){
        $type = '';
        $modTag = $this->system->loadModel('system/tag');
        if(is_array($mix)){
            if($mix['items']){
                $modTag->begin();
                foreach($mix['items'] as $id){
                    $modTag->addTag($tag_id,$id,$type);
                }
                $modTag->end();
            }elseif($mix['filter']){
                parse_str($mix['filter'],$filter);
            }
        }else{
            $modTag->addTag($tag_id,$mix,$type);
        }
    }

    function newTag($tagName){
        $modTag = &$this->system->loadModel('system/tag');
        return $modTag->newTag($tagName,$this->typeName);
    }

    function setTag($data,$tags){
        $a = array();
        foreach($this->db->select("select {$this->tableName}.{$this->idColumn} as rel_id from {$this->tableName} where ".$this->_filter($data,$this->tableName)) as $r){
            $a[] = $r['rel_id'];
        }
        $tag_id=array();
        foreach($this->db->select("SELECT DISTINCT(r.tag_id) FROM sdb_tag_rel r LEFT JOIN sdb_tags t ON r.tag_id = t.tag_id
            where tag_type='".$this->typeName."' AND rel_id IN(".implode(',',$a).")") as $rows){
                $tag_id[] = $rows['tag_id'];
            }
        if(count($tag_id) > 0){
            $this->db->exec('delete from sdb_tag_rel where tag_id in('.implode(',',$tag_id).') and rel_id in('.implode(',',$a).')');
        }else{
            $this->db->exec('delete from sdb_tag_rel where rel_id in('.implode(',',$a).')');
        }

        $modTag = &$this->system->loadModel('system/tag');
        foreach($tags as $tag){
            $tagId = $modTag->tagId($tag,$this->typeName);
            $tag_id[] =  $tagId;
            if(defined('DB_OLDVERSION') && DB_OLDVERSION){
                foreach($this->db->select("select {$tagId} as tag_id,{$this->tableName}.{$this->idColumn} as rel_id from {$this->tableName} where ".$this->_filter($data,$this->tableName)) as $r){
                    if(!$this->db->exec("insert into sdb_tag_rel (tag_id,rel_id) values({$r['tag_id']},{$r['rel_id']})")){
                        return false;
                    }
                }
            }else{
                $sql = "insert into sdb_tag_rel (tag_id,rel_id) select {$tagId} as tag_id,{$this->tableName}.{$this->idColumn} as rel_id from {$this->tableName} where ".$this->_filter($data,$this->tableName);
                if(!$this->db->exec($sql)){
                    return false;
                }
            }
        }

        $modTag->recount(array_unique($tag_id));
        return true;
    }

    function &tagList($count=false){
        $modTag = &$this->system->loadModel('system/tag');
        return $modTag->tagList($this->typeName,$count,$this->tableName,$this->idColumn);
    }

    //回收站
    function recycle($filter){
        $sql = 'update '.$this->tableName.' set disabled=\'true\' where '.$this->_filter($filter);
        return $this->db->exec($sql);
    }

    //从回收站移回
    function active($filter){
        $this->disabledMark = 'recycle';
        $sql = 'update '.$this->tableName.' set disabled=\'false\' where '.$this->_filter($filter);
        return $this->db->exec($sql);
    }

    function objects(){
        include('objects.php');
        return $objects;
    }

    /**
     * getList
     *
     * @param mixed $cols 列
     * @param mixed $filter 过滤数组
     * @param  $disabled 删除状态  normal|recycle|all
     * @access public
     * @return void
     *
     */
    function getList($cols,$filter='',$start=0,$limit=20,&$count,$orderType=null){
        $ident=md5($cols.print_r($filter,true).$start.$limit);
        if(!$this->_dbstorage[$ident]){
            if(!$cols){
                $cols = $this->defaultCols;
            }
            if(!empty($this->appendCols)){
                $cols.=','.$this->appendCols;
            }
            $orderType = $orderType?$orderType:$this->defaultOrder;
            $sql = 'SELECT '.$cols.' FROM '.$this->tableName.' WHERE '.$this->_filter($filter);
            if($orderType)$sql.=' ORDER BY '.implode($orderType,' ');
            $count = $this->db->_count($sql);
            if($count===false){
                return false;
            }
            $this->_dbstorage[$ident]=$this->db->selectLimit($sql,$limit,$start);
        }
        return $this->_dbstorage[$ident];
    }

    function instance($id,$cols='*'){
        return $this->db->selectrow('SELECT '.$cols.' FROM '.$this->tableName.' WHERE '.$this->idColumn .'= \''.$id.'\'');
    }

    function wFilter($words,$colum){
        $replace = array(",", "+");
        $return=str_replace($replace,' ',$words);
        $word=explode(" ",$return);

        foreach($word as $k=>$v){
            foreach($colum as $k=>$v){
                $sSql[]=$colum[$k].' LIKE \'%'.$word[$k].'%\'';
            }
            $sql[]='('.implode('or',$sSql).')';
            //$sql[]='($this->textColumn LIKE \'%'.$word[$k].'%\' or bn LIKE \'%'.$word[$k].'%\')';
        }
        return implode('and',$sql);
    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){
        $tPre = ($tableAlias?$tableAlias:$this->tableName).'.';
        $where = $baseWhere?$baseWhere:array(1);
        if($this->disabledMark=='normal'){
            $where[] =$tPre.'disabled = \'false\'';
        }elseif($this->disabledMark=='recycle'){
            if(isset($filter['disabled'])){
                $where[]=$tPre.'disabled = \''.$filter['disabled'].'\'';
            }else{
                $where[]=$tPre.'disabled = \'true\'';
            }
        }

        if(isset($filter['keywords']) && $filter['keywords']){
            //$where[]=$this->wFilter($filter['keywords']);
            if($this->keywordsColumn){
                $colum[]=$this->keywordsColumn;

            }
            if($this->textColumn){
                $colum[]=$this->textColumn;
            }


            $where[]=$this->wFilter($filter['keywords'],$colum);
        }

        if(isset($filter['tag']) && $tag = $filter['tag']){
            unset($filter['tag']);
            if(is_array($tag)){
                if(count($tag) == 0){
                    unset($tag);
                }
            }else{
                $tag = array($tag);
            }
            if($tag){
                foreach($this->db->select('select tag_id  from sdb_tags where tag_name in (\''.implode('\',\'',$tag).'\')') as $row){
                    $tag_id[]= $row['tag_id'];
                }
                if(count($tag_id)>0){
                    if(defined('DB_OLDVERSION') && DB_OLDVERSION){
                        $a = array();
                        foreach($this->db->select("select rel_id from sdb_tag_rel where tag_id in (".implode(',',$tag_id).")") as $r){
                            $a[] = $r['rel_id'];
                        }
                        if(count($a)>0){
                            $where[] = "{$this->idColumn} in (".implode(',',$a).")";
                        }
                    }else{
                        $where[] = "{$this->idColumn} in (select rel_id from sdb_tag_rel where tag_id in (".implode(',',$tag_id).") )";
                    }
                }
            }
        }

        $cols = $this->getColumns($filter);
        if(is_array($filter))
            foreach($filter as $k=>$v){
                $ac = array();
                if(isset($cols[$k])){
                    if(is_array($v)){
                        foreach($v as $m){
                            if($m!=='_ANY_' && $m!==''){
                                $ac[] = $cols[$k]['fuzzySearch']?($tPre.$k.' like \'%'.$m.'%\''):($tPre.$k.'=\''.$m.'\'');
                            }else{
                                $ac = array();
                                break;
                            }
                        }
                        if(count($ac)>0){
                            $where[] = '('.implode($ac,' or ').')';
                        }
                    }elseif($v && $v!=''){
                        $where[] = $cols[$k]['fuzzySearch']?($tPre.$k.' like \'%'.$v.'%\''):($tPre.$k.'=\''.$v.'\'');
                    }
                }
            }
        return implode($where,' AND ');
    }



    /**
     * insert
     *
     * @param mixed $data
     * @access public
     * @return void
     */
    function insert($data){
        if(method_exists($this,'pre_insert')){
            $this->pre_insert($data);
        }
        if(method_exists($this,'post_insert')){
            $this->post_insert($data);
        }
        $rs = $this->db->exec('select * from '.$this->tableName.' where 0=1');
        foreach($data as $k=>$v){
            $data[$k] = trim($data[$k]);
        }
        $sql = $this->db->getInsertSQL($rs,$data);
        $cols = $this->getColumns();
        $cols[$this->textColumn]['required'] = true;
        foreach($cols as $k=>$p){
            if($p['required']){
                if(!$data[$k]){
                    trigger_error('<b>'.$p['label'].'</b> 不能为空！',E_USER_ERROR);
                }
            }
        }
        if($sql && $this->db->exec($sql)){
            return $this->db->lastInsertId();
        }else{
            return false;
        }
    }

    function preUpdate(){


    }

    /**
     * update
     *
     * @param mixed $data
     * @param mixed $filter
     * @access public
     * @return void
     */
    function update($data,$filter){

        if(method_exists($this,'pre_update')){
            $this->pre_insert($data);
        }
        if(count($data)==0){
            return true;
        }

        $columnsList = $this->getColumns();

        $result = $this->db->exec('select * from '.$this->tableName.' where 0=1');
        for($i=0;$i<$result->FieldCount();$i++){
            $column = $result->FetchField($i);
            if(isset($data[$column->name])){
                if($column->type=='unknown' && $columnsList[$column->name]['type']=='money'){
                    $column->type = 'real'; //PHP_BUG http://bugs.php.net/bug.php?id=36069
                }
                if($columnsList[$column->name]['required'] && !$data[$column->name]){
                    trigger_error($columnsList[$column->name]['label'].'不能为空。',E_USER_WARNING);
                    $GLOBALS['php_errormsg'] = $php_errormsg;
                    return false;
                }
                $UpdateValues[] ='`'.$column->name.'`='.$this->db->_quotevalue(trim($data[$column->name]),$column->type,$this->db->_instance);
            }
        }

        if(count($UpdateValues)>0){
            $sql = 'update '.$this->tableName.' set '.implode(',',$UpdateValues).' where '.$this->_filter($filter);

           if($this->db->exec($sql)){
               if($this->db->affect_row()){
                    return $this->db->affect_row();
               }else{
                    return true;
               }
           }else{
                return false;
           }

        }
    }

    /**
     * delete
     *
     * @param mixed $filter
     * @access public
     * @return void
     */
    function delete($filter){
        if(method_exists($this,'pre_delete')){
            $this->pre_delete($filter);
        }
        if(method_exists($this,'post_delete')){
            $this->post_delete($filter);
        }
        $this->disabledMark = 'recycle';
        $sql = 'delete from '.$this->tableName.' where '.$this->_filter($filter);
        if($this->db->exec($sql)){
            if($this->db->affect_row()){
               return $this->db->affect_row();
            }else{
               return true;
            }
        }else{
             return false;
        }
    }

    /**
     * enable
     *
     * @param mixed $filter
     * @access public
     * @return void
     */
    function enable($filter){
        $sql = 'delete from '.$this->tableName.' where '.$this->_filter($filter);
        return $this->db->exec($sql);
    }

    /**
     * disable
     *
     * @param mixed $filter
     * @access public
     * @return void
     */
    function disable($filter){
        $sql = 'delete from '.$this->tableName.' where '.$this->_filter($filter);
        return $this->db->exec($sql);
    }

    function inputElement($params){
        $ident = md5(print_r($params['filter'],true));
        $max = 1000;
        if($params['data']){
            $this->_input[$ident] = $params['data'];
        }
        if(!isset($this->_input[$ident])){
            $this->_input[$ident] = $this->getList($this->idColumn.','.$this->textColumn,$params['filter'],0,$max,$this->_input[$ident.'_c']);
        }
        unset($params['filter']);
        $html = buildTag($params,'select',true);
        if(!$params['value']){
            $html.='<option></option>';
        }
        foreach($this->_input[$ident] as $r){
            $html.='<option value="'.$r[$this->idColumn].'"'.($r[$this->idColumn]==$params['value']?' selected="selected"':'').'>'.$r[$this->textColumn].'</option>';
        }
        if($this->_input[$ident.'_c']>$max){
            $html.='<option>更多'.$this->_input[$ident.'_c']-$max.'...</option>';
        }
        return $html.='</select>';
    }

    function &export($list){


        $colarray = $this->getColumns();
        foreach($list as $i=>$row){
            foreach($row as $k=>$v){
                if($colarray[$k]['type'] && !is_null($v) && $colarray[$k]['export'] != 'false'){
                    $modifier_key = $colarray[$k]['options']?$colarray[$k]['type'].'|'.serialize($colarray[$k]['options']):$colarray[$k]['type'];
                    $modifiers[$modifier_key][$v] = $v;
                    $list[$i][$k] = &$modifiers[$modifier_key][$v];
                }
            }
        }


        foreach($modifiers as $type=>$rows){
            $params = explode('|',$type,2);
            $options = explode(':',$params[0]);
            if(count($params)>1){
                $options['options'] = unserialize($params[1]);
            }

            $type_part = array_shift($options);;
           if(method_exists($this,$func = 'exporter_'.$type_part)){
                $this->$func($modifiers[$type],$options);
           }elseif(method_exists($this,$func = 'modifier_'.$type_part)){
                $this->$func($modifiers[$type],$options);
           }
        }

        return $list;
    }
}
?>
