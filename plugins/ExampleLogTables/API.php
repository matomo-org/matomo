<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Archive;
use Piwik\DataTable\DataTableInterface;
use Piwik\Piwik;
use Piwik\Plugins\ExampleLogTables\RecordBuilders\AdminGroupVisits;

/**
 * API for plugin ExampleLogTables
 *
 * Every public method of an API class is a public HTTP endpoint the moment the plugin is
 * activated. There is no gate above it, so each one checks access for itself.
 *
 * @method static \Piwik\Plugins\ExampleLogTables\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the number of visits made by users belonging to a group flagged as an admin group.
     *
     * @param string      $idSite  (might be a number, or the string all)
     * @param string      $period  day, week, month, year or range
     * @param string      $date    a date or date range the period is resolved against
     * @param string|null $segment an optional segment definition to restrict the metric to
     * @return DataTableInterface one row per period: a DataTable for one, a DataTable\Map for several
     */
    public function getAdminGroupVisits(
        string $idSite,
        string $period,
        string $date,
        ?string $segment = null
    ): DataTableInterface {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);

        return $archive->getDataTableFromNumeric([AdminGroupVisits::NB_VISITS_ADMIN_GROUP_RECORD]);
    }
}
