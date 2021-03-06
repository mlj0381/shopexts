<?php
class ctl_menus extends adminPage{
    var $workground ='site';

    function menus(){
        $oMenus=$this->system->loadModel('content/menus');
        $this->pagedata['menus']=$oMenus->menusList();
        $this->page('content/menus.html');
    }
/*    function menusList(){
        $o=$this->system->loadModel('content/menus');
        $this->system->output(json_encode($o->menusList()));
    }*/
    function menusDel(){
        $o=$this->system->loadModel('content/menus');

        if(!$o->menusDel($this->in['id'])) {
            $this->splash('failed','index.php?ctl=content/menus&act=menus',__('对不起,菜单删除失败!'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menus',__('菜单删除成功!'));

    }

    function menusDetail(){
        $oMenus=$this->system->loadModel('content/menus');
        $this->pagedata['menus']=$oMenus->menusDetailList($this->in['id']);
        $this->pagedata['id']=$this->in['id'];
        $this->page('content/menusDetail.html');
    }
/*    function menusDetailList(){
        $o=$this->system->loadModel('content/menus');
        $this->system->output(json_encode($o->menusDetailList($this->in['id'])));
    }*/
    function menusDetailEditPage(){
        $o=$this->system->loadModel('content/menus');
        $data=$o->menusDetail($this->in['id']);
        $this->pagedata['menu_id']=$this->in['id'];
        $this->pagedata['label']=$data['label'];
        $this->pagedata['type']=$data['type'];
        $this->pagedata['res_id']=$data['res_id'];
        $this->pagedata['setting']=unserialize($data['setting']);
/*
        if($data['type']==0){
            $link=
        }elseif($data['type']==1){
            $link=
        }elseif($data['type']==2){
            $link=
        }elseif($data['type']==3){
            $link=
        }elseif($data['type']==4){
            $link=
        }elseif($data['type']==5){
            $link=
        }*/
    
        $this->page('content/menusDetailEdit.html');
    }
    function menusDetialEdit(){
        $o=$this->system->loadModel('content/menus');
        if($this->in['type']==0){
            $this->in['setting']=$this->in['link'];
        }elseif($this->in['type']==1){
            $this->in['setting']=$this->in['browser'];
        }elseif($this->in['type']==2){
            $this->in['res_id']=$this->in['product'];
        }elseif($this->in['type']==3){
            $this->in['setting']=$this->in['article'];
        }elseif($this->in['type']==4){
            $this->in['setting']=$this->in['art_cat'];
        }elseif($this->in['type']==5){
            $this->in['setting']=$this->in['tag'];
        }        
        if (!$o->menusDetialEdit($this->in)) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetailEditPage&id='.$this->in['menu_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetailEditPage&id='.$this->in['menu_id'],__('操作成功'));

    }
    function menusDetailAddPage(){
        $o=$this->system->loadModel('content/menus');
        if(empty($this->in['id'])){
            $this->in['id']=$o->menusAdd();
        }
        $this->pagedata['menu_grp_id']=$this->in['id'];
        $this->page('content/menusDetailAdd.html');
    }
    function menusDetailAdd(){
        $o=$this->system->loadModel('content/menus');
        if($this->in['type']==0){
            $this->in['setting']=$this->in['link'];
        }elseif($this->in['type']==1){
            $this->in['setting']=$this->in['browser'];
        }elseif($this->in['type']==2){
            $this->in['res_id']=$this->in['product'];
        }elseif($this->in['type']==3){
            $this->in['setting']=$this->in['article'];
        }elseif($this->in['type']==4){
            $this->in['setting']=$this->in['art_cat'];
        }elseif($this->in['type']==5){
            $this->in['setting']=$this->in['tag'];
        }        
        if (!$o->menusDetailAdd($this->in)) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetail&id='.$this->in['menu_grp_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetail&id='.$this->in['menu_grp_id'],__('操作成功'));
    }
    function menusDetailDel(){
        $o=$this->system->loadModel('content/menus');
        
        if(!$o->menusDetailDel($this->in['id'])) {
            $this->splash('failed','index.php?ctl=content/menus&act=menusDetail&id='.$this->in['menu_grp_id'],__('对不起,操作失败'));
        }
        $this->splash('success','index.php?ctl=content/menus&act=menusDetail&id='.$this->in['menu_grp_id'],__('操作成功'));
    }

    function saveInfo(){
        $o=$this->system->loadModel('content/menus');
        if($o->editDefinemenus($this->in)){
            $this->splash('success','index.php?ctl=content/menus&act=defineMenus',__('操作成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/menus&act=defineMenus',__('对不起,操作失败'));
        }
    }
    function toRemove($id){
        $o=$this->system->loadModel('content/menus');
        if($o->toRemoveDefineMenus($id,$msg)){
            $this->splash('success','index.php?ctl=content/menus&act=defineMenus',__('操作成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/menus&act=defineMenus',__('对不起,操作失败'));
        }
    }

    function doAdd(){
        $o=$this->system->loadModel('content/menus');
        if($o->addDefinemenus($this->in)){
            $this->splash('success','index.php?ctl=content/menus&act=defineMenus',__('操作成功'));
        }else{
            $this->splash('failed','index.php?ctl=content/menus&act=defineMenus',__('对不起,操作失败'));
        }
    }

}
?>
