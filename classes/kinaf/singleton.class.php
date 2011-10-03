<?php

namespace kinaf;

/**
 * Classe Singleton abstraite
 */
abstract class Singleton
{

    /**
     * Renvoie l'instance Singleton de la classe
     */
    //public static function getInstance()
    public static function singleton()
    {
        // Un tableau statique contenant les instances de
        // toutes les classes filles
        static $_instances = array();
        // Récupère le nom de la classe appelée (PHP 5.3, Late Static Binding)
        $classname = get_called_class();
        // Vérifie si l'instance a déjà été chargée
        if (! isset($_instances[$classname])) {
            // Si l'instance n'existe pas on la charge
            $_instances[$classname] = new $classname();
        }
        return $_instances[$classname];
    }

    /**
     * Le constructeur peut être redéclaré dans les classes filles
     * mais sera en protected pour éviter qu'il soit possible de faire
     * $o = new ClasseFille()   (on sera obligé d'utiliser getInstance())
     */
    protected function __construct() {}

    /**
     * On déclare cette méchode en final private pour interdire son
     * utilisation par des classes filles
     */
    final private function __clone() {}

}

?>