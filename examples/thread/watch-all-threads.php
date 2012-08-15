<?php
require_once '../../phpLive.php';
// use the root of your web directory as the start location
$p1 = $live->process('/examples/thread/test1.php');
$p2 = $live->process('/examples/thread/test2.php');
while($live->processing){
//	var_dump($live->pollThread($p1->thread_id));
//	var_dump($live->pollThread($p1->thread_id));
	echo "Processing...\n";
	sleep(1);
}
echo "Done!\n";