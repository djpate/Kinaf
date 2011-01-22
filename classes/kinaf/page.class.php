<?php
namespace kinaf;
	class Page {
		private $routes;
		public function __construct(){
			$this->routes = Routes::singleton();
			$routeInfo = $this->routes->getControllerInfo();
			if(is_array($routeInfo)){
				$controller = '\\controllers\\frontend\\'.$routeInfo['controller'];
				$controller = new $controller($routeInfo['controller'],$routeInfo['action']);
				$method = $routeInfo['action']."Action";
				if(method_exists($controller,$method)){
					if(count($routeInfo['matches'])>1){
						/* A rewrite pour x arguments possibles */
						$controller->$method($routeInfo['matches'][1]);
					} else {
						$controller->$method();
					}
				} else {
					new Error("Action ".$routeInfo['action']." Not found on controller ".$routeInfo['controller']);
				}
			} else {
				new Error("Route ".$routeInfo." not found !");
			}
		}
	}
?>
