<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\ExamplePlugin\RecordBuilders\ExampleMetric;
use Piwik\Plugins\ExamplePlugin\RecordBuilders\ExampleMetric2;
use Piwik\Segment;

/**
 * API for plugin ExamplePlugin
 *
 * @method static \Piwik\Plugins\ExamplePlugin\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Example method. Please remove if you do not need this API method.
     * You can call this API method like this:
     * /index.php?module=API&method=ExamplePlugin.getAnswerToLife
     * /index.php?module=API&method=ExamplePlugin.getAnswerToLife&truth=0
     *
     * @param  bool $truth
     *
     * @return int
     */
    public function getAnswerToLife(bool $truth = true): int
    {
        if ($truth) {
            return 42;
        }

        return 24;
    }

    /**
     * Another example method that returns a data table.
     * @param string $idSite  (might be a number, or the string all)
     * @param string $period
     * @param string $date
     * @param null|string $segment
     * @return DataTable
     */
    public function getExampleReport(string $idSite, string $period, string $date, ?string $segment = null): DataTable
    {
        Piwik::checkUserHasViewAccess($idSite);

        $table = DataTable::makeFromSimpleArray(array(
            array('label' => 'My Label 1', 'nb_visits' => '1'),
            array('label' => 'My Label 2', 'nb_visits' => '5'),
        ));

        return $table;
    }

    /**
     * Returns the example metric we archive in Archiver.php.
     * @param string $idSite (might be a number, or the string all)
     * @param string $period
     * @param string $date
     * @param null|string $segment
     * @return DataTable\DataTableInterface
     */
    public function getExampleArchivedMetric(string $idSite, string $period, string $date, ?string $segment = null): DataTable\DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTableFromNumeric([ExampleMetric::EXAMPLEPLUGIN_METRIC_NAME, ExampleMetric2::EXAMPLEPLUGIN_CONST_METRIC_NAME]);
    }

    public function getSegmentHash(string $idSite, string $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $segment = new Segment($segment, [$idSite]);
        return $segment->getHash();
    }
}
