<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\ReportEmailGenerator;

use Piwik\Filesystem;
use Piwik\Mail;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;
use Piwik\ReportRenderer\Html;
use Piwik\SettingsPiwik;
use Piwik\View;

class AttachedFileReportEmailGenerator extends ReportEmailGenerator
{
    /**
     * @var string
     */
    private $attachedFileExtension;

    /**
     * @var string
     */
    private $attachedFileMimeType;

    /**
     * @var string
     */
    private $piwikUrl;

    public function __construct($attachedFileExtension, $attachedFileMimeType, $piwikUrl = null)
    {
        $this->attachedFileExtension = $attachedFileExtension;
        $this->attachedFileMimeType = $attachedFileMimeType;
        $this->piwikUrl = $piwikUrl === null ? SettingsPiwik::getPiwikUrl() : $piwikUrl;
    }

    protected function configureEmail(Mail $mail, GeneratedReport $report)
    {
        $message = $this->getMessageBody($report);
        $mail->setBodyHtml($message);

        $reportFilename = Filesystem::sanitizeFilename($report->getReportDescription());

        $mail->addAttachment(
            $report->getContents(),
            $this->attachedFileMimeType,
            $reportFilename . $this->attachedFileExtension
        );
    }

    private function getMessageBody(GeneratedReport $report)
    {
        $reportDetails = $report->getReportDetails();

        $segment = null;
        if (!empty($reportDetails['idsegment'])) {
            $segment = API::getSegment($reportDetails['idsegment']);
        }

        $headerView = new View\HtmlReportEmailHeaderView(
            $report->getReportTitle(),
            $report->getPrettyDate(),
            $report->getReportDescription(),
            [],
            $segment,
            $reportDetails['idsite'],
            $reportDetails['period']
        );
        $headerView->isAttachedFile = true;

        $footerView = new View\HtmlEmailFooterView(Html::UNSUBSCRIBE_LINK_PLACEHOLDER);

        return $headerView->render() . $footerView->render();
    }
}
