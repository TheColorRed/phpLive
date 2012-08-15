<?php
require_once '../../phpLive.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>phpLive Plugins Twitter | Plugin Version: <?php echo $live->twitter->pversion(); ?></title>
		<style>
			.tweet{
				padding: 10px;
				border-bottom: dashed 1px #aaaaaa;
			}
		</style>
	</head>
	<body>
		<form method="post">
			<p>
				Twitter Username: <input type="text" name="twitter-username" value="<?php echo $live->post("twitter-username"); ?>" />
				<select name="feed-count">
					<?php $count = $live->post("feed-count", 20); ?>
					<option value="5" <?php echo $count=="5"?"selected='selected'":""; ?>>5</option>
					<option value="10" <?php echo $count=="10"?"selected='selected'":""; ?>>10</option>
					<option value="15" <?php echo $count=="15"?"selected='selected'":""; ?>>15</option>
					<option value="20" <?php echo $count=="20"?"selected='selected'":""; ?>>20</option>
					<option value="25" <?php echo $count=="25"?"selected='selected'":""; ?>>25</option>
					<option value="50" <?php echo $count=="50"?"selected='selected'":""; ?>>50</option>
					<option value="100" <?php echo $count=="100"?"selected='selected'":""; ?>>100</option>
				</select>
				<input type="submit" value="Get Latest Feed" />
			</p>
		</form>
		<?php
			$twitter = $live->twitter;
			if($live->post()){
				$twitter->loadTimeline($live->post("twitter-username")->toString(), $count)->getTweets()->each(function($key, $value) use ($twitter){
					echo "<div class='tweet'>".$twitter->formatTweet($value)."<br />At: ".$key."</div>";
				});
			}
			echo "<h2>Page Source</h2>";
			echo $live->highlight("twitter-feed.php", HIGHLIGHT_PHP, INPUT_FILE);
		?>
	</body>
</html>