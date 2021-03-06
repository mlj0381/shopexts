<?php
class mdl_checkSystem extends modelFactory {

    function ttype(){
        //无配送方式
        $sSql = "SELECT count(*) as count from sdb_mall_offer_t where";
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return true;
        else
            return false;
    }

    function ptype(){
        //无支付方式
        $sSql = "SELECT count(*) as count from sdb_mall_offer_p ";
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return true;
        else
            return false;
    }

    function currency(){
        //支付方式支持的交易货币在货币列表中没有添加
        return true;
    }

    function deliverarea(){
        //无配送地区
        $sSql = "SELECT count(*) as count from sdb_mall_offer_deliverarea" ;
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return true;
        else
            return false;
    }

    function deliverexp(){
        //配送方式选择了公式计算，却没有添加公式，或者没有去关联公式
        $sSql = "SELECT count(*) as count from sdb_mall_offer_t where type=1 and areaexp=null";
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return false;
        else
            return true;
    }

    function dftcurrency(){
        //无默认货币
        $sSql = "SELECT count(*) as count from sdb_mall_currency where basicmark = 1";
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return true;
        else
            return false;
    }

    function tmpl(){
        //无当前模板
        $sSql = "SELECT count(t.id) as count from sdb_mall_template t
                left join sdb_mall_offer m on m.offer_mod = t.id";
        $aData = $this->db->selectrow($sSql);
        if($aData['count'])
            return true;
        else
            return false;
    }

    function test(){
        $this->tocheck = array(
                'ttype'=>array(
                        'url'=>'admin_ttype.php',
                        'title'=>__('Add Shipping Method'),
                        'message'=>__('None Shipping Method'),
                        'id'=>'ttype'
                    ),
                'ptype'=>array(
                        'url'=>'admin_ptype.php',
                        'title'=>__('Add Payment Method'),
                        'message'=>__('None Payment Method'),
                        'id'=>'ptype'
                    ),
                'currency'=>array(
                        'ctl'=>'currency/currency',
                        'act'=>'addCurrency',
                        'target'=>'currency/currency:addCurrency',
                        'message'=>__('No currency supported by payment method.'),
                        'id'=>'currency'
                    ),
                'deliverarea'=>array(
                        'url'=>'admin_deliverarea.php',
                        'title'=>__('Add Shipping Area'),
                        'message'=>__('None Shipping Area'),
                        'id'=>'deliverarea'
                    ),
                'deliverexp'=>array(
                        'url'=>'admin_deliverexp_list.php',
                        'title'=>__('Shipping Function List'),
                        'message'=>__('Choosing a shipping formula, but no any formula or no relevance to the formula.'),
                        'id'=>'deliverexp'
                    ),
                'dftcurrency'=>array(
                        'ctl'=>'currency/currency',
                        'act'=>'addCurrency',
                        'target'=>'currency/currency:addCurrency',
                        'message'=>__('None Default Currency'),
                        'id'=>'dftcurrency'
                    ),
                'tmpl'=>array(
                        'ctl'=>'template/template',
                        'act'=>'templateList',
                        'target'=>'template/template:templateList',
                        'message'=>__('None Current Template'),
                        'id'=>'tmpl'
                    ),
            );
        foreach($this->tocheck as $method=>$info){
            if(!call_user_func(array($this, $method))){
                $return[]=$info;
            }
        }
        return $return;
    }
    
    function setConfigTime($second){
        if($this->checkValue("lasttime")){
            $sSql = "UPDATE sdb_mall_offer_setup SET value = '".$second."' WHERE keyword = 'lasttime'";
            $this->db->query($sSql);
        }else{
            $sSql = "INSERT INTO sdb_mall_offer_setup (keyword,value) VALUES ('lasttime','".$second."')";
            $this->db->query($sSql);
        }
    }

    function getConfigTime(){
        $sSql = "SELECT value FROM sdb_mall_offer_setup WHERE keyword='lasttime' ";
        $aData = $this->db->selectrow($sSql);
        return $aData['value'];
    }

    function updateTimeOnline($second,$newTime){  //更新后台在线时间
        if($this->checkValue("shop_adon_h")){            
            $sSql = "UPDATE sdb_mall_offer_setup  SET value=value+".$second." WHERE keyword='shop_adon_h' OR keyword='shop_adon_w'";
            $this->db->query($sSql);
            $sSql = "UPDATE sdb_mall_offer_setup  SET value=".$newTime." WHERE keyword='lasttime'";
            $this->db->query($sSql);
        }else{
            $sSql = "REPLACE INTO sdb_mall_offer_setup (keyword,value)
                          VALUES ('shop_adon_h','0'),('shop_adon_w','0')";            
            $this->db->query($sSql);
        }        
    }

    function checkValue($vname){
        $sSql = "SELECT keyword FROM sdb_mall_offer_setup WHERE keyword='{$vname}'";        
        $aData = $this->db->selectrow($sSql);
        if($aData['keyword']){
            return true;
        }else{
            return false;
        }
    }

    function versioncompare(){
        $versionA=$this->system->version();
        $http = $this->system->network();
        $version = $versionA['app'];
        $versionno = $versionA['rev'];
        $url="http://top.shopex.cn/promotion/version.php?version=".base64_encode($version)."&versionno=".base64_encode($versionno)."&function=compare";
        $http->fetch($url);
        $currentVersion = $http->results;
        if($currentVersion=='false'){
            return true;
        }
        if($versionno < $currentVersion){
            return false;
        }else{
            return true;
        }
    }
    function versionUrl(){
        $versionA=$this->system->version();
        $http = $this->system->network();
        $version = $versionA['app'];
        $versionno = $versionA['rev'];
        return $url="http://top.shopex.cn/promotion/version.php?version=".base64_encode($version)."&versionno=".base64_encode($versionno)."&function=getlist";
        $http->fetch($url);
        $listInfo=json_decode($http->results,'true');
        return  $listInfo;
    }
}
?>
