<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog\Repository\Posts;
use Blog\Repository\Categories;
use Blog\Repository\Authors;
use Blog\Utilities;

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
    protected $postsRepo;
    protected $catsRepo;
    protected $authorsRepo;
    
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
    
    public function getPost(Request $request, Response $response, array $args)
    {
        $args['post'] = $this->postsRepo->getOne($args['PostId']);
        $args['categories'] = $this->catsRepo->getAllWithSelection($args['PostId']);
        $args['authors'] = $this->authorsRepo->getAllWithSelection($args['PostId']);
        return $this->view->render($response, '/Post/edit_post.html.twig', $args);
    }
    
    public function editPost(Request $request, Response $response, array $args)
    {
        $body = ['title', 'category', 'author', 'content'];
        $params = Utilities::getPDOParams($body, $request);
        $params['postid'] = $args['PostId'];
        $rowsAffected = $this->postsRepo->updatePost($params['postid'], $params);
        if ($rowsAffected)
        {
            $args['message'] = 'The post was successfully updated.';
        }
        else
        {
            $args['message'] = 'The post could not be updated. Please try again later.';
        }
        return $this->view->render($response, 'message.html.twig', $args);
    }
    
    public function deletePost(Request $request, Response $response, array $args)
    {
        $rowsAffected = $this->postsRepo->deletePost($args['PostId']);
        
        if ($rowsAffected)
        {
            $args['message'] = 'The post was successfully deleted.';
        }
        else
        {
            $args['message'] = 'The post could not be deleted. Please try again later.';
        }
        return $this->view->render($response, 'message.html.twig', $args);
    }
}