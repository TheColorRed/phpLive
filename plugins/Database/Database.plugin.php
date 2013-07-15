<?php

/*
  define("PDO_DATABASE", "mysql");
  define("PDO_DBNAME", "test");
  define("PDO_HOST", "localhost");
  define("PDO_USER", "root");
  define("PDO_PASSWD", "afrid123");
 */

/**
 * @property PDO $db PDO Class
 * @property PDOStatement $sql PDO Statement
 */
class Database extends phpLive{

    private $db       = null,
            $dbtype   = null,
            $database = null,
            $hostname = null,
            $username = null,
            $password = null

    ;

    public function __construct($data){
        $this->dbtype   = $data["dbtype"];
        $this->database = $data["database"];
        $this->hostname = $data["hostname"];
        $this->username = $data["username"];
        $this->password = $data["password"];
        parent::__construct();
    }

    public function connect(){
        $this->db = new PDO("$this->dbtype:dbname=$this->database;host=$this->hostname;", $this->username, $this->password);
    }

    private function isConnected(){
        if($this->db === null){
            $this->connect();
        }
    }

    private function query($query, $args){
        $this->isConnected();
        $this->sql = $this->db->prepare($query, $args);
        $this->sql->execute($args);
    }

    private function queryinfo($args){
        $query = array_shift($args);
        if(isset($args[0]) && is_array($args[0])){
            $args = $args[0];
        }
        return (object)array("query" => $query, "args"  => $args);
    }

    public function select(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this;
        return $this->sql->fetchAll();
    }

    public function insert(){
        $info = $this->queryinfo(func_get_args());
        $this->query($info->query, $info->args);
        return $this->db->lastInsertId();
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