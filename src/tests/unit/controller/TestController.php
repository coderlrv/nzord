<?php 
namespace Modulos\Tests;

use Slim\Http\Request;
use Slim\Http\Response;

class TestController {
    public function form(Reques $request, Response $response, $args = null){
        return [
            'test'=>'testes'
        ];
    }
}