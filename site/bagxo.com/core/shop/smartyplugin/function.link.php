<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.getlink.php
 * Type:     function
 * Name:     eightball
 * Purpose:  outputs a random magic answer
 * -------------------------------------------------------------
 */

function smarty_function_link($params, &$smarty)
{
    $args=isset($params['args'])?$params['args']:array();
    foreach($params as $key=>$val){
        if(preg_match('/^arg([0-9]+)$/',$key,$matches)){
            $args[$matches[1]]=str_replace('-', '@', $val);    //字符串中含有“-”替换成@,临时解决
        }
    }
    return $smarty->system->mkUrl($params['ctl'],$params['act'],$args,$params['extname']?$params['extname']:'html');
}
?>