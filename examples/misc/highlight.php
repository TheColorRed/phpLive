<?php

require_once "../../phpLive.php";
echo "<h3>Html File</h3>";
echo "<pre>" . $live->highlight("highlights/test_html.html", HIGHLIGHT_HTML)->tabToSp() . "</pre>";
echo "<hr />";

echo "<h3>Xml File</h3>";
echo "<pre>" . $live->highlight("highlights/test_xml.xml", HIGHLIGHT_HTML)->tabToSp() . "</pre>";
echo "<hr />";

echo "<h3>CSS File</h3>";
echo "<pre>" . $live->highlight("highlights/test_css.css", HIGHLIGHT_CSS)->tabToSp() . "</pre>";
echo "<hr />";

echo $live->highlight("highlight.php", HIGHLIGHT_PHP);