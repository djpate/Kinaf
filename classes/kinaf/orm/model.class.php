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
    
		/* let's iterate over the fields */
		$fields = $this->orm->getFields();
		foreach($fields as $field){
			$constraints = $this->orm->getConstraints($field);
			/* if the constraints options was set */
			if( is_array($constraints) ){
				/* loop over all constraints */
				foreach($constraints as $constraint => $value){
					/* verify that the constraint method exists */
					if(!method_exists('\kinaf\orm\validation',$constraint)){
                        throw new \Exception("Validation ".$constraint." does not exist");
                    }
                    
                    /* we need to validate the field if and only if it is *required* and/or *set* */
                    if(array_key_exists("required",$constraints)||$this->$field!=""){
                    
                        if(!validation::$constraint($this->$field,$value)){
                            return false;
                            //Throw new \Exception($field." => \"".$this->$field."\" does not validate against ".$constraint);
                        }
                        
                    }
				}
			}
			
		}
		
		return true;
    
    }
    
    private function prepareForDb($field){
		if(is_object($this->$field)){
			return $this->$field->id;
		} else {
			return $this->$field;
		}
	}
    
    private function create(){
		
		$fields = $this->orm->getFields();
		$values = array();
		$tmp = array();
		
		foreach($fields as $field){
			$values[] = $this->prepareForDb($field);
			$tmp[] = "?";
		}
		
		$sql = "INSERT into ".$this->getTable()." (";
		
		$sql .= '`'.implode('`,`',$fields).'`';
		
		$sql .= ") VALUES (";
		
		$sql .= implode(",",$tmp);
		
		$sql .= ")";
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute($values);
		
		$this->id = $this->pdo->lastInsertId();
	
	}
	
	private function update(){
		
	}
    
    /* Save the current state of the entity into the db if the entity validates */
    public function save(){
		if($this->isValid()){ // make sure the entity is valid
			if($this->id != 0){ //if id is set then we need to update
				$this->update();
			} else {
				$this->create(); // no id so we need to create the entity
			}
		} else {
			return false;
		}
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
        return $this->get_called_classname().'['.$this->id.']';
    }

}

?>
