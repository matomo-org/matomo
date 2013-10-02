<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_LiveTab
 */

namespace Piwik\Plugins\LiveTab;

use Piwik\Piwik;
use Piwik\Common;
use Piwik\Db;

/**
 *
 * @package Piwik_LiveTab
 */
class Model
{
    private $login;

    public function __construct($login)
    {
        $this->login = $login;
    }

    public function getSettings()
    {
        $settings = Piwik_GetOption($this->getKey());

        if (empty($settings)) {
            return array();
        }

        return (array) unserialize($settings);
    }

    public function setSettings($metric, $lastMinutes, $refreshIntervalInMinutes)
    {
        $setting = array(
            'metric'           => $metric,
            'last_minutes'     => $lastMinutes,
            'refresh_interval' => $refreshIntervalInMinutes);

        Piwik_SetOption($this->getKey(), serialize($setting), 1);
    }

    private function getKey()
    {
        return 'LiveTab-settings-' . $this->login;
    }
}
