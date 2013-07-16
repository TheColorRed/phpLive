<?php

require_once '../../phpLive.php';
$num = mt_rand(0, 10);

echo "List: 1, 3, 5, 6<br />";

if($live->in($num, 1, 3, 5, 6)){
    echo "$num is in the list";
}else{
    echo "$num is not in the list";
}
echo "<hr />";
echo $live->highlight("in.php", HIGHLIGHT_PHP);