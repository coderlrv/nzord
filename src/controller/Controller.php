<?php
namespace NZord\Controller;

use NZord\Responses\ModalResponse;

/**
 *  Controller base nzord.
 */
class Controller
{
    protected $app;

    /**
     * Instancia da Sessão
     *
     * @var \Base\Controller\Session
     */
    protected $session;

    /**
     * Instancia do router
     *
     * @var \Slim\Router
     */
    protected $router;
    /**
     * Instancia da View
     *
     * @var  \Slim\Views\Twig
     */
    protected $view;

    /**
     * Gerador de url
     *
     * @var Base\Helpers\NUrlRoute
     */
    protected $url;

    /**
     *  Construtor base
     *
     * @param \Slim\Container $app
     */
    public function __construct($app)
    {
        $this->app     = $app;
        $this->session = $this->app->session;
        $this->router  = $this->app->router;
        $this->view    = $this->app->view;
        $this->url     = $this->app->url;
    }
    /**
     * Checa se usuário esteja nas permissão passada por paramentro.
     *
     * @param string|array $permission - Alias da permissão ou array permissao , users | perfils | depto
     * @return boolean
     */
    public function authorize($permission)
    {
        return $this->app->acl->can($permission);
    }
    /**
     *  Checa se permissão nao seja igual a passada por paramentro
     *  igual a acl->can()
     *
     * @param string|array $permission - Alias da permissão ou array permissao , users | perfils | depto
     * @return boolean
     */
    public function authorizeNot($permission)
    {
        return $this->app->acl->canNot($permission);
    }

    /**
     * Redireciona para pagina erro 404 - Página nao encontrada
     *
     * @return mixed
     */
    public function redirect404()
    {
        return $this->app->view->render($this->app->response, '404.html.twig');
    }
    /**
     * Redireciona para pagina erro 404 -Modal
     *
     * @return mixed
     */
    public function redirect404Modal()
    {
        return $this->app->view->render($this->app->response, '404_modal.html.twig');
    }

    /**
     * Redireciona para pagina erro 403 - Usuário não possui permissão
     *
     * @return mixed
     */
    public function redirect403($json = false, $modal = false)
    {
        if ($json) {
            return $this->responseJson('Usuário não possui permissão!', 403);
        } else {
            if ($modal) {
                return $this->app->view->render($this->app->response, '403_modal.html.twig');
            } else {
                return $this->app->view->render($this->app->response, '403.html.twig');
            }
        }
    }
    /**
     * Redireciona para pagina que chamou a ação.
     *
     * @return mixed
     */
    public function redirectToback(){
        $url = $this->app->request->getHeader('HTTP_REFERER');
        return $this->app->response->withRedirect($url?$url[0]:'');
    }


    /**
     * Retorna json 
     *
     * @param string $message
     * @param integer $status
     * @param \Exception $ex
     * @return void
     */
    public function responseJson($message, $status = 200,$ex = null){
        $msg = ['statusCode'=> $status,'message' => $message ];
        if($ex){
            Logger($ex->getMessage(),null,500);

            //Envia erro usuario somente quando debug tiver ativo.
            if($this->app->get('settings')['debug']){
                $msg['error'] = $ex->getMessage();
            }
        }

        return $this->app->response
                ->withHeader('Content-Type', 'application/json')
                ->withJson($msg, $status);
    }
}
