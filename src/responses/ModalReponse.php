<?php
namespace NZord\Responses;

use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;

class ModalResponse extends Response
{
    public function __construct($message, $status = 200,$ex = null){   
        $msg = ['statusCode'=>$status,'message'=>$message ];
        if($ex){
            Logger($ex->getMessage(),null,500);

            //Envia erro usuario somente quando debug tiver ativo.
            $settings = require(__DIR__.'/../settings.php');
            if($settings['settings']['debug']){
                $msg['error'] = $ex->getMessage();
            }
        }
       
        $handle = fopen("php://temp", "wb+");
        $body = new Stream($handle);
        $body->write(json_encode($msg));
        $headers = new Headers;
        $headers->set("Content-type", "application/json");
       
        parent::__construct($status, $headers, $body);
    }
}