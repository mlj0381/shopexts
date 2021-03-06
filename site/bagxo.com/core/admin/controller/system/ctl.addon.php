<?php
class ctl_addon extends adminPage{

    var $workground = 'tools';

    function ctl_addon(){
        $this->path[] = array('text'=>'工具箱','url'=>'index.php?ctl=system/tools&act=welcome');
        $this->path[] = array('text'=>'网店扩展');
        parent::adminPage();
    }

    function plugin($type='payment'){
        $_GET['p'][0] = $type;
        $this->pagedata['allow_disable'] = false;
        $model = &$this->system->loadModel('system/addons');

        $tpList = $model->getType();
        $this->path[] = array('text'=>'插件');
        $this->path[] = array('text'=>$tpList[$type]['text']);
        $model->plugin_type = $tpList[$type]['type'];
        $model->prefix = $tpList[$type]['prefix'];
        $model->plugin_name = $type;
        $model->plugin_case = $tpList[$type]['case'];

        $this->pagedata['type'] = &$tpList;
        $list =  $model->getList(null,null,true);

        $this->pagedata['items'] = &$list;
        $this->pagedata['infoPage'] = "system/addons/{$_GET['act']}-{$_GET['p'][0]}.html";
        $this->page('system/addons/page.html');
    }

    function widget(){
        $this->path[] = array('text'=>'板块');
        $model = $this->system->loadModel('content/widgets');
        $items = $model->getLibs();
        foreach($items as $key=>$item){
            $items[$key]['name'] = $item['label'];
            $items[$key]['file'] = 'plugins/widgets/'.$key;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['allow_disable'] = false;
        $this->pagedata['infoPage'] = "system/addons/widgets.html";
        $this->page('system/addons/page.html');
    }

    function package(){
        $this->path[] = array('text'=>'功能包');
        $this->pagedata['allow_disable'] = true;
        $this->page('system/addons/page.html');
    }

}
?>
