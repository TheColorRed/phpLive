<?php

/**
 * @property PDO $pdo PDO Class
 * @property PDOStatement $sql PDO Statement
 * 
 * This class ueses the instance name of $db by default. 
 * Access to this class is as follows: $live->db->methodName();
 */
class Database extends phpLive{

    private $pdo      = null,
            $dbtype   = null,
            $database = null,
            $hostname = null,
            $username = null,
            $password = null,
            $sql      = null
    ;

    public function __construct($data){
        $this->dbtype   = $data["dbtype"];
        $this->database = $data["database"];
        $this->hostname = $data["hostname"];
        $this->username = $data["username"];
        $this->password = $data["password"];
        parent::__construct();
    }

    public function __get($name){
        parent::__get($name);
    }

    public function connect(){
        $this->pdo = new PDO("$this->dbtype:dbname=$this->database;host=$this->hostname;", $this->username, $this->password);
    }

    private function isConnected(){
        if($this->pdo === null){
            $this->connect();
        }
    }

    private function query($query, $args){
        $this->isConnected();
        try{
            $this->sql = $this->pdo->prepare($query, $args);
            $this->sql->execute($args);
        }catch(PDOException $e){
            throw $e;
        }
    }

    private function queryinfo($args){
        $query = array_shift($args);
        if(isset($args[0]) && is_array($args[0])){
            $args = $args[0];
        }
        return (object)array("query" => $query, "args"  => $args);
    }

    public function select(){
        $info       = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        $this->list = $this->sql->fetchAll();
        return $this;
        return $this->sql->fetchAll();
    }

    public function insert(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->pdo->lastInsertId();
    }

    public function update(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->sql->rowCount();
    }

    public function delete(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->sql->rowCount();
    }

    public function getFirst(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->sql->fetchColumn();
    }

    public function getEntry(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->sql->fetch();
    }

}