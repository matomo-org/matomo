<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\UserSettings\Segment;

class Operatingsystem extends VisitDimension
{    
    protected $fieldName = 'config_os';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('operatingSystemCode');
        $segment->setName('UserSettings_ColumnOperatingSystem');
        $segment->setAcceptValues('WXP, WI7, MAC, LIN, AND, IPD, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnOperatingSystem');
    }
}