<?php
namespace NZord\Helpers\TwigExtensions;

class TwigExtensionCustom extends \Twig_Extension
{
    /**
     * @var string|\Slim\Http\Uri
     */
    private $uri;
    private $app;

    public function __construct($uri,$app)
    {
        $this->uri = $uri;
        $this->app = $app;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path_for_model', [$this, 'pathForModel'],['is_safe' => ['html']]),
            new \Twig_SimpleFunction('path_for_modal', [$this, 'pathForModal'],['is_safe' => ['html']]),
            new \Twig_SimpleFunction('path_for_report', array($this, 'pathForReport')),
            new \Twig_SimpleFunction('file_public', array($this, 'filePublic')),
            new \Twig_SimpleFunction('file_dir', array($this, 'fileDir'))
        ];
    }
    /**
     *  Gerar link para os modulos.
     *
     * @param string $model - Nome modulo
     * @param string $controler - Nome arquivo controler
     * @param string $action - Nome ação a se chamada ou chama index.
     * @param array $args - Agumentos para url. Ex: .../index/{arg1}/{arg2}/{arg3}....
     * @param array $queryParams - Lista de paramentros nomeados. Ex: [ 'meuParamentro'=> 2 ]
     * @return void
     */
    public function pathForModel($model,$controler=null,$action='index', $args = [], $queryParams = []){
        $url =  $this->baseUrl().'/app/'.$model;

        if(isset($controler)){
            $url .= '/'.$controler.'/'.$action;

            foreach($args as $arg){
                $url .= '/'.$arg;
            }
        }
        
        if(count($queryParams) > 0) $url .= '?'.http_build_query($queryParams);
        
        return $url;
    }
    
    /**
     *  Gerar link para os modulos.
     *
     * @param string $model - Nome modulo.
     * @param string $controler - Nome arquivo controler.
     * @param string $action - Nome ação a se chamada ou chama index.
     * @param array $args - Agumentos para url. Ex: .../index/{arg1}/{arg2}/{arg3}....
     * @param array $queryParams - Lista de paramentros nomeados. Ex: [ 'meuParamentro'=> 2 ]
     * @return void
     */
    public function pathForModal($model,$controler,$action='index', $args = [], $queryParams = [])
    {
        $url = $this->baseUrl().'/modal?p=app/'.$model.'/'.$controler.'/'.$action;
        //params
        if(count($args) > 0){
            foreach($args as $arg){
                $url .= '/'.$arg;
            }
        }
        //Query params
        if(count($queryParams) > 0){
            $url .= '&'.http_build_query($queryParams);
        }
        
        return $url;
    }
    public function pathForReport($chaveReport,$args = null,$queryParams = [],$type='pdf',$fullPath=false)
    {
        $url =  ($fullPath ? $this->baseUrl() : '').'/report/'.$type.'/'.$chaveReport;
        if($args){
            $url .= $args;
        }
        if(count($queryParams)> 0) $url .= '?'.http_build_query($queryParams);
        
        return $url;
    }
    /**
     *  Url base
     *  ex: https://localhost/
     * 
     * @return string
     */
    public function baseUrl()
    {
        if (is_string($this->uri)) {
            return $this->uri;
        }
        if (method_exists($this->uri, 'getBaseUrl')) {
            return $this->uri->getBaseUrl();
        }
    }
    /**
     *  Retorna o caminho publico para arquivo.
     *
     * @param string $path
     * @return string
     */
    public function filePublic($path){
        return $this->baseUrl().'/files'.$path;
    }
    /**
     *  Retorna o caminho publico para arquivo.
     *
     * @param string $path
     * @return string
     */
    public function fileDir($path){
        return $this->app->settings['files']['pathPublic'].$path;
    }
}
