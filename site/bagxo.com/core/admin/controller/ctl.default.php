<?php
class ctl_default extends adminPage{

    function index(){
        
        $this->pagedata['statusId'] = $this->system->getConf('shopex.wss.enable');
        if(!IN_AJAX){
            
            $lang = $_GET['_lang']?$_GET['_lang']:$this->op->get('lang');
            $lang = $lang?$lang:'zh_CN';

            $setting = array('lang'=>$lang);

            foreach($_GET as $k=>$v){
                
                if(substr($k,0,1)=='_' && strlen($k)>1){
                    $setting[substr($k,1)] = $v;
                }
            }
            $this->op->load();
            if(defined('SAAS_MODE')&&SAAS_MODE){
                $this->pagedata['saas_mode'] = true;
                $saas = $this->system->loadModel('service/saas');
                if($shopinfo = $saas->native_svc('host.getinfo',array('host_id'=>HOST_ID))){
                    if($shopinfo['response_code']>0){
                        $this->pagedata['shop_service_info'] = $shopinfo['response_error'];
                    }else{
                        $this->pagedata['shop_service_info'] .= $shopinfo['service_name'];
                        $this->pagedata['shop_service_info'] .= $shopinfo['status']=='tryout'?'(试用)':'';
                        $this->pagedata['shop_service_info'] .= '['.date('y/m/d',$shopinfo['add_time'])
                                                        .'-'.date('y/m/d',$shopinfo['finish_time']).']';
                    }
                }
            }

            $titlename = $this->system->getConf('system.shopname');
            $this->pagedata['title'] = $titlename.' - Powered By ShopEx';
            $this->pagedata['shopname'] = (empty($titlename) ? "点此设置商店名称" : $titlename);
            $this->pagedata['session_id'] = $this->system->session->sess_id;
            $this->pagedata['shopadmin_dir']=dirname($_SERVER['PHP_SELF']).'/';
            $this->pagedata['shop_base']=$this->system->base_url();
            $this->pagedata['setting_query'] = http_build_query($setting).'&_lang='.$lang;
            $this->pagedata['uname'] = $this->op->name?$this->op->name:$this->op->loginName;
               
            include_once('adminSchema.php');
            $this->pagedata['menu'] = $this->op->getMenu(null,$this->op->is_super);
            $this->_fetchM($this->pagedata['menu'],$menus,array());
            $menus = array_values($menus);
      
            foreach($menus as $i=>$m){
                foreach($menus[$i]['key'] as $k=>$v){
                    $mkey[]=array($k,$i);
                }
                unset($menus[$i]['key']);
            }

            $i = count($menus);
            foreach($mlist as $k=>$v){
                $menus[$i] = $v;
                $mkey[] = array($k,$i);
                $i++;
            }
            $this->pagedata['guide']=$this->system->getConf('system.guide');
            
            $this->pagedata['scripts'] = find(dirname($_SERVER['SCRIPT_FILENAME']).'/js','js');
            $this->pagedata['mlist'] =array('menus'=>&$menus,'key'=>&$mkey);
            $this->setView('index.html');
            $this->output();
        }else{
            $this->system->error(401);
        }
    }

    function node($model,$pid){
        $model = $this->system->loadModel($model);
        $nodes = $model->getNodes($pid);
        echo json_encode($nodes);
        exit();
    }
    
    function _fetchM($menu,&$arr,$p){
        foreach($menu as $m){
            if($m['link']){
                if(isset($arr[$m['link']])){
                    $arr[$m['link']]['key'][$m['label']]=1;
                }else{
                    $arr[$m['link']] = array('link'=>$m['link'],'path'=>((count($p)>0?implode('/',$p).'/':'').$m['label']),'key'=>array($m['label']=>1));
                }
                if($m['keywords']){
                    foreach($m['keywords'] as $k){
                        $arr[$m['link']]['key'][$k]=1;
                    }
                }
            }
            if($m['items']){
                $np = array_slice($p,0);
                $np[]=$m['label'];
                $this->_fetchM($m['items'],$arr,$np);
            }
        }
    }

    function task(){
        $t = $this->system->loadModel('utility/task');
        $t->run();
    }

    function tnode($model,$id,$depth){
        $o = $this->system->loadModel($model);
        $this->pagedata['item'] = $options = $o->treeOptions();
        $this->pagedata['item']['items']=$o->getNodes($id);
        $this->pagedata['item']['model']=$model;
        $this->pagedata['depth'] = $depth+1;
        $this->setView('treeNode.html');
        $this->output();
    }

    function uploadSplash(){
        foreach($_POST as $k=>$v) {
            if ($v=='null') {
                unset($_POST[$k]);
            }
        }
        echo '<script>top.$("loadMask").hide();top.MODALPANEL.hide();</script>';
        call_user_func_array(array(&$this,'splash'),$_POST);
    }

    function saveNote(){
    }

    function status(){
        $this->system->session->close(true);

        $operate = $this->system->loadModel('admin/operator');
        $config=unserialize($operate->getConfig($this->op->opid));

        $oOrder = $this->system->loadModel('trading/order');
        $filter['status'] = 'active';
        $filter['pay_status'] = array(0);
        $filter['ship_status'] = array(0);
        $filter['confirm'] = 'N';
        if($config['ordertime']){
            $filter['createtime'] = $config['ordertime'];
        }
        $oOrder->getList('', $filter, 0, 1000, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=order/order&act=index&p[0]=admin\')">实时提醒：最新订单'.$count.'个</li>';
        }
        unset($filter);
        $oNotify = $this->system->loadModel('goods/goodsNotify');
        $filter['status'] = 'ready';
        
        if($config['notifytime']){

            $filter['notifytime'] = $config['notifytime'];
        }
        

        $oNotify->getList('', $filter, 0, 1000, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=goods/gnotify&act=index&p[0]=admin\')">最新缺货登记'.$count.'条</li>';
        }
        unset($filter);

        $oProduct = $this->system->loadModel('goods/finderPdt');
        $filter_p['store_alarm'] = $this->system->getConf('system.product.alert.num');
        foreach($oProduct->getList('goods_id', $filter_p, 0, 1000) as $row){
            $filter['goods_id'][] = $row['goods_id'];
        }
        if(empty($filter['goods_id'])) $filter['goods_id'][] = -1;
        unset($filter_p);
        $oGoods = $this->system->loadModel('goods/products');
        $oGoods->getList('', $filter, 0, -1, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=goods/product&act=goodsAlert\',{data:\'a=a\',method:\'post\'})">最新库存报警'.$count.'条</li>';
        }
        unset($filter);
        
        $oDiscuss = $this->system->loadModel('comment/discuss');
        $filter['adm_read_status'] = 'false';
        $oDiscuss->getList('', $filter, 0, 1000, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=goods/discuss&act=index\')">最新商品评论'.$count.'条</li>';
        }
        unset($filter);
        $oGask = $this->system->loadModel('comment/gask');
        $filter['adm_read_status'] = 'false';
        $oGask->getList('', $filter, 0, 1000, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=member/gask&act=index\')">最新购买咨询'.$count.'条</li>';
        }
        unset($filter);
        $oBBS = $this->system->loadModel('resources/shopbbs');
        $filter['unread'] = 0;
        $oBBS->getList('', $filter, 0, 1000, $count);
        if($count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=member/shopbbs&act=index\')">最新商店留言'.$count.'条</li>';
        }
        $oShopbbs = $this->system->loadModel('resources/shopbbs');
        $order_message=$oShopbbs->getNewOrderMessage();
        if($order_message){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=order/order&act=new_order_message_list\')">新留言订单'.$order_message.'条</li>';
        }
        $order_message=null;
        unset($order_message);
        $oShopbbs=null;
        unset($oShopbbs);

        $oReturnData=&$this->system->loadModel('trading/return_product');
        $return_count=$oReturnData->count_return_product();
        if($return_count){
            $html .= '<li class="menuitem" onclick="W.page(\'index.php?ctl=order/return_product&act=new_msg\')">待处理售后服务'.$return_count.'条</li>';
        }
        $oReturnData=null;
        unset($oReturnData);
        $return_count=null;
        unset($return_count);


        unset($filter);
        echo $html;
        flush();
        set_time_limit(0);

        $messenger = &$this->system->loadModel('system/messenger');
        $messenger->runQueue();
    }

    function sel_region($path,$depth){
         header('Content-type:text/html;charset=utf-8');
        $local = &$this->system->loadModel('system/local');
        if($ret = $local->get_area_select($path)){
            echo '&nbsp;-&nbsp;'.$local->get_area_select($path,array('depth'=>$depth));
        }else{
            echo '';
        }
    }
    
    function get_menulist($searchPanel){
       header('Content-type:text/html;charset=utf-8');
      require('adminSchema.php');   
      if (is_array($menu)){
        foreach($menu as $key => $val){
            foreach($val as $skey => $sval){
                foreach($sval as $sskey=>$ssval){
                    if ($ssval['type']=="group"){
                        foreach($ssval['items'] as $ssskey =>$sssval){
                            if ($sssval['type'] == "menu"){
                                $tmpMenu[]=array(
                                    "label"=>$sssval['label'],
                                    "link"=>$sssval['link']
                                );
                            }
                        }
                    }    
                }
            }
            
        }

      }
      
      if($searchPanel){
         $this->setView('menuSearch.html');
         $this->output();
         exit;
      }
      print_r(json_encode($tmpMenu));
    }
}

?>
