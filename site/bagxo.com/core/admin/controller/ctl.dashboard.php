<?php
class ctl_dashboard extends adminPage{

    function ctl_dashboard(){
        parent::adminPage();
        $system = &$GLOBALS['system'];
        $system->session->close(false);
    }

    function update(){

        $this->ajaxdata['data']['dashboard'] = array(
            'count'=>array(
                'data'=>'',
                'method'=>'widgets.count'
            )
        );

        $this->ajaxdata['data']['status'] = $oAnalytics->getStatus();

        $oCheckSystem = $this->system->loadModel('checkSystem');

        $nNowTime = time();
        $nOldTime = $oCheckSystem->getConfigTime();
        if ($nNowTime - $nOldTime > 10*60) {
            $oCheckSystem->setConfigTime($nNowTime);
        } else {
            $nUpdateTime = $nNowTime - $nOldTime;
            $oCheckSystem->updateTimeOnline($nUpdateTime, $nNowTime);
        }

        $this->ajaxdata['data']['checkSystem'] = $oCheckSystem->test();

        $this->ajaxdata['jscall'] = 'X.dashboard.display(data)';
        $this->ajax();
    }


    function getcertInfo(){
        $cert = $this->system->loadModel('service/certificate');
        echo $cert->getInfo();
    }

    function index(){
        $this->getinfo();
    }
    function templete(){
        $templete=$this->runTemplete();
        foreach($templete as $k=>$v){
            if($templete[$k]!=md5(file_get_contents(CORE_DIR.'/admin/view/'.base64_decode($k)))){
                if($k=='aW5kZXguaHRtbA=='){
                    return base64_decode('PHNjcmlwdD5hbGVydCgi57O757uf5qOA5rWL5Yiw5qC45b+D5paH5Lu26KKr5pu05pS577yM5Y+v6IO95Lya5b2x5ZON5Yiw572R5bqX55qE5q2j5bi46L+Q6KGM77yM6K+35bC95b+r5LiOU2hvcEV45a6i5pyN6IGU57O7Iik7PC9zY3JpcHQ+');
                }
                return base64_decode('PGxpPjxiPuasoui/juS9v+eUqFNob3BFeOe9keS4iuWVhuW6l+ezu+e7nzwvYj48L2xpPg==');
            }
        }
        return false;
    }
    function getinfo(){
            if(defined('SAAS_MODE')&&SAAS_MODE){
                $this->pagedata['saas_mode'] = true;
            }
            $this->path[] = array('text'=>'桌面');
            $this->pagedata['options'] = array('cat_id'=>1);
            $url_arr = parse_url($this->system->base_url());

            $operate = &$this->system->loadModel('admin/operator');
            $opinfo = $operate->instance($this->op->opid,'lastlogin');
            $this->pagedata['logintime'] = $opinfo['lastLogin'];
            $version=$this->system->version();
            $this->pagedata['templete']=$this->templete();
            $this->pagedata['version']=$version['app'].".".$version['rev'];

            $oGoodsCat = $this->system->loadModel('goods/productCat');
            $aCat = $oGoodsCat->getExists();
            if(count($aCat) == 0){
                $systemInfo['cat']=true;
            }

            $oCur = $this->system->loadModel('system/cur');
            $aCur = $oCur->curAll();
            if(!function_exists("NewMagickWand") && !function_exists("imagecreate")){
                $systemInfo['imageneed']=true;
            }

            if(count($aCur) == 0){
                $systemInfo['price']=true;
            }
            $oPay = $this->system->loadModel('trading/payment');
            $aPay = $oPay->getMethods();
            if(count($aPay) == 0){
                $systemInfo['pay']=true;
            }
            $oDelivery = $this->system->loadModel('trading/delivery');
            $aDelivery = $oDelivery->getDlTypeList();
            if(count($aDelivery) == 0){
                $systemInfo['sender']=true;
            }
            $aArea = $oDelivery->getDlAreaList();
            if(count($aArea) == 0){
                $systemInfo['area']=true;
            }

            unset($filter);

           
            
            $this->pagedata['version'] = $this->system->version();
            $status = &$this->system->loadModel('system/status');
            $this->pagedata['affairinfo'] = $status->getList();
            $this->pagedata['affairinfo']['todaysorder'] = $status->get('ORDER_NEW',date('Y-m-d'))+0;
            $this->pagedata['affairinfo']['yesterday_order'] = $status->get('ORDER_NEW',date("Y-m-d",strtotime("-1 day")))+0;

            $this->pagedata['affairinfo']['memberadd'] = $status->get('MEMBER_REG',date('Y-m-d'))+0;
            $this->pagedata['affairinfo']['yesterday_new_member'] = $status->get('MEMBER_REG',date("Y-m-d",strtotime("-1 day")))+0;

            $oShopbbs = $this->system->loadModel('resources/shopbbs');
            $this->pagedata['affairinfo']['order_message']=$oShopbbs->getNewOrderMessage();
                            
            $oReturnData=&$this->system->loadModel('trading/return_product');
            $this->pagedata['affairinfo']['return_product']=$oReturnData->count_return_product();
            $oProduct = $this->system->loadModel('goods/finderPdt');
            $filter_p['store_alarm'] = $this->system->getConf('system.product.alert.num');
            foreach($oProduct->getList('goods_id', $filter_p, 0, 1000) as $row){
                $filter['goods_id'][] = $row['goods_id'];
            }
            if(empty($filter['goods_id'])) $filter['goods_id'][] = -1;
            unset($filter_p);
            $oGoods = $this->system->loadModel('goods/products');
            $oGoods->getList('', $filter, 0, -1, $alert_count);
            $this->pagedata['affairinfo']['goods_alert']=$alert_count?$alert_count:'0';
            $sales = $this->system->loadModel('utility/salescount');
            $this->pagedata['affairinfo']['yesterday_order_execution']=$sales->count_yesterday_order();
            $member = $this->system->loadModel('member/member');
            $this->pagedata['affairinfo']['birthday'] = $member->getBirthdayMember()+0;



            $day_st=date("Y-m-d",strtotime("-6 day"));
            $day_en=date("Y-m-d");
            $day_search=$sales->mdl_dosearch($day_st,$day_en,"","","day");
            $this->pagedata['day']=$day_search;

            $this->pagedata['systeminfo']=$systemInfo;
            $this->pagedata['businessinfo']=$businessInfo;
            $server_name = $url_arr['host'];
            $path = $url_arr['path'];
            $strDesc =  "|".$server_name."|".phpversion()."|".$_SERVER["SERVER_SOFTWARE"]."|".$_ENV["OS"]."|".$path."|2|4.8||";

            $this->pagedata['addurl'] = 'http://top.shopex.cn/promotion/promotion.php?id='.$this->_encDesc($strDesc);
            $this->setView('dashboard.html');
            $this->output();

    }
    function _encDesc($str){
            $str = base64_encode($str);
            $outstr = "";
            for($i=0;$i<strlen($str);$i++){
                $outstr .= ord(substr($str,$i,1))+270;
            }
            return $outstr;
    }
}
?>
