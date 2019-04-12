<?php
namespace NZord\Controller;

use Modulos\System\Models\Parametro;
/**
 * Class ActiveDirectory
 * @author Gabriel Heming <gabriel_heming@hotmail.com>
 * 
 */ 
class ActiveDirectory {
    
    private $ldap;
    private $ldapBind;
    private $serverAD;
    private $userAD;
    private $domain;
    private $passwordAD;
    private $dnAD;
    private $protocolVersion = 3;
    //--------------------------------------------------------------------------------
    public function __construct($domain='PMLRV') {
        $dados = Parametro::where('nome', 'userAccesAD')->first();
        $server = json_decode($dados['valor'],true);
        
        foreach($server as $valor ){
            if( $valor['domain'] == $domain ){
                $this->setDomain($valor['domain']);
                $this->setServerAD($valor['server']);
                $this->setUser($valor['user']);
                $this->setPass($valor['pass']);
            }
        }
        $this->connect();
    }
    //--------------------------------------------------------------------------------
    public function setServerAD( $server ){
    	$this->serverAD = 'ldap://'.$server;
    }
    //--------------------------------------------------------------------------------    
    public function setUser( $user ){
    	$this->userAD = $user;
    }
    //--------------------------------------------------------------------------------
    public function setDomain( $domain = 'PMLRV' ){
    	$this->domain = '@'.$domain.'.local';
        $this->dnAD = 'dc='.$domain.',dc=LOCAL';
    }
    //--------------------------------------------------------------------------------
    public function setPass( $pass ){
    	$this->passwordAD = $pass;
    }
    //--------------------------------------------------------------------------------
    private function connect() {
    	$ldap = ldap_connect($this->serverAD) or die('não foi possível conectar');
    	$this->ldap = $ldap;
    	$this->ldapBind = false;
    	
        if($this->ldapBind === false) {        
            $this->ldapBind = $this->ldap;
            if ( !ldap_set_option($this->ldapBind, LDAP_OPT_PROTOCOL_VERSION, $this->protocolVersion ) ) {
                exit( 'Falha em definir protocolo na versao '.$this->protocolVersion );
            }
            ldap_set_option($this->ldapBind, LDAP_OPT_REFERRALS, 0 );
            ldap_set_option($ldap, LDAP_OPT_SIZELIMIT, 0); 
            ldap_bind($this->ldapBind);
            if ( ldap_errno($this->ldapBind) !== 0 ) {
                exit('Nao foi possivel conectar no servidor');
            }
            ldap_bind($this->ldapBind , $this->userAD.$this->domain , $this->passwordAD);
            if (ldap_errno($this->ldapBind) !== 0) {
                return false;
            }   
        }
        return true;
    }
    //--------------------------------------------------------------------------------
    public function isUser($user) {        
        if($this->connect() === true) {
            $searchResults = ldap_search($this->ldapBind, $this->dnAD, '(|(samaccountname='.$usuario.'))');
            if (count(ldap_get_entries($this->ldapBind , $searchResults)) > 1) {
                return true; 
            } 
        }
        return false;
    }
    //--------------------------------------------------------------------------------    
    public function authUser($user , $password) {   
        if (strlen($senha) > 0) {
            $bind = ldap_bind($this->ldap , $user.$this->domain , $password);
            if ($bind) {
                return true;
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------------    
    public function getUser($user) {    
        if($this->connect() === true) {
            $searchResults = ldap_search($this->ldapBind , $this->dnAD , '(|(samaccountname='.$user.'))');    
            // Lê a quantidade maxima de dias para troca de senha setada para o dominio  
            $sr = ldap_read($this->ldapBind, $this->dnAD, 'objectclass=*', array('maxPwdAge'));
            $info = ldap_get_entries($this->ldapBind, $sr);
            $maxpwdage = $info[0]['maxpwdage'][0];
            
            if (count(ldap_get_entries($this->ldapBind , $searchResults)) > 1) {
                ldap_sort($this->ldapBind,$searchResults,"sn");
                $object = ldap_get_entries($this->ldapBind , $searchResults);
                $user = $object[0];
                $user['maxpwdage'] = $maxpwdage;
                return $user; 
            }
        }
        return false;
    }
    //--------------------------------------------------------------------------------
    public function getOU( $grupo=null ) {
        $dnAD = Parametro::get('ouADPadrao');
        if($this->connect() === true) {            
            $dnAD = ( $grupo ) ? $grupo: $dnAD;
            $filter ="(ou=*)";
            $justthese = array('ou');
            $result = ldap_list($this->ldapBind, $dnAD, $filter, $justthese) or die("No search data found."); 
            $info = ldap_get_entries($this->ldapBind, $result);
            for ($i=0; $i < $info["count"]; $i++) {
                $grup[] = $info[$i]["ou"][0];
            }
            return $grup;
        }
        return false;
    }
    //--------------------------------------------------------------------------------
}