<?php
	if(isset($_REQUEST['action'])){
		if($_REQUEST['action']=="autocomplete"){
			$a = array();
			$obj = $_REQUEST['objet'];
			$o = new $obj();
			$q = $this->pdo->query("select id from ".$o->get('table')." where ".$o->get('autoSuggestField')." like '".$_REQUEST['term']."%' limit 20");
			foreach($q as $r){
				$o = new $obj($r['id']);
				array_push($a,array("id"=>$o->get('id'),"label"=>$o->__toString()));
			}
			echo json_encode($a);
		}
	}
?>
