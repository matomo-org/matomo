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

    public const NO_REPORT_WIDGETS = ['widgetTourgetEngagement', 'widgetMarketplacegetPremiumFeatures', 'widgetRssWidgetrssPiwik',
        'widgetRssWidgetrssChangelog', 'widgetProfessionalServicespromoServices', 'widgetInstallationgetSystemCheck', 'widgetCoreHomequickLinks',
        'widgetCoreHomegetSystemSummary', 'widgetCoreHomegetPromoVideo', 'widgetMarketplacegetNewPlugins', 'widgetReferrersgetCampaignUrlBuilder',
        'widgetCoreHomegetDonateForm'];

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
                $mapping = $this->getEventsWidgetMapping($widgetConfig, $mapping);
                continue;
            } elseif (!$this->shouldMapWidget($widgetConfig)) {
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
            $uniqueId = $widgetConfig->getUniqueId();
            if (in_array($uniqueId, self::NO_REPORT_WIDGETS, true)) {
                continue;
            }
            if (!isset($widgetIdLookup[$uniqueId])) {
                continue;
            }

            $widgetName = $widgetConfig->getName();
            $namesById[$uniqueId] = $widgetName ? Piwik::translate($widgetName) : $uniqueId;
        }

        return $namesById;
    }

    /**
     * @param mixed $layout
     * @return string[]
     */
    public function extractWidgetIdsFromLayout($layout): array
    {
        $columns = $layout;
        if (is_object($layout) && isset($layout->columns)) {
            $columns = $layout->columns;
        } elseif (is_array($layout) && array_key_exists('columns', $layout)) {
            $columns = $layout['columns'];
        }
        if (is_object($columns)) {
            $columns = get_object_vars($columns);
        }
        $widgets = [];
        $seen = [];
        foreach ($columns as $column) {
            if (is_object($column)) {
                $column = get_object_vars($column);
            }
            foreach ($column as $widget) {
                if (!$widget) {
                    continue;
                }
                $uniqueId = $widget->uniqueId ?? null;
                if (!$uniqueId || isset($seen[$uniqueId])) {
                    continue;
                }
                $seen[$uniqueId] = true;
                $widgets[] = $uniqueId;
            }
        }
        return $widgets;
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

    /**
     * Helper function to map how Funnel Widget can be mapped to its report id
     * @param string $widgetId
     * @return string|null
     */
    private function mapFunnelsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetFunnels(funnelReportTable|funnelReport).*?idFunnel(\d+)(?:\D|$)/', $widgetId, $matches)) {
            return null;
        }

        $reportAction = $matches[1] === 'funnelReportTable' ? 'getFunnelFlowTable' : 'getMetrics';

        return 'Funnels_' . $reportAction . '_idFunnel--' . $matches[2];
    }

    /**
     * Helper function to map how Goals Widget can be mapped to its report id
     * @param string $widgetId
     * @return string|null
     */
    private function mapGoalsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetGoal_(\d+)$/', $widgetId, $matches)) {
            return null;
        }

        return 'Goals_get_idGoal--' . $matches[1];
    }

    /**
     * @param EventsByDimension $widgetConfig
     * @param array $mapping
     * @return array
     */
    private function getEventsWidgetMapping(EventsByDimension $widgetConfig, array $mapping)
    {
        foreach ($widgetConfig->getWidgetConfigs() as $configs) {
            $widgetUniqueId = $configs->getUniqueId();
            $reportId = $this->mapEventsWidgetIdToReportId($widgetUniqueId);
            if ($reportId) {
                $mapping[$widgetUniqueId] = $reportId;
            }
        }
        return $mapping;
    }

    /**
     * Helper function to map how Events Widget can be mapped to its report id
     * @param string $widgetId
     * @return string|null
     */
    private function mapEventsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetEventsget(Action|Name|Category)secondaryDimension/', $widgetId, $matches)) {
            return null;
        }

        return 'Events_get' . $matches[1];
    }
}
