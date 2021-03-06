<?php
class ctl_cur extends adminPage{
    var $workground ='setting';

    function index(){
        $this->path[] = array('text'=>'货币列表');
        $ocur=$this->system->loadModel('system/cur');
        $aCur = $ocur->curList();
        $this->pagedata['currency'] = $aCur['main'];
        $this->page('system/cur/cur.html');
    }
    
    function curDetail($id){
        $this->path[] = array('text'=>'货币内容页');
        $ocur=$this->system->loadModel('system/cur');
        if($id) $data=$ocur->getcur($id);
        $aCur = $ocur->getSysCur();
        $data['selcur'] = array_merge(array(''=>__('请选择')),$aCur);
        $aCur = $ocur->curList();
        $aCur = $aCur['main'];
        if ($aCur){
            foreach($aCur as $item) {
                if ($item['cur_code']!=$id) {
                    unset($data['selcur'][$item['cur_code']]);
                }
            }
        }
        if(count($data['selcur'])==1){
            $this->begin('index.php?ctl=system/cur&act=index');
            $this->end(false,__('系统货币已经全部添加完毕'));
        }
        $data['defopt']=array('true'=>'是','false'=>'否');
        if(!$data['def_cur']) $data['def_cur']='false';
        $this->pagedata['currency'] = $data;
        $this->page('system/cur/curDetail.html');
    }
    function curAddPage(){
        $this->page('system/cur/curAddPage.html');
    }
    
    function curDel($id){
        $this->begin('index.php?ctl=system/cur&act=index');
        $o=$this->system->loadModel('system/cur');
        $this->end($o->curDel($id),__('删除成功'));
    }
    
    function curAdd(){
        $this->begin('index.php?ctl=system/cur&act=index');
        $ocur=$this->system->loadModel('system/cur');
        $data['cur_code']=$this->in['cur_code'];
        $data['cur_sign']=$this->in['cur_sign'];
        $data['cur_rate']=$this->in['cur_rate'];
        $data['def_cur']=$this->in['def_cur'];
        $data['cur_name']=$this->in['cur_name'];
        if($data['def_cur']=='true')$data['cur_rate']=1;
        $this->end($ocur->curAdd($data),'货币新增成功');
    }
    
    function curEdit(){
        $this->begin('index.php?ctl=system/cur&act=index');
        $ocur=$this->system->loadModel('system/cur');
        $data['cur_code']=$this->in['cur_code'];
        $data['cur_sign']=$this->in['cur_sign'];
        $data['cur_rate']=$this->in['cur_rate'];
        $data['def_cur']=$this->in['def_cur'];
        $data['cur_name']=$this->in['cur_name'];
        $this->end($ocur->curEdit($this->in['cur_code'],$data),__('货币保存成功'));
    }
}
?>
