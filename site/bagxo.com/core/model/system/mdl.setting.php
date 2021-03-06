<?php
/**
 * mdl_setting
 *
 * @uses modelFactory
 * @package
 * @version $Id: mdl.setting.php 1902 2008-04-24 06:53:18Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
require_once('shopObject.php');

class mdl_setting extends shopObject{

    //START
    var $listView='setting/config/finder_list.html';
    var $actionView='setting/config/finder_action.html';
    //var $filterView='setting/config/finder_filter.html';
    var $idColumn='s_name';
    var $textColumn = 's_name';
    var $defaultCols = 's_name,s_data,s_time';
    //var $defaultOrder = array('goods_id','desc');
    var $tableName = 'sdb_settings';

    function getColumns(){
        return array(
                        'key'=>array('label'=>'参数标识','class'=>'span-3'),    /* 商品id */
                        'desc'=>array('label'=>'参数名称','class'=>'span-3'),    /* 分类ID */
                        'type'=>array('label'=>'参数类型','class'=>'span-3','type'=>'type'),    /* 分类ID */
                        'default'=>array('label'=>'参数默认值','class'=>'span-3'),    /* 类型id */
                        'value'=>array('label'=>'参数当前值','class'=>'span-3')    /* 类型id */
                );
    }

    function modifier_type(&$rows,$options){
        foreach($rows as $k=>$v){
            switch($v){
                case SET_T_STR:
                    $rows[$k] = '字符串';
                    break;
                case SET_T_INT:
                    $rows[$k] = '数值型';
                    break;
                case SET_T_ENUM:
                    $rows[$k] = '枚举值';
                    break;
                case SET_T_BOOL:
                    $rows[$k] = '布尔型';
                    break;
                case SET_T_TXT:
                    $rows[$k] = '文本';
                    break;
                case SET_T_FILE:
                    $rows[$k] = '文件';
                    break;
                }
        }
    }

    function getList($sCols,$aFilter,$nStart=0,$nLimit=null,&$count){
        $return = array();
        include('setting.php');
        $sCols="*";
        $data=$this->db->select('select '.$sCols.' from sdb_settings');
        foreach($data as $row){
            if($set = unserialize($row['s_data'])){
                foreach($set as $k=>$v){
                    $key = $row['s_name'].'.'.$k;
                    $setting[$key]['value'] = $v;
                }
            }
        }
        ksort($setting);
        $count=count($setting);
        if($aFilter['key']){
            foreach($setting as $k=>$v){
                if(is_bool(strpos($k,$aFilter['key']))){
                    unset($setting[$k]);
                }
            }
        }
        foreach($setting as $k=>$v){
            $temp['key'] = $k;
            $temp['desc'] = $v['desc'];
            $temp['type'] = $v['type'];
            $temp['default'] = $v['default'];
            $temp['value'] = $v['value'];
            $return[] = $temp;
        }
        return $return;
    }

    function getFilter($p){
        return;
    }

    function searchOptions(){
        return array(
            'key'=>'Preference name',
        );
    }

    //设商品全局规格库
    function setGoodsSpec(){
        return true;
    }

    //取商品全局规格库
    function getGoodsSpec(){
        return array();
    }

    function certUpload(){
        if($_FILES['cert']['size']>0){
            if($_FILES['cert']['name']=='bazs.cert'){
                $dir='../cert/';
                if(file_exists($dir)){
                    @move_uploaded_file($_FILES['cert']['tmp_name'],$dir.$_FILES['cert']['name']);
                    if(file_exists($dir.$_FILES['cert']['name'])){
                        //Log files upload succeed!
                        return true;
                    }else{
                        //System has no right to build the cert directory, please contact the administrator.
                        return false;
                    }
                }else{
                    //System can not auto build the cert directory, please contact the administrator.
                    return false;
                }
            }else{
                //Log files name is wrong or empty, please upload again.
                return false;
            }
        }elseif(!file_exists($dir.'bazs.cert')){
            //Log files name is wrong or empty, please upload again.
            return false;
        }
    }
    function certEdit($data){
        foreach($data as $k=>$v){
            $this->system->setConf('store.'.$k,$v);
        }
        return true;
    }

    function basicinfoEdit($data){
        foreach($data as $k=>$v){
            $this->system->setConf('store.'.$k,$v);
        }
        return true;
    }

    function ext_valid($filename,$type) {
        $extarr = array();
        $filename = strtolower($filename);
        $extarr[0]= array(".gif",".jpg",".jpeg",".png");
        if(!isset($extarr[$type])) return false;
        if($ext = strrchr($filename,".")) {
            if(in_array($ext,$extarr[$type])) return true;
            else return false;
        }else return false;
    }

    function getFontFile() {
        $font_dir = PUBLIC_DIR.'/fonts';
        if (is_dir($font_dir)) {
            if ($dh = opendir($font_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if(preg_match('/\.ttf$|\.ttc$/i',$file)){
                        $arr[$file]=$file;
                    }
                }
                closedir($dh);
            }
        }
        return $arr;
    }
    function saveWatermarkCfg($data){
        //图片处理
        $storager = $this->system->loadModel('system/storager');
        $files = array();
        $procssing_files = array('default_thumbnail_pic'=>'site.default_thumbnail_pic',
                                'default_big_pic'=>'site.default_big_pic',
                                'default_small_pic'=>'site.default_small_pic',
                                'wm_small_pic'=>'site.watermark.wm_small_pic',
                                'wm_big_pic'=>'site.watermark.wm_big_pic',
                                'spec_default_pic'=>'spec.default.pic'
            );
        foreach($procssing_files as $k=>$v){
            if($_FILES[$k]['name']){
                $files[$k] = $storager->save_upload($_FILES[$k],'default',$k);
                if(!$files[$k]){
                    unset($files[$k]);
                }else{
                    $this->system->setConf($v,$files[$k]);
                }
            }
        }

        $this->system->setConf('site.reading_glass_width',$data['readingGlassWidth']);
        $this->system->setConf('site.reading_glass_height',$data['readingGlassHeight']);

        $this->system->setConf('site.reading_glass',$data['readingGlass']?1:0);
        $this->system->setConf('site.thumbnail_pic_width',$data['thumbnail_pic_width']);
        $this->system->setConf('site.thumbnail_pic_height',$data['thumbnail_pic_height']);
        $this->system->setConf('site.big_pic_width',$data['big_pic_width']);
        $this->system->setConf('site.big_pic_height',$data['big_pic_height']);
        $this->system->setConf('site.small_pic_width',$data['small_pic_width']);
        $this->system->setConf('site.small_pic_height',$data['small_pic_height']);

        $ib=$this->system->loadModel('utility/magickwand');
        if($ib->magickwand_loaded){
            $loaded = true;
        }else{
            $ib=$this->system->loadModel('utility/gdimage');
            $loaded = $ib->gd_loaded;
        }
        if($loaded){
            $watermark = array();
            $watermark['wm_small_enable']=$data['wm_small_enable'];
            if($watermark['wm_small_enable']){
                $watermark['wm_small_loc']=$data['wm_small_loc'];
                if($watermark['wm_small_enable']==1){

                    $watermark['wm_small_transition']=$data['wm_small_transition'];
                }elseif($watermark['wm_small_enable']==2){

                    $watermark['wm_small_text']=$data['wm_small_text'];
                    $watermark['wm_small_font']=$data['wm_small_font'];
                    $watermark['wm_small_font_size']=$data['wm_small_font_size'];
                    $watermark['wm_small_font_color']=$data['wm_small_font_color'];
                }
            }
            $watermark['wm_big_enable']=$data['wm_big_enable'];
            if($watermark['wm_big_enable']){

                $watermark['wm_big_loc']=$data['wm_big_loc'];
                if($watermark['wm_big_enable']==1){

                    $watermark['wm_big_transition']=$data['wm_big_transition'];
                }elseif($watermark['wm_big_enable']==2){

                    $watermark['wm_big_text']=$data['wm_big_text'];
                    $watermark['wm_big_font']=$data['wm_big_font'];
                    $watermark['wm_big_font_size']=$data['wm_big_font_size'];
                    $watermark['wm_big_font_color']=$data['wm_big_font_color'];

                }
            }
            foreach($watermark as $k=>$v)
                $this->system->setConf('site.watermark.'.$k,$v);
            $this->system->setConf('spec.image.width', $data['spec_image_width']);
            $this->system->setConf('spec.image.height', $data['spec_image_height']);
        }
        return true;
    }
}
?>
