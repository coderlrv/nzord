<?php
namespace NZord\Helpers;
use Illuminate\Database\Capsule\Manager as DB;

class NQuery{
    const PARAM_STR = 1;
    const PARAM_INT = 2;
    const PARAM_RAW = 3;

    protected $query = '';
    protected $wheres = '';
    protected $limitValue = '';
    protected $orderByValue = '';
    protected $offsetValue = '';
    protected $groupByvalue = '';

    function __construct($sql = ''){
        $this->query = $sql;
    }
    /**
     *  Seta Sql para se utilizada na class
     *
     * @param [string] $sql
     * @return void
     */
    public function setQuery($sql){
        $this->query = $sql;
    }

    /**
     * Adicione uma cláusula where básica à consulta
     *
     * @param  string|\Closure  $column
     * @param  mixed   $operator
     * @param  mixed   $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null){
        //Checa se nao uma sub where
        if ($column instanceof \Closure) {
            $this->setSubWhere($column);
        }else if(is_null($value)){
            $this->setWhere($column,"=", $operator);
        }else{
            $this->setWhere($column,$operator,$value);
        }
        return $this;
    }
    /**
     * Adicione uma cláusula "or where" básica à consulta
     *
     * @param  string|\Closure  $column
     * @param  mixed   $operator
     * @param  mixed   $value
     * @return $this
     */
    public function whereOr($column, $operator = null, $value = null){
        //Checa se nao uma sub where
        if ($column instanceof \Closure) {
            $this->setSubWhere($column,true);
        }else if(is_null($value)){
            $this->setWhere($column,"=", $operator,true);
        }else{
            $this->setWhere($column,$operator,$value,true);
        }
        return $this;
    }
    /**
     * Adicione uma cláusula "where in" básica à consulta
     *
     * @param  string  $column 
     * @param  mixed   $values
     * @param  string  $boolean  AND ou OR
     * @param  bool    $not 
     * @return $this
     */
    public function whereIn($column, $values, $boolean = "AND", $not = false){
        $type = $not ? 'Not In': 'In';
        
        //Verifica se inicio do where
        if(!$this->checkInitialWhere()){
            $this->wheres .= ' '.$boolean.' ';
        }

        //Checa se é array
        if(is_array($values)){
            $value = $this->convertArrayToIn($values);
            $this->wheres .= "$column $type ($value)";
        }

        //Checa se foi string.
        if(is_string($values)){
            $this->wheres .= "$column $type ($values)";
        }
        return $this;
    }
    /**
     * Adicione uma cláusula "where in" básica à consulta
     *
     * @param  string  $column 
     * @param  mixed   $values
     * @param  string  $boolean  AND ou OR
     * @param  bool    $not 
     * @return $this
     */
    public function whereNotIn($column, $values,$boolean= "AND"){
        $this->whereIn($column,$values,$boolean,true);

        return $this;
    }
    /**
     * Adicione uma cláusula "Between" básica à consulta
     *
     * @param  string $column 
     * @param  mixed $from
     * @param  mixed $to
     * @param  string  $boolean  AND ou OR
     * @param  bool    $not 
     * @return $this
     */
    public function whereBetween($column,$from,$to,$boolean="AND",$not=false){
        $this->wheres .= $this->checkInitialWhere() ? "" : " $boolean ";
        $from = $this->toTypeSql($from);
        $to = $this->toTypeSql($to);
        //Adiciona negacao na clausula
        $not = $not ? "not":"";

        $this->wheres .= "$column $not between $from and $to";
        return $this;
    }

    /**
     * Adiciona codições 'coluna is not null' e aplica 'AND' caso ja exista codições.
     * 
     * @param string $column
     * @return $this
     */
    public function whereNotNull($column){
        $this->wheres .= $this->checkInitialWhere() ? '' : ' AND ';
        $this->wheres .= "$column is not null";

        return $this;
    }

    /**
     * Adiciona codições 'coluna is not null' e aplica 'AND' caso ja exista codições.
     * 
     * @param string $column
     * @return $this
     */
    public function whereNull($column){
        $this->wheres .= $this->checkInitialWhere() ? "" : " AND ";
        $this->wheres .= "$column is null";
        
        return $this;
    }
    /**
     * Adiciona sql na codições where da consulta e aplica 'AND' caso ja exista codições.
     * 
     * @param string $sql
     * @return $this
     */
    public function whereRaw($sql){
        $this->wheres .= $this->checkInitialWhere() ? "" : " AND ";
        $this->wheres .= "$sql";

        return $this;
    }
    /**
     * Defini o "orderBy " na query.
     * 
     * @param string | array $columns - Coluna ou lista de coluna a ser ordenadas.
     * @param string | array $order - order ASC ou DESC.
     * @return $this
     */
    public function orderBy($columns,$order = 'ASC'){
        if(is_array($columns)){
            $value = implode(",", $columns);
            $this->orderByValue = " order by $value $order";
        }else{
            $this->orderByValue = " order by $columns $order";
        }
        return $this;
    }
    /**
     * Defini o "limit " na query.
     * 
     * @param int $value - quantidade limite para 
     * @return $this
     */
    public function limit($value){
        $this->limitValue = "limit $value";
        return $this;
    }
    /**
     * Defini o "offset " na query.
     * @param int $value
     * @return $this
     */
    public function offSet($value){
        $this->offsetValue = "offset $value";
        return $this;
    }
    /**
     * Defini o "groupBy " na query.
     * @param int $value
     * @return $this
     */
    public function groupBy($value){
        $this->groupByvalue = "group by $value";
        return $this;
    }
    /**
     * Retorna consulta sql completa
     * @return string
     */
    public function toSql(){
        $this->query .= (strlen($this->wheres) > 0 ) ? ' where': '';
        return rtrim("$this->query $this->wheres $this->groupByvalue $this->orderByValue $this->limitValue $this->offsetValue");
    }
    /**
     * Retorna codições where gerada.
     *
     * @return string
     */
    public function toWhere(){
        return (string) $this->wheres;
    }
    /**
     * @param string $params
     * @param mixed $value
     * @param int $typeData NQuery::PARAM_INT (int) / NQuery::PARAM_STR (string) / NQuery::PARAM_RAW 
     * @return void
     */
    public function bindParam(){
        $count = func_num_args();
        //Checa se nao é funcao
        if($count == 2 || $count == 3){

            //Passando 2 paramentros set a '=' por padrão
            $params = func_get_arg(0);
            $value = func_get_arg(1);

            //Ve tipo dado por paramentro.
            if($count == 3){
                $typeData = func_get_arg(2);

                if($typeData == 1){
                    $value = "'$value'";
                }
            }else{
                if(is_array($value)){
                    $value = $this->convertArrayToIn($value);
               
                }else if(is_string($value) || is_float($value)){
                    $value = "'".$this->sqlEscapeMimic($value)."'";
                }
            }
          
            //Substitui parametros
            $this->query = str_replace(":$params",$value,$this->query);
            $this->wheres = str_replace(":$params",$value,$this->wheres);
            $this->limitValue = str_replace(":$params",$value,$this->limitValue);
            $this->orderByValue = str_replace(":$params",$value,$this->orderByValue);
            $this->offsetValue = str_replace(":$params",$value, $this->offsetValue);
        }else{
            throw new \Exception("Número de argumentos inválido");
        }

        return $this;
    }
    /**
     * Junta where com 'AND' NQuery com a passada por paramentro.
     *
     * @param \NZord\Helpers\NQuery $query
     * @param string $boolean   AND / OR
     * @return void
     */
    public function joinWhere(\NZord\Helpers\NQuery $query,$boolean = 'AND'){
        //Checa se where inicial ou join nao é nullo.
        if(!$this->checkInitialWhere() && strlen($query->toWhere()) > 0){
            $this->wheres .= " $boolean ";
        }
        
        $this->wheres .= $query->toWhere();

        return $this;
    }
    /**
     * Junta where com  'OR' nquery com a passada por paramentro.
     * @param \NZord\Helpers\NQuery $query
     * @return void
     */
    public function joinWhereOr(\NZord\Helpers\NQuery $query){
        $this->joinWhere($query,'OR');

        return $this;
    }
    /**
     *  Executa sql e converte para collection
     *
     * @return collection
     */
    public function toCollection(){
        $result = DB::select((string) $this->toSql());

        return collect($result);
    }

    /**
     * Executa query e retorna contador da query gerada.
     *
     * @return int
     */
    public function toCount(){
        $sql =  $this->toSql();
        $rex = "/^select((.|\n)*)from/";

        //Subtitui pelo contador.
        $result = preg_replace($rex,"select COALESCE(count(*),0) as count from ",$sql);
        
        return DB::select($result)[0]->count;
    }
    /**
     * Executa query e retorna primeiro registro da pesquisa como objeto 
     *
     * @return object
     */
    public function toFirst(){
        $this->limit(1);
        $result = $this->toArray();
        return count($result) ? (object) $result[0] : null;
    }
    /**
     * Executa query e retorna unico resultado, pegando primeira coluna.
     * 
     *  
     * @return mixed
     */
    public function toUniqueResult($default = null){
        $this->limit(1);
        $result = $this->toArray();
        
        if(count($result)){
            return current($result[0]);
        }else{
            return $default;
        }
    }

    /**
     *  Executa query e retorna array do resultado
     *  
     * @return array
     */
    public function toArray(){
        $result = DB::select((string) $this->toSql());
        return $result;
    }

    //--------------------------------------------------------------------------------
    private function setWhere($colums,$expression,$value,$isOr = false){
        if(!$this->checkInitialWhere()){
            $this->wheres .= ( $isOr ? " OR " : " AND ");
        }
        $this->wheres .= $colums." ".$expression;
        $this->wheres .= " ".$this->toTypeSql($this->sqlEscapeMimic($value));
    }

    private function setSubWhere($callback,$isOr = false){
        $query = new NQuery();
        call_user_func_array($callback,[$query]);
        $subCondition = $query->toWhere();

        if(!$this->checkInitialWhere()){
            $this->wheres .= $isOr ? " OR " :" AND ";
        }

        $this->wheres .= "($subCondition)";
    }
    private function checkInitialWhere(){
        return (strlen($this->wheres) == 0);
    }

    private function convertArrayToIn(array $value){
        if(is_int($value[0])){
            $value = implode(",",$value);
        }else{
            $value = array_map(function($val){
                return "'$val'";
            },$value);

            $value = implode(",",$value);
        }
        return $value;
    }
    
    private function antiInjection($obj) {	   
        $obj = preg_replace("/(from|alter table|select|insert|delete|update|where|drop table|show tables|#|--|\\)/i", "", $obj);
        $obj = trim($obj);
        $obj = strip_tags($obj);
        if(!get_magic_quotes_gpc()) {
            $obj = addslashes($obj);
            return $obj;	   
        }
    }   
    function sqlEscapeMimic($inp) { 
        if(is_array($inp)) 
            return array_map(__METHOD__, $inp); 
    
        if(!empty($inp) && is_string($inp)) { 
           return addslashes($inp); 
        } 
    
        return $inp; 
    } 

    private function toTypeSql($value){
        if(!is_int($value)){
            return "'$value'";
        }else{
            return $value;
        }
    }


    function __toString(){
        return (string) $this->toSql();
    }
}