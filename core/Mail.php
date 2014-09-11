<?php
/**
 * Piwik - free/libre analytics platform
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
        return parent::setFrom(
            $this->parseDomainPlaceholderAsPiwikHostName($email),
            $name
        );
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param null|string $name
     * @return Zend_Mail
     */
    public function setReplyTo($email, $name = null)
    {
        return parent::setReplyTo(
            $this->parseDomainPlaceholderAsPiwikHostName($email),
            $name
        );
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

    /**
     * @param string $email
     * @return string
     */
    protected function parseDomainPlaceholderAsPiwikHostName($email)
    {
        $hostname = Config::getInstance()->mail['defaultHostnameIfEmpty'];
        $piwikHost = Url::getCurrentHost($hostname);

        // If known Piwik URL, use it instead of "localhost"
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $url = parse_url($piwikUrl);
        if ($this->isHostDefinedAndNotLocal($url)) {
            $piwikHost = $url['host'];
        }

        return str_replace('{DOMAIN}', $piwikHost, $email);
    }

    /**
     * @param array $url
     * @return bool
     */
    protected function isHostDefinedAndNotLocal($url)
    {
        return isset($url['host']) && !in_array($url['host'], array('localhost', '127.0.0.1'), true);
    }
}
