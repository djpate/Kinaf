<?php
session_start();

require('../configuration/configuration.php');
require('../classes/loader.php');

if(isset($_SESSION['adminid'])){
	$admin = new admin($_SESSION['adminid']);
	if(isset($_REQUEST['doLogout'])){
		$admin->logout();
	}
} else {
	$admin = new admin();
}

if(isset($_REQUEST['login'])&&isset($_REQUEST['password'])&&!isset($_SESSION['adminid'])){
	if(!$admin->logIn($_REQUEST['login'],$_REQUEST['password'])){
		$errLogin = true;
	}
}

if($admin->isConnected()){
	$page = new pageAdmin();
	$page->run();
} else {
	require('login.php');
}

?>
