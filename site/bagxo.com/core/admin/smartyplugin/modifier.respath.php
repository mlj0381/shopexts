<?php 
/* 
* Smarty plugin 
* ------------------------------------------------------------- 
* File:     modifier.capitalize.php 
* Type:     modifier 
* Name:     capitalize 
* Purpose:  capitalize words in the string 
* ------------------------------------------------------------- 
*/ 
function smarty_modifier_respath($path) 
{ 
    if(defined('__PKG__'))
    return '?ctl=sfile&act=res&use_pkg='.__PKG__.'&p[0]='.$path; 
    else
    return '?ctl=sfile&act=res&p[0]=/'.$path; 
} 
?> 
