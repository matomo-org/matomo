<?php

class LegacyAutoloader
{
    public function __construct()
    {
        spl_autoload_register(array($this, 'load_class'));
    }

    public static function register()
    {
        new LegacyAutoloader();
    }

    public function load_class($className)
    {
        if (strpos($className, 'Matomo\\') === 0) {
            $newName = 'Piwik' . substr($className, 6);
            if (class_exists($newName)) {
                @class_alias($newName, $className);
            }
        }
    }
}

LegacyAutoloader::register();