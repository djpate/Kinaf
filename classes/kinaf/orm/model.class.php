<?php

namespace kinaf\orm;

use \kinaf\db;

abstract class Model {
    
    protected $id = 0;
    protected $pdo;
    protected $orm;
    protected $modifiedFields = array();
    protected $fields;
    protected $i18nFields;
    protected $i18nValues;
    protected $values = array();
    protected $oneToMany = array();
    
    /* static methods */
    
    public static function all($order_column="id",$order_sort="asc"){
		return static::fetch($order_column,$order_sort);
	}
	
	public static function fetch($limit_offset = null, $limit_count = null, $order_column="id", $order_sort="asc"){
		
		$pdo = db::singleton();
		$ret = array();
		
		if( is_numeric($limit_offset) && is_numeric($limit_count) ){
			$limit = " LIMIT ".$limit_offset.",".$limit_count; 
		} else {
			$limit = "";
		}
		
		$statement = $pdo->prepare("SELECT id FROM `".static::getTable()."` ORDER BY `".$order_column."` ".$order_sort.$limit);
		$statement->execute();
		
		if( $statement->rowCount() > 0 ){
			
			$class = '\\entities\\'.static::get_called_classname();
			
			foreach($statement as $row){
				$ret[] = new $class($row['id']);
			}
		}
		
		return $ret;
		
	}
	
	public static function count(){
		$pdo = db::singleton();
		$info = $pdo->query("select count(id) as cnt from ".static::getTable())->fetch();
		return $info['cnt'];
	}
	
	protected static function getTable(){
        
        $orm = new orm(get_called_class());
        
        /* if table was set in the orm we return it from there */
        
        if(!is_null($orm->getTable())){
            return $orm->getTable();
        }
        
        /* otherwise the convention is the entity name lowercase */
        
        return strtolower(static::get_called_classname());
        
    }
    
    /* returns a namespace free version of get_called_class() */
    public static function get_called_classname(){
        
        $class = explode('\\', get_called_class() );
        return $class[count($class) - 1];
        
    }

    public function __construct($id = 0){
    
        /* I hope we'll get scalar type hinting in php 5.4*/
        if( !is_numeric($id) ){
            throw new \Exception("You tried to load an entity with an invalid id (".$id.") ".get_called_class());
        }
        
        /* Now let's verify that an orm definition exists for this entity */
        $this->orm = new orm(get_called_class());
        
        /* now let's populate the various fields definitions */
        $this->fields = $this->orm->getFields();
        $this->i18nFields = $this->orm->getFields(true);
        $this->oneToMany = $this->orm->getOneToMany();
        $this->manyToMany = $this->orm->getManyToMany();
        
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
        
        $query = "SELECT * from ".static::getTable()." where id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(array("id"=>$this->id));
        
        if($statement->rowCount()==0){
            throw new \Exception("You tried to load an entity that does not exist");
        }
        
        $info = $statement->fetch();
        
        /* now let's iterate over the fields of the mapping in order to hydrate our entity */
        
        foreach($this->fields as $field){
            
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
                    
                    $this->values[$field] = new $classname($info[$field]);
                    
                break;
                default:
                    $this->values[$field] = $info[$field];
                break;
            }
            
        }
        
        /* now let's check for i18n fields */
        
        if(count($this->i18nFields)>0){
			
			$locale = setlocale("LC_ALL",0); // get the current local
			
			if($locale == "C"){
				throw new \Exception("You locale was not set ! Please check the i18n configuration");
			}
			
			$statement = $this->pdo->prepare("SELECT * FROM ".static::getTable()."_i18n where id = ? and locale = ?");
			$statement->execute(array($this->id,$locale));
			
			if($statement->rowCount() == 1){
				
				$row = $statement->fetch();
				
				foreach($this->i18nFields as $field){
					
					$this->i18nValues[$field] = $row[$field];
					
				}
				
			} else {
				//translation not found !
				foreach($this->i18nFields as $field){
					
					$this->i18nValues[$field] = "not translated"; // TODO: find something smart to do here
					
				}
			}
			
		}
        
        
    }
    
    /* this methods analyse the current entity state against the constraints
     * specified in the orm mapping */
    public function isValid(){
    
		/* let's iterate over the fields */
		foreach($this->fields as $field){
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
                    if(array_key_exists("required",$constraints)||$this->values[$field]!=""){
                    
                        if(!validation::$constraint($this->values[$field],$value)){
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
		
		$type = $this->orm->getType($field);
		
		if($type == "entity"){
		
			if(is_object($this->values[$field])){
				
				/* PHP 5.4 should fix this extra step */
				
				$obj = $this->values[$field];
				
				return $obj->id;
				
			} else {
				
				return 0;
				
			}
		
		} else {
			
			return $this->values[$field];
			
		}
	}
    
    private function create(){
		
		$values = array();
		$tmp = array();
		
		foreach($this->fields as $field){
			$values[] = $this->prepareForDb($field);
			$tmp[] = "?";
		}
		
		$sql = "INSERT into ".static::getTable()." (";
		
		$sql .= '`'.implode('`,`',$this->fields).'`';
		
		$sql .= ") VALUES (";
		
		$sql .= implode(",",$tmp);
		
		$sql .= ")";
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute($values);
		
		$this->id = $this->pdo->lastInsertId();
	
	}
	
	private function update(){
		
		if(count($this->modifiedFields)>0){
		
			$values = array();
		
			$sql = "UPDATE ".static::getTable()." set ";
			
			foreach($this->modifiedFields as $field){
				
				$values[] = $this->prepareForDb($field);
				
				$sql .= '`'.$field.'` = ?,';
				
			}
			
			$sql = rtrim($sql,",");
			
			$sql .= " where id = ".$this->id;
			
			$statement = $this->pdo->prepare($sql);
			$statement->execute($values);
			
		}
		
	}
    
    /* Save the current state of the entity into the db if the entity validates */
    public function save(){
		if($this->isValid()){ // make sure the entity is valid
			if($this->id != 0){ //if id is set then we need to update
				$this->update();
			} else {
				$this->create(); // no id so we need to create the entity
			}
			
			/* reset the modified fields */
			$this->modifiedFields = array();
			
			return true;
			
		} else {
			return false;
		}
    }
    
    /* Delete the entity from the db and 
     * delete all related onetomany & manytomany
     * if cascade is set to true */
    public function delete(){
		
		/* handles one to many */
		if(count($this->oneToMany)>0){
			foreach($this->oneToMany as $name => $defintion){
				if( isset($defintion['cascade']) && $defintion['cascade'] == true ){
					$function_name = "get".$name;
					$childs = $this->$function_name();
					if(count($childs)>0){
						foreach($childs as $child){
							$child->delete();
						}
					}
				}
			}
		}
		
		/* handles many to many */
		if(count($this->manyToMany)>0){
			foreach($this->manyToMany as $name => $defintion){
				if( isset($defintion['cascade']) && $defintion['cascade'] == true ){
					$function_name = "get".$name;
					$childs = $this->$function_name();
					if(count($childs)>0){
						foreach($childs as $child){
							$child->delete();
						}
					}
				}
			}
		}
		
		/* handles i18n */
		if(count($this->i18nFields)>0){
			$statement = $this->pdo->prepare("delete from ".static::getTable()."_i18n where id = ?");
			$statement->execute(array($this->id));
		}
		
		/* handles the entity itself */
		$statement = $this->pdo->prepare("delete from ".static::getTable()."_i18n where id = ?");
		$statement->execute(array($this->id));
		
	}
    
    /* getter and setter */
    
    public function get($key){
		
		if(in_array($key,$this->fields)){
			
			return $this->values[$key];
			
		} else if(in_array($key,$this->i18nFields)){
			
			return $this->i18nValues[$key];
			
		} else {
			
			return $this->$key;
			
		}
		
    }
    
    public function set($key,$value){
		
		if(in_array($key,$this->fields)){
			
			if( $this->values[$key] != $value ){
				
				$this->values[$key] = $value;
				$this->modifiedFields[] = $key;
				
			}
			
		} else {
			
			$this->$key = $value;
			
		}
		
    }
    
    /* one to many methods */
    
    private function one_to_many_info($name){
		
		if(!array_key_exists("entity",$this->oneToMany[$name])){
			throw new Exception("The oneToMany relationship called ".$name." is missing it's entity definition");
		}
		
		$entity = $this->oneToMany[$name]['entity'];
		
		/* first of all we got to get the table name since it can different
		 * than the convention one */
		
		$orm = new Orm($entity);
		if(!is_null($orm->getTable())){
			$table = $orm->getTable();
		} else {
			$table = strtolower($entity);
		}
		 
		/* then we have to find the column name */
		
		if(array_key_exists("column",$this->oneToMany[$name])){
			$column = $this->oneToMany[$name]['column'];
		} else {
			$column = strtolower(static::get_called_classname()); // convention
		}
		
		return array($entity, $table, $column);
		
	}
    
    private function get_one_to_many($name, $limit_offset = null, $limit_count = null, $order_column = "id", $order_sort = "asc"){
		
		$ret = array();
		
		list($entity, $table, $column) = $this->one_to_many_info($name);
		
		$classname = '\entities\\'.$entity;
		
		if( is_numeric($limit_offset) && is_numeric($limit_count) ){
			$limit = " LIMIT ".$limit_offset.",".$limit_count; 
		} else {
			$limit = "";
		}
		
		$sql = "SELECT id FROM `".$table."` WHERE `".$column."` = ? ORDER BY `".$order_column."` ".$order_sort.$limit;
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute(array($this->id));
		
		if($statement->rowCount()>0){
			foreach($statement as $row){
				array_push($ret,new $classname($row['id']));
			}
		}
		
		return $ret;
		
		
	}
	
	private function count_one_to_many($name){
	
		list($entity, $table, $column) = $this->one_to_many_info($name);
		
		$sql = "SELECT count(*) as count FROM `".$table."` WHERE `".$column."` = ?";
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute(array($this->id));
		$result = $statement->fetch();
		
		return $result['count'];
	
	}
	
	/* many to many methods */
	
	private function many_to_many_info($name){
		
		if(!array_key_exists("entity",$this->manyToMany[$name])){
			throw new Exception("The manyToMany relationship called ".$name." is missing it's entity definition");
		}
		
		if(!array_key_exists("table",$this->manyToMany[$name])){
			throw new Exception("The manyToMany relationship called ".$name." is missing it's table definition");
		}
		
		return array($this->manyToMany[$name]['entity'],$this->manyToMany[$name]['table']);
		
	}
	
	private function get_many_to_many($name,$limit_offset = null, $limit_count = null,$order_column = "id", $order_sort = "asc"){
		
		$ret = array();
		
		list($entity, $table) = $this->many_to_many_info($name);
		
		$classname = '\entities\\'.$entity;
		
		if( is_numeric($limit_offset) && is_numeric($limit_count) ){
			$limit = " LIMIT ".$limit_offset.",".$limit_count; 
		} else {
			$limit = "";
		}
		
		$sql = 'SELECT `'.$entity.'` as id FROM `'.$table.'` where '.static::getTable().' = ? ORDER BY `'.$order_column.'` '.$order_sort.$limit;
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute(array($this->id));
		
		if($statement->rowCount() > 0){
			foreach($statement as $row){
				$ret[] = new $classname($row['id']);
			}
		}
		
		return $ret;
		
	}
	
	private function count_many_to_many($name){
	
		list($entity, $table) = $this->many_to_many_info($name);
		
		$sql = 'SELECT count(*) as count FROM `'.$table.'` where '.static::getTable().' = ?';
		
		$statement = $this->pdo->prepare($sql);
		$statement->execute(array($this->id));
		
		$row = $statement->fetch();
		
		return $row['count'];
		
	}
    
    /* magics methods */
    
    public function __get($key){
        return $this->get($key);
    }
    
    public function __set($key,$value){
        $this->set($key,$value);
    }
    
    public function __isset($key){
        
        if(in_array($key,$this->fields)){
		
			return array_key_exists($key,$this->values);
		
		} else if(in_array($key,$this->i18nFields)){
			
			return array_key_exists($key,$this->i18nValues);
		
		} else {
		
			return isset($this->$key);
		
		}
    }
    
    public function __toString(){
        return static::get_called_classname().'['.$this->id.']';
    }
    
    public function __call($name, $args){
		
		if( strpos($name,"get") === 0 ){ //if the function starts with get with check for onetomany & manyToMany relationships
			
			$name = strtolower(substr($name,3));
			
			$args = array_merge(array($name), $args);
						
			if(array_key_exists($name,$this->oneToMany)){
				return call_user_func_array(array($this,"get_one_to_many"),$args);
			}
			
			if(array_key_exists($name,$this->manyToMany)){
				return call_user_func_array(array($this,"get_many_to_many"),$args);
			}
			
		} else if ( strpos($name,"count") === 0){
		
			$name = strtolower(substr($name,5));
			
			if(array_key_exists($name,$this->oneToMany)){
				return $this->count_one_to_many($name);
			}
			
			if(array_key_exists($name,$this->manyToMany)){
				return $this->count_many_to_many($name);
			}
			
		}
		
	}

}

?>
