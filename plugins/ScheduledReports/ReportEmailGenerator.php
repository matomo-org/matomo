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
        $mail = new Mail();
        $mail->setDefaultFromPiwik();
        $mail->setSubject($report->getReportDescription());

        if (!empty($customReplyTo)) {
            $mail->addReplyTo($customReplyTo['email'], $customReplyTo['login']);
        }

        $this->configureEmail($mail, $report);

        foreach ($report->getAdditionalFiles() as $additionalFile) {
            $fileContent = $additionalFile['content'];
            $mail->createAttachment(
                $fileContent,
                $additionalFile['mimeType'],
                'inline',
                $additionalFile['encoding'],
                $additionalFile['filename']
            );

            unset($fileContent);
        }

        return $mail;
    }

    protected abstract function configureEmail(Mail $mail, GeneratedReport $report);
}
