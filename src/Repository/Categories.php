<?php

namespace Blog\Repository;

class Categories
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
                      CategoryId,
                      Name AS CategoryName
                  FROM
                      Category";
        return $this->db->getQueryResults($query, []);
    }
    
    public function getAllWithSelection(int $PostId):array
    {
        $query = "SELECT
                      C.CategoryId,
                      C.Name AS CategoryName,
                      CASE C.CategoryId WHEN P.CategoryId THEN 'Selected' END AS Selected
                  FROM
                      Category AS C
                      LEFT JOIN Post AS P ON C.CategoryId = P.CategoryId
                          AND P.PostId = :PostId";
        $params = ['PostId' => $PostId];
        return $this->db->getQueryResults($query, $params);
    }
}
