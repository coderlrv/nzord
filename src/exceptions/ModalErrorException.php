<?php
/**
 *  Exception ModalErrorException | base/execeptions/ModalErrorException.php
 */

namespace NZord\Exceptions;

/**
 *  Erro retorno modal.
 */
class ModalErrorException extends \Exception
{
    public $data = null;
    // Redefine a exceção de forma que a mensagem não seja opcional
    public function __construct($message,$data=null, $code = 500, Exception $previous = null) {
        // garante que tudo está corretamente inicializado
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }
}