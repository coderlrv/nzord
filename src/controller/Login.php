<?php
namespace NZord\Controller;

use Modulos\System\Models\Sessao;
use Modulos\System\Models\Usuario;
use Modulos\System\Models\ModAcesso;
use Slim\Http\Request;
use Slim\Http\Response;
use NZord\Middlewares\Auth;

class Login
{
    protected $app;
    protected $session;
    protected $request;

    public function __construct($app)
    {
        $this->app     = $app;
        $this->session = $app->session;
        $this->request = $this->app->request;
    }
    //--------------------------------------------------------------------------------
    public function abreSessao($idUser, $bind=null)
    {
        $user       = Usuario::find($idUser);
        if(!$user){
            return false;
        }
        $uConfig   = \Modulos\System\Models\UserConfig::find($idUser);
        //$phpsessid = $this->request->getCookieParam('PHPSESSID');
        $phpsessid = $_COOKIE['PHPSESSID'];

        //Busca sessao atual
        $sessaook = Sessao::where('session_id', $phpsessid)->where('status', 'A')->orderBy('id', 'desc')->first();
        $sessaoOp = Sessao::where('session_id', '<>', $phpsessid)
            ->where('user_id', $user->id)
            ->where('status', 'A')
            ->first();

        //Fecha sessão
        if ($sessaoOp) {
            $sessaoOp->status     = 'E';
            $sessaoOp->data_encer = date('Y-m-d H:i:s');
            $sessaoOp->save();
            unset($sessaoOp);
        }

   
        if ($sessaook) {
            //Seta na sessao infomações.
            $this->setSessao($user, $sessaook->id, $bind, $uConfig);

            return $sessaook->id;
        } else {
            try {
                $sessao     = new Sessao();
                $dataAcesso = date('Y-m-d H:i:s');
                $sessao->session_id = $phpsessid;
                $sessao->url        = (string) $this->request->getUri();
                $sessao->browser    = $this->request->getHeader('HTTP_USER_AGENT')[0];
                $sessao->data       = $dataAcesso;
                $sessao->tipo       = $bind;
                $sessao->dpto       = $user->dpto;
                $sessao->setor      = $user->setor;
                $sessao->user_id    = $user->id;
                $sessao->perfil     = $user->perfil;
                $sessao->remo_ip    = $this->request->getServerParam('REMOTE_ADDR');
                $sessao->status     = 'A';

                $sessao->save();
            } catch (\Exception $ex) {                
                $this->app->flash->addMessageNow('error', 'Problemas na criação de Sessão!');
                header("Refresh:0; url=./public");
                exit;
            }

            //Seta na sessao infomações.
            $this->setSessao($user, $sessao->id, $bind, $uConfig);
            $user->access_at = $dataAcesso;
            $user->save();
            return $sessao->id;
        }
    }
    //--------------------------------------------------------------------------------
    public static function fechaSessao()
    {
        $phpsessid = $_COOKIE['PHPSESSID'];
        $sessao    = Sessao::where('session_id', '=', $phpsessid)->where('status', '=', 'A')->get()->first();
        if ($sessao) {
            $sessao->status     = 'E';
            $sessao->data_encer = date('Y-m-d H:i:s');
            $sessao->save();
            unset($sessao);
        }
        return true;
    }
    //--------------------------------------------------------------------------------
    private function setSessao($user, $sessao, $bind, $config=null)
    {


        $this->session->set('user', $user->id);
        $this->session->set('login', $user->login);
        $this->session->set('userNome', $user->nome);
        $this->session->set('userMatricula', $user->matricula);
        $this->session->set('userDpto', $user->dpto);
        $this->session->set('userSetor', $user->setor);
        $this->session->set('userAvatar', $user->avatar);
        $this->session->set('userPerfil', $user->perfil);
        $this->session->set('userADpto', $user->acesso_dpto);
        $this->session->set('sessiontime', time());
        $this->session->set('bind', $bind);
        $this->session->set('loggedIn', date('Y-m-d H:i:s'));
        $this->session->set('isLoggedIn', true);
        $this->session->set('remoteIp', $this->request->getServerParam('REMOTE_ADDR'));

        $userStatus = Usuario::getUserStatus($user->id);
        if($userStatus){
            $this->session->set('userStatus', $userStatus);
        }
        
        $this->session->set('sessao', $sessao);
        $perfil[] = $user->perfil;
        
        if( $config ){
            $pAdic = $config->perfil;
            $this->session->set('userUrlPadrao', $config->padrao);
            if( $pAdic ){
                array_push( $perfil, $pAdic );
            }
        }
        $perfil  = implode(',',$perfil);
        $perfilArr = explode(',',$perfil);
        $accessMods = ModAcesso::getPermissaoPerfils($perfilArr);

        $this->session->set('userModAccess',$accessMods);
        $this->session->set('userPerfilAdic', $perfil);

        //Usuário tercerizado
        if ($user->perfil == 2) {
            $this->session->set('userEmpresa', $user->empresa);
        }
    }
    //--------------------------------------------------------------------------------
}
