<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog;

class PostController extends BaseController
{
    protected $view;
    /**
     *
     * @var \Blog\Database 
     */
    public $db;
    
    public function __construct($view, \Blog\Database $db) 
    {
        parent::__construct($view, $db);
    }
    
    public function allposts(Request $request, Response $response)
    {
        $results = $this->db->getQueryResults("SELECT * FROM Post", []);
        echo "hey there";
        die();
        $dbParams = [
                        'host'      => 'localhost',
                        'user'      => 'root',
                        'pass'      => '',
                        'dbname'    => 'Blog'
                    ];
        $db = new Blog\Database($dbParams);
        //$results = $db->getQueryResults("SELECT * FROM Posts", []);
        var_dump($results);
        die();
        return $this->view->render($response, 'message.html.twig',
            [
               'message' => 'this is the ALL POSTS method'
            ]);
    }
}