<?php
function smarty_function_widgets($params, &$smarty){
    if(!isset($smarty->widgets_mdl)){
        $system = &$GLOBALS['system'];
        $smarty->widgets_mdl = &$system->loadModel('content/widgets');
    }
    return $smarty->widgets_mdl->load($smarty->files[0],intval($smarty->_wgbar[$smarty->files[0]]++),isset($params['id'])?$params['id']:null);
}
?>
