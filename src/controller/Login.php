<?php
namespace NZord\Controller;

use Modulos\System\Models\Sessao;
use Modulos\System\Models\Usuario;
use Slim\Http\Request;
use Slim\Http\Response;

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
    
    public function abreSessao($idUser, $bind)
    {
        $user            = Usuario::find($idUser);
        if(!$user){
            return false;
        }

        $req       = $this->request->getHeaders();
        $phpsessid = $this->request->getCookieParam('PHPSESSID');

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
            $this->setSessao($user, $sessaook->id, $bind);

            return $sessaook->id;
        } else {
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

            //Seta na sessao infomações.
            $this->setSessao($user, $sessao->id, $bind);

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

    private function setSessao($user, $sessao, $bind)
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

        $this->session->set('sessao', $sessao);

        //Usuário tercerizado
        if ($user->perfil == 2) {
            $this->session->set('userEmpresa', $user->empresa);
        }
    }
}
