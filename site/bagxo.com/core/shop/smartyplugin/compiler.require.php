<?php 
/* 
* Smarty plugin 
* ------------------------------------------------------------- 
* File:     compiler.tplheader.php 
* Type:     compiler 
* Name:     tplheader 
* Purpose:  Output header containing the source file name and 
*           the time it was compiled. 
* ------------------------------------------------------------- 
*/ 
function smarty_compiler_require($tag_args, &$smarty) 
{ 
    $attrs = $smarty->_parse_attrs($tag_args);
    $output = '';

    if (isset($assign_var)) {
        $output .= "ob_start();\n";
    }

    $output .=
        "\$_smarty_tpl_vars = \$this->_tpl_vars;\n";

    $type = ($pos = strpos($smarty->_current_file,':'))?substr($smarty->_current_file,0,$pos):$smarty->default_resource_type;
    $p1=strpos($smarty->_current_file,'/')+1;
    $p2=strpos($smarty->_current_file,'/',$p1);

    if(($type=='user') && (!$p2 || substr($smarty->_current_file,$p1,$p2-$p1)!=='view' )){
        $_params = "array('smarty_include_tpl_file' => 'user:'.\$this->theme.'/'.{$attrs['file']}, 'smarty_include_vars' => array())";
    }else{
        $_params = "array('smarty_include_tpl_file' =>  \$this->template_exists('user:'.\$this->theme.'/view/'.{$attrs['file']})?'user:'.\$this->theme.'/view/'.{$attrs['file']}:'shop:'.{$attrs['file']}, 'smarty_include_vars' => array())";
    }

    $output .= "\$this->_smarty_include($_params);\n" .
    "\$this->_tpl_vars = \$_smarty_tpl_vars;\n" .
    "unset(\$_smarty_tpl_vars);\n";

    if (isset($assign_var)) {
        $output .= "\$this->assign(" . $assign_var . ", ob_get_contents()); ob_end_clean();\n";
    }

    return $output;
}
?>
