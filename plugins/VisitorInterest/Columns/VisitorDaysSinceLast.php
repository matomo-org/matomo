<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitorInterest\Columns;

use Piwik\Db\Schema;
use Piwik\Plugin\Dimension\VisitDimension;

class VisitorDaysSinceLast extends VisitDimension
{
    protected $category = 'General_Visitors';
    protected $type = self::TYPE_NUMBER;
    protected $nameSingular = 'General_DaysSinceLastVisit';
    protected $columnName = 'visitor_seconds_since_last';
    protected $sqlSegment = 'FLOOR(log_visit.visitor_seconds_since_last / 86400)';
    protected $segmentName = 'daysSinceLastVisit';

    public function getSqlSegment()
    {
        // the declared MySQL expression, unless a subclass or setSqlSegment() replaced it, compares as a number
        if ($this->sqlSegment === 'FLOOR(log_visit.visitor_seconds_since_last / 86400)') {
            return Schema::getInstance()->getIntegerSql($this->sqlSegment);
        }

        return parent::getSqlSegment();
    }
}
