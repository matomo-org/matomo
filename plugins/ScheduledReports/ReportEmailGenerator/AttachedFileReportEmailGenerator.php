<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports\ReportEmailGenerator;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\GeneratedReport;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;
use Piwik\SettingsPiwik;
use Piwik\View;
use Zend_Mime;

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
        $message = $this->getMessageBody($report->getReportTitle(), $report->getReportDetails());
        $mail->setBodyText($message);

        $mail->createAttachment(
            $report->getContents(),
            $this->attachedFileMimeType,
            Zend_Mime::DISPOSITION_INLINE,
            Zend_Mime::ENCODING_BASE64,
            $report->getReportDescription() . $this->attachedFileExtension
        );
    }

    private function getMessageBody($reportTitle, $reportDetails)
    {
        $frequency = $this->getReportFrequencyTranslation($reportDetails['period']);

        $view = new View('@ScheduledReports\emailMessageBody');
        $view->piwikUrl = $this->piwikUrl;
        $view->frequency = $frequency;
        $view->reportTitle = $reportTitle;
        $view->reportDetails = $reportDetails;
        if (!empty($reportDetails['idsegment'])) {
            $view->segment = API::getSegment($reportDetails['idsegment']);
        }
        return $view->render();
    }
}
