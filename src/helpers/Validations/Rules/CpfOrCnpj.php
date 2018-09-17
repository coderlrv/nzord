<?php
namespace NZord\Helpers\Validations\Rules;

use Respect\Validation\Rules;

/**
 * 
 * Validação para verificar se cnpf ou cpf estão valido.
 * 
 * 
 */
class CpfOrCnpj extends Rules\AbstractRule
{
	public function validate($input)
    {
        //Checa cnpj
        if(strlen($input) > 14){
            $cnpjRule = new Rules\Cnpj();
            return $cnpjRule->validate($input);
        }else{
            //Checa CPF
            $cpfRule = new Rules\Cpf();
            return $cpfRule->validate($input);
        }
    }
}