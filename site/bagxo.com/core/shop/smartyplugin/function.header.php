<?php
function smarty_function_header($params, &$smarty)
{
    $system = &$GLOBALS['system'];
    $data['title'] = $smarty->_tpl_vars['title'];
    if($GLOBALS['runtime']['query']=='index.html' && $system->getConf('system.index_title')){
        $data['title'] =  $system->getConf('system.index_title');
    }
    $data['headers'] = $system->ctl->header;
    $data['base_url'] = $system->base_url();

    $shop=array('set'=>array());    
    $shop['set']['path'] = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/') + 1);
    $shop['set']['buytarget']=$system->getConf('site.buy.target');    //ajax加入购物车
    $shop['set']['dragcart']=$system->getConf('ux.dragcart');;    //拖动购物
    $shop['set']['refer_timeout']=$system->getConf('site.refer_timeout');;    //refer过期时间
    $shop['url']['addcart'] = $system->mkUrl('cart','ajaxadd');
    $shop['url']['shipping'] = $system->mkUrl('cart','shipping');
    $shop['url']['payment'] = $system->mkUrl('cart','payment');
    $shop['url']['total'] = $system->mkUrl('cart','total');
    $shop['url']['viewcart'] = $system->mkUrl('cart','view');
    $shop['url']['ordertotal'] = $system->mkUrl('cart','total');
    $shop['url']['applycoupon'] = $system->mkUrl('cart','applycoupon');
    $shop['url']['product_diff'] = $system->mkUrl('product','diff');
    $data['shopDefine'] = json_encode($shop);
    $output = &$system->loadModel('system/frontend');
    $theme_dir = $system->base_url().'themes/'.$output->theme;

    $data['theme_dir']=$theme_dir;
    if(defined('DEBUG_JS') && DEBUG_JS){
        $data['scripts'] = find(BASE_DIR.'/statics/headjs','js','statics/headjs');
    }elseif(defined('GZIP_JS') && GZIP_JS){
        $data['scripts'] = array('statics/head.jgz');
    }else{
        $data['scripts'] = array('statics/head.js');
    }
    $smarty->_smarty_include(array(
        'smarty_include_tpl_file' =>'shop:common/header.html', 
        'smarty_include_vars' =>$data,
    ));
}
?>
