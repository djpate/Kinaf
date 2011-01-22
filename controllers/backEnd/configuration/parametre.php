<?php
	$a = new AdminCrud("parametre");
	$a->addField(array("champ"=>"id","designation"=>"#"));
	$a->addField(array("champ"=>"designation","designation"=>"Désignation"));
	$a->addField(array("champ"=>"valeur","designation"=>"Valeur"));
	$a->addContrainte("valeur","required");
	$a->addContrainte("designation","required");
	$a->set('titreListing','Listing des paramètres');
	
?>
