<form action="" method="post">
	<p>
		Date 1: <input type="text" name="date1" value="<?php echo date("Y-m-d H:i:s"); ?>" /><br />
		Date 2: <input type="text" name="date2" value="<?php echo date("Y-m-d H:i:s"); ?>" />
	</p>
	<p>
		<input type="submit" value="Go!" />
	</p>
</form><?php
require_once "../../phpLive.php";
echo $live->datediff($_POST["date1"], $_POST["date2"]);
?>
<p>&nbsp;</p>
<?php
echo "<hr />";
echo $live->highlight("datediff.php", HIGHLIGHT_PHP, INPUT_FILE);