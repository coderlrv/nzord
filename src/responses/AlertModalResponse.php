<?php
namespace NZord\Responses;

use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;
/**
 *  Retorna messagem de alert somente html. 
 * 
 */
class AlertModalResponse extends Response
{
    /**
     *  Criar nova alertar
     *
     * @param string $title - Titulo do alerta
     * @param string $message -  Mensagem a ser visualizada
     * @param string $typeAlert - Tipo alerta danger/warning/success
     */
    public function __construct($title,$message,$typeAlert="danger")
    {   

        $alert = '<div class="alert alert-block alert-%s">
                    <h4 class="alert-heading">%s</h4>
                    <p>%s</p>
                    </div>';

        $handle = fopen("php://temp", "wb+");
        $body = new Stream($handle);
        $body->write(sprintf($alert,$typeAlert,$title,$message));
        $headers = new Headers;
        $headers->set("Content-type", "text/html");

        parent::__construct(200, $headers, $body);
    }
}