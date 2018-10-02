<?php
namespace NZord\Controller;

use Modulos\System\Models\Institucional;
use Modulos\System\Models\Setor;
use Modulos\System\Models\Usuario;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 *  Controller por modulos.
 */
class AppController extends Controller
{
    /**
     *  Recebe todas chamada da rota /app  se argumento id for do tipo inteiro
     *  [/{modulo}[/{pagina}[/{act}[/{id:[0-9]+}]]]]
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     */
    public function actionsWithId(Request $request, Response $response, $args)
    {
        $modulo = @$args['modulo'];
        $pagina = (@$args['pagina']) ? dashesToCamelCase($args['pagina']) : 'index';
        $act    = (@$args['act']) ? dashesToCamelCase($args['act'], false) : 'index';
        $params = (@$args['params']) ? explode('/', $args['params']) : [];

        if ($modulo) {
            $class = 'Modulos\\' . ucfirst($modulo) . '\\' . ucfirst($pagina) . 'Controller';

            //Nao existe a função
            if (!class_exists($class)) {
                $response = $response->withStatus(404);
                return $this->view->render($response, '404.html.twig');
            }
            $ctrl = new $class($this->app);

            // Defini template a ser utilizado.
            $this->view->getEnvironment()
                ->addGlobal('url', array(
                    'modulo'    => $modulo,
                    'urlPagina' => $pagina . '.twig',
                    'pagina'    => ucfirst($pagina),
                    'act'       => $act
                ));

            //Seta caminha template do modulos.
            $pathTemplates = getPathClass($class).'/../templates';
            $this->view->getLoader()->addPath($pathTemplates, 'twMod');

            //Checa permissao ACL definido no controller.
            if (!$this->app->acl->checkPermissionAction($modulo,$pagina,$act)) {
                
                //Caso for ajax retorna json.
                if ($request->isXhr()) { 
                    $response = $response->withStatus(403);
                    return $response
                        ->withJson(['statusCode' => 403, 'message' => 'Você não tem permissão de acesso!'], 403);
                } else {
                    return $response->withRedirect($this->router->pathFor('403'));
                }
            }

            if (!method_exists($ctrl, $act)) {
                $response = $response->withStatus(404);
                return $this->view->render($response, '404.html.twig');
            }
            return $ctrl->$act($request, $response, $args, new \NZord\Helpers\NParams($params));
        }

        //Envia para base.
        $setor = Setor::where('id', '=', $this->session->get('userSetor'))
            ->where('detalhe', '<>', '""')
            ->first();

        $inst      = Institucional::get();
        $setorDesc = Setor::selectOrgaoSetor(null, 's.id = ' . $this->session->get('userSetor'));
        $pessoas   = Usuario::selectUserForSetor($this->session->get('userSetor'));

        return $this->view->render($response, 'base.html.twig', array(
            'setor'     => $setor,
            'inst'      => $inst,
            'setorDesc' => $setorDesc[0],
            'pessoas'   => $pessoas,
        ));
    }
    /**
     *  Recebe todas chamada da rota /app
     *  /{modulo}/{pagina}/{act}/[{params:.*}]
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     */
    public function actions(Request $request, Response $response, $args)
    {
        $modulo = $args['modulo'];
        $pagina = (@$args['pagina']) ? dashesToCamelCase($args['pagina']) : 'index';
        $act    = (@$args['act']) ? $args['act'] : 'index';
        $params = (@$args['params']) ? explode('/', $args['params']) : [];

        if ($modulo) {

            //Define modulo a ser chamado.
            $class = 'Modulos\\' . ucfirst($modulo) . '\\' . ucfirst($pagina) . 'Controller';

            //Nao existe a função
            if (!class_exists($class)) {
                $response = $response->withStatus(404);
                return $this->view->render($response, '404.html.twig');
            }

            $ctrl = new $class($this->app);

            //Configura template twig
            $this->view->getEnvironment()
                ->addGlobal('url', array(
                    'modulo'    => $modulo,
                    'urlPagina' => $pagina . '.twig',
                    'pagina'    => ucfirst($pagina),
                    'act'       => $act,
                    'pagdate'   => @$dataArq,
                ));
            
            //Seta caminha template do modulos.
            $pathTemplates = getPathClass($class).'/../templates';
            $this->view->getLoader()->addPath($pathTemplates, 'twMod');

            //Checa permissao ACL definido no controller.
            if (!$this->app->acl->checkPermissionAction($modulo,$pagina,$act)) {
                if ($request->isXhr()) { //Caso for ajax retorna json.

                    $response = $response->withStatus(403);
                    return $response
                        ->withJson(['statusCode' => 403, 'message' => 'Você não tem permissão de acesso!'], 403);
                } else {
                    return $response->withRedirect($this->router->pathFor('403'));
                }
            }

            if (!method_exists($ctrl, $act)) {
                $response = $response->withStatus(404);
                return $this->view->render($response, '404.html.twig');
            }
            return $ctrl->$act($request, $response, $args, new \NZord\Helpers\NParams($params));
        }

        //Caso nao encontre rota , renderiza a pagina base.
        $setor = Setor::where('id', '=', $this->session->get('userSetor'))
            ->where('detalhe', '<>', '""')
            ->first();

        $inst      = Institucional::get();
        $setorDesc = Setor::selectOrgaoSetor(null, 's.id = ' . $this->session->get('userSetor'));
        $pessoas   = Usuario::selectUserForSetor($this->session->get('userSetor'));

        return $this->view->render($response, 'base.html.twig', array(
            'setor'     => $setor,
            'inst'      => $inst,
            'setorDesc' => $setorDesc[0],
            'pessoas'   => $pessoas,
        ));
    }
}
