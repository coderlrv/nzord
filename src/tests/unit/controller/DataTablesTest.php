<?php
namespace NZord\Tests\Unit\Helpers;

use Illuminate\Database\Capsule\Manager as DB;
use Mockery as m;
use \NZord\Controller\DataTables;
use NZord\Helpers\NQuery;

class DataTablesTest extends \PHPUnit\Framework\TestCase
{
    public function getMockRequest(){
        
        return [
            "draw" => 1,
            "columns" => [
                [
                    "data" => "id",
                    "name" => "id",
                    "searchable" => "true",
                    "orderable"  => "true",
                    "search" => [
                        "value" => "",
                        "regex" => "false"
                    ]
                ],
                [
                    "data" => "session_id",
                    "name" => "session_id",
                    "searchable" => "true",
                    "orderable"  => "true",
                    "search" => [
                        "value" => "",
                        "regex" => "false"
                    ]
                ]

            ],
            "order"=>[
                [
                    "column" =>"0",
                    "dir"    => "desc"
                ]
            ],
            "start" => "0",
            "length" => "10",
            "search"=> [
                "value"=>"",
                "regex"=>"false"
            ]
        ];
    }
    //--------------------------------------------------------------------------------
    // Retorno filtro sem pesquisa.
    public function testNoSearchFilter(){
        $mockRequest = $this->getMockRequest();

        $filterResult = DataTables::filter($mockRequest,'mysql');

        $this->assertEquals("",$filterResult->toSql());
    }
    //--------------------------------------------------------------------------------
    // Pesquisa em todas colunas no banco mysql e outros
    public function testFilterMysql(){
        $mockRequest = $this->getMockRequest();
        $mockRequest['search']['value'] = 'teste';

        $filterResult = DataTables::filter($mockRequest,'mysql');

        $this->assertEquals(" where `ID` like CONCAT('%','teste','%') OR `SESSION_ID` like CONCAT('%','teste','%')",$filterResult->toSql());
    }

    //--------------------------------------------------------------------------------
    // Pesquisa em todas colunas no banco postgres
    public function testFilterPgsql(){
        $mockRequest = $this->getMockRequest();
        $mockRequest['search']['value'] = 'teste';

        $filterResult = DataTables::filter($mockRequest,'pgsql');

        $this->assertEquals(" where x::text like E'%teste%'",$filterResult->toSql());
    }

    //--------------------------------------------------------------------------------
    // Verifica ordenação 
    public function testOrder(){
        $mockRequest = $this->getMockRequest();
        $mockRequest['search']['value'] = 'teste';

        $order = DataTables::order($mockRequest);

        $orderResult = [[
            'column' => 1,
            'order'  => 'DESC'
        ]];
        
        $this->assertEquals( $orderResult,$order);
    }

    //--------------------------------------------------------------------------------
    // Sem busca
    public function testGetSqlNoSearch(){
        $mockRequest = $this->getMockRequest();

        $sql = DataTables::getSql($mockRequest,'select * from reg_sessao');

        $this->assertEquals($sql->toSql(),'select x.* from ( select * from reg_sessao ) as x  order by 1 DESC limit 10 offset 0');
    }

    //--------------------------------------------------------------------------------
    // BUSCA  Mysql
    public function testGetSqlSearchMysql(){
        $mockRequest = $this->getMockRequest();

        //Campo pesquisa
        $mockRequest['search']['value'] = '35';

        $sql = DataTables::getSql($mockRequest,'select * from reg_sessao');

        $this->assertEquals($sql->toSql(),"select x.* from ( select * from reg_sessao ) as x where `ID` like CONCAT('%','35','%') OR `SESSION_ID` like CONCAT('%','35','%') order by 1 DESC limit 10 offset 0");
    }

    //--------------------------------------------------------------------------------
    // BUSCA Postgres
    public function testGetSqlSearchPgsql(){
        $mockRequest = $this->getMockRequest();

        //Campo pesquisa
        $mockRequest['search']['value'] = '35';

        $sql = DataTables::getSql($mockRequest,'select * from reg_sessao','pgsql');

        $this->assertEquals($sql->toSql(),"select x.* from ( select * from reg_sessao ) as x where x::text like E'%35%' order by 1 DESC limit 10 offset 0");
    }

    
    //--------------------------------------------------------------------------------
    // BUSCA Postgres
    public function testSimple(){
        $mockRequest = $this->getMockRequest();
        $result =  mockDatatable::simple($mockRequest,'select * from reg_sessao');
       
        $this->assertEquals($result, [
            'draw'         =>1,
            'recordsTotal'    => 10,
            'recordsFiltered' => 10,
            'columns' =>[
                'ID',
                'SESSION_ID'
            ],
            'data'=>[
                [
                    'ID'         => 1,
                    'SESSION_ID' => 'vqoqabsurnlougciu323576872'
                ],
                [
                    'ID'         => 2,
                    'SESSION_ID' => 'cui5gekta04f6bp7bhsp5vhg16'
                ]
            ]
        ]);
    }
}

//Mock 
class mockDatatable extends DataTables{
    public static function getDriver()
    {
        return 'mysql';
    }
    //
    public static function getSql( $request, $sql ){

        //Campo pesquisa
        $externalMock = m::mock('\NZord\Helpers\NQuery');
        $externalMock->shouldReceive('toArray')
            ->once()
            ->andReturn([
                ['ID'=>1,'SESSION_ID'=> 'vqoqabsurnlougciu323576872'],
                ['ID'=>2,'SESSION_ID'=> 'cui5gekta04f6bp7bhsp5vhg16'],
            ]);

        return $externalMock;
    }
    public static function getTotRegister($sql)
    {
        return 10;
    }
}
