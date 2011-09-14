<?php
namespace kinaf;

use \kinaf\i18n\i18n;
    
    class Page {
        
        private $routes;
        
        public function __construct(){
            
            /* setup i18n */
            
            i18n::bootstrap();
            
            $this->routes = Routes::singleton();
            $routeInfo = $this->routes->getControllerInfo();
            
            if(is_array($routeInfo)){
                
                $controller = '\\controllers\\'.$routeInfo['controller'];
                $controller = new $controller($routeInfo['controller'],$routeInfo['action']);
                $method = $routeInfo['action'];

                if(method_exists($controller,$method)){
                    if(count($routeInfo['matches'])>1){
                        $args = array_slice($routeInfo['matches'],1);
                        call_user_func_array(array($controller,$method),$args);
                    } else {
                        $controller->$method();
                    }
                } else {
                    throw new Exception("Action ".$routeInfo['action']." Not found on controller ".$routeInfo['controller']);
                }
            
            } else {
                /* the route was not found */
                header("HTTP/1.0 404 Not Found");
                exit; 
            }
        }
    }
    
?>
