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
        return $this->view->render($response, 'message.html.twig',
            [
               'message' => 'this is the ALL POSTS method'
            ]);
    }
}