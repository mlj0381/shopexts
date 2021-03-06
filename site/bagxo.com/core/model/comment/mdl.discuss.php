<?php
include_once(dirname(__FILE__).'/mdl.comment.php');
class mdl_discuss extends mdl_comment{
    var $idColumn = 'comment_id';
    var $textColumn = 'comment_id';
    var $appendCols = 'adm_read_status';
    var $adminCtl = 'goods/discuss';
    var $defaultCols = 'p_index,title,goods_id,author,time,lastreply,adm_read_status,levelname,display,reply_name';
    var $defaultOrder = array('comment_id','DESC');
    var $tableName = 'sdb_comments';

    function getColumns(){
        return array(
                        'comment_id' => array('label'=>'序号','class'=>'span-3'),    /* 自增ID */
                        'for_comment_id' => array('label'=>'对comments的回复','class'=>'span-3'),    /* 对comments的回复 */
                        'goods_id' => array('label'=>'商品名称','class'=>'span-4','type'=>'object:goods'),    /* 商品id*/
                        'object_type' => array('label'=>'评论类型','class'=>'span-3'),    /* 评论类型 */
                        'author_id' => array('label'=>'会员(后台管理员)id','class'=>'span-3'),    /* 会员(后台管理员)id */
                        'author' => array('label'=>'评论人','class'=>'span-2'),    /* 留言者名称 */
                        'levelname' => array('label'=>'会员等级','class'=>'span-2'),    /* 会员等级 */
                        'contact' => array('label'=>'联系方式','class'=>'span-3'),    /* 联系方式 */
                        'adm_read_status' => array('label'=>'已阅','class'=>'span-1','type'=>'adm_read_status'),    /* 管理员阅读标识 */
                        'time' => array('label'=>'评论时间','class'=>'span-2','type'=>'time:SDATE_STIME'),    /* 时间值 */
                        'lastreply' => array('label'=>'最后回复时间','class'=>'span-2','type'=>'time:SDATE_STIME'),    /* 最后回复时间 */
                        'reply_name' => array('label'=>'最后回复人','class'=>'span-2'),    /* 最后回复人 */
                        'title' => array('label'=>'标题','class'=>'span-3','type'=>'title'),    /* 标题 */
                        'comment' => array('label'=>'内容','class'=>'span-3','comment'),    /* 内容 */
                        'ip' => array('label'=>'IP','class'=>'span-3'),    /*  */
                        'display' => array('label'=>'显示','class'=>'span-2','type'=>'display'),    /* 显示 */
                        'p_index' => array('label'=>'首页置顶','class'=>'span-2','type'=>'p_index')    /*  */
                );
    }
    
    function _filter($filter){
        $filter['object_type'] = 'discuss';
        $where[] = 'for_comment_id IS NULL';
        if(is_array($filter['adm_read_status'])){
            foreach($filter['adm_read_status'] as $val){
                if($val!='_ANY_'){
                    $aRead[] = 'adm_read_status=\''.$val.'\'';
                }
            }
            if(count($aRead)>0){
                $where[] = '('.implode($aRead,' OR ').')';
            }
        }
        if(isset($filter['goods_name']) && $filter['goods_name'] !== ''){
            $aId = array(0);
            foreach($this->db->select('SELECT goods_id FROM sdb_goods WHERE name LIKE \'%'.$filter['goods_name'].'%\'') as $rows){
                $aId[] = $rows['goods_id'];
            }
            $where[] = 'goods_id IN ('.implode(',', $aId).')';
            unset($filter['goods_name']);
        }

        if(isset($filter['mem_name']) && $filter['mem_name'] !== ''){
            $aId = array(0);
            foreach($this->db->select('SELECT member_id FROM sdb_members WHERE uname = \''.$filter['mem_name'].'\'') as $rows){
                $aId[] = $rows['member_id'];
            }
            $where[] = 'author_id IN ('.implode(',', $aId).')';
            unset($filter['mem_name']);
        }
        $where = implode(' AND ', $where);

        return parent::_filter($filter)." AND ".$where;
    }
    
    /**
     * is_highlight 
     * 高亮finder的行
     * 
     * @param mixed $row 
     * @access public
     * @return void
     */
    function is_highlight($row){
        if($row['adm_read_status'] == 'false') return 1;
        else return 0;
    }
    
    function modifier_p_index(&$rows){
        $status = array(1=>'已置顶',
                    0=>'' );
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }
    function modifier_title(&$rows){
        foreach($rows as $k => $v){
            $rows[$k]=htmlspecialchars($rows[$k]);
        }
    }
    function modifier_comment(&$rows){
        foreach($rows as $k => $v){
            $rows[$k]=htmlspecialchars($rows[$k]);
        }
    }
    
    function modifier_adm_read_status(&$rows){
        $status = array('true'=>'是',
                    'false'=>'否' );
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }
    
    function modifier_display(&$rows){
        $status = array('true'=>'显示',
                    'false'=>'不显示' );
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }
    
    function searchOptions(){
        return array(
                'goods_name'=>'商品名称',
                'mem_name'=>'会员名称',
            );
    }
}
?>