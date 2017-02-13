<?php

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
                                                $container['repo.authors']
                                              );
};

$app = new \Slim\App($container);
//$app->config('debug', true);
        
$app->get('/', 'controller.home:hello');

$app->get('/allposts', 'controller.post:allposts');

$app->get('/post/{PostId}', 'controller.post:getPost');

$app->put('/post/{PostId}', 'controller.post:editPost');

$app->delete('/post/{PostId}', 'controller.post:deletePost');

$app->post('/post', function(Request $request, Response $response) {
    if (
            checkValidParams($request, ['categoryid', 'authorid', 'title', 'content'])
       )
    {
        try
        {
            $query = "INSERT INTO Post
                        (
                            CategoryId,
                            AuthorId,
                            Title,
                            Content
                        )
                    VALUES(:categoryid, :authorid, :title, :content)";
            $params = getPDOParams(['categoryid', 'authorid', 'title', 'content'], $request);
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

$app->get('/deletepost/{id}', function(Request $request, Response $response, $args){
    if (array_key_exists('id', $args))
    {
        if (is_numeric($args['id']) == 1)
        {
            /* @var $db PDO */
            $db = $this->db;
            $query = "DELETE FROM Post WHERE PostId = :id";
            $params = getPDOParamsArray(['id'], $args);
            $rows = dbCreateUpdateDelete($this->db, $query, $params);
            if($rows)
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
    return $this->view->render($response, 'message.html.twig', $args);
});

$app->get('/newpost', function(Request $request, Response $response, $args) {
    $categoryQuery = "SELECT * FROM Category";
    $authorQuery = "SELECT AuthorId, DisplayName FROM Author";
    $args['categories'] = dbGetRecords($this->db, $categoryQuery, [], 0);
    $args['authors'] = dbGetRecords($this->db, $authorQuery, [], 0);
    
    return $this->view->render($response, "newpost.html.twig", $args);
});

$app->get('/editpost/{id}', function(Request $request, Response $response, $args) {
    if (array_key_exists('id', $args) && is_numeric($args['id']))
    {
        $checkPost = "SELECT PostId FROM Post WHERE PostId = :postid";
        if (dbGetRecords($this->db, $checkPost, ['postid' => $args['id']], 1))
        {
            $id = $args['id'];
            $postQuery = "SELECT PostId, CategoryId, AuthorId, Title, Content FROM Post WHERE PostId = :id";
            $args['post'] = dbGetRecords($this->db, $postQuery, ['id' => $id], 1);

            $categoriesQuery = "SELECT
                                    *, 
                                    CASE WHEN CategoryId = :categoryId THEN 'selected' ELSE NULL END AS sel 
                                FROM 
                                    Category";
            $args['categories'] = dbGetRecords($this->db, $categoriesQuery, ['categoryId' => $args['post']['CategoryId']], 0);

            $authorsQuery = "SELECT 
                                AuthorId, 
                                DisplayName, 
                                CASE WHEN AuthorId = :authorid THEN 'selected' ELSE NULL END AS sel 
                            FROM 
                                Author";
            $args['authors'] = dbGetRecords($this->db, $authorsQuery, ['authorid' => $args['post']['AuthorId']], 0);

            return $this->view->render($response, "editpost.html.twig", $args);
        }
        else
        {
            $args['message'] = "A post with this ID does not exist. Please try anotehr ID.";
            return $this->view->render($response, "message.html.twig", $args);
        }
    }
    else
    {
        $args['message'] = "You have entered an invalid post, please try again";
        return $this->view->render($response, "message.html.twig", $args);
    }
});

$app->post('/editpost', function(Request $request, Response $response, $args) {
    $params = getPDOParams(['postid', 'title', 'content', 'category', 'author'], $request);
    $query = "UPDATE POST
                SET CategoryId = :category,
                    AuthorId = :author,
                    Title = :title,
                    Content = :content
              WHERE
                PostId = :postid";
    $rows = dbCreateUpdateDelete($this->db, $query, $params);
    $args['message'] = ($rows >= 1 ? "Post updated successfully" : "Problem updated Post. Please try again.");
    $this->view->render($response, 'message.html.twig', $args);
});

$app->get('/addcategory', function(Request $request, Response $response, $args) { 
    return $this->view->render($response, "addcategory.html.twig", $args);
});

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