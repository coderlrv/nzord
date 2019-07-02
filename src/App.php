<?php
/**
 * app base/app.php
 */

namespace NZord;

class App extends \Slim\App{
    public function __construct($settings){
        parent::__construct($settings);

        // New Slim app
        $app = $this;

        //Chama rotas e dependencias
        
        require 'dependencies.php';
        require 'routes.php';
        require 'middleware.php';
        
    }
}
