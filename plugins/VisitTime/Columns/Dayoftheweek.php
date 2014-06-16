<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;

class DayOfTheWeek extends VisitDimension
{    
    public function getName()
    {
        return Piwik::translate('VisitTime_DayOfWeek');
    }
}