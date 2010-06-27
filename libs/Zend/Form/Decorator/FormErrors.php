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
 * Zend_Form_Decorator_FormErrors
 *
 * Displays all form errors in one view.
 *
 * Any options passed will be used as HTML attributes of the ul tag for the errors.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FormErrors.php 22317 2010-05-29 10:13:31Z alab $
 */
class Zend_Form_Decorator_FormErrors extends Zend_Form_Decorator_Abstract
{
    /**
     * Default values for markup options
     * @var array
     */
    protected $_defaults = array(
        'ignoreSubForms'          => false,
        'showCustomFormErrors'    => true,
        'onlyCustomFormErrors'    => false,
        'markupElementLabelEnd'   => '</b>',
        'markupElementLabelStart' => '<b>',
        'markupListEnd'           => '</ul>',
        'markupListItemEnd'       => '</li>',
        'markupListItemStart'     => '<li>',
        'markupListStart'         => '<ul class="form-errors">',
    );

    /**#@+
     * Markup options
     * @var string
     */
    protected $_ignoreSubForms;
    protected $_showCustomFormErrors;
    protected $_onlyCustomFormErrors;
    protected $_markupElementLabelEnd;
    protected $_markupElementLabelStart;
    protected $_markupListEnd;
    protected $_markupListItemEnd;
    protected $_markupListItemStart;
    protected $_markupListStart;
    /**#@-*/

    /**
     * Render errors
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form = $this->getElement();
        if (!$form instanceof Zend_Form) {
            return $content;
        }

        $view = $form->getView();
        if (null === $view) {
            return $content;
        }

        $this->initOptions();
        $markup = $this->_recurseForm($form, $view);

        if (empty($markup)) {
            return $content;
        }

        $markup = $this->getMarkupListStart()
                . $markup
                . $this->getMarkupListEnd();

        switch ($this->getPlacement()) {
            case self::APPEND:
                return $content . $this->getSeparator() . $markup;
            case self::PREPEND:
                return $markup . $this->getSeparator() . $content;
        }
    }

    /**
     * Initialize options
     *
     * @return void
     */
    public function initOptions()
    {
        $this->getMarkupElementLabelEnd();
        $this->getMarkupElementLabelStart();
        $this->getMarkupListEnd();
        $this->getMarkupListItemEnd();
        $this->getMarkupListItemStart();
        $this->getMarkupListStart();
        $this->getPlacement();
        $this->getSeparator();
        $this->ignoreSubForms();
        $this->getShowCustomFormErrors();
        $this->getOnlyCustomFormErrors();
    }

    /**
     * Retrieve markupElementLabelStart
     *
     * @return string
     */
    public function getMarkupElementLabelStart()
    {
        if (null === $this->_markupElementLabelStart) {
            if (null === ($markupElementLabelStart = $this->getOption('markupElementLabelStart'))) {
                $this->setMarkupElementLabelStart($this->_defaults['markupElementLabelStart']);
            } else {
                $this->setMarkupElementLabelStart($markupElementLabelStart);
                $this->removeOption('markupElementLabelStart');
            }
        }

        return $this->_markupElementLabelStart;
    }

    /**
     * Set markupElementLabelStart
     *
     * @param  string $markupElementLabelStart
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupElementLabelStart($markupElementLabelStart)
    {
        $this->_markupElementLabelStart = $markupElementLabelStart;
        return $this;
    }

    /**
     * Retrieve markupElementLabelEnd
     *
     * @return string
     */
    public function getMarkupElementLabelEnd()
    {
        if (null === $this->_markupElementLabelEnd) {
            if (null === ($markupElementLabelEnd = $this->getOption('markupElementLabelEnd'))) {
                $this->setMarkupElementLabelEnd($this->_defaults['markupElementLabelEnd']);
            } else {
                $this->setMarkupElementLabelEnd($markupElementLabelEnd);
                $this->removeOption('markupElementLabelEnd');
            }
        }

        return $this->_markupElementLabelEnd;
    }

    /**
     * Set markupElementLabelEnd
     *
     * @param  string $markupElementLabelEnd
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupElementLabelEnd($markupElementLabelEnd)
    {
        $this->_markupElementLabelEnd = $markupElementLabelEnd;
        return $this;
    }

    /**
     * Retrieve markupListStart
     *
     * @return string
     */
    public function getMarkupListStart()
    {
        if (null === $this->_markupListStart) {
            if (null === ($markupListStart = $this->getOption('markupListStart'))) {
                $this->setMarkupListStart($this->_defaults['markupListStart']);
            } else {
                $this->setMarkupListStart($markupListStart);
                $this->removeOption('markupListStart');
            }
        }

        return $this->_markupListStart;
    }

    /**
     * Set markupListStart
     *
     * @param  string $markupListStart
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupListStart($markupListStart)
    {
        $this->_markupListStart = $markupListStart;
        return $this;
    }

    /**
     * Retrieve markupListEnd
     *
     * @return string
     */
    public function getMarkupListEnd()
    {
        if (null === $this->_markupListEnd) {
            if (null === ($markupListEnd = $this->getOption('markupListEnd'))) {
                $this->setMarkupListEnd($this->_defaults['markupListEnd']);
            } else {
                $this->setMarkupListEnd($markupListEnd);
                $this->removeOption('markupListEnd');
            }
        }

        return $this->_markupListEnd;
    }

    /**
     * Set markupListEnd
     *
     * @param  string $markupListEnd
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupListEnd($markupListEnd)
    {
        $this->_markupListEnd = $markupListEnd;
        return $this;
    }

    /**
     * Retrieve markupListItemStart
     *
     * @return string
     */
    public function getMarkupListItemStart()
    {
        if (null === $this->_markupListItemStart) {
            if (null === ($markupListItemStart = $this->getOption('markupListItemStart'))) {
                $this->setMarkupListItemStart($this->_defaults['markupListItemStart']);
            } else {
                $this->setMarkupListItemStart($markupListItemStart);
                $this->removeOption('markupListItemStart');
            }
        }

        return $this->_markupListItemStart;
    }

    /**
     * Set markupListItemStart
     *
     * @param  string $markupListItemStart
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupListItemStart($markupListItemStart)
    {
        $this->_markupListItemStart = $markupListItemStart;
        return $this;
    }

    /**
     * Retrieve markupListItemEnd
     *
     * @return string
     */
    public function getMarkupListItemEnd()
    {
        if (null === $this->_markupListItemEnd) {
            if (null === ($markupListItemEnd = $this->getOption('markupListItemEnd'))) {
                $this->setMarkupListItemEnd($this->_defaults['markupListItemEnd']);
            } else {
                $this->setMarkupListItemEnd($markupListItemEnd);
                $this->removeOption('markupListItemEnd');
            }
        }

        return $this->_markupListItemEnd;
    }

    /**
     * Set markupListItemEnd
     *
     * @param  string $markupListItemEnd
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setMarkupListItemEnd($markupListItemEnd)
    {
        $this->_markupListItemEnd = $markupListItemEnd;
        return $this;
    }

    /**
     * Retrieve ignoreSubForms
     *
     * @return bool
     */
    public function ignoreSubForms()
    {
        if (null === $this->_ignoreSubForms) {
            if (null === ($ignoreSubForms = $this->getOption('ignoreSubForms'))) {
                $this->setIgnoreSubForms($this->_defaults['ignoreSubForms']);
            } else {
                $this->setIgnoreSubForms($ignoreSubForms);
                $this->removeOption('ignoreSubForms');
            }
        }

        return $this->_ignoreSubForms;
    }

    /**
     * Set ignoreSubForms
     *
     * @param  bool $ignoreSubForms
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setIgnoreSubForms($ignoreSubForms)
    {
        $this->_ignoreSubForms = (bool) $ignoreSubForms;
        return $this;
    }

    /**
     * Get showCustomFormErrors
     * 
     * @return bool
     */
    public function getShowCustomFormErrors()
    {
        if (null === $this->_showCustomFormErrors) {
            if (null === ($how =  $this->getOption('showCustomFormErrors'))) {
                $this->setShowCustomFormErrors($this->_defaults['showCustomFormErrors']);
            } else {
                $this->setShowCustomFormErrors($show);
                $this->removeOption('showCustomFormErrors');
            }
        }
        return $this->_showCustomFormErrors;
    }

    /**
     * Set showCustomFormErrors
     *
     * @param  bool $showCustomFormErrors
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setShowCustomFormErrors($showCustomFormErrors)
    {
        $this->_showCustomFormErrors = (bool)$showCustomFormErrors;
        return $this;
    }

    /**
     * Get onlyCustomFormErrors
     * 
     * @return bool
     */
    public function getOnlyCustomFormErrors()
    {
        if (null === $this->_onlyCustomFormErrors) {
            if (null === ($show =  $this->getOption('onlyCustomFormErrors'))) {
                $this->setOnlyCustomFormErrors($this->_defaults['onlyCustomFormErrors']);
            } else {
                $this->setOnlyCustomFormErrors($show);
                $this->removeOption('onlyCustomFormErrors');
            }
        }
        return $this->_onlyCustomFormErrors;
    }

    /**
     * Set onlyCustomFormErrors, whether to display elements messages
     * in addition to custom form messages.
     *
     * @param  bool $onlyCustomFormErrors
     * @return Zend_Form_Decorator_FormErrors
     */
    public function setOnlyCustomFormErrors($onlyCustomFormErrors)
    {
        $this->_onlyCustomFormErrors = (bool)$onlyCustomFormErrors;
        return $this;
    }

    /**
     * Render element label
     *
     * @param  Zend_Form_Element $element
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function renderLabel(Zend_Form_Element $element, Zend_View_Interface $view)
    {
        $label = $element->getLabel();
        if (empty($label)) {
            $label = $element->getName();
        }

        return $this->getMarkupElementLabelStart()
             . $view->escape($label)
             . $this->getMarkupElementLabelEnd();
    }

    /**
     * Recurse through a form object, rendering errors
     *
     * @param  Zend_Form $form
     * @param  Zend_View_Interface $view
     * @return string
     */
    protected function _recurseForm(Zend_Form $form, Zend_View_Interface $view)
    {
        $content = '';

        $custom = $form->getCustomMessages();
        if ($this->getShowCustomFormErrors() && count($custom)) {
            $content .= $this->getMarkupListItemStart()
                     .  $view->formErrors($custom, $this->getOptions())
                     .  $this->getMarkupListItemEnd();
        }
        foreach ($form->getElementsAndSubFormsOrdered() as $subitem) {
            if ($subitem instanceof Zend_Form_Element && !$this->getOnlyCustomFormErrors()) {
                $messages = $subitem->getMessages();
                if (count($messages)) {
                    $subitem->setView($view);
                    $content .= $this->getMarkupListItemStart()
                             .  $this->renderLabel($subitem, $view)
                             .  $view->formErrors($messages, $this->getOptions())
                             .  $this->getMarkupListItemEnd();
                }
            } else if ($subitem instanceof Zend_Form && !$this->ignoreSubForms()) {
                $content .= $this->getMarkupListStart()
                          . $this->_recurseForm($subitem, $view)
                          . $this->getMarkupListEnd();
            }
        }
        return $content;
    }
}
