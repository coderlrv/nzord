<?php
namespace NZord\Helpers;
use NZord\Responses\ModalResponse;

class NValidator extends \Awurth\SlimValidation\Validator{


    public function getErrorsArray(){
        return $this->errors;
    }
    /**
     * Retorna response com json de erros.
     *
     * @param integer $statusCode
     * @return void
     */
    public function responseJsonErrors($statusCode=400){
        return new ModalResponse($this->errors,$statusCode);
    }
}