<?php
class goods_cat_1Validator extends BaseValidator
{
    function goods_cat_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (!isset($row['cat_name']) || empty($row['cat_name'])) return false;
        
        $row['parent_id'] = isset($row['parent_id']) ? $row['parent_id'] : 0;
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['is_leaf']  = (isset($row['is_leaf']) && $row['is_leaf']) ? 'true' : 'false';
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        $this->updateCache();
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        if (isset($row['is_leaf']))  $row['is_leaf']  = $row['is_leaf'] ? 'true' : 'false';
        return true;
    }
    
    function validateUpdateAfter(&$row)
    {        
        $this->updateCache();
        return true;
    }
    
    function validateDeleteBefore(&$row)
    {
        return true;
    }
    
    function validateDeleteAfter(&$row)
    {                
        $this->updateCache();
        return true;
    }
    
    function updateCache(){
        $cache_file = MEDIA_DIR.'/goods_cat.data';
        if (file_exists($cache_file)) {
            @unlink($cache_file);
        }
    }
} 

?>