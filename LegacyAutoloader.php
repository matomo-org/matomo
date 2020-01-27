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
        if (strpos($className, 'Piwik\\') === 0) {
            $newName = 'Matomo' . substr($className, 5);
            if (class_exists($newName)) {
                class_alias($newName, $className);
            }
        }
    }
}

LegacyAutoloader::register();