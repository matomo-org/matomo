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
 * to padraic dot brady at yahoo dot com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Writer_Entry_Rss
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
 
/**
 * @see Zend_Feed_Writer_Extension_RendererInterface
 */
require_once 'Zend/Feed/Writer/Extension/RendererInterface.php';
 
 /**
 * @category   Zend
 * @package    Zend_Feed_Writer_Entry_Rss
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Feed_Writer_Extension_RendererAbstract
    implements Zend_Feed_Writer_Extension_RendererInterface
{
    /**
     * @var DOMDocument
     */
    protected $_dom = null;
    
    /**
     * @var mixed
     */
    protected $_entry = null;
    
    /**
     * @var DOMElement
     */
    protected $_base = null;
    
    /**
     * @var mixed
     */
    protected $_container = null;
    
    /**
     * @var string
     */
    protected $_type = null;
    
    /**
     * @var DOMElement
     */
    protected $_rootElement = null;
    
    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Constructor
     * 
     * @param  mixed $container 
     * @return void
     */
    public function __construct($container)
    {
        $this->_container = $container;
    }
    
    /**
     * Set feed encoding
     * 
     * @param  string $enc 
     * @return Zend_Feed_Writer_Extension_RendererAbstract
     */
    public function setEncoding($enc)
    {
        $this->_encoding = $enc;
        return $this;
    }
    
    /**
     * Get feed encoding
     * 
     * @return void
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * Set DOMDocument and DOMElement on which to operate
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $base 
     * @return Zend_Feed_Writer_Extension_RendererAbstract
     */
    public function setDomDocument(DOMDocument $dom, DOMElement $base)
    {
        $this->_dom  = $dom;
        $this->_base = $base;
        return $this;
    }
    
    /**
     * Get data container being rendered
     * 
     * @return mixed
     */
    public function getDataContainer()
    {
        return $this->_container;
    }
    
    /**
     * Set feed type
     * 
     * @param  string $type 
     * @return Zend_Feed_Writer_Extension_RendererAbstract
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }
    
    /**
     * Get feedtype
     * 
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * Set root element of document 
     * 
     * @param  DOMElement $root 
     * @return Zend_Feed_Writer_Extension_RendererAbstract
     */
    public function setRootElement(DOMElement $root)
    {
        $this->_rootElement = $root;
        return $this;
    }
    
    /**
     * Get root element
     * 
     * @return DOMElement
     */
    public function getRootElement()
    {
        return $this->_rootElement;
    }
    
    /**
     * Append namespaces to feed
     * 
     * @return void
     */
    abstract protected function _appendNamespaces();
}
