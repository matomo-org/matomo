<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Mail_Transport_Abstract
 */
// require_once 'Zend/Mail/Transport/Abstract.php';

/**
 * @see Zend_Mime
 */
// require_once 'Zend/Mime.php';

/**
 * @see Zend_Mime_Message
 */
// require_once 'Zend/Mime/Message.php';

/**
 * @see Zend_Mime_Part
 */
// require_once 'Zend/Mime/Part.php';


/**
 * Class for sending an email.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail extends Zend_Mime_Message
{
    /**#@+
     * @access protected
     */

    /**
     * @var Zend_Mail_Transport_Abstract
     * @static
     */
    protected static $_defaultTransport = null;

    /**
     * @var array
     * @static
     */
    protected static $_defaultFrom;

    /**
     * @var array
     * @static
     */
    protected static $_defaultReplyTo;

    /**
     * Mail character set
     * @var string
     */
    protected $_charset = 'iso-8859-1';

    /**
     * Mail headers
     * @var array
     */
    protected $_headers = array();

    /**
     * Encoding of Mail headers
     * @var string
     */
    protected $_headerEncoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;

    /**
     * From: address
     * @var string
     */
    protected $_from = null;

    /**
     * To: addresses
     * @var array
     */
    protected $_to = array();

    /**
     * Array of all recipients
     * @var array
     */
    protected $_recipients = array();

    /**
     * Reply-To header
     * @var string
     */
    protected $_replyTo = null;

    /**
     * Return-Path header
     * @var string
     */
    protected $_returnPath = null;

    /**
     * Subject: header
     * @var string
     */
    protected $_subject = null;

    /**
     * Date: header
     * @var string
     */
    protected $_date = null;

    /**
     * Message-ID: header
     * @var string
     */
    protected $_messageId = null;

    /**
     * text/plain MIME part
     * @var false|Zend_Mime_Part
     */
    protected $_bodyText = false;

    /**
     * text/html MIME part
     * @var false|Zend_Mime_Part
     */
    protected $_bodyHtml = false;

    /**
     * MIME boundary string
     * @var string
     */
    protected $_mimeBoundary = null;

    /**
     * Content type of the message
     * @var string
     */
    protected $_type = null;

    /**#@-*/

    /**
     * Flag: whether or not email has attachments
     * @var boolean
     */
    public $hasAttachments = false;


    /**
     * Sets the default mail transport for all following uses of
     * Zend_Mail::send();
     *
     * @todo Allow passing a string to indicate the transport to load
     * @todo Allow passing in optional options for the transport to load
     * @param  Zend_Mail_Transport_Abstract $transport
     */
    public static function setDefaultTransport(Zend_Mail_Transport_Abstract $transport)
    {
        self::$_defaultTransport = $transport;
    }

    /**
     * Gets the default mail transport for all following uses of
     * unittests
     *
     * @todo Allow passing a string to indicate the transport to load
     * @todo Allow passing in optional options for the transport to load
     */
    public static function getDefaultTransport()
    {
        return self::$_defaultTransport;
    }

    /**
     * Clear the default transport property
     */
    public static function clearDefaultTransport()
    {
        self::$_defaultTransport = null;
    }

    /**
     * Public constructor
     *
     * @param  string $charset
     * @return void
     */
    public function __construct($charset = null)
    {
        if ($charset != null) {
            $this->_charset = $charset;
        }
    }

    /**
     * Return charset string
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Set content type
     *
     * Should only be used for manually setting multipart content types.
     *
     * @param  string $type Content type
     * @return Zend_Mail Implements fluent interface
     * @throws Zend_Mail_Exception for types not supported by Zend_Mime
     */
    public function setType($type)
    {
        $allowed = array(
            Zend_Mime::MULTIPART_ALTERNATIVE,
            Zend_Mime::MULTIPART_MIXED,
            Zend_Mime::MULTIPART_RELATED,
        );
        if (!in_array($type, $allowed)) {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Invalid content type "' . $type . '"');
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Get content type of the message
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set an arbitrary mime boundary for the message
     *
     * If not set, Zend_Mime will generate one.
     *
     * @param  string    $boundary
     * @return Zend_Mail Provides fluent interface
     */
    public function setMimeBoundary($boundary)
    {
        $this->_mimeBoundary = $boundary;

        return $this;
    }

    /**
     * Return the boundary string used for the message
     *
     * @return string
     */
    public function getMimeBoundary()
    {
        return $this->_mimeBoundary;
    }

    /**
     * Return encoding of mail headers
     *
     * @deprecated use {@link getHeaderEncoding()} instead
     * @return string
     */
    public function getEncodingOfHeaders()
    {
        return $this->getHeaderEncoding();
    }

    /**
     * Return the encoding of mail headers
     *
     * Either Zend_Mime::ENCODING_QUOTEDPRINTABLE or Zend_Mime::ENCODING_BASE64
     *
     * @return string
     */
    public function getHeaderEncoding()
    {
        return $this->_headerEncoding;
    }

    /**
     * Set the encoding of mail headers
     *
     * @deprecated Use {@link setHeaderEncoding()} instead.
     * @param  string $encoding
     * @return Zend_Mail
     */
    public function setEncodingOfHeaders($encoding)
    {
        return $this->setHeaderEncoding($encoding);
    }

    /**
     * Set the encoding of mail headers
     *
     * @param  string $encoding Zend_Mime::ENCODING_QUOTEDPRINTABLE or Zend_Mime::ENCODING_BASE64
     * @return Zend_Mail Provides fluent interface
     */
    public function setHeaderEncoding($encoding)
    {
        $allowed = array(
            Zend_Mime::ENCODING_BASE64,
            Zend_Mime::ENCODING_QUOTEDPRINTABLE
        );
        if (!in_array($encoding, $allowed)) {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Invalid encoding "' . $encoding . '"');
        }
        $this->_headerEncoding = $encoding;

        return $this;
    }

    /**
     * Sets the text body for the message.
     *
     * @param  string $txt
     * @param  string $charset
     * @param  string $encoding
     * @return Zend_Mail Provides fluent interface
    */
    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($charset === null) {
            $charset = $this->_charset;
        }

        $mp = new Zend_Mime_Part($txt);
        $mp->encoding = $encoding;
        $mp->type = Zend_Mime::TYPE_TEXT;
        $mp->disposition = Zend_Mime::DISPOSITION_INLINE;
        $mp->charset = $charset;

        $this->_bodyText = $mp;

        return $this;
    }

    /**
     * Return text body Zend_Mime_Part or string
     *
     * @param  bool textOnly Whether to return just the body text content or the MIME part; defaults to false, the MIME part
     * @return false|Zend_Mime_Part|string
     */
    public function getBodyText($textOnly = false)
    {
        if ($textOnly && $this->_bodyText) {
            $body = $this->_bodyText;
            return $body->getContent();
        }

        return $this->_bodyText;
    }

    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($charset === null) {
            $charset = $this->_charset;
        }

        $mp = new Zend_Mime_Part($html);
        $mp->encoding = $encoding;
        $mp->type = Zend_Mime::TYPE_HTML;
        $mp->disposition = Zend_Mime::DISPOSITION_INLINE;
        $mp->charset = $charset;

        $this->_bodyHtml = $mp;

        return $this;
    }

    /**
     * Return Zend_Mime_Part representing body HTML
     *
     * @param  bool $htmlOnly Whether to return the body HTML only, or the MIME part; defaults to false, the MIME part
     * @return false|Zend_Mime_Part|string
     */
    public function getBodyHtml($htmlOnly = false)
    {
        if ($htmlOnly && $this->_bodyHtml) {
            $body = $this->_bodyHtml;
            return $body->getContent();
        }

        return $this->_bodyHtml;
    }

    /**
     * Adds an existing attachment to the mail message
     *
     * @param  Zend_Mime_Part $attachment
     * @return Zend_Mail Provides fluent interface
     */
    public function addAttachment(Zend_Mime_Part $attachment)
    {
        $this->addPart($attachment);
        $this->hasAttachments = true;

        return $this;
    }

    /**
     * Creates a Zend_Mime_Part attachment
     *
     * Attachment is automatically added to the mail object after creation. The
     * attachment object is returned to allow for further manipulation.
     *
     * @param  string         $body
     * @param  string         $mimeType
     * @param  string         $disposition
     * @param  string         $encoding
     * @param  string         $filename OPTIONAL A filename for the attachment
     * @return Zend_Mime_Part Newly created Zend_Mime_Part object (to allow
     * advanced settings)
     */
    public function createAttachment($body,
                                     $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {

        $mp = new Zend_Mime_Part($body);
        $mp->encoding = $encoding;
        $mp->type = $mimeType;
        $mp->disposition = $disposition;
        $mp->filename = $filename;

        $this->addAttachment($mp);

        return $mp;
    }

    /**
     * Return a count of message parts
     *
     * @return integer
     */
    public function getPartCount()
    {
        return count($this->_parts);
    }

    /**
     * Encode header fields
     *
     * Encodes header content according to RFC1522 if it contains non-printable
     * characters.
     *
     * @param  string $value
     * @return string
     */
    protected function _encodeHeader($value)
    {
        if (Zend_Mime::isPrintable($value) === false) {
            if ($this->getHeaderEncoding() === Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                $value = Zend_Mime::encodeQuotedPrintableHeader($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
            } else {
                $value = Zend_Mime::encodeBase64Header($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
            }
        }

        return $value;
    }

    /**
     * Add a header to the message
     *
     * Adds a header to this message. If append is true and the header already
     * exists, raises a flag indicating that the header should be appended.
     *
     * @param string  $headerName
     * @param string  $value
     * @param bool $append
     */
    protected function _storeHeader($headerName, $value, $append = false)
    {
        if (isset($this->_headers[$headerName])) {
            $this->_headers[$headerName][] = $value;
        } else {
            $this->_headers[$headerName] = array($value);
        }

        if ($append) {
            $this->_headers[$headerName]['append'] = true;
        }

    }

    /**
     * Clear header from the message
     *
     * @param string $headerName
     * @deprecated use public method directly
     */
    protected function _clearHeader($headerName)
    {
        $this->clearHeader($headerName);
    }

    /**
     * Helper function for adding a recipient and the corresponding header
     *
     * @param string $headerName
     * @param string $email
     * @param string $name
     */
    protected function _addRecipientAndHeader($headerName, $email, $name)
    {
        $email = $this->_filterEmail($email);
        $name  = $this->_filterName($name);
        // prevent duplicates
        $this->_recipients[$email] = 1;
        $this->_storeHeader($headerName, $this->_formatAddress($email, $name), true);
    }

    /**
     * Adds To-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array $email
     * @param  string $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addTo($email, $name='')
    {
        if (!is_array($email)) {
            $email = array($name => $email);
        }

        foreach ($email as $n => $recipient) {
            $this->_addRecipientAndHeader('To', $recipient, is_int($n) ? '' : $n);
            $this->_to[] = $recipient;
        }

        return $this;
    }

    /**
     * Adds Cc-header and recipient, $email can be an array, or a single string address
     *
     * @param  string|array    $email
     * @param  string    $name
     * @return Zend_Mail Provides fluent interface
     */
    public function addCc($email, $name='')
    {
        if (!is_array($email)) {
            $email = array($name => $email);
        }

        foreach ($email as $n => $recipient) {
            $this->_addRecipientAndHeader('Cc', $recipient, is_int($n) ? '' : $n);
        }

        return $this;
    }

    /**
     * Adds Bcc recipient, $email can be an array, or a single string address
     *
     * @param  string|array    $email
     * @return Zend_Mail Provides fluent interface
     */
    public function addBcc($email)
    {
        if (!is_array($email)) {
            $email = array($email);
        }

        foreach ($email as $recipient) {
            $this->_addRecipientAndHeader('Bcc', $recipient, '');
        }

        return $this;
    }

    /**
     * Return list of recipient email addresses
     *
     * @return array (of strings)
     */
    public function getRecipients()
    {
        return array_keys($this->_recipients);
    }

    /**
     * Clear header from the message
     *
     * @param string $headerName
     * @return Zend_Mail Provides fluent inter
     */
    public function clearHeader($headerName)
    {
        if (isset($this->_headers[$headerName])){
            unset($this->_headers[$headerName]);
        }
        return $this;
    }

    /**
     * Clears list of recipient email addresses
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearRecipients()
    {
        $this->_recipients = array();
        $this->_to = array();

        $this->clearHeader('To');
        $this->clearHeader('Cc');
        $this->clearHeader('Bcc');

        return $this;
    }

    /**
     * Sets From-header and sender of the message
     *
     * @param  string    $email
     * @param  string    $name
     * @return Zend_Mail Provides fluent interface
     * @throws Zend_Mail_Exception if called subsequent times
     */
    public function setFrom($email, $name = null)
    {
        if (null !== $this->_from) {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('From Header set twice');
        }

        $email = $this->_filterEmail($email);
        $name  = $this->_filterName($name);
        $this->_from = $email;
        $this->_storeHeader('From', $this->_formatAddress($email, $name), true);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string $name
     * @return Zend_Mail
     * @throws Zend_Mail_Exception if called more than one time
     */
    public function setReplyTo($email, $name = null)
    {
        if (null !== $this->_replyTo) {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Reply-To Header set twice');
        }

        $email = $this->_filterEmail($email);
        $name  = $this->_filterName($name);
        $this->_replyTo = $email;
        $this->_storeHeader('Reply-To', $this->_formatAddress($email, $name), true);

        return $this;
    }

    /**
     * Returns the sender of the mail
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * Returns the current Reply-To address of the message
     *
     * @return string|null Reply-To address, null when not set
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     * Clears the sender from the mail
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearFrom()
    {
        $this->_from = null;
        $this->clearHeader('From');

        return $this;
    }

     /**
      * Clears the current Reply-To address from the message
      *
      * @return Zend_Mail Provides fluent interface
      */
    public function clearReplyTo()
    {
        $this->_replyTo = null;
        $this->clearHeader('Reply-To');

        return $this;
    }

    /**
     * Sets Default From-email and name of the message
     *
     * @param  string               $email
     * @param  string    Optional   $name
     * @return void
     */
    public static function setDefaultFrom($email, $name = null)
    {
        self::$_defaultFrom = array('email' => $email, 'name' => $name);
    }

    /**
     * Returns the default sender of the mail
     *
     * @return null|array   Null if none was set.
     */
    public static function getDefaultFrom()
    {
        return self::$_defaultFrom;
    }

    /**
     * Clears the default sender from the mail
     *
     * @return void
     */
    public static function clearDefaultFrom()
    {
        self::$_defaultFrom = null;
    }

    /**
     * Sets From-name and -email based on the defaults
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function setFromToDefaultFrom() {
        $from = self::getDefaultFrom();
        if($from === null) {
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception(
                'No default From Address set to use');
        }

        $this->setFrom($from['email'], $from['name']);

        return $this;
    }

    /**
     * Sets Default ReplyTo-address and -name of the message
     *
     * @param  string               $email
     * @param  string    Optional   $name
     * @return void
     */
    public static function setDefaultReplyTo($email, $name = null)
    {
        self::$_defaultReplyTo = array('email' => $email, 'name' => $name);
    }

    /**
     * Returns the default Reply-To Address and Name of the mail
     *
     * @return null|array   Null if none was set.
     */
    public static function getDefaultReplyTo()
    {
        return self::$_defaultReplyTo;
    }

    /**
     * Clears the default ReplyTo-address and -name from the mail
     *
     * @return void
     */
    public static function clearDefaultReplyTo()
    {
        self::$_defaultReplyTo = null;
    }

    /**
     * Sets ReplyTo-name and -email based on the defaults
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function setReplyToFromDefault() {
        $replyTo = self::getDefaultReplyTo();
        if($replyTo === null) {
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception(
                'No default Reply-To Address set to use');
        }

        $this->setReplyTo($replyTo['email'], $replyTo['name']);

        return $this;
    }

    /**
     * Sets the Return-Path header of the message
     *
     * @param  string    $email
     * @return Zend_Mail Provides fluent interface
     * @throws Zend_Mail_Exception if set multiple times
     */
    public function setReturnPath($email)
    {
        if ($this->_returnPath === null) {
            $email = $this->_filterEmail($email);
            $this->_returnPath = $email;
            $this->_storeHeader('Return-Path', $email, false);
        } else {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Return-Path Header set twice');
        }
        return $this;
    }

    /**
     * Returns the current Return-Path address of the message
     *
     * If no Return-Path header is set, returns the value of {@link $_from}.
     *
     * @return string
     */
    public function getReturnPath()
    {
        if (null !== $this->_returnPath) {
            return $this->_returnPath;
        }

        return $this->_from;
    }

    /**
     * Clears the current Return-Path address from the message
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearReturnPath()
    {
        $this->_returnPath = null;
        $this->clearHeader('Return-Path');

        return $this;
    }

    /**
     * Sets the subject of the message
     *
     * @param   string    $subject
     * @return  Zend_Mail Provides fluent interface
     * @throws  Zend_Mail_Exception
     */
    public function setSubject($subject)
    {
        if ($this->_subject === null) {
            $subject = $this->_filterOther($subject);
            $this->_subject = $this->_encodeHeader($subject);
            $this->_storeHeader('Subject', $this->_subject);
        } else {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Subject set twice');
        }
        return $this;
    }

    /**
     * Returns the encoded subject of the message
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Clears the encoded subject from the message
     *
     * @return  Zend_Mail Provides fluent interface
     */
    public function clearSubject()
    {
        $this->_subject = null;
        $this->clearHeader('Subject');

        return $this;
    }

    /**
     * Sets Date-header
     *
     * @param  timestamp|string|Zend_Date $date
     * @return Zend_Mail Provides fluent interface
     * @throws Zend_Mail_Exception if called subsequent times or wrong date format.
     */
    public function setDate($date = null)
    {
        if ($this->_date === null) {
            if ($date === null) {
                $date = date('r');
            } else if (is_int($date)) {
                $date = date('r', $date);
            } else if (is_string($date)) {
                $date = strtotime($date);
                if ($date === false || $date < 0) {
                    /**
                     * @see Zend_Mail_Exception
                     */
                    // require_once 'Zend/Mail/Exception.php';
                    throw new Zend_Mail_Exception('String representations of Date Header must be ' .
                                                  'strtotime()-compatible');
                }
                $date = date('r', $date);
            } else if ($date instanceof Zend_Date) {
                $date = $date->get(Zend_Date::RFC_2822);
            } else {
                /**
                 * @see Zend_Mail_Exception
                 */
                // require_once 'Zend/Mail/Exception.php';
                throw new Zend_Mail_Exception(__METHOD__ . ' only accepts UNIX timestamps, Zend_Date objects, ' .
                                              ' and strtotime()-compatible strings');
            }
            $this->_date = $date;
            $this->_storeHeader('Date', $date);
        } else {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Date Header set twice');
        }
        return $this;
    }

    /**
     * Returns the formatted date of the message
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Clears the formatted date from the message
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearDate()
    {
        $this->_date = null;
        $this->clearHeader('Date');

        return $this;
    }

    /**
     * Sets the Message-ID of the message
     *
     * @param   boolean|string  $id
     * true  :Auto
     * false :No set
     * null  :No set
     * string:Sets given string (Angle brackets is not necessary)
     * @return  Zend_Mail Provides fluent interface
     * @throws  Zend_Mail_Exception
     */
    public function setMessageId($id = true)
    {
        if ($id === null || $id === false) {
            return $this;
        } elseif ($id === true) {
            $id = $this->createMessageId();
        }

        if ($this->_messageId === null) {
            $id = $this->_filterOther($id);
            $this->_messageId = $id;
            $this->_storeHeader('Message-Id', '<' . $this->_messageId . '>');
        } else {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Message-ID set twice');
        }

        return $this;
    }

    /**
     * Returns the Message-ID of the message
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->_messageId;
    }


    /**
     * Clears the Message-ID from the message
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearMessageId()
    {
        $this->_messageId = null;
        $this->clearHeader('Message-Id');

        return $this;
    }

    /**
     * Creates the Message-ID
     *
     * @return string
     */
    public function createMessageId() {

        $time = time();

        if ($this->_from !== null) {
            $user = $this->_from;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $user = $_SERVER['REMOTE_ADDR'];
        } else {
            $user = getmypid();
        }

        $rand = mt_rand();

        if ($this->_recipients !== array()) {
            $recipient = array_rand($this->_recipients);
        } else {
            $recipient = 'unknown';
        }

        if (isset($_SERVER["SERVER_NAME"])) {
            $hostName = $_SERVER["SERVER_NAME"];
        } else {
            $hostName = php_uname('n');
        }

        return sha1($time . $user . $rand . $recipient) . '@' . $hostName;
    }

    /**
     * Add a custom header to the message
     *
     * @param  string              $name
     * @param  string              $value
     * @param  boolean             $append
     * @return Zend_Mail           Provides fluent interface
     * @throws Zend_Mail_Exception on attempts to create standard headers
     */
    public function addHeader($name, $value, $append = false)
    {
        $prohibit = array('to', 'cc', 'bcc', 'from', 'subject',
                          'reply-to', 'return-path',
                          'date', 'message-id',
                         );
        if (in_array(strtolower($name), $prohibit)) {
            /**
             * @see Zend_Mail_Exception
             */
            // require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Cannot set standard header from addHeader()');
        }

        $value = $this->_filterOther($value);
        $value = $this->_encodeHeader($value);
        $this->_storeHeader($name, $value, $append);

        return $this;
    }

    /**
     * Return mail headers
     *
     * @return void
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Sends this email using the given transport or a previously
     * set DefaultTransport or the internal mail function if no
     * default transport had been set.
     *
     * @param  Zend_Mail_Transport_Abstract $transport
     * @return Zend_Mail                    Provides fluent interface
     */
    public function send($transport = null)
    {
        if ($transport === null) {
            if (! self::$_defaultTransport instanceof Zend_Mail_Transport_Abstract) {
                // require_once 'Zend/Mail/Transport/Sendmail.php';
                $transport = new Zend_Mail_Transport_Sendmail();
            } else {
                $transport = self::$_defaultTransport;
            }
        }

        if ($this->_date === null) {
            $this->setDate();
        }

        if(null === $this->_from && null !== self::getDefaultFrom()) {
            $this->setFromToDefaultFrom();
        }

        if(null === $this->_replyTo && null !== self::getDefaultReplyTo()) {
            $this->setReplyToFromDefault();
        }

        $transport->send($this);

        return $this;
    }

    /**
     * Filter of email data
     *
     * @param string $email
     * @return string
     */
    protected function _filterEmail($email)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
                      '"'  => '',
                      ','  => '',
                      '<'  => '',
                      '>'  => '',
        );

        return strtr($email, $rule);
    }

    /**
     * Filter of name data
     *
     * @param string $name
     * @return string
     */
    protected function _filterName($name)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
                      '"'  => "'",
                      '<'  => '[',
                      '>'  => ']',
        );

        return trim(strtr($name, $rule));
    }

    /**
     * Filter of other data
     *
     * @param string $data
     * @return string
     */
    protected function _filterOther($data)
    {
        $rule = array("\r" => '',
                      "\n" => '',
                      "\t" => '',
        );

        return strtr($data, $rule);
    }

    /**
     * Formats e-mail address
     *
     * @param string $email
     * @param string $name
     * @return string
     */
    protected function _formatAddress($email, $name)
    {
        if ($name === '' || $name === null || $name === $email) {
            return $email;
        } else {
            $encodedName = $this->_encodeHeader($name);
            if ($encodedName === $name  &&  strcspn($name, '()<>[]:;@\\,') != strlen($name)) {
                $format = '"%s" <%s>';
            } else {
                $format = '%s <%s>';
            }
            return sprintf($format, $encodedName, $email);
        }
    }

}
