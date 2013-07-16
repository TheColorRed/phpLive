<?php
require_once "../../phpLive.php";
echo $live->inetAToN("10.0.5.9");
echo "<br />";
echo $live->inetNToA(167773449);
echo "<hr />";

echo $live->highlight("inet-aton.php", HIGHLIGHT_PHP);