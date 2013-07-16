<?php
require_once '../../phpLive.php';
echo <<<HTML
	<form method="post">
		<p>
			URL: <input type="text" name="url" value="{$live->post("url")}" />
			<input type="submit" value="Get Top Words" />
		</p>
	</form>
HTML;
if($live->post()){
	echo $live->getHttp($live->post("url")->toString())->commonWords()->each(function($key, $value){
		return "<b>".strtoupper($key)."</b> was found $value times<br />";
	})->implode();
}
echo "<h2>Page Source</h2>";
echo $live->highlight("common_words.php", HIGHLIGHT_PHP);