<?php
/* 
 * loads all the necessary classes 
 * 
 * All classes should end with .class.php so it can be loaded
 * */
	require('db.class.php');
	require('modele.class.php');
	require('level.class.php');
	require('admincrud.class.php');
	require('libs/yaml/sfYaml.php');
	require('libs/yaml/sfYamlParser.php');
	
	if(is_dir("classes")){
		$d = dir("classes/");
	} else { // we load it from admin dir
		$d = dir("../classes/");
	}
	while (false !== ($entry = $d->read())){
		if(substr($entry,-9)=="class.php"){
			require_once($entry);
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
