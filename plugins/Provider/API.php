<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\Archive;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * @see plugins/Provider/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Provider/functions.php';

/**
 * The Provider API lets you access reports for your visitors Internet Providers.
 *
 * @method static \Piwik\Plugins\Provider\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getProvider($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable(Archiver::PROVIDER_RECORD_NAME);
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'url', __NAMESPACE__ . '\getHostnameUrl'));
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\getPrettyProviderName'));
        $dataTable->filter('AddSegmentValue', array(function ($label) {
            if ($label === Piwik::translate('General_Unknown')) {
                return '';
            }

            return $label;
        }));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }
}
