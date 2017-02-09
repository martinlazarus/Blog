<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog\Repository\Posts;

class PostController
{
    /**
     *
     * @var \Slim\Views\Twig 
     */
    protected $view;
    /**
     *
     * @var Posts 
     */
    public $postsRepo;
    
    public function __construct($view, Posts $postsRepo) 
    {
        $this->view = $view;
        $this->postsRepo = $postsRepo;
    }
    
    public function allposts(Request $request, Response $response)
    {
       $args['posts'] = $this->postsRepo->getAll();
       return $this->view->render($response, '/Post/posts.html.twig', $args);
    }
}