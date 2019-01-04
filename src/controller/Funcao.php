<?php
namespace NZord\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class Funcao extends Controller
{
    //--------------------------------------------------------------------------------
    public function index(Request $request, Response $response, $args = null)
    {
        $post  = $request->getQueryParams();
        $act   = $post['act'];
        $dados = $post['dados'];

        $result = $this->$act($dados);

        return $response->withJson($result);
    }

    //--------------------------------------------------------------------------------
    public function getAlias($args = null)
    {
        $dados   = @$args;
        $retorno = array();

        $sql = base64_decode($dados);
        if( strlen($sql) <= 30 ) {
            $ret = \Modulos\System\Models\RelTipo::where('nome',$sql)->get();
            if($ret){
                $sql = $ret[0]->codsql;
            }
        }
        $sql = str_replace('select ', '', str_replace('SELECT ', '', $sql));
        $sql = strtoupper(str_replace('\n', '', $sql));
        $sql = preg_replace("/\(([^()]*+|(?R))*\)/", "null", $sql);

        $sel = explode('FROM', $sql);
        $sel = explode(',', $sel[0]);
        $i   = 1;
        foreach ($sel as $val) {
            if (strpos($val, ' AS ')) {
                $val       = explode(' AS ', $val);
                $nome      = trim(end($val));
                $retorno[] = array(
                    'idtab' => $i,
                    'nome'  => strtoupper($nome),
                );
            } else {
                $val       = explode('.', $val);
                $nome      = trim(end($val));
                $retorno[] = array(
                    'idtab' => $i,
                    'nome'  => @strtoupper($nome),
                );
            }
            $i++;
        }
        return $retorno;
    }
    //--------------------------------------------------------------------------------
}
