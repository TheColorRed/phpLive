<?php
require_once '../../phpLive.php';
echo <<<OPT
<form action="" method="post">
	<p>First:<br /><input type="text" name="first" /></p>
	<p>Last:<br /><input type="text" name="last" /></p>
	<p>Email:<br /><input type="text" name="email" /></p>
	<p><input type="submit" value="Test for empties!" /></p>
</form>
OPT;
if($live->post()){
	if($live->empties($_POST["first"], $_POST["last"], $_POST["email"])){
		echo "The following were empty:<br />";
		print_r($live->list);
	}else{
		echo "There are no empty values!";
	}
}
echo "<hr />";
echo $live->highlight("empties.php", HIGHLIGHT_PHP, INPUT_FILE);