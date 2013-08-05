<?php
/**
 * Base class for HTML_QuickForm2 renderers
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
 * @version    SVN: $Id: Renderer.php 299706 2010-05-24 18:32:37Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */
use Piwik\Plugin;

/**
 * Class with static methods for loading classes and files
 */
// require_once 'HTML/QuickForm2/Loader.php';

/**
 * Abstract base class for QuickForm2 renderers
 *
 * This class serves two main purposes:
 * <ul>
 *   <li>Defines the API all renderers should implement (render*() methods);</li>
 *   <li>Provides static methods for registering renderers and their plugins
 *       and {@link factory()} method for creating renderer instances.</li>
 * </ul>
 *
 * Note that renderers should always be instantiated through factory(), in the
 * other case it will not be possible to add plugins.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Renderer
{
   /**
    * List of registered renderer types
    * @var array
    */
    private static $_types = array(
        'default' => array('HTML_QuickForm2_Renderer_Default', null),
        'array'   => array('HTML_QuickForm2_Renderer_Array', null)
    );

   /**
    * List of registered renderer plugins
    * @var array
    */
    private static $_pluginClasses = array(
        'default' => array(),
        'array'   => array()
    );

   /**
    * Renderer options
    * @var  array
    * @see  setOption()
    */
    protected $options = array(
        'group_hiddens' => true,
        'required_note' => '<em>*</em> denotes required fields.',
        'errors_prefix' => 'Invalid information entered:',
        'errors_suffix' => 'Please correct these fields.',
        'group_errors'  => false
    );

   /**
    * Javascript builder object
    * @var  HTML_QuickForm2_JavascriptBuilder
    */
    protected $jsBuilder;

   /**
    * Creates a new renderer instance of the given type
    *
    * A renderer is always wrapped by a Proxy, which handles calling its
    * "published" methods and methods of its plugins. Registered plugins are
    * added automagically to the existing renderer instances so that
    * <code>
    * $foo = HTML_QuickForm2_Renderer::factory('foo');
    * // Plugin implementing bar() method
    * HTML_QuickForm2_Renderer::registerPlugin('foo', 'Plugin_Foo_Bar');
    * $foo->bar();
    * </code>
    * will work.
    *
    * @param    string  Type name (treated case-insensitively)
    * @return   HTML_QuickForm2_Renderer_Proxy  A renderer instance of the given
    *                   type wrapped by a Proxy
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the renderer can
    *           not be found and/or loaded from file
    */
    final public static function factory($type)
    {
        $type = strtolower($type);
        if (!isset(self::$_types[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer type '$type' is not known"
            );
        }

        list ($className, $includeFile) = self::$_types[$type];
        if (!class_exists($className)) {
            HTML_QuickForm2_Loader::loadClass($className, $includeFile);
        }
        if (!class_exists('HTML_QuickForm2_Renderer_Proxy')) {
            HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_Renderer_Proxy');
        }
        return new HTML_QuickForm2_Renderer_Proxy(new $className, self::$_pluginClasses[$type]);
    }

   /**
    * Registers a new renderer type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    * @throws   HTML_QuickForm2_InvalidArgumentException if type already registered
    */
    final public static function register($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        if (!empty(self::$_types[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer type '$type' is already registered"
            );
        }
        self::$_types[$type] = array($className, $includeFile);
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = array();
        }
    }

   /**
    * Registers a plugin for a renderer type
    *
    * @param    string  Renderer type name (treated case-insensitively)
    * @param    string  Plugin class name
    * @param    string  File containing the plugin class, leave empty if class already loaded
    * @throws   HTML_QuickForm2_InvalidArgumentException if plugin is already registered
    */
    final public static function registerPlugin($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        // We don't check self::$_types, since a plugin may be registered
        // before renderer itself if it goes with some custom element
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = array(array($className, $includeFile));
        } else {
            foreach (self::$_pluginClasses[$type] as $plugin) {
                if (0 == strcasecmp($plugin[0], $className)) {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        "Plugin '$className' for renderer type '$type' is already registered"
                    );
                }
            }
            self::$_pluginClasses[$type][] = array($className, $includeFile);
        }
    }

   /**
    * Constructor
    *
    * Renderer instances should not be created directly, use {@link factory()}
    */
    protected function __construct()
    {
    }

   /**
    * Returns an array of "published" method names that should be callable through proxy
    *
    * Methods defined in HTML_QuickForm2_Renderer are proxied automatically,
    * only additional methods should be returned.
    *
    * @return   array
    */
    public function exportMethods()
    {
        return array();
    }

   /**
    * Sets the option(s) affecting renderer behaviour
    *
    * The following options are available:
    * <ul>
    *   <li>'group_hiddens' - whether to group hidden elements together or
    *                         render them where they were added (boolean)</li>
    *   <li>'group_errors'  - whether to group error messages or render them
    *                         alongside elements they apply to (boolean)</li>
    *   <li>'errors_prefix' - leading message for grouped errors (string)</li>
    *   <li>'errors_suffix' - trailing message for grouped errors (string)</li>
    *   <li>'required_note' - note displayed if the form contains required
    *                         elements (string)</li>
    * </ul>
    *
    * @param    string|array    option name or array ('option name' => 'option value')
    * @param    mixed           parameter value if $nameOrConfig is not an array
    * @return   HTML_QuickForm2_Renderer
    * @throws   HTML_QuickForm2_NotFoundException in case of unknown option
    */
    public function setOption($nameOrOptions, $value = null)
    {
        if (is_array($nameOrOptions)) {
            foreach ($nameOrOptions as $name => $value) {
                $this->setOption($name, $value);
            }

        } else {
            if (!array_key_exists($nameOrOptions, $this->options)) {
                throw new HTML_QuickForm2_NotFoundException(
                    "Unknown option '{$nameOrOptions}'"
                );
            }
            $this->options[$nameOrOptions] = $value;
        }

        return $this;
    }

   /**
    * Returns the value(s) of the renderer option(s)
    *
    * @param    string  parameter name
    * @return   mixed   value of $name parameter, array of all configuration
    *                   parameters if $name is not given
    * @throws   HTML_QuickForm2_NotFoundException in case of unknown option
    */
    public function getOption($name = null)
    {
        if (null === $name) {
            return $this->options;
        } elseif (!array_key_exists($name, $this->options)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Unknown option '{$name}'"
            );
        }
        return $this->options[$name];
    }

   /**
    * Returns the javascript builder object
    *
    * @return   HTML_QuickForm2_JavascriptBuilder
    */
    public function getJavascriptBuilder()
    {
        if (empty($this->jsBuilder)) {
            if (!class_exists('HTML_QuickForm2_JavascriptBuilder')) {
                HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');
            }
            $this->jsBuilder = new HTML_QuickForm2_JavascriptBuilder();
        }
        return $this->jsBuilder;
    }

   /**
    * Sets the javascript builder object
    *
    * You may want to reuse the same builder object if outputting several
    * forms on one page.
    *
    * @param    HTML_QuickForm2_JavascriptBuilder
    * @return   HTML_QuickForm2_Renderer
    */
    public function setJavascriptBuilder(HTML_QuickForm2_JavascriptBuilder $builder = null)
    {
        $this->jsBuilder = $builder;
        return $this;
    }

   /**
    * Renders a generic element
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    */
    abstract public function renderElement(HTML_QuickForm2_Node $element);

   /**
    * Renders a hidden element
    *
    * @param    HTML_QuickForm2_Node    Hidden element being rendered
    */
    abstract public function renderHidden(HTML_QuickForm2_Node $element);

   /**
    * Starts rendering a form, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    abstract public function startForm(HTML_QuickForm2_Node $form);

   /**
    * Finishes rendering a form, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    abstract public function finishForm(HTML_QuickForm2_Node $form);

   /**
    * Starts rendering a generic container, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    abstract public function startContainer(HTML_QuickForm2_Node $container);

   /**
    * Finishes rendering a generic container, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    abstract public function finishContainer(HTML_QuickForm2_Node $container);

   /**
    * Starts rendering a group, called before processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    abstract public function startGroup(HTML_QuickForm2_Node $group);

   /**
    * Finishes rendering a group, called after processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    abstract public function finishGroup(HTML_QuickForm2_Node $group);
}
?>
