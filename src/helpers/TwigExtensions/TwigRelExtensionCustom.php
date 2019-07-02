<?php
namespace NZord\Helpers\TwigExtensions;

use Modulos\System\Models\RelModelo;
use Illuminate\Database\Capsule\Manager as DB;

class TwigRelExtensionCustom extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('renderSub', [$this, 'renderSubRel'],['needs_environment' => true,'is_safe' => ['html']])
        ];
    }   
    /**
     *  Renderiza sub relatorio.
     *
     * @param \Twig_Environment $env
     * @param [type] $chave - chave relatorio.
     * @param array $params - objeto de paramentros para sql. Ex: {'id'=> 5}
     * @return void
     */
    public function renderSubRel(\Twig_Environment $env,$chave,$params=[])
    {
        //Busca modelo relatorio.
        $rel = RelModelo::where('chave',$chave)->first();
        if(!$rel){
            $template = $env->createTemplate("<h1> Sub relatório não encontrado. Entre contato com suporte.</h1>");
            return $template->render([]); 
        }

        $result = DB::select($rel->cod_sql,$params);
        //Dados do select
        $dados = count($result) > 0 ? $result : [];

        $template = $env->createTemplate($rel->detalhe);
        return $template->render([ 'dados'=>$dados, 'params'=>$params]); 
    }
}
