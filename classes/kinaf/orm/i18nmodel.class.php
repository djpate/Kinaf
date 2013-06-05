<?php

namespace kinaf\orm;

    class i18nModel extends model{
        
        protected $i18nFields = array();
        protected $i18nValues = array();
        
        public function set($nom,$valeur,$locale=null){
            if(in_array($nom,$this->i18nFields)){
                $this->i18nValues[$locale][$nom] = $valeur;
            } else {
               $this->$nom = $valeur;
            }
        }

        protected function bindi18n(array $values){

            $orm = Orm::getFromCache(get_called_class());

            if(isset($values['locale'])) {
                $locale = $values['locale'];
            } else {
                $locale = $this->locale;
            }
        
            foreach($values as $field => $value){
                if( in_array($field, $this->i18nFields) ){
                    $type = $orm->getType($field);
                    switch($type){
                        case 'entity':

                            /* first we need to detect if a specific classname as been set */
                            $classname = $orm->getClass($field);
                            if(is_null($classname)){
                                /* if none was set we set the default one */
                                $classname = $field;
                            }

                            /* add proprer namespace */
                            $classname = '\\entities\\'.$classname;
                            
                            //Check if the value is already an object
                            if(is_object($value)) {

                                //check if the class of the value is the same as specified if specified
                                if('\\'.get_class($value) == $classname) {
                                    $this->i18nValues[$locale][$field] = $value;
                                } else {
                                    $this->i18nValues[$locale][$field] = null;
                                }

                            } else {
                                
                                if(is_numeric($value)){
                                
                                    $this->i18nValues[$locale][$field] = new $classname($value);
                                    
                                } else {
                                    
                                    $this->i18nValues[$locale][$field] = null;
                                    
                                }

                            }
                            
                        break;
                        default:
                            $this->i18nValues[$locale][$field] = $value;
                        break;
                    }
                }
            }
            
        }
        
        public function save(array $values = null,array $allowedFields = null){
            if(parent::save($values, $allowedFields)){
                $this->bindi18n($values);
                foreach($this->i18nValues as $locale => $content){
                    foreach($content as $field => $value){
                        $sql = "select count(*) as nb from ".static::getTable()."_i18n where `locale` = ? and id = ?";
                        $stm = $this->pdo->prepare($sql);
                        $stm->execute(array($locale, $this->id));
                        $result = $stm->fetch();
                        if($result['nb']==0){
                            // If we are creating a object we initiate the row
                            $sql = "INSERT INTO ".static::getTable()."_i18n (id, `locale`, `".$field."`) values (:id, :locale, :value)";
                        } else {
                            $sql = "UPDATE ".static::getTable()."_i18n set `".$field."` = :value where `locale` = :locale and id = :id";
                        }
                        $stm = $this->pdo->prepare($sql);
                        $stm->execute(array(':id' => $this->id, ':value' => $value, ':locale' => $locale));
                    }
                }
            }
        }
        
        public function delete(){
            $this->pdo->exec("delete from ".static::getTable()." where id = ".$this->id);
            $this->pdo->exec("delete from ".static::getTable()."_i18n where id = ".$this->id);
        }
        
        public function getI18nFields(){
            return $this->i18nFields;
        }
        
    }
?>
