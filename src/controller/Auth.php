<?php
namespace NZord\Controller;

use Modulos\System\Models\Parametro;
use Modulos\System\Models\Usuario;
use Slim\Http\Request;
use Slim\Http\Response;
use Modulos\System\Models\LogLogin;

/**
 * Controller de Login / Logout do sistema.
 */
class AuthController extends Controller
{
    /**
     *  Envia para pagina de login.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     */
    public function login(Request $request, Response $response, $args)
    {  
      
        if( @$_COOKIE['PHPSESSID'] == null ){
            session_start();
        } 
        if (!$this->session->has('isLoggedIn')) {
            $this->app->flash->addMessageNow('warning', 'Necessário Logar!');
            $response = $this->view->render($response, 'login.html.twig');
        } else {
            $response = $response->withRedirect($this->router->pathFor('index'));
        }

        return $response;
    }

    /**
     *  Gera sessão do usuário enviado por paramentro
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     */
    public function postLogin(Request $request, Response $response, $args)
    {   
        $data = $request->getParsedBody();
        $bind = null;

        $this->session->set('pg', 'PostLogin');
    
        // Configuracao para realizar authenticação via AD.
        $useAD = $this->app->settings['auth']['useAD'];
        
        if($useAD){
            $ad   = new Ldap();
            $bind = $ad->escolheAD($data['username'], $data['password']);
        }

        $auth = new Login($this->app);

        if ($bind && $useAD) {
            $user = Usuario::where('login', '=', $data['username'])->first();

            //Criar usuário no sistema - primeiro acesso
            if (!$user) {
                $userAd = $ad->getInfoUser($data['username']);
                
                //Criar novo usuario.
                $novoUsuario = $this->createUser($data['username'],$userAd['displayname'],$data['password']);
                
                //Abre sessao para usuario.
                $auth->abreSessao($novoUsuario->id, $bind);
                
                LogLogin::add($data['username'], $request->getServerParam('REMOTE_ADDR'), 'Usuário Criado no Banco pelo AD', 1);
                return $response->withRedirect($this->router->pathFor('index'));
            }

            //Usuário inativo.
            if ($user->status != 'A') {
                LogLogin::add($data['username'], $request->getServerParam('REMOTE_ADDR'), 'Usuário inativo!', 2);
                $this->app->flash->addMessageNow('error', 'Usuário se encontra inativo. Entre contato com suporte!');

                return $this->redirectLogin();
            } else {

                //Grava sessao.
                $auth->abreSessao($user->id, $bind);

                //Atualiza senha usuario
                $user->senha = md5($data['password']);
                $user->save();
            
                LogLogin::add($data['username'], $request->getServerParam('REMOTE_ADDR'), 'Logou pelo AD!', 1);
                return $response->withRedirect($this->router->pathFor('index'));
            }

        } else {
            $user = Usuario::where('login', $data['username'])
                            ->where('senha', md5($data['password']))
                            ->where('status', 'A');


            // Se possui AD em funcionamento ,verificar se o perfil esta habilitado para acesso externo.
            if($useAD){
                $perfLog = Parametro::get('perfLogInterno', function ($value) { 
                                            return isset($value)? explode(',', $value) : [];
                                        });

                $user->whereIn('perfil', $perfLog);
            }

            $user = $user->first();
            if ($user) {
                //Grava sessao.
                $auth->abreSessao($user->id);
                LogLogin::add($data['username'], $request->getServerParam('REMOTE_ADDR'), 'Logou Internamente!', 1);

                return $response->withRedirect($this->router->pathFor('index'));

            } else {
                LogLogin::add($data['username'], $request->getServerParam('REMOTE_ADDR'), 'Pass: ' . $data['password'] . ' | Não logou!', 2);

                $this->app->flash->addMessageNow('error', 'Usuário ou senha incorreta. Verifique!');

                return $this->redirectLogin(['usuario' => $data['username']]);
            }
        }
    }

    /**
     * Realizar logout da sessão
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     */
    public function logout(Request $request, Response $response, $args)
    {
        Login::fechaSessao();

        //Limpar Sessão do Coockie do navegador
        $this->session->clear();
        $this->session->destroy();

        $this->app->flash->addMessageNow('success', 'Logout com sucesso!');

        return $response->withRedirect($this->router->pathFor('login'));
    }
    /**
     * redireciona para pagina login
     *
     * @param array $data
     * @return void
     */
    private function redirectLogin($data = [])
    {
        $this->session->set('isLoggedIn', false);
        $this->session->clear();
        $this->session->destroy();
        $this->view->render($this->app->response, 'login.html.twig', ['login' => $data]);
    }

    /**
     * Criar novo usuario
     *
     * @param string $login
     * @param string $nome
     * @param string $senha
     * @return Modulos\System\Models\Usuario
     */
    private function createUser($login, $nome, $senha)
    {
        $usuario         = new Usuario();
        $usuario->login  = $login;
        $usuario->nome   = isset($nome) && !empty($nome) ? $nome : $login;
        $usuario->senha  = md5($senha);
        $usuario->status = 'A';
        $usuario->perfil = 3;
        $usuario->setor  = 0;
        $usuario->avatar = 'files/avatar/user.png';
        $usuario->save();

        return $usuario;
    }
}
