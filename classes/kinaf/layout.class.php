<?php

namespace kinaf;

    class layout{
        
        private $layout_name;
        
        private $loader;
        private $twig;
        
        private $variable_stack;
        
        public function __construct($layout){
            
            if(is_null($layout)){
                $conf = Configuration::get();
                $layout = $conf['layout']['name'];
            }
            
            $this->loader = new \Twig_Loader_Filesystem(array(__dir__.'/../../../layout/'.$layout,__dir__."/../../../views/"));
            $this->twig = new \Twig_Environment($this->loader);
            
        }
        
        
        
        public function load($view,$variableStack){
            
            if(file_exists(__dir__.'/../../../views/'.$view)){
            
                $template = $this->twig->loadTemplate($view);
                echo $template->render($variableStack);
            
            } else {
                
                throw new \Exception("The view <$view> was not found");
                
            }

        }
        
    }

?>
