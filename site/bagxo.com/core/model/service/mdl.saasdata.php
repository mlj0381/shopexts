<?php

include_once(dirname(__FILE__).'/mdl.apiclient.php'); 

class mdl_saasdata extends mdl_apiclient {

    function saasdata(){
        $this->key = SAAS_NATIVE_KEY;
        $this->url = SAAS_API_URL;
        parent::modelFactory();
    }
}