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
                       JOIN Author AS A ON P.AuthorId = A.AuthorId
                   WHERE
                       IsDeleted = 0";
        return $this->db->getQueryResults($query, []);
    }
    
    public function newPost(array $params):int
    {
        $query = "INSERT INTO Post(Title, CategoryId, AuthorId, Content)
                      VALUES(:title, :category, :author, :content)";
                      
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
                       IsDeleted = 0
                       AND P.PostId = :postId";

        $params = ['postId' => $postId];
        return $this->db->getQueryResultOneRecord($query, $params);
    }
    
    public function updatePost(int $postId, array $data)
    {
        $query = "UPDATE Post
                      SET CategoryId = :category,
                          AuthorId = :author,
                          Title = :title,
                          Content = :content
                      WHERE
                          PostId = :postid";
        
        return $this->db->getAffectedRows($query, $data);
    }
    
    public function deletePost(int $postId)
    {
        $query = "UPDATE FROM Post
                  SET 
                      IsDeleted = 1 
                  WHERE
                      PostId = :postid";
        $params = ['postid' => $postId];
        
        return $this->db->getAffectedRows($query, $params);
    }
}