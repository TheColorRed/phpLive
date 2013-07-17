<?php

/**
 * @property PDO $pdo PDO Class
 * @property PDOStatement $sql PDO Statement
 * 
 * This class ueses the instance name of $db by default. 
 * Access to this class is as follows: $live->db->methodName();
 */
class Database extends phpLive{

    private $pdo        = null,
            $dbtype     = null,
            $database   = null,
            $hostname   = null,
            $username   = null,
            $password   = null,
            $sql        = null,
            $queryCount = 0,
            $table      = ""

    ;

    public function __construct($data){
        $this->dbtype   = $data["dbtype"];
        $this->database = $data["database"];
        $this->hostname = $data["hostname"];
        $this->username = $data["username"];
        $this->password = $data["password"];
        parent::__construct();
    }
    
    public function __call($name, $args){
        if(preg_match("/findBy(.+)/", $name, $matches)){
            if($this->validName($matches[1])){
                return $this->find($matches[1], $args);
            }else{
                throw new Exception("Invalid Column Name.");
            }
        }
        return parent::__call($name, $args);
    }

    public function connect(){
        $this->pdo = new PDO("$this->dbtype:dbname=$this->database;host=$this->hostname;", $this->username, $this->password);
    }

    public function setTable($table){
        if($this->validName($table)){
            $this->table = $table;
        }else{
            throw new Exception("Invalid Table Name.");
        }
        return $this;
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
            $this->queryCount++;
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

    public function queryCount(){
        $this->string = $this->queryCount;
        return $this;
    }

    public function select(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->list = $this->sql->fetchAll();
        return $this;
    }

    public function insert(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->string = $this->pdo->lastInsertId();
        return $this;
    }

    public function update(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->string = $this->sql->rowCount();
        return $this;
    }

    public function delete(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->string = $this->sql->rowCount();
        return $this;
    }

    public function getFirst(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->string = $this->sql->rowCount();
        return $this;
    }

    public function getEntry(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);

        $this->list = $this->sql->fetchAll();
        return $this;
    }

    private function validName($string){
        if(preg_match("/[^a-zA-Z0-9\\\$_]+/", $string)){
            return false;
        }
        return true;
    }

    private function find($column, $args){
        if(isset($args[1]) && !empty($args[1])){
            $this->setTable($args[1]);
        }
        if(empty($this->table)){
            throw new Exception("No table is set.");
        }
        $this->select("select * from $this->table where $column = ?", $args[0]);
        return $this;
    }

}