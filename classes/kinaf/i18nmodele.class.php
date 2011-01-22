<?php

namespace kinaf;

    class i18nModele extends modele{
        
        protected $i18nFields = array();
        protected $i18nValues = array();
        
        public function get($nom){
            if(in_array($nom,$this->i18nFields)){
                return $this->i18nValues[$nom];
            } else {
                return $this->$nom;
            }
        }
        
        public function set($nom,$valeur){
            if(in_array($nom,$this->i18nFields)){
                $this->i18nValues[$nom][$_SESSION['lang']] = $valeur;
            } else {
               $this->$nom = $valeur;
            }
        }
        
        public function save(){
            if(parent::save()){
                // save des i18n
                foreach($this->i18nValues as $field => $values){
                    foreach($values as $lang => $value){
                        $this->pdo->query("update ".static::$table."_i18n set `".$field."` = '$value' where id = ".$this->id." and Lang = ".$lang);
                    }
                }
                return true;
            } else {
                return false;
            }
        }
        
        protected function load(){
		$info = $this->pdo->query("select * from ".static::$table." where id = ".$this->id)->fetch();
            foreach($this->orm['fields'] as $id => $val){
                if(!in_array($val,$this->i18nFields)){
                    if($val=="object"){ // is what we are trying to load is an object we instancied it here
                        if($info[$id]!=0){
                            $this->$id = new $id($info[$id]);
                        } else {
                            $this->$id = null;
                        }
                    } elseif($val=="date"){
                        $this->$id = date_en_to_fr($info[$id]);
                    } else {
                        $this->$id = $info[$id];
                    }
                }
            }
        
        $fields = $this->pdo->query("show columns from ".static::$table."_i18n where Type like 'varchar%' OR TYPE LIKE 'text%'");
        $info_i18n = $this->pdo->query("select * from ".static::$table."_i18n where id = ".$this->id." and Lang = ".$_SESSION['lang'])->fetch();
			foreach($fields as $field){
				$this->i18nValues[$field['Field']] = $info_i18n[$field['Field']];
			}
        }
        
        public function delete(){
            $this->pdo->exec("delete from ".static::$table." where id = ".$this->id);
            $this->pdo->exec("delete from ".static::$table."_i18n where id = ".$this->id);
        }
        
    }
?>
