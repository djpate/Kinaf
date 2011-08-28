<?php

    namespace Kinaf;

    class Configuration {
    
        public static function get(){
            
            $yaml = new \sfYamlParser();
            $conf = $yaml->parse(file_get_contents(__dir__.'/../../../configuration/configuration.yaml'));
            
            /* merge environnement configuration */
            
            if(file_exists(__dir__.'/../../../configuration/environnement.yaml')){
                
                $envs = $yaml->parse(file_get_contents(__dir__.'/../../../configuration/environnement.yaml'));
                
                /* since hostnames can be regexp we need to loop over each one
                 * only the first good match is used */
                foreach($envs as $hostname => $env){
                    if( preg_match('`'.$hostname.'`',$_SERVER['SERVER_NAME']) ){
                        $conf = static::mergeConf($conf,$env['configuration']);
                        break;
                    }
                }
                
            }
            
            return $conf;
            
        }
        
        public static function mergeConf($Arr1, $Arr2){
            foreach($Arr2 as $key => $Value){
                if(array_key_exists($key, $Arr1) && is_array($Value)){
                    $Arr1[$key] = static::mergeConf($Arr1[$key], $Arr2[$key]);
                } else {
                    $Arr1[$key] = $Value;
                }
            }
            
            return $Arr1;
        }

    
    }
    
?>
