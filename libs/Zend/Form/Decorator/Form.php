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
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
// require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_Form
 *
 * Render a Zend_Form object.
 *
 * Accepts following options:
 * - separator: Separator to use between elements
 * - helper: which view helper to use when rendering form. Should accept three
 *   arguments, string content, a name, and an array of attributes.
 *
 * Any other options passed will be used as HTML attributes of the form tag.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Form_Decorator_Form extends Zend_Form_Decorator_Abstract
{
    /**
     * Default view helper
     * @var string
     */
    protected $_helper = 'form';

    /**
     * Set view helper for rendering form
     *
     * @param  string $helper
     * @return Zend_Form_Decorator_Form
     */
    public function setHelper($helper)
    {
        $this->_helper = (string) $helper;
        return $this;
    }

    /**
     * Get view helper for rendering form
     *
     * @return string
     */
    public function getHelper()
    {
        if (null !== ($helper = $this->getOption('helper'))) {
            $this->setHelper($helper);
            $this->removeOption('helper');
        }
        return $this->_helper;
    }

    /**
     * Retrieve decorator options
     *
     * Assures that form action and method are set, and sets appropriate
     * encoding type if current method is POST.
     *
     * @return array
     */
    public function getOptions()
    {
        if (null !== ($element = $this->getElement())) {
            if ($element instanceof Zend_Form) {
                $element->getAction();
                $method = $element->getMethod();
                if ($method == Zend_Form::METHOD_POST) {
                    $this->setOption('enctype', 'application/x-www-form-urlencoded');
                }
                foreach ($element->getAttribs() as $key => $value) {
                    $this->setOption($key, $value);
                }
            } elseif ($element instanceof Zend_Form_DisplayGroup) {
                foreach ($element->getAttribs() as $key => $value) {
                    $this->setOption($key, $value);
                }
            }
        }

        if (isset($this->_options['method'])) {
            $this->_options['method'] = strtolower($this->_options['method']);
        }

        return $this->_options;
    }

    /**
     * Render a form
     *
     * Replaces $content entirely from currently set element.
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form    = $this->getElement();
        $view    = $form->getView();
        if (null === $view) {
            return $content;
        }

        $helper        = $this->getHelper();
        $attribs       = $this->getOptions();
        $name          = $form->getFullyQualifiedName();
        $attribs['id'] = $form->getId();
        return $view->$helper($name, $attribs, $content);
    }
}
