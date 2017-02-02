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

$app->post('/post', function(Request $request, Response $response) {
    if (
            checkValidParams($request, ['title', 'content'])
        )
    {
        try
        {
            $query = "INSERT INTO Post VALUES(DEFAULT, :title, :content, DEFAULT, DEFAULT)";
            $params = getPDOParams(['title', 'content'], $request);
            dbCreateUpdateDelete($this->db, $query, $params);
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
    return $this->view->render($response, 'message.html.twig', $args);
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
    $categoryQuery = "SELECT * FROM Category";
    $authorQuery = "SELECT AccountId, FirstName, LastName FROM Account";
    $args['categories'] = dbGetRecords($this->db, $categoryQuery, [], 0);
    $args['authors'] = dbGetRecords($this->db, $authorQuery, [], 0);
    
    return $this->view->render($response, "newpost.html.twig", $args);
});

$app->get('/editpost/{id}', function(Request $request, Response $response, $args) {
    if (array_key_exists('id', $args) && is_numeric($args['id']))
    {
        $id = $args['id'];
        $args['post'] = dbGetRecords($this->db, "SELECT CategoryId, AccountId, Title, Content FROM Post WHERE PostId = :id", ['id' => $id], 1);        
        $args['categories'] = dbGetRecords($this->db, "SELECT *, CASE WHEN CategoryId = :categoryId THEN 'selected' ELSE NULL END AS sel FROM Category", ['categoryId' => $args['post']['CategoryId']], 0);
        $args['authors'] = dbGetRecords($this->db, "SELECT AccountId, FirstName, LastName, CASE WHEN AccountId = :accountId THEN 'selected' ELSE NULL END AS sel FROM Account", ['accountId' => $args['post']['AccountId']], 0);
        
        return $this->view->render($response, "editpost.html.twig", $args);
    }
    else
    {
        $args['message'] = "You have entered an invalid post, please try again";
        return $this->view->render($response, "editpost.html.twig", $args);
    }
});

$app->get('/addcategory', function(Request $request, Response $response, $args) { 
    return $this->view->render($response, "addcategory.html.twig", $args);
});

$app->post('/addcategory', function(Request $request, Response $response, $args) { 
    if($request->getParam('category') != null)
    {
        $category = $request->getParam('category');
        $query = "INSERT INTO Category VALUES (DEFAULT, :category)";
        $params = ['category' => $category];
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
            checkValidParams($request, ['email', 'password', 'firstname', 'lastname'])
       )
    {
//        $email = $request->getParam('email');
//        $password = $request->getParam('password');
//        $firstname = $request->getParam('firstname');
//        $lastname = $request->getParam('lastname');
        
        $query = "INSERT INTO Account VALUES(DEFAULT, :email, :password, :firstname, :lastname)";
        $params = getPDOParams(['email', 'password', 'firstname', 'lastname'], $request);
//        $params = [
//                    'email' => $email, 
//                    'password' => $password, 
//                    'firstname' => $firstname, 
//                    'lastname' => $lastname
//                  ];
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