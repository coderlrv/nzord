<?php

use Base\Helpers\NUrlRoute;

class NUrlRouteTest extends LocalWebTestCase
{
    public function testGeraLinkBaseTest()
    {
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/',$url->currentUrl());
    }

    public function testUrlAtual()
    {
        $request = $this->mockRequest('GET','/app/tributacao');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao', $url->currentUrl());
    }

    public function testUrlAtualPath()
    {
        $request = $this->mockRequest('GET','/app/tributacao?parametro=1&parametro=2');
        $url  = new NUrlRoute($request);

        $this->assertEquals('http://localhost/app/tributacao', $url->currentPath());
    }

    public function testGerarLinkModulos(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao', $url->to('tributacao'));
    }

    public function testGerarLinkCtrl(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao/autorizacao-escri', $url->to('tributacao','AutorizacaoEscri'));
    }

    public function testGerarLinkActions(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao/autorizacao-escri/listagem', $url->to('tributacao','AutorizacaoEscri','Listagem'));
    }

    public function testGerarLinkArgs(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao/autorizacao-escri/listagem/33/teste', $url->to('tributacao','AutorizacaoEscri','Listagem',['33','teste']));
    }

    public function testGerarLinkQueryParams(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/app/tributacao/autorizacao-escri/listagem?teste=232', $url->to('tributacao','AutorizacaoEscri','Listagem',null,['teste'=>232]));
    }

    public function testGerarSomentePathLinkQueryParams(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('/app/tributacao/autorizacao-escri/listagem?teste=232', $url->to('tributacao','AutorizacaoEscri','Listagem',null,['teste'=>232],false));
    }

    public function testGerarLinkModalActions(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertEquals('http://localhost/modal?p=app/tributacao/AutorizacaoEscri/Edit&teste=232', $url->toModal('tributacao','AutorizacaoEscri','Edit',null,['teste'=>232]));
    }
    
    public function testChecaSeUrlValida(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertNotFalse($url->isValidUrl('http://localhost/modal?p=app/tributacao/AutorizacaoEscri/Edit&teste=232'));
    }

    public function testChecaSeUrlInvalida(){
        $request = $this->mockRequest('GET','/');
        $url  = new NUrlRoute($request);
        $this->assertNotTrue($url->isValidUrl('localhost\modal?p=app/tributacao/AutorizacaoEscri/Edit&teste=232'));
    }
}