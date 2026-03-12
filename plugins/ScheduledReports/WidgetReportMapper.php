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
            $parameters = $widgetConfig->getParameters();

            // Checking if we have other parameters aside from module and action that we can use
            $reportId = null;
            if (count($parameters) > 2) {
                unset($parameters['module']);
                unset($parameters['action']);
                $reportId = $this->findReportIdByWidgetParameters($widgetKey, $parameters, $reportIndex);
            }
            $reportId = $reportId ?? $this->guessReportIdFromHeuristics(
                $widgetModule,
                $widgetAction,
                $reportIndex,
                $parameters
            );
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
     * @param array<string, mixed> $parameters
     * @param array<string, string> $reportIndex
     */
    private function findReportIdByWidgetParameters(
        string $widgetKey,
        array $parameters,
        array $reportIndex
    ): ?string {
        $scalarParameterValues = [];

        foreach ($parameters as $parameterName => $parameterValue) {
            if (!is_scalar($parameterValue) || is_bool($parameterValue)) {
                continue;
            }

            $parameterValue = (string) $parameterValue;
            $scalarParameterValues[] = $parameterValue;

            $candidateKey = $widgetKey . '.' . (string) $parameterName . '.' . $parameterValue;
            if (isset($reportIndex[$candidateKey])) {
                return $reportIndex[$candidateKey];
            }
        }

        if (count($scalarParameterValues) !== 1) {
            return null;
        }

        $candidateKey = $widgetKey . '.' . $scalarParameterValues[0];
        if (isset($reportIndex[$candidateKey])) {
            return $reportIndex[$candidateKey];
        }

        return null;
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
            if (!$widgetName) {
                $namesById[$uniqueId] = $uniqueId;
                continue;
            }

            $translatedWidgetName = Piwik::translate($widgetName);
            $translatedCategoryName = Piwik::translate($widgetConfig->getCategoryId());
            if (
                !empty($translatedCategoryName)
                && stripos($translatedWidgetName, $translatedCategoryName) !== 0
            ) {
                $translatedWidgetName = $translatedCategoryName . ' ' . $translatedWidgetName;
            }

            $namesById[$uniqueId] = $translatedWidgetName;
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
        if (!is_array($columns)) {
            return [];
        }
        $widgets = [];
        $seen = [];
        foreach ($columns as $column) {
            if (is_object($column)) {
                $column = get_object_vars($column);
            } elseif (!is_array($column)) {
                continue;
            }
            foreach ($column as $widget) {
                if (!$widget) {
                    continue;
                }
                if (is_object($widget)) {
                    $uniqueId = $widget->uniqueId ?? null;
                } elseif (is_array($widget)) {
                    $uniqueId = $widget['uniqueId'] ?? null;
                } else {
                    continue;
                }
                if (!$uniqueId || isset($seen[$uniqueId])) {
                    continue;
                }
                $seen[$uniqueId] = true;
                $widgets[] = $uniqueId;
            }
        }
        return $widgets;
    }

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
                foreach ($reportMeta['parameters'] as $parameterName => $parameterValue) {
                    if (!is_scalar($parameterValue) || is_bool($parameterValue)) {
                        continue;
                    }

                    $parameterValue = (string) $parameterValue;
                    $keyWithName = $key . '.' . (string) $parameterName . '.' . $parameterValue;
                    if (!isset($index[$keyWithName])) {
                        $index[$keyWithName] = $reportMeta['uniqueId'];
                    }

                    $keyByValue = $key . '.' . $parameterValue;
                    if (!isset($index[$keyByValue])) {
                        $index[$keyByValue] = $reportMeta['uniqueId'];
                    }
                }
                continue;
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
     * @param array<string, string> $reportIndex
     * @param array<string, mixed> $parameters
     */
    private function guessReportIdFromHeuristics(
        string $module,
        string $action,
        array $reportIndex,
        array $parameters = []
    ): ?string {
        if ('getEvolutionGraph' !== $action) {
            return null;
        }

        if ($module === 'CustomReports') {
            $reportId = $this->findReportIdByWidgetParameters(
                'CustomReports.getCustomReport',
                $parameters,
                $reportIndex
            );

            if ($reportId !== null) {
                return $reportId;
            }
        }

        $fallbackKey = $module . '.get';
        $reportId = $this->findReportIdByWidgetParameters(
            $fallbackKey,
            $parameters,
            $reportIndex
        );

        if ($reportId !== null) {
            return $reportId;
        }

        return $reportIndex[$fallbackKey] ?? null;
    }

    /**
     * Helper function to map how Funnel Widget can be mapped to its report id
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
     */
    private function mapGoalsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetGoal_(\d+)$/', $widgetId, $matches)) {
            return null;
        }

        return 'Goals_get_idGoal--' . $matches[1];
    }

    /**
     * @param array $mapping
     * @return array
     */
    private function getEventsWidgetMapping(EventsByDimension $widgetConfig, array $mapping): array
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
     */
    private function mapEventsWidgetIdToReportId(string $widgetId): ?string
    {
        if (!preg_match('/^widgetEventsget(Action|Name|Category)secondaryDimension/', $widgetId, $matches)) {
            return null;
        }

        return 'Events_get' . $matches[1];
    }
}
