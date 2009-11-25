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
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xml.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @see Zend_Config_Writer
 */
require_once 'Zend/Config/Writer.php';

/**
 * @see Zend_Config_Xml
 */
require_once 'Zend/Config/Xml.php';

/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Writer_Xml extends Zend_Config_Writer
{
    /**
     * Filename to write to
     *
     * @var string
     */
    protected $_filename = null;

    /**
     * Wether to exclusively lock the file or not
     *
     * @var boolean
     */
    protected $_exclusiveLock = false;

    /**
     * Set the target filename
     *
     * @param  string $filename
     * @return Zend_Config_Writer_Xml
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;

        return $this;
    }

    /**
     * Set wether to exclusively lock the file or not
     *
     * @param  boolean     $exclusiveLock
     * @return Zend_Config_Writer_Array
     */
    public function setExclusiveLock($exclusiveLock)
    {
        $this->_exclusiveLock = $exclusiveLock;

        return $this;
    }

    /**
     * Defined by Zend_Config_Writer
     *
     * @param  string      $filename
     * @param  Zend_Config $config
     * @param  boolean     $exclusiveLock
     * @throws Zend_Config_Exception When filename was not set
     * @throws Zend_Config_Exception When filename is not writable
     * @return void
     */
    public function write($filename = null, Zend_Config $config = null, $exclusiveLock = null)
    {
        if ($filename !== null) {
            $this->setFilename($filename);
        }

        if ($config !== null) {
            $this->setConfig($config);
        }

        if ($exclusiveLock !== null) {
            $this->setExclusiveLock($exclusiveLock);
        }

        if ($this->_filename === null) {
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('No filename was set');
        }

        if ($this->_config === null) {
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('No config was set');
        }

        $xml         = new SimpleXMLElement('<zend-config xmlns:zf="' . Zend_Config_Xml::XML_NAMESPACE . '"/>');
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        if (is_string($sectionName)) {
            $child = $xml->addChild($sectionName);

            $this->_addBranch($this->_config, $child, $xml);
        } else {
            foreach ($this->_config as $sectionName => $data) {
                if (!($data instanceof Zend_Config)) {
                    $xml->addChild($sectionName, (string) $data);
                } else {
                    $child = $xml->addChild($sectionName);

                    if (isset($extends[$sectionName])) {
                        $child->addAttribute('zf:extends', $extends[$sectionName], Zend_Config_Xml::XML_NAMESPACE);
                    }

                    $this->_addBranch($data, $child, $xml);
                }
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        $xmlString = $dom->saveXML();

        $flags = 0;

        if ($this->_exclusiveLock) {
            $flags |= LOCK_EX;
        }

        $result = @file_put_contents($this->_filename, $xmlString, $flags);

        if ($result === false) {
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Could not write to file "' . $this->_filename . '"');
        }
    }

    /**
     * Add a branch to an XML object recursively
     *
     * @param  Zend_Config      $config
     * @param  SimpleXMLElement $xml
     * @param  SimpleXMLElement $parent
     * @return void
     */
    protected function _addBranch(Zend_Config $config, SimpleXMLElement $xml, SimpleXMLElement $parent)
    {
        $branchType = null;

        foreach ($config as $key => $value) {
            if ($branchType === null) {
                if (is_numeric($key)) {
                    $branchType = 'numeric';
                    $branchName = $xml->getName();
                    $xml        = $parent;

                    unset($parent->{$branchName});
                } else {
                    $branchType = 'string';
                }
            } else if ($branchType !== (is_numeric($key) ? 'numeric' : 'string')) {
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception('Mixing of string and numeric keys is not allowed');
            }

            if ($branchType === 'numeric') {
                if ($value instanceof Zend_Config) {
                    $child = $parent->addChild($branchName, (string) $value);

                    $this->_addBranch($value, $child, $parent);
                } else {
                    $parent->addChild($branchName, (string) $value);
                }
            } else {
                if ($value instanceof Zend_Config) {
                    $child = $xml->addChild($key);

                    $this->_addBranch($value, $child, $xml);
                } else {
                    $xml->addChild($key, (string) $value);
                }
            }
        }
    }
}
