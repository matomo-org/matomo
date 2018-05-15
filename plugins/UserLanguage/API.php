<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * @see plugins/UserLanguage/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

/**
 * The UserLanguage API lets you access reports about your Visitors language setting
 *
 * @method static \Piwik\Plugins\UserLanguage\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    public function getLanguage($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::LANGUAGE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\groupByLangCallback'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'segment', function($label) {
            if (empty($label) || $label == 'xx') {
                return 'languageCode==xx';
            }
            return sprintf('languageCode==%1$s,languageCode=@%1$s-', $label);
        }));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\languageTranslate'));

        return $dataTable;
    }

    public function getLanguageCode($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::LANGUAGE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\languageTranslateWithCode'));

        return $dataTable;
    }
}
