<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Mail;
use Piwik\Piwik;

class Transport
{
    /**
     * Sends the given mail
     *
     * @param Mail $mail
     * @return bool
     * @throws \DI\NotFoundException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(Mail $mail)
    {
        $phpMailer = new PHPMailer(true);

        //check self-signed config in mail
        $phpMailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => (int)Config::getInstance()->mail['ssl_verify_peer'],
                'verify_peer_name'  => (int)Config::getInstance()->mail['ssl_verify_peer_name'],
                'allow_self_signed' => Config::getInstance()->mail['ssl_disallow_self_signed'] == "1" ? 0 : 1,
            ],
        ];

        PHPMailer::$validator = 'pcre8';
        $phpMailer->CharSet = PHPMailer::CHARSET_UTF8;
        $phpMailer->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
        $phpMailer->XMailer = ' ';
        // avoid triggering automated (vacation) responses
        $phpMailer->addCustomHeader('Auto-Submitted', 'yes');
        $phpMailer->setLanguage(StaticContainer::get('Piwik\Translation\Translator')->getCurrentLanguage());
        $this->initSmtpTransport($phpMailer);

        if ($mail->isSmtpDebugEnabled()) {
            $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        $phpMailer->Subject = $mail->getSubject();

        $htmlContent = $mail->getBodyHtml();
        $textContent = $mail->getBodyText();

        if (!empty($htmlContent)) {
            $phpMailer->msgHTML($htmlContent);

            if (!empty($textContent)) {
                $phpMailer->AltBody = $textContent;
            }
        } else {
            $phpMailer->Body = $textContent;
        }

        $phpMailer->setFrom($mail->getFrom(), $mail->getFromName());

        foreach ($mail->getRecipients() as $address => $name) {
            $phpMailer->addAddress($address, $name);
        }

        foreach ($mail->getBccs() as $address => $name) {
            $phpMailer->addBCC($address, $name);
        }

        foreach ($mail->getReplyTos() as $address => $name) {
            $phpMailer->addReplyTo($address, $name);
        }

        foreach ($mail->getAttachments() as $attachment) {
            if (!empty($attachment['cid'])) {
                $phpMailer->addStringEmbeddedImage(
                    $attachment['content'],
                    $attachment['cid'],
                    $attachment['filename'],
                    PHPMailer::ENCODING_BASE64,
                    $attachment['mimetype']
                );
            } else {
                $phpMailer->addStringAttachment(
                    $attachment['content'],
                    $attachment['filename'],
                    PHPMailer::ENCODING_BASE64,
                    $attachment['mimetype']
                );
            }
        }

        if (defined('PIWIK_TEST_MODE')) { // hack
            /**
             * @ignore
             * @internal
             */
            Piwik::postTestEvent("Test.Mail.send", array($phpMailer));
            return true;
        }

        return $phpMailer->send();
    }

    /**
     * @return void
     */
    private function initSmtpTransport(PHPMailer $phpMailer)
    {
        $mailConfig = Config::getInstance()->mail;

        if (empty($mailConfig['host'])
            || $mailConfig['transport'] != 'smtp'
        ) {
            return;
        }

        $phpMailer->isSMTP();

        if (!empty($mailConfig['type'])) {
            $phpMailer->SMTPAuth = true;
            $phpMailer->AuthType = strtoupper($mailConfig['type']);
        }

        if (!empty($mailConfig['username'])) {
            $phpMailer->Username = $mailConfig['username'];
        }

        if (!empty($mailConfig['password'])) {
            $phpMailer->Password = $mailConfig['password'];
        }

        if (!empty($mailConfig['encryption'])) {
            if (strtolower($mailConfig['encryption']) === 'none') {
                $phpMailer->SMTPAutoTLS = false; // force using no encryption
            } else {
                $phpMailer->SMTPSecure = $mailConfig['encryption'];
            }
        }

        if (!empty($mailConfig['port'])) {
            $phpMailer->Port = $mailConfig['port'];
        }

        $phpMailer->Host = trim($mailConfig['host']);
    }
}
