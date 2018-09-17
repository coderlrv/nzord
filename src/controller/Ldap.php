<?php
namespace NZord\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Modulos\System\Models\Parametro;

class Ldap{
    
    private $server;
    private $ldap;

    private $domain;
    private $dnAD; 

    //--------------------------------------------------------------------------------
    public function __construct(){
        $dados = Parametro::where('nome','=','userAccesAD')->first();
        $dados = $dados->valor;
        $server = json_decode($dados,true);
        foreach($server as $valor ){
            $this->server[] = array(
                'server'=>$valor['server']
                ,'domain'=>$valor['domain']
            );
        }
    }
    //--------------------------------------------------------------------------------
    public function escolheAD($nusuario,$pass){
        $server = $this->server;
        foreach($server as $valor ){
            if( $this->connectServerAD($valor['server'],$valor['domain'].'.LOCAL',$nusuario,$pass) == true ){
                $this->domain = $valor['domain'];

                $this->setDomain($this->domain);
                return  $this->domain;
            }
        }
        return false;
    }    
    //--------------------------------------------------------------------------------
    public function connectServerAD($adServer,$dominio,$usuario,$senha){
        $this->ldap = ldap_connect($adServer);
       
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        
        $ver = @ldap_bind($this->ldap, $usuario.'@'.$dominio, $senha);
        if( $ver ){
            return true;
        }else {
            return null;
        }
    }

    public function getInfoUser($user){
        $result = ['displayname' => null];

        if($this->ldap) {
            $searchResults = ldap_search($this->ldap , $this->dnAD , '(|(samaccountname='.$user.'))');
            if (count(ldap_get_entries($this->ldap , $searchResults)) > 1) {
                $object = ldap_get_entries($this->ldap , $searchResults);
                $user = $object[0];
                
                if(count($user['displayname'])){
                    $result['displayname'] = $user['displayname'][0];
                }
                return  $result;
            }

        }

        return $result;
    }

    public function setDomain( $domain ){
    	//$this->domain = '@'.$domain.'.local';
    	if( $domain == 'PMLRV' ){
    		$this->dnAD = 'ou=PREFEITURA,dc='.$domain.',dc=LOCAL';
    	}else{
    		$this->dnAD = 'dc='.$domain.',dc=LOCAL';
    	}
    }
}