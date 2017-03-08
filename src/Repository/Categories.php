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

//    if($request->getParam('category') != null)
//    {
//        $query = "INSERT INTO Category VALUES (DEFAULT, :category)";
//        $params = getPDOParams(['category'], $request);
//        dbCreateUpdateDelete($this->db, $query, $params);
//        $args = ['message' => 'Category created successfully'];
//        return $this->view->render($response, "message.html.twig", $args);
//    }
//    else
//    {
//        $args = ['message' => 'Please enter a category and try again'];
//        return $this->view->render($response, "message.html.twig", $args);
//    }