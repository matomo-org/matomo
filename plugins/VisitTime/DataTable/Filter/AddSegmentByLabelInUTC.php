<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\DataTable\Filter;

use Piwik\DataTable;
use Piwik\Period;

/**
 * Adds a segment value to each row by interpreting the label value as hour in the website's timezone and
 * converting the hour to UTC.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSegmentByLabelInUTC', array($idSite = 'UTC+1', $period = 'day', $date = 'today');
 */
class AddSegmentByLabelInUTC extends DataTable\Filter\AddSegmentValue
{
    private $timezone;
    private $date;

    /**
     * @param DataTable $table
     * @param int    $timezone  The timezone of the current selected site / the timezone of the labels
     * @param string $period    The requested period and date is needed to respect daylight saving etc.
     * @param string $date
     */
    public function __construct($table, $timezone, $period, $date)
    {
        $this->timezone = $timezone;
        $this->date = Period\Factory::build($period, $date)->getDateEnd();

        $self = $this;

        parent::__construct($table, function ($label) use ($self) {
            $hour = str_pad($label, 2, 0, STR_PAD_LEFT);

            return $self->convertHourToUtc($hour);
        });
    }

    public function convertHourToUTC($hour)
    {
        $dateWithHour   = $this->date->setTime($hour . ':00:00');
        $dateInTimezone = $dateWithHour->setTimezone($this->timezone);
        $hourInUTC = $dateInTimezone->getHourUTC();

        return $hourInUTC;
    }
}