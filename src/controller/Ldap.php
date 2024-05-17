<?php

namespace NZord\Controller;

use Modulos\System\Models\Parametro;
use NZord\Exceptions\LdapException;

class Ldap
{
    private $server;
    private $ldap;

    private $domain;
    private $dnAD;

    
    public function __construct()
    {
        $dados = Parametro::where('nome', '=', 'userAccesAD')->first();

        if (!$dados) {
            throw new LdapException('Não foi encontrado parâmetros de configurações do AD. Verifique!');
        }

        $dados  = $dados->valor;
        $server = json_decode($dados, true);
        foreach ($server as $valor) {
            $this->server[] = array(
                'server' => $valor['server'], 'domain' => $valor['domain'],
            );
        }
    }
    
    public function escolheAD($nusuario, $pass)
    {
        $server = $this->server;

        foreach ($server as $valor) {
            if ($this->connectServerAD($valor['server'], $valor['domain'] . '.LOCAL', $nusuario, $pass) == true) {
                $this->domain = $valor['domain'];

                $this->setDomain($this->domain);
                return $this->domain;
            }
        }
        return false;
    }

    private function parseExentedLdapErrorCode($message)
    {
        $code = null;
        if (preg_match("/(?<=data\s).*?(?=\,)/", $message, $code)) {
            return $code[0];
        }
        return null;
    }
 
    private function getErrorsBind($code)
    {
        switch ($code) {
            case '525':
                return 'AD: Usuário não encontrado!';
            case '52e':
                return 'AD: Usuário ou senha incorreta!';
            case '530':
                return 'AD: Não é permitido fazer logon neste momento!';
            case '531':
                return 'Não é permitido fazer logon nesta estação de trabalho!';
            case '532':
                return 'A senha da conta AD especificada expirou!';
            case '533':
                return 'Conta AD atualmente desabilitada.';
            case '534':
                return 'A conta AD do usuário expirou!';
            case '701':
                return 'A conta AD do usuário expirou!';
            case '773':
                return 'A senha do usuário AD deve ser alterada antes de efetuar login pela primeira vez.';
            case '775':
                return 'A conta AD referenciada está atualmente bloqueada e pode não estar conectada';
            default:
                return 'Ocorreu erro ao buscar usuário no AD. Tente novamente!';
        }
    }
    
    public function connectServerAD($adServer, $dominio, $usuario, $senha)
    {
        $this->ldap = ldap_connect($adServer);

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

        $bindCheck = @ldap_bind($this->ldap, $usuario . '@' . $dominio, $senha);
        if($bindCheck) return true;

        if (ldap_get_option($this->ldap, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
            $erroCode = $this->parseExentedLdapErrorCode($extended_error);

            throw new LdapException($this->getErrorsBind($erroCode));
        } else {
            throw new LdapException('Ocorreu erro ao buscar usuário no AD. Tente novamente!');
        }
    }

    public function getInfoUser($user)
    {
        $result = ['displayname' => null];

        if ($this->ldap) {

            $searchResults = ldap_search($this->ldap, $this->dnAD, '(|(samaccountname=' . $user . '))');
            if (count(ldap_get_entries($this->ldap, $searchResults)) > 1) {
                $object = ldap_get_entries($this->ldap, $searchResults);
                $user   = $object[0];

                if (count($user['displayname'])) {
                    $result['displayname'] = $user['displayname'][0];
                }
                return $result;
            }
        }

        return $result;
    }
    
    public function setDomain($domain)
    {
        //$this->domain = '@'.$domain.'.local';
        if ($domain == 'PMLRV') {
            $this->dnAD = 'ou=PREFEITURA,dc=' . $domain . ',dc=LOCAL';
        } else {
            $this->dnAD = 'dc=' . $domain . ',dc=LOCAL';
        }
    }
}
