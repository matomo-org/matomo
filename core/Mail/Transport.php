<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Mail;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Mail;
use Piwik\Mail\OAuth\AccessTokenProvider;
use Piwik\Mail\OAuth\TokenRequestFailedException;
use Piwik\Mail\OAuth\XOAuth2Authenticator;
use Piwik\Piwik;

class Transport
{
    /**
     * Sends the given mail
     *
     * @return bool
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
        PHPMailer::setLanguage(StaticContainer::get('Piwik\Translation\Translator')->getCurrentLanguage());
        $this->initSmtpTransport($phpMailer);

        if ($mail->isSmtpDebugEnabled()) {
            $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->hideOAuthCredentialsInDebugOutput($phpMailer);
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

        try {
            if (defined('PIWIK_TEST_MODE')) { // hack
                /**
                 * @ignore
                 * @internal
                 */
                Piwik::postTestEvent("Test.Mail.send", array($phpMailer));
                return true;
            }

            return $phpMailer->send();
        } catch (PHPMailerException $e) {
            if ($phpMailer->AuthType === 'XOAUTH2') {
                // the mail server may have rejected the access token, so the next email requests a new one
                StaticContainer::get(AccessTokenProvider::class)->forgetAccessToken();
            }

            throw $e;
        }
    }

    /**
     * @return void
     */
    private function initSmtpTransport(PHPMailer $phpMailer)
    {
        $mailConfig = Config::getInstance()->mail;

        if (
            empty($mailConfig['host'])
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

        if ($phpMailer->AuthType === 'XOAUTH2') {
            $this->initOAuth($phpMailer);
        } elseif (!empty($mailConfig['password'])) {
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

    /**
     * The access token is requested here, and not when PHPMailer authenticates, because PHPMailer
     * would replace the reason for a failed token request with a generic authentication error.
     */
    private function initOAuth(PHPMailer $phpMailer): void
    {
        if ($phpMailer->Username === '') {
            throw new TokenRequestFailedException(
                'The SMTP OAuth settings are incomplete. Please set username in the [mail] section of the config file'
                . ' to the address of the mailbox that sends the emails.'
            );
        }

        $accessToken = StaticContainer::get(AccessTokenProvider::class)->getAccessToken();

        $phpMailer->setOAuth(new XOAuth2Authenticator($phpMailer->Username, $accessToken));
    }

    /**
     * PHPMailer only hides passwords in the debug output, not the OAuth credentials.
     */
    private function hideOAuthCredentialsInDebugOutput(PHPMailer $phpMailer): void
    {
        $authenticator = $phpMailer->getOAuth();

        if (!$authenticator instanceof XOAuth2Authenticator) {
            return;
        }

        $credentials = $authenticator->getOauth64();
        $isCli = strpos(PHP_SAPI, 'cli') !== false;

        $phpMailer->Debugoutput = static function ($message) use ($credentials, $isCli): void {
            $message = trim(str_replace($credentials, '[credentials hidden]', (string) $message));

            if ($isCli) {
                echo gmdate('Y-m-d H:i:s') . "\t" . $message . "\n";
            } else {
                echo gmdate('Y-m-d H:i:s') . ' ' . htmlentities($message, ENT_QUOTES, 'UTF-8') . "<br>\n";
            }
        };
    }
}
