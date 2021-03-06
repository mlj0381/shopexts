<?PHP
    class ctl_tools extends adminPage{
        var $workground ='sale';
        function sitemaps(){
            $this->path[] = array('text'=>'搜索引擎优化');
            $this->pagedata['url'] = $this->system->realUrl('sitemaps','catalog',null,'xml',$this->system->base_url());
            $this->page('system/tools/sitemaps.html');
        }
        function createLink(){
            $this->path[] = array('text'=>"站外推广链接");
            $timer=$this->system->getConf('site.refer_timeout');
            $this->pagedata['base_url'] = $this->system->base_url();
            $this->pagedata['validtime'] = $timer;
            $this->page('system/tools/createlink.html');
        }
        function seo(){
            $this->path[] = array('text'=>'SEO设置');
            $this->page('system/tools/seo.html');
        }
        function seoedit(){
        
            $this->begin('index.php?ctl=sale/tools&act=seo');
            
            $_POST['setting']['site.tax_ratio'] = $_POST['setting']['site.tax_ratio']/100;
            $storager = $this->system->loadModel('system/storager');
            
            $this->end($this->settingEdit(),__('修改成功'));

            //$this->end($this->settingEdit(),__('修改成功'));
        }
        function editValidtime(){
            $timer=intval($_POST['validtime']);
            $this->begin('index.php?ctl=sale/tools&act=createLink');
            if($this->system->setConf('site.refer_timeout',$timer)){
                $this->end(true,"修改成功");
            }else{
                $this->end(false,"修改失败");
            }
        }
        function set_wltx($status=0){
            $this->begin('index.php?ctl=sale/tools&act=wltx');
            $function  = $status==0? 'co.close_se':'co.open_se';
            $this->sendwltx($function);
            $this->end(true,($status==0)?'网罗天下服务已暂停':'网罗天下服务已开启');
        }
        function wltx(){
            $this->path[] = array('text'=>'网罗天下');
            $cer = $this->system->loadModel('service/certificate');
            $this->pagedata['cert_id'] = $cer->getCerti();
            $data = $this->sendwltx('co.valid_se');
            $this->pagedata['linklist'] = $this->showwltx();
            if($data['msg']!='certificate_id_false'&&$data['info']['service_status']!='close'&&count($data)!=0){
                if(time()>$data['info']['service_close_time']){
                    $this->pagedata['isold'] = true;
                }else{
                    $time = intval(($data['info']['service_close_time']-time())/3600/24);
                    if($time<15){
                        $this->pagedata['timeneed'] = $time;
                    }
                }

                $this->pagedata['data'] = $data;
                $this->page('sale/wltx/open.html');
            }else{
                if($data['info']['service_status']=='close'){
                    $this->pagedata['hasopen'] = true;
                }
                $this->page('sale/wltx/index.html');
            }

        }

        function showwltx(){
            $data = $this->sendwltx('co.show_se');
            if(!is_array($data)){
                return '数据获取错误';
            }
            return $data['info']['se'];
        }
        
        function sendwltx($function){
            $network = $this->system->network();
            $params['certi_app'] = $function;
            $cer = $this->system->loadModel('service/certificate');
            $params['certificate_id'] = $cer->getCerti();
            $token = $cer->getToken();
            if((!$token||!$params['certificate_id'])&&$function!='co.show_se'){
               return array();
            }
            $params['app_id'] = APP_WLTX_ID;
            $params['version'] = APP_WLTX_VERSION;
            $params['certi_url'] = $this->system->base_url();
            $params['certi_ac'] = $this->make_shopex_ac($params,$token);
            $network->submit(APP_WLTX_URL,$params);
            $data = json_decode($network->results,true);
            if(!is_array($data)){
                echo '链接地址错误，请稍后再试';
                exit;
            }
            return $data;
        }


        function settingEdit(){


            foreach($_POST['_set_'] as $key=>$type){
                if($type=='bool'){
                    $_POST['setting'][$key] = $_POST['setting'][$key]?true:false;
                }
            }

            if($this->_modified($_POST['setting'],'site.stripHtml')){
                $frontend = $this->system->loadModel('system/frontend');
                $frontend->clear_compiled_tpl();
            }
            $this->system->setConf("readingGlass",$_POST['readingGlass']?1:0);

            if(isset($_POST['setting']['system.seo.emuStatic']) && $_POST['setting']['system.seo.emuStatic']){
                $svinfo = $this->system->loadModel('utility/serverinfo');

                $url = parse_url($this->system->base_url());
                $code = substr(md5(time()),0,6);
                $content = $svinfo->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");

                if(!strpos($content,'[*['.md5($code).']*]')){
                    
                    if(false===strpos(strtolower($_SERVER["SERVER_SOFTWARE"]),'apache')){
                        trigger_error(__('您的服务器不是apache,无法使用htaccess文件。请手动启用rewrite，否则无法启用伪静态'),E_USER_ERROR);
                    }
                    
                    if(file_exists(BASE_DIR.'/'.ACCESSFILENAME)){
                        trigger_error(__('您的系统存在无效的'.ACCESSFILENAME.', 无法启用伪静态'),E_USER_ERROR);
                    }else{
                        if(($content = file_get_contents(BASE_DIR.'/root.htaccess'))){
                            $content = preg_replace('/RewriteBase\s+.*\//i','RewriteBase '.$url['path'],$content);
                            if(file_put_contents(BASE_DIR.'/'.ACCESSFILENAME,$content)){
                                $content = $svinfo->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");
                                if(!strpos($content,'[*['.md5($code).']*]')){
                                    unlink(BASE_DIR.'/'.ACCESSFILENAME);
                                    trigger_error(__('您的系统不支持apache的'.ACCESSFILENAME.',启用伪静态失败.'),E_USER_ERROR);
                                }
                            }else{
                                trigger_error(__('无法自动生成'.ACCESSFILENAME.',可能是权限问题,启用伪静态失败'),E_USER_ERROR);
                            }
                        }else{
                            trigger_error(__('系统不支持rewrite,同时读取原始root.htaccess文件来生成目标'.ACCESSFILENAME.'文件,因此无法启用伪静态'),E_USER_ERROR);
                        }
                    }
                    //trigger_error(__('不支持rewrite,放弃'),E_USER_ERROR);
                }
            }
            
            foreach($_POST['setting'] as $k=>$v){
                
                
                if(!$this->system->setConf($k,stripslashes($v))){
                    
                    trigger_error($k.__('设置错误'),E_USER_ERROR);
                    return false;
                }

            }
            
            return true;
        }
        function _modified($src,$key){
            if(isset($src[$key]) && ($src[$key]!=$this->system->getConf($key))){
                return true;
            }else{
                return false;
            }
        }


        function make_shopex_ac($temp_arr,$token){
            ksort($temp_arr);
            $str = '';
            foreach($temp_arr as $key=>$value){
                if($key!='certi_ac') {
                    $str.=$value;
                }
            }
            return md5($str.$token);
        }

    }
?>