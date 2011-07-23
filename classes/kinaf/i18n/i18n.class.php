<?php

	namespace kinaf\i18n;
	
	use \kinaf\configuration;
	
	class i18n {
		
		public static function bootstrap(){
		
			$conf = Configuration::get();
			
			if(isset($conf['i18n']['default'])){
			
				setlocale("LC_ALL",$conf['i18n']['default']);
				
			}
		
		}
		
		public static function getLocales(){
			
			$ret = array();
			$conf = configuration::get();
			
			if(isset($conf['i18n']['locales'])){
				if( count($conf['i18n']['locales']) > 0 ){
					foreach($conf['i18n']['locales'] as $locale => $display){
						$ret[] = new Locale($locale,$display);
					}
				}
			}
			
			return $ret;
			
		}
		
		public static function setDefaultLocale($locale){
		
		}
	
	}
?>
