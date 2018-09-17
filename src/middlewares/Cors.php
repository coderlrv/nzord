<?php
namespace NZord\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Cors{
    public function __invoke(Request $request, Response $response, $next){
        $response = $next($request, $response);

        $httpOrigin = $request->getServerParam('HTTP_ORIGIN');
        if( isset($httpOrigin) ){
            $response = $response
                ->withHeader("Access-Control-Allow-Origin", $httpOrigin)
                ->withHeader('Access-Control-Allow-Credentials', true)
                ->withHeader('Access-Control-Max-Age', 86400); // cache por 1 dia
        }
        
        if($request->isOptions()){
            if($request->getServerParam('HTTP_ACCESS_CONTROL_REQUEST_METHOD')){
                $response = $response->withHeader('Access-Control-Allow-Methods','GET, POST, PUT, PATCH, DELETE, OPTIONS');
            }
            //Seta cabecalho
            $headers = $request->getServerParams('HTTP_ACCESS_CONTROL_REQUEST_HEADERS');
            if($headers){
                $response = $response
                    ->withHeader('Access-Control-Allow-Methods','GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers',$headers);
            }
        }
        
        return $response;
    }
}