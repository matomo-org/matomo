<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Mail;
use Piwik\Piwik;
use Zend_Mime;

abstract class ReportEmailGenerator
{
    public function makeEmail(GeneratedReport $report, $customReplyTo = null)
    {
        $mail = new Mail();
        $mail->setDefaultFromPiwik();
        $mail->setSubject($report->getReportDescription());

        if (!empty($customReplyTo)) {
            $mail->setReplyTo($customReplyTo['email'], $customReplyTo['alias']);
        }

        $this->configureEmail($mail, $report);

        foreach ($report->getAdditionalFiles() as $additionalFile) {
            $fileContent = $additionalFile['content'];
            $at = $mail->createAttachment(
                $fileContent,
                $additionalFile['mimeType'],
                Zend_Mime::DISPOSITION_INLINE,
                $additionalFile['encoding'],
                $additionalFile['filename']
            );
            $at->id = $additionalFile['cid'];

            unset($fileContent);
        }

        return $mail;
    }

    protected abstract function configureEmail(Mail $mail, GeneratedReport $report);
}
