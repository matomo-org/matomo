<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Container\StaticContainer;
use Piwik\Email\ContentGenerator;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Translation\Translator;

/**
 * Class for sending mails
 *
 * @see PHPMailer
 * @api
 */
class Mail extends PHPMailer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(true);
        static::$validator = 'pcre8';
        $this->CharSet = self::CHARSET_UTF8;
        $this->Encoding = self::ENCODING_QUOTED_PRINTABLE;
        $this->XMailer = 'Matomo ' . Version::VERSION;
        $this->setLanguage(StaticContainer::get('Piwik\Translation\Translator')->getCurrentLanguage());
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
        $this->msgHTML($bodyHtml);
    }

    public function setBodyHtml($html)
    {
        $this->msgHTML($html);
    }

    /**
     * Alias method for addAddress to keep BC to Zend_Mail
     *
     * @deprecated
     * @param        $address
     * @param string $name
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addTo($address, $name = '')
    {
        $this->addAddress($address, $name);
    }

    public function setBodyText($txt)
    {
        if ($this->ContentType == static::CONTENT_TYPE_TEXT_HTML) {
            $this->AltBody = $txt;
            return;
        }

        $this->Body = $txt;
    }

    /**
     * Sets the sender.
     *
     * @param string $email Email address of the sender.
     * @param null|string $name Name of the sender.
     * @return bool
     */
    public function setFrom($email, $name = null, $auto = true)
    {
        return parent::setFrom(
            $this->parseDomainPlaceholderAsPiwikHostName($email),
            $name,
            $auto
        );
    }

    /**
     * Alias method for addReplyTo to keep BC to Zend_Mail
     *
     * @deprecated
     * @param      $email
     * @param null $name
     * @return bool
     */
    public function setReplyTo($email, $name = null)
    {
        return $this->addReplyTo($email, $name);
    }

    /**
     * Add Reply-To Header
     *
     * @param string $email
     * @param null|string $name
     * @return bool
     */
    public function addReplyTo($email, $name = null)
    {
        return parent::addReplyTo(
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

        $this->isSMTP();

        if (!empty($mailConfig['type'])) {
            $this->SMTPAuth = true;
            $this->AuthType = strtoupper($mailConfig['type']);
        }

        if (!empty($mailConfig['username'])) {
            $this->Username = $mailConfig['username'];
        }

        if (!empty($mailConfig['password'])) {
            $this->Password = $mailConfig['password'];
        }

        if (!empty($mailConfig['encryption'])) {
            $this->SMTPSecure = $mailConfig['encryption'];
        }

        if (!empty($mailConfig['port'])) {
            $this->Port = $mailConfig['port'];
        }

        $this->Host = trim($mailConfig['host']);
    }

    public function send()
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
            return parent::send();
        }
    }

    public function createAttachment($body, $mimeType = '', $disposition = 'attachment', $encoding = self::ENCODING_BASE64, $filename = null)
    {
        $filename = $this->sanitiseString($filename);
        return parent::addStringAttachment(
            $body,
            $filename,
            $encoding,
            $mimeType,
            $disposition);
    }

    public function setSubject($subject)
    {
        $subject = $this->sanitiseString($subject);
        $this->Subject = $subject;
    }

    public function getSubject()
    {
        return $this->Subject;
    }

    public function getRecipients()
    {
        return $this->getAllRecipientAddresses();
    }

    public function getFrom()
    {
        return $this->From;
    }

    public function getBodyHtml()
    {
        return $this->Body;
    }

    public function getBodyText()
    {
        if ($this->ContentType == static::CONTENT_TYPE_TEXT_HTML) {
            return $this->AltBody;
        }

        return $this->Body;
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
    public function sanitiseString($string)
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
