<style type="text/css">
p.good{color:green;font-weight:bold;}
p.bad{color:red;font-weight:bold;}
</style><?php
require_once "../../phpLive.php";
if($live->get()){
	if($live->get("name")->blank()){
		echo "<p class='bad'>Name is blank!</p>";
	}else{
		echo "<p class='good'>Name is not blank!</p>";
	}
}
echo <<<FORM
	<form action="" method="get">
		<table>
			<tr><td>Name:</td><td><textarea type="text" name="name">{$live->get("name")}</textarea></td></tr>
			<tr><td><input type="submit" value="Is Empty?"></td></tr>
		</table>
	</form>
FORM;
?>
<p>
	<b>Note:</b> $live->blank() is <u>NOT</u> the same as empty(). A space is not considered an empty string when using empty().<br />
	<b>Note:</b> Using $live->blank() a space is considered an empty string, so are tabs, newlines and carriage returns.<br />
	<b>Note:</b> Use $live->blanks() to check for a list of empty strings.
</p>
<hr />
<?php
echo $live->highlight("blank.php", HIGHLIGHT_PHP);
?>