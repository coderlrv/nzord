<?php
namespace NZord\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Router;

class Auth{
    
    protected $router;
    protected $session;
    
    public function __construct($session,Router $router){
        $this->session = $session;
        $this->router  = $router;
    }
    
    public function __invoke(Request $request, Response $response, $next){
        $loggedIn = $this->session->get('isLoggedIn');
        if ( $loggedIn != true ) {
            
            return $response->withRedirect($this->router->pathFor('login'));
        }
        
        $response = $next($request, $response);
        
        return $response;
    }
}