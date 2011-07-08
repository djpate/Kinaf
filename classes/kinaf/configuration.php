<?php

    namespace Kinaf;

    class Configuration {
    
        public static function get(){
            
            $yaml = new \sfYamlParser();
            $conf = $yaml->parse(file_get_contents(__dir__.'/../../../configuration/configuration.yaml'));
            
            return $conf;
            
        }
    
    }
    
?>
