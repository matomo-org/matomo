<?php

return array(
    'observers.global' => DI\add(array(
        array('Request.dispatchCoreAndPluginUpdatesScreen', function () {
            $pluginName = 'GeoIp2';
            $unloadGeoIp2 = \Piwik\Container\StaticContainer::get('test.vars.unloadGeoIp2');
            if ($unloadGeoIp2) {
                $pluginManager = \Piwik\Plugin\Manager::getInstance();
                if ($pluginManager->isPluginActivated($pluginName)
                    && $pluginManager->isPluginLoaded($pluginName)) {
                    $pluginManager->unloadPlugin($pluginName);
                }
            }
        }),
    ))
);