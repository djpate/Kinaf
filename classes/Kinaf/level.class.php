<?php
	class level extends modele {
		protected static $table = "level";
		
		public function __toString(){
			return $this->designation;
		}
		
	}
?>
