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
 * @package    Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 4389 2007-04-06 15:17:41Z mike $
 */

/** Zend_Log_Formatter_Interface */
require_once 'Zend/Log/Formatter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 4389 2007-04-06 15:17:41Z mike $
 */ 
class Zend_Log_Formatter_Xml implements Zend_Log_Formatter_Interface
{
    /**
     * @var Relates XML elements to log data field keys.
     */
    protected $_rootElement;

    /**
     * @var Relates XML elements to log data field keys.
     */
    protected $_elementMap;

    /**
     * Class constructor
     *
     * @param array $elementMap
     */
    public function __construct($rootElement = 'logEntry', $elementMap = null)
    {
        $this->_rootElement = $rootElement;
        $this->_elementMap  = $elementMap;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        if ($this->_elementMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_elementMap as $elementName => $fieldKey) {
                $dataToInsert[$elementName] = $event[$fieldKey];
            }
        }        
        
        $dom = new DOMDocument();
        $elt = $dom->appendChild(new DOMElement($this->_rootElement));

        foreach ($dataToInsert as $key => $value) {
            $elt->appendChild(new DOMElement($key, $value));
        }
        
        $xml = $dom->saveXML();
        $xml = preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $xml);
        
        return $xml . PHP_EOL;
    }

}
