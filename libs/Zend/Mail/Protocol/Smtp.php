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
 * @subpackage Protocol
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Smtp.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Mime
 */
// require_once 'Zend/Mime.php';


/**
 * @see Zend_Mail_Protocol_Abstract
 */
// require_once 'Zend/Mail/Protocol/Abstract.php';


/**
 * Smtp implementation of Zend_Mail_Protocol_Abstract
 *
 * Minimum implementation according to RFC2821: EHLO, MAIL FROM, RCPT TO, DATA, RSET, NOOP, QUIT
 *
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Protocol
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Protocol_Smtp extends Zend_Mail_Protocol_Abstract
{
    /**
     * The transport method for the socket
     *
     * @var string
     */
    protected $_transport = 'tcp';


    /**
     * Indicates that a session is requested to be secure
     *
     * @var string
     */
    protected $_secure;


    /**
     * Indicates an smtp session has been started by the HELO command
     *
     * @var boolean
     */
    protected $_sess = false;


    /**
     * Indicates the HELO command has been issues
     *
     * @var unknown_type
     */
    protected $_helo = false;


    /**
     * Indicates an smtp AUTH has been issued and authenticated
     *
     * @var unknown_type
     */
    protected $_auth = false;


    /**
     * Indicates a MAIL command has been issued
     *
     * @var unknown_type
     */
    protected $_mail = false;


    /**
     * Indicates one or more RCTP commands have been issued
     *
     * @var unknown_type
     */
    protected $_rcpt = false;


    /**
     * Indicates that DATA has been issued and sent
     *
     * @var unknown_type
     */
    protected $_data = null;


    /**
     * Constructor.
     *
     * @param  string  $host
     * @param  integer $port
     * @param  array   $config
     * @return void
     * @throws Zend_Mail_Protocol_Exception
     */
    public function __construct($host = '127.0.0.1', $port = null, array $config = array())
    {
        if (isset($config['ssl'])) {
            switch (strtolower($config['ssl'])) {
                case 'tls':
                    $this->_secure = 'tls';
                    break;

                case 'ssl':
                    $this->_transport = 'ssl';
                    $this->_secure = 'ssl';
                    if ($port == null) {
                        $port = 465;
                    }
                    break;

                default:
                    /**
                     * @see Zend_Mail_Protocol_Exception
                     */
                    // require_once 'Zend/Mail/Protocol/Exception.php';
                    throw new Zend_Mail_Protocol_Exception($config['ssl'] . ' is unsupported SSL type');
                    break;
            }
        }

        // If no port has been specified then check the master PHP ini file. Defaults to 25 if the ini setting is null.
        if ($port == null) {
            if (($port = ini_get('smtp_port')) == '') {
                $port = 25;
            }
        }

        parent::__construct($host, $port);
    }


    /**
     * Connect to the server with the parameters given in the constructor.
     *
     * @return boolean
     */
    public function connect()
    {
        return $this->_connect($this->_transport . '://' . $this->_host . ':'. $this->_port);
    }


    /**
     * Initiate HELO/EHLO sequence and set flag to indicate valid smtp session
     *
     * @param  string $host The client hostname or IP address (default: 127.0.0.1)
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function helo($host = '127.0.0.1')
    {
        // Respect RFC 2821 and disallow HELO attempts if session is already initiated.
        if ($this->_sess === true) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('Cannot issue HELO to existing session');
        }

        // Validate client hostname
        if (!$this->_validHost->isValid($host)) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception(join(', ', $this->_validHost->getMessages()));
        }

        // Initiate helo sequence
        $this->_expect(220, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
        $this->_ehlo($host);

        // If a TLS session is required, commence negotiation
        if ($this->_secure == 'tls') {
            $this->_send('STARTTLS');
            $this->_expect(220, 180);
            if (!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                /**
                 * @see Zend_Mail_Protocol_Exception
                 */
                // require_once 'Zend/Mail/Protocol/Exception.php';
                throw new Zend_Mail_Protocol_Exception('Unable to connect via TLS');
            }
            $this->_ehlo($host);
        }

        $this->_startSession();
        $this->auth();
    }


    /**
     * Send EHLO or HELO depending on capabilities of smtp host
     *
     * @param  string $host The client hostname or IP address (default: 127.0.0.1)
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    protected function _ehlo($host)
    {
        // Support for older, less-compliant remote servers. Tries multiple attempts of EHLO or HELO.
        try {
            $this->_send('EHLO ' . $host);
            $this->_expect(250, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->_send('HELO ' . $host);
            $this->_expect(250, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
        } catch (Zend_Mail_Protocol_Exception $e) {
            throw $e;
        }
    }


    /**
     * Issues MAIL command
     *
     * @param  string $from Sender mailbox
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function mail($from)
    {
        if ($this->_sess !== true) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('A valid session has not been started');
        }

        $this->_send('MAIL FROM:<' . $from . '>');
        $this->_expect(250, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2

        // Set mail to true, clear recipients and any existing data flags as per 4.1.1.2 of RFC 2821
        $this->_mail = true;
        $this->_rcpt = false;
        $this->_data = false;
    }


    /**
     * Issues RCPT command
     *
     * @param  string $to Receiver(s) mailbox
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function rcpt($to)
    {
        if ($this->_mail !== true) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('No sender reverse path has been supplied');
        }

        // Set rcpt to true, as per 4.1.1.3 of RFC 2821
        $this->_send('RCPT TO:<' . $to . '>');
        $this->_expect(array(250, 251), 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
        $this->_rcpt = true;
    }


    /**
     * Issues DATA command
     *
     * @param  string $data
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function data($data)
    {
        // Ensure recipients have been set
        if ($this->_rcpt !== true) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('No recipient forward path has been supplied');
        }

        $this->_send('DATA');
        $this->_expect(354, 120); // Timeout set for 2 minutes as per RFC 2821 4.5.3.2

        foreach (explode(Zend_Mime::LINEEND, $data) as $line) {
            if (strpos($line, '.') === 0) {
                // Escape lines prefixed with a '.'
                $line = '.' . $line;
            }
            $this->_send($line);
        }

        $this->_send('.');
        $this->_expect(250, 600); // Timeout set for 10 minutes as per RFC 2821 4.5.3.2
        $this->_data = true;
    }


    /**
     * Issues the RSET command and validates answer
     *
     * Can be used to restore a clean smtp communication state when a transaction has been cancelled or commencing a new transaction.
     *
     * @return void
     */
    public function rset()
    {
        $this->_send('RSET');
        // MS ESMTP doesn't follow RFC, see [ZF-1377]
        $this->_expect(array(250, 220));

        $this->_mail = false;
        $this->_rcpt = false;
        $this->_data = false;
    }


    /**
     * Issues the NOOP command and validates answer
     *
     * Not used by Zend_Mail, could be used to keep a connection alive or check if it is still open.
     *
     * @return void
     */
    public function noop()
    {
        $this->_send('NOOP');
        $this->_expect(250, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
    }


    /**
     * Issues the VRFY command and validates answer
     *
     * Not used by Zend_Mail.
     *
     * @param  string $user User Name or eMail to verify
     * @return void
     */
    public function vrfy($user)
    {
        $this->_send('VRFY ' . $user);
        $this->_expect(array(250, 251, 252), 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
    }


    /**
     * Issues the QUIT command and clears the current session
     *
     * @return void
     */
    public function quit()
    {
        if ($this->_sess) {
            $this->_send('QUIT');
            $this->_expect(221, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
            $this->_stopSession();
        }
    }


    /**
     * Default authentication method
     *
     * This default method is implemented by AUTH adapters to properly authenticate to a remote host.
     *
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function auth()
    {
        if ($this->_auth === true) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            // require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('Already authenticated for this session');
        }
    }


    /**
     * Closes connection
     *
     * @return void
     */
    public function disconnect()
    {
        $this->_disconnect();
    }


    /**
     * Start mail session
     *
     * @return void
     */
    protected function _startSession()
    {
        $this->_sess = true;
    }


    /**
     * Stop mail session
     *
     * @return void
     */
    protected function _stopSession()
    {
        $this->_sess = false;
    }
}
