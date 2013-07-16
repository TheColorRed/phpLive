<?php
require_once '../../phpLive.php';
$download = (bool)$live->get("d")->toString();
if($download){
	/**
	 * Array key is the location on the server
	 * Array value is the location in the zip
	 *
	 * If an array key is not given it will save in the root of the zip and look
	 * for it with the given server location. If the file is not found it will be
	 * added as a directory in the zip.
	 */
	$files = array(
		/**
		 * creates a folder ".." and a "construct" folder in it and puts "each.php" there
		 * add a value to this to save it with a better path name
		 */
		"../construct/each.php",
		"inet-aton.php",
		"folder A",
		"random.php" => "folder A/random.php"
	);
	$live->zip($files)->download(PHP_TMPFILE, "download.zip");
	exit;
}else{
	echo "<p><a href='?d=1'>Download Zip File</a></p>";
	echo "<hr />";
	echo $live->highlight("zip.php", HIGHLIGHT_PHP);
}