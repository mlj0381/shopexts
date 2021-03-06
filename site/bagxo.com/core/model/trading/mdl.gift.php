<?php
include_once('shopObject.php');
class mdl_gift extends shopObject{
    var $idColumn = 'gift_id'; //表示id的列
    var $textColumn = 'name';
    var $defaultCols = 'name,giftcat_id,limit_start_time,limit_end_time,point,storage,shop_iffb,orderlist,ifrecommend';
    var $adminCtl = 'sale/gift';
    var $defaultOrder = array('orderlist','desc');
    var $tableName = 'sdb_gift';

    function getColumns(){
        return array(
            'gift_id'=>array('label'=>'赠品ID','class'=>'span-3','readonly'=>true),    /* 赠品ID */
                                'giftcat_id'=>array('label'=>'赠品分类','class'=>'span-3','type'=>'object:giftcat'),    /* 赠品分类ID */
                                'insert_time'=>array('label'=>'插入时间','class'=>'span-3','type'=>'time:FDATE','readonly'=>true),    /* 插入时间 */
                                'update_time'=>array('label'=>'更新时间','class'=>'span-3','type'=>'time:FDATE','readonly'=>true),    /* 更新时间 */
                                'name'=>array('label'=>'赠品名称','class'=>'span-6','required'=>1),    /* 赠品名称 */
                                'thumbnail_pic'=>array('label'=>'列表页缩略图','class'=>'span-3','readonly'=>true),    /* 缩略图片 */
                                'small_pic'=>array('label'=>'缩略图','class'=>'span-3','readonly'=>true),    /* 小图 */
                                'big_pic'=>array('label'=>'详细图','class'=>'span-3','readonly'=>true),    /* 大图 */
//                                'image_file'=>array('label'=>'自动生成时的原图','class'=>'span-3'),    /* 自动生成时的原图 */
                                'intro'=>array('label'=>'简介','class'=>'span-3'),    /* 简介 */
                                'gift_describe'=>array('label'=>'详细描述','class'=>'span-3','readonly'=>true),    /* 详细说明 */
                                'weight'=>array('label'=>'重量','class'=>'span-3','required'=>true),    /* 重量 */
                                'storage'=>array('label'=>'库存','class'=>'span-1','required'=>true),    /* 库存 */
                                'price'=>array('label'=>'价格','class'=>'span-3'),    /* 价格 */
                                'orderlist'=>array('label'=>'排序','class'=>'span-1'),    /* 排序权值 */
                                'shop_iffb'=>array('label'=>'发布','class'=>'span-1','type'=>'bool','bool'=>'number'),    /* 是否发布 */
                                'limit_num'=>array('label'=>'限购数量','class'=>'span-3'),    /* 最大购买数量 */
                                'limit_start_time'=>array('label'=>'起始时间','class'=>'span-2','type'=>'time','inputType'=>'date','required'=>1),    /* 起始时间 */
                                'limit_end_time'=>array('label'=>'终止时间','class'=>'span-2','type'=>'time','inputType'=>'date','required'=>1),    /* 截止时间 */
                                'limit_level'=>array('label'=>'允许兑换等级','class'=>'span-3','readonly'=>true),    /* 限制会员等级   枚举,逗号分隔  */
                                'ifrecommend'=>array('label'=>'推荐','class'=>'span-1','type'=>'bool','bool'=>'number'),    /* 是否推荐 */
                                'point'=>array('label'=>'积分','class'=>'span-1','required'=>1),    /* 兑换所需积分 */
                                'freez'=>array('label'=>'冻结库存','class'=>'span-3','readonly'=>true),    /* 冻结库存 */
                        );
    }

    function _filter($filter) {
        $where=array(1);
        if(is_array($filter['gcat'])){
            foreach($filter['gcat'] as $giftcat_id){
                if($giftcat_id!='_ANY_'){
                    $cats[] = $giftcat_id;
                }
                if(count($cats)>0){
                    $where[] = 'giftcat_id in ('.implode($cats, ',').')';
                }
            }
        }

        if(is_array($filter['giftcat_id'])){
            foreach($filter['giftcat_id'] as $giftcat_id){
                if($giftcat_id!='_ANY_'){
                    $giftcats[] = 'giftcat_id='.intval($giftcat_id);
                }
            }
            if(count($giftcats)>0){
                $where[] = '('.implode($giftcats,' or ').')';
            }
        }

        if(is_array($filter['point'])){
            foreach($filter['point'] as $point){
                if($point!='_ANY_'){
                    $point = explode('-',$point);
                    $points[] = '(price >='.$point[0].' and price <='.$point[1].')';
                }
            }
            if(count($points)>0){
                $where[] = '('.implode($points,' or ').')';
            }
        }

        if ($filter['name']) {
            $where[] = 'name like\'%'.$filter['name'].'%\'';
        }

        if (isset($filter['shop_iffb'])) {
            if ($filter['shop_iffb']===1) {
                $where[] = 'shop_iffb=\'1\'';
            }else if ($filter['shop_iffb']==0){
                $where[] = 'shop_iffb=\'0\'';
            }
        }

        if (isset($filter['time_ifvalid'])) {
            if ($filter['time_ifvalid']==1) {
                $curTime = time();
                $where[] = 'limit_start_time<='.$curTime.' and limit_end_time>'.$curTime;
            }
        }

        if (isset($filter['storage_ifenough'])) {
            if ($filter['storage_ifenough']===1) {
//                $where[] = '(storage-freez>0)';
                $where[] = 'storage > 0'; //赠品不考虑库存冻结
            }
        }
        return parent::_filter($filter).' and '.implode($where,' and ');
    }

    function getFilter($p){
        $oGift = $this->system->loadModel('trading/giftcat');
        $return['giftcat_ids'] = $oGift->getTypeArr();
//        echox($return);
        return $return;
    }


    function searchOptions(){
        return array(
                'name'=>'赠品名称',
            );
    }

    function getGiftList($pageStart,$pageEnd,&$count,$filter){
        $curTime = time();
        if($filter['gid']){
            $_filter=' and A.giftcat_id="'.$filter['gid'].'"';
        }
        if($filter['ifrecommend']){
            $_filter=' and A.ifrecommend="1"';
        }
        $sSql = 'SELECT * FROM sdb_gift as A
                Left Join  sdb_gift_cat as B ON A.giftcat_id=B.giftcat_id and A.shop_iffb="1" and A.limit_start_time<='.$curTime.' and A.limit_end_time>'.$curTime.'
                where B.shop_iffb="1" '.$_filter.'  and A.disabled!="true"  order by A.orderlist desc';



        $count = $this->db->_count($sSql);
        $sSql.=' limit ' . $pageStart . ','. $pageEnd;
        return  $this->db->select($sSql);
    }

    function getAllList(){
        $curTime = time();
        $sSql = 'SELECT cat,name,gift_id,B.giftcat_id FROM sdb_gift_cat as B Left Join sdb_gift as A ON A.giftcat_id=B.giftcat_id and A.shop_iffb="1" and A.disabled!="true" and A.limit_start_time<='.$curTime.' and A.limit_end_time>'.$curTime.' where B.shop_iffb="1" and B.disabled!="true" order by B.orderlist desc';


        return  $this->db->select($sSql);
    }
    //----------------------------------------------------------
    //前台
    //num: 购买数量
    function isOnSale($aGift, $mlv, $num=1){

        $aGiftLimitLevel = explode(',', $aGift['limit_level']);
        if($mlv<=0){
            return true;
        }

        if ( $aGift['limit_start_time']<time() && $aGift['limit_end_time']>time() && (($aGift['storage']-$aGift['freez'])>=$num) &&
                ($aGift['limit_num']>=$num || intval($aGift['limit_num']==0))
                && in_array($mlv, $aGiftLimitLevel)) {
            return true;
        }else{
            return false;
        }
    }


    function getGiftByIds($aGift) {
        if (is_array($aGift) && !empty($aGift)) {
            $sSql = 'SELECT * FROM sdb_gift WHERE gift_id in ('.implode(',', $aGift).')';
            $aTemp = $this->db->select($sSql);
            return $aTemp;
        }else{
            return false;
        }
    }
    //后台

    function checkStock($giftId,  $chgNum) {
        $chgNum = abs($chgNum);
        if (($this->getStock($giftId)-$this->getFreezStock($giftId) - $chgNum)>=0) {
            return true;
        } else {
            return false;
        }
    }

    function getStock($giftId) {
        $sSql = 'SELECT storage FROM sdb_gift WHERE gift_id = '.intval($giftId);
        $result = $this->db->selectrow($sSql);
        return $result['storage'];
    }

    function getFreezStock($giftId) {
        $sSql = 'SELECT freez FROM sdb_gift WHERE gift_id = '.intval($giftId);
        $result = $this->db->selectrow($sSql);
        return $result['freez'];
    }

    //调整库存
    function adjustStock($giftId, $chgNum, $isDirect=false) {//isDirect 是否不管冻结库存直接扣除
        if ($this->checkStock($giftId, $chgNum)) {
            if ($chgNum>0) {
                $sSql = 'UPDATE sdb_gift SET storage = storage + '.$chgNum.' WHERE gift_id = '.$giftId;
            }else if($chgNum<0){
                if ($isDirect) {
                    $sSql = 'UPDATE sdb_gift SET storage = storage-'.abs($chgNum).' WHERE gift_id = '.$giftId;
                }else{
                    $sSql = 'UPDATE sdb_gift SET storage=storage-'.abs($chgNum).',freez=freez-'.abs($chgNum).' WHERE gift_id = '.$giftId;
                }
            }else{
                return true;
            }

            if ($this->db->exec($sSql)) {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //更改库存
    function changeStock($giftId, $num){
        if (intval($num)<0){
            $num = 0;
        }
        $sSql = "UPDATE sdb_gift SET storage = ".intval($num)." WHERE gift_id = ".intval($giftId);
        if ($this->db->exec($sSql)){
            return true;
        }else{
            return false;
        }
    }

    function freezStock($giftId, $num) {
        $aData = $this->getFieldById($giftId, array('storage', 'freez'));
        $nStorage = $aData['storage'];

        if ($this->checkStock($giftId, $num)) {
            $sSql = 'update sdb_gift set freez=freez+'.abs(intval($num)).' where gift_id='.intval($giftId);
            return $this->db->exec($sSql);
        }else{
            return false;
        }
    }

    function unFreezStock($giftId, $num) {
        if ($num>0) {
            $sSql = 'update sdb_gift set freez=freez-'.abs(intval($num)).' where gift_id='.intval($gift_id);
            $this->db->exec($sSql);
        }
    }

    function toConsign($orderId, $giftId, $sendNum) {
        $sendNum = intval($sendNum);
        if ($this->adjustStock($giftId, -$sendNum)){
            $this->db->exec('UPDATE sdb_gift_items set sendnum=sendnum+'.$sendNum.' WHERE order_id=\''.$orderId.'\' and gift_id='.intval($giftId));
            return true;
        }else{
            return false;
        }
    }

    function toCancel($orderId, $giftId) {
        $aItem = $this->db->selectrow('SELECT nums FROM sdb_gift_items WHERE order_id=\''.$orderId.'\' and gift_id='.intval($giftId));
        $this->unFreezStock($giftId, $aItem['nums']);

    }

    function getOrderItemsList($orderId, $aGiftId) {
        if(is_array($aGiftId) && $aGiftId){
            $sqlWhere = " AND gift_id in (".implode(',', $aGiftId).")";
        }
        $aRet = $this->db->select('SELECT * FROM sdb_gift_items WHERE order_id = \''.$orderId.'\''.$sqlWhere);
        return $aRet;
    }


    function getTypeList($catName='',$isFront=false) {
        $sTemp = '';
        if (isset($catName) && $catName != '') {
            $sTemp .= ' and gc.cat like"%'.$catName.'%" ';
        }
        if ($isFront) {
            $sTemp .= ' and shop_iffb=\'1\'';
        }
        $sSql = 'SELECT * FROM sdb_gift_cat as gc where 1.'.$sTemp.' order by orderlist desc';
        return $this->db->select_b($sSql, PAGELIMIT);
    }
    //+
    function getTypeById($catid) {
        $sql = 'SELECT * FROM sdb_gift_cat WHERE giftcat_id='.$catid;
        return $this->db->selectRow($sql);
    }

    function addType($aData) {
        if ($aData['giftcat_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_gift_cat WHERE giftcat_id='.$aData['giftcat_id']);
            $sSql = $this->db->getUpdateSql($aRs,$aData);
            return (!$sSql || $this->db->exec($sSql));
        }else{
            $aRs = $this->db->query('SELECT * FROM sdb_gift_cat WHERE 0');
            $sSql = $this->db->getInsertSql($aRs,$aData);
            if ($this->db->exec($sSql)){
                return $this->db->lastInsertId();
            }else{
                return false;
            }
        }
    }

    //+
    function getTypeArr(){
        return $this->db->select('SELECT giftcat_id,cat FROM sdb_gift_cat WHERE disabled = \'false\' ORDER BY orderlist desc');
    }

    function getGiftById($nGift) {
//        $sSql = 'SELECT * FROM sdb_gift WHERE gift_id='.$nGift;
        $sSql = 'SELECT g.*,gc.cat FROM sdb_gift as g
                        left join sdb_gift_cat as gc on g.giftcat_id=gc.giftcat_id
                        WHERE g.gift_id='.intval($nGift);
        if($aTemp = $this->db->selectRow($sSql)){
//            $aTemp['content'] =  preg_replace('/[\n\r]+/','',$aTemp['content']);
            return $aTemp;
        }else{
            return false;
        }
    }

    function getFieldById($giftId, $aField=array('*')){
        return $this->db->selectrow("SELECT ".implode(",", $aField)." FROM sdb_gift WHERE gift_id='{$giftId}'");
    }

    function getInitOrder() {
        $aTemp = $this->db->selectRow('select max(orderlist) as orderlist from sdb_gift');
        return $aTemp['orderlist']+1;
    }

    //+
    function saveGift($aData){
        if(!$aData['small_pic']){
            unset($aData['small_pic']);
        }
        if(!$aData['thumbnail_pic']){
            unset($aData['thumbnail_pic']);
        }
        $aData['limit_level'] = implode(',', $aData['limit_level']);
        $aData['limit_start_time'] = strtotime($aData['limit_start_time']);
        $aData['limit_end_time'] = strtotime($aData['limit_end_time']);
        $storager = &$this->system->loadModel('system/storager');
        if ($_FILES['thumbnail_pic']) {
            $aData['thumbnail_pic'] = $storager->save_upload($_FILES['thumbnail_pic'],'gift',array($aData['gift_id'],'thumbnail'));
        }
        if ($_FILES['small_pic']['name']) {
            $aData['small_pic'] = $storager->save_upload($_FILES['small_pic'],'gift',array($aData['gift_id'],'small'));
        }
        if ($_FILES['big_pic']['name']) {
            $aData['big_pic'] = $storager->save_upload($_FILES['big_pic'],'gift',array($aData['gift_id'],'big'));
        }

        if ($aData['gift_id']){
            $aData['update_time'] = time();
            $aRs = $this->db->query('SELECT * FROM sdb_gift WHERE gift_id='.$aData['gift_id']);
            $sSql = $this->db->getUpdateSql($aRs,$aData);
            return (!$sSql || $this->db->exec($sSql));
        }else{
            $aData['insert_time'] = time();
            $aRs = $this->db->query('SELECT * FROM sdb_gift WHERE 0');
            $sSql = $this->db->getInsertSql($aRs,$aData);
            if ($this->db->exec($sSql)){
                return $this->db->lastInsertId();
            }else{
                return false;
            }
        }
    }

    //+
    function delGift($arrId,&$msg) {
        if ($arrId) {
            $sSql = 'delete from sdb_gift where  gift_id in ('.implode($arrId, ',').')';
            if ($this->db->exec($sSql)) {
                return true;
            } else {
                $msg = __('数据删除失败！');
                return false;
            }
        }else{
            $msg = 'no select';
            return false;
        }
    }
}
?>
