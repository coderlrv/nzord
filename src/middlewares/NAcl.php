<?php
namespace NZord\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Router;

class NAcl{
    
    protected $router;
    protected $acl;
    
    public function __construct($router,$acl){
        $this->router = $router;
        $this->acl = $acl;
    }
    
    public function __invoke(Request $request, Response $response, $next){
        $route = $request->getAttribute('route');
        $act = $route->getArgument('act') ? $route->getArgument('act') : 'index';
      
        $controller = $request->getAttribute('route')->getCallable();

        if(!$this->acl->checkPermissionAction($act)){
            if($request->isXhr()){
                return $response
                    ->withJson(['statusCode'=>403,'message'=>'Você não tem permissão de acesso!'], 403);
            }else{
                return $response->withRedirect($this->router->pathFor('403'));
            }
        }
        $response = $next($request, $response);
        return $response;
    }
}