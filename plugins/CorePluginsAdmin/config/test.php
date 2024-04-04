<?php

return array(
    'observers.global' => Piwik\DI::add(array(
        array('Request.dispatchCoreAndPluginUpdatesScreen', \Piwik\DI::value(function () {
            $pluginName = 'TagManager';
            $unloadTagManager = \Piwik\Container\StaticContainer::get('test.vars.unloadTagManager');
            $tagManagerTeaser = new \Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser(\Piwik\Piwik::getCurrentUserLogin());
            if ($unloadTagManager) {
                $pluginManager = \Piwik\Plugin\Manager::getInstance();
                if (
                    $pluginManager->isPluginActivated($pluginName)
                    && $pluginManager->isPluginLoaded($pluginName)) {
                    $pluginManager->unloadPlugin($pluginName);
                }
                $tagManagerTeaser->reset();
            }
        })),
    ))
);
