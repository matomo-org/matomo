<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CampaignConversionsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\FormPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\KeywordsNotDefinedTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MediaOutlinksTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultiplePageVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ReturningVisitorsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SlowPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\WooCommerceUrlsTrigger;
use Piwik\Url;

/**
 * The report a promotion's figure came from, so the reader can go and look at it.
 *
 * The figure in the copy is the reason the promotion appeared, and on its own it asks to
 * be taken on trust. Linking it to the report it was read from lets the reader check it,
 * and lands them on the same reporting week the figure was taken from rather than on
 * whatever the current one happens to be.
 *
 * Not every promotion has a report behind it: the bundles, the user counts and the custom
 * logo are instance state rather than reporting data, and there is nothing to link them
 * to. Those render their figure as plain text.
 */
class ReportLink
{
    /**
     * Trigger name to the reporting page its figure is read from, as
     * `[categoryId, subcategoryId]`.
     */
    private const REPORTS = [
        BounceRateTrigger::NAME => ['General_Actions', 'Actions_SubmenuPageTitles'],
        FormPageTrigger::NAME => ['General_Actions', 'General_Pages'],
        WooCommerceUrlsTrigger::NAME => ['General_Actions', 'General_Pages'],
        SlowPageTrigger::NAME => ['General_Actions', 'General_Pages'],
        MediaOutlinksTrigger::NAME => ['General_Actions', 'General_Outlinks'],
        MultiplePageVisitsTrigger::NAME => ['General_Actions', 'VisitorInterest_Engagement'],
        ReturningVisitorsTrigger::NAME => ['General_Actions', 'VisitorInterest_Engagement'],
        KeywordsNotDefinedTrigger::NAME => ['Referrers_Referrers', 'Referrers_SubmenuSearchEngines'],
        // The Goals view of the campaigns report, not its default one. This promotion
        // quotes the campaign's goal conversions, and the default view lists visits - so
        // the reader would land on a bigger, unrelated number. The parameter forces the
        // view for this visit only; a stored preference is written by
        // `CoreHome.saveViewDataTableParameters`, which this does not go through.
        CampaignConversionsTrigger::NAME => [
            'Referrers_Referrers',
            'Referrers_Campaigns',
            ['viewDataTable' => 'tableGoals'],
        ],
        MultipleConversionChannelsTrigger::NAME => ['Referrers_Referrers', 'Referrers_WidgetGetAll'],
    ];

    /**
     * The goal promotions open the goal their figure is about rather than a fixed page, so
     * their subcategory is the goal's own id.
     */
    private const GOAL_REPORTS = [
        LowConversionRateTrigger::NAME,
        HighConversionRateTrigger::NAME,
    ];

    /**
     * The in-app URL of the report behind this promotion, or null when it has none.
     *
     * @param array<string, mixed> $context the locked trigger outcome
     */
    public function getUrl(string $triggerName, int $idSite, array $context, ?string $periodStart): ?string
    {
        $page = $this->getPage($triggerName, $context);

        if (null === $page) {
            return null;
        }

        [$category, $subcategory] = $page;
        $extra = $page[2] ?? [];

        $params = [
            'idSite' => $idSite,
            // The week the figure was read from, so the report shows the same number the
            // promotion quotes rather than a newer one.
            'period' => ReportPeriod::PERIOD,
            'date' => $periodStart ?: ReportPeriod::DATE,
            'category' => $category,
            'subcategory' => $subcategory,
        ] + $extra;

        return 'index.php?module=CoreHome&action=index&' . Url::getQueryStringFromParameters([
            'idSite' => $idSite,
            'period' => $params['period'],
            'date' => $params['date'],
        ]) . '#?' . Url::getQueryStringFromParameters($params);
    }

    /**
     * @param array<string, mixed> $context
     * @return array{0: string, 1: string, 2?: array<string, string>}|null
     */
    private function getPage(string $triggerName, array $context): ?array
    {
        if (in_array($triggerName, self::GOAL_REPORTS, true)) {
            $idGoal = (int) ($context['goalId'] ?? 0);

            return $idGoal > 0 ? ['Goals_Goals', (string) $idGoal] : null;
        }

        return self::REPORTS[$triggerName] ?? null;
    }
}
