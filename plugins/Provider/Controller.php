<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Provider
 */
use Piwik\Controller;
use Piwik\ViewDataTable;

/**
 *
 * @package Piwik_Provider
 */
class Piwik_Provider_Controller extends Controller
{
    /**
     * Provider
     * @param bool $fetch
     * @return string|void
     */
    public function getProvider($fetch = false)
    {
        return Piwik_ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}

