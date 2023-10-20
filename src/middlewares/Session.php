<?php
namespace NZord\Middlewares;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as Slim;

class Session {
    protected $settings = [
        'name'           => 'slim_session',
        'lifetime'       => 24,
        'path'           => '/',
        'domain'         => null,
        'secure'         => false,
        'httponly'       => true,
        'cookie_autoset' => true,
        'save_path'      => null,
        'cache_limiter'  => 'nocache',
        'autorefresh'    => false,
        'encryption_key' => null,
        'namespace'      => 'slim_app'
    ];
    
    public function __construct(array $settings = []){
        $this->settings = array_merge($this->settings, $settings);
    }

    public function __invoke(Request $request, Response $response, callable $next){
        if ($this->settings['cookie_autoset'] === true) {
            $this->settings['path']   = $request->getUri()->getBasePath() . '/';
            $this->settings['domain'] = $request->getUri()->getHost();
            $this->settings['secure'] = $request->getUri()->getScheme() === 'https' ? true : false;
        }

        $this->start($request);
        return $next($request, $response);
    }
    
    protected function start(Request $request){

       
        $settings = $this->settings;
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        if (is_string($settings['lifetime'])) {
            $settings['lifetime'] = strtotime($settings['lifetime']) - time();
        } else {
            $settings['lifetime'] *= 60;
        }
        if ($settings['lifetime'] > 0) {
            ini_set('session.gc_maxlifetime', $settings['lifetime']);
        }
        if (is_string($settings['save_path'])) {
            if (!is_writable($settings['save_path'])) {
                throw new RuntimeException('O caminho de salvamento da sessão não é gravável.');
            }
            ini_set('session.save_path', $settings['save_path']);
        }
        if (version_compare(PHP_VERSION, '7.1', '<')) {
            ini_set('session.entropy_file', '/dev/urandom');
            ini_set('session.entropy_length', 128);
            ini_set('session.hash_function', 'sha512');
        } else {
            ini_set('session.sid_length', 128);
        }

        session_cache_limiter($settings['cache_limiter']);
        session_name($settings['name']);
        session_set_cookie_params(
            $settings['lifetime'],
            $settings['path'],
            $settings['domain'],
            $settings['secure'],
            $settings['httponly']
        );

        if (is_string($settings['encryption_key'])) {
            $settings['encryption_key'] .= md5($request->getHeaderLine('HTTP_USER_AGENT'));
            $handler = new \NZord\SecureSessionHandler($settings['encryption_key']);
            session_set_save_handler($handler, true);
        }
       
        if ($settings['autorefresh'] === true && isset($_COOKIE[$settings['name']])) {
            setcookie(
                $settings['name'],
                $_COOKIE[$settings['name']],
                time() + $settings['lifetime'],
                $settings['path'],
                $settings['domain'],
                $settings['secure'],
                $settings['httponly']
            );
        }
    }
}