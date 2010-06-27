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
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_PregReplace
 */
// require_once 'Zend/Filter/PregReplace.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @uses       Zend_Filter_PregReplace
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Filter_Word_Separator_Abstract extends Zend_Filter_PregReplace
{

    protected $_separator = null;

    /**
     * Constructor
     *
     * @param  string $separator Space by default
     * @return void
     */
    public function __construct($separator = ' ')
    {
        $this->setSeparator($separator);
    }

    /**
     * Sets a new seperator
     *
     * @param  string  $separator  Seperator
     * @return $this
     */
    public function setSeparator($separator)
    {
        if ($separator == null) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('"' . $separator . '" is not a valid separator.');
        }
        $this->_separator = $separator;
        return $this;
    }

    /**
     * Returns the actual set seperator
     *
     * @return  string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

}