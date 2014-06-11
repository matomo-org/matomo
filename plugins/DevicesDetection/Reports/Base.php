<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'DevicesDetection_DevicesDetection';
    }

    protected function getOsRelatedReports()
    {
        return array(
            'DevicesDetection.getOsFamilies' => Piwik::translate('DevicesDetection_OperatingSystemFamilies'),
            'DevicesDetection.getOsVersions' => Piwik::translate('DevicesDetection_OperatingSystemVersions')
        );
    }

    protected function getBrowserRelatedReports()
    {
        return array(
            'DevicesDetection.getBrowserFamilies' => Piwik::translate('UserSettings_BrowserFamilies'),
            'DevicesDetection.getBrowserVersions' => Piwik::translate('DevicesDetection_BrowserVersions')
        );
    }
}
