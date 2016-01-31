<?php

namespace Piwik\Tests\Framework\Mock\Plugin;

class Manager extends \Piwik\Plugin\Manager
{
    private $installedPlugins = array();

    public function __construct()
    {
    }

    public function setInstalledPlugins($pluginsList)
    {
        $this->installedPlugins = $pluginsList;
    }

    public function isPluginInstalled($pluginName)
    {
        return in_array($pluginName, $this->installedPlugins);
    }

}
