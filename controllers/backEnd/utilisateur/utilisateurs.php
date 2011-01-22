<?php
	$a = new adminCrud("admin");
	$a->addField(array("champ"=>"login","designation"=>"Login"));
	$a->addField(array("champ"=>"prenom","designation"=>"Prénom"));
	$a->addField(array("champ"=>"nom","designation"=>"Nom"));
	$a->addField(array("champ"=>"Level","designation"=>"Type"));
	$a->addContrainte("nom","required");
	$a->addContrainte("prenom","required");
	$a->addContrainte("login","required");
	$a->addContrainte("password","required");
	$a->addFiltre("nom");
	$a->addFiltre("prenom");
	$a->addFiltre("Level");
?>
