<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Common;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;

class DebugView extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Db.getTablesInstalled'                  => 'getTablesInstalled',
            'SitesManager.deleteSite.end'            => 'onSiteDeleted',
        ];
    }

    /**
     * A deleted site's arming option would otherwise linger until it expires
     * and the hourly trim task removes it.
     */
    public function onSiteDeleted($idSite)
    {
        \Piwik\Option::delete(Model\DebugRequests::OPTION_ACTIVE_PREFIX . (int) $idSite);
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function install()
    {
        (new RawRequestLog())->install();
    }

    public function uninstall()
    {
        (new RawRequestLog())->uninstall();
        \Piwik\Option::deleteLike(Model\DebugRequests::OPTION_ACTIVE_PREFIX . '%');
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        // blocks shared by several components
        $stylesheets[] = 'plugins/DebugView/stylesheets/debugview.less';
        // one sibling stylesheet per component
        $stylesheets[] = 'plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.less';
        $stylesheets[] = 'plugins/DebugView/vue/src/MinutesRail/MinutesRail.less';
        $stylesheets[] = 'plugins/DebugView/vue/src/HitsStream/HitsStream.less';
        $stylesheets[] = 'plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.less';
        $stylesheets[] = 'plugins/DebugView/vue/src/HitDetailsPane/DetailRows.less';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $keys = [
            'DebugView_DebugView',
            'DebugView_PageDescription',
            'DebugView_WaitingForRequests',
            'DebugView_WaitingForRequestsHint',
            'DebugView_NewHitsSincePaused',
            'DebugView_Pause',
            'DebugView_Resume',
            'DebugView_StreamPaused',
            'DebugView_StreamLive',
            'DebugView_ParametersTab',
            'DebugView_ProcessedTab',
            'DebugView_ProcessedVisitDetails',
            'DebugView_Redacted',
            'DebugView_TrackingParameters',
            'DebugView_DefaultParameters',
            'DebugView_OtherParameters',
            'DebugView_ProcessedDetails',
            'DebugView_ProcessedCannotBeShown',
            'DebugView_ProcessedNotAvailableBot',
            'DebugView_BotBadge',
            'DebugView_ProcessedAggregatedHint',
            'DebugView_VisitNotAvailable',
            'DebugView_CloseDetails',
            'DebugView_HitDetails',
            'DebugView_MinutesTimeline',
            'DebugView_HitsInMinute',
            'DebugView_OneHitInMinute',
            'DebugView_NoHitsInMinute',
            'DebugView_SecondsStream',
            'DebugView_PollingErrorTitle',
            'DebugView_PollingErrorMessage',
            'DebugView_TypePageview',
            'DebugView_TypeEvent',
            'DebugView_TypeGoal',
            'DebugView_TypeDownload',
            'DebugView_TypeOutlink',
            'DebugView_TypeSearch',
            'DebugView_TypeEcommerceOrder',
            'DebugView_TypeEcommerceAbandonedCart',
            'DebugView_TypeContent',
            'DebugView_TypePing',
            'DebugView_TypeMedia',
            'DebugView_TypeForm',
            'DebugView_TypeSessionRecording',
            'DebugView_TypeCrash',
            'DebugView_TypeVendor',
            'DebugView_SecondsAgoShort',
            'DebugView_MinutesAgoShort',
            'DebugView_SelectedMinute',
            'DebugView_CurrentMinute',
            'DebugView_JumpToMinute',
        ];

        foreach ($keys as $key) {
            $translationKeys[] = $key;
        }
    }

    /**
     * Register the new tables, so Matomo knows about them.
     *
     * @param array $allTablesInstalled
     */
    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable(RawRequestLog::TABLE);
    }
}
