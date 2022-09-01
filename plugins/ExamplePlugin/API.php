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
    public function getAnswerToLife($truth = true)
    {
        if ($truth) {
            return 42;
        }

        return 24;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getExampleReport($idSite, $period, $date, $segment = false)
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
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getExampleArchivedMetric($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTableFromNumeric([Archiver::EXAMPLEPLUGIN_METRIC_NAME, Archiver::EXAMPLEPLUGIN_CONST_METRIC_NAME]);
        return $dataTable;
    }

    public function getSegmentHash($idSite, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $segment = new Segment($segment, [(int) $idSite]);
        return $segment->getHash();
    }
}
