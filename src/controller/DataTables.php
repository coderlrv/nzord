<?php
namespace NZord\Controller;

use NZord\Helpers\NQuery;
use Illuminate\Database\Capsule\Manager as DB;

class DataTables{
    protected $namespace = 'slim_app';
    
    public function __construct($namespace = null){
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
        $this->setNamespace($this->namespace);
    }

    public static function getDriver(){
        return DB::connection()->getDriverName();
    }

    public static function simple( $request, $sql ){
        $driver = static::getDriver();
        $query = static::getSql($request,$sql,$driver);

        //Busca dados
        $dados = $query->toArray();

        //TotalFiltrado
        $recordsFiltered = count($dados);

        // retorna  colunas
        if($dados){
            $columns = (array)$dados[0];
            $columns = @array_keys($columns);
            $columns = @array_map('strtoupper', $columns);
        }
        
        if( $request['draw'] == '0' ){
            $recordsTotal = 0;
            $recordsTotal = 0;
        }else {

            $recordsTotal = $recordsFiltered;
            $recordsTotal = static::getTotRegister($sql);
        }

        return [ 
            "draw"              => isset ( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"      => intval( $recordsTotal ),
            "recordsFiltered"   => intval( $recordsTotal ),
            "columns"           => @$columns,
            "data"              => $dados
        ];
    }

    static function getSql($request,$sql,$driver = 'mysql'){
        if( $request['draw'] == '0'  ){
            $query = new NQuery('select x.* from ( '.$sql.' LIMIT 1 ) as x ');

        }else{
            $query = new NQuery('select x.* from ( '.$sql.' ) as x');
            
            //Ordenação
            $orders = self::order($request);
            foreach($orders as $order){
                $query->orderBy($order['column'],$order['order']);
            }

            // Paginacao no dados. 
            // lenght = Limite
            // start = Pagina atual
            if ( isset($request['start']) && $request['length'] != -1 ) {
                $query->limit($request['length']);
                $query->offset($request['start']);
            }

            //Adiciona where
            $where = self::filter($request,$driver);
            $query->joinWhereOr($where);
        }

        return $query;
    }

    static function filter($request,$driver='mysql'){
        $query = new NQuery(); 
        
        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = strtolower($request['search']['value']);
            
            if($driver == 'pgsql'){
                $query->whereRawOR("x::text like E'%:search%'");

                //Paramentros
                $value = strip_tags($str);
                $value = addslashes($value);
                $query->bindParam('search',$value,NQuery::PARAM_RAW);

            }else{
                for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                    $requestColumn = $request['columns'][$i];
                    
                    if ( $requestColumn['searchable'] == 'true' ) {
                        $value = strip_tags($str);
                        $value = addslashes($value);

                        $query->whereRawOR("`".strtoupper($requestColumn['name'])."` like CONCAT('%','".$value."','%')");
                    }
                }
            }
        }

        return $query;
    }
    
    static function order ( $request ){
        $orderBy = [];

        if ( isset($request['order']) && count($request['order']) ) {

            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
            
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                
                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ? 'ASC' :	'DESC';
                    // [ coluna, ordernação ]
                    $orderBy[] = ['column' => ($columnIdx + 1),'order' => $dir];
                }
            }
        }
        return $orderBy;
    }

    static function getTotRegister($sql){
        return (new NQuery('select count(*) as qtde from ( '.$sql.' ) as x '))->toUniqueResult();
    }
}