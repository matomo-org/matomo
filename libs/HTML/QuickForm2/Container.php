<?php
/**
 * Base class for simple HTML_QuickForm2 containers
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
 * @version    SVN: $Id: Container.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for all HTML_QuickForm2 elements
 */
// require_once 'HTML/QuickForm2/Node.php';


/**
 * Implements a recursive iterator for the container elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_ContainerIterator extends RecursiveArrayIterator implements RecursiveIterator
{
    public function __construct(HTML_QuickForm2_Container $container)
    {
        parent::__construct($container->getElements());
    }

    public function hasChildren(): bool
    {
        return $this->current() instanceof HTML_QuickForm2_Container;
    }

    public function getChildren(): HTML_QuickForm2_ContainerIterator
    {
        return new HTML_QuickForm2_ContainerIterator($this->current());
    }
}

/**
 * Abstract base class for simple QuickForm2 containers
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Container extends HTML_QuickForm2_Node
    implements IteratorAggregate, Countable
{
   /**
    * Array of elements contained in this container
    * @var array
    */
    protected $elements = array();


    public function setName($name)
    {
        $this->attributes['name'] = (string)$name;
        return $this;
    }

    public function toggleFrozen($freeze = null)
    {
        if (null !== $freeze) {
            foreach ($this as $child) {
                $child->toggleFrozen($freeze);
            }
        }
        return parent::toggleFrozen($freeze);
    }

    public function persistentFreeze($persistent = null)
    {
        if (null !== $persistent) {
            foreach ($this as $child) {
                $child->persistentFreeze($persistent);
            }
        }
        return parent::persistentFreeze($persistent);
    }

   /**
    * Whether container prepends its name to names of contained elements
    *
    * @return   bool
    */
    protected function prependsName()
    {
        return false;
    }

   /**
    * Returns the element's value
    *
    * The default implementation for Containers is to return an array with
    * contained elements' values. The array is indexed the same way $_GET and
    * $_POST arrays would be for these elements.
    *
    * @return   array|null
    */
    public function getValue()
    {
        $values = array();
        foreach ($this as $child) {
            $value = $child->getValue();
            if (null !== $value) {
                if ($child instanceof HTML_QuickForm2_Container
                    && !$child->prependsName()
                ) {
                    $values = self::arrayMerge($values, $value);
                } else {
                    $name = $child->getName();
                    if (!strpos($name, '[')) {
                        $values[$name] = $value;
                    } else {
                        $tokens   =  explode('[', str_replace(']', '', $name));
                        $valueAry =& $values;
                        do {
                            $token = array_shift($tokens);
                            if (!isset($valueAry[$token])) {
                                $valueAry[$token] = array();
                            }
                            $valueAry =& $valueAry[$token];
                        } while (count($tokens) > 1);
                        $valueAry[$tokens[0]] = $value;
                    }
                }
            }
        }
        return empty($values)? null: $this->applyFilters($values);
    }

   /**
    * Merges two arrays
    *
    * Merges two arrays like the PHP function array_merge_recursive does,
    * the difference being that existing integer keys will not be renumbered.
    *
    * @param    array
    * @param    array
    * @return   array   resulting array
    */
    protected static function arrayMerge($a, $b)
    {
        foreach ($b as $k => $v) {
            if (!is_array($v) || isset($a[$k]) && !is_array($a[$k])) {
                $a[$k] = $v;
            } else {
                $a[$k] = self::arrayMerge(isset($a[$k])? $a[$k]: array(), $v);
            }
        }
        return $a;
    }

   /**
    * Returns an array of this container's elements
    *
    * @return   array   Container elements
    */
    public function getElements()
    {
        return $this->elements;
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
        if ($this === $element->getContainer()) {
            $this->removeChild($element);
        }
        $element->setContainer($this);
        $this->elements[] = $element;
        return $element;
    }

   /**
    * Appends an element to the container (possibly creating it first)
    *
    * If the first parameter is an instance of HTML_QuickForm2_Node then all
    * other parameters are ignored and the method just calls {@link appendChild()}.
    * In the other case the element is first created via
    * {@link HTML_QuickForm2_Factory::createElement()} and then added via the
    * same method. This is a convenience method to reduce typing and ease
    * porting from HTML_QuickForm.
    *
    * @param    string|HTML_QuickForm2_Node  Either type name (treated
    *               case-insensitively) or an element instance
    * @param    mixed   Element name
    * @param    mixed   Element attributes
    * @param    array   Element-specific data
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    * @throws   HTML_QuickForm2_NotFoundException
    */
    public function addElement($elementOrType, $name = null, $attributes = null,
                               array $data = array())
    {
        if ($elementOrType instanceof HTML_QuickForm2_Node) {
            return $this->appendChild($elementOrType);
        } else {
            return $this->appendChild(HTML_QuickForm2_Factory::createElement(
                $elementOrType, $name, $attributes, $data
            ));
        }
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

        if ($element->getContainer() !== $this) {
            throw new HTML_QuickForm2_NotFoundException(
                "Element with name '".$element->getName()."' was not found"
            );
        }
        foreach ($this as $key => $child){
            if ($child === $element) {
                unset($this->elements[$key]);
                $element->setContainer(null);
                break;
            }
        }
        return $element;
    }


   /**
    * Returns an element if its id is found
    *
    * @param    string  Element id to find
    * @return   HTML_QuickForm2_Node|null
    */
    public function getElementById($id)
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ($id == $element->getId()) {
                return $element;
            }
        }
        return null;
    }

   /**
    * Returns an array of elements which name corresponds to element
    *
    * @param    string  Elements name to find
    * @return   array
    */
    public function getElementsByName($name)
    {
        $found = array();
        foreach ($this->getRecursiveIterator() as $element) {
            if ($element->getName() == $name) {
                $found[] = $element;
            }
        }
        return $found;
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
    public function insertBefore(HTML_QuickForm2_Node $element, ?HTML_QuickForm2_Node $reference = null)
    {
        if (null === $reference) {
            return $this->appendChild($element);
        }
        $offset = 0;
        foreach ($this as $child) {
            if ($child === $reference) {
                if ($this === $element->getContainer()) {
                    $this->removeChild($element);
                }
                $element->setContainer($this);
                array_splice($this->elements, $offset, 0, array($element));
                return $element;
            }
            $offset++;
        }
        throw new HTML_QuickForm2_NotFoundException(
            "Reference element with name '".$reference->getName()."' was not found"
        );
    }

   /**
    * Returns a recursive iterator for the container elements
    *
    * @return    HTML_QuickForm2_ContainerIterator
    */
    public function getIterator(): HTML_QuickForm2_ContainerIterator
    {
        return new HTML_QuickForm2_ContainerIterator($this);
    }

   /**
    * Returns a recursive iterator iterator for the container elements
    *
    * @param    int     mode passed to RecursiveIteratorIterator
    * @return   RecursiveIteratorIterator
    */
    public function getRecursiveIterator($mode = RecursiveIteratorIterator::SELF_FIRST): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
                        new HTML_QuickForm2_ContainerIterator($this), $mode
                   );
    }

   /**
    * Returns the number of elements in the container
    *
    * @return    int
    */
    public function count(): int
    {
        return count($this->elements);
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * The default behaviour is just to call the updateValue() methods of
    * contained elements, since default Container doesn't have any value itself
    */
    public function updateValue()
    {
        foreach ($this as $child) {
            $child->updateValue();
        }
    }


   /**
    * Performs the server-side validation
    *
    * This method also calls validate() on all contained elements.
    *
    * @return   boolean Whether the container and all contained elements are valid
    */
    protected function validate()
    {
        $valid = parent::validate();
        foreach ($this as $child) {
            $valid = $child->validate() && $valid;
        }
        return $valid;
    }

   /**
    * Appends an element to the container, creating it first
    *
    * The element will be created via {@link HTML_QuickForm2_Factory::createElement()}
    * and then added via the {@link appendChild()} method.
    * The element type is deduced from the method name.
    * This is a convenience method to reduce typing.
    *
    * @param    mixed   Element name
    * @param    mixed   Element attributes
    * @param    array   Element-specific data
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    * @throws   HTML_QuickForm2_NotFoundException
    */
    public function __call($m, $a)
    {
        if (preg_match('/^(add)([a-zA-Z0-9_]+)$/', $m, $match)) {
            if ($match[1] == 'add') {
                $type = strtolower($match[2]);
                $name = isset($a[0]) ? $a[0] : null;
                $attr = isset($a[1]) ? $a[1] : null;
                $data = isset($a[2]) ? $a[2] : array();
                return $this->addElement($type, $name, $attr, $data);
            }
        }
        trigger_error("Fatal error: Call to undefined method ".get_class($this)."::".$m."()", E_USER_ERROR);
    }

   /**
    * Renders the container using the given renderer
    *
    * @param    HTML_QuickForm2_Renderer    Renderer instance
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        foreach ($this->rules as $rule) {
            if ($rule[1] & HTML_QuickForm2_Rule::RUNAT_CLIENT) {
                $renderer->getJavascriptBuilder()->addRule($rule[0]);
            }
        }
        $renderer->startContainer($this);
        foreach ($this as $element) {
            $element->render($renderer);
        }
        $renderer->finishContainer($this);
        return $renderer;
    }

    public function __toString()
    {
        // require_once 'HTML/QuickForm2/Renderer.php';

        return $this->render(HTML_QuickForm2_Renderer::factory('default'))->__toString();
    }

   /**
    * Returns Javascript code for getting the element's value
    *
    * @return   string
    */
    public function getJavascriptValue()
    {
        $args = array();
        foreach ($this as $child) {
            if ($child instanceof HTML_QuickForm2_Container) {
                $args[] = $child->getJavascriptValue();
            } else {
                $args[] = "'" . $child->getId() . "'";
            }
        }
        return 'qf.form.getContainerValue(' . implode(', ', $args) . ')';
    }
}

?>
