<?php

namespace NZord\Helpers;

class NParams{
    protected $params = [];
    function __construct($params){
        $this->params = $params;
    }   
    
    public function get($index,$default=null){
        return isset($this->params[$index]) ? $this->params[$index] : $default;
    }
}
