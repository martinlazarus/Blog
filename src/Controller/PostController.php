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
    
    public function getPost(Request $request, Response $response, array $args)
    {
        $args['post'] = $this->postsRepo->getOne($args['PostId']);
        $args['categories'] = $this->catsRepo->getAllWithSelection($args['PostId']);
        $args['authors'] = $this->authorsRepo->getAllWithSelection($args['PostId']);
        $args['message'] = $this->flash->getMessage('editedPost')[0];
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
            $this->flash->addMessage('editedPost', 'This post has been updated');
        }
        else
        {
            $args['message'] = 'The post could not be updated. Please try again later.';
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
        return $this->view->render($response, 'message.html.twig', $args);
    }
}