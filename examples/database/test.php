<?php

require_once '../../phpLive.php';

$live->db->select("select * from users where fname in(?,?)", "Ryan", "Jaimee")->each(function($row){
    return $row["fname"];
});