<?php
class cachemgr{

    var $_objects = null;
    var $_base_rev = '$Rev: 23906 $';

    function cachemgr(){
        $this->_vary_store_key = md5('objectStatus'.STORE_KEY);
        $this->fetch($this->_vary_store_key,$this->_vary_list);
    }

    function setModified($key){
        $now = time();
        if(is_array($key)){
            foreach($key as $k){
                $this->_vary_list[strtoupper($k)] = $now;
            }
        }else{
            $this->_vary_list[strtoupper($key)] = $now;
        }
        return $this->store($this->_vary_store_key,$this->_vary_list);
    }

    function getModified($key){
        return isset($this->_vary_list[$key])?$this->_vary_list[$key]:$this->_base_rev;
    }

    function set($ident,$content,$varys=null){
        $data = array('content'=>$content);
        if(is_array($varys) && count($varys)>0){
            $data['varys'] = array();
            foreach($varys as $o){
                $o = strtoupper($o);
                $data['cotime'][$o] = $this->getModified($o);
                $data['varys'][] = $o;
            }
        }
        return $this->store(md5($_SERVER['HTTP_HOST'].$ident.STORE_KEY),$data);
    }

    function get($ident,&$content){
       
        if($this->fetch(md5($_SERVER['HTTP_HOST'].$ident.STORE_KEY),$data)){
            if(count($data['varys'])>0){
                foreach($data['varys'] as $o){
                    if(!isset($data['cotime'][$o]) || $data['cotime'][$o] != $this->getModified($o)){
                        return false;
                    }
                }
            }

            $content = $data['content'];
            return true;
        }else{
            return $content = false;
        }
    }

    function &exec($func,$args,$ttl=3600){
        if(is_array($func)){
            $ident = md5(get_class($func[0]).$func[1].implode(',',$args).STORE_KEY);
        }else{
            $ident = md5($func.implode(',',$args).STORE_KEY);
        }

        $data = &$this->fetch($ident);

        if(!$data || ( time() - $data['time'] > $ttl)){
            $return = call_user_func_array($func,$args);
            $data = array('time'=>time(),'return'=>$return);
            $this->store($ident,$data,$ttl);
        }else{
            $return = &$data['return'];
        }
        return $return;
    }
}
?>
