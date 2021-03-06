<?php
/**
 * mdl_operator
 *
 * @uses modelFactory
 * @package
 * @version $Id: mdl.operator.php 1985 2008-04-28 06:36:02Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Likunpeng <leoleegood@zovatech.com>
 * @license Commercial
 */
include_once('shopObject.php');
class mdl_operator extends shopObject{

    var $idColumn = 'op_id';
    var $textColumn = 'username';
    var $defaultCols = 'username,name,lastlogin,status,department';
    var $adminCtl = 'admin/operator';
    var $defaultOrder = array('op_id', 'DESC');
    var $tableName = 'sdb_operators';

    function getColumns(){ 
        return array(
            'op_id'=>array('label'=>'ID','class'=>'span-1'),    /* 管理员id */
            'username'=>array('label'=>__('用户名'),'class'=>'span-3'),    /* 管理员登陆名 */
            'op_no'=>array('label'=>__('编号'),'class'=>'span-1'),    /* 管理员编号 */
            'name'=>array('label'=>__('姓名'),'class'=>'span-3'),    /* 管理员姓名 */
            'status'=>array('label'=>__('状态'),'class'=>'span-2','type'=>'status'),    /* 状态(0不可用 1可用) */
            'department'=>array('label'=>__('部门'),'class'=>'span-2'),
            'super'=>array('label'=>__('超级管理员'),'class'=>'span-2','type'=>'bool'),    /* 是否是总管理员 */
            'lastlogin'=>array('label'=>__('最后登陆时间'),'class'=>'span-3','type'=>'time','readonly'=>1),    /* 最后登陆时间 */
            //'lastip'=>array('label'=>'最后登陆ip','class'=>'span-3','readonly'=>1),    /* 最后登陆时间 */
            'logincount'=>array('label'=>__('登陆次数'),'class'=>'span-3','readonly'=>1),    /* 最后登陆时间 */
            'roles'=>array('label'=>__('角色'),'class'=>'span-7','readonly'=>1,'type'=>'roles'),
            'memo'=>array('label'=>__('备注'),'class'=>'span-7','readonly'=>1),
        );
    }

    function modifier_status(&$rows){
        foreach($rows as $k=>$v){
            $rows[$k] = $v?__('启用'):__('禁用');
        }
    }

    function getList($cols,$filter='',$start=0,$limit=20,&$count,$orderType=null){
        return parent::getList( str_replace('roles','op_id as roles',$cols) ,$filter,$start,$limit,$count,$orderType);
    }

    function modifier_roles(&$rows){
        $role_list = $this->db->select('select l.op_id,r.role_name 
            from sdb_lnk_roles l 
            left join sdb_admin_roles r on r.role_id=l.role_id
            where l.op_id in('.implode(',',$rows).') and r.disabled!="true"');
        $rst = array();
        foreach($role_list as $r){
            $rst[$r['op_id']][] = $r['role_name'];
        }
        foreach($rows as $k=>$r){
            $rows[$k] = is_array($rst[$k])?(implode(',',$rst[$k])):'';
        }
    }

    function delete($filter,$current_op_id=false){
        if(method_exists($this,'pre_delete')){
            $this->pre_delete($filter);
        }
        if(method_exists($this,'post_delete')){
            $this->post_delete($filter);
        }
        $this->disabledMark = 'normal';
        $sql = 'delete from '.$this->tableName.' where '.$this->_filter($filter);
        if($current_op_id){
            $sql.=' and op_id != '.intval($current_op_id);
        }
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

    function getUsedRoles($op_id){
        $rows = $this->db->select('select role_id from sdb_lnk_roles where op_id='.intval($op_id));
        foreach($rows as $r){
            $rtn[$r['role_id']] = $r['role_id'];
        }
        return $rtn;
    }

    /**
     * tryLogin
     *
     * @param mixed $aValue
     * @access public
     * @return array
     */
    //+
    function updateReadTime($op_id,$update){
        $sys_config=$this->db->selectRow('SELECT config FROM sdb_operators where op_id="'.$op_id.'"');
        if($sys_config['config']){
            $sys_config=unserialize($sys_config['config']);
            if($sys_config[$update[0]]){
                $sys_config[$update[0]]=$update[1];
                $combind=serialize($sys_config);
            }else{
                $combind=serialize(array_merge(array($update[0]=>$update[1]),$sys_config));
            }
            $sql = "update sdb_operators set config='".$combind."' where op_id=".$op_id;
            return $this->db->exec($sql);
        }
    }

    function update($data,$filter){
        if(isset($data['userpass'])){
            $data['userpass'] = md5($data['userpass']);
        }

        $c = parent::update($data,$filter);

        if(!isset($data['roles'])){
            return $c;
        }

        if($filter['op_id']){
            $op_id = array();
            foreach($this->getList('op_id',$filter) as $r){
                $op_id[] = $r['op_id'];
            }
        }else{
            $op_id = $filter['op_id'];
        }

        if(count($op_id)==1){
            $rows = $this->db->select('select role_id from sdb_lnk_roles where op_id in ('.implode(',',$op_id).')');
            $in_db = array();
            foreach($rows as $r){
                $in_db[] = $r['role_id'];
            }
            $to_add = array_diff($data['roles'],$in_db);
            $to_del = array_diff($in_db,$data['roles']);

            if(count($to_add)>0){
                $sql = 'INSERT INTO `sdb_lnk_roles` (`op_id`,`role_id`) VALUES ';
                foreach($to_add as $role_id){
                    $actions[] = "({$op_id[0]},$role_id)";
                }
                $sql .= implode($actions,',').';';
                $a = $this->db->exec($sql);
            }

            if(count($to_del)>0){
                $this->db->exec('delete from sdb_lnk_roles where role_id in ('.implode(',',$to_del).') and op_id='.intval($op_id[0]));
            }
        }else{
        }

        return $c;
    }

    function insert($data){
        $data['userpass'] = md5(trim($data['userpass']));
        $op_id = parent::insert($data);
        if($op_id && is_array($data['roles'])){
            $sql = 'INSERT INTO `sdb_lnk_roles` (`op_id`,`role_id`) VALUES ';
            foreach($data['roles'] as $role_id){
                $roles[] = "($op_id,$role_id)";
            }
            $sql .= implode($roles,',').';';
            $a = $this->db->exec($sql);
        }
        return $op_id;
    }

    function getConfig($op_id){
        $sys_config=$this->db->selectRow('SELECT config FROM sdb_operators where op_id="'.$op_id.'"');
        return $sys_config['config'];
    }

    function tryLogin($aValue){    
        if($aValue['passwd']=='+_-_-_+'){
            $aValue['passwd']='';
        }
        return $this->db->selectrow("SELECT * FROM sdb_operators
            WHERE username = '{$aValue['usrname']}' AND userpass = '".md5($aValue['passwd'])."' AND status = '1' AND disabled='false'" );

    }

    function doLogin($aValue){
        return $this->db->selectrow("SELECT * FROM sdb_operators
            WHERE username = '".$aValue['usrname']."' AND userpass = '".$aValue['passwd']."' AND status = '1' AND disabled='false'" );    
    }

    /**
     * updateLogin
     *
     * @access public
     * @return array
     */
    //+
    function updateLogin($ars){
        if($ars){
            $aTemp['Lastlogin'] = time();
            $rs = $this->db->query("SELECT * FROM sdb_operators WHERE op_id=".$ars['op_id']."");
            $sql = $this->db->GetUpdateSql($rs,$aTemp);
            if(!$sql || $this->db->exec($sql)){
                return true;
            }else{
                return false;    
            }
        }else
            return false;
    }

    /**
     * getOpCfg
     *
     * @access public
     * @return array
     */
    function getOpCfg($opId,$getRoleId=true){
        if(!$opId) return false;
        $aConfig = array();
        $aTemp = $this->db->selectrow("SELECT role_id, config FROM sdb_operators WHERE op_id=".$opId);
        if($aTemp){
            $aConfig = unserialize($aTemp['config']);
            if($getRoleId)
                $aConfig['role_id'] = $aTemp['role_id'];
        }
        return $aConfig;
    }


    /**
     * toUpdateSelf
     *
     * @param mixed $aValue,$aSetting
     * @access public
     * @return array
     */
    //+
    function toUpdateSelf($aValue,$aSetting){
        $aSetting['lang'] = $aValue['language'];
        $aSetting['timezone'] = $aValue['timezone'];
        $aValue['config'] = $aSetting;
        if(isset($aValue['userpass'])){
            $aValue['userpass'] = md5($aValue['userpass']);
        }

        $aRs = $this->db->query("SELECT * FROM sdb_operators WHERE op_id=".$aValue['op_id']);
        $sSql = $this->db->GetUpdateSql($aRs,$aValue);
        return !$sSql || $this->db->exec($sSql);
    }

    /**
     * chackOldPass
     *
     * @param mixed $sPass
     * @param int $operatorId
     * @access public
     * @return boolean
     */
    function chackOldPass($sPass,$operatorId){
        if(is_numeric($operatorId)){
            $oldPass = $this->db->selectrow("SELECT userpass FROM sdb_operators WHERE op_id=".$operatorId);
            if(md5($sPass) != $oldPass['userpass']){
                return true;
            }else{
                return false;
            }
        }        
    }

    /**
     * checkValue
     *
     * @param string $sValue
     * @param int $nId    default false
     * @access public
     * @return array
     */
    function checkValue($sValue,$nId=false){
        if($nId)
            return $this->db->selectrow("SELECT username FROM sdb_operators WHERE username='".$sValue."' AND op_id!=".$nId." AND disabled='false'");
        else
            return $this->db->selectrow("SELECT username FROM sdb_operators WHERE username='".$sValue."' AND disabled='false'");
    }

    /**
     * checkValue2
     *
     * @param string $sValue

     * @access public
     * @return array
     */
    function checkValue2($sValue){
        return $this->db->selectrow("SELECT username FROM sdb_operators WHERE username='".$sValue."' AND disabled='true'");
    }


    /**
     * toSearch
     *
     * @param string $sStr
     * @access public
     * @return boolean
     */
    function toSearch($sStr){
        return $this->db->select("SELECT op_id AS id,username,name,status,Lastlogin FROM sdb_operators
            WHERE super = 0 AND username LIKE '%".$sStr."%' ORDER BY op_id",0,null,PERPAGE,'searchOptList');
    }

    function check_role($op_id,$workground){
        if(!$workground)return true;

        $role = &$this->system->loadModel('admin/adminroles');
        $opt = $role->rolemap();
        $r = $this->db->selectrow('SELECT a.action_id
            FROM sdb_lnk_roles s
            INNER JOIN sdb_lnk_acts a ON a.role_id=s.role_id
            where op_id='.intval($op_id).' and action_id='.intval($opt[$workground]));
        return $r;
    }

    function &getActions($op_id){
        if(!isset($this->actmap[$op_id])){
            $allow_wground = array();
            $sql = 'SELECT distinct(a.action_id)
                FROM sdb_lnk_roles s
                    INNER JOIN sdb_admin_roles r ON r.role_id=s.role_id AND r.disabled!="true"
                    LEFT JOIN sdb_lnk_acts a ON a.role_id=r.role_id
                    where s.op_id='.intval($op_id);
            foreach($this->db->select($sql) as $r){
                        $allow_wground[$r['action_id']] = $r['action_id'];
                    }
            $this->actmap[$op_id] = &$allow_wground;
        }
        return $this->actmap[$op_id];
    }
}
?>
