<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Columns\Os;
use Piwik\Plugins\UserSettings\Segment;

class Operatingsystem extends Os
{
    protected $columnName = 'config_os';
    protected $columnType = 'CHAR(3) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('operatingSystemCode');
        $segment->setName('UserSettings_ColumnOperatingSystem');
        $segment->setAcceptedValues('WXP, WI7, MAC, LIN, AND, IPD, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnOperatingSystem');
    }
}