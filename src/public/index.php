<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$container = new \Slim\Container;
$container["config"] = 
    ["db" => 
        [
            'host'      => 'localhost',
            'user'      => 'root',
            'pass'      => '',
            'dbname'    => 'Blog'
        ]
];

$container["db"] = function ($c)
{
    $db = $c["config"]["db"];
    $pdo = new PDO("mysql:host=" . $db["host"] . ";dbname=" . $db["dbname"] , $db["user"], $db["pass"]);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container["view"] = function ($container)
{
    $view = new \Slim\Views\Twig(__DIR__.'/views/', [
        'cache' =>false
    ]);
    
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$app = new \Slim\App($container);
        
$app->get('/', function(Request $request, Response $response) {
    $response->getBody()->write("Hello World");
    
    return $response;
});

$app->get('/posts', function(Request $request, Response $response, $args) {
    /* @var $db PDO */
    $db = $this->db;
    $statement = $db->query("SELECT * FROM Post");
    $statement->execute();
    $result = $statement->fetchAll();

    return $this->view->render($response, 'posts.html', [
                                                            'posts' => $result,
                                                            'title' => "Posts"
                                                        ]
                              );
});

$app->run();

