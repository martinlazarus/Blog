<?php

namespace Blog\Repository;

class Posts
{
    /**
     *
     * @var \Blog\Database 
     */
    protected $db;
    
    public function __construct(\Blog\Database $db) 
    {
        $this->db = $db;
    }
    
    public function getAll():array
    {
        $query = "SELECT
                      P.PostId,
                      P.Title,
                      C.Name AS CategoryName,
                      P.Content,
                      A.DisplayName,
                      A.FirstName,
                      A.LastName,
                      P.Created_at,
                      P.Updated_at
                   FROM
	               Post AS P
                       JOIN Category AS C ON P.CategoryId = C.CategoryId
                       JOIN Author AS A ON P.AuthorId = A.AuthorId";
        return $this->db->getQueryResults($query, []);
    }
    public function getOne(int $postId):array
    {
        $query = "SELECT
                      P.PostId,
                      P.Title,
                      C.Name AS CategoryName,
                      P.Content,
                      A.DisplayName,
                      A.FirstName,
                      A.LastName,
                      P.Created_at,
                      P.Updated_at
                   FROM
	               Post AS P
                       JOIN Category AS C ON P.CategoryId = C.CategoryId
                       JOIN Author AS A ON P.AuthorId = A.AuthorId
                   WHERE
                       P.PostId = :postId";

        $params = ['postId' => $postId];
        return $this->db->getQueryResultOneRecord($query, $params);
    }
}