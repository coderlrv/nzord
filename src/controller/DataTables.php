<?php
namespace NZord\Controller;

use Illuminate\Database\Capsule\Manager as DB;

class DataTables{
    protected $namespace = 'slim_app';
    
    public function __construct($namespace = null){
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
        $this->setNamespace($this->namespace);
    }
        
    public static function simple( $request, $sql ){
        $where = self::filter($request);
    
        if( $request['draw'] == '0'  ){
            $dados = DB::select('select x.* from ( '.$sql.' LIMIT 1 ) as x ');
        }else{
            $order = self::order($request);
            $limit = self::limit($request);

            $dados = DB::select('select x.* from ( '.$sql.' ) as x '.$where.$order.$limit );
            
            /*$recordsTotal = $db->get_results('select count(*) as qtde from ( '.$sql.' ) as x ' );*/
            $recordsTotal = DB::select('select count(*) as qtde from ( '.$sql.' ) as x ' );
            $recordsFiltered = DB::select('select count(*) as qtde from ( '.$sql.' ) as x '.$where );
        }
        if( $dados ){
            $k = (array)$dados[0];
            $k = @array_keys($k);
            $k = @array_map('strtoupper', $k);
        }
        
        if( $request['draw'] == '0'  ){
            $qfield = 0;
            $tfield = 0;
        }else {
            $qfield = $recordsFiltered[0]->qtde;
            $tfield = $recordsTotal[0]->qtde;
        }
        $data = json_decode(json_encode($dados),false);
        //$draw = isset ( $_REQUEST['draw'] ) ? intval( $_REQUEST['draw'] ) : 0;
        
        return [ 
            "draw"              => isset ( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"      => intval( $tfield ),
            "recordsFiltered"   => intval( $qfield ),
            "columns"			=> @$k,
            "data"           	=> $data
            /*,"sql"               => $sql*/
        ];
    }
    
    
    public static function simple2( $request, $class ){
        $where = self::filter($request);
        
        if( $request['draw'] == '0'  ){
            $dados = DB::select('select x.* from ( '.$sql.' LIMIT 1 ) as x ');
        }else{
            $order = self::order($request);
            $limit = self::limit($request);

            $dados = $class::selectGrid( str_replace(' ORDER BY ', '',$order).$limit,str_replace(' WHERE ', '',$where));            
        }
        if( $dados ){
            $k = (array)$dados[0];
            $k = @array_keys($k);
            $k = @array_map('strtoupper', $k);
        }
        
        if( $request['draw'] == '0'  ){
            $qfield = 0;
            $tfield = 0;
        }else {
            $qfield = @$recordsFiltered[0]->qtde;
            $tfield = @$recordsTotal[0]->qtde;
        }
        $data = json_decode(json_encode($dados),false);
        
        return array(
            "draw"            => isset ( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $tfield ),
            "recordsFiltered" => intval( $qfield ),
            "columns"			=> @$k,
            "data"           	=> $data
        );
    }
    
    static function limit ( $request ){
        $limit = '';
        
        if ( isset($request['start']) && $request['length'] != -1 ) {
            $limit = " LIMIT ".intval($request['start']).", ".intval($request['length']);
        }
        
        return $limit;
    }
    
    
    static function filter ( $request, $other=null ){
        $sqlStr = null;
        $globalSearch = array();  
        
        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = strtolower($request['search']['value']);
            
            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                
                if ( $requestColumn['searchable'] == 'true' ) {
                    $value = strip_tags($str);
                    $value = addslashes($value); 

                    $globalSearch[] = " UPPER(`".$requestColumn['name']."`) LIKE CONCAT('%','".$value."','%')";
                }
            }
        }
 
        if ( count( $globalSearch ) ) {
            $sqlStr = '('.implode(' OR ', $globalSearch).')';
        }        
        if ( $other ){
            if ( $sqlStr ){
                $sqlStr .= ' AND '.$other;
            }else{
                $sqlStr = $other;
            }
        }        
        if ( $sqlStr != null ) {
            $where = ' WHERE '.$sqlStr;
        }        
        return @$where;
    }
    
    
    static function order ( $request ){
        $order = '';
        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            
            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                
                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ? 'ASC' :	'DESC';
                    $orderBy[] = ($columnIdx + 1).' '.$dir;
                }
            }
            $order = ' ORDER BY '.implode(', ', $orderBy);
        }
        return $order;
    }
}