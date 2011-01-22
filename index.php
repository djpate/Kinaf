<?php
	/* front controller */
	session_start();
	require('configuration/configuration.php');
	require('classes/loader.php');
	
	$r = Routes::singleton();
	
	$p = new Page();
?>
