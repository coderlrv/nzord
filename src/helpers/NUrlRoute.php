<?php
namespace NZord\Helpers;
use \Slim\Http\Request;

class NUrlRoute
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Retorna url do projeto.
     *
     * @return string
     */
    public function base()
    {
        $urlBase = $this->request->getUri();

        return $urlBase->getBaseUrl();
    }
    /**
     * Retorna url atual completa com query string
     *
     * @return void
     */
    public function currentUrl(){
        return (string) $this->request->getUri();
    }
    
    /**
     * Retornar url atual sem query string
     *
     * @return string
     */
    public function currentPath()
    {
        $uri = $this->request->getUri();
        return $uri->getScheme().'://'.$uri->getHost().$uri->getPath();
    }
    /**
     *  Busca url anterior
     *
     * @return string
     */
    public function previous()
    {
        $referrer = $this->request->headers->get('referer');
        return $referrer ?: $this->base();
    }

    /**
     * Gera url para rotas de modals
     *
     * @param string $modulo - Nome modulo
     * @param string $controler - Nome controller
     * @param string $action  - Nome ação dentro controller
     * @param array $args - Argumentos passado após actions Ex:  /minha-actions/args1/args2/....
     * @param array $queryParams - Parametros de url chave e falor  Ex; ?param1=232&param2=343
     * @param boolean $absolute - Juntar ao url do projeto.
     * @return string
     */
    public function to($modulo, $controler = '', $action = '', $args = [], $queryParams = [], $absolute = true)
    {
        $url = ($absolute ? $this->base() : ''). '/app/' . $this->camel2dashed($modulo);

        if (!empty($controler)) {
            $url .= '/' . $this->camel2dashed($controler);
            //Verifica se enviarao action
            if(!empty($action)){
                $url.= '/' . $this->camel2dashed($action);
            }

            if($args){
                foreach ($args as $arg) {
                    $url .= '/' . $arg;
                }
            }
        }

        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
    /**
     * Gera url para rotas de modals
     *
     * @param string $modulo - Nome modulo
     * @param string $controler - Nome controller
     * @param string $action  - Nome ação dentro controller
     * @param array $args - Argumentos passado após actions Ex:  /minha-actions/args1/args2/....
     * @param array $queryParams - Parametros de url chave e falor  Ex; ?param1=232&param2=343
     * @param boolean $absolute - Juntar ao url do projeto.
     * @return string
     */
    public function toModal($modulo, $controler, $action, $args = [], $queryParams = [], $absolute = true)
    {
        $url = ($absolute ? $this->base() : ''). '/modal?p=app/' . $modulo . '/' . $controler . '/' . $action;

        //params
        if (count($args) > 0) {
            foreach ($args as $arg) {
                $url .= '/' . $arg;
            }
        }

        //Query params
        if (count($queryParams) > 0) {
            $url .= '&' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Determine se o caminho fornecido é um URL válido.
     *
     * @param  string  $path
     * @return bool
     */
    public function isValidUrl($path)
    {
        if (starts_with($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://'])) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    private function camel2dashed($className)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $className));
    }
}
