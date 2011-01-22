<?php
/* 
 * loads all the necessary classes 
 * 
 * All classes should end with .class.php so it can be loaded
 * */
	
	/* loads yaml */
	
	require(dirname(__file__).'/../libs/yaml/sfYaml.php');
	
	/* autoload dans classes */
	
	function __autoload($class_name) {
		if(preg_match("/sfYaml/",$class_name)){
			require_once dirname(__FILE__) . "/../libs/yaml/" . $class_name . '.php';
		} elseif(!preg_match("/(.+)Controller/",$class_name)){
			require_once dirname(__FILE__) . "/" . strtolower($class_name) . '.class.php';
		} else {
			require_once dirname(__FILE__) . "/../controllers/frontEnd/" . $class_name . '.php';
		}
	}
	
	function date_en_to_fr($date_en){
		$d = explode("-",$date_en);
		return $d[2]."/".$d[1]."/".$d[0];
	}
	
	function date_fr_to_en($date_fr){
		$d = explode("/",$date_fr);
		return $d[2]."-".$d[1]."-".$d[0];
	}
	

?>
