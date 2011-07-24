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
            
            $dirs = array();
            
            $dirs[] = __DIR__.'/../../../layout/'.$layout;
            $dirs[] = __DIR__.'/../../../views/';
            
            /* load plugins views */
            
            if(is_dir(__DIR__.'/../../../plugins')){
				foreach (new \DirectoryIterator(__DIR__.'/../../../plugins') as $fileInfo) {
					
					if($fileInfo->isDot()) continue;
					
					if($fileInfo->isDir()){
						
						if(is_dir(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/views')){
							$dirs[] = realpath(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/views');
						}
						
						if(is_dir(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/layout')){
							$dirs[] = realpath(__DIR__.'/../../../plugins/'.$fileInfo->getFilename().'/layout');
						}
						
					}
					
				}
			}
            
            
            $this->loader = new \Twig_Loader_Filesystem($dirs);
            $this->twig = new \Twig_Environment($this->loader);
            
        }
        
        
        
        public function load($view,$variableStack){
            /* no need to check something here since the check is done by twig itself */
                $template = $this->twig->loadTemplate($view);
                echo $template->render($variableStack);
        }
        
    }

?>
