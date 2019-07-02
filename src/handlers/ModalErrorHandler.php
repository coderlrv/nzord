<?php
namespace NZord\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModalExceptionHandler
{
    /**
     * @var bool
     */
    protected $displayErrorDetails;

    function __construct($displayErrorDetails = false)
    {
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke(Request $request, Response $response, $exception)
    {   
        $codeError = 500;
       
        $errors['message'] = $this->displayErrorDetails ? $exception->getMessage() : 'A website error has occurred. Sorry for the temporary inconvenience.';
        $errors['codeStatus'] = $codeError;
        
        if($exception->data && $this->displayErrorDetails){
            $errors['data'] = json_encode($exception->data);
        }

        return $response
            ->withStatus($codeError)
            ->withJson($errors);
    }
}