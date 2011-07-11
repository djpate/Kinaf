<?php
namespace kinaf;
    abstract class Controller{
        
        protected $controller;
        protected $action;
        protected $variableStack;
        protected $pdo;
        
        public function __construct($controller,$action){
            
            $this->controller = $controller;
            $this->action = $action;
            
            $this->variableStack = array();
            $this->pdo = db::singleton();
            
            $this->add("routes",routes::singleton());
            
            $this->preExecute();
        }
        
        protected function preExecute(){
			// this function enables action before any action is executed
			// you just have to define it in your controller
		}
        
        /* render another view by using controller action parameters */
        protected function render_view($controller,$action,$layout=null){
            
            $layout = new layout($layout);
            $layout->load($controller."/".$action.".html",$this->variableStack);

        }
        
        /* render the current view */
        protected function render($layout = null){
            
            $layout = new layout($layout);
            $layout->load($this->controller."/".$this->action.".html",$this->variableStack);
            
        }
        
        protected function add($key,$value){
            
            if(array_key_exists($key,$this->variableStack)){
                throw new Exception("The key $key is allready in the variable stack");
            }
            
            $this->variableStack[$key] = $value;
            
        }
    }
?>
