<?php
define('MNU_LINK',0);
define('MNU_BROWSER',1);
define('MNU_PRODUCT',2);
define('MNU_ARTICLE',3);
define('MNU_ART_CAT',4);
define('MNU_TAG',5);
define('MNU_CTL_ACT',6);

class mdl_menus extends modelFactory{



    function addNew($label,$type,$setting,$enabled=0,$required_lv=0,$before_menu_id=null){
        $data=array(
            'label'=>$label,
            'type'=>$type,
            'res_id'=>$setting['id'],
            'enabled'=>$enabled,
            'required_lv'=>$required_lv
            );
        unset($setting['id']);
        $data['setting'] = serialize($setting);

        if($before_menu_id){
            $rs = $this->db->exec('SELECT * FROM sdb_menus WHERE menu_id='.intval($before_menu_id));
            $rows = $this->db->getRows($rs);
            $data['order_num'] = intval($rows[0]['order_num'])+1;
        }else{
            $rs = $this->db->exec('SELECT * FROM sdb_menus WHERE 0=1');
        }

        $sql = $this->db->getInsertSQL($rs,$data);
        $rs = null;
        return $this->db->exec($sql);
    }
    
    function editMenu($id,$label,$type,$setting,$enabled=0,$required_lv=0,$before_menu_id=null){
        $data=array(
            'label'=>$label,
            'type'=>$type,
            'res_id'=>$setting['id'],
            'enabled'=>$enabled,
            'required_lv'=>$required_lv
            );
        unset($setting['id']);
        $data['setting'] = serialize($setting);
        if($before_menu_id){
            $rows = $this->db->selectrow('SELECT * FROM sdb_menus WHERE menu_id='.intval($before_menu_id));
            $data['order_num'] = intval($rows['order_num'])+1;
        }
        $rs = $this->db->exec('SELECT * FROM sdb_menus WHERE menu_id = '.$id);
         $sql = $this->db->GetUpdateSQL($rs,$data);
        $rs = null;
        return $this->db->exec($sql);
    }
    
    function removeMenu($strId=''){
        if ($strId != ''){
            return $this->db->query('DELETE FROM sdb_menus WHERE menu_id in ('.$strId.')');
        }else{
            return false;
        }
    }

    function getList($grp_id='default'){
        if($grp_id=='goods_cat'){//取一级商品类
            $group=$this->db->select('select cat_name as label,cat_id as res_id,'.MNU_BROWSER.' as type from sdb_goods_cat where parent_id IS NULL');
        }elseif($grp_id=='article_cat'){//取文章类
            $group=$this->db->select('select cat_info as label,cat_id as res_id,'.MNU_ART_CAT.' as type from sdb_article_cat');
        }else{
            $group = $this->db->select("SELECT menu_id AS id,label, type, setting, res_id, enabled, required_lv, order_num FROM sdb_menus WHERE menu_grp_id={$grp_id} ORDER BY menu_grp_id,order_num DESC");
        }
        foreach($group as $menu){
            switch($menu['type']){
                case MNU_LINK://    任意连接
                    $menu['setting'] = unserialize($menu['setting']);
                    $menus[] = array('label'=>$menu['label'],'link'=>$menu['setting']['link']);
                    break;

                case MNU_BROWSER://            goods_cat
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array($menu['res_id'])));
                    break;

                case MNU_PRODUCT:
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl('product','index',array($menu['res_id'])));
                    break;

                case MNU_ARTICLE:
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl('article','index',array($menu['res_id'])));
                    break;

                case MNU_ART_CAT:
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl('artlist','index',array($menu['res_id'])));
                    break;

                case MNU_TAG:
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl('tags','index',array($menu['res_id'])));
                    break;

                case MNU_CTL_ACT:
                    $menu['setting'] = unserialize($menu['setting']);
                    $menus[] = array('label'=>$menu['label'],'link'=>$this->system->mkUrl($menu['setting']['ctl'],$menu['setting']['act'],$menu['setting']['args']));
                    break;

                default:
                    $menus[] = array('label'=>$menu['label'],'link'=>$menus['type'].':'.$menus['res_id']);
                }
        }
            
        return $menus;    
    }
    
    /**
     * getFieldById 
     * 
     * @param string $fieldname 
     * @param int $id 
     * @access public
     * @return void
     */
    function getFieldById($fieldname='', $id=0) {
        $sqlString = "SELECT ".$fieldname." FROM sdb_menus
                WHERE menu_id = ".intval($id);
        return $this->db->selectrow($sqlString);
    }
    
    function editRank($aData, $nId) {
        $rArticle = $this->db->query('SELECT * FROM sdb_menus WHERE menu_id='.$nId);
        $sSql = $this->db->GetUpdateSql($rArticle, $aData);
        if (!$sSql || $this->db->exec($sSql)) {
            return true;
        } else {
            return false;
        }
    }

    //wzp
    function getMenuGroup(){//取菜单组
        $data=$this->db->select('select menu_grp_id from sdb_menus group by menu_grp_id');
        $data[]=array('menu_grp_id'=>'article_cat');
        $data[]=array('menu_grp_id'=>'goods_cat');
        return $data;
    }
    function menusList(){
        $menus=$this->getMenuGroup();
        for($i=0;$i<count($menus);$i++){
            $groupID=$menus[$i]['menu_grp_id'];
            $data[$groupID]=$this->getList($groupID);
        }
        return $data;
    }
    function menusAdd(){
        $max=$this->db->selectrow('select max(menu_grp_id) as max_id from sdb_menus');
        if(!empty($max['max_id'])){
            return ++$max['max_id'];
        }else{
            return 0;
        }
        
    } 
    function menusDel($id){
        return $this->db->exec('delete from sdb_menus where  menu_grp_id='.$id);
    }

    function menusDetailList($id){
        return $this->db->select('select * from sdb_menus where menu_grp_id='.$id);
    }
    function menusDetail($id){
        return $this->db->selectrow('select * from sdb_menus where menu_id='.$id);
    }
    function menusDetialEdit($data){
        $data['setting']=serialize($data['setting']);
        $rs=$this->db->query('select * from sdb_menus where menu_id='.$data['menu_id']);
        unset($data['menu_id']);
        $sql=$this->db->GetUpdateSQL($rs,$data);
        if(!$sql || $this->db->query($sql)){
            return true;
        }else{
            return false;
        }
    }
    function menusDetailAdd($data){
        $data['setting']=serialize($data['setting']);
        $rs = $this->db->query('select * from sdb_menus WHERE 0=1');
        $sql= $this->db->GetInsertSQL($rs,$data);
        if(!$sql || $this->db->query($sql)){
            return true;
        }else{
            return false;
        }
    }
    function menusDetailDel($id){
        return $this->db->exec('delete from sdb_menus where menu_id='.$id);
    }


    function getnavTreeData($table,$id=0){//user by widgets
        if($id){
            $sql=' where cat_path like "%,'.$id.',%"';
        }else{
            $sql='';
        }
        //file_put_contents('d:/php/index.php','select * from '.$table.$sql);
        $list = $this->db->select('select * from '.$table.$sql);
        if($id>0){
            foreach($list as $k=>$m){
                $list[$k]['link'] = $this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array($m['cat_id'],$this->lnkStr($m['filter'])));
            }
            return $list;
        }else{ //实目录 : 商品分类，*文章分类
            foreach($list as $k=>$m){
                $list[$k]['link'] = $this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array($m['cat_id']));
            }
            return $list;
        }
    }
    function lnkStr($filter){
        parse_str($filter,$output);
        if($output['brand']){
            $b='b';
            for($i=0;$i<count($output['brand']);$i++){
                $b.=','.$output['brand'][$i];
            }
        }
        if($output['tag']){
            $t='t';
            for($i=0;$i<count($output['tag']);$i++){
                $t.=','.$output['tag'][$i];
            }
        }
        if($t){
            $str=$b.'_'.$t;
        }else{
            $str=$b;
        }
        return $str;
    }
    function makeNavTree($data,$sID=0){//user by widgets
        global $step;
        $step++;
        if($step==4){
            $step--;
            return $cat;
        }
        for($i=0;$i<count($data);$i++){
            $id=$data[$i]['cat_id'];
            $pid=$data[$i]['parent_id'];
            $data[$i]['step']=$step;
            if(!$sID){ //第一轮圈套
                if(empty($pid)){ //原始节点
                    $data[$i]['sub']=$this->makeNavTree($data,$id);
                    $cat[]=$data[$i];
                    
                }else{ //
                    continue;
                }
            }else{ //子节点
                if($sID==$pid){
                    $data[$i]['sub']=$this->makeNavTree($data,$id);
                    $cat[]=$data[$i];
                }
            }
        }
        $step--;
        return $cat;
    }
    function getNavTreeByParent_id($id=0){
        return $this->db->select('select * from sdb_define_menus where parent_id='.$id);
    }
    function addDefinemenus($data){
        $rs = $this->db->query('select * from sdb_define_menus WHERE 0=1');
        $sql= $this->db->GetInsertSQL($rs,$data);
        if(!$sql || $this->db->query($sql)){
            if($data['parent_id']){
                $tmp=$this->getDefineMenusById($data['parent_id']);
                $temp=explode(',',substr($tmp['cat_path'],1,-1));
            }
            $cat_id=$this->db->lastInsertId();
            $temp[]=$cat_id;
            $cat_path=','.implode(',',$temp).',';
            $this->editDefinemenus(array('cat_id'=>$cat_id,'cat_path'=>$cat_path));
            return true;
        }else{
            return false;
        }
    }
    function editDefinemenus($data){
        $rs= $this->db->query('SELECT * FROM sdb_define_menus WHERE cat_id='.$data['cat_id']);
        $sql = $this->db->GetUpdateSql($rs, $data);
        if (!$sql || $this->db->exec($sql)) {
            return true;
        } else {
            return false;
        }
    }
    function getDefineMenusById($id){
        return $this->db->selectrow('select * from sdb_define_menus where cat_id='.$id);
    }
    function getDefineMenus(){
        return $this->db->select('select * from sdb_define_menus');
    }
    function toRemoveDefineMenus($id,&$msg){
        $aData = $this->db->select('SELECT cat_id FROM sdb_define_menus WHERE parent_id = '.$id);
        if(count($aData) > 0){
            $msg=__('该目录下还有子目录，请把子目录都删除光，才能删除该目录');
            return false;
        }
        return $this->db->exec('delete from sdb_define_menus where cat_id='.$id);
    }


    function makeDefineMenusTree($data,$sID=0){
        global $step,$cat;
        $step++;
        for($i=0;$i<count($data);$i++){
            $id=$data[$i]['cat_id'];
            $pid=$data[$i]['parent_id'];
            $data[$i]['step']=$step;
            if(!$sID){ //第一轮圈套
                if(empty($pid)){ //原始节点
                    $cat[]=$data[$i];
                    $this->makeDefineMenusTree($data,$id);
                }else{ //
                    continue;
                }
            }else{ //子节点
                if($sID==$pid){
                    $cat[]=$data[$i];
                    $this->makeDefineMenusTree($data,$id);
                }
            }
        }
        $step--;
        return $cat;
    }
}

?>
