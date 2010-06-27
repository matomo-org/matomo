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
 * Zend_Form_Decorator_Tooltip
 *
 * Will translate the title attribute, if available
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Tooltip.php$
 */
class Zend_Form_Decorator_Tooltip extends Zend_Form_Decorator_Abstract
{
    /**
     * Translates the title attribute if it is available, if the translator is available
     * and if the translator is not disable on the element being rendered.
     *
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        if (null !== ($title = $this->getElement()->getAttrib('title'))) {
            if (null !== ($translator = $this->getElement()->getTranslator())) {
                $title = $translator->translate($title);
            }
        }

        $this->getElement()->setAttrib('title', $title);
        return $content;
    }

}