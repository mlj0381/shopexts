<?php
class ctl_artlist extends shopPage{

    var $type='articles';

    function index($cat_id,$page=1) {
        $this->id=array('node_id'=>$cat_id);
        if(intval($cat_id)){
            $objSitemap = $this->system->loadModel('content/sitemap');
            $filter['node_id'] = intval($cat_id);
            $aInfo = $objSitemap->getPathById($filter['node_id'],false);
            foreach($aInfo as $r){
                if($r['node_id'] == $filter['node_id']){
                    $this->pagedata['cat_name'] = $r['title'];
                    break;
                }
            }
        }
        $filter['ifpub'] = 1;

        if($this->system->getConf('system.seo.noindex_catalog'))
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $pageLimit = 20;
        $objArticle = $this->system->loadModel('content/article');
        $this->pagedata['articles'] = $objArticle->getList('title,article_id,uptime',$filter,($page-1)*$pageLimit,$pageLimit,$count);
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>floor($count/$pageLimit)+1,
                'link'=>$this->system->mkUrl('artlist','index',array($cat_id,($tmp = time()))),'token'=>$tmp);
        
        if($page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        $this->path[]=array('title'=>'');
        $this->title= $this->pagedata['cat_name'];

        $this->output();
    }
}
?>
