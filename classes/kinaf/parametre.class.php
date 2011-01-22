<?php

namespace kinaf;

	class parametre extends modele {
		protected static $table = "parametre";
		
		public function __toString(){
			return $this->designation;
		}
		
	}
?>
