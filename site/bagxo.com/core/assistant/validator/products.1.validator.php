<?php
class products_1Validator extends BaseValidator
{
    function products_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['price'] = isset($row['price']) ? $row['price'] : 0;
        $row['name'] = isset($row['name']) ? $row['name'] : '';
        
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        return true;
    }
    
    function validateUpdateAfter(&$row)
    {        
        return true;
    }
    
    function validateDeleteBefore(&$row)
    {
        return true;
    }
    
    function validateDeleteAfter(&$row)
    {                
        return true;
    }
} 

?>