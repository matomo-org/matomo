<?php
/**
 * Base class for HTML_QuickForm2 groups
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
 * @version    SVN: $Id: Group.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for all HTML_QuickForm2 containers
 */
// require_once 'HTML/QuickForm2/Container.php';

/**
 * Base class for QuickForm2 groups of elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Container_Group extends HTML_QuickForm2_Container
{
   /**
    * Group name
    * If set, group name will be used as prefix for contained
    * element names, like groupname[elementname].
    * @var string
    */
    protected $name;

   /**
    * Previous group name
    * Stores the previous group name when the group name is changed.
    * Used to restore children names if necessary.
    * @var string
    */
    protected $previousName;

    public function getType()
    {
        return 'group';
    }

    protected function prependsName()
    {
        return strlen($this->name) > 0;
    }

    public function getValue()
    {
        $value = parent::getValue();
        if (!$this->prependsName()) {
            return $value;

        } elseif (!strpos($this->getName(), '[')) {
            return isset($value[$this->getName()])? $value[$this->getName()]: null;

        } else {
            $tokens   =  explode('[', str_replace(']', '', $this->getName()));
            $valueAry =& $value;
            do {
                $token = array_shift($tokens);
                if (!isset($valueAry[$token])) {
                    return null;
                }
                $valueAry =& $valueAry[$token];
            } while ($tokens);
            return $valueAry;
        }
    }

    public function setValue($value)
    {
        // Prepare a mapper for element names as array

        if ($this->prependsName()) {
            $prefix = explode('[', str_replace(']', '', $this->getName()));
        }

        $elements = array();
        foreach ($this as $child) {
            $tokens = explode('[', str_replace(']', '', $child->getName()));
            if (!empty($prefix)) {
                $tokens = array_slice($tokens, count($prefix));
            }
            $elements[] = $tokens;
        }

        // Iterate over values to find corresponding element

        $index = 0;

        foreach ($value as $k => $v) {
            $val = array($k => $v);
            $found = null;
            foreach ($elements as $i => $tokens) {
                do {
                    $token = array_shift($tokens);
                    $numeric = false;
                    if ($token == "") {
                        // Deal with numeric indexes in values
                        $token = $index;
                        $numeric = true;
                    }
                    if (isset($val[$token])) {
                        // Found a value
                        $val = $val[$token];
                        $found = $val;
                        if ($numeric) {
                            $index += 1;
                        }
                    } else {
                        // Not found, skip next iterations
                        $found = null;
                        break;
                    }

                } while (!empty($tokens));

                if (!is_null($found)) {
                    // Found a value corresponding to element name
                    $child = $this->elements[$i];
                    $child->setValue($val);
                    unset($val);
                    if (!($child instanceof HTML_QuickForm2_Container_Group)) {
                        // Speed up next iterations
                        unset($elements[$i]);
                    }
                    break;
                }
            }
        }
    }


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->previousName = $this->name;
        $this->name = $name;
        foreach ($this as $child) {
            $this->renameChild($child);
        }
        return $this;
    }

    protected function renameChild(HTML_QuickForm2_Node $element)
    {
        $tokens = explode('[', str_replace(']', '', $element->getName()));
        if ($this === $element->getContainer()) {
            // Child has already been renamed by its group before
            if (!is_null($this->previousName) &&
                $this->previousName !== '') {
                $gtokens = explode('[', str_replace(']', '', $this->previousName));
                $pos = array_search(end($gtokens), $tokens);
                if (!is_null($pos)) {
                    $tokens = array_slice($tokens, $pos+1);
                }
            }
        }
        if (is_null($this->name) || $this->name === '') {
            if (is_null($this->previousName) || $this->previousName === '') {
                return $element;
            } else {
                $elname = $tokens[0];
                unset($tokens[0]);
                foreach ($tokens as $v) {
                    $elname .= '['.$v.']';
                }
            }
        } else {
            $elname = $this->getName().'['.implode('][', $tokens).']';
        }
        $element->setName($elname);
        return $element;
    }

   /**
    * Appends an element to the container
    *
    * If the element was previously added to the container or to another
    * container, it is first removed there.
    *
    * @param    HTML_QuickForm2_Node     Element to add
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    */
    public function appendChild(HTML_QuickForm2_Node $element)
    {
        if (null !== ($container = $element->getContainer())) {
            $container->removeChild($element);
        }
        // Element can be renamed only after being removed from container
        $this->renameChild($element);

        $element->setContainer($this);
        $this->elements[] = $element;
        return $element;
    }

   /**
    * Removes the element from this container
    *
    * If the reference object is not given, the element will be appended.
    *
    * @param    HTML_QuickForm2_Node     Element to remove
    * @return   HTML_QuickForm2_Node     Removed object
    */
    public function removeChild(HTML_QuickForm2_Node $element)
    {
        $element = parent::removeChild($element);
        if ($this->prependsName()) {
            $name = preg_replace('/^' . $this->getName() . '\[([^\]]*)\]/', '\1', $element->getName());
            $element->setName($name);
        }
        return $element;
    }

   /**
    * Inserts an element in the container
    *
    * If the reference object is not given, the element will be appended.
    *
    * @param    HTML_QuickForm2_Node     Element to insert
    * @param    HTML_QuickForm2_Node     Reference to insert before
    * @return   HTML_QuickForm2_Node     Inserted element
    */
    public function insertBefore(HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $reference = null)
    {
        if (null === $reference) {
            return $this->appendChild($element);
        }
        return parent::insertBefore($this->renameChild($element), $reference);
    }

   /**
    * Sets string(s) to separate grouped elements
    *
    * @param    string|array    Use a string for one separator, array for
    *                           alternating separators
    * @return   HTML_QuickForm2_Container_Group
    */
    public function setSeparator($separator)
    {
        $this->data['separator'] = $separator;
        return $this;
    }

   /**
    * Returns string(s) to separate grouped elements
    *
    * @return   string|array    Separator, null if not set
    */
    public function getSeparator()
    {
        return isset($this->data['separator'])? $this->data['separator']: null;
    }

   /**
    * Renders the group using the given renderer
    *
    * @param    HTML_QuickForm2_Renderer    Renderer instance
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->startGroup($this);
        foreach ($this as $element) {
            $element->render($renderer);
        }
        $renderer->finishGroup($this);
        return $renderer;
    }

    public function __toString()
    {
        // require_once 'HTML/QuickForm2/Renderer.php';

        return $this->render(
                   HTML_QuickForm2_Renderer::factory('default')
                       ->setTemplateForId($this->getId(), '{content}')
               )->__toString();
    }
}
?>
