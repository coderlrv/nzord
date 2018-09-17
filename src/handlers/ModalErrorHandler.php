<?php
namespace NZord\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModalExceptionHandler
{
    public function __invoke(Request $request, Response $response, $exception)
    {   
        $codeError =  $exception->getCode() ? $exception->getCode() : 500;
        $errors['message'] = $exception->getMessage();
        $errors['codeStatus'] = $codeError;
        
        if($exception->data){
            $errors['data'] = json_encode($exception->data);
        }

        return $response
            ->withStatus($codeError)
            ->withJson($errors);
    }
}