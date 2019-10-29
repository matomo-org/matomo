<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Email\ContentGenerator;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Translation\Translator;
use Piwik\View\HtmlReportEmailHeaderView;
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

        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        $fromEmailName = Config::getInstance()->General['noreply_email_name'];

        if (empty($fromEmailName) && $customLogo->isEnabled()) {
            $fromEmailName = $translator->translate('CoreHome_WebAnalyticsReports');
        } elseif (empty($fromEmailName)) {
            $fromEmailName = $translator->translate('TagManager_MatomoTagName');
        }

        $fromEmailAddress = Config::getInstance()->General['noreply_email_address'];
        $this->setFrom($fromEmailAddress, $fromEmailName);
    }

    /**
     * @param View|string $body
     * @throws \DI\NotFoundException
     */
    public function setWrappedHtmlBody($body)
    {
        $contentGenerator = StaticContainer::get(ContentGenerator::class);
        $bodyHtml = $contentGenerator->generateHtmlContent($body);
        $this->setBodyHtml($bodyHtml);
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
        $tr = StaticContainer::get('Zend_Mail_Transport_Abstract');
        if (empty($tr)) {
            return;
        }

        Mail::setDefaultTransport($tr);
    }

    public function send($transport = null)
    {
        if (!$this->shouldSendMail()) {
            return $this;
        }

        $mail = $this;

        /**
         * This event is posted right before an email is sent. You can use it to customize the email by, for example, replacing
         * the subject/body, changing the from address, etc.
         *
         * @param Mail $this The Mail instance that is about to be sent.
         */
        Piwik::postEvent('Mail.send', [$mail]);

        if (defined('PIWIK_TEST_MODE')) { // hack
            /**
             * @ignore
             * @deprecated
             */
            Piwik::postTestEvent("Test.Mail.send", array($this));
        } else {
            return parent::send($transport);
        }
    }

    public function createAttachment($body, $mimeType = null, $disposition = null, $encoding = null, $filename = null)
    {
        $filename = $this->sanitiseString($filename);
        return parent::createAttachment($body, $mimeType, $disposition, $encoding, $filename);
    }

    public function setSubject($subject)
    {
        $subject = $this->sanitiseString($subject);
        return parent::setSubject($subject);
    }

    public function getMailHost()
    {
        $hostname  = Config::getInstance()->mail['defaultHostnameIfEmpty'];
        $piwikHost = Url::getCurrentHost($hostname);

        // If known Piwik URL, use it instead of "localhost"
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        $url      = parse_url($piwikUrl);
        if ($this->isHostDefinedAndNotLocal($url)) {
            $piwikHost = $url['host'];
        }
        return $piwikHost;
    }

    /**
     * @param string $email
     * @return string
     */
    protected function parseDomainPlaceholderAsPiwikHostName($email)
    {
        $piwikHost = $this->getMailHost();
        return str_replace('{DOMAIN}', $piwikHost, $email);
    }

    /**
     * @param array $url
     * @return bool
     */
    protected function isHostDefinedAndNotLocal($url)
    {
        return isset($url['host']) && !Url::isLocalHost($url['host']);
    }

    /**
     * Replaces characters known to appear incorrectly in some email clients
     *
     * @param $string
     * @return mixed
     */
    function sanitiseString($string)
    {
        $search = array('–', '’');
        $replace = array('-', '\'');
        $string = str_replace($search, $replace, $string);
        return $string;
    }

    private function shouldSendMail()
    {
        $config = Config::getInstance();
        $general = $config->General;
        if (empty($general['emails_enabled'])) {
            return false;
        }

        $shouldSendMail = true;

        $mail = $this;

        /**
         * This event is posted before sending an email. You can use it to abort sending a specific email, if you want.
         *
         * @param bool &$shouldSendMail Whether to send this email or not. Set to false to skip sending.
         * @param Mail $mail The Mail instance that will be sent.
         */
        Piwik::postEvent('Mail.shouldSend', [&$shouldSendMail, $mail]);

        return $shouldSendMail;
    }
}
