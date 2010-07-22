<?php
/**
 * Default renderer for HTML_QuickForm2
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Default.php 299706 2010-05-24 18:32:37Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
// require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Default renderer for QuickForm2
 *
 * Mostly a direct port of Default renderer from QuickForm 3.x package.
 *
 * While almost everything in this class is defined as public, its properties
 * and those methods that are not published (i.e. not in array returned by
 * exportMethods()) will be available to renderer plugins only.
 *
 * The following methods are published:
 *   - {@link reset()}
 *   - {@link setTemplateForClass()}
 *   - {@link setTemplateForId()}
 *   - {@link setErrorTemplate()}
 *   - {@link setElementTemplateForGroupClass()}
 *   - {@link setElementTemplateForGroupId()}
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Renderer_Default extends HTML_QuickForm2_Renderer
{
   /**
    * Whether the form contains required elements
    * @var  bool
    */
    public $hasRequired = false;

   /**
    * HTML generated for the form
    * @var  array
    */
    public $html = array(array());

   /**
    * HTML for hidden elements if 'group_hiddens' option is on
    * @var  string
    */
    public $hiddenHtml = '';

   /**
    * Array of validation errors if 'group_errors' option is on
    * @var  array
    */
    public $errors = array();

   /**
    * Default templates for elements of the given class
    * @var  array
    */
    public $templatesForClass = array(
        'html_quickform2_element_inputhidden' => '<div style="display: none;">{element}</div>',
        'html_quickform2' => '<div class="quickform">{errors}<form{attributes}>{hidden}{content}</form><qf:reqnote><div class="reqnote">{reqnote}</div></qf:reqnote></div>',
        'html_quickform2_container_fieldset' => '<fieldset{attributes}><qf:label><legend id="{id}-legend">{label}</legend></qf:label>{content}</fieldset>',
        'special:error' => array(
            'prefix'    => '<div class="errors"><qf:message><p>{message}</p></qf:message><ul><li>',
            'separator' => '</li><li>',
            'suffix'    => '</li></ul><qf:message><p>{message}</p></qf:message></div>'
        ),
        'html_quickform2_element' => '<div class="row"><label for="{id}" class="element"><qf:required><span class="required">* </span></qf:required>{label}</label><div class="element<qf:error> error</qf:error>"><qf:error><span class="error">{error}</span><br /></qf:error>{element}</div></div>',
        'html_quickform2_container_group' => '<div class="row"><label class="element"><qf:required><span class="required">* </span></qf:required>{label}</label><div class="element group<qf:error> error</qf:error>"><qf:error><span class="error">{error}</span><br /></qf:error>{content}</div></div>'
    );

   /**
    * Custom templates for elements with the given IDs
    * @var  array
    */
    public $templatesForId = array();

   /**
    * Default templates for elements in groups of the given classes
    *
    * Array has the form ('group class' => ('element class' => 'template', ...), ...)
    *
    * @var  array
    */
    public $elementTemplatesForGroupClass = array(
        'html_quickform2_container' => array(
            'html_quickform2_element' => '{element}',
            'html_quickform2_container_fieldset' => '<fieldset{attributes}><qf:label><legend id="{id}-legend">{label}</legend></qf:label>{content}</fieldset>'
        )
    );

   /**
    * Custom templates for grouped elements in the given group IDs
    *
    * Array has the form ('group id' => ('element class' => 'template', ...), ...)
    *
    * @var  array
    */
    public $elementTemplatesForGroupId = array();

   /**
    * Array containing IDs of the groups being rendered
    * @var  array
    */
    public $groupId = array();

    public function exportMethods()
    {
        return array(
            'reset',
            'setTemplateForClass',
            'setTemplateForId',
            'setErrorTemplate',
            'setGroupedTemplateForClass',
            'setElementTemplateForGroupClass',
            'setElementTemplateForGroupId'
        );
    }

   /**
    * Sets template for form elements that are instances of the given class
    *
    * When searching for a template to use, renderer will check for templates
    * set for element's class and its parent classes, until found. Thus a more
    * specific template will override a more generic one.
    *
    * @param    string  Class name
    * @param    mixed   Template to use for elements of that class
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setTemplateForClass($className, $template)
    {
        $this->templatesForClass[strtolower($className)] = $template;
        return $this;
    }

   /**
    * Sets template for form element with the given id
    *
    * If a template is set for an element via this method, it will be used.
    * In the other case a generic template set by {@link setTemplateForClass()}
    * or {@link setGroupedTemplateForClass()} will be used.
    *
    * @param    string  Element's id
    * @param    mixed   Template to use for rendering of that element
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setTemplateForId($id, $template)
    {
        $this->templatesForId[$id] = $template;
        return $this;
    }

   /**
    * Sets template for rendering validation errors
    *
    * This template will be used if 'group_errors' option is set to true.
    * The template array should contain 'prefix', 'suffix' and 'separator'
    * keys.
    *
    * @param    array   Template for validation errors
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setErrorTemplate(array $template)
    {
        return $this->setTemplateForClass('special:error', $template);
    }

   /**
    * Sets grouped elements templates using group class
    *
    * Templates set via {@link setTemplateForClass()} will not be used for
    * grouped form elements. When searching for a template to use, the renderer
    * will first consider template set for a specific group id, then the
    * group templates set by group class.
    *
    * @param    string  Group class name
    * @param    string  Element class name
    * @param    mixed   Template
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setElementTemplateForGroupClass($groupClass, $elementClass, $template)
    {
        $this->elementTemplatesForGroupClass[strtolower($groupClass)][strtolower($elementClass)] = $template;
        return $this;
    }

   /**
    * Sets grouped elements templates using group id
    *
    * Templates set via {@link setTemplateForClass()} will not be used for
    * grouped form elements. When searching for a template to use, the renderer
    * will first consider template set for a specific group id, then the
    * group templates set by group class.
    *
    * @param    string  Group id
    * @param    string  Element class name
    * @param    mixed   Template
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setElementTemplateForGroupId($groupId, $elementClass, $template)
    {
        $this->elementTemplatesForGroupId[$groupId][strtolower($elementClass)] = $template;
        return $this;
    }

   /**
    * Resets the accumulated data
    *
    * This method is called automatically by startForm() method, but should
    * be called manually before calling other rendering methods separately.
    *
    * @return HTML_QuickForm2_Renderer_Default
    */
    public function reset()
    {
        $this->html        = array(array());
        $this->hiddenHtml  = '';
        $this->errors      = array();
        $this->hasRequired = false;
        $this->groupId     = array();

        return $this;
    }

   /**
    * Returns generated HTML
    *
    * @return string
    */
    public function __toString()
    {
        return (isset($this->html[0][0])? $this->html[0][0]: '') .
               $this->hiddenHtml;
    }

   /**
    * Renders a generic element
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $elTpl = $this->prepareTemplate($this->findTemplate($element), $element);
        $this->html[count($this->html) - 1][] = str_replace(array('{element}', '{id}'),
                                                            array($element, $element->getId()), $elTpl);
    }

   /**
    * Renders a hidden element
    *
    * @param    HTML_QuickForm2_Node    Hidden element being rendered
    */
    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->options['group_hiddens']) {
            $this->hiddenHtml .= $element->__toString();
        } else {
            $this->html[count($this->html) - 1][] = str_replace('{element}', $element,
                                                                $this->findTemplate($element));
        }
    }

   /**
    * Starts rendering a generic container, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $this->html[]    = array();
        $this->groupId[] = false;
    }

   /**
    * Finishes rendering a generic container, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        array_pop($this->groupId);

        $cTpl  = str_replace(
            array('{attributes}', '{id}'),
            array($container->getAttributes(true), $container->getId()),
            $this->prepareTemplate($this->findTemplate($container, '{content}'), $container)
        );
        $cHtml  = array_pop($this->html);
        $break  = HTML_Common2::getOption('linebreak');
        $indent = str_repeat(HTML_Common2::getOption('indent'), count($this->html));
        $this->html[count($this->html) - 1][] = str_replace(
            '{content}', $break . $indent . implode($break . $indent, $cHtml), $cTpl
        );
    }

   /**
    * Starts rendering a group, called before processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $this->html[]    = array();
        $this->groupId[] = $group->getId();
    }

   /**
    * Finishes rendering a group, called after processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $gTpl = str_replace(
            array('{attributes}', '{id}'),
            array($group->getAttributes(true), array_pop($this->groupId)),
            $this->prepareTemplate($this->findTemplate($group, '{content}'), $group)
        );

        $separator = $group->getSeparator();
        $elements  = array_pop($this->html);
        if (!is_array($separator)) {
            $content = implode((string)$separator, $elements);
        } else {
            $content    = '';
            $cSeparator = count($separator);
            for ($i = 0, $count = count($elements); $i < $count; $i++) {
                $content .= (0 == $i? '': $separator[($i - 1) % $cSeparator]) .
                            $elements[$i];
            }
        }

        $this->html[count($this->html) - 1][] = str_replace('{content}', $content, $gTpl);
    }

   /**
    * Starts rendering a form, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->reset();
    }

   /**
    * Finishes rendering a form, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $formTpl = str_replace(
            array('{attributes}', '{hidden}', '{errors}'),
            array($form->getAttributes(true), $this->hiddenHtml,
                  $this->outputGroupedErrors()),
            $this->findTemplate($form, '{content}')
        );
        $this->hiddenHtml = '';

        // required note
        if (!$this->hasRequired || $form->toggleFrozen() ||
            empty($this->options['required_note']))
        {
            $formTpl = preg_replace('!<qf:reqnote>.*</qf:reqnote>!isU', '', $formTpl);
        } else {
            $formTpl = str_replace(
                array('<qf:reqnote>', '</qf:reqnote>', '{reqnote}'),
                array('', '', $this->options['required_note']),
                $formTpl
            );
        }

        $break         = HTML_Common2::getOption('linebreak');
        $script        = $this->getJavascriptBuilder()->__toString();
        $this->html[0] = array((empty($script)? '': $script . $break) . str_replace(
            '{content}', $break . implode($break, $this->html[0]), $formTpl
        ));
    }

   /**
    * Creates a error list if 'group_errors' option is true
    *
    * @return   string  HTML with a list of all validation errors
    */
    public function outputGroupedErrors()
    {
        if (empty($this->errors)) {
            return '';
        }
        if (!empty($this->options['errors_prefix'])) {
            $errorHtml = str_replace(array('<qf:message>', '</qf:message>', '{message}'),
                                     array('', '', $this->options['errors_prefix']),
                                     $this->templatesForClass['special:error']['prefix']);
        } else {
            $errorHtml = preg_replace('!<qf:message>.*</qf:message>!isU', '',
                                      $this->templatesForClass['special:error']['prefix']);
        }
        $errorHtml .= implode($this->templatesForClass['special:error']['separator'], $this->errors);
        if (!empty($this->options['errors_suffix'])) {
            $errorHtml .= str_replace(array('<qf:message>', '</qf:message>', '{message}'),
                                      array('', '', $this->options['errors_suffix']),
                                      $this->templatesForClass['special:error']['suffix']);
        } else {
            $errorHtml .= preg_replace('!<qf:message>.*</qf:message>!isU', '',
                                      $this->templatesForClass['special:error']['suffix']);
        }
        return $errorHtml;
    }

   /**
    * Finds a proper template for the element
    *
    * Templates are scanned in a predefined order. First, if a template was
    * set for a specific element by id, it is returned, no matter if the
    * element belongs to a group. If the element does not belong to a group,
    * we try to match a template using the element class.
    * But, if the element belongs to a group, templates are first looked up
    * using the containing group id, then using the containing group class.
    * When no template is found, the provided default template is returned.
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    * @param    string                  Default template to use if not found
    * @return   string  Template
    */
    public function findTemplate(HTML_QuickForm2_Node $element, $default = '{element}')
    {
        if (!empty($this->templatesForId[$element->getId()])) {
            return $this->templatesForId[$element->getId()];
        }
        $class          = strtolower(get_class($element));
        $groupId        = end($this->groupId);
        $elementClasses = array();
        do {
            if (empty($groupId) && !empty($this->templatesForClass[$class])) {
                return $this->templatesForClass[$class];
            }
            $elementClasses[$class] = true;
        } while ($class = strtolower(get_parent_class($class)));

        if (!empty($groupId)) {
            if (!empty($this->elementTemplatesForGroupId[$groupId])) {
                while (list($elClass) = each($elementClasses)) {
                    if (!empty($this->elementTemplatesForGroupId[$groupId][$elClass])) {
                        return $this->elementTemplatesForGroupId[$groupId][$elClass];
                    }
                }
            }

            $group = $element->getContainer();
            $grClass = strtolower(get_class($group));
            do {
                if (!empty($this->elementTemplatesForGroupClass[$grClass])) {
                    reset($elementClasses);
                    while (list($elClass) = each($elementClasses)) {
                        if (!empty($this->elementTemplatesForGroupClass[$grClass][$elClass])) {
                            return $this->elementTemplatesForGroupClass[$grClass][$elClass];
                        }
                    }
                }
            } while ($grClass = strtolower(get_parent_class($grClass)));
        }
        return $default;
    }

   /**
    * Processes the element's template, adding label(s), required note and error message
    *
    * @param    string                  Element template
    * @param    HTML_QuickForm2_Node    Element being rendered
    * @return   string  Template with some substitutions done
    */
    public function prepareTemplate($elTpl, HTML_QuickForm2_Node $element)
    {
        // if element is required
        $elTpl = $this->markRequired($elTpl, $element->isRequired());
        $elTpl = $this->outputError($elTpl, $element->getError());
        return $this->outputLabel($elTpl, $element->getLabel());
    }

   /**
    * Marks element required or removes "required" block
    *
    * @param    string  Element template
    * @param    bool    Whether element is required
    * @return   string  Template with processed "required" block
    */
    public function markRequired($elTpl, $required)
    {
        if ($required) {
            $this->hasRequired = true;
            $elTpl = str_replace(array('<qf:required>', '</qf:required>'),
                                  array('', ''), $elTpl);
        } else {
            $elTpl = preg_replace('!<qf:required>.*</qf:required>!isU', '', $elTpl);
        }
        return $elTpl;
    }

   /**
    * Outputs element error, removes empty error blocks
    *
    * @param    string  Element template
    * @param    string  Validation error for the element
    * @return   string  Template with error substitutions done
    */
    public function outputError($elTpl, $error)
    {
        if ($error && !$this->options['group_errors']) {
            $elTpl = str_replace(array('<qf:error>', '</qf:error>', '{error}'),
                                  array('', '', $error), $elTpl);
        } else {
            if ($error && $this->options['group_errors']) {
                $this->errors[] = $error;
            }
            $elTpl = preg_replace('!<qf:error>.*</qf:error>!isU', '', $elTpl);
        }
        return $elTpl;
    }

   /**
    * Outputs element's label(s), removes empty label blocks
    *
    * @param    string  Element template
    * @param    mixed   Element label(s)
    * @return   string  Template with label substitutions done
    */
    public function outputLabel($elTpl, $label)
    {
        $mainLabel = is_array($label)? array_shift($label): $label;
        $elTpl     = str_replace('{label}', $mainLabel, $elTpl);
        if (false !== strpos($elTpl, '<qf:label>')) {
            if ($mainLabel) {
                $elTpl = str_replace(array('<qf:label>', '</qf:label>'), array('', ''), $elTpl);
            } else {
                $elTpl = preg_replace('!<qf:label>.*</qf:label>!isU', '', $elTpl);
            }
        }
        if (is_array($label)) {
            foreach($label as $key => $text) {
                $key   = is_int($key)? $key + 2: $key;
                $elTpl = str_replace(array('<qf:label_' . $key . '>', '</qf:label_' . $key . '>', '{label_' . $key . '}'),
                                     array('', '', $text), $elTpl);
            }
        }
        if (strpos($elTpl, '{label_')) {
            $elTpl = preg_replace('!<qf:label_([^>]+)>.*</qf:label_\1>!isU', '', $elTpl);
        }
        return $elTpl;
    }
}
?>
