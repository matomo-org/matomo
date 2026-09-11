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
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BusinessBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\EnterpriseBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TeamBundleTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\FormPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleActiveSitesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ScheduledReportsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CampaignConversionsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\KeywordsNotDefinedTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManySitesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManyUsersTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MediaOutlinksTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultiplePageVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleSuperusersTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ReturningVisitorsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SlowPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\WooCommerceUrlsTrigger;
use Piwik\Url;
use Piwik\View;

/**
 * Turns the selected promotion into the banner shown above the dashboard widgets.
 */
class PromotionRenderer
{
    /**
     * Campaign name for the outbound link, so promotion clicks can be told apart from
     * other links to the same Marketplace page.
     */
    public const CAMPAIGN_NAME = 'app_premiumplugins';

    /**
     * Campaign group and placement for the outbound link. Every promotion shares them:
     * the group separates triggered promotions from the rest of the app's campaign
     * traffic, and the placement names the one slot they are rendered in.
     */
    public const CAMPAIGN_GROUP = 'triggered_ad';

    public const CAMPAIGN_PLACEMENT = 'top_banner';

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
        // The reason reads as a whole sentence of its own, so it is not wrapped in a
        // "why you're seeing this" lead-in the way the first copy was.
        $view->reason = Piwik::translate($promotion->getReasonTranslationKey());

        // The headline and the call to action lead to the same page, so they carry the
        // same campaign parameters and the link is built once.
        $view->learnMoreUrl = $this->getCampaignUrl($promotion);
        $view->marketplaceUrl = $view->learnMoreUrl;
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
     * The outbound link of the banner, and the only place promotion analytics are carried.
     * No website data is included, only which promotion was clicked and from where.
     *
     * `mtm_kwd` is the one dimension of the scheme that `addCampaignParametersToMatomoLink()`
     * takes no argument for, so the trigger name is put on the URL directly; the helper
     * preserves the query string it is given and only merges its own parameters in.
     */
    private function getCampaignUrl(Promotion $promotion): string
    {
        $url = 'https://plugins.matomo.org/' . $promotion->getPluginName()
            . '?mtm_kwd=' . urlencode($promotion->getTriggerName());

        return (string) Url::addCampaignParametersToMatomoLink(
            $url,
            self::CAMPAIGN_NAME,
            $this->getCampaignSource(),
            $this->getCampaignMedium(),
            self::CAMPAIGN_GROUP,
            $promotion->getPluginName(),
            self::CAMPAIGN_PLACEMENT
        );
    }

    /**
     * Spelled the way the campaign scheme spells it, but still telling Cloud and on
     * premise apart the way core does: an instance reporting the wrong one would be worse
     * than the casing.
     */
    private function getCampaignSource(): string
    {
        return 'matomo_app_' . (Manager::getInstance()->isPluginActivated('Cloud') ? 'cloud' : 'onpremise');
    }

    /**
     * Where in the app the promotion was shown. Null hands the decision back to core,
     * which builds the same thing in its own casing and drops the campaign parameters
     * altogether when there is no module or action to name.
     */
    private function getCampaignMedium(): ?string
    {
        $module = Piwik::getModule();
        $action = Piwik::getAction();

        if (empty($module) || empty($action)) {
            return null;
        }

        return 'app.' . $module . '.' . $action;
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
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                ];

            // The bundle copy names how many premium products are already in use, which
            // is what the trigger counts.
            case TeamBundleTrigger::NAME:
            case BusinessBundleTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                ];

            // The largest bundle is pitched on the size of the team rather than on how
            // many products it already has, so its copy names the user count instead.
            case EnterpriseBundleTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['numUsers'] ?? 0))],
                ];

            // These all name a single count in the body and nothing in the headline.
            case ManySitesTrigger::NAME:
            case ManyUsersTrigger::NAME:
            case MediaOutlinksTrigger::NAME:
            case MultiplePageVisitsTrigger::NAME:
            case MultipleSuperusersTrigger::NAME:
            case KeywordsNotDefinedTrigger::NAME:
            case ReturningVisitorsTrigger::NAME:
            case MultipleActiveSitesTrigger::NAME:
            case ScheduledReportsTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                ];

            case BounceRateTrigger::NAME:
                $bounceRate = $metricsFormatter->getPrettyPercentFromQuotient((float) ($context['bounceRate'] ?? 0));

                return [
                    'title' => [],
                    // A page title, which can be as long as a URL, so it is trimmed the
                    // same way to keep the sentence on one line.
                    'text' => [$bounceRate, $this->truncateUrl((string) ($context['title'] ?? ''))],
                ];

            case FormPageTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [
                        $this->truncateUrl((string) ($context['url'] ?? '')),
                        $numberFormatter->formatNumber((int) ($context['count'] ?? 0)),
                    ],
                ];

            case WooCommerceUrlsTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [$numberFormatter->formatNumber((int) ($context['count'] ?? 0))],
                ];

            case CampaignConversionsTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [
                        (string) ($context['name'] ?? ''),
                        $numberFormatter->formatNumber((int) ($context['count'] ?? 0)),
                    ],
                ];

            case MultipleConversionChannelsTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [
                        (string) ($context['goalName'] ?? ''),
                        $numberFormatter->formatNumber((int) ($context['count'] ?? 0)),
                    ],
                ];

            case SlowPageTrigger::NAME:
                return [
                    'title' => [],
                    'text' => [
                        $this->truncateUrl((string) ($context['url'] ?? '')),
                        // One decimal: "3.4 seconds" reads as a measurement where the raw
                        // float from the archive does not.
                        $numberFormatter->formatNumber(round((float) ($context['loadTime'] ?? 0), 1), 1),
                    ],
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
                return [
                    'title' => [],
                    'text' => [
                        (string) ($context['goalName'] ?? ''),
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
