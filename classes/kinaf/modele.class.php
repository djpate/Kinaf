<?php

namespace kinaf;

abstract class Modele {
	
	protected $id;
	protected $pdo;
	protected $autoSuggestField = "designation";
	protected $errorStack = array();
	protected $classname = __class__;
	protected $orm = array();
	protected static $table;
	
	/* DAO */
	public static function all(){
		$pdo = db::singleton();
		$classname = get_called_class();
		$a = array();
		$r = $pdo->query("select id from ".static::$table);
		foreach($r as $id => $val){
			array_push($a,new $classname($val['id']));
		}
		return $a;
	}
	
	public static function count(){
		$pdo = db::singleton();
		$r = $pdo->query("select count(*) as cnt from ".static::$table)->fetch();
		return $r['cnt'];
	}
	
	/* END DAO */
	
	/* magic */
	
	public function __call($method,$args){
		
		if(!array_key_exists("has_many",$this->orm)){
			new Error("Method ".$method." does not exist");
			exit;
		}
		
		if(!array_key_exists($method,$this->orm['has_many'])){
			new Error("Method ".$method." does not exist");
			exit;
		}
		
		return $this->get_many($this->orm['has_many'][$method]);
		
	}
	
	protected function get_many($type){
		if($this->id!=0){
			return $this->pdo->query("select id from ".$type::$table." where ".ucfirst(strtolower(__class__))." = ".$this->id);
		} else {
			new Error("This object does not have an id");
			exit;
		}
	}
	
	/* end magic */
	
	public function __construct($id=0){
		
		/* loads PDO */ 
		$this->pdo = Db::singleton();
		
		/* loads ORM */
		$yaml = new sfYamlParser();
		if(!is_file(dirname(__file__)."/../orm/".strtolower(static::$table).".yaml")){
			new Error(strtolower(static::$table).".yaml was not found");
		}
		try{
	      $this->orm = $yaml->parse(file_get_contents(dirname(__file__)."/../orm/".strtolower(static::$table).".yaml"));
	    } catch (InvalidArgumentException $e)
	    {
	      new Error("Unable to parse the YAML string: ".$e->getMessage());
	    }
	    
	    if(!(static::$table != null && count($this->orm)>0)){
			new Error("Object definition not valid");
			exit;
		}
		
		if(!is_numeric($id)&&!is_array($id)){
			new Error("Should be an integer or an array");
			exit;
		}
		
		if(is_numeric($id)){
			if($id!=0){
				$r = $this->pdo->query("select id from ".static::$table." where id = ".$id);
				if($r->rowCount()==1){
					$this->id = $id;
					$this->load();
				} else {
					$e = new Error("Specified ID was not found ".$id." on table ".static::$table);
				}
			}
		} else {
			foreach($id as $key => $val){
				if($val=="on"){$val = 1;} // pour les checkbox
				if(array_key_exists($key,$this->orm['fields'])){
					if($this->orm['fields'][$key] == "object"){
						$this->$key = new $key($val);
					} else {
						$this->$key = $val;
					}
				}
			}
		}
	}
	
	public function __get($nom){
		return $this->$nom;
	}
	
	public function __set($nom,$valeur){
		$this->$nom = $valeur;
	}
	
	public function get($nom){
		return $this->$nom;
	}
	
	public function set($nom,$valeur){
		$this->$nom = $valeur;
	}
	
	protected function load(){
		$info = $this->pdo->query("select * from ".static::$table." where id = ".$this->id)->fetch();
		foreach($this->orm['fields'] as $id => $val){
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
	
	public function save(){
		
		if($this->isValid()){
		
			if($this->id==0){ // is this is a new record we create it
				$this->create();
			}
			
			$req = "update ".static::$table." set ";
			
			foreach($this->orm['fields'] as $id => $val){
				
				$req .= "`".$id."` = ";
				
				if($val=="object"&&!is_null($this->$id)){
					$value = $this->$id->get('id');
				} elseif($val=="date") {
					$value = date_fr_to_en($this->$id);
				} else {
					$value = $this->$id;
				}
				
				$req .= $this->pdo->quote(stripslashes($value)).",";
				
			}
			$req = substr($req, 0, -1); 
			$req .= "where id = ".$this->id;
			
			$this->pdo->exec($req);
			
			return true;
		} else {
			return false;
		}
	}
	
	private function create(){
		if($this->id==0){
			$this->pdo->exec("insert into ".static::$table." (id) values ('')");
			$this->id = $this->pdo->lastInsertId(); 
		}
	}
	
	public function isValid(){
		$ret = true;
		if(array_key_exists("constraints",$this->orm)){
			foreach($this->orm['constraints'] as $id => $val){
				foreach($val as $contrainte => $bool){
					if(!method_exists('validation',$contrainte)){
						new Error("Validation ".$contrainte." does not exist");
						$ret = false;
					}
					
					/* on ne fais la validation que si la valeur est required et/ou qu'elle est renseigné */
					
					if(array_key_exists("required",$this->orm['constraints'][$id])||$this->$id!=""){
					
						if(!validation::$contrainte($this->$id)){
							new Error($id." => \"".$this->$id."\" ne valide pas la contrainte ".$contrainte);
							$ret = false;
						}
						
					}
				}
			}
		}
		return $ret;
	}
	
	public function delete(){
		$this->pdo->exec("delete from ".static::$table." where id = ".$this->id);
	}
	
	public function __toString(){
		return $this->id;
	}
	
	public function addError($err){
		array_push($this->errorStack,$err);
	}
	
	public function getNumError(){
		return count($this->errorStack);
	}
	
	public function getLastError(){
		return end($this->errorStack);
	}
		
}
?>
