<?php
namespace NZord\Helpers;

use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;

class NValidator extends \Awurth\SlimValidation\Validator
{
    public function getErrorsArray()
    {
        return $this->errors;
    }
    /**
     * Retorna response com json de erros.
     *
     * @param integer $statusCode
     * @return void
     */
    public function responseJsonErrors($statusCode = 400)
    {
        $handle = fopen("php://temp", "wb+");
        $body = new Stream($handle);
        $body->write(json_encode(['statusCode'=>$statusCode,'message'=>$this->errors]));
        $headers = new Headers;
        $headers->set("Content-type", "application/json");

        return new Response($statusCode, $headers, $body);
    }
}
