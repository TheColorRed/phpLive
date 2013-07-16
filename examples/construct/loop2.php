<?php

require_once '../../phpLive.php';
echo "type 'exit' to quit.\n";
$live->loop(function() use($live){
            $equation = $live->strOut("type a math equation")->strIn()->toString();
            if(strtolower($equation) == "exit"){
                return true; // End the while loop
            }
            echo $equation . " = ";
            eval("echo number_format($equation, 2);");
            echo "\n";
        });
echo "<hr />";
echo $live->highlight("listen.php", HIGHLIGHT_PHP);