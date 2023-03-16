<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use DI\NotFoundException;
use DI\DependencyException;
use Piwik\Container\StaticContainer;
use Piwik\Email\ContentGenerator;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Translation\Translator;
use Psr\Log\LoggerInterface;

/**
 * Class for sending mails
 *
 * @api
 */
class Mail
{
    protected $fromEmail = '';
    protected $fromName = '';
    protected $bodyHTML = '';
    protected $bodyText = '';
    protected $subject = '';
    protected $recipients = [];
    protected $replyTos = [];
    protected $bccs = [];
    protected $attachments = [];
    protected $smtpDebug = false;

    public function __construct()
    {
    }

    /**
     * Sets the sender.
     *
     * @param string      $email Email address of the sender.
     * @param null|string $name  Name of the sender.
     */
    public function setFrom($email, $name = null)
    {
        $this->fromName = $name;
        $this->fromEmail = $this->parseDomainPlaceholderAsPiwikHostName($email);
    }

    /**
     * Sets the default sender
     *
     * @throws \DI\NotFoundException
     */
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
     * Returns the address the mail will be sent from
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->fromEmail;
    }

    /**
     * Returns the address the mail will be sent from
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param View|string $body
     * @throws \DI\NotFoundException
     */
    public function setWrappedHtmlBody($body)
    {
        $contentGenerator = StaticContainer::get(ContentGenerator::class);
        $bodyHtml = $contentGenerator->generateHtmlContent($body);
        $this->bodyHTML = $bodyHtml;
    }

    /**
     * Sets the HTML part of the mail
     *
     * @param $html
     */
    public function setBodyHtml($html)
    {
        $this->bodyHTML = $html;
    }

    /**
     * Sets the text part of the mail.
     * If bodyHtml is set, this will be used as alternative text part
     *
     * @param $txt
     */
    public function setBodyText($txt)
    {
        $this->bodyText = $txt;
    }

    /**
     * Returns html content of the mail
     *
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->bodyHTML;
    }

    /**
     * Returns text content of the mail
     *
     * @return string
     */
    public function getBodyText()
    {
        return $this->bodyText;
    }

    /**
     * Sets the subject of the mail
     *
     * @param $subject
     */
    public function setSubject($subject)
    {
        $subject = $this->sanitiseString($subject);
        $this->subject = $subject;
    }

    /**
     * Return the subject of the mail
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Adds a recipient
     *
     * @param string $address
     * @param string $name
     */
    public function addTo($address, $name = '')
    {
        $this->recipients[$address] = $name;
    }

    /**
     * Returns the list of recipients
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Add Bcc address
     *
     * @param string $email
     * @param string $name
     */
    public function addBcc($email, $name = '')
    {
        $this->bccs[$email] = $name;
    }

    /**
     * Returns the list of bcc addresses
     *
     * @return array
     */
    public function getBccs()
    {
        return $this->bccs;
    }

    /**
     * Removes all recipients and bccs from the list
     */
    public function clearAllRecipients()
    {
        $this->recipients = [];
        $this->bccs = [];
    }

    /**
     * Add Reply-To address
     *
     * @param string $email
     * @param string $name
     */
    public function addReplyTo($email, $name = '')
    {
        $this->replyTos[$this->parseDomainPlaceholderAsPiwikHostName($email)] = $name;
    }

    /**
     * Sets the reply to address (all previously added addresses will be removed)
     *
     * @param string $email
     * @param string $name
     */
    public function setReplyTo($email, $name = '')
    {
        $this->replyTos = [];
        $this->addReplyTo($email, $name);
    }

    /**
     * Returns the list of reply to addresses
     *
     * @return array
     */
    public function getReplyTos()
    {
        return $this->replyTos;
    }

    public function addAttachment($body, $mimeType = '', $filename = null, $cid = null)
    {
        $filename = $this->sanitiseString($filename);
        $this->attachments[] = [
            'content' => $body,
            'filename' => $filename,
            'mimetype' => $mimeType,
            'cid' => $cid
        ];
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Sends the mail
     *
     * @return bool|null returns null if sending the mail was aborted by the Mail.send event
     * @throws \DI\NotFoundException
     */
    public function send()
    {
        if (!$this->shouldSendMail()) {
            return false;
        }

        $mail = $this;

        /**
         * This event is posted right before an email is sent. You can use it to customize the email by, for example, replacing
         * the subject/body, changing the from address, etc.
         *
         * @param Mail $mail The Mail instance that is about to be sent.
         */
        Piwik::postEvent('Mail.send', [$mail]);

        return StaticContainer::get('Piwik\Mail\Transport')->send($mail);
    }

    /**
     * If the send email process throws an exception, we catch it and log it
     *
     * @return void
     * @throws NotFoundException
     * @throws DependencyException
     */
    public function safeSend()
    {
        try {
            $this->send();
        } catch (\Exception $e) {
            // we do nothing but log if the email send was unsuccessful
            StaticContainer::get(LoggerInterface::class)->warning('Could not send {class} email: {exception}', ['class' => get_class($this), 'exception' => $e]);
        }
    }

    /**
     * Enables SMTP debugging
     *
     * @param bool $smtpDebug
     */
    public function setSmtpDebug($smtpDebug = true)
    {
        $this->smtpDebug = $smtpDebug;
    }

    /**
     * Returns whether SMTP debugging is enabled or not
     *
     * @return bool
     */
    public function isSmtpDebugEnabled()
    {
        return $this->smtpDebug;
    }

    /**
     * Returns the hostname mails will be sent from
     *
     * @return string
     */
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
