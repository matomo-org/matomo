<?php

use Piwik\Common;
use Piwik\Config;
use Piwik\Config\IniFileChain;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\DbHelper;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\TestConfig;

require_once PIWIK_INCLUDE_PATH . "/core/Config.php";

if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

class Piwik_MockAccess
{
    private $access;

    public function __construct($access)
    {
        $this->access = $access;
        $access->setSuperUserAccess(true);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->access, $name), $arguments);
    }

    public function reloadAccess($auth = null)
    {
        return true;
    }

    public function getLogin()
    {
        return 'superUserLogin';
    }
}
