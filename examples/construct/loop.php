<?php

require_once '../../phpLive.php';
echo "<p>The following will loop 10 times.</p>";

$live->loop(function(){
            static $i = 0;
            echo "This is : $i<br />";
            $i++;
        }, 10);
echo "<hr />";
echo $live->highlight("loop.php", HIGHLIGHT_PHP, INPUT_FILE);