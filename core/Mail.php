<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Zend_Mail;

/**
 * Class for sending mails, for more information see:
 * {@link http://framework.zend.com/manual/en/zend.mail.html}
 *
 * @see Zend_Mail, libs/Zend/Mail.php
 * @api
 */
class Mail extends Zend_Mail
{
    /**
     * Constructor.
     *
     * @param string $charset charset, defaults to utf-8
     */
    public function __construct($charset = 'utf-8')
    {
        parent::__construct($charset);
        $this->initSmtpTransport();
    }

    public function setDefaultFromPiwik()
    {
        $customLogo = new CustomLogo();
        $fromEmailName = $customLogo->isEnabled()
            ? Piwik::translate('CoreHome_WebAnalyticsReports')
            : Piwik::translate('ScheduledReports_PiwikReports');
        $fromEmailAddress = Config::getInstance()->General['noreply_email_address'];
        $this->setFrom($fromEmailAddress, $fromEmailName);
    }

    /**
     * Sets the sender.
     *
     * @param string $email Email address of the sender.
     * @param null|string $name Name of the sender.
     * @return Zend_Mail
     */
    public function setFrom($email, $name = null)
    {
        $hostname = Config::getInstance()->mail['defaultHostnameIfEmpty'];
        $piwikHost = Url::getCurrentHost($hostname);

        // If known Piwik URL, use it instead of "localhost"
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $url = parse_url($piwikUrl);
        if (isset($url['host'])
            && $url['host'] != 'localhost'
            && $url['host'] != '127.0.0.1'
        ) {
            $piwikHost = $url['host'];
        }
        $email = str_replace('{DOMAIN}', $piwikHost, $email);
        return parent::setFrom($email, $name);
    }

    /**
     * @return void
     */
    private function initSmtpTransport()
    {
        $mailConfig = Config::getInstance()->mail;
        if (empty($mailConfig['host'])
            || $mailConfig['transport'] != 'smtp'
        ) {
            return;
        }
        $smtpConfig = array();
        if (!empty($mailConfig['type']))
            $smtpConfig['auth'] = strtolower($mailConfig['type']);
        if (!empty($mailConfig['username']))
            $smtpConfig['username'] = $mailConfig['username'];
        if (!empty($mailConfig['password']))
            $smtpConfig['password'] = $mailConfig['password'];
        if (!empty($mailConfig['encryption']))
            $smtpConfig['ssl'] = $mailConfig['encryption'];

        $tr = new \Zend_Mail_Transport_Smtp($mailConfig['host'], $smtpConfig);
        Mail::setDefaultTransport($tr);
        ini_set("smtp_port", $mailConfig['port']);
    }

    public function send($transport = NULL)
    {
        if (defined('PIWIK_TEST_MODE')) { // hack
            Piwik::postTestEvent("Test.Mail.send", array($this));
        } else {
            return parent::send($transport);
        }
    }
}
