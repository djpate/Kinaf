<?

	/* kinaf autoload */

    set_include_path(get_include_path().PATH_SEPARATOR.__dir__."/classes".PATH_SEPARATOR.__dir__."/../namespace");
    spl_autoload_extensions('.class.php,.php');
    spl_autoload_register();
    
    /* libs autoload */
    
    include_once(__dir__."/libs/yaml/SfYaml.php");
    include_once(__dir__."/libs/Twig/Autoloader.php");
    
    Twig_Autoloader::register();
    
?>
