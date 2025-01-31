<?php
/**
 *
 *  Controle de acesso ao menus.
 *
 */
$app->add(function ($request,$response,$next) {
    $uri  = $request->getUri();
    $path = $uri->getPath();
    $sessao   = $this->session->get('sessao');
    $sesAtivo = null;
    if ($sessao) {
        $sesAtivo = \Modulos\System\Models\Sessao::getSessaoAtiva($sessao);
    }
    
    if (!$this->session->has('isLoggedIn') or !$sesAtivo) {

        if ($path != '/login' && $path != 'login' ) {
            $this->session->clear();
            return $response->withRedirect($this->router->pathFor('login'));
        }
      
        return $next($request->withUri($uri), $response);
    }
    
    /**
     * Carrega ao Menu de acordo com as permissões na session
     *
     * @var \Modulos\System\SysMenuController $menu
     */
    $menu    = new \Modulos\System\SysMenuController($this);
    $menuArr = $menu->getMenuArr($request, $response);
    $menu    = $menu->getMenuAccess($request, $response);
/**
 *
 * Sessão
 *
 */
//$app->add(new \NZord\Middlewares\Session($container->get('settings')['session']));

    $this->session->set('menu', $menu);
    $this->session->set('menuArr', $menuArr);

    return $next($request, $response);
});

/**
 *
 * Logger
 *
 */
$app->add(new \NZord\Middlewares\Logger(
    $container->get('logger'),
    $container
));
/**
 *
 * Cors
 *
 */
$app->add(new \NZord\Middlewares\Cors());


/**
 *
 * Seta Url na request.
 *
 */
$app->add(function ($request, $response, $next) {
    $gUrl = $request->getServerParams();

    $request = $request->withAttribute('session', $this->session);
    $uri     = $request->getUri();

    $scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])     
                    ? $_SERVER['HTTP_X_FORWARDED_PROTO'] 
                    : $uri->getScheme();

    $request = $request->withAttribute('url', $scheme. '://'  . $gUrl['SERVER_NAME'] . $gUrl['REQUEST_URI']);
    
    return $next($request, $response);  
});

/**
 *
 * Sessão
 *
 */

 $app->add(new \NZord\Middlewares\Session($container->get('settings')['session']));
