<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
$db['meta_value_varchar']=array (
  'columns' => 
  array (
    'mr_id' => 
    array (
      'type' => 'number',
      'required' => false,
      'comment' => '关联表的id id',
    ),
    'pk' => 
    array (
      'type' => 'number',
      'required' => true, 
      'comment' => '查询结果集定位基准', 
    ),
    'value' => 
    array (
      'type' => 'varchar(255) NOT NULL default \'\'',
      'required' => true,
      'comment' => 'meta值',
    ),
  ),
  'comment' => 'meta系统varchar类型存值表',
  'index' => 
  array (
    'ind_mr_pk' => 
    array (
      'columns' => 
      array (
        0 => 'mr_id',
        1 => 'pk',
      ),
    ),
    'ind_value' => 
    array (
      'columns' => 
      array (
        0 => 'value',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
);
