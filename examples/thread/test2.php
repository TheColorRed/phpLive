<?php
function flush_buffers(){
    ob_end_flush();
    flush();
    ob_start();
}
for($i=0;$i<10;$i++){
	echo "Test2: $i";
	flush_buffers();
	sleep(1);
}
?>