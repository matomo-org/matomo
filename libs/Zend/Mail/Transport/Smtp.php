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
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Smtp.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Zend_Mime
 */
require_once 'Zend/Mime.php';

/**
 * @see Zend_Mail_Protocol_Smtp
 */
require_once 'Zend/Mail/Protocol/Smtp.php';

/**
 * @see Zend_Mail_Transport_Abstract
 */
require_once 'Zend/Mail/Transport/Abstract.php';


/**
 * SMTP connection object
 *
 * Loads an instance of Zend_Mail_Protocol_Smtp and forwards smtp transactions
 *
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Transport_Smtp extends Zend_Mail_Transport_Abstract
{
    /**
     * EOL character string used by transport
     * @var string
     * @access public
     */
    public $EOL = "\n";

    /**
     * Remote smtp hostname or i.p.
     *
     * @var string
     */
    protected $_host;


    /**
     * Port number
     *
     * @var integer|null
     */
    protected $_port;


    /**
     * Local client hostname or i.p.
     *
     * @var string
     */
    protected $_name = 'localhost';


    /**
     * Authentication type OPTIONAL
     *
     * @var string
     */
    protected $_auth;


    /**
     * Config options for authentication
     *
     * @var array
     */
    protected $_config;


    /**
     * Instance of Zend_Mail_Protocol_Smtp
     *
     * @var Zend_Mail_Protocol_Smtp
     */
    protected $_connection;


    /**
     * Constructor.
     *
     * @param  string $host OPTIONAL (Default: 127.0.0.1)
     * @param  array|null $config OPTIONAL (Default: null)
     * @return void
     */
    public function __construct($host = '127.0.0.1', Array $config = array())
    {
        if (isset($config['name'])) {
            $this->_name = $config['name'];
        }
        if (isset($config['port'])) {
            $this->_port = $config['port'];
        }
        if (isset($config['auth'])) {
            $this->_auth = $config['auth'];
        }

        $this->_host = $host;
        $this->_config = $config;
    }


    /**
     * Class destructor to ensure all open connections are closed
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_connection instanceof Zend_Mail_Protocol_Smtp) {
            try {
                $this->_connection->quit();
            } catch (Zend_Mail_Protocol_Exception $e) {
            	// ignore
            }
            $this->_connection->disconnect();
        }
    }


    /**
     * Sets the connection protocol instance
     *
     * @param Zend_Mail_Protocol_Abstract $client
     *
     * @return void
     */
    public function setConnection(Zend_Mail_Protocol_Abstract $connection)
    {
        $this->_connection = $connection;
    }


    /**
     * Gets the connection protocol instance
     *
     * @return Zend_Mail_Protocol|null
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Send an email via the SMTP connection protocol
     *
     * The connection via the protocol adapter is made just-in-time to allow a
     * developer to add a custom adapter if required before mail is sent.
     *
     * @return void
     */
    public function _sendMail()
    {
        // If sending multiple messages per session use existing adapter
        if (!($this->_connection instanceof Zend_Mail_Protocol_Smtp)) {
            // Check if authentication is required and determine required class
            $connectionClass = 'Zend_Mail_Protocol_Smtp';
            if ($this->_auth) {
                $connectionClass .= '_Auth_' . ucwords($this->_auth);
            }
            Zend_Loader::loadClass($connectionClass);
            $this->setConnection(new $connectionClass($this->_host, $this->_port, $this->_config));
            $this->_connection->connect();
            $this->_connection->helo($this->_name);
        } else {
            // Reset connection to ensure reliable transaction
            $this->_connection->rset();
        }

        // Set mail return path from sender email address
        $this->_connection->mail($this->_mail->getReturnPath());

        // Set recipient forward paths
        foreach ($this->_mail->getRecipients() as $recipient) {
            $this->_connection->rcpt($recipient);
        }

        // Issue DATA command to client
        $this->_connection->data($this->header . Zend_Mime::LINEEND . $this->body);
    }

    /**
     * Format and fix headers
     *
     * Some SMTP servers do not strip BCC headers. Most clients do it themselves as do we.
     *
     * @access  protected
     * @param   array $headers
     * @return  void
     * @throws  Zend_Transport_Exception
     */
    protected function _prepareHeaders($headers)
    {
        if (!$this->_mail) {
            /**
             * @see Zend_Mail_Transport_Exception
             */
            require_once 'Zend/Mail/Transport/Exception.php';
            throw new Zend_Mail_Transport_Exception('_prepareHeaders requires a registered Zend_Mail object');
        }

        unset($headers['Bcc']);

        // Prepare headers
        parent::_prepareHeaders($headers);
    }
}
