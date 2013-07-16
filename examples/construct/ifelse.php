<?php

require_once '../../phpLive.php';
$age = mt_rand(0, 3);
echo $live->ifelse($age == 0, function() use ($age){
            return "if statement fired! (\$age = $age)";
        }, $age == 1, function() use ($age){
            return "ifelse statement fired! (\$age = $age)";
        }, function() use ($age){
            return "else fired! (\$age = $age)";
        });
echo "<hr />";
echo $live->highlight("ifelse.php", HIGHLIGHT_PHP, INPUT_FILE);