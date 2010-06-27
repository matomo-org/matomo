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
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Form_Decorator_Interface
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
interface Zend_Form_Decorator_Interface
{
    /**
     * Constructor
     *
     * Accept options during initialization.
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null);

    /**
     * Set an element to decorate
     *
     * While the name is "setElement", a form decorator could decorate either
     * an element or a form object.
     *
     * @param  mixed $element
     * @return Zend_Form_Decorator_Interface
     */
    public function setElement($element);

    /**
     * Retrieve current element
     *
     * @return mixed
     */
    public function getElement();

    /**
     * Set decorator options from an array
     *
     * @param  array $options
     * @return Zend_Form_Decorator_Interface
     */
    public function setOptions(array $options);

    /**
     * Set decorator options from a config object
     *
     * @param  Zend_Config $config
     * @return Zend_Form_Decorator_Interface
     */
    public function setConfig(Zend_Config $config);

    /**
     * Set a single option
     *
     * @param  string $key
     * @param  mixed $value
     * @return Zend_Form_Decorator_Interface
     */
    public function setOption($key, $value);

    /**
     * Retrieve a single option
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key);

    /**
     * Retrieve decorator options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Delete a single option
     *
     * @param  string $key
     * @return bool
     */
    public function removeOption($key);

    /**
     * Clear all options
     *
     * @return Zend_Form_Decorator_Interface
     */
    public function clearOptions();

    /**
     * Render the element
     *
     * @param  string $content Content to decorate
     * @return string
     */
    public function render($content);
}
