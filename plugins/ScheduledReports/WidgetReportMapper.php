<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Plugins\API\API as ReportsApi;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

/**
 * Utility that builds a map between dashboard widgets and scheduled-report definitions.
 *
 * The mapper works with the widget metadata that Matomo exposes and attempts to match
 * each widget with a report by comparing the widget's module/action pair against the
 * available report metadata (via API.getReportMetadata). Some widgets call controller
 * actions such as {@code getEvolutionGraph} instead of the raw API method. For those
 * cases we ship a couple of fallbacks and a configurable override list so that adding
 * new widgets/reports later on only requires editing one class.
 */
class WidgetReportMapper
{
    /**
     * Manual overrides for widgets that cannot be matched automatically.
     *
     * The array key is a widget module/action pair (eg, 'UserCountry.getDistinctCountries').
     * The value is the report unique ID that Scheduled Reports uses (eg, 'UserCountry_getCountry').
     *
     * Add entries here whenever a new widget controller action cannot be resolved through
     * the default heuristics.
     */
    private const SPECIAL_CASES = [
        // 'UserCountry.getDistinctCountries' => 'UserCountry_getCountry',
    ];

    /**
     * Builds a widget => report map for the supplied site.
     *
     * @param int $idSite
     * @return array<string, string> map of widget unique IDs => report unique IDs
     */
    public function getMappingForSite(int $idSite): array
    {
        $reports = ReportsApi::getInstance()->getReportMetadata($idSite);
        $reportIndex = $this->indexReportsByModuleAndAction($reports);

        $mapping = [];

        foreach (WidgetsList::get()->getWidgetConfigs() as $widgetConfig) {
            if (!$this->shouldMapWidget($widgetConfig)) {
                continue;
            }

            $widgetModule = $widgetConfig->getModule();
            $widgetAction = $widgetConfig->getAction();
            $widgetKey = $widgetModule . '.' . $widgetAction;

            $reportId = $reportIndex[$widgetKey] ?? null;

            if (null === $reportId) {
                $reportId = $this->guessReportIdFromHeuristics($widgetModule, $widgetAction, $reportIndex);
            }

            if (null === $reportId) {
                $reportId = self::SPECIAL_CASES[$widgetKey] ?? null;
            }

            if (null === $reportId) {
                continue;
            }

            $mapping[$widgetConfig->getUniqueId()] = $reportId;
        }

        return $mapping;
    }

    /**
     * Maps a JSON array of widget unique IDs to Scheduled Reports report IDs.
     *
     * @param [] $dashboardWidgetsJson JSON array of widget unique IDs (eg, ["widgetId1","widgetId2"])
     * @param int $idSite
     * @return string[]
     */
    public function mapDashboardWidgetsJsonToReportIds(array $widgetIds, int $idSite): array
    {
//        $widgetIds = json_decode($dashboardWidgetsJson, true);

        if (!is_array($widgetIds)) {
            return [];
        }

        $mapping = $this->getMappingForSite($idSite);
        $reportIds = [];

        foreach ($widgetIds as $widgetId) {
            if (!is_string($widgetId) && !is_int($widgetId)) {
                continue;
            }

            $widgetId = (string) $widgetId;

            if (!isset($mapping[$widgetId])) {
                continue;
            }

            $reportId = $mapping[$widgetId];

            if (!in_array($reportId, $reportIds, true)) {
                $reportIds[] = $reportId;
            }
        }

        return $reportIds;
    }

    /**
     * @param WidgetConfig $config
     */
    private function shouldMapWidget(WidgetConfig $config): bool
    {
        if (!$config->isWidgetizeable()) {
            return false;
        }

        return $config instanceof ReportWidgetConfig;
    }

    /**
     * @param array<string, mixed>[] $reports
     * @return array<string, string>
     */
    private function indexReportsByModuleAndAction(array $reports): array
    {
        $index = [];

        foreach ($reports as $reportMeta) {
            if (empty($reportMeta['module']) || empty($reportMeta['action']) || empty($reportMeta['uniqueId'])) {
                continue;
            }

            $key = $reportMeta['module'] . '.' . $reportMeta['action'];

            if (!isset($index[$key])) {
                $index[$key] = $reportMeta['uniqueId'];
            }
        }

        return $index;
    }

    /**
     * @param string $module
     * @param string $action
     * @param array<string, string> $reportIndex
     * @return string|null
     */
    private function guessReportIdFromHeuristics(string $module, string $action, array $reportIndex): ?string
    {
        if ('getEvolutionGraph' === $action) {
            $fallbackKey = $module . '.get';
            return $reportIndex[$fallbackKey] ?? null;
        }

        return null;
    }
}
