<form action="" method="post">
	<p>
		Add: <input type="text" name="date" value="1 day" /> from now.
	</p>
	<p>
		<input type="submit" value="Go!" />
	</p>
</form><?php
if(isset($_POST["date"]))
	$range = $_POST["date"];
else
	$range = "1 day";
require_once "../../phpLive.php";
echo $live->dateAdd(time(), $range);
?>
<p>&nbsp;</p>
<?php
echo "<hr />";
echo $live->highlight("date-add.php", HIGHLIGHT_PHP);