<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleReport;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * API for plugin ExampleReport
 *
 * @method static \Piwik\Plugins\ExampleReport\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Another example method that returns a data table.
     *
     * @param string $idSite (might be a number, or the string all)
     */
    public function getExampleReport(string $idSite, string $period, string $date, ?string $segment = null): DataTable
    {
        Piwik::checkUserHasViewAccess($idSite);

        $table = new DataTable();

        $table->addRowFromArray([Row::COLUMNS => ['nb_visits' => 5]]);

        return $table;
    }
}
