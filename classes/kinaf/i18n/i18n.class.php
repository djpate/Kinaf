<?php

	namespace kinaf\i18n;
	
	use \kinaf\configuration;
	
	class i18n {
		
		public static function bootstrap(){
		
			$conf = Configuration::get();
			
			if(isset($conf['i18n']['default'])){
			
				setlocale("LC_ALL",$conf['i18n']['default']);
				$_SESSION['lang'] = $conf['i18n']['default'];
				//TODO faire le language detector
				
			}
		
		}
		
		public static function getLocales(){
			
			$ret = array();
			$conf = configuration::get();
			
			if(isset($conf['i18n']['locales'])){
				if( count($conf['i18n']['locales']) > 0 ){
					foreach($conf['i18n']['locales'] as $locale => $info){
						$ret[] = new Locale($locale,$info['name']);
					}
				}
			}
			
			return $ret;
			
		}
		
		public static function setDefaultLocale($locale){
		
		}
	
	}
?>
