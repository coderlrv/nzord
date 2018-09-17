<?php
use NZord\Helpers\NQuery;

class NQueryTest extends LocalWebTestCase
{
    public function testGeraLinkBaseTest()
    {
        $query = new NQuery('select coluna, coluna2,coluna3 from tab_usuario');


        $this->assertEquals('http://localhost/',$url->currentUrl());
    }
}