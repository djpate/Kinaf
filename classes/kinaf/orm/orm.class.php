<?php
    namespace kinaf\orm;
    
    class orm{
        
    	private static $cache = array();
        private $model;
        private $yaml;
        private $fields;
        private $table = null;
        private $oneToMany = array();
        private $manyToMany = array();
        
        public static function getFromCache($model){
        	if( !isset(self::$cache[$model]) ){
        		self::$cache[$model] = new Orm($model);
        	}
        	return self::$cache[$model];
        }
        
        public function __construct($model){
        	
            $class = explode('\\', $model);
            $this->model = $class[count($class) - 1];
            $this->yaml = new \sfYamlParser();

            $paths = explode(PATH_SEPARATOR, get_include_path());
            $orm_file = '';
            foreach($paths as $path) {
                if(strpos($path, 'namespace') !== false) {
                    $file = realpath($path."/../orm").'/'.strtolower($this->model).".yaml";
                    if(file_exists($file)) {
                        $orm_file = $file;
                        break;
                    }
                }
            }

            if(empty($orm_file)) {
                throw new \Exception("Orm for model ".$this->model." was not found");
            }
            
            try{
            
                $parsed = $this->yaml->parse(file_get_contents($orm_file));
                
                $this->fields = $parsed['fields'];
                
                if(array_key_exists('table',$parsed)){
					$this->table = $parsed['table'];
				}
				
				if(array_key_exists('one_to_many',$parsed)){
					$this->oneToMany = $parsed['one_to_many'];
				}
            
            } catch (\InvalidArgumentException $e){
                
                throw new \Exception("Unable to parse the YAML string: ".$e->getMessage());
            
            }
            
        }
        
        public function getTable(){
			return $this->table;
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
        
        public function getOneToMany(){
			return $this->oneToMany;
		}
		
		public function getManyToMany(){
			return $this->manyToMany;
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
