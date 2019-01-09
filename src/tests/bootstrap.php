<?php
// Settings to make all errors more obvious during testing
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('UTC');

use There4\Slim\Test\WebTestCase;
use There4\Slim\Test\WebTestClient;
use \Slim\Http\Environment;
use \Slim\Http\Uri;
use \Slim\Http\Headers;
use \Slim\Http\RequestBody;
use \Slim\Http\Request;

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

require_once PROJECT_ROOT . '/../vendor/autoload.php';

//Inicializa copia da instancia do slim.
class LocalWebTestCase extends WebTestCase {
    /**
     * Container slim
     *
     * @var Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Sessao do slim
     *
     * @var [type]
     */
    protected $session;

    protected $cookies = [];

    public function getSlimInstance() {
        $setting = require 'settings.php';
        $app = new \NZord\App($setting);
        $app->run();

        // Include our core application file
      
        $this->container = $app->getContainer();
        $this->session   = $this->container->session;
        return $app;
    }

    /**
     * Simula sessao no slim
     * Valores padrão
     *  $defaultUser = [
     *       'user'             =>'1',
     *       'login'            =>'test.test',
     *       'userNome'         => 'Test',
     *       'userMatricula'    => '1',
     *       'userDpto'         => '1',
     *       'userSetor'        => '1',
     *       'userPerfil'       => '1',
     *       'sessiontime'      => time(),
     *       'loggedIn'         => date('Y-m-d H:i:s'),
     *       'isLoggedIn'       => true,
     *       'remoteIp'         => '127.0.0.1',
     *       'sessao'           => '1'
     *   ];
     * @param array $data
     * @return void
     */
    public function mockSession($data = []){

        $defaultUser = [
            'user'             =>'1',
            'login'            =>'test.test',
            'userNome'         => 'Test',
            'userMatricula'    => '1',
            'userDpto'         => '1',
            'userSetor'        => '1',
            'userPerfil'       => '1',
            'sessiontime'      => time(),
            'loggedIn'         => date('Y-m-d H:i:s'),
            'isLoggedIn'       => true,
            'remoteIp'         => '127.0.0.1',
            'sessao'           => '1'
        ];
    
        $dataSession = array_merge($defaultUser,$data);
        
        //Seta info na sessao
        foreach ($dataSession as $key => $value) {
            $this->session->set($key ,$value);
        }
    }

    public function mockRequest($method,$path,$data = array(), $optionalHeaders = array()){
        //Make method uppercase
        $method = strtoupper($method);
        $options = array(
            'REQUEST_METHOD' => $method,
            'REQUEST_URI'    => $path
        );

        if ($method === 'GET') {
            $options['QUERY_STRING'] = http_build_query($data);
        } else {
            $params  = json_encode($data);
        }

        // Prepare a mock environment
        $env = Environment::mock(array_merge($options, $optionalHeaders));
        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $cookies = $this->cookies;
        $serverParams = $env->all();
        $body = new RequestBody();

        // Attach JSON request
        if (isset($params)) {
            $headers->set('Content-Type', 'application/json;charset=utf8');
            $body->write($params);
        }

        return new Request($method, $uri, $headers, $cookies, $serverParams, $body);
    }
};