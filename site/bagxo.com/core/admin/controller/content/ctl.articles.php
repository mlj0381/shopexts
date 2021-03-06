<?php
include_once('objectPage.php');
class ctl_articles extends objectPage{

    var $name='文章';
    var $workground ='site';
    var $object = 'content/article';
    var $actionView = 'content/article/finder_action.html'; //默认的动作html模板,可以为null
    var $filterView = 'content/article/finder_filter.html'; //默认的过滤器html,可以为null
    var $allowImport =false;
    var $allowExport = false;
    
    function index($node_id){
        $this->pagedata['node_id'] = $node_id;
      
        $oArticle = $this->system->loadModel('content/article');

        parent::index(array('params'=>array('node_id'=>$node_id)));
    }

    function addArticle($node_id){
        if($_POST['ifpub']) $_POST['ifpub'] = 1;
        else $_POST['ifpub'] = 0;
        $oArticle = $this->system->loadModel('content/article');
        if(!$oArticle->addArticle($_POST,$msg)){
            $this->splash('failed','index.php?ctl=content/articles&act=index&p[0]='.$node_id,$msg);
        }
        $this->splash('success','index.php?ctl=content/articles&act=index&p[0]='.$node_id,__('文章添加成功'));
    }

    function detail($nConId){

        $oArticle = $this->system->loadModel('content/article');
        $sitemap = $this->system->loadModel('content/sitemap');
        $this->pagedata['article'] = $oArticle->get($nConId);
        //$this->pagedata['path'] = $sitemap->getPathById($this->pagedata['article']['node_id'],false);
        $this->pagedata['node_id'] = $this->pagedata['article']['node_id'];
        $this->pagedata['article_cat'] = $oArticle->getArticleCat();

        $this->pagedata['article_id'] = $nConId;
        $this->setView('content/article/article.html');
        $this->output();
    }
    
    function addNew($node_id){
        $this->path[] = array('text'=>'添加文章');
        $sitemap = $this->system->loadModel('content/sitemap');
        $this->pagedata['node_id'] = $node_id;
        //$this->pagedata['path'] = $sitemap->getPathById($node_id,false);
        //print_r($node_id);

        $oArticle = $this->system->loadModel('content/article');
        $this->pagedata['article_cat'] = $oArticle->getArticleCat();
        $this->page('content/article/article.html');
    }
    
    function edit($article_id,$node_id){

        $this->path[] = array('text'=>'编辑文章');
        $sitemap = $this->system->loadModel('content/sitemap');
        $this->pagedata['node_id'] = $node_id;
        $this->pagedata['path'] = $sitemap->getPathById($node_id,false);

        $oArticle = $this->system->loadModel('content/article');
        $this->pagedata['article'] = $oArticle->get($article_id);

        $oArticle = $this->system->loadModel('content/article');
        $this->page('content/article/article.html');
    }

    function save($article_id,$node_id){
        if($_POST['ifpub']) $_POST['ifpub'] = 1;
        else $_POST['ifpub'] = 0;
        $this->begin('index.php?ctl=content/articles&act=detail&p[0]='.$article_id);
        $oArticle = $this->system->loadModel('content/article');
        $this->end($oArticle->saveArticle($_POST),__('文章保存成功'));
    }
}
?>
