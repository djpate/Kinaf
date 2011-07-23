<?php
	namespace kinaf\i18n;
	
	class locale {
	
		private $locale;
		private $display;
		
		public function __construct($locale,$display){
			$this->locale = $locale;
			$this->display = $display;
		}
		
		public function getLocale(){
			return $this->locale;
		}
		
		public function getDisplay(){
			return $this->display;
		}
	
	}
?>
