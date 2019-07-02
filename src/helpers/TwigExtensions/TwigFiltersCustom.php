<?php
namespace NZord\Helpers\TwigExtensions;

class TwigFiltersCustom extends \Twig_Extension
{
    public function __construct(){
    }

    public function getFunctions(){
        return [];
    }

    public function getFilters(){
        return [
            new \Twig_SimpleFilter('cpfCnpj', [ $this, 'cpfCnpjFilter' ])
        ];
    }

    public function cpfCnpjFilter($value){
        $valueLimpo = preg_replace("/[' '-.\/]/",'', $value);
    
        if(empty($valueLimpo)) return '';
        
        if(strlen($valueLimpo) > 12){
            return $this->mask($valueLimpo,'##.###.###/####-##');
        }else{
            return $this->mask($valueLimpo,'###.###.###-##');
        }
    }

    private function mask($val, $mask){
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++){
            if($mask[$i] == '#'){
                if(isset($val[$k]))
                    $maskared .= $val[$k++];
            }else{
                if(isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }

        return $maskared;
    }
}