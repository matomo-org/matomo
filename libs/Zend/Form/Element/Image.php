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
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
// require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Image form element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 22329 2010-05-30 15:12:58Z bittarman $
 */
class Zend_Form_Element_Image extends Zend_Form_Element_Xhtml
{
    /**
     * What view helper to use when using view helper decorator
     * @var string
     */
    public $helper = 'formImage';

    /**
     * Image source
     * @var string
     */
    public $src;

    /**
     * Image value
     * @var mixed
     */
    protected $_imageValue;

    /**
     * Load default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Tooltip')
                 ->addDecorator('Image')
                 ->addDecorator('Errors')
                 ->addDecorator('HtmlTag', array('tag' => 'dd'))
                 ->addDecorator('Label', array('tag' => 'dt'));
        }
        return $this;
    }

    /**
     * Set image path
     *
     * @param  string $path
     * @return Zend_Form_Element_Image
     */
    public function setImage($path)
    {
        $this->src = (string) $path;
        return $this;
    }

    /**
     * Get image path
     *
     * @return string
     */
    public function getImage()
    {
        return $this->src;
    }

    /**
     * Set image value to use when submitted
     *
     * @param  mixed $value
     * @return Zend_Form_Element_Image
     */
    public function setImageValue($value)
    {
        $this->_imageValue = $value;
        return $this;
    }

    /**
     * Get image value to use when submitted
     *
     * @return mixed
     */
    public function getImageValue()
    {
        return $this->_imageValue;
    }

    /**
     * Was this element used to submit the form?
     *
     * @return bool
     */
    public function isChecked()
    {
        $imageValue = $this->getImageValue();
        return ((null !== $imageValue) && ($this->getValue() == $imageValue));
    }

}
