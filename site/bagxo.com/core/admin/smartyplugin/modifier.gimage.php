<?php 
/* 
* Smarty plugin 
* ------------------------------------------------------------- 
* File:     modifier.t.php 
* Type:     modifier 
* Name:     capitalize 
* Purpose:  capitalize words in the string 
* ------------------------------------------------------------- 
*/ 
function smarty_modifier_gimage($ident,$size) 
{ 
    if(!$GLOBALS['_gimage']){
        $system = &$GLOBALS['system'];
        $gimage = $system->loadModel('goods/gimage');
        $GLOBALS['_gimage'] = &$gimage;
    }else{
        $gimage = &$GLOBALS['_gimage'];
    }
    return $gimage->getUrl($ident,$size);
} 
?> 
