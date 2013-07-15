<?php

require_once '../../phpLive.php';

$live->db->select("select * from users where fname in(?,?)", array("Ryan", "Jaimee"))->each(function($col, $name){
    echo "here";
});