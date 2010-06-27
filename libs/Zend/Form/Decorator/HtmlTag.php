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

/**
 * @see Zend_Form_Decorator_Abstract
 */
// require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_Element_HtmlTag
 *
 * Wraps content in an HTML block tag.
 *
 * Options accepted are:
 * - tag: tag to use in decorator
 * - noAttribs: do not render attributes in the opening tag
 * - placement: 'append' or 'prepend'. If 'append', renders opening and
 *   closing tag after content; if prepend, renders opening and closing tag
 *   before content.
 * - openOnly: render opening tag only
 * - closeOnly: render closing tag only
 *
 * Any other options passed are processed as HTML attributes of the tag.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HtmlTag.php 20104 2010-01-06 21:26:01Z matthew $
 */
class Zend_Form_Decorator_HtmlTag extends Zend_Form_Decorator_Abstract
{
    /**
     * Character encoding to use when escaping attributes
     * @var string
     */
    protected $_encoding;

    /**
     * Placement; default to surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * HTML tag to use
     * @var string
     */
    protected $_tag;

    /**
     * @var Zend_Filter
     */
    protected $_tagFilter;

    /**
     * Convert options to tag attributes
     *
     * @return string
     */
    protected function _htmlAttribs(array $attribs)
    {
        $xhtml = '';
        $enc   = $this->_getEncoding();
        foreach ((array) $attribs as $key => $val) {
            $key = htmlspecialchars($key, ENT_COMPAT, $enc);
            if (is_array($val)) {
                $val = implode(' ', $val);
            }
            $val    = htmlspecialchars($val, ENT_COMPAT, $enc);
            $xhtml .= " $key=\"$val\"";
        }
        return $xhtml;
    }

    /**
     * Normalize tag
     *
     * Ensures tag is alphanumeric characters only, and all lowercase.
     *
     * @param  string $tag
     * @return string
     */
    public function normalizeTag($tag)
    {
        if (!isset($this->_tagFilter)) {
            // require_once 'Zend/Filter.php';
            // require_once 'Zend/Filter/Alnum.php';
            // require_once 'Zend/Filter/StringToLower.php';
            $this->_tagFilter = new Zend_Filter();
            $this->_tagFilter->addFilter(new Zend_Filter_Alnum())
                             ->addFilter(new Zend_Filter_StringToLower());
        }
        return $this->_tagFilter->filter($tag);
    }

    /**
     * Set tag to use
     *
     * @param  string $tag
     * @return Zend_Form_Decorator_HtmlTag
     */
    public function setTag($tag)
    {
        $this->_tag = $this->normalizeTag($tag);
        return $this;
    }

    /**
     * Get tag
     *
     * If no tag is registered, either via setTag() or as an option, uses 'div'.
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            if (null === ($tag = $this->getOption('tag'))) {
                $this->setTag('div');
            } else {
                $this->setTag($tag);
                $this->removeOption('tag');
            }
        }

        return $this->_tag;
    }

    /**
     * Get the formatted open tag
     *
     * @param  string $tag
     * @param  array $attribs
     * @return string
     */
    protected function _getOpenTag($tag, array $attribs = null)
    {
        $html = '<' . $tag;
        if (null !== $attribs) {
            $html .= $this->_htmlAttribs($attribs);
        }
        $html .= '>';
        return $html;
    }

    /**
     * Get formatted closing tag
     *
     * @param  string $tag
     * @return string
     */
    protected function _getCloseTag($tag)
    {
        return '</' . $tag . '>';
    }

    /**
     * Render content wrapped in an HTML tag
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $tag       = $this->getTag();
        $placement = $this->getPlacement();
        $noAttribs = $this->getOption('noAttribs');
        $openOnly  = $this->getOption('openOnly');
        $closeOnly = $this->getOption('closeOnly');
        $this->removeOption('noAttribs');
        $this->removeOption('openOnly');
        $this->removeOption('closeOnly');

        $attribs = null;
        if (!$noAttribs) {
            $attribs = $this->getOptions();
        }

        switch ($placement) {
            case self::APPEND:
                if ($closeOnly) {
                    return $content . $this->_getCloseTag($tag);
                }
                if ($openOnly) {
                    return $content . $this->_getOpenTag($tag, $attribs);
                }
                return $content
                     . $this->_getOpenTag($tag, $attribs)
                     . $this->_getCloseTag($tag);
            case self::PREPEND:
                if ($closeOnly) {
                    return $this->_getCloseTag($tag) . $content;
                }
                if ($openOnly) {
                    return $this->_getOpenTag($tag, $attribs) . $content;
                }
                return $this->_getOpenTag($tag, $attribs)
                     . $this->_getCloseTag($tag)
                     . $content;
            default:
                return (($openOnly || !$closeOnly) ? $this->_getOpenTag($tag, $attribs) : '')
                     . $content
                     . (($closeOnly || !$openOnly) ? $this->_getCloseTag($tag) : '');
        }
    }

    /**
     * Get encoding for use with htmlspecialchars()
     * 
     * @return string
     */
    protected function _getEncoding()
    {
        if (null !== $this->_encoding) {
            return $this->_encoding;
        }

        if (null === ($element = $this->getElement())) {
            $this->_encoding = 'UTF-8';
        } elseif (null === ($view = $element->getView())) {
            $this->_encoding = 'UTF-8';
        } elseif (!$view instanceof Zend_View_Abstract
            && !method_exists($view, 'getEncoding')
        ) {
            $this->_encoding = 'UTF-8';
        } else {
            $this->_encoding = $view->getEncoding();
        }
        return $this->_encoding;
    }
}
