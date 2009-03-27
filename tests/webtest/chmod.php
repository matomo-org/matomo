<?php
/*
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("@PATH@/*"));
	echo("@PATH@\n");
	foreach($files as $file) {
		echo("File: " . $file . "\n");
		chmod($file, 0666);
	}
*/
	exec('chmod -R a+rw @PATH@/*');

?>