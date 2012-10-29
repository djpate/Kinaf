<?php

	namespace kinaf\i18n;
	
	use \kinaf\configuration;
	
	class i18n {
		
		public static function bootstrap(){
		
			$conf = Configuration::get();
			
			if(isset($conf['i18n']['default'])){
			
				//Récupération de la langue préférée du browser
				$lang = strtr(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4), '-', '_').'.utf8';

				if(isset($conf['i18n']['locales'][$lang])) {
					setlocale("LC_ALL", $lang);
					$_SESSION['lang'] = $lang;
				} else {
					setlocale("LC_ALL",$conf['i18n']['default']);
					$_SESSION['lang'] = $conf['i18n']['default'];
				}

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
			$conf = configuration::get();

			if(isset($conf['i18n']['locales'][$locale])) {
				setlocale("LC_ALL", $locale);
				$_SESSION['lang'] = $locale;
			}
		}
	
	}
?>
