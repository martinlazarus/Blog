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
//$app->config('debug', true);
        
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
    
    $args = [
                'posts' => $result,
                'title' => "Posts",
                'another' => "variable"
            ];
    
    return $this->view->render($response, 'posts.html', $args);
});

$app->post("/post", function(Request $request, Response $response) {
    
    if (
            $request->getParam("title") != null && 
            $request->getParam("content") != null && 
            $request->getParam("created_at") != null && 
            $request->getParam("updated_at") != null
        )
    {
    
        $title = $request->getParam("title");
        $content = $request->getParam("content");
        $created_at = $request->getParam("created_at");
        $updated_at = $request->getParam("updated_at");

        /* @var $db PDO */
        $db = $this->db;

        try
        {
            $stmp = $db->prepare("INSERT INTO Post VALUES(DEFAULT, :title, :content, :created_at, :updated_at)");
            $stmp->bindParam('title', $title);
            $stmp->bindParam('content', $content);
            $stmp->bindParam('created_at', $created_at);
            $stmp->bindParam('updated_at', $updated_at);
            $stmp->execute();
            $args = ['message' => 'Post created successfully!'];
        } 
        catch(Exception $e)
        {
            $args = ['message' => 'Sorry, there was an error. Please try again.'];
        }
    }
    else
    {
        $args = ['message' => 'Please check your request and try again.'];
    }
    return $this->view->render($response, 'post.html.twig', $args);
});

$app->delete('/post/{id}', function(Request $request, Response $response, $args){
    if (array_key_exists('id', $args))
    {
        if (is_numeric($args['id']) == 1)
        {
            /* @var $db PDO */
            $db = $this->db;
            
            $stmp = $db->prepare("DELETE FROM Post WHERE PostId = :id");
            $stmp->bindParam('id', $args['id']);
            $stmp->execute();
            $rowsEffected = $stmp->rowCount();
            if($rowsEffected)
            {
                $args = ['message' => 'Post has been deleted successfully'];
            }
            else 
            {
                $args = ['message' => 'This post does not exist, please try another post id'];
            }
        }
        else
        {
            $args = ['message' => 'You have entered a value that is not a number. Please try again' ];
        }
    }
    else
    {
        $args = ['message' => 'Please check your request and try again'];
    }
    return $this->view->render($response, 'post.html.twig', $args);
});

$app->get('/newpost', function(Request $request, Response $response, $args) {
    
    return $this->view->render($response, "newpost.html.twig", $args);
});

$app->run();

