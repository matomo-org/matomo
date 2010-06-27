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
 * Zend_Form_Decorator_DtDdWrapper
 *
 * Creates an empty <dt> item, and wraps the content in a <dd>. Used as a
 * default decorator for subforms and display groups.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DtDdWrapper.php 22129 2010-05-06 11:20:39Z alab $
 */
class Zend_Form_Decorator_DtDdWrapper extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Render
     *
     * Renders as the following:
     * <dt>$dtLabel</dt>
     * <dd>$content</dd>
     *
     * $dtLabel can be set via 'dtLabel' option, defaults to '\&#160;'
     * 
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
        
        $dtLabel = $this->getOption('dtLabel');
        if( null === $dtLabel ) {
            $dtLabel = '&#160;';
        }

        return '<dt id="' . $elementName . '-label">' . $dtLabel . '</dt>' .
               '<dd id="' . $elementName . '-element">' . $content . '</dd>';
    }
}
