<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\DataTable\Renderer\Json;
use Piwik\Piwik;
use Piwik\Plugins\BotTracking\BotTrackingMethod\BotTrackingMethodAbstract;
use Piwik\Plugins\BotTracking\Reports\SegmentNotSupportedMessageHelper;
use Piwik\Plugin\Menu;
use Piwik\Plugin\Manager;
use Piwik\Request;
use Piwik\SiteContentDetector;
use Piwik\Url;

class Controller extends \Piwik\Plugin\Controller
{
    public function siteWithoutData(): string
    {
        $this->checkSitePermission();

        $request = Request::fromRequest();
        $period  = $request->getStringParameter('period', '');
        $date    = $request->getStringParameter('date', '');

        return $this->renderTemplateAs('siteWithoutData', [
            'backToMatomoLink' => $this->buildAIBotsOverviewUrl($period, $date),
            'hideWhatIsNew'    => true,
        ], $viewType = 'basic');
    }

    public function getTrackingMethodsForSite(): string
    {
        $this->checkSitePermission();

        $siteContentDetector   = $this->buildSiteContentDetector();
        $trackingMethodClasses = $this->getBotTrackingMethods();
        $detectContent         = [];

        foreach ($trackingMethodClasses as $trackingMethodClass) {
            $contentDetectionId = $trackingMethodClass::getSiteContentDetectionId();
            if ($contentDetectionId !== null && $contentDetectionId !== '') {
                $detectContent[] = $contentDetectionId;
            }
        }

        $siteContentDetector->detectContent($detectContent, $this->idSite);

        $trackingMethods = [];

        foreach ($trackingMethodClasses as $trackingMethodClass) {
            $contentDetectionId    = $trackingMethodClass::getSiteContentDetectionId();
            $contentDetectionClass = null;
            $wasDetected           = false;

            if (!empty($contentDetectionId)) {
                $contentDetectionClass = $siteContentDetector->getSiteContentDetectionById($contentDetectionId);

                if (null === $contentDetectionClass) {
                    continue;
                }

                $wasDetected = $siteContentDetector->wasDetected($contentDetectionId);
            }

            $tabContent = $trackingMethodClass::renderInstructionsTab();
            $link       = $trackingMethodClass::getLink();
            $icon       = $trackingMethodClass::getIcon();

            if (!empty($tabContent) || !empty($link)) {
                if (empty($icon) && $contentDetectionClass) {
                    $icon = $contentDetectionClass->getIcon();
                }

                $trackingMethods[] = [
                    'id'          => $trackingMethodClass::getId(),
                    'name'        => $trackingMethodClass::getName(),
                    'content'     => $tabContent,
                    'icon'        => $icon ?? '',
                    'isOthers'    => $trackingMethodClass::isOthers(),
                    'priority'    => $trackingMethodClass::getPriority(),
                    'wasDetected' => $wasDetected,
                    'link'        => $link,
                ];
            }
        }

        usort($trackingMethods, function ($a, $b) {
            if ($a['wasDetected'] === $b['wasDetected']) {
                return $a['priority'] === $b['priority'] ? 0 : ($a['priority'] < $b['priority'] ? -1 : 1);
            }

            return $a['wasDetected'] ? -1 : 1;
        });

        Json::sendHeaderJSON();
        return json_encode(['trackingMethods' => $trackingMethods]);
    }

    public function getEvolutionGraph(): ?string
    {
        $this->checkSitePermission();

        $columns = [];

        $columnsFromRequest = Request::fromRequest()->getParameter('columns', '');
        if (!empty($columnsFromRequest) && (is_array($columnsFromRequest) || is_string($columnsFromRequest))) {
            $columns = Piwik::getArrayFromApiParameter($columnsFromRequest);
        }

        $documentation = Piwik::translate('BotTracking_ChatbotsOverTimeReportDocumentation') . '<br /><br />';
        $translations  = Metrics::getMetricTranslations();
        $docs          = Metrics::getMetricDocumentation();
        foreach (Metrics::getSparklineMetricOrder() as $metric) {
            if (empty($translations[$metric]) || empty($docs[$metric])) {
                continue;
            }

            $documentation .= sprintf('<b>%s:</b> %s<br />', $translations[$metric], $docs[$metric]);
        }

        $metrics = Metrics::getSparklineMetricOrder();

        if (Request::fromRequest()->getStringParameter('period', '') !== 'day') {
            $metrics = array_filter($metrics, function ($metric) {
                return !in_array($metric, [Metrics::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS]);
            });
        }

        $view = $this->getLastUnitGraphAcrossPlugins(
            $this->pluginName,
            __FUNCTION__,
            $columns,
            $metrics,
            $documentation,
            'BotTracking.get'
        );

        if (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = [Metrics::METRIC_AI_CHATBOTS_REQUESTS];
        }

        SegmentNotSupportedMessageHelper::addSegmentNotSupportedMessage($view);

        return $this->renderView($view);
    }

    public function noRecentRequestsMessage(): string
    {
        $this->checkSitePermission();

        $request = Request::fromRequest();
        $period  = $request->getStringParameter('period', '');
        $date    = $request->getStringParameter('date', '');

        $noDataParams = [
            'module' => 'BotTracking',
            'action' => 'siteWithoutData',
            'idSite' => $this->idSite,
        ];

        if ($period !== '') {
            $noDataParams['period'] = $period;
        }

        if ($date !== '') {
            $noDataParams['date'] = $date;
        }

        $noDataUrl = 'index.php?' . Url::getQueryStringFromParameters($noDataParams);

        return $this->renderTemplate('noRecentRequestsMessage', [
            'noDataUrl' => $noDataUrl,
        ]);
    }

    public function showNoRecentRequestsMessage(): string
    {
        $this->checkSitePermission();

        $request = Request::fromRequest();
        $period  = $request->getStringParameter('period', '');
        $date    = $request->getStringParameter('date', '');

        Json::sendHeaderJSON();

        return json_encode(
            NoRecentRequestsMessage::shouldShow($this->idSite, $period, $date)
        );
    }

    /**
     * @return array<class-string<BotTrackingMethodAbstract>>
     */
    protected function getBotTrackingMethods(): array
    {
        return Manager::getInstance()->findMultipleComponents(
            'BotTrackingMethod',
            BotTrackingMethodAbstract::class
        );
    }

    protected function buildSiteContentDetector(): SiteContentDetector
    {
        return new SiteContentDetector();
    }

    protected function buildAIBotsOverviewUrl(string $period, string $date): string
    {
        $menu             = new Menu();
        $params           = $menu->urlForDefaultUserParams($this->idSite);
        $params['module'] = 'CoreHome';
        $params['action'] = 'index';

        if ($period !== '') {
            $params['period'] = $period;
        }

        if ($date !== '') {
            $params['date'] = $date;
        }

        $hashParams                = $params;
        $hashParams['category']    = 'General_AIAssistants';
        $hashParams['subcategory'] = 'BotTracking_AIChatbotsOverview';

        return 'index.php?'
            . Url::getQueryStringFromParameters($params)
            . '#?'
            . Url::getQueryStringFromParameters($hashParams);
    }
}
