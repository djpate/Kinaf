<?php

    namespace Kinaf;

    class environnement {
    
        public static function name(){
            
            if(!defined("ENV")){
                
                $name = "default";
                
                if(file_exists(__dir__.'/../../../configuration/environnement.yaml')){
                    
                    $yaml = new \sfYamlParser();
                    $envs = $yaml->parse(file_get_contents(__dir__.'/../../../configuration/environnement.yaml'));
                    
                    /* since hostnames can be regexp we need to loop over each one
                     * only the first good match is used */
                    foreach($envs as $hostname => $env){
                        if( preg_match('`'.$hostname.'`',$_SERVER['SERVER_NAME']) ){
                            if(isset($env['name'])){
                            
                                $name = $env['name'];
                            
                            }
                        }
                    }
                
                }
                
                define('ENV',$name);
                
            }
            
            return ENV;
            
        }
    
    }
    
?>
