<?php

namespace kinaf;

    class layout{
        
        private $loader;
        private $twig;
        private $variable_stack;
        
        public function __construct(){
            
            $this->variableStack = array();
            
        }
        
        public function setLayout($layout){
            
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
            
			if( isset($conf['twig']) ){
                $twig_conf = $conf['twig'];
                if($conf['twig']['cache']) {
                    $twig_conf['cache'] = __DIR__.'/../../../'.$conf['twig']['cache'];
                }
			} else {
				$twig_conf = array();
			}
            
            $this->loader = new \Twig_Loader_Filesystem($dirs);
            
            $this->twig = new \Twig_Environment($this->loader,$twig_conf);
            $this->twig->addExtension(new \kinaf\extensiontwig\NumberFormat());
            $this->twig->addExtension(new \Twig_Extensions_Extension_I18n());
            $this->twig->addExtension(new \Twig_Extensions_Extension_Text());
        }
        
        
        
        public function load($view){
            /* no need to check something here since the check is done by twig itself */
                $template = $this->twig->loadTemplate($view);
                echo $template->render($this->variableStack);
        }
        
        public function add($key, $value){
            $this->variableStack[$key] = $value;
        }
        
        public function addArray(array $values){
            $this->variableStack = array_merge($this->variableStack, $values);
        }
        
    }

?>
