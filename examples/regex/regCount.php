<?php
require_once "../../phpLive.php";
echo $live->read("../../phpLive.php")->regCount("public function");
echo "<hr />";
echo $live->highlight("regCount.php", HIGHLIGHT_PHP);