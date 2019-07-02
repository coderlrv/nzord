<?php
namespace NZord\Helpers\Validations;

/**
 *  Gera funções para validação.
 * 
 */
class Validator extends \Respect\Validation\Validator{

    public static function __callStatic($ruleName, $arguments){
        parent::with('\\NZord\\Helpers\\Validations\\Rules');
        return parent::__callStatic($ruleName,$arguments);
    }
}