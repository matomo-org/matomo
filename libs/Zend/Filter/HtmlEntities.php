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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HtmlEntities.php 4135 2007-03-20 12:46:11Z darby $
 */


/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_HtmlEntities implements Zend_Filter_Interface
{
    /**
     * Corresponds to second htmlentities() argument
     *
     * @var integer
     */
    protected $_quoteStyle;

    /**
     * Corresponds to third htmlentities() argument
     *
     * @var string
     */
    protected $_charSet;

    /**
     * Sets filter options
     *
     * @param  integer $quoteStyle
     * @param  string  $charSet
     * @return void
     */
    public function __construct($quoteStyle = ENT_COMPAT, $charSet = 'ISO-8859-1')
    {
        $this->_quoteStyle = $quoteStyle;
        $this->_charSet    = $charSet;
    }

    /**
     * Returns the quoteStyle option
     *
     * @return integer
     */
    public function getQuoteStyle()
    {
        return $this->_quoteStyle;
    }

    /**
     * Sets the quoteStyle option
     *
     * @param  integer $quoteStyle
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setQuoteStyle($quoteStyle)
    {
        $this->_quoteStyle = $quoteStyle;
    }

    /**
     * Returns the charSet option
     *
     * @return string
     */
    public function getCharSet()
    {
        return $this->_charSet;
    }

    /**
     * Sets the charSet option
     *
     * @param  string $charSet
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setCharSet($charSet)
    {
        $this->_charSet = $charSet;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, converting characters to their corresponding HTML entity
     * equivalents where they exist
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return htmlentities((string) $value, $this->_quoteStyle, $this->_charSet);
    }
}
