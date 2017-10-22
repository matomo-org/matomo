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
        $message = "\n";

        $frequency = $this->getReportFrequencyTranslation($reportDetails['period']);
        $message .= Piwik::translate('ScheduledReports_PleaseFindAttachedFile', [
            $frequency,
            $reportTitle,
        ]);
        $message .= "\n";

        if (!empty($this->piwikUrl)) {
            $message .= Piwik::translate('ScheduledReports_SentFromX', $this->piwikUrl);
        }

        if (!empty($reportDetails['idsegment'])) {
            $segment = API::getSegment($reportDetails['idsegment']);
            if ($segment != null) {
                $message .= " " . Piwik::translate('ScheduledReports_SegmentAppliedToReports', $segment['name']);
            }
        }

        return $message;
    }
}
