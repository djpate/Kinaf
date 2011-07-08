<?php
	namespace kinaf\orm;
	
	class orm{
		
		private $model;
		private $yaml;
		private $fields;
		
		public function __construct($model){
			
			$orm_dir = __dir__."/../../../orm/";
			
			$this->model = $model;
			$this->yaml = new sfYamlParser();
			
			if(!is_file($orm_dir.strtolower($model).".yaml")){
				
				throw new Exception("Orm for model ".$model." was not found");
			
			}
			
			try{
			
				$this->fields = $this->yaml->parse(file_get_contents($orm_dir.strtolower($model).".yaml"));
				$this->fields = $this->fields['fields'];
			
			} catch (\InvalidArgumentException $e){
				
				throw new Exception("Unable to parse the YAML string: ".$e->getMessage());
			
			}
			
		}
		
		public function getFields($i18n = false){
			$ret = array();
			
			foreach($this->fields as $id => $val){
				
				if($i18n){
				
					if(isset($val['i18n'])&&$val['i18n']==1){
						array_push($ret,$id);
					}
				
				} else {
					if(!isset($val['i18n']) || (isset($val['i18n'])&&$val['i18n']==0)){
						array_push($ret,$id);
					}
				}
				
			}
			return $ret;
		}
		
		public function getType($field){
			return $this->get($field,"type");
		}
		
		public function getConstraints($field){
			return $this->get($field,"constraints");
		}
		
		public function getNamespace($field){
			return $this->get($field,"namespace");
		}
		
		public function getClass($field){
			return $this->get($field,"class");
		}
		
		public function getDisplay($field){
			if(is_null($this->get($field,"display"))){
				return $field;
			} else {
				return $this->get($field,"display");
			}
		}
		
		public function get($field,$conf){
			if(isset($this->fields[$field])){
				if(array_key_exists($conf,$this->fields[$field])){
					return $this->fields[$field][$conf];
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
		
	}
?>
