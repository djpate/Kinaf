<?

    /* kinaf autoload */

    set_include_path(get_include_path().PATH_SEPARATOR.__dir__.'/classes'.PATH_SEPARATOR.realpath(__dir__.'/../namespace'));
    spl_autoload_extensions('.php,.class.php');
    
    /* yaml autoloading */
    function autoload_yaml($classname){
        
        if (0 !== strpos($classname, 'sfYaml')) {
            return;
        }
        
        require(__dir__.'/libs/yaml/'.$classname.'.php');
        
    }
    
    /* twig */
    
    require(__dir__.'/libs/Twig/Autoloader.php');
    
    spl_autoload_register();
    spl_autoload_register('autoload_yaml');
    Twig_Autoloader::register();
    
?>
