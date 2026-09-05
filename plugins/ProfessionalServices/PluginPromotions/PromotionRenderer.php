<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Plugins\Marketplace\SiteAwareLinks;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\ProfessionalServices\Advertising;
use Piwik\Url;
use Piwik\View;

/**
 * Turns the selected promotion into the banner shown above the dashboard widgets.
 */
class PromotionRenderer
{
    /**
     * Campaign medium for the outbound link, so promotion clicks can be told apart from
     * other links to the same Marketplace page.
     */
    public const CAMPAIGN_MEDIUM = 'App.Dashboard.pluginPromotion';

    /**
     * Entry page URLs can be arbitrarily long; keep the headline on one line.
     */
    private const MAX_URL_LENGTH = 60;

    public function render(SelectedPromotion $selected): string
    {
        $promotion = $selected->getPromotion();
        $productName = Piwik::translate($promotion->getProductNameTranslationKey());

        $view = new View('@ProfessionalServices/productPromotion');
        $view->pluginName = $promotion->getPluginName();
        $view->triggerName = $promotion->getTriggerName();
        $view->productName = $productName;
        $view->imageUrl = 'plugins/ProfessionalServices/images/' . $promotion->getImageName();

        $copyArguments = $this->getCopyArguments(
            $promotion->getTriggerName(),
            $selected->getTriggerResult()->getContext()
        );

        $view->title = Piwik::translate($promotion->getTitleTranslationKey(), $copyArguments['title']);
        $view->text = Piwik::translate($promotion->getTextTranslationKey(), $copyArguments['text']);
        $view->reason = Piwik::translate(
            'ProfessionalServices_PromotionReason',
            Piwik::translate($promotion->getReasonTranslationKey())
        );

        $view->learnMoreUrl = $this->getCampaignUrl($promotion);
        $view->marketplaceUrl = (new SiteAwareLinks())->getOverviewUrl($promotion->getPluginName());
        $view->canRequestTrial = $this->canRequestTrial();
        $view->tryLabel = Piwik::translate('ProfessionalServices_PromotionCtaTry', $productName);

        return $view->render();
    }

    /**
     * Super users can start a trial themselves, so they are sent to the Marketplace where
     * the recommended licence tier is already selected for them. Everyone else asks a
     * super user for a trial from the banner. When trial requests are turned off, every
     * role gets the Marketplace link so they can still read about the plugin.
     */
    private function canRequestTrial(): bool
    {
        if (Piwik::hasUserSuperUserAccess()) {
            return false;
        }

        try {
            return StaticContainer::get(PluginTrialService::class)->isEnabled();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * The one outbound link of the banner, and the only place promotion analytics are
     * carried. No website data is included, only which promotion was clicked.
     */
    private function getCampaignUrl(Promotion $promotion): string
    {
        $url = 'https://plugins.matomo.org/' . $promotion->getPluginName()
            . '?trigger_name=' . urlencode($promotion->getTriggerName());

        return (string) Url::addCampaignParametersToMatomoLink(
            $url,
            Advertising::CAMPAIGN_NAME_PROFESSIONAL_SERVICES,
            null,
            self::CAMPAIGN_MEDIUM,
            $promotion->getCampaignContent()
        );
    }

    /**
     * The values the headline and the body need, per trigger.
     *
     * The headline names what the promotion is about and the body carries the figure
     * behind it, so a value can legitimately appear in both.
     *
     * @param array<string, mixed> $context
     * @return array{title: array<int, string>, text: array<int, string>}
     */
    private function getCopyArguments(string $triggerName, array $context): array
    {
        $numberFormatter = NumberFormatter::getInstance();
        $metricsFormatter = new Formatter();

        switch ($triggerName) {
            case SegmentsTrigger::NAME:
                return [
                    'title' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                    'text' => [],
                ];

            case ScheduledReportsTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                ];

            case BounceRateTrigger::NAME:
                $url = $this->truncateUrl((string) ($context['url'] ?? ''));
                $bounceRate = $metricsFormatter->getPrettyPercentFromQuotient((float) ($context['bounceRate'] ?? 0));

                return [
                    'title' => [$url],
                    'text' => [$bounceRate, $url],
                ];

            case LowConversionRateTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [
                        $metricsFormatter->getPrettyPercentFromQuotient((float) ($context['conversionRate'] ?? 0)),
                        (string) ($context['goalName'] ?? ''),
                    ],
                ];

            case HighConversionRateTrigger::NAME:
                $goalName = (string) ($context['goalName'] ?? '');

                return [
                    'title' => [$goalName],
                    'text' => [
                        $goalName,
                        $numberFormatter->formatNumber((int) ($context['nbConversions'] ?? 0)),
                    ],
                ];
        }

        return ['title' => [], 'text' => []];
    }

    private function truncateUrl(string $url): string
    {
        if (mb_strlen($url) <= self::MAX_URL_LENGTH) {
            return $url;
        }

        return mb_substr($url, 0, self::MAX_URL_LENGTH - 1) . '…';
    }
}
