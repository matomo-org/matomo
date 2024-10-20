<?php
/**
 * Proxy class for HTML_QuickForm2 renderers and their plugins
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
 * @version    SVN: $Id: Proxy.php 299706 2010-05-24 18:32:37Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
// require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Proxy class for HTML_QuickForm2 renderers and their plugins
 *
 * This class serves two purposes:
 * <ol>
 *   <li>Aggregates renderer and its plugins. From user's point of view
 *       renderer plugins simply add new methods to renderer instances.</li>
 *   <li>Restricts access to renderer properties and methods. Those are defined
 *       as 'public' to allow easy access from plugins, but only methods
 *       with names explicitly returned by Renderer::exportMethods() are
 *       available to the outside world.</li>
 * </ol>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Renderer_Proxy extends HTML_QuickForm2_Renderer
{
   /**
    * Renderer instance
    * @var HTML_QuickForm2_Renderer
    */
    private $_renderer;

   /**
    * Additional renderer methods to proxy via __call(), as returned by exportMethods()
    * @var array
    */
    private $_rendererMethods = array();

   /**
    * Reference to a list of registered renderer plugins for that renderer type
    * @var array
    */
    private $_pluginClasses;

   /**
    * Plugins for this renderer
    * @var array
    */
    private $_plugins = array();

   /**
    * Plugin methods to call via __call() magic method
    *
    * Array has the form ('lowercase method name' => 'index in _plugins array')
    *
    * @var array
    */
    private $_pluginMethods = array();

   /**
    * Constructor, sets proxied renderer and its plugins
    *
    * @param    HTML_QuickForm2_Renderer    Renderer instance to proxy
    * @param    array                       Plugins registered for that renderer type
    */
    protected function __construct(HTML_QuickForm2_Renderer $renderer, array &$pluginClasses)
    {
        foreach ($renderer->exportMethods() as $method) {
            $this->_rendererMethods[strtolower($method)] = true;
        }
        $this->_renderer      = $renderer;
        $this->_pluginClasses = &$pluginClasses;
    }

   /**
    * Magic function; call an imported method of a renderer or its plugin
    *
    * @param    string  method name
    * @param    array   method arguments
    * @return   mixed
    */
    public function __call($name, $arguments)
    {
        $lower = strtolower($name);
        if (isset($this->_rendererMethods[$lower])) {
            // support fluent interfaces
            $ret = call_user_func_array(array($this->_renderer, $name), $arguments);
            return $ret === $this->_renderer? $this: $ret;
        }
        // any additional plugins since last __call()?
        for ($i = count($this->_plugins); $i < count($this->_pluginClasses); $i++) {
            list($className, $includeFile) = $this->_pluginClasses[$i];
            if (!class_exists($className)) {
                HTML_QuickForm2_Loader::loadClass($className, $includeFile);
            }
            $this->addPlugin($i, new $className);
        }
        if (isset($this->_pluginMethods[$lower])) {
            return call_user_func_array(
                array($this->_plugins[$this->_pluginMethods[$lower]], $name),
                $arguments
            );
        }
        trigger_error("Fatal error: Call to undefined method " .
                      get_class($this->_renderer) . "::" . $name . "()", E_USER_ERROR);
    }

   /**
    * Adds a plugin for the current renderer instance
    *
    * Plugin's methods are imported and can be later called as this object's own
    *
    * @param    HTML_QuickForm2_Renderer_Plugin     a plugin instance
    * @throws   HTML_QuickForm2_InvalidArgumentException if a plugin has already
    *                   imported name
    */
    protected function addPlugin($index, HTML_QuickForm2_Renderer_Plugin $plugin)
    {
        $methods    = array();
        $reflection = new ReflectionObject($plugin);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $lower = strtolower($method->getName());
            if ('HTML_QuickForm2_Renderer_Plugin' == $method->getDeclaringClass()->getName()) {
                continue;
            } elseif (isset($this->_rendererMethods[$lower])
                      || isset($this->_pluginMethods[$lower])
            ) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    'Duplicate method name: name ' . $method->getName() . ' in plugin ' .
                    get_class($plugin) . ' already taken by ' .
                    (isset($this->_rendererMethods[$lower])?
                     get_class($this->_renderer):
                     get_class($this->_plugins[$this->_pluginMethods[$lower]])
                    )
                );
            }
            $methods[$lower] = $index;
        }
        $plugin->setRenderer($this->_renderer);
        $this->_plugins[$index]  = $plugin;
        $this->_pluginMethods   += $methods;
    }

   /**#@+
    * Proxies for methods defined in {@link HTML_QuickForm2_Renderer}
    */
    public function setOption($nameOrOptions, $value = null)
    {
        $this->_renderer->setOption($nameOrOptions, $value);
        return $this;
    }

    public function getOption($name = null)
    {
        return $this->_renderer->getOption($name);
    }

    public function getJavascriptBuilder()
    {
        return $this->_renderer->getJavascriptBuilder();
    }

    public function setJavascriptBuilder(?HTML_QuickForm2_JavascriptBuilder $builder = null)
    {
        $this->_renderer->setJavascriptBuilder($builder);
        return $this;
    }

    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $this->_renderer->renderElement($element);
    }

    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        $this->_renderer->renderHidden($element);
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->_renderer->startForm($form);
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->_renderer->finishForm($form);
    }

    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $this->_renderer->startContainer($container);
    }

    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        $this->_renderer->finishContainer($container);
    }

    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $this->_renderer->startGroup($group);
    }

    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $this->_renderer->finishGroup($group);
    }
   /**#@-*/

    public function __toString()
    {
        if (method_exists($this->_renderer, '__toString')) {
            return $this->_renderer->__toString();
        }
        trigger_error("Fatal error: Object of class " . get_class($this->_renderer) .
                      " could not be converted to string", E_USER_ERROR);
    }
}
?>
