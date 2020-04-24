<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\Diagnostics\Diagnostic\CronArchivingLastRunCheck;
use Piwik\View;

class Diagnostics extends Plugin
{
    const NO_DATA_ARCHIVING_NOT_RUN_NOTIFICATION_ID = 'DiagnosticsNoDataArchivingNotRun';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Visualization.onNoData' => ['function' => 'onNoData', 'before' => true],
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Diagnostics/stylesheets/configfile.less";
    }

    public function onNoData(View $dataTableView)
    {
        if (!Piwik::isUserHasSomeAdminAccess()) {
            return;
        }

        if (Rules::isBrowserTriggerEnabled()) {
            return;
        }

        $lastSuccessfulRun = CronArchivingLastRunCheck::getTimeSinceLastSuccessfulRun();
        if ($lastSuccessfulRun > CronArchivingLastRunCheck::SECONDS_IN_DAY) {
            $content = Piwik::translate('Diagnostics_NoDataForReportArchivingNotRun', [
                '<a href="https://matomo.org/docs/setup-auto-archiving/" target="_blank" rel="noreferrer noopener">',
                '</a>',
            ]);

            $notification = new Notification($content);
            $notification->priority = Notification::PRIORITY_HIGH;
            $notification->context = Notification::CONTEXT_INFO;
            $notification->flags = Notification::FLAG_NO_CLEAR;
            $notification->type = Notification::TYPE_TRANSIENT;
            $notification->raw = true;

            $dataTableView->notifications[self::NO_DATA_ARCHIVING_NOT_RUN_NOTIFICATION_ID] = $notification;
        }
    }
}
