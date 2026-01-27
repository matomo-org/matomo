<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Piwik;
use Piwik\Plugins\API\API as ReportsApi;
use Piwik\Plugins\Events\Widgets\EventsByDimension;
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
     * @var WidgetConfig[]|null
     */
    private $widgetConfigs;

    /**
     * Builds a widget => report map for the supplied site.
     *
     * @param string $idSite
     * @return array<string, string> map of widget unique IDs => report unique IDs
     */
    public function getMappingForSite(string $idSite): array
    {
        $reports = ReportsApi::getInstance()->getReportMetadata($idSite);
        $reportIndex = $this->indexReportsByModuleAndAction($reports);

        $mapping = [];
        foreach ($this->getWidgetConfigs() as $widgetConfig) {
            $widgetUniqueId = $widgetConfig->getUniqueId();
            $goalReportId = $this->mapGoalsWidgetIdToReportId($widgetUniqueId);
            if ($goalReportId) {
                $mapping[$widgetUniqueId] = $goalReportId;
                continue;
            }

            if ($widgetConfig instanceof EventsByDimension) {
                $this->getEventsWidgetMapping($widgetConfig, $mapping);
                continue;
            } else if (!$this->shouldMapWidget($widgetConfig)) {
                continue;
            }

            $widgetModule = $widgetConfig->getModule();
            $widgetAction = $widgetConfig->getAction();
            $widgetKey = $widgetModule . '.' . $widgetAction;

            // Checking if we have other parameters aside from module and action that we can use
            $reportId = null;
            if (count($widgetConfig->getParameters()) > 2) {
                $parameters = $widgetConfig->getParameters();
                unset($parameters['module']);
                unset($parameters['action']);
                foreach ($parameters as $parameter) {
                    $widgetKey .= '.' . $parameter;
                    if (isset($reportIndex[$widgetKey])) {
                        $reportId = $reportIndex[$widgetKey];
                        break;
                    }
                }
            }
            $reportId = $reportId ?? $this->guessReportIdFromHeuristics($widgetModule, $widgetAction, $reportIndex);
            $reportId = $reportId ?? $this->mapFunnelsWidgetIdToReportId($widgetUniqueId);
            if ($reportId === null) {
                continue;
            }

            $mapping[$widgetConfig->getUniqueId()] = $reportId;
        }

        $mappingFromReports = $this->buildMappingFromReportMetadata($reports);
        foreach ($mappingFromReports as $widgetId => $reportId) {
            if (!isset($mapping[$widgetId])) {
                $mapping[$widgetId] = $reportId;
            }
        }

        return $mapping;
    }

    /**
     * @param string[] $widgetIds
     * @return array<string, string>
     */
    public function getWidgetNamesById(array $widgetIds): array
    {
        $namesById = [];
        $widgetIdLookup = array_fill_keys($widgetIds, true);

        foreach ($this->getWidgetConfigs() as $widgetConfig) {
//            if (!$this->shouldMapWidget($widgetConfig)) {
//                continue;
//            }

            $uniqueId = $widgetConfig->getUniqueId();
            if (!isset($widgetIdLookup[$uniqueId])) {
                continue;
            }

            $widgetName = $widgetConfig->getName();
            $namesById[$uniqueId] = $widgetName ? Piwik::translate($widgetName) : $uniqueId;
        }

        return $namesById;
    }
    /**
     * Maps a JSON array of widget unique IDs to Scheduled Reports report IDs.
     *
     * @param array $widgetIds
     * @param string $idSite
     * @return string[]
     */
    public function mapDashboardWidgetsJsonToReportIds(array $widgetIds, string $idSite): array
    {
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

            $reportId = $mapping[$widgetId] ?? null;

            if (null === $reportId) {
                continue;
            }

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
     * @return WidgetConfig[]
     */
    private function getWidgetConfigs(): array
    {
        if ($this->widgetConfigs === null) {
            $this->widgetConfigs = WidgetsList::get()->getWidgetConfigs();
        }

        return $this->widgetConfigs;
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

            if (!empty($reportMeta['parameters']) && is_array($reportMeta['parameters'])) {
                $parameterValue = reset($reportMeta['parameters']);
                $key .= '.' . $parameterValue;
            }

            if (!isset($index[$key])) {
                $index[$key] = $reportMeta['uniqueId'];
            }
        }

        return $index;
    }

    /**
     * @param array<string, mixed>[] $reports
     * @return array<string, string>
     */
    private function buildMappingFromReportMetadata(array $reports): array
    {
        $mapping = [];

        foreach ($reports as $reportMeta) {
            if (empty($reportMeta['module']) || empty($reportMeta['action']) || empty($reportMeta['uniqueId'])) {
                continue;
            }

            $parameters = [];
            if (!empty($reportMeta['parameters']) && is_array($reportMeta['parameters'])) {
                $parameters = $reportMeta['parameters'];
            }

            unset($parameters['module'], $parameters['action']);

            $widgetId = WidgetsList::getWidgetUniqueId($reportMeta['module'], $reportMeta['action'], $parameters);
            if (!isset($mapping[$widgetId])) {
                $mapping[$widgetId] = $reportMeta['uniqueId'];
            }
        }

        return $mapping;
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

    private function mapFunnelsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetFunnels(funnelReportTable|funnelReport).*?idFunnel(\d+)(?:\D|$)/', $widgetId, $matches)) {
            return null;
        }

        $reportAction = $matches[1] === 'funnelReportTable' ? 'getFunnelFlowTable' : 'getMetrics';

        return 'Funnels_' . $reportAction . '_idFunnel--' . $matches[2];
    }

    private function mapGoalsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetGoal_(\d+)$/', $widgetId, $matches)) {
            return null;
        }

        return 'Goals_get_idGoal--' . $matches[1];
    }
    private function getEventsWidgetMapping(EventsByDimension $widgetConfig, array &$mapping)
    {
        foreach ($widgetConfig->getWidgetConfigs() as $configs) {
            $params = $configs->getParameters();
            $widgetUniqueId = $configs->getUniqueId();
            $reportId = $this->mapEventsWidgetIdToReportId($widgetUniqueId);
            if ($reportId) {
                $mapping[$widgetUniqueId] = $reportId;
            }
        }
    }
    private function mapEventsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetEventsget(Action|Name|Category)secondaryDimension/', $widgetId, $matches)) {
            return null;
        }

        return 'Events_get' . $matches[1];
    }
}
