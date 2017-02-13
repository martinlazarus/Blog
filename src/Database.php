<?php

namespace Blog;

class Database
{
    /**
     *
     * @var \PDO
     */
    protected $handle;
    
    public function __construct(array $config) 
    {
        $db = $config;
        
        $pdo = new \PDO("mysql:host=" . $db["host"] . ";dbname=" . $db["dbname"] , $db["user"], $db["pass"]);
    
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->handle = $pdo;
    }
    
    public function getQueryResults(string $query, array $params):array
    {
        $stmp = $this->handle->prepare($query);
        $stmp->execute($params);
        return $stmp->fetchAll();
    }
    
    public function getQueryResultOneRecord(string $query, array $params)
    {
        $stmp = $this->handle->prepare($query);
        $stmp->execute($params);
        return $stmp->fetch();
    }
    
    public function getAffectedRows(string $query, array $params)
    {
        $stmp = $this->handle->prepare($query);
        $stmp->execute($params);
        return $stmp->rowCount();
    }
}
