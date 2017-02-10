<?php

namespace Blog\Repository;

Class Authors
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
                      AuthorId,
                      DisplayName,
                      FirstName,
                      LastName
                  FROM
                      Author";
        return $this->db->getQueryResults($query, []);
    }
    
    public function getAllWithSelection(int $PostId):array
    {
        $qeuery = "SELECT
                       A.AuthorId,
                       A.DisplayName,
                       A.FirstName,
                       A.LastName,
                       CASE A.AuthorId WHEN P.AuthorId THEN 'Selected' END AS Selected
                   FROM
                       Author AS A
                       LEFT JOIN Post AS P ON A.AuthorId = P.AuthorId
                           AND P.PostId = :PostId";
        $params = ['PostId' => $PostId];
        return $this->db->getQueryResults($qeuery, $params);
    }
}