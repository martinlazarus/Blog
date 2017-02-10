<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog\Repository\Posts;
use Blog\Repository\Categories;
use Blog\Repository\Authors;

class PostController
{
    /**
     * 
     * @param type $view
     * @param Posts $postsRepo
     * @param Categories $catsRepo
     * @param Authors $authorsRepo
     */
    
    protected $view;
    
    public $postsRepo;
    
    
    public $catsRepo;
    
    
    
    public $authorsRepo;
    
    public function __construct($view, Posts $postsRepo, Categories $catsRepo, Authors $authorsRepo) 
    {
        $this->view = $view;
        $this->postsRepo = $postsRepo;
        $this->catsRepo = $catsRepo;
        $this->authorsRepo = $authorsRepo;
    }
    
    public function allposts(Request $request, Response $response)
    {
       $args['posts'] = $this->postsRepo->getAll();
       return $this->view->render($response, '/Post/posts.html.twig', $args);
    }
    
    public function edit_post(Request $request, Response $response, array $args)
    {
        $args['post'] = $this->postsRepo->getOne($args['PostId']);
        $args['categories'] = $this->catsRepo->getAllWithSelection($args['PostId']);
        $args['authors'] = $this->authorsRepo->getAllWithSelection($args['PostId']);
        return $this->view->render($response, '/Post/edit_post.html.twig', $args);
    }
}