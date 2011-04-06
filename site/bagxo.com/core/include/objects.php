<?php
$objects = array(
        /*对象名                model */
        'goods'                =>'goods/products',
        'order'                =>'trading/order',
        'comment'            =>'comment/comment',
        'discuss'            =>'comment/discuss',
        'gask'                =>'comment/gask',
        'article'            =>'content/article',
        'member'            =>'member/member',
        'memlevel'            =>'member/level',
        'memmessage'        =>'resources/message',
        'msgbox'            =>'resources/msgbox',
        'shopbbs'                =>'resources/shopbbs',
        'payment'            =>'trading/payment',
        'refund'            =>'trading/refund',
        'shipping'            =>'trading/shipping',
        'reship'            =>'trading/reship',
        'activity'            =>'trading/promotionActivity',
        'promotion'            =>'trading/promotion',
        'coupon'            =>'trading/coupon',
        'couponGenerate'    =>'trading/couponGenerate',
        'exchangeCoupon'    =>'trading/exchangeCoupon',
        'gift'                =>'trading/gift',
        'giftcat'            =>'trading/giftcat',
        'package'            =>'trading/package',
        'operator'            =>'admin/operator',
        'products'            =>'goods/finderPdt',
        'setting'            =>'system/setting',
        'goodscat'            =>'goods/productCat',
        'gtype'                =>'goods/gtype',
        'brand'                =>'goods/brand',
        'delivery'            =>'trading/delivery',
        'tmpimage'            =>'resources/tmpimage',
        'gnotify'            =>'goods/goodsNotify',
        'advance'            =>'member/advance',
        'spec'            =>'goods/specification',

/*        'bill'            =>'bill',
        'billConsign'        =>'billConsign',
        'billRefund'        =>'billRefund',
        'billReship'        =>'billReship'*/
//        'promotionActivity' => 'trading/promotionActivity',
    );
    if (defined('CUSTOM_CORE_DIR')&&file_exists(CUSTOM_CORE_DIR.'/include/customobjects.php')){
        include(CUSTOM_CORE_DIR.'/include/customobjects.php');
        if (is_array($cumobjects)){
            $objects=array_merge($objects,$cumobjects);
        }
    }
?>
