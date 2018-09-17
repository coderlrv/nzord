<?php

class ModalTest extends LocalWebTestCase
{   

    function __construct(){
        // Mock 
        $this->ctrl = $this->getMockBuilder(\Modulos\System\BasicaController::class)
                    ->setMethods(['form'])
                    ->disableOriginalConstructor()
                    ->getMock();

        // // Mock
        // $this->view = $this->getMockBuilder(\Twig::class)
        //             ->setMethods(['render'])
        //             ->disableOriginalConstructor()
        //             ->getMock();
    }

    public function testCheckModalPermissaoTest()
    {
        $this->ctrl->expects($this->any())
                    ->method('form')
                    ->willReturn([
                        'act' => 'form',
                        'basica' => [],
                        'status' => [],
                        'tabela' => ''
                    ]);
    
        // Instancia controller
        $action = new \Base\Controller\Modal($this->container);

        $environment = \Slim\Http\Environment::mock([
            'SERVER_PORT'    => '80',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/modal',
            'QUERY_STRING'   => 'p=app/system/basica/form']
        );

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        // run the controller action and test it
        $response = $action->modal($request, $response, []);

        $this->assertEquals(403,$response->getStatusCode());
    }

    public function testCheckModalRenderTest(){

        // Mock sessao
        $this->mockSession();
        $this->ctrl->expects($this->any())
                    ->method('form')
                    ->willReturn([
                        'act'    => 'form',
                        'basica' => ''
                    ]);
        // $this->view->expects($this->any())
        //             ->method('render')
        //             ->with(null, 'test.twig', ['authors' => 'teste'])
        //             ->willReturn(null);
   
        // Instancia controller
        $action = new \Base\Controller\Modal($this->container);

        $environment = \Slim\Http\Environment::mock([
            'SERVER_PORT'    => '80',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/modal',
            'QUERY_STRING'   => 'p=app/system/basica/form']
        );

        $request  = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        // run the controller action and test it
        $response = $action->modal($request, $response, []);
    
        $this->assertEquals(403,$response->getStatusCode());
    }
}