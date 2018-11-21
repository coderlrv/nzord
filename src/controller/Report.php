<?php
namespace NZord\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Database\Capsule\Manager as DB;
use Dompdf\Dompdf;
use Modulos\System\Models\Sessao;
use Modulos\System\Models\RelModelo;

class Report {
    
    protected $app;
    
    public function __construct($app) {
        $this->app = $app;
    }
    //--------------------------------------------------------------------------------
    public function index( $request, $response, $args=null){
        $params = explode('/', $args['params']);
        $tipo = $params[0];
        $chave = $params[1];
        $id = $params[2];
        self::gera($request, $response, array('tipo'=>$tipo,'chave'=>$chave,'id'=>$id) );
        
    }
    //--------------------------------------------------------------------------------
    public function gera( $request, $response, $args=null){
        $tipo = $args['tipo'];
        $chave = $args['chave'];
        $id = $args['id'];
        $arquivoPrint = 'report_'.$id.'_'.date('YmdHis').'.pdf';
        //header ('Content-type: text/html; charset=UTF-8');
        
        $rel = RelModelo::where('chave',$chave)->get();
        $rel = $rel[0];
        $rel['cod_sql'] = str_replace('?', $id, $rel['cod_sql']);
        $dados = DB::select( 'select * from ('.$rel['cod_sql'].') as x ');
        $dados = json_decode(json_encode($dados[0]), true);
        
        $keys = array_keys($dados);
        foreach($keys as $k => $v){
            $busca[$k] = '{'.strtoupper($v).'}';
        }
                
        foreach($keys as $k => $v){
            $campo = strtolower($v);
            if( strpos($campo, 'texto') !== false or strpos($campo, 'detalhe') !== false or strpos($campo, 'conteudo') !== false  ){
                $altera[$k] = utf8_decode(nl2br($dados[$v]));
            }elseif( strpos($campo, 'cpf_cnpj') !== false ){
                $altera[$k] = formatarCPF_CNPJ($dados[$v],true);
            }elseif( strstr($campo, '_table_') ){
                $altera[$k] = montaTableRelSQL($dados[$v]);
            }else{
                $altera[$k] = utf8_decode($dados[$v]);
            }
        }
        
        $paper = ($rel['rotacao'] == 1 ) ? 'portrait':'Landscape';
        
        $modelo = addModeloPadrao( utf8_decode($rel['detalhe']), $rel['cabecalho'], $rel['rodape'] );
        $texto = str_replace($busca, $altera, $modelo);
        
        if( $tipo == 'html'){
            echo '<button fieldtype="button" class="btn btn-sm btn-primary hidden-print" onclick="printData(\'prtReport\');">
            		<span class="fa fa-print"></span> Imprimir
            	</button>';
            echo '<div id="prtReport" class="small">';
                echo $texto;
            echo '</div>';
        }else{
            $pdf = new DOMPDF();       
            $pdf->set_option('enable_remote', true); 
            $pdf->set_option('isHtml5ParserEnabled', true);
            $pdf->load_html($texto);
            $pdf->set_paper('A4',$paper);
            //$pdf->set_paper('A4','portrait');
            $pdf->render();
            $pdf->stream($arquivoPrint,array('Attachment'=>0)); 
        }
    }  
    //--------------------------------------------------------------------------------
}


