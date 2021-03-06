<?php


class mdl_apiclient extends modelFactory {
    var $url = '';
    var $key = '';
    var $response_type = 'json';
    var $read_timeout = 5;
    var $_fp_timeout = 3;

    function _verify(){
    }
    
    function native_svc($service,$params){
        if(!$service||$this->url==''||$this->key==''){
            return false;
        }
        $params['service'] = $service;
        $params['response_type'] = $this->response_type;
        ksort($params);
        $query = '';
        foreach($params as $k=>$v){
            $query .= $k.'='.urlencode($v).'&';
        }
        $sign = md5(substr($query,0,strlen($query)-1).$this->key);
        $query .= 'sign='.$sign;    

        $network = $this->system->network();
        $network->read_timeout = $this->read_timeout;
        $network->_fp_timeout = $this->_fp_timeout;
        $response_url  = $this->url.'?'.$query;
        if($network->fetch($response_url)){
            $this->net_result = $network->results;
                if(substr($this->net_result,0,1)=='{'&&substr($this->net_result,-1)=='}'){

                if($this->response_type=='json'){
                    return json_decode($network->results,true);
                }elseif($this->response_type=='serialized'){
                    return unserialize($network->results);
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


}
