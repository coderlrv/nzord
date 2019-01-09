<?php

use NZord\Helpers\NQuery;
use Illuminate\Database\Capsule\Manager as DB;
use Mockery as m;

class NQueryTest extends \NZord\Tests\Unit\TestCaseUnit
{
    public function testSetSql()
    {
        $query = new NQuery();
        $query->setQuery('select coluna from tab_usuario b');

        $this->assertEquals('select coluna from tab_usuario b',$query->toSql());
    }
    public function testToWhere()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);

        $this->assertEquals('b.coluna = 1',$query->toWhere());
    }
    public function testWhere()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1',$query->toSql());
    }

    public function testWhereAnd()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->where('b.coluna',2);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna = 2',$query->toSql());
    }
    
    public function testWhereOr()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereOr('b.coluna',2);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 OR b.coluna = 2',$query->toSql());
    }

    public function testInString()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereIn('b.coluna','1,2,3');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna In (1,2,3)',$query->toSql());
    }
    public function testInArrayValueInt()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereIn('b.coluna',[1,2,3]);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna In (1,2,3)',$query->toSql());
    }

    public function testInArrayValueString()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereIn('b.coluna',['test']);

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 1 AND b.coluna In ('test')",$query->toSql());
    }
    public function testLimit()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->limit(1);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1   limit 1',$query->toSql());
    }
    public function testOffSet()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->offSet(10);

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1    offset 10',$query->toSql());
    }
    public function testOrderBy()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->orderBy('b.coluna');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1   order by b.coluna ASC',$query->toSql());
    }
    public function testOrderByDesc()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->orderBy('b.coluna','DESC');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1   order by b.coluna DESC',$query->toSql());
    }
    public function testNotNull()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereNotNull('b.coluna');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna is not null',$query->toSql());
    }
    public function testWhereNull()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereNull('b.coluna');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna is null',$query->toSql());
    }

    public function testWhereRaw()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereRaw('b.coluna = 2 or b.coluna = 3');
        

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 AND b.coluna = 2 or b.coluna = 3',$query->toSql());
    }

    public function testWhereBetween()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereBetween('b.coluna','2019-02-21','2019-02-30');
        

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 1 AND b.coluna  between '2019-02-21' and '2019-02-30'",$query->toSql());
    }
    public function testWhereBetweenOr()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->whereBetween('b.coluna','2019-02-21','2019-02-30',"OR");
        

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 1 OR b.coluna  between '2019-02-21' and '2019-02-30'",$query->toSql());
    }
    public function testGroupBy()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',1);
        $query->groupBy('b.coluna');

        $this->assertEquals('select coluna from tab_usuario b where b.coluna = 1 group by b.coluna',$query->toSql());
    }
    public function testWhereFunction()
    {
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where(function($q){
            $q->where('b.coluna',1)->where('b.coluna','test');
        });
        
        $this->assertEquals("select coluna from tab_usuario b where (b.coluna = 1 AND b.coluna = 'test')",$query->toSql());
    }
    
    public function testWhereFunctionIn()
    {
        $listId = '1,2,3,4';
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where(function($q)use ($listId){
            $q->whereIn('b.coluna',$listId);
        });
        
        $this->assertEquals("select coluna from tab_usuario b where (b.coluna In (1,2,3,4))",$query->toSql());
    }
    public function testWhereFunctionInOr()
    {
        $listId = "1,2,3,4";
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where(function($q)use ($listId){
            $q->where('b.coluna',2)->whereOr('b.coluna',3)->whereIn('b.coluna',$listId,'OR');
        });
        
        $this->assertEquals("select coluna from tab_usuario b where (b.coluna = 2 OR b.coluna = 3 OR b.coluna In (1,2,3,4))",$query->toSql());
    }

    public function testJoinWhere(){
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',2);

        $query2 = new NQuery();
        $query2->whereIn('b.coluna','1,2,3,4');
        
        $query->joinWhere($query2);

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 2 AND b.coluna In (1,2,3,4)",$query->toSql());
    }

    public function testJoinWhereOr(){
        $query = new NQuery('select coluna from tab_usuario b');
        $query->where('b.coluna',2);

        $query2 = new NQuery();
        $query2->whereIn('b.coluna','1,2,3,4');
        
        $query->joinWhereOr($query2);

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 2 OR b.coluna In (1,2,3,4)",$query->toSql());
    }

    public function testBindParam(){
        $query = new NQuery('select coluna from tab_usuario b where b.coluna = :coluna and b.test = :test or b.list in (:list)');
        $query->bindParam('coluna',2);
        $query->bindParam('test','Joao');
        $query->bindParam('list',[1,2,3]);

        $this->assertEquals("select coluna from tab_usuario b where b.coluna = 2 and b.test = 'Joao' or b.list in (1,2,3)",$query->toSql());
    }

    public function testToArray(){
        $values = [
            ['id'=>1,'name'=>'teste'],
            ['id'=>2,'name'=>'Teste2']
        ];

        $externalMock = m::mock('overload:Illuminate\Database\Capsule\Manager');
        $externalMock->shouldReceive('select')
            ->once()
            ->andReturn( $values);
            
        $query = new NQuery('select coluna from tab_usuario b');
        $this->assertEquals($values,$query->toArray());
    }
    
    public function testToFirst(){
        $values = [
            ['id'=>1,'name'=>'teste'],
            ['id'=>2,'name'=>'Teste2']
        ];

        $externalMock = m::mock('overload:Illuminate\Database\Capsule\Manager');
        $externalMock->shouldReceive('select')
            ->once()
            ->andReturn( $values);
            
        $query = new NQuery('select coluna from tab_usuario b');

        $this->assertObjectHasAttribute('id',$query->toFirst());
    }

    public function testToCollection(){
        $values = [
            ['id'=>1,'name'=>'teste'],
            ['id'=>2,'name'=>'Teste2']
        ];

        $externalMock = m::mock('overload:Illuminate\Database\Capsule\Manager');
        $externalMock->shouldReceive('select')
            ->once()
            ->andReturn([$values]);
            
        $query = new NQuery('select coluna from tab_usuario b');
        $this->assertInstanceOf('Illuminate\Support\Collection',$query->toCollection());
    } 
    public function testToUniqueResult(){
        $values = [
            ['count'=>1]
        ];

        $query = m::mock(new NQuery('select coluna from tab_usuario b'));
        $query->shouldReceive('toArray')
            ->once()
            ->andReturn([$values]);

        $this->assertEquals($query->toUniqueResult(),1);
    } 
}