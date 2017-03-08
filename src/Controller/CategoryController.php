<?php

namespace Blog\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Blog\Repository\Categories;
use Blog\Utilities;
use \Slim\Flash\Messages;

class CategoryController
{
    /**
     *
     * @param type $view
     * @param Categories $categories
     * @param Messages $flash  
     */
    protected $view;
    protected $categories;
    protected $flash;
    
    public function __construct($view, Categories $categories, Messages $flash)
    {
        $this->view = $view;
        $this->categories = $categories;
        $this->flash = $flash;
    }
    
    public function allcategories(Request $request, Response $response)
    {
        echo var_dump($this->categories->getAll());
    }
}
