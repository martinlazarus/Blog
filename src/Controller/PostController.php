<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog\Repository\Posts;
use Blog\Repository\Categories;
use Blog\Repository\Authors;
use Blog\Utilities;
use \Slim\Flash\Messages;

class PostController
{
    /**
     * 
     * @param type $view
     * @param Posts $postsRepo
     * @param Categories $catsRepo
     * @param Authors $authorsRepo
     * @param Messages $flash
     */
    
    protected $view;
    protected $postsRepo;
    protected $catsRepo;
    protected $authorsRepo;
    protected $flash;
    
    public function __construct($view, Posts $postsRepo, Categories $catsRepo, Authors $authorsRepo,
                    Messages $flash) 
    {
        $this->view = $view;
        $this->postsRepo = $postsRepo;
        $this->catsRepo = $catsRepo;
        $this->authorsRepo = $authorsRepo;
        $this->flash = $flash;
    }
    
    public function allposts(Request $request, Response $response)
    {
       $args['posts'] = $this->postsRepo->getAll();
       return $this->view->render($response, '/Post/posts.html.twig', $args);
    }
    
    public function newPost(Request $request, Response $response, array $args)
    {
        $args = $this->postsRepo->getNewPost();
        return $this->view->render($response, '/Post/newpost.html.twig', $args);
    }
    
    public function savePost(Request $request, Response $response, array $args)
    {
        $body = ['title', 'category', 'author', 'content'];
        $params = Utilities::getPDOParams($body, $request);
    }
    
    public function getPost(Request $request, Response $response, array $args)
    {
        $args['post'] = $this->postsRepo->getOne($args['PostId']);
        $args['categories'] = $this->catsRepo->getAllWithSelection($args['PostId']);
        $args['authors'] = $this->authorsRepo->getAllWithSelection($args['PostId']);
        $args['success'] = $this->flash->getMessage('success')[0];
        $args['error'] = $this->flash->getMessage('error')[0];
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
            $this->flash->addMessage('success', 'This post has been updated');
        }
        else
        {
            $this->flash->addMessage('error', 'Post could not be updated, please try again later');
        }
        return $response->withHeader('Location', '/post/' . $params['postid']);
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
        
        return $response->withHeader('Location', '/posts');
    }
}