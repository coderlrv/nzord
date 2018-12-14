<?php
namespace NZord\Controller;

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
    public function can($permission,$action=null)
    {
        return $this->app->acl->can($permission,$action);
    }
    /**
     *  Checa se permissão nao seja igual a passada por paramentro
     *  igual a acl->can()
     *
     * @param string|array $permission - Alias da permissão ou array permissao , users | perfils | depto
     * @return boolean
     */
    public function canNot($permission)
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
    public function redirectToback()
    {
        $url = $this->app->request->getHeader('HTTP_REFERER');

        return $this->app->response->withRedirect($url ? $url[0] : '');
    }

    public function redirect($modulo, $controler = '', $action = '', $args = [], $queryParams = [], $absolute = true)
    {
        $url = $this->url->to(
            $modulo,
            $controler = '',
            $action = '',
            $args = [],
            $queryParams = [],
            $absolute = true
        );
        return $this->app->response->withRedirect($url);
    }

    /**
     * Retorna json
     *
     * @param string $message
     * @param integer $status
     * @param \Exception $ex
     * @return void
     */
    public function responseJson($message, $status = 200, $ex = null)
    {
        $msg = ['statusCode' => $status, 'message' => $message];
        if ($ex) {
            $this->logger($ex->getMessage(), null, 500);

            //Envia erro usuario somente quando debug tiver ativo.
            if ($this->app->get('settings')['debug']) {
                $msg['error'] = $ex->getMessage();
            }
        }

        return $this->app->response
            ->withHeader('Content-Type', 'application/json')
            ->withJson($msg, $status);
    }
    /**
     *  Retorna arquivo do arquivo passado.
     *
     * @param string $file - Arquivo a ser retornado.
     * @return mixed
     */
    public function responseFile($file,$forceDownload=false){
        $fh = fopen($file, 'rb');
        $stream = new \Slim\Http\Stream($fh); // create a stream instance for the response body

        $response =  $this->app->response
                        ->withHeader('Content-Type', 'application/octet-stream')
                        ->withHeader('Content-Type', 'application/download')
                        ->withHeader('Content-Description', 'File Transfer')
                        ->withHeader('Content-Transfer-Encoding', 'binary')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
                        ->withHeader('Expires', '0')
                        ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                        ->withHeader('Pragma', 'public')
                        ->withBody($stream); // all stream contents will be sent to the response

        if($forceDownload){
            $response = $response->withHeader('Content-Type', 'application/force-download');
        }
        
        return  $response;
    }
    /**
     * Grava SysLogger no banco de dados
     *
     * @param string $msg
     * @param string $tela
     * @param integer $statusCode
     * @return void 
     */
    public function logger($msg,$tela=null,$statusCode=200 ){
        $pathTemp = $this->app->settings['logger']['path_tmp'].'/logs';
        $data = date("Y-m-d");
        $hora = date("Y-m-d H:i:s");
        $ip = $this->app->request->getServerParam('REMOTE_ADDR');
        
        if( $tela != null ){
            $tela = 'origen: '.$tela.'; ';
        }else{
            $tela = (string) $this->app->request->getUri();
        }        
        if( $this->app->session->get('user') != null ){
            $user = $this->app->session->get('user');
            $sessao = $this->app->session->get('sessao');
            $userName = ' - '.$this->app->session->get('login');
        }        
        //Nome do arquivo:
        if($statusCode == 200){
            $arquivo = $pathTemp."/nz_$data.log";
        }else{
            $arquivo = $pathTemp."/nz_error_$data.log";
        }
        if( is_array($msg) ){
            $msg = json_encode($msg);
            $msg = str_replace('","','", "',$msg);
        }        
        //Texto a ser impresso no log:
        $texto = "Date: [$hora] | Ip: [$ip] | User: [$user $userName] | Source: [ $tela ] | Log: [ $msg ] \n";

        if( file_exists($pathTemp.'/') ){
            // Gravando log no arquivo
            $manipular = fopen($arquivo, "a+b");
            fwrite($manipular, $texto);
            fclose($manipular);
        }        
        // Gravar no Banco de Dados
        if($statusCode == 200){
            $save = new \Modulos\System\Models\Log();
            $save->data = $hora;
            $save->sessao = $sessao;
            $save->ip = $ip;
            $save->tela = $tela;
            $save->user = $user;
            $save->detalhe = $texto;
            $save->save(); 
        }
    }

    public function url(){
        return $this->app->url;
    }
}
