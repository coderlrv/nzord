<?php
namespace NZord\Helpers;

use Illuminate\Database\Capsule\Manager as DB;
use Dompdf\Dompdf;

class NReportTwig {

    protected $container;
    
    //--------------------------------------------------------------------------------
    function __construct(\Slim\Container $container){
        $this->container = $container;
    }
    //--------------------------------------------------------------------------------
    /**
     * Gerar relatorio com modelo passado por paramentro
     *
     * @param string $chave - chave do relátorio
     * @param string $tipo  - pdf | download | html
     * @param int $id
     * @param array $args
     * @param array $options 
     *  opções disponivel
     *   * namePrint = nome do arquivo ao gerar.
     * @return void
     */
    public function gerar($chave,$tipo ='pdf',$id,$args=[], array $options=[]){
        $header = '';
        $footer = '';

        //Aplica options default
        $options = $this->getOptions($options,[
            'namePrint'=>'report_'.$id.'_'.date('YmdHis').'.pdf',
            'btnPrint'=>true,
            'cabecalhoRodape'=>true
        ]);
        
        //Busca relatorio
       
        $rel = DB::table('tab_relModelo')->where('chave',$chave)->first();
        if( $rel->tabela != 7 ){
            $relTipo = DB::table('tab_relTipo')->where('id',$rel->tabela)->first();
        
            $result = (array) DB::select('select * from '.$relTipo->nome.' as x where id = :id ',['id'=>$id]);
        }else{
            $codSql = $rel->cod_sql;
            $result = (array) DB::select('select * from ('.$codSql.') as x ',['id'=>$id]);
        }
        $dados = (array) $result[0];
        $paper = ( $rel->rotacao == 1 ) ? 'portrait':'Landscape';

        if($options->cabecalhoRodape){
            // Adiciona cabeçalho
            if($rel->cabecalho){
                $cabecalho = DB::table('tab_parametro')->where('id',$rel->cabecalho)->first();
                if($cabecalho) $header = $cabecalho->valor;
            }
            
            // Adiciona rodapé
            if($rel->rodape){
                $rodape = DB::table('tab_parametro')->where('id',$rel->rodape)->first();
                if($rodape) $footer = $rodape->valor;
            }
        }
       

        $loader = new \Twig_Loader_Array(['report.html' => $this->renderModelo($rel->detalhe,$header,$footer,$tipo)]);
        $twig = new \Twig_Environment($loader);

        //Add Extenções;
        $twig->addExtension(new \Base\Helpers\TwigExtensions\TwigRelExtensionCustom());
        $twig->addExtension(new \Base\Helpers\TwigExtensions\TwigFiltersCustom());
        $twig->addExtension(new \Slim\Views\TwigExtension($this->container->get('router'), $this->container->get('request')->getUri()));

        $twig->getExtension('Twig_Extension_Core')->setNumberFormat(2, ',', '.');
        $twig->addFunction('dataExtenso', new \Twig_Function_Function('dataExtenso'));
        $twig->addFunction('valorPorExtenso', new \Twig_Function_Function('valorPorExtenso'));
        $twig->addFunction('iniciaisNome',new \Twig_Function_Function('iniciaisNome'));
        
        //Renderiza template
        array_push($args,['tipo'=>$tipo]);
        $html = $twig->render('report.html',[ 'dados'=>$dados,'params'=>$args]);
        
        //Tipo de documento
        if( $tipo == 'html'){
            $this->renderHtml($html,$options->btnPrint);
        }elseif ($tipo == 'download'){
            $this->renderPdf($html,$options->namePrint,$paper,true);
        }else{
            $this->renderPdf($html,$options->namePrint,$paper);
        }
     
    }
    //--------------------------------------------------------------------------------
    private function renderPdf($html,$nameFile,$paper,$forceDownload = false){
        $pdf = new DOMPDF();
        $pdf->set_option('enable_remote', true); 
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->load_html($html);
        $pdf->set_paper('A4',$paper);
        $pdf->render();

        if($forceDownload){
            $pdf->stream($nameFile);
        }else{
            $pdf->stream($nameFile,['Attachment'=>0]);
        }
    }
    //--------------------------------------------------------------------------------
    private function renderHtml($html,$btnPrint=true){
        $body = '';
        
        if($btnPrint){
            $body =  "<script src='http://192.168.12.202/nzord/public/js/app.js'></script>
            <button fieldtype='button' class='btn btn-sm btn-primary hidden-print' onclick='printDiv(\"prtReport\")'>
                <span class='fa fa-print'></span> Imprimir
            </button>";
        }
        
        $body .= "<div id='prtReport' class='small'> $html </div>";
          
        echo $body;
    }
    //--------------------------------------------------------------------------------
    private function renderModelo($detalha,$header='',$footer='',$tipo='pdf'){
        $page = '@page { margin: 10px 20px 15px; }';
        
        if( $header ){
            $page = '@page { margin: 120px 50px 15px; }';
        }

        $texto = '<html>
                    <head>
                    <style>
                    '.$page;

        if($tipo=='pdf'){
            $texto .= '#header { position: fixed; left: 0px; top: -150px; right: 0px; height: 50px; }
            #footer { position: fixed; left: 0px; bottom: -10px; right: 0px; height: 20px; font-size: 9px; }';
        }

        $texto .= '#footer .page:after { content: counter(page); }
                        hr {margin-bottom: px; border-width: 1px; }
                        body { font-family: Arial, Helvetica, sans-serif; }
                    </style>
                    </head>
                    <body>
                        <div id="header">'.$header.'</div>
                        <div id="footer">'.$footer.'</div>
                        <div id="content">'.$detalha.'</div>
                    </body>
                </html>';

        return $texto;
    }
    //--------------------------------------------------------------------------------
    private function getOptions(array $options,$default){
        if(count($options) == 0 ) return (object) $default;
        return (object) array_merge($default,$options);
    }
}
