<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Piwik;
use Piwik\Plugins\Live\VisitorDetailsAbstract;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['deviceType']               = $this->getDeviceType();
        $visitor['deviceTypeIcon']           = $this->getDeviceTypeIcon();
        $visitor['deviceBrand']              = $this->getDeviceBrand();
        $visitor['deviceModel']              = $this->getDeviceModel();
        $visitor['operatingSystem']          = $this->getOperatingSystem();
        $visitor['operatingSystemName']      = $this->getOperatingSystemName();
        $visitor['operatingSystemIcon']      = $this->getOperatingSystemIcon();
        $visitor['operatingSystemCode']      = $this->getOperatingSystemCode();
        $visitor['operatingSystemVersion']   = $this->getOperatingSystemVersion();
        $visitor['browserFamily']            = $this->getBrowserEngine();
        $visitor['browserFamilyDescription'] = $this->getBrowserEngineDescription();
        $visitor['browser']                  = $this->getBrowser();
        $visitor['browserName']              = $this->getBrowserName();
        $visitor['browserIcon']              = $this->getBrowserIcon();
        $visitor['browserCode']              = $this->getBrowserCode();
        $visitor['browserVersion']           = $this->getBrowserVersion();
    }

    protected function getDeviceType()
    {
        return getDeviceTypeLabel($this->details['config_device_type']);
    }

    protected function getDeviceTypeIcon()
    {
        return getDeviceTypeLogo($this->details['config_device_type']);
    }

    protected function getDeviceBrand()
    {
        return getDeviceBrandLabel($this->details['config_device_brand']);
    }

    protected function getDeviceModel()
    {
        return getModelName($this->details['config_device_model']);
    }

    protected function getOperatingSystemCode()
    {
        return $this->details['config_os'];
    }

    protected function getOperatingSystem()
    {
        return getOsFullName($this->details['config_os'] . ";" . $this->details['config_os_version']);
    }

    protected function getOperatingSystemName()
    {
        return getOsFullName($this->details['config_os']);
    }

    protected function getOperatingSystemVersion()
    {
        return $this->details['config_os_version'];
    }

    protected function getOperatingSystemIcon()
    {
        return getOsLogo($this->details['config_os']);
    }

    protected function getBrowserEngineDescription()
    {
        return getBrowserEngineName($this->getBrowserEngine());
    }

    protected function getBrowserEngine()
    {
        return $this->details['config_browser_engine'];
    }

    protected function getBrowserCode()
    {
        return $this->details['config_browser_name'];
    }

    protected function getBrowserVersion()
    {
        return $this->details['config_browser_version'];
    }

    protected function getBrowser()
    {
        return getBrowserNameWithVersion($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    protected function getBrowserName()
    {
        return getBrowserName($this->details['config_browser_name']);
    }

    protected function getBrowserIcon()
    {
        return getBrowserLogo($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }


    private $devices = array();

    public function initProfile($visits, &$profile)
    {
        $this->devices = array();
    }

    public function handleProfileVisit($visit, &$profile)
    {
        $deviceType     = $visit->getColumn('deviceType');
        $deviceTypeIcon = $visit->getColumn('deviceTypeIcon');
        $deviceBrand    = $visit->getColumn('deviceBrand');
        $deviceModel    = $visit->getColumn('deviceModel');
        if ($deviceBrand == Piwik::translate('General_Unknown')) {
            $deviceName = $deviceModel;
        } else {
            $deviceName = trim($deviceBrand . " " . $deviceModel);
        }

        if (!isset($this->devices[$deviceType])) {
            $this->devices[$deviceType] = array(
                'count'   => 0,
                'icon'    => $deviceTypeIcon,
                'devices' => array()
            );
        }

        ++$this->devices[$deviceType]['count'];

        if (!isset($this->devices[$deviceType]['devices'][$deviceName])) {
            $this->devices[$deviceType]['devices'][$deviceName] = 0;
        }

        ++$this->devices[$deviceType]['devices'][$deviceName];
    }

    public function finalizeProfile($visits, &$profile)
    {
        $devices = $this->devices;
        uksort($this->devices, function ($a, $b) use ($devices) {
            $cmp = strcmp($devices[$b]['count'], $devices[$a]['count']);
            if (0 == $cmp) {
                $cmp = strcmp($a, $b);
            }
            return $cmp;
        });

        $devices = [];

        foreach ($this->devices as $type => $devicesData) {
            $typeDevices = [];
            foreach ($devicesData['devices'] as $name => $count) {
                $typeDevices[] = [
                    'name'  => $name,
                    'count' => $count
                ];
            }
            $devices[] = [
                'type'    => $type,
                'count'   => $devicesData['count'],
                'icon'    => $devicesData['icon'],
                'devices' => $typeDevices,
            ];
        }

        $profile['devices'] = $devices;
    }
}