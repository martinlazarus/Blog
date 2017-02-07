<?php

namespace Blog\Controller;

class BaseController
{
    protected $view;
    /**
     *
     * @var \Blog\Database
     */
    public $db;
    
    public function __construct($view, \Blog\Database $db) 
    {
        $this->view = $view;
        $this->db = $db;
    }
}