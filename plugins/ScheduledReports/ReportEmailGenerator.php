<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Mail;

abstract class ReportEmailGenerator
{
    public function makeEmail(GeneratedReport $report, $customReplyTo = null)
    {
        $mail = new ScheduledReportEmail();
        $mail->setDefaultFromPiwik();
        $mail->setSubject($report->getReportDescription());

        if (!empty($customReplyTo)) {
            $mail->addReplyTo($customReplyTo['email'], $customReplyTo['login']);
        }

        $this->configureEmail($mail, $report);

        foreach ($report->getAdditionalFiles() as $additionalFile) {
            $mail->addAttachment(
                $additionalFile['content'],
                $additionalFile['mimeType'],
                $additionalFile['filename'],
                $additionalFile['cid'] ?? null
            );
        }

        return $mail;
    }

    protected abstract function configureEmail(Mail $mail, GeneratedReport $report);
}
