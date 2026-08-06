<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Common;
use Piwik\Log;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

class BanIpNotificationEmail
{
    public function send($ipRange, $ip, $email, $maxActionsAllowed, $locationData, $nowDateTime)
    {
        if (empty($email) || !Piwik::isValidEmailString($email)) {
            return;
        }

        $mail = new Mail();
        $mail->addTo($email);
        $mail->setSubject('An IP was banned as too many actions were tracked.');
        $mail->setDefaultFromPiwik();
        if (
            empty($mail->getFromName()) || in_array($mail->getFromName(), [
                'CoreHome_WebAnalyticsReports',
                'TagManager_MatomoTagName'
            ])
        ) {
            $mail->setFrom($mail->getFrom(), 'Web Analytics Reports');
        }


        $mailBody = 'This is for your information. The following IP was banned because visit tried to track more than ' . Common::sanitizeInputValue($maxActionsAllowed) . ' actions:';
        $mailBody .= PHP_EOL . PHP_EOL . '"' . Common::sanitizeInputValue($ipRange) . '"' . PHP_EOL;
        $instanceId = SettingsPiwik::getPiwikInstanceId();
        $matomoUrl = SettingsPiwik::getPiwikUrl();
        if (!empty($matomoUrl)) {
            $url = parse_url($matomoUrl);
            $matomoHost = $url['host'];
        }

        if (!empty($_GET)) {
            $get = $_GET;
            if (isset($get['token_auth'])) {
                $get['token_auth'] = 'XYZANONYMIZED';
            }
        } else {
            $get = [];
        }

        if (!empty($_POST)) {
            $post = $_POST;
            if (isset($post['token_auth'])) {
                $post['token_auth'] = 'XYZANONYMIZED';
            }
        } else {
            $post = [];
        }

        if (!empty($instanceId)) {
            $mailBody .= PHP_EOL . 'Instance ID: ' . Common::sanitizeInputValue($instanceId);
        }
        if (!empty($matomoHost)) {
            $mailBody .= PHP_EOL . 'URL: ' . Common::sanitizeInputValue($matomoHost);
        }
        $mailBody .= PHP_EOL . 'Current date (UTC): ' . Common::sanitizeInputValue($nowDateTime) . '
IP as detected in header: ' . Common::sanitizeInputValue($ip) . '
GET request info: ' . json_encode($get) . '
POST request info: ' . json_encode($post) . PHP_EOL;

        if (!empty($locationData)) {
            $mailBody .= 'Geo IP info: ' . json_encode($locationData) . PHP_EOL;
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $mailBody .= 'User Agent: ' . Common::sanitizeInputValue($_SERVER['HTTP_USER_AGENT']) . PHP_EOL;
        }

        $mail->setBodyText($mailBody);

        $testMode = (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE);
        if ($testMode) {
            Log::info($mail->getSubject() . ':' . $mail->getBodyText());
        } else {
            $mail->send();
        }

        $a = $mail->getBodyText();

        return $mail->getBodyText();
    }
}
