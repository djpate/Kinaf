<?

    /* kinaf autoload */

    set_include_path(get_include_path().PATH_SEPARATOR.__dir__.'/classes'.PATH_SEPARATOR.realpath(__dir__.'/../namespace'));
    spl_autoload_extensions('.php,.class.php');
    
    /* plugin autoloading */
    
    if(is_dir(__DIR__.'/../plugins')){
		foreach (new DirectoryIterator(__DIR__.'/../plugins') as $fileInfo) {
			
			if($fileInfo->isDot()) continue;
			
			if($fileInfo->isDir()){
				if(is_dir(__DIR__.'/../plugins/'.$fileInfo->getFilename().'/namespace')){
					set_include_path(get_include_path().PATH_SEPARATOR.realpath(__DIR__.'/../plugins/'.$fileInfo->getFilename().'/namespace'));
				}
			}
			
		}
	}
    
    /* yaml autoloading */
    function autoload_yaml($classname){
        
        if (0 !== strpos($classname, 'sfYaml')) {
            return;
        }
        
        require(__dir__.'/libs/yaml/lib/'.$classname.'.php');
        
    }
    
    /* twig */
    
    require(__dir__.'/libs/Twig/lib/Twig/Autoloader.php');
    require(__dir__.'/libs/Twig-extensions/lib/Twig/Extensions/Autoloader.php');
    
    spl_autoload_register();
    spl_autoload_register('autoload_yaml');
    Twig_Autoloader::register();
    Twig_Extensions_Autoloader::register();
    
?>
