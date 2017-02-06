<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PostController
{
    protected $view;
    
    public function __construct($view) 
    {
        $this->view = $view;
    }
    
    public function allposts(Request $request, Response $response)
    {
        $dbParams = [
                        'host'      => 'localhost',
                        'user'      => 'root',
                        'pass'      => '',
                        'dbname'    => 'Blog'
                    ];
        $db = new Database($dbParams);
        $results = $db->getQueryResults("SELECT * FROM Posts", []);
        var_dump($results);
        die();
        return $this->view->render($response, 'message.html.twig',
            [
               'message' => 'this is the ALL POSTS method'
            ]);
    }
}