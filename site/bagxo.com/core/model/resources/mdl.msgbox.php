<?php
include_once(dirname(__FILE__).'/mdl.message.php');
class mdl_msgbox extends mdl_message{

    var $actionView = 'member/message/finder_action.html'; //默认的动作html模板,可以为null
    var $idColumn = 'msg_id';
    var $textColumn = 'msg_id';
    var $appendCols = 'unread';
    var $adminCtl = 'member/msgbox';
    var $defaultCols = 'msg_from,from_type,date_line,subject,is_sec,unread';
    var $defaultOrder = array('msg_id','desc');
    var $tableName = 'sdb_message';

    function getColumns(){
        return array(
                    'msg_id'=>array('label'=>'序号','class'=>'span-3'),    /* 自增ID */
                    //'for_id'=>array('label'=>'回复哪个信件的','class'=>'span-3'),    /* 回复哪个信件的 */
                    'msg_from'=>array('label'=>'发送者','class'=>'span-2','type'=>'msg_from'),    /* 发信人用户名/留言人姓名 */
                    //'from_id'=>array('label'=>'发信人id/若为非会员留言该值为默认值0','class'=>'span-3'),    /* 发信人id/若为非会员留言该值为默认值0 */
                    //'from_type'=>array('label'=>'留言者类型','class'=>'span-2','type'=>'form_type'),    /* 0代表会员，1代表管理员，2代表非会员 */
                    //'to_id'=>array('label'=>'收信人id','class'=>'span-3'),    /* 收信人id */
                    //'to_type'=>array('label'=>'0代表会员，1代表管理员','class'=>'span-3'),    /* 0代表会员，1代表管理员 */
//          'unread'=>array('label'=>'是否已读','class'=>'span-2','type'=>'bool'),    /* 收件人是否已读 */
                    //'folder'=>array('label'=>'属于哪个文件夹(inbox表收件箱,outbox表草稿箱)','class'=>'span-3'),    /* 属于哪个文件夹(inbox表收件箱,outbox表草稿箱) */
//                    'email'=>array('label'=>'发送者邮箱','class'=>'span-3'),    /* 留言人邮箱 */
//                    'tel'=>array('label'=>'发送者电话','class'=>'span-3'),    /* 留言人电话 */
                    'subject'=>array('label'=>'消息标题','class'=>'span-4','type'=>'subject'),    /* 信件标题/留言不需要 */
                    'message'=>array('label'=>'内容','class'=>'span-3','type'=>'message'),    /* 留言和信件的内容 */
                    //'rel_order'=>array('label'=>'关联订单号 ，标识为订单留言','class'=>'span-3'),    /* 关联订单id ，标识为订单留言 */
                    'date_line'=>array('label'=>'时间','class'=>'span-3','type'=>'time:SDATE_STIME'),    /* 产生时间 */
                    'is_sec'=>array('label'=>'作为留言','class'=>'span-2','type'=>'is_sec'),    /* 是否保密 */
                    //'del_status'=>array('label'=>'0表未删除，1表由收件人删除，2表由收件人删除','class'=>'span-3'),    /* 0表未删除，1表由收件人删除，2表由收件人删除 */
                );
    }

    function is_highlight($row){
        if($row['unread'] == '1') return 0;
        else return 1;
    }
    
    function modifier_form_type(&$rows){
        $status = array(0=>'会员',
                    1=>'管理员',
                    2=>'非会员' );
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }
    function modifier_message(&$rows){
        foreach($rows as $k => $v){
            $rows[$k] = htmlspecialchars($rows[$k]);
        }
    }
    function modifier_subject(&$rows){
        foreach($rows as $k => $v){
            $rows[$k] = htmlspecialchars($rows[$k]);
        }
    }
    function modifier_msg_from(&$rows){
        foreach($rows as $k => $v){
            $rows[$k] = htmlspecialchars($rows[$k]);
        }
    }
    function modifier_is_sec(&$rows){
        $status = array('true'=>'否',
                    'false'=>'是');
        foreach($rows as $k => $v){
            $rows[$k] = $status[$v];
        }
    }

    function searchOptions(){
        return array('msg_from'=>'留言者',
            'keyword'=>'留言标题');
    }

    function _filter($filter){
        $filter['to_type'] = 1;
        $where[] = 'folder = \'inbox\'';
        $where[] = 'for_id = 0';
        $where[] = 'rel_order = 0';
        
        if($filter['msg_from']){
            $where[] = "msg_from ='".$filter['msg_from']."'";
        }
        if($filter['keyword']){
            $where[] = "subject like '%".$filter['keyword']."%'";
        }
        if($filter['del_status']){
            $where[] = 'del_status =\''.intval($filter['del_status']).'\'';
        }

        if($filter['is_sec']){
            $where[] = "is_sec ='".$filter['is_sec']."'";
        }
        if($filter['to_id']){
            $where[] = "(to_id ='".$filter['to_id']."' or to_id = 0)";
        }

        if($filter['to_type']){
            $where[] = "to_type ='".$filter['to_type']."'";
        }
        
        if($filter['unread']){
            $where[] = "unread ='".$filter['unread']."'";
        }
        return parent::_filter($filter).' AND '.implode($where,' AND ');

    }
    function delMessage($aMid){
        $sSql = 'DELETE FROM sdb_message WHERE';
        if(count($aMid)>0){
            $sSql .= ' msg_id IN ('.implode(',',$aMid).')';
            return $this->db->exec($sSql);
        }
        return false;
    }
    
    function revert($aData){
        if(!$aData['for_id']){
            trigger_error(__('保存失败：留言ID丢失'), E_USER_ERROR);
            return false;
        }
        $aData['date_line'] = time();
        $aData['msg_from'] = $this->getOpNameById($aData['from_id']);
        $aData['from_type'] = 1;
        $aData['unread'] = '0';
        $aData['folder'] = 'inbox';
        $aData['is_sec'] = 'false';
        $aRs = $this->db->query('SELECT * FROM sdb_message WHERE 0');
        $sSql = $this->db->getInsertSql($aRs,$aData);
        if($this->db->exec($sSql)) {
            $aMsg = $this->getFieldById($aData['for_id'], array('is_sec', 'from_type'));
            if($aMsg['from_type'] == 2 && $aMsg['is_sec'] == 'true'){
                $aMsg['is_sec'] = 'false';
                $aRs = $this->db->query('SELECT * FROM sdb_message WHERE msg_id='.$aData['for_id']);
                $sSql = $this->db->getUpdateSql($aRs,$aMsg);
                $this->db->exec($sSql);
            }
            return true;
        }else{
            trigger_error(__('保存失败：'.$sSql), E_USER_ERROR);
            return false;
        }
    }
    
    function toDisplay($msg_id, $status){
        
        $this->db->exec('UPDATE sdb_message SET is_sec = \''.$status.'\' WHERE msg_id = '.intval($msg_id));
        return true;
    }
    
    //会员留言列表
    function getMsgList($filter,$nPage) {
        $aRs = $this->db->select_f("SELECT * FROM sdb_message ".$this->listFilter($filter).' order by date_line desc',$nPage,PERPAGE);
        if($filter['from_type'] == 0){
            foreach($aRs['data'] as $key => $val){
                
                if($val['for_id']!=0){
                    if(!$this->db->selectrow('SELECT msg_id FROM sdb_message WHERE msg_id='.$val['for_id'].' and disabled="false"')){
                        unset($aRs['data'][$key]);
                        break;
                    }
                }
                    if($val['to_type'] == 0){
                        $aTmp[$val['to_id']] = $key;
                        $aUser[$key] = $val['to_id'];

                        $tmp = $this->db->selectrow('SELECT member_id,uname FROM sdb_members WHERE member_id='.$val['to_id']);
                        $aRs['data'][$key]['to_name'] = $tmp['uname'];
                    }

            }
        }
        return $aRs;
    }
    
    function listFilter($filter){
        $where = array(1);
        if(isset($filter['from_id'])) $where[] = 'from_id = '.intval($filter['from_id']);
        if(isset($filter['from_type'])) $where[] = 'from_type = '.intval($filter['from_type']);
        if(isset($filter['to_id'])) $where[] = 'to_id = '.intval($filter['to_id']);
        if(isset($filter['to_type'])) $where[] = 'to_type = '.intval($filter['to_type']);
        if(isset($filter['folder'])) $where[] = 'folder = \''.$filter['folder'].'\'';
        if(isset($filter['is_sec'])) $where[] = 'is_sec = \''.$filter['is_sec'].'\'';
        if($filter['del_status']) $where[] = 'del_status != \''.intval($filter['del_status']).'\'';
        return 'WHERE '.implode(' AND ',$where);
    }
    
//=====================

    function updateRevert($aData,&$msg) {
        if(!$aData['for_id'] || !$aData['revert_id']){
            $msg = __('参数丢失！');
            return false;
        }
        $nRevertId = $aData['revert_id'];
        $nForId = $aData['for_id'];
        unset($aData['for_id']);
        unset($aData['revert_id']);
        $aTemp['revert_time'] = $aData['post_date'] = time();
        $aRs = $this->db->query('SELECT * FROM sdb_message WHERE msg_id='.$nRevertId);
        $sSql = $this->db->getUpdateSql($aRs,$aData);
        if(!$sSql || $this->db->exec($sSql)) {
            $aRs = $this->db->query('SELECT * FROM sdb_message WHERE msg_id='.$nForId);
            $sUpdateSql = $this->db->getUpdateSql($aRs,$aTemp);
            if($sUpdateSql) $this->db->exec($sUpdateSql);
            return true;
        } else {
            $msg = __('保存失败！');
            return false;
        }
    }

    #<<<<<<<前台部分>>>>>>>
    function getMemIdByUName($sName){
        $aRs = $this->db->selectrow("SELECT member_id FROM sdb_members WHERE uname='".$sName."'");
        return $aRs['member_id'];
    }
    function getMemUNameById($nMid){
        $aRs = $this->db->selectrow("SELECT uname FROM sdb_members WHERE member_id=".$nMid);
        return $aRs['uname'];
    }
    function getOpNameById($nOpId){
        if(!$this->opName){
            $aRs = $this->db->selectrow("SELECT op_id, username FROM sdb_operators WHERE op_id=".$nOpId);
            $this->opName = $aRs['username'];
        }
        return $this->opName;
    }
    function getOpId(){
        $aRs = $this->db->selectrow("SELECT op_id FROM sdb_operators WHERE super=1");
        return $aRs['op_id'];
    }
    
    /**
    * sendMsg
    *
    * @param int $from        发送人id
    * @param int $to        收信人id
    * @param string $meessage        信件内容
    * @param mixed  $options        其他参数 具体如下：rel_order:定单id
                                                    is_sec:信件是否保密值为字符窜形式的'true'和'false' 默认为'false'
                                                    from_type:是否来自管理员 1代表是，0代表会员 默认为0
                                                    to_type:是否发给管理员 1代表是，0代表会员 默认为0
                                                    msg_from:发送者的用户名,如果调用者不易取得发送者的用户则不要传该参数就可
                                                    subject: 信件的标题 若为空则默认值为‘无标题’
                                                    folder:'inbox'发送，'outbox'不发送存入草稿箱  默认是发送
    * @access public
    * @return boolean
    */
    //$options = array(msg_from=>username,'rel_order'=>order_id,'is_sec'=>'true','from_type'=>1,'to_type'=>0);
    function sendMsg($from,$to,$meessage,$options=false){
        $aData['from_id'] = $from;
        $aData['to_id'] = $to;
        $aData['from_type'] = isset($options['from_type'])?$options['from_type']:0;
        $aData['msg_from'] = $aData['from_type']?
            (isset($options['msg_from'])?$options['msg_from']:$this->getOpNameById($from)):
            (isset($options['msg_from'])?$options['msg_from']:$this->getMemUNameById($from));
        $aData['to_type'] = isset($options['to_type'])?$options['to_type']:0;
        $aData['subject'] =  isset($options['subject'])?$options['subject']:__('无标题');
        $aData['message'] = $meessage;
        $aData['unread'] = '0';
        $aData['is_sec'] = (isset($options['is_sec']) && $options['is_sec'] != '')?$options['is_sec']:'true';
        $aData['folder'] = isset($options['folder'])?$options['folder']:'inbox';
        $aData['date_line'] = time();
        
        if($options['msg_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_message WHERE msg_id='.intval($options['msg_id']));
            $sSql = $this->db->GetUpdateSql($aRs,$aData,true);
        }else{
            $aRs = $this->db->query('SELECT * FROM sdb_message WHERE 0');
            $sSql = $this->db->GetInsertSql($aRs,$aData);
        }
        if(!$sSql || $this->db->exec($sSql)){
            if($options['folder']=='inbox'){
                $msgNun = $this->db->selectrow('SELECT unreadmsg FROM sdb_members WHERE member_id='.$to);
                $aRs = $this->db->query('SELECT unreadmsg FROM sdb_members WHERE member_id='.$to);
                $sSql = $this->db->getUpdateSql($aRs,array('unreadmsg'=>$msgNun['unreadmsg']+1));
                if($sSql) $this->db->exec($sSql);
            }
            return true;
        }
        return false;
    }

    //前台读取信息
    function getMsgById($nMsgId) {
        $aTemp = $this->db->selectrow("SELECT to_id,to_type, subject, message, unread, is_sec, folder
                                            FROM sdb_message 
                                            WHERE msg_id=" . $nMsgId .' and disabled="false"');
        if($aTemp) {
            if($aTemp['unread']=='0'){
                $aRs = $this->db->query('SELECT unread FROM sdb_message WHERE msg_id='.$nMsgId);
                $sSql = $this->db->getUpdateSql($aRs,array('unread'=>'1'));
                if($sSql)    $this->db->exec($sSql);
                $msgNum = $this->db->selectrow('SELECT count(msg_id) as num FROM sdb_message WHERE unread="0" and folder="inbox" and to_type='.$aTemp['to_type'].' and to_id='.$aTemp['to_id']);
                $aRs = $this->db->query('SELECT unreadmsg FROM sdb_members WHERE member_id='.$aTemp['to_id']);
                $sSql = $this->db->getUpdateSql($aRs,array('unreadmsg'=>$msgNum['num']));
                if($sSql) $this->db->exec($sSql);
            }
        }
        return $aTemp;
    }

    function getMsgInfo($nMsgId, $status='send'){
        $aRs = $this->db->selectrow('SELECT * FROM sdb_message WHERE msg_id='.$nMsgId);
        if($aRs){
            if($status == 'send')
                $aRs['msg_to'] = $aRs['to_type']?__('管理员'):$this->getMemUNameById($aRs['to_id']);
            else
                $aRs['msg_to'] = $aRs['from_type']?__('管理员'):$this->getMemUNameById($aRs['from_id']);
        }
        return $aRs;
    }

    function delInBoxMsg($aMsgId) {
        foreach($aMsgId as $val){
            $val = intval($val);
            if($val){
                $aTmp[] = $val;
            }
        }
        if($aTmp){
            $this->db->exec('DELETE FROM sdb_message WHERE msg_id IN ('.implode(',',$aTmp).') AND del_status=\'2\'');
            $this->db->exec('UPDATE sdb_message SET del_status=\'1\' WHERE msg_id IN ('.implode(',',$aTmp).')');
        }
        return true;
    }
    function delTrackMsg($aMsgId){
        foreach($aMsgId as $val){
            $val = intval($val);
            if($val){
                $aTmp[] = $val;
            }
        }
        if($aTmp){
            $this->db->exec('DELETE FROM sdb_message WHERE msg_id IN ('.implode(',',$aTmp).') AND del_status=\'1\'');
            $this->db->exec('UPDATE sdb_message SET del_status=\'2\' WHERE msg_id IN ('.implode(',',$aTmp).')');
        }
        return true;
    }
    function delOutBoxMsg($aMsgId){
        foreach($aMsgId as $val){
            $val = intval($val);
            if($val){
                $aTmp[] = $val;
            }
        }
        if($aTmp){
            $this->db->exec('DELETE FROM sdb_message WHERE msg_id IN ('.implode(',',$aTmp).')');
        }
        return true;
    }
    #<<<<<<前台部分－－结束>>>>>>>
    //
    function getTotalMsg($nMId) {
        $aRow = $this->db->selectrow('SELECT COUNT(msg_id) AS num FROM sdb_message WHERE (from_id='.intval($nMId).' and from_type=0) OR (to_id='.intval($nMId).' and to_type=0) and disabled="false"');
        return $aRow['num'];
    }
    //获取某会员的留言及回复情况
    function getMsgListByMemId($nMId) {
        $aRs = $this->db->select('SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username
                                                    FROM sdb_message s
                                                    LEFT JOIN sdb_members m ON s.to_id = m.member_id
                                                    LEFT JOIN sdb_operators o ON s.to_id = o.op_id 
                                                    WHERE (s.from_id='.$nMId.' AND from_type=0 and s.disabled="false") OR (s.to_id='.$nMId.' AND to_type=0 and s.disabled="false")
                                                    ORDER BY s.msg_id');
        if($aRs){
            foreach($aRs as $key=>$val){
                $aRs[$key]['msg_to'] = $val['to_type']==0?$val['uname']:$val['username'];
            }
        }
        return $aRs;
    }
    //依id获取留言的详细信息#
    function adminGetMsgById($nMsgId) {
        return $this->db->selectrow('SELECT m.subject,temp.msg_id FROM sdb_message m
                                                LEFT JOIN sdb_message temp ON temp.for_id = m.msg_id WHERE m.msg_id='.$nMsgId);
    }
    //依id获取留言和回复的详细信息 后台会员中心用到
    function getMsgByIdBG($nMsgId) {
         $aRs = $this->db->selectrow('SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.message, s.date_line, m.uname, o.username
                                                    FROM sdb_message s
                                                    LEFT JOIN sdb_members m ON s.to_id = m.member_id
                                                    LEFT JOIN sdb_message temp ON temp.for_id = s.msg_id 
                                                    LEFT JOIN sdb_operators o ON s.to_id = o.op_id 
                                                    WHERE s.msg_id='.$nMsgId);
        $aRs['msg_to'] = $val['to_type']==0?$aRs['uname']:$aRs['username'];
        $aRs['date_line'] = timeFormat($aRs['date_line']);
        return $aRs;
    }
    //依id获取留言和回复的详细信息 留言管理用的
    function getMsgByIdBGM($nMsgId) {
         $aRs = $this->db->selectrow('SELECT s.msg_id,s.msg_from,s.from_id,s.email,s.tel,s.subject,s.message,s.date_line,
                                             m.email as m_email,m.tel as m_tel, m.sex,temp.for_id AS revert
                                                    FROM sdb_message s
                                                    LEFT JOIN sdb_members m ON s.from_id = m.member_id
                                                    LEFT JOIN sdb_message temp ON temp.for_id = s.msg_id 
                                                    WHERE s.msg_id='.$nMsgId);
        $aRs['date_line'] = timeFormat($aRs['date_line']);
        if(!$aRs['email']) $aRs['email'] = $aRs['m_email'];
        if(!$aRs['tel']) $aRs['tel'] = $aRs['m_tel'];
        return $aRs;
    }

    function getRevertById($nMsgId){
        $aRs = $this->db->select('SELECT subject,message,date_line FROM sdb_message WHERE for_id='.$nMsgId);
        if($aRs){
            foreach($aRs as $key=>$val){
                $aRs[$key]['date_line'] = timeFormat($val['date_line']);
            }
        }
        return $aRs;
    }
    //后台留言管理
    function getAdminMsgList($sKeyword){
        $sKeyword = trim($sKeyword);
        if($sKeyword != ''){
            $aRs = $this->db->select_b("SELECT m.*,mb.email as m_email,mb.tel as m_tel, temp.for_id AS revert
                                                FROM sdb_message m 
                                                LEFT JOIN sdb_members mb ON m.from_id=mb.member_id 
                                                LEFT JOIN sdb_message temp ON temp.for_id = m.msg_id 
                                                WHERE m.to_type=1 
                                                AND (m.subject LIKE '%".$sKeyword."%' OR m.message LIKE '%".$sKeyword."%')
                                                ORDER BY m.msg_id DESC",PAGELIMIT);
        }else{
            $aRs = $this->db->select_b('SELECT m.*, mb.email as m_email, mb.tel as m_tel, temp.for_id AS revert
                                                FROM sdb_message m 
                                                LEFT JOIN sdb_members mb ON m.from_id=mb.member_id 
                                                LEFT JOIN sdb_message temp ON temp.for_id = m.msg_id 
                                                WHERE m.to_type=1 
                                                ORDER BY m.msg_id DESC',PAGELIMIT);
            if($aRs && $aRs['main']){
                foreach($aRs['main'] as $key=>$val){
                    if(!$aRs['main'][$key]['email']) $aRs['main'][$key]['email'] = $val['m_email'];
                    if(!$aRs['main'][$key]['tel']) $aRs['main'][$key]['tel'] = $val[$key]['m_tel'];
                }
            }
        }
        return $aRs;
    }

    //读取会员最新消息数量
    function getNewMessageNum($memberid){
        $aMsg = $this->db->selectrow('SELECT count(*) AS unreadmsg FROM sdb_message WHERE to_type = 0 AND del_status != \'1\' AND folder = \'inbox\' AND unread = \'0\' AND to_id ='.intval($memberid));
        return $aMsg['unreadmsg'];
    }
    
    function getOrderMessage($orderid){
        $row = $this->db->select('SELECT * FROM sdb_message WHERE rel_order = \''.$orderid.'\' and disabled="false"');
        return $row;
    }
}
?>