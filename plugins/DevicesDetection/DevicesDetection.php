<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\ArchiveProcessor;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\UserSettings\UserSettings;
use Piwik\Plugins\DevicesDetection\Visitor;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'      => Piwik::translate("DevicesDetection_PluginDescription"),
            'authors'          => array(array('name' => 'Piwik PRO', 'homepage' => 'http://piwik.pro')),
            'version'          => '1.14',
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html'
        );
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Live.getAllVisitorDetails' => 'extendVisitorDetails'
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['deviceType']               = $instance->getDeviceType();
        $visitor['browserFamily']            = $instance->getBrowserEngine();
        $visitor['browserFamilyDescription'] = $instance->getBrowserEngineDescription();

        if (!PluginManager::getInstance()->isPluginActivated('UserSettings')) {
            $instance = new UserSettings();
            $instance->extendVisitorDetails($visitor, $details);
        }
    }

}
