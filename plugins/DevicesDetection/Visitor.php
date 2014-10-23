<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getDeviceType()
    {
        return getDeviceTypeLabel($this->details['config_device_type']);
    }

    public function getOperatingSystemCode()
    {
        return $this->details['config_os'];
    }

    public function getOperatingSystem()
    {
        return getOsFullName($this->details['config_os']);
    }

    public function getOperatingSystemShortName()
    {
        $shortNameMapping = array(
            'PS3' => 'PS3',
            'PSP' => 'PSP',
            'WII' => 'Wii',
            'WIU' => 'Wii U',
            'NDS' => 'DS',
            'DSI' => 'DSi',
            '3DS' => '3DS',
            'PSV' => 'PS Vita',
            'WI8' => 'Win 8',
            'WI7' => 'Win 7',
            'WVI' => 'Win Vista',
            'WS3' => 'Win S2003',
            'WXP' => 'Win XP',
            'W98' => 'Win 98',
            'W2K' => 'Win 2000',
            'WNT' => 'Win NT',
            'WME' => 'Win Me',
            'W95' => 'Win 95',
            'WPH' => 'WinPhone',
            'WMO' => 'WinMo',
            'WCE' => 'Win CE',
            'WOS' => 'webOS',
        );
        $osShort = $this->details['config_os'];
        if (array_key_exists($osShort, $shortNameMapping)) {
            return $shortNameMapping[$osShort];
        }
        return getOsFullName($osShort);
    }

    public function getOperatingSystemIcon()
    {
        return getBrowserEngineName($this->details['config_os']);
    }

    public function getBrowserEngineDescription()
    {
        return getBrowserEngineName($this->getBrowserEngine());
    }

    public function getBrowserEngine()
    {
        return $this->details['config_browser_engine'];
    }

    public function getBrowserCode()
    {
        return $this->details['config_browser_name'];
    }

    public function getBrowserVersion()
    {
        return $this->details['config_browser_version'];
    }

    public function getBrowser()
    {
        return getBrowserName($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    public function getBrowserIcon()
    {
        return getBrowserLogo($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }
}