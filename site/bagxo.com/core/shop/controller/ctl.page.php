<?php
class ctl_page extends shopPage{

    var $_call='display';

    function display($ident) {
        $ident = urldecode($ident);
        $page = &$this->system->loadModel('content/systmpl');
        $this->pagedata['page'] = 'page:'.$ident;
        $this->pagedata['_MAIN_'] = 'page/single-page.html';
        $title=$page->getTitle($ident);
        if($title){
            foreach($title as $k=>$v){
                $uLink=explode(":",$title[$k]['link']);
                $this->path[]=array('title'=>$title[$k]['title'],'link'=>$this->system->mkUrl('page',$uLink[1]));
            }
        }
        //if($ident!='index') $this->path[]=$page->getTitle($ident);
        $this->output();
    }

    function error($errArr){
        $this->pagedata['error'] = $errArr;
        $this->pagedata['_MAIN_'] = 'page/error.html';
        $this->output();
    }
}
?>
