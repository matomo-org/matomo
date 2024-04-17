<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

class Environment extends \Piwik\Plugins\Marketplace\Environment
{
    public function __construct()
    {
    }

    public function getNumUsers()
    {
        return 5;
    }

    public function getNumWebsites()
    {
        return 21;
    }

    public function getPhpVersion()
    {
        return '7.0.1';
    }

    public function getPiwikVersion()
    {
        return '2.16.3';
    }

    public function doesPreferStable()
    {
        return true;
    }

    public function getReleaseChannel()
    {
        return 'latest_stable';
    }

    public function getMySQLVersion()
    {
        return '5.7.1';
    }
}
