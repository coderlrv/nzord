<?php
namespace NZord\Controller;

use NZord\Controller\Dot;

if ( !function_exists('dot') ) { 
    function dot($items){
        return new Dot($items);
    }
}

class Session extends dot{
    protected $namespace = 'slim_app';
    
    public function __construct($namespace = null){
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
        $this->setNamespace($this->namespace);
    }
    
    public function setNamespace($namespace){
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = [];
        }
        $this->setReference($_SESSION[$namespace]);
        $this->namespace = $namespace;
    }
    
    public function getNamespace(){
        return $this->namespace;
    }
    
    public function deleteNamespace($namespace){
        if (isset($_SESSION[$namespace])) {
            unset($_SESSION[$namespace]);
        }
    }
    
    public function setTo($namespace, $key, $value = null){
        $oldNamespace = $this->namespace;
        $this->setNamespace($namespace);
        $this->set($key, $value);
        $this->setNamespace($oldNamespace);
    }
    
    public function addTo($namespace, $key, $value = null){
        $oldNamespace = $this->namespace;
        $this->setNamespace($namespace);
        $this->add($key, $value);
        $this->setNamespace($oldNamespace);
    }
    
    public function getFrom($namespace, $key = null, $default = null){
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $result = $this->get($key, $default);
            $this->setNamespace($oldNamespace);
            return $result;
        }
        return $default;
    }
    
    public function hasIn($namespace, $key){
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $result = $this->has($key);
            $this->setNamespace($oldNamespace);
            return $result;
        }
    }
    
    public function deleteFrom($namespace, $key){
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $this->delete($key);
            $this->setNamespace($oldNamespace);
        }
    }
    
    public function clearFrom($namespace, $key = null){
        $oldNamespace = $this->namespace;
        $this->setNamespace($namespace);
        $this->clear($key);
        $this->setNamespace($oldNamespace);
    }
    
    public static function isActive(){
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    public static function key(){
        return session_id();
    }
    
    public static function regenerateId($deleteOld = true){
        if (self::isActive()) {
            session_regenerate_id($deleteOld);
        }
    }

    public static function destroy(){
        if ( self::isActive() ) {
            $_SESSION = [];
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
                );
            session_destroy();
        }
       return false;
    }
    
    public function check () {
        return isset($_SESSION['user']);
    }
}