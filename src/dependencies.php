<?php

use Illuminate\Database\Capsule\Manager as DB;

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Cuiaba');

// DIC configuration
$container = $app->getContainer();

// Database - Instancia configuranção
$capsule = new DB;
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

$container['session'] = function ($container) {
    return new \NZord\Controller\Session(
        $container->get('settings')['session']['namespace']
    );
};

$container['config'] = function ($container) {
    $sys = $container->get('settings')['app'];

    $config   = \Modulos\System\Models\Config::first();
    $base_url = str_replace($container->get('request')->getUri()->getPath(), '', $container->get('request')->getUri());

    $gUrl = $container->get('request')->getServerParams();

    $diaSem    = array('Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sabado');
    $diaSemana = $diaSem[date('w')];
    $diaHoje   = date('d/m/Y');
    $horaAtual = date('H:i');

    $sys['diaSemana']  = $diaSemana;
    $sys['diaHoje']    = $diaHoje;
    $sys['horaAtual']  = $horaAtual;
    $sys['url']        = $base_url;
    $sys['title']      = $config->title;
    $sys['gTitle']     = $config->title;
    $sys['mTitle']     = $config->title_mini;
    $sys['theme']      = $config->theme;
    $sys['userAvatar'] = $base_url . '/images/avatar/user.png';

    $sys['config'] = $config;

    return $sys;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Register component on container
$container['view'] = function ($c) {
    $settings = $c->get('settings')['render'];

    // Configurations Twig ----------------------------------------------------------------------------------------
    $view = new \Slim\Views\Twig(__DIR__.'/templates', [
        'cache'            => $settings['cache_path'],
        'charset'          => 'utf-8',
        'auto_reload'      => true,
        'strict_variables' => false,
        'autoescape'       => true,
        'debug'            => true,
    ]); 

    //Adiciona caminho template base
    $view->getLoader()->addPath($settings['template_path']);

    // Extesions / Advance funcitions ------------------------------------------------------------------------------
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new \NZord\Helpers\TwigExtensions\TwigExtensionCustom($c->get('request')->getUri()));
    $view->addExtension(new \NZord\Helpers\TwigExtensions\TwigFiltersCustom());
    $view->addExtension(new \NZord\Helpers\TwigExtensions\NAclTwigExtension($c['acl']));
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new Knlv\Slim\Views\TwigMessages($c->get('flash')));
    $view->addExtension(new Awurth\SlimValidation\ValidatorExtension($c->get('validator')));

    // Filters -----------------------------------------------------------------------------------------------------
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
        $response = array();
        if ($stdClassObject) {
            foreach ($stdClassObject as $key => $value) {
                $response[] = array($key, $value);
            }
        }
        return $response;
    }));

    // Functions ---------------------------------------------------------------------------------------------------
    $view->getEnvironment()->addFunction('zdebug', new Twig_Function_Function('zdebug'));
    $view->getEnvironment()->addFunction('convertColGrid', new Twig_Function_Function('convertColGrid'));
    $view->getEnvironment()->addFunction('json_decode', new Twig_Function_Function('json_decode'));
    $view->getEnvironment()->getExtension('Twig_Extension_Core')->setNumberFormat(2, ',', '.');

    // Variables global --------------------------------------------------------------------------------------------
    $view->getEnvironment()->addGlobal('session', $_SESSION);
    $view->getEnvironment()->addGlobal('currentUrl', $c["request"]->getUri()->getPath());
    $view->getEnvironment()->addGlobal('path', $c->get('request')->getUri());
    $view->getEnvironment()->addGlobal('sys', $c['config']);

    $view->getEnvironment()->addGlobal('flash', $c['flash']);

    return $view;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c->view->render($response, '404.html.twig');
    };
};

$container['errorHandler'] = function ($container) {
    $settings = $container->get('settings');

    return function ($request, $response, $exception) use ($container) {
        if ($exception instanceof Base\Exceptions\ModalErrorException) {
            $handler = new \NZord\Handlers\ModalExceptionHandler();

            return $handler->__invoke($request, $response, $exception);
        } else {
            $handler = new Slim\Handlers\Error(true);
            return $handler->__invoke($request, $response, $exception);
        }
    };
};

$container['logger'] = function (\Slim\Container $container) {
    $settings = $container->get('settings')['logger'];

    $logger = new Monolog\Logger($settings['name']);

    $handler = new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG);

    $handler->setFormatter(new Monolog\Formatter\LineFormatter(
        "[%datetime%] %level_name% > %message% - %context% - %extra%\n"
    ));

    $logger->pushHandler($handler);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushProcessor(new Monolog\Processor\WebProcessor);

    return $logger;
};

// Registra controler de acesos modulos.
$container['acl'] = function (\Slim\Container $c) {
    return new \NZord\Helpers\NAcl($c, $c['session']);
};

//Validação campos slim
$container['validator'] = function () {
    $defaultMessages = [
        'length'   => 'O campo <b>{{name}}</b> deve ter um tamanho entre {{minValue}} e {{maxValue}} caracteres.',
        'notBlank' => 'O campo <b>{{name}}</b> é obrigatório.',
        'intVal'   => 'O campo <b>{{name}}</b> deve ser um número inteiro.',
        'email'    => 'O campo <b>{{name}}</b> deve ser um email válido.',
        'ip'       => 'O campo <b>{{name}}</b> deve ser um endereço de IP válido.',
        'in'       => 'O campo <b>{{name}}</b> selecionado é inválido,Opções: {{haystack}}.',
        'date'     => 'O campo <b>{{name}}</b> não é uma data válida.',
        'numeric'  => 'O campo <b>{{name}}</b> deve ser um número.',
        'cpf'      => 'O campo <b>{{name}}</b> deve ser um número de CPF válido.',
    ];

    return new \NZord\Helpers\NValidator(true, $defaultMessages);
};

//Report
$container['report'] = function (\Slim\Container $c) {
    return new \NZord\Helpers\NReportTwig($c);
};

$container['url'] = function(\Slim\Container $c){
    return new \NZord\Helpers\NUrlRoute($c['request']);
};
