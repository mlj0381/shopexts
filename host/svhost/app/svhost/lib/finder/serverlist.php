<?php
class svhost_finder_serverlist{
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);
    }    
    var $detail_basic = ' 基本信息';
    function detail_basic($server_id){
        $serverlist_model = $this->app->model('serverlist');
        if($_POST){
            $_POST['server_id'] = $server_id;
            $serverlist_model->save($_POST);
        }
        $data = $serverlist_model->dump($server_id); 
        $data = $data['server'];
        return  $this->gen_html($serverlist_model,$data);
    }
    
    var $detail_http = ' http服务';
    function detail_http($server_id){
        $http_model = $this->app->model('http');
        $serverlist_model = $this->app->model('serverlist');
        if($_POST){
            $sdf['server_id'] = $server_id;
            $sdf['http'] = $_POST;
            $serverlist_model->save($sdf);
        }
        $data = $serverlist_model->dump($server_id,'*',array('http'=>'*'));
        $data = $data['http'];
        
        return $this->gen_html($http_model,$data);
    }
    
    var $detail_database = ' 数据库';
    function detail_database($server_id){
        $database_model = $this->app->model('database');
        $serverlist_model = $this->app->model('serverlist');
        if($_POST){
            $sdf['server_id'] = $server_id;
            $sdf['database'] = $_POST;
            $serverlist_model->save($sdf);
        }
        $data = $serverlist_model->dump($server_id,'*',array('database'=>'*'));
        $data = $data['database'];

        return $this->gen_html($database_model,$data);
    }
    
    var $detail_ftp = ' FTP';
    function detail_ftp($server_id){
        $ftp_model = $this->app->model('ftp');
        $serverlist_model = $this->app->model('serverlist');
        if($_POST){
            $sdf['server_id'] = $server_id;
            foreach($_POST as $key=>$value){
                if($pos = strpos($key,'/')){
                    $one = substr($key,0,$pos);
                    $two = substr($key,$pos+1);
                    $_POST[$one][$two] = $value;
                }
            }
            $sdf['ftp'] = $_POST;
            $serverlist_model->save($sdf);
        }
        $data = $serverlist_model->dump($server_id,'*',array('ftp'=>'*'));
        $data = $data['ftp'];
        foreach($data as $key=>$val){
            if(is_array($val)){
                foreach($val as $k=>$v){
                    $new_key = $key."/".$k;
                    $data[$new_key]  = $v;   
                }
            }
        }
        return $this->gen_html($ftp_model,$data);
    }
    
    private function get_schema_by_sdfpath(&$obj,$sdfpath){
        if($col = $obj->schema['columns'][$sdfpath]){
            return $col;
        }
        foreach($obj->schema['columns'] as $col){
            if($col['sdfpath'] == $sdfpath){
                return $col;
            }
        }
    }
    
    private function gen_html(&$obj,&$data){
        $html .= $this->ui->form_start();
        foreach($data as $k=>$val){
            $col = $this->get_schema_by_sdfpath($obj,$k);
            if($col['editable'] != 'true'){
                continue;
            }
            $input['value'] = $val;
            if($col['type'] == 'bool'){
                $input['type'] = 'bool';
            }
            if(substr($col['type'],0,4) == 'enum'){
                $input['type'] = 'select';
                $input['required'] = 'true';
                $text = str_replace('enum','array',$col['type']);
                eval('$tmp='.$text.';');
                $options = null;
                foreach($tmp as $v){
                    $options[$v] = $v;
                }
                $input['options'] = $options;
            }
            $input['name'] = $k;
            $input['title'] =$col ['label'];
            $html .= $this->ui->form_input($input);
            unset($input);
        }
        $html .= $this->ui->form_end();
        return $html;
    }
}