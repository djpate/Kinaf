<?php

namespace kinaf\orm;

use \kinaf\db;

abstract class Model {
    
    protected $id = 0;
    protected $orm;

    public function __construct($id = 0){
    
        /* I hope we'll get scalar type hinting in php 5.4*/
        if( !is_numeric($id) ){
            throw new \Exception("You tried to load an entity with an invalid id (".$id.")");
        }
        
        /* Now let's verify that an orm definition exists for this entity */
        $this->orm = new orm(get_called_class());
        
        /* Seems to be good so let's add pdo */
        $this->pdo = Db::singleton();
        
        /* If the id is set we hydrate the object */
        if( $id != 0 ){
            $this->id = $id;
            $this->hydrate();
        }
    
    }
    
    private function hydrate(){
        
        /* prepare and run the query */
        
        $query = "SELECT * from ".$this->getTable()." where id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(array("id"=>$this->id));
        
        if($statement->rowCount()==0){
            throw new \Exception("You tried to load an entity that does not exist");
        }
        
        $info = $statement->fetch();
        
        /* now let's iterate over the fields of the mapping in order to hydrate our entity */
        
        $fields = $this->orm->getFields();
        
        foreach($fields as $field){
            
            $type = $this->orm->getType($field);
            
            switch($type){
                case 'entity':
                    
                    /* first we need to detect if a specific classname as been set */
                    $classname = $this->orm->getClass($field);
                    if(is_null($classname)){
                        /* if none was set we set the default one */
                        $classname = $field;
                    }
                    
                    /* add proprer namespace */
                    $classname = '\\entities\\'.$classname;
                    
                    $this->$field = new $classname($info[$field]);
                    
                break;
                default:
                    $this->$field = $info[$field];
                break;
            }
            
            
        }
        
        
    }
    
    /* this methods analyse the current entity state against the constraints
     * specified in the orm mapping */
    public function isValid(){
    
    }
    
    /* Save the current state of the entity into the db if the entity validates */
    public function save(){
        
    }
    
    public function getTable(){
        
        /* if table was set in the orm we return it from there */
        
        if(!is_null($this->orm->getTable())){
            return $this->orm->getTable();
        }
        
        /* otherwise the convention is the entity name lowercase */
        
        return strtolower($this->get_called_classname());
        
    }
    
    /* returns a namespace free version of get_called_class() */
    public function get_called_classname(){
        
        $class = explode('\\', get_called_class() );
        return $class[count($class) - 1];
        
    }
    
    /* getter and setter */
    
    public function get($key){
        return $this->$key;
    }
    
    public function set($key,$value){
        $this->$key = $value;
    }
    
    /* magics methods */
    
    public function __get($key){
        $this->get($key);
    }
    
    public function __set($key,$value){
        $this->set($key,$value);
    }
    
    public function __isset($key){
        return isset($this->$key);
    }
    
    public function __toString(){
        return get_called_class().'['.$this->id.']';
    }

}

?>
