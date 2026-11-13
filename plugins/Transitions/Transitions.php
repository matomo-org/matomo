<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\Transitions\PreprocessedPages\ArchivingMode;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao as PreprocessedPagesDao;

class Transitions extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
            'Template.jsGlobalVariables'             => 'addJsGlobalVariables',
            'UsersManager.deleteUser'                => 'anonymizePreprocessedPagesCreatedBy',
            'SitesManager.deleteSite.end'            => 'deletePreprocessedPagesForSite',
            'CronArchive.archiveSingleSite.finish'   => 'rememberCronArchiving',
        );
    }

    /**
     * Catches up on users and websites deleted while the plugin was deactivated.
     */
    public function activate()
    {
        StaticContainer::get(PreprocessedPagesDao::class)->cleanUpDeletedUsersAndSites();
    }

    public function anonymizePreprocessedPagesCreatedBy(string $userLogin): void
    {
        StaticContainer::get(PreprocessedPagesDao::class)->anonymizeCreatedBy($userLogin);
    }

    public function deletePreprocessedPagesForSite(int $idSite): void
    {
        StaticContainer::get(PreprocessedPagesDao::class)->deletePagesForSite($idSite);
        ArchivingMode::forgetSite($idSite);
    }

    /**
     * @param int|string $idSite
     */
    public function rememberCronArchiving($idSite): void
    {
        ArchivingMode::rememberCronArchiving((int) $idSite);
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = "General_Actions.Transitions_Transitions";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionsReport/TransitionsSourceHeader.less';
        $stylesheets[] = 'plugins/Transitions/vue/src/ManagePreprocessedPages/ManagePreprocessedPages.less';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Transitions/javascripts/transitions.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_TransitionsRowActionTooltipTitle';
        $translationKeys[] = 'General_TransitionsRowActionTooltip';
        $translationKeys[] = 'Actions_PageUrls';
        $translationKeys[] = 'Actions_WidgetPageTitles';
        $translationKeys[] = 'Transitions_NumPageviews';
        $translationKeys[] = 'Transitions_Transitions';
        $translationKeys[] = 'CoreHome_ThereIsNoDataForThisReport';
        $translationKeys[] = 'General_Others';
        $translationKeys[] = 'General_ErrorRequest';
        $translationKeys[] = 'Actions_ActionType';
        $translationKeys[] = 'Transitions_TopX';
        $translationKeys[] = 'Transitions_AvailableInOtherReports';
        $translationKeys[] = 'Actions_SubmenuPageTitles';
        $translationKeys[] = 'Actions_SubmenuPagesEntry';
        $translationKeys[] = 'Actions_SubmenuPagesExit';
        $translationKeys[] = 'Transitions_AvailableInOtherReports2';
        $translationKeys[] = 'Transitions_FeatureDescription';

        // Group titles and center card labels rendered by the Vue TransitionsReport component.
        $translationKeys[] = 'Transitions_IncomingTraffic';
        $translationKeys[] = 'Transitions_OutgoingTraffic';
        $translationKeys[] = 'Transitions_OtherSources';
        $translationKeys[] = 'Transitions_OtherDestinations';
        $translationKeys[] = 'Transitions_FromPreviousPages';
        $translationKeys[] = 'Transitions_FromPreviousSiteSearches';
        $translationKeys[] = 'Transitions_FromSearchEngines';
        $translationKeys[] = 'Transitions_FromSocialNetworks';
        $translationKeys[] = 'Transitions_FromAIAssistants';
        $translationKeys[] = 'Transitions_FromWebsites';
        $translationKeys[] = 'Transitions_FromCampaigns';
        $translationKeys[] = 'Transitions_DirectEntries';
        $translationKeys[] = 'Transitions_ToFollowingPages';
        $translationKeys[] = 'Transitions_ToFollowingSiteSearches';
        $translationKeys[] = 'General_Downloads';
        $translationKeys[] = 'General_Outlinks';
        $translationKeys[] = 'General_ColumnExits';

        // Inline metric labels; each carries a %s placeholder for the formatted metric value.
        $translationKeys[] = 'Transitions_LoopsInline';
        $translationKeys[] = 'Transitions_FromPreviousPagesInline';
        $translationKeys[] = 'Transitions_FromPreviousSiteSearchesInline';
        $translationKeys[] = 'Transitions_ToFollowingPagesInline';
        $translationKeys[] = 'Transitions_ToFollowingSiteSearchesInline';
        $translationKeys[] = 'Transitions_NumDownloads';
        $translationKeys[] = 'Transitions_NumOutlinks';
        $translationKeys[] = 'Transitions_ExitsInline';
        $translationKeys[] = 'Referrers_TypeSearchEngines';
        $translationKeys[] = 'Referrers_TypeSocialNetworks';
        $translationKeys[] = 'Referrers_TypeAIAssistants';
        $translationKeys[] = 'Referrers_TypeWebsites';
        $translationKeys[] = 'Referrers_TypeCampaigns';
        $translationKeys[] = 'Referrers_TypeDirectEntries';

        // Tooltips.
        $translationKeys[] = 'Transitions_XOfAllPageviews';
        $translationKeys[] = 'Transitions_ShareOfAllPageviews';
        $translationKeys[] = 'General_DateRange';

        // Error contract. The API throws these exception names for
        // Transitions.getTransitionsForAction; each has a matching Details variant, and both
        // errors share Transitions_ErrorBack as the back link label.
        $translationKeys[] = 'Transitions_NoDataForAction';
        $translationKeys[] = 'Transitions_NoDataForActionDetails';
        $translationKeys[] = 'Transitions_PeriodNotAllowed';
        $translationKeys[] = 'Transitions_PeriodNotAllowedDetails';
        $translationKeys[] = 'Transitions_ErrorBack';

        // Pre-processed pages: the report's source badge and toggle, and the admin page.
        $translationKeys[] = 'Transitions_PreprocessedBadge';
        $translationKeys[] = 'Transitions_PreprocessedBadgeTooltip';
        $translationKeys[] = 'Transitions_RawDataBadge';
        $translationKeys[] = 'Transitions_PreprocessThisPage';
        $translationKeys[] = 'Transitions_StopPreprocessing';
        $translationKeys[] = 'Transitions_StopPreprocessingConfirm';
        $translationKeys[] = 'Transitions_StopPreprocessingHelp';
        $translationKeys[] = 'Transitions_PreprocessedPagesUsageShort';
        $translationKeys[] = 'Transitions_PreprocessedPagesLimitReachedHelp';
        $translationKeys[] = 'Transitions_ManagePreprocessedPages';
        $translationKeys[] = 'Transitions_PreprocessedPageAddedRebuild';
        $translationKeys[] = 'Transitions_PreprocessedPageAddedNewDataOnly';
        $translationKeys[] = 'Transitions_PreprocessedTransitions';
        $translationKeys[] = 'Transitions_PreprocessedPagesIntro';
        $translationKeys[] = 'Transitions_PreprocessedPagesUsage';
        $translationKeys[] = 'Transitions_PreprocessedPagesEmpty';
        $translationKeys[] = 'Transitions_PreprocessedPagesLimitReached';
        $translationKeys[] = 'Transitions_PreprocessedPagesOverLimit';
        $translationKeys[] = 'Transitions_PreprocessedPagesAddingDisabled';
        $translationKeys[] = 'Transitions_DateTimeUtc';
        $translationKeys[] = 'Transitions_PreprocessedPageAlreadySelected';
        $translationKeys[] = 'Transitions_PreprocessedPageDuplicateHelp';
        $translationKeys[] = 'Transitions_PreprocessedPagesRebuildHint';
        $translationKeys[] = 'Transitions_PreprocessedPagesNewDataOnlyHint';
        $translationKeys[] = 'Transitions_PreprocessedPagesSuggestions';
        $translationKeys[] = 'Transitions_AddPageUrl';
        $translationKeys[] = 'Transitions_AddPage';
        $translationKeys[] = 'Transitions_ColumnDateAdded';
        $translationKeys[] = 'Transitions_ColumnAddedBy';
        $translationKeys[] = 'Transitions_UnknownUserHelp';
        $translationKeys[] = 'Transitions_AddedBySuperUser';
        $translationKeys[] = 'Actions_ColumnPageURL';
        $translationKeys[] = 'General_ColumnPageviews';
        $translationKeys[] = 'General_Remove';
        $translationKeys[] = 'General_Cancel';
        $translationKeys[] = 'General_Unknown';
    }

    public function addJsGlobalVariables(&$out)
    {
        $idSite = Common::getRequestVar('idSite', 1, 'int');
        $maxPeriodAllowed = self::getPeriodAllowedConfig($idSite);

        $out .= '    piwik.transitionsMaxPeriodAllowed = "' . ($maxPeriodAllowed ? $maxPeriodAllowed : 'all') . '"' . "\n";

        // pre-processed pages can be served beyond the period limit; only whether there are any is exposed
        $hasPreprocessedPages = Piwik::isUserHasViewAccess($idSite)
            && StaticContainer::get(PreprocessedPagesDao::class)->hasPagesForSite($idSite);
        $out .= '    piwik.transitionsHasPreprocessedPages = ' . ($hasPreprocessedPages ? 'true' : 'false') . ';' . "\n";
    }

    /**
     * Retrieve the max period allowed config setting for the given site,
     * falling back to the general config when no site is given.
     *
     * @param int|null $idSite
     */
    public static function getPeriodAllowedConfig($idSite): string
    {
        $transitionsGeneralConfig = Config::getInstance()->Transitions;
        $generalMaxPeriodAllowed = ($transitionsGeneralConfig && !empty($transitionsGeneralConfig['max_period_allowed']) ? $transitionsGeneralConfig['max_period_allowed'] : null);

        $siteMaxPeriodAllowed = null;
        if ($idSite) {
            $sectionName = 'Transitions_' . $idSite;
            $transitionsSiteConfig = Config::getInstance()->$sectionName;
            $siteMaxPeriodAllowed = ($transitionsSiteConfig && !empty($transitionsSiteConfig['max_period_allowed']) ? $transitionsSiteConfig['max_period_allowed'] : null);
        }

        if (!$generalMaxPeriodAllowed && !$siteMaxPeriodAllowed) {
            return 'all'; // No config setting, so all periods are valid
        }

        // Site setting overrides general, if it exists
        return $siteMaxPeriodAllowed ?? $generalMaxPeriodAllowed;
    }
}
