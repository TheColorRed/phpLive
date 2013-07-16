<?php
require_once '../../phpLive.php';
echo "<p style='color: red;'>The below output is html from a random page (see code at the bottom of this page)</p>";
echo "<hr />";
echo htmlentities($live->random("http://php.net", "http://phplive.org", "http://phpsnips.com")->getHttp());

echo "<hr />";
echo $live->highlight("random.php", HIGHLIGHT_PHP);