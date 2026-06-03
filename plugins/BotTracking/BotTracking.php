<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\BotTracking\Metrics as BotMetrics;
use Piwik\Tracker\Request;

/**
 * BotTracking Plugin
 *
 * Tracks AI assistant and bot interactions without creating visits.
 * Stores telemetry data in dedicated tables for analysis of bot behavior
 * and system performance.
 */
class BotTracking extends Plugin
{
    /**
     * @return bool
     */
    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function registerEvents(): array
    {
        return [
            'AssetManager.getStylesheetFiles'                   => 'getStylesheetFiles',
            'PrivacyManager.deleteLogsOlderThan'                => 'deleteLogsOlderThan',
            'PrivacyManager.deleteDataSubjectsForDeletedSites'  => 'deleteDataSubjectsForDeletedSites',
            'Tracker.isBotRequest'                              => 'isBotRequest',
            'Translate.getClientSideTranslationKeys'            => 'getClientSideTranslationKeys',
            'Metrics.getEvolutionUnit'                          => 'getEvolutionUnit',
            'Metrics.getDefaultMetricTranslations'              => 'addMetricTranslations',
            'Metrics.getDefaultMetricDocumentationTranslations' => 'addMetricDocumentationTranslations',
            'Metrics.getDefaultMetricSemanticTypes'             => 'addMetricSemanticTypes',
        ];
    }

    /**
     * @return void
     */
    public function install()
    {
        (new BotRequestsDao())->createTable();
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        (new BotRequestsDao())->dropTable();
    }

    public function deleteLogsOlderThan(Date $dateUpperLimit): void
    {
        (new BotRequestsDao())->deleteOldRecords($dateUpperLimit);
    }

    /**
     * @param array<string, int> $result
     */
    public function deleteDataSubjectsForDeletedSites(array &$result): void
    {
        $allExistingIdSites = API::getInstance()->getAllSitesId();
        $allExistingIdSites = array_map('intval', $allExistingIdSites);
        $maxIdSite          = max($allExistingIdSites);

        if (empty($maxIdSite)) {
            return;
        }

        $dao                     = new BotRequestsDao();
        $idSitesInTable          = $dao->getDistinctIdSitesInTable($maxIdSite);
        $idSitesNoLongerExisting = array_diff($idSitesInTable, $allExistingIdSites);

        if (count($idSitesNoLongerExisting) > 0) {
            $result[$dao::getTableName()] = $dao->deleteRecordsForIdSites($idSitesNoLongerExisting);
        }
    }

    /**
     * @todo Remove, once Device Detector is able to detect all known ai bots
     */
    public function isBotRequest(bool &$isBot, Request $request): void
    {
        $botDetector = new BotDetector($request->getUserAgent());

        if ($botDetector->isBot()) {
            $isBot = true;
        }
    }

    public function getEvolutionUnit(?string &$unit, string $column): void
    {
        if ($column === Metrics::METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE) {
            $unit = '%';
        }
    }

    /**
     * @param array<string, string> $translations
     */
    public function addMetricTranslations(array &$translations): void
    {
        $translations = array_merge($translations, BotMetrics::getMetricTranslations());

        // Register a default name for the generic 'requests' column used by the new content-URL
        // reports. Without this the Glossary majority heuristic renders name = null for the
        // 'requests' metric because three reports expose it under different per-report scoped
        // documentation strings, leaving no single translation with a majority. Registering the
        // default here mirrors the documentation registration in addMetricDocumentationTranslations.
        if (!isset($translations[Metrics::COLUMN_REQUESTS])) {
            $translations[Metrics::COLUMN_REQUESTS] = Piwik::translate('BotTracking_ColumnRequests');
        }
    }

    /**
     * @param array<string, string> $translations
     */
    public function addMetricDocumentationTranslations(array &$translations): void
    {
        $translations = array_merge($translations, BotMetrics::getMetricDocumentation());

        // Register a default documentation for the generic 'requests' column used by the
        // new content-URL reports. Without this default the Glossary majority heuristic
        // (array_sum - max < max) would not pick a winner because the three reports that
        // expose 'requests' each use a different documentation string (generic, page-scoped,
        // document-scoped), leaving no single value with a majority. Setting a default here
        // ensures the metric always appears in the glossary with the generic tooltip.
        if (!isset($translations[Metrics::COLUMN_REQUESTS])) {
            $translations[Metrics::COLUMN_REQUESTS] = Piwik::translate('BotTracking_ColumnRequestsDocumentation');
        }
    }

    /**
     * @param array<string|int, string> $types
     */
    public function addMetricSemanticTypes(array &$types): void
    {
        $types = array_merge($types, BotMetrics::getMetricSemanticTypes());
    }

    /**
     * @param string[] $translationKeys
     * @return void
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'BotTracking_DetectingYourSite';
        $translationKeys[] = 'BotTracking_SiteWithoutDataBackToMatomo';
        $translationKeys[] = 'BotTracking_SiteWithoutDataChooseTrackingMethod';
        $translationKeys[] = 'BotTracking_SiteWithoutDataChooseTrackingMethodPreamble1';
        $translationKeys[] = 'BotTracking_SiteWithoutDataChooseTrackingMethodPreamble2';
        $translationKeys[] = 'BotTracking_SiteWithoutDataInstallWithX';
        $translationKeys[] = 'BotTracking_SiteWithoutDataNotYetReady';
        $translationKeys[] = 'BotTracking_SiteWithoutDataOtherInstallMethods';
        $translationKeys[] = 'BotTracking_SiteWithoutDataOtherInstallMethodsIntro';
        $translationKeys[] = 'BotTracking_SiteWithoutDataInstallWithXRecommendation';
        $translationKeys[] = 'BotTracking_SiteWithoutDataRecommendationText';
        $translationKeys[] = 'General_ErrorRequest';
        $translationKeys[] = 'General_Refresh';
        $translationKeys[] = 'Mobile_NavigationBack';
        $translationKeys[] = 'BotTracking_NoRecentAIBotRequests';
    }

    /**
     * @param string[] $stylesheets
     * @return void
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/BotTracking/stylesheets/BotTracking.less";
    }
}
