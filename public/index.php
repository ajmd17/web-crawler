<?php
    error_reporting(E_ERROR | E_PARSE);

    use Phalcon\Di\FactoryDefault;
    use Phalcon\Loader;
    use Phalcon\Mvc\View;
    use Phalcon\Mvc\Application;
    use Phalcon\Url;
    

    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');

    $loader = new Loader();

    $loader->registerDirs(
        [
            APP_PATH . '/controllers/',
            APP_PATH . '/models/',
            APP_PATH . '/providers/',
            APP_PATH . '/plugins/'
        ]
    );

    $loader->register();

    $container = new FactoryDefault();

    $container->set(
        'view',
        function () {
            $view = new View();
            $view->setViewsDir(APP_PATH . '/views/');
            return $view;
        }
    );

    $container->set(
        'url',
        function () {
            $url = new Url();
            $url->setBaseUri('/');
            return $url;
        }
    );

    $application = new Application($container);


    $providers = [
        'CrawlerProvider'
    ];

    foreach ($providers as $providerClass) {
        // require_once();
        // echo(APP_PATH . '/providers/' . $providerClass . 'Provider.php');
        $provider = new $providerClass;
        $provider->register($container);
    }

    try {
        // Handle the request
        $response = $application->handle(
            $_SERVER["REQUEST_URI"]
        );

        $response->send();
    } catch (\Exception $e) {
        echo 'Exception: ', $e->getMessage();
    }
?>