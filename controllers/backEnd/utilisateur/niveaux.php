<?php
	$a = new adminCrud("level");
	$a->addField(array("champ"=>"designation","designation"=>"Désignation"));
	$a->addContrainte("designation","required");
?>
