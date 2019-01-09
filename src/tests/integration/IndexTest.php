<?php

/* 
class IndexTest extends LocalWebTestCase
{
    public function testIndexVersion()
    {
        $this->client->get('/');
        
        //Usuario sem acesso é redirecionado para login
        $this->assertEquals(302, $this->client->response->getStatusCode());
        $this->assertContains('/login', $this->client->response->getHeader('location'));
    }
}

*/