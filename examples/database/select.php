<?php

require_once '../../phpLive.php';

echo $live->db->select("select * from users where fname in(?,?)", "Ryan", "Jaimee")->each(function($row){
            return $row["fname"];
        }, $result)->implode("\n");

echo "\n\n";
print_r($result);
echo "\n";

$live->db->select("select * from users where lname = ?", "Naddy");
print_r($live->db->list);
echo "\n";

echo "Ran " . $live->db->queryCount() . " queries";