<?php
/**
 * settings base/settings.php
 * Arquivo de configurações com informações de configuração do Slim e Plugins
 */
return [
	'settings' => [
	    'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
		'app' => [
		    'base_url'  => 'http://localhost',
		    'asset_path'=> __DIR__ . '/resources',
		    'error_log' => __DIR__ . "/logs/error.log",
			'language' 	=> "pt_BR",
		    'version' 	=> '3.1.0'
		],
		'displayErrorDetails' => true, // set to false in production
        'debug'          => true,
        'mode'           => 'test',
		'logger' => [
			'name' => 'nzord',
			'path' => __DIR__ . '/tmp/logs/' . date('Y-m-d') . '.log',
			'path_tmp' => __DIR__ . '/tmp',
		],
		'render' => [
		    'template_path' => __DIR__ . '/templates/',
		    'cache_path' =>  __DIR__ . '/tmp/cache/'
		],
        'db' => [
            'driver'    => 'mysql',
            'host'      => '192.168.173.25',
			'database'  => 'bd_gci5',
			//'database'  => 'bdgci',
            'username'  => 'root',
            'password'  => 'klr258',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
		'session' => [
			'name' => 'gci',
			'lifetime' => 240,
			'path' => '/',
			'domain' => null,
			'secure' => false,
		    'httponly' => true,
		    'cookie_autoset' => true,
		    'save_path'      => __DIR__ . '/tmp/sessions',
		    'cache_limiter'  => 'nocache',
		    'autorefresh'    => false,
		    'encryption_key' => null,
		    'filesPath' => __DIR__ . '/tmp/sessions',
		    'namespace'      => 'app'
		],
		'view' => [
			'template_path' => __DIR__ . '/templates/',
			'twig' => [
				'cache' => __DIR__ . '/../tmp/cache/twig',
				'cache' => false,
				'debug' => true,
				'auto_reload' => true,
			],
		],
		'auth' => [
			'useAD' => true
		],
		'files' => [
			'path' =>__DIR__.'/../files/',
			'pathPublic' => __DIR__.'/../public/files',
			'pathPublicImages' => __DIR__.'/../public/files/uploads/images',
			'uriPublicImages' => '/files/uploads/images'
		]
    ]
];
