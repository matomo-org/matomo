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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SeparatorToSeparator.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_PregReplace
 */
// require_once 'Zend/Filter/PregReplace.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Word_SeparatorToSeparator extends Zend_Filter_PregReplace
{

    protected $_searchSeparator = null;
    protected $_replacementSeparator = null;

    /**
     * Constructor
     *
     * @param  string  $searchSeparator      Seperator to search for
     * @param  string  $replacementSeperator Seperator to replace with
     * @return void
     */
    public function __construct($searchSeparator = ' ', $replacementSeparator = '-')
    {
        $this->setSearchSeparator($searchSeparator);
        $this->setReplacementSeparator($replacementSeparator);
    }

    /**
     * Sets a new seperator to search for
     *
     * @param  string  $separator  Seperator to search for
     * @return $this
     */
    public function setSearchSeparator($separator)
    {
        $this->_searchSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set seperator to search for
     *
     * @return  string
     */
    public function getSearchSeparator()
    {
        return $this->_searchSeparator;
    }

    /**
     * Sets a new seperator which replaces the searched one
     *
     * @param  string  $separator  Seperator which replaces the searched one
     * @return $this
     */
    public function setReplacementSeparator($separator)
    {
        $this->_replacementSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set seperator which replaces the searched one
     *
     * @return  string
     */
    public function getReplacementSeparator()
    {
        return $this->_replacementSeparator;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, replacing the searched seperators with the defined ones
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return $this->_separatorToSeparatorFilter($value);
    }

    /**
     * Do the real work, replaces the seperator to search for with the replacement seperator
     *
     * Returns the replaced string
     *
     * @param  string $value
     * @return string
     */
    protected function _separatorToSeparatorFilter($value)
    {
        if ($this->_searchSeparator == null) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('You must provide a search separator for this filter to work.');
        }

        $this->setMatchPattern('#' . preg_quote($this->_searchSeparator, '#') . '#');
        $this->setReplacement($this->_replacementSeparator);
        return parent::filter($value);
    }

}