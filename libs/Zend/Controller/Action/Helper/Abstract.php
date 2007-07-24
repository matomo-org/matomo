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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 


/** Zend_Controller_Exception */
require_once 'Zend/Controller/Action/Exception.php';

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';


/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Controller_Action_Helper_Abstract 
{

    /**
     * $_actionController
     *
     * @var Zend_Controller_Action
     */
    protected $_actionController = null;

    /**
     * Request object
     * 
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Response object
     * 
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * setActionController()
     *
     * @param Zend_Controller_Action $actionController
     * @return Zend_Controller_ActionHelper_Abstract
     */
    public function setActionController(Zend_Controller_Action $actionController)
    {
        $this->_actionController = $actionController;
        return $this;
    }

    /**
     * Retrieve current action controller
     * 
     * @return Zend_Controller_Action
     */
    public function getActionController()
    {
        return $this->_actionController;
    }
    
    /**
     * Hook into action controller initialization
     * 
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * Hook into action controller preDispatch() workflow
     * 
     * @return void
     */
    public function preDispatch()
    {
    }
    
    /**
     * Hook into action controller postDispatch() workflow
     * 
     * @return void
     */
    public function postDispatch()
    {
    }
    
    /**
     * getRequest() - 
     * 
     * @return Zend_Controller_Request_Abstract $request 
     */
    public function getRequest() 
    {
        if (null === $this->_request) {
            $this->_request = $this->_actionController->getRequest();
        }

        return $this->_request;
    }

    /**
     * getResponse() -
     * 
     * @return Zend_Controller_Response_Abstract $response 
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->_response = $this->_actionController->getResponse();
        }

        return $this->_response;
    }
    
    /**
     * getName() 
     *
     * @return string
     */
    public function getName()
    {
        $full_class_name = get_class($this);

        if (strpos($full_class_name, '_') !== false) {
            $helper_name = strrchr($full_class_name, '_');
            return ltrim($helper_name, '_');
        } else {
            return $full_class_name;
        }
    }
}
