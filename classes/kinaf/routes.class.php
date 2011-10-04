<?php
namespace kinaf;
    
    class Routes extends Singleton {
        
        protected $routing_array;
        protected $routes;
        
        protected function __construct(){
            $this->routing_array = array();
            $this->routes = array();
            $this->loadRoutes();
        }
        
        public function getControllerInfo(){
            
            $uri = preg_replace("/\?(.*)/","",$_SERVER['REQUEST_URI']);
            
            $matches = array();
                
            if(preg_match("$/(.*)/$",$_SERVER['PHP_SELF'],$matches)){
                $uri = str_replace($matches[0],"",$uri);
            }
            
            $ret = $uri;
            $matches = array();
            foreach($this->routing_array as $id => $val){
                if(preg_match("#^".$id."$#",$uri,$matches)){
                    $ret = array("controller"=>$val['controller'],"action"=>$val['action'],"matches"=>$matches);
                    break;
                }
            }
            return $ret;
        }
        /**
         * Returns the url for the specified controller and action
         * @param string $controller
         * @param string $action
         * @param objet $object 
         * @return string
         */
        public function url_to($controller,$action,$info=null){
            
            if(!array_key_exists($controller,$this->routes)){
                throw new \Exception("Controller ".$controller." not found");
            }
            
            if(!array_key_exists($action,$this->routes[$controller])){
                throw new \Exception("Action ".$action." not found");
            }
            
            /* now we check if there is any variables in the route definition */
            if(preg_match("/{([a-z]+)}/",$this->routes[$controller][$action]['url'])>0){
                
                $url = $this->routes[$controller][$action]['url'];
                
                if( !is_array($info) && !is_object($info) ){
                    throw new \Exception("You forgot to pass the required info for the route !");
                }
                
                $matches = array();
                preg_match_all("/{([a-z]+)}/",$url,$matches);
                
                foreach($matches[0] as $key => $value){
                    
                    $s = $matches[1][$key];
                    
                    if(is_array($info)){

                        if(array_key_exists($s,$info)){
                            
                            $url = str_replace($value,self::slugify($info[$s]),$url);
                            
                        } else {
                        
                            throw new \Exception("The variable $s was not found, therefore the route url could not be formed");
                        
                        }

                        
                    } else if (is_object($info)) {
                        
                        if( isset($info->$s) ){
                            
                            $url = str_replace($value,self::slugify($info->$s),$url);
                            
                        } else {
                        
                            throw new \Exception("The variable $s was not found, therefore the route url could not be formed");
                        
                        }
                        
                    }
           
                }
                
                return $url;
                
            } else{
                return $this->routes[$controller][$action]['url'];
            }
            
        }
        /**
         * Renvoi un lien href vers la bonne url en fonction du controller et d'une action
         * @param string $value Le contenu du lien
         * @param string $controller
         * @param string $action
         * @param objet $object
         * @param string $class
         * @param string $id
         * @param string $title
         * @return string
         */
        public function link_to($value,$controller,$action,$objet=null,$class=null,$id=null,$title=null){
            
            if(!is_null($class)){
                $class = "class=\"".$class."\"";
            }
            
            if(!is_null($id)){
                $id = "id=\"".$id."\"";
            }
            
            if(!is_null($title)){
                $title = "title=\"".$title."\"";
            }
            
            return "<a $class $id $title href=".$this->url_to($controller,$action,$objet).">".$value."</a>";
        }
        /**
         * Redirige vers la bonne url en utilisant header location
         * @param string $controller
         * @param string $action
         * @param objet $object 
         * @return void
         */
        public function redirect_to($controller,$action,$objet=null,$get=""){
            $url = $this->url_to($controller,$action,$objet);
            header("location:".$url.$get);
        }
        
        private function loadRoutes(){
            
            $this->fetchRoutes();
            
            foreach($this->routes as $controller => $actions){
                foreach($actions as $action => $infos){
                    if(preg_match("/{([a-z]+)}/",$infos['url'])>0){
                        
                        $url_reg = $infos['url'];
                        
                        $matches = array();
                        preg_match_all("/{([a-z]+)}/",$infos['url'],$matches);
                        
                        if(is_array($matches[0])){
                            foreach($matches[0] as $k => $v){
                                if(is_array($infos['reg'][$k])){
                                    print_r($infos['reg'][$k]);
                                }
                                $url_reg = str_replace($v,$infos['reg'][$k],$url_reg);
                            }
                        }

                    } else {
                        $url_reg = $infos['url'];
                    }
                    
                    if(!array_key_exists("verb",$infos)){
                        $verb = "GET";
                    } else {
                        $verb = $infos['verb'];
                    }
                    
                    $this->routing_array[$url_reg] = array("controller"=>$controller,"action"=>$action,"verb"=>$verb);
                }
            }
        }
        
        private function fetchRoutes(){
            
            $routing_dir = __dir__.'/../../../routing';
            
            $yaml = new \sfYamlParser();
            
            /* concatenation des tous les fichiers de routings */
            $routing_content = "";
            
            /* load project routing */
            $d = Dir($routing_dir);
            
            while (false !== ($entry = $d->read())) {
                if(pathinfo($entry, PATHINFO_EXTENSION)=="yaml"){
                    $routing_content .= file_get_contents($routing_dir."/".$entry);
                }
            }
            
            /* load plugins routing files */
            
            if(is_dir(__DIR__.'/../../../plugins')){
                
                foreach (new \DirectoryIterator(__DIR__.'/../../../plugins') as $fileInfo) {
                    
                    if($fileInfo->isDot()) continue;
                    
                    if($fileInfo->isDir()){
                        
                        if(is_dir(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/routing')){
                            
                            foreach(new \DirectoryIterator(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/routing') as $routing_file){
                                
                                if($routing_file->isDot()) continue;
                                
                                if($routing_file->isFile()){
                                
                                    if(pathinfo($routing_file->getPathname(), PATHINFO_EXTENSION)=="yaml"){
                                    
                                        $routing_content .= file_get_contents($routing_file->getPathname());
                                    
                                    }
                                    
                                }
                                
                            }
                        
                        }
                        
                    }
                    
                }
            }
            
            try {
            
                $this->routes = $yaml->parse($routing_content);
            
            } catch (\InvalidArgumentException $e) {
                
                new \Error("Unable to parse the YAML string: ".$e->getMessage());
            
            }
            
        }
    
        static private function slugify($text){
            // replace non letter or digits by -
            $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
            
            // trim
            $text = trim($text, '-');
            
            // transliterate
            if (function_exists('iconv'))
            {
                $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            }
            
            // lowercase
            $text = strtolower($text);
            
            // remove unwanted characters
            $text = preg_replace('~[^-\w]+~', '', $text);
            
            if (empty($text))
            {
                return 'n-a';
            }
            
            return $text;
        }
    
    }

?>
