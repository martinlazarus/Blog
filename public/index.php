<?php
session_start();

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Blog\Controller as Controller;
use Blog\Repository\Posts;
use Blog\Repository\Categories;
use Blog\Repository\Authors;
use Blog\Database;

require '../vendor/autoload.php';

$container = new \Slim\Container(['settings' => ['displayErrorDetails' => true]]);
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
    return new Blog\Database($c["config"]["db"]);
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container["repo.posts"] = function($c)
{
    return new Posts($c["db"]);
};

$container["repo.categories"] = function($c)
{
    return new Categories($c["db"]);
};

$container["repo.authors"] = function($c)
{
    return new Authors($c["db"]);
};

$container["view"] = function ($container)
{
    $view = new \Slim\Views\Twig(__DIR__.'/../views/', [
        'cache' =>false
    ]);
    
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['controller.home'] = function($container)
{
    return new Controller\HomeController($container['view']);
};

$container['controller.post'] = function($container)
{
    return new \Blog\Controller\PostController(
                                                $container['view'], 
                                                $container['repo.posts'],
                                                $container['repo.categories'],
                                                $container['repo.authors'],
                                                $container['flash']
                                              );
};

$app = new \Slim\App($container);
//$app->config('debug', true);
        
$app->get('/', 'controller.home:hello');

$app->get('/post', 'controller.post:newPost');

$app->get('/posts', 'controller.post:allposts');

$app->get('/post/{PostId}', 'controller.post:getPost');

$app->put('/post/{PostId}', 'controller.post:editPost');

$app->delete('/post/{PostId}', 'controller.post:deletePost');

$app->post('/post', 'controller.post:savePost');

$app->post('/addcategory', function(Request $request, Response $response, $args) { 
    if($request->getParam('category') != null)
    {
        $query = "INSERT INTO Category VALUES (DEFAULT, :category)";
        $params = getPDOParams(['category'], $request);
        dbCreateUpdateDelete($this->db, $query, $params);
        $args = ['message' => 'Category created successfully'];
        return $this->view->render($response, "message.html.twig", $args);
    }
    else
    {
        $args = ['message' => 'Please enter a category and try again'];
        return $this->view->render($response, "message.html.twig", $args);
    }
});

$app->get('/addauthor', function(Request $request, Response $response, $args) { 
    return $this->view->render($response, "addauthor.html.twig", $args);
});

$app->post('/addauthor', function(Request $request, Response $response, $args) {
    if (
            checkValidParams($request, ['displayname', 'firstname', 'lastname'])
       )
    {   
        $query = "INSERT INTO Author VALUES(DEFAULT, :displayname, :firstname, :lastname)";
        $params = getPDOParams(['displayname', 'firstname', 'lastname'], $request);
        dbCreateUpdateDelete($this->db, $query, $params);
        $args = ['message' => 'Account inserted successfully'];
    }
    else
    {
        $args = ['message' => 'Please check your input and try again.'];
    }
    return $this->view->render($response, "message.html.twig", $args); 
});

$app->run();

function dbCreateUpdateDelete(PDO $db, string $query, array $params):int
{
    $stmp = $db->prepare($query);
    $stmp->execute($params);
    return $stmp->rowCount();
}

function dbGetRecords(PDO $db, string $query, array $params, bool $oneRecordOnly)
{
    $stmp = $db->prepare($query);
    $stmp->execute($params);
    return ($oneRecordOnly ? $stmp->fetch() : $stmp->fetchAll());
}

function checkValidParams(Request $request, array $params):bool
{
    foreach($params as $p)
    {
        if (!empty($request->getParam($p)))
        {   
            continue;
        }
        else
        {
            return false;
        }
    }
    return true;
}

function getPDOParams(array $keys, Request $request) {
    $vals = [];
    foreach ($keys as $k) {
        $vals[$k] = $request->getParam($k);
    }
    return $vals;
}

function getPDOParamsArray(array $keys, array $args) {
    $vals = [];
    foreach ($keys as $k) {
        $vals[$k] = $args[$k];
    }
    return $vals;
}