<?php
namespace NZord\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class Modal extends Controller
{
    //--------------------------------------------------------------------------------
    public function modal(Request $request, Response $response)
    {
        //Lista paramentro do link
        $par = explode('/', $request->getQueryParams()['p']);

        $modulo = $par[1];
        $pagina = dashesToCamelCase(ucfirst($par[2]));
        $action = dashesToCamelCase(getParam($par[3]), false);
        //
        $args = array('modulo' => ucfirst($modulo), 'pagina' => $pagina, 'act' => $action, 'id' => @$par[4], 'args' => @array_slice($par, 4));

        $class = '\Modulos\\' . ucfirst($modulo) . '\\' . $pagina . 'Controller';

        if (!class_exists($class)) {
            return $this->redirect404Modal();
        }

        $ctrl = new $class($this->app);

        $pathTemplates = getPathClass($class) . '/../templates';
        $this->app->view->getLoader()->addPath($pathTemplates, 'twigModal');

        //Checa permissão de acesso.
        if (!$this->app->acl->checkPermissionAction($modulo,$pagina,$action)) {
            return $this->app->view->render($response, '403_modal.html.twig');
        }
        //Checa se existe metodo no controller.
        if (!method_exists($ctrl, $action)) {
            $response = $response->withStatus(404);
            return $this->app->view->render($response, '404.html.twig', ['modal' => true]);
        }

        //Executa acao modal.
        $dados = $ctrl->$action($request, $response, ['p' => $request->getQueryParams()['p'], 'id' => @$par[4], 'args' => @array_slice($par, 4)]);

        //Caso retornar um Respose retorna ele mesmo.
        if ($dados instanceof Response) {
            return $dados;
        }

        if ($dados) {
            $args['dados'] = $dados;
        }
        //Gera url do modal.
        $args['currentUrl'] = (string) $request->getUri();

        //Verifica arquivos modal.
        if ($action && file_exists($pathTemplates . '/mdl' . ucfirst($action) . '.twig')) {
            //Verifica se existe arquivo com nome a action.
            return $this->app->view->render(
                $response, 
                '@twigModal/mdl' . ucfirst($action) . '.twig', 
                $args
            );
        } elseif ($action && file_exists($pathTemplates . '/' . $pagina . '/mdl' . ucfirst($action) . '.twig')) {
            //Verifica se existe pasta com nome controller
            return $this->app->view->render(
                $response, 
                '@twigModal/' . $pagina . '/mdl' . ucfirst($action) . '.twig', 
                $args
            );
        } elseif (file_exists(sprintf('%s/%s/mdl%s.twig', $pathTemplates, $pagina, $pagina))) {
            return $this->app->view->render(
                $response,
                '@twigModal/' . $pagina . '/mdl' . $pagina . '.twig',
                $args
            );
        } elseif (file_exists($pathTemplates . '/mdl' . $pagina . '.twig')) {
            return $this->app->view->render(
                $response, 
                '@twigModal/mdl' . $pagina . '.twig', 
                $args
            );
        }
    }
    //--------------------------------------------------------------------------------
    public function save($request, $response, $args)
    {
        $data   = $request->getParsedBody();
        $modulo = $data['tmodulo'];
        $model  = $data['tcontrol'];
        $class  = 'Modulos\\' . $modulo . '\Models\\' . $model;

        if (isset($data['id'])) {
            $insert = $class::find($data['id']);
            if (!$insert) {
                echo 'Usuario não encontrato!';
                exit();
            }
        } else {
            $insert = new $class();
        }

        unset($data['tmodulo']);
        unset($data['tcontrol']);

        $keys = array_keys($data);
        foreach ($keys as $k => $v) {
            $method = $v;
            $campo  = "\$data['$v']";
            eval("\$insert->" . $method . ' = ' . $campo . ";");
        }
        try {
            $insert->save();

            return $this->responseJson('Sucesso');
        } catch (\Exception $ex) {
            return $this->responseJson('Erro ao Salvar', 500, $ex);
        }
    }
    //--------------------------------------------------------------------------------
}
