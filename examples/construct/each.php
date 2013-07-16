<?php

require_once '../../phpLive.php';
$live->list = array(1, 2, 3, 4, 5);
echo $live->each(function($v){
            return "$v<br />";
        })->implode("\n");
echo "<hr />";
echo $live->highlight("each.php", HIGHLIGHT_PHP, INPUT_FILE);