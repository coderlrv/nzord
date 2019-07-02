<?php
/**
 * Rota principal
 */
$app->get('/', 'Base\Controller\BaseController:index')->setName('index');
/**
 * Rota salvar dados modal
 */
$app->post('/modal/save', 'NZord\Controller\Modal:save')->setName('msave');
/**
 * Rota abrir modal
 */
$app->get('/modal', 'NZord\Controller\Modal:modal')->setName('modal');
$app->get('/funcao', 'NZord\Controller\Funcao:index')->setName('funcao');
/**
 *  Rotas de controller de acesso
 */
$app->post('/login', 'NZord\Controller\AuthController:postLogin')->setName('postLogin'); 
$app->get('/login', 'NZord\Controller\AuthController:login')->setName('login');
$app->get('/logout', 'NZord\Controller\AuthController:logout')->setName('logout');

/**
 *  Redirecionamentos
 */
$app->get('/403', function ($request, $response) {
    return $this->view->render($response, '403.html.twig');
})->setName('403');

/**
 *  Rota para acessar paginas dos modulos disponivel
 */
$app->group('/app', function (){
    /**
     * Rotas com id.
     */
    $this->any('[/{modulo}[/{pagina}[/{act}[/{id:[0-9]+}]]]]', 'NZord\Controller\AppController:actionsWithId');

    /**
     * Rotas com paramentros.
     */
    $this->any('/{modulo}/{pagina}/{act}/[{params:.*}]', 'NZord\Controller\AppController:actions');
});
