<?php
namespace kinaf;
    abstract class Controller{
        
        protected $controller;
        protected $action;
        protected $pdo;
        protected $layout;
        
        public function __construct($controller,$action){
            
            $this->controller = $controller;
            $this->action = $action;
            $this->layout = new Layout();
            
            $this->pdo = db::singleton();
            
            $this->layout->add("routes",routes::singleton());
            
            $this->preExecute();
        }
        
        protected function preExecute(){
			// this function enables action before any action is executed
			// you just have to define it in your controller
		}
        
        /* render another view by using controller action parameters */
        protected function render_view($controller,$action,$layout=null){
            
            $this->layout->setLayout($layout);
            $this->layout->load($controller."/".$action.".html",$this->variableStack);

        }
        
        /* render the current view */
        protected function render($layout = null){
            
            $this->layout->setLayout($layout);
            $this->layout->load($this->controller."/".$this->action.".html",$this->variableStack);
            
        }
        
    }
?>
