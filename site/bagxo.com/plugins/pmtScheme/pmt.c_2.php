<?php
class pmt_c_2{
    var $name = '优惠券规则--商品直接打折，如全场女鞋8折。可以对商品任意折扣，适合低价清货促销';
    var $memo = '商品直接打折，如全场女鞋8折。可以对商品任意折扣，适合低价清货促销';
    var $pmts_solution = array(
        'type'=>'goods',
        'condition'=>array(
            array('mLev')
        ),            
        'method'=>array(
            array('discount'),
        )
    );
    var $pmts_type = PMT_SCHEME_COUPON;
}
?>
