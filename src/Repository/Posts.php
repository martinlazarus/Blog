<?php

namespace Blog\Controller\Repository;

class Posts
{
    protected $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function allPosts()
    {
        
    }
}