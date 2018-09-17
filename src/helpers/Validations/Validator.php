<?php
namespace NZord\Helpers\Validations;

/**
 *  Gera funções para validação.
 * 
 */
class Validator extends \Respect\Validation\Validator{

    public static function __callStatic($ruleName, $arguments){
        parent::with('\\Base\\Helpers\\Validations\\Rules');
        return parent::__callStatic($ruleName,$arguments);
    }
}