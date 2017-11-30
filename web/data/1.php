<?php
	mkdir('test');
	chmod('test',0777);
	$fp = fopen('test/test.csv',"w");
	fclose($fp);
?>
