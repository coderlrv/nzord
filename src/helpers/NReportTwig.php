<?php
namespace NZord\Helpers;

use Dompdf\Dompdf;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;

class NReportTwig
{

    protected $container;

    //--------------------------------------------------------------------------------
    public function __construct(\Slim\Container $container)
    {
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
    public function gerar($chave, $tipo = 'pdf', array $argsSQL = [], array $argsTmpl = [], array $options = [])
    {
        $header  = '';
        $footer  = '';
        $nameDoc = 'report_' . date('YmdHis');
      
        //Aplica options default
        $options = $this->getOptions($options, [
            'namePrint'       => 'report_' . date('YmdHis'),
            'btnPrint'        => true,
            'cabecalhoRodape' => true,
            'debug'           => false,
        ]);

        //Busca relatorio
        $rel    = DB::table('tab_relModelo')->where('chave', $chave)->first();
        $params = isset($rel->parametros) ? (array) json_decode($rel->parametros) : [];

        if (!$rel) {
            throw new \Exception('Relatório não encontrado.Chave=' . $chave);
        }

        if ($rel->tabela != 7) {

            $relTipo = DB::table('tab_relTipo')->where('id', $rel->tabela)->first();

            $codSql = 'select * from ' . $relTipo->nome . ' as x ' . $this->makeClause($params, $argsSQL);

            $query = new NQuery($codSql);
          
            $query->bindParams($argsSQL);
            $result = $query->toArray();

            // Adiciona debug
            if ($options->debug) {
                printR([
                    'sql'      => $codSql,
                    'argsSQL'  => $argsSQL,
                    'argsTmpl' => $argsTmpl,
                    'sqlRaw'   => $query->toSql()
                ]);
            }

        } else {
            if (empty($rel->cod_sql)) {
                $result = [];
            } else {
                $codSql = $rel->cod_sql . ' ' . $this->makeClause($params, $argsSQL);
             
                //Criar sql
                $query = new NQuery('select * from (' . $codSql . ') as x ');
                $query->bindParams($argsSQL);
                $result = $query->toArray();

                // Adiciona debug
                if ($options->debug) {
                    printR([
                        'sql'      => $codSql,
                        'argsSQL'  => $argsSQL,
                        'argsTmpl' => $argsTmpl,
                        'sqlRaw'   => $query->toSql()
                    ]);
                }
            }
        }

        $dados = $rel->tipo == 1 ? $result : @$result[0];
        $paper = ($rel->rotacao == 1) ? 'portrait' : 'Landscape';

        if ($options->cabecalhoRodape) {
            // Adiciona cabeçalho
            if ($rel->cabecalho) {
                $cabecalho        = DB::table('tab_parametro')->where('id', $rel->cabecalho)->first();
                $dhHeader         = $nameDoc . '<br /><b>Data: </b>' . date('d/m/Y') . ' <br /><b>Hora: </b>' . date('H:i:s');
                $cabecalho->valor = str_replace('{DATAHORA}', $dhHeader, $cabecalho->valor);
                if ($cabecalho) {
                    $header = $cabecalho->valor;
                }

            }

            // Adiciona rodapé
            if ($rel->rodape) {
                $rodape        = DB::table('tab_parametro')->where('id', $rel->rodape)->first();
                $dhRodape      = date('d/m/Y H:i:s') . ' | ' . $nameDoc;
                $rodape->valor = str_replace('{DATAHORA}', $dhRodape, $rodape->valor);
                if ($rodape) {
                    $footer = $rodape->valor;
                }

            }
        }

        // Loader
        $settings = $this->container->get('settings')['render'];

        $loader1 = new \Twig_Loader_Filesystem(__DIR__.'/../templates');
        $loader2 = new \Twig_Loader_Array(['report.html' => $this->renderModelo($rel->detalhe, $header, $footer, $tipo)]);

        $loader = new \Twig_Loader_Chain();
        $loader1->addPath($settings['template_path']);

        $loader->addLoader($loader1);
        $loader->addLoader($loader2);
        $twig   = new \Twig_Environment( $loader );

        // Add Extenções;
        $twig->addExtension(new \NZord\Helpers\TwigExtensions\TwigRelExtensionCustom());
        $twig->addExtension(new \NZord\Helpers\TwigExtensions\TwigFiltersCustom());

        $uri = $this->container->get('request')->getUri();
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){
            $uri = $uri->withScheme($_SERVER['HTTP_X_FORWARDED_PROTO']);
        }

        $twig->addExtension(new \Slim\Views\TwigExtension($this->container->get('router'), $uri));

        $twig->getExtension('Twig_Extension_Core')->setNumberFormat(2, ',', '.');
        $twig->addFunction('dataExtenso', new \Twig_Function_Function('dataExtenso'));
        $twig->addFunction('valorPorExtenso', new \Twig_Function_Function('valorPorExtenso'));
        $twig->addFunction('iniciaisNome', new \Twig_Function_Function('iniciaisNome'));
        $twig->addFunction('ucwords', new \Twig_Function_Function('ucwords'));
        $twig->addFunction('convertColGrid', new \Twig_Function_Function('convertColGrid'));
        $twig->addFunction('arr_sum', new \Twig_Function_Function('arr_sum'));


        //Renderiza template
        array_push($argsTmpl, ['tipo' => $tipo]);
        $html = $twig->render('report.html', ['dados' => $dados, 'args' => $argsTmpl, 'params' => $argsSQL]);

        //Tipo de documento
        if ($tipo == 'html') {
            return $this->renderHtml($html, $options->btnPrint);
        } elseif ($tipo == 'odt') {
            return $this->renderODT($html, $options->namePrint);
        } elseif ($tipo == 'download') {
            return $this->renderPdf($html, $options->namePrint, $paper, true);
        } else {
            return $this->renderPdf($html, $options->namePrint, $paper);
        }
    }
    /**
     * Checa se argumentos existe na query sql.
     * :nomeargumento
     * @param string $str
     * @param array $args
     * @return boolean
     */
    private function checkParam($str, $args)
    {
        foreach ($args as $key => $arg) {
            if (preg_match(sprintf('/:%s/', $key), $str, $result)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Criar clasuras sql a partir do parametros enviado
     *
     * @param array $params
     * @param array $args
     * @return string
     */
    private function makeClause($params, $args)
    {
        $where   = '';
        $having  = '';
        $orderBy = '';
        $groupBy = '';

        // Where
        $paramsWhere = array_filter($params, function ($param) {return strtoupper($param->clause) == 'WHERE' ? true : false;});
        foreach ($paramsWhere as $key => $param) {
            if (!property_exists($param, 'optional') || $param->optional == 0) {

                //Caso for primeira expressao , nao passa codição.
                if (!empty($where)) {
                    $where .= ' ' . $param->condition . ' ';
                }

                $where .= $param->expression;
            } else {
                //Opcional
                //Checa se existe argumento na expressao
                if ($this->checkParam($param->expression, $args)) {
                    if (!empty($where)) {
                        $where .= ' ' . $param->condition . ' ';
                    }

                    $where .= $param->expression;
                }
            }
        }

        //Having
        $paramsHaving = array_filter($params, function ($param) {return strtoupper($param->clause) == 'HAVING' ? true : false;});
        foreach ($paramsHaving as $key => $param) {
            if (!property_exists($param, 'optional') || $param->optional == 0) {
                //Caso for primeira expressao , nao passa codição.
                if (!empty($having)) {
                    $having .= ' ' . $param->condition . ' ';
                }
                $having .= $param->expression;
            } else {
                //Opcional
                //Checa se existe argumento na expressao
                if ($this->checkParam($param->expression, $args)) {
                    if (!empty($having)) {
                        $having .= $param->condition;
                    }

                    $having .= ' ' . $param->expression;
                }
            }
        }
        // Group By
        $paramsGroupBy = array_filter($params, function ($param) {return strtoupper($param->clause) == 'GROUP_BY' ? true : false;});
        foreach ($paramsGroupBy as $key => $param) {
            $groupBy = $param->expression;
        }
        // Order By
        $paramsOrderBy = array_filter($params, function ($param) {return strtoupper($param->clause) == 'ORDER_BY' ? true : false;});
        foreach ($paramsOrderBy as $key => $param) {
            $orderBy = $param->expression;
        }

        //caso exista passa clasura where.
        if (!empty($where)) {
            $where = 'where ' . $where;
        }
        //Caso exista passa clasura having.
        if (!empty($having)) {
            $having = 'having ' . $having;
        }
        //Caso exista passa clasura group by.
        if (!empty($groupBy)) {
            $groupBy = 'Group by ' . $groupBy;
        }
        //Caso exista passa clasura order by.
        if (!empty($orderBy)) {
            $orderBy = 'Order by ' . $orderBy;
        }

        return sprintf('%s %s %s %s', trim($where), trim($having), trim($groupBy), trim($orderBy));
    }
    //--------------------------------------------------------------------------------
    private function renderPdf($html, $nameFile, $paper, $forceDownload = false)
    {
        $pdf = new DOMPDF();
        $pdf->set_option('enable_remote', true);
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->load_html($html);
        $pdf->set_paper('A4', $paper);
        $pdf->render();

        if ($forceDownload) {
            $pdf->stream($nameFile . '.pdf');
        } else {
            $pdf->stream($nameFile . '.pdf', ['Attachment' => 0]);
        }
    }
    //--------------------------------------------------------------------------------
    private function renderHtml($html, $btnPrint = true)
    {
        $body = '';

        $uri = $this->container->get('request')->getUri();
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){
            $uri = $uri->withScheme($_SERVER['HTTP_X_FORWARDED_PROTO']);
        }

        $port = ($uri->getPort()) ? ':' . $uri->getPort() : '';
        $dir  = $uri->getScheme() . '://' . $uri->getHost() . $port . $uri->getBasePath();

        if ($btnPrint) {
            //$body .= '<script src="'.$dir.'/node_modules/jquery/dist/jquery.min.js"></script>';
            $body .= '<script> 
                if (typeof ifJqueryExecute === "function") {
                    ifJqueryExecute();
                }
            </script>';
            $body .= '<!-- LoadJS -->
                <script>
                    var baseUrl = "'. $dir.'";
                    var baseAppUrl = "'. $dir.'/app";
                </script>
                <script src="'. $dir .'/node_modules/loadjs/dist/loadjs.min.js"></script>
                <script src="'. $dir .'/libs/nzord-app/src/js/nzord.require.js"></script>
                <!-- Fim Loadjs-->';
            $body .= '<style>
            @media print {
                .highcharts-button-symbol{
                    display:none!important;
                }
            }
            </style>';

            $body .= '<link href="' . $dir . '/node_modules/bootstrap/dist/css/bootstrap.css?v=' . time() . '" rel="stylesheet">
                <link href="' . $dir . '/node_modules/font-awesome/css/font-awesome.min.css?v=' . time() . '" rel="stylesheet">
                <script src="' . $dir . '/libs/nzord-app/src/js/nzord.require.js"></script>
                <script src="' . $dir . '/libs/nzord-app/src/js/nzord-aux.js?v=' . time() . '"></script>
                <script src="' . $dir . '/js/app.modules.js"></script>';

    
            $body .= "<button class='btn btn-sm btn-primary hidden-print btn-print' id='btn-print-direct' onclick=\"printData('prtReport')\">
                <i class='fa fa-print'></i> Imprimir </button>";
            $body .= '<button class="btn btn-sm btn-success hidden-print btn-print" id="btn-print-excel" style="margin-left:5px;" onclick="generateexcel(\'prtReport\')">
                <i class="fa fa-table"></i>  Export Excel </button>';
        }

        $body .= "<div id='prtReport' class='small' style='margin-top:10px'> $html </div>";

        return $body;
    }
    //--------------------------------------------------------------------------------

    private function renderODT($html, $nameFile, $forceDownload = true)
    {
        $response = $this->container->get('response');

        $handle  = fopen("php://temp", "wb+");
        $body    = new Stream($handle);
        $htmlDoc = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">' . $html;
        $body->write($htmlDoc);

        $headers = new Headers();
        $headers->set("Content-type", "application/vnd.oasis.opendocument.text;charset=ISO8859_1");
        $headers->set('Content-Disposition', 'attachment;filename="' . $nameFile . '.odt"');
        $headers->set('Expires', '0');
        $headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $headers->set('Pragma', 'public');

        return new Response(200, $headers, $body);
    }

    //--------------------------------------------------------------------------------
    private function renderModelo($detalha, $header = '', $footer = '', $tipo = 'pdf')
    {
        $page = '@page { margin: 10px 20px 15px; }';

        if ($header) {
            $page = '@page { margin: 120px 50px 15px; }';
        }

        // Aplica utf no arquivo.
        if ($tipo == 'odt') {
            $texto = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                        <html>
                        </head>';
            $texto .= '<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html;charset=utf-8">';
        } else {
            $texto = '<html moznomarginboxes mozdisallowselectionprint>
            <head>';
        }

        $texto .= '<style type="text/css">' . $page;
        if ($tipo == 'pdf') {
            $texto .= '#header { position: fixed; left: 0px; top: -150px; right: 0px; height: 50px; }
            #footer { position: fixed; left: 0px; bottom: -10px; right: 0px; height: 20px; font-size: 9px; }';
        }
        $texto .= '#footer .page:after { content: counter(page); }
                        hr { border-width: 1px; }
                        body { font-family: Arial, Helvetica, sans-serif; }
                    </style>
                    </head>
                    <body>';
        //ca
        if (strlen($header) > 0) {
            $texto .= ' <div id="header">' . $header . '</div>';
        }
        //Rodapé
        if (strlen($footer) > 0) {
            $texto .= ' <div id="footer">' . $footer . '</div>';
        }
        //Conteudo

        if ($tipo == 'odt') {
            $texto .= $detalha . '
                </body>
            </html>';
        } else {
            $texto .= '<div id="content">' . $detalha . '</div>
                </body>
            </html>';
        }

        return $texto;
    }
    //--------------------------------------------------------------------------------
    private function getOptions(array $options, $default)
    {
        if (count($options) == 0) {
            return (object) $default;
        }

        return (object) array_merge($default, $options);
    }
}
