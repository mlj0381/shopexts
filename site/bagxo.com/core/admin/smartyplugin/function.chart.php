<?php
function smarty_function_chart($params, &$smarty){

    foreach($params['chart'] as $k=>$v) {
        if ($k == 'data') {
            foreach($v as $kk=>$vv) {
                foreach($vv as $kkk=>$vvv) {
                    $data['data'][$kk][] = array($kkk,$vvv);
                }
            }
        } elseif($k == 'cols') {
            foreach($v as $kk=>$vv) {
                $data['cols'][] = array('v'=>$kk,'label'=>$vv);    
            }
        }
    }
    $GLOBALS['_chartId']++;
    $GLOBALS['ajaxdata']['charts']['_chart_'.$GLOBALS['_chartId']] = $data;
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') != false) { //IE
        $html='<div>&nbsp;<canvas id="_chart_'.($GLOBALS['_chartId']).'" width="'.($params['width']?$params['width']:600).'" height="'.($params['height']?$params['height']:300).'"></canvas></div>';
    } else { 
        $html='<canvas id="_chart_'.($GLOBALS['_chartId']).'" width="'.($params['width']?$params['width']:600).'" height="'.($params['height']?$params['height']:300).'"></canvas>';
    }
    return $html;
}

?>
