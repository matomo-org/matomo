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
 * @package    Zend_Loader
 * @subpackage Autoloader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Resource.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Loader_Autoloader_Interface */
require_once 'Zend/Loader/Autoloader/Interface.php';

/**
 * Resource loader
 *
 * @uses       Zend_Loader_Autoloader_Interface
 * @package    Zend_Loader
 * @subpackage Autoloader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Loader_Autoloader_Resource implements Zend_Loader_Autoloader_Interface
{
    /**
     * @var string Base path to resource classes
     */
    protected $_basePath;

    /**
     * @var array Components handled within this resource
     */
    protected $_components = array();

    /**
     * @var string Default resource/component to use when using object registry
     */
    protected $_defaultResourceType;

    /**
     * @var string Namespace of classes within this resource
     */
    protected $_namespace;

    /**
     * @var array Available resource types handled by this resource autoloader
     */
    protected $_resourceTypes = array();

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options Configuration options for resource autoloader
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            require_once 'Zend/Loader/Exception.php';
            throw new Zend_Loader_Exception('Options must be passed to resource loader constructor');
        }

        $this->setOptions($options);

        $namespace = $this->getNamespace();
        if ((null === $namespace)
            || (null === $this->getBasePath())
        ) {
            require_once 'Zend/Loader/Exception.php';
            throw new Zend_Loader_Exception('Resource loader requires both a namespace and a base path for initialization');
        }

        if (!empty($namespace)) {
            $namespace .= '_';
        }
        Zend_Loader_Autoloader::getInstance()->unshiftAutoloader($this, $namespace);
    }

    /**
     * Overloading: methods
     *
     * Allow retrieving concrete resource object instances using 'get<Resourcename>()'
     * syntax. Example:
     * <code>
     * $loader = new Zend_Loader_Autoloader_Resource(array(
     *     'namespace' => 'Stuff_',
     *     'basePath'  => '/path/to/some/stuff',
     * ))
     * $loader->addResourceType('Model', 'models', 'Model');
     *
     * $foo = $loader->getModel('Foo'); // get instance of Stuff_Model_Foo class
     * </code>
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Loader_Exception if method not beginning with 'get' or not matching a valid resource type is called
     */
    public function __call($method, $args)
    {
        if ('get' == substr($method, 0, 3)) {
            $type  = strtolower(substr($method, 3));
            if (!$this->hasResourceType($type)) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception("Invalid resource type $type; cannot load resource");
            }
            if (empty($args)) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception("Cannot load resources; no resource specified");
            }
            $resource = array_shift($args);
            return $this->load($resource, $type);
        }

        require_once 'Zend/Loader/Exception.php';
        throw new Zend_Loader_Exception("Method '$method' is not supported");
    }

    /**
     * Helper method to calculate the correct class path
     *
     * @param string $class
     * @return False if not matched other wise the correct path
     */
    public function getClassPath($class)
    {
        $segments          = explode('_', $class);
        $namespaceTopLevel = $this->getNamespace();
        $namespace         = '';

        if (!empty($namespaceTopLevel)) {
            $namespace = array_shift($segments);
            if ($namespace != $namespaceTopLevel) {
                // wrong prefix? we're done
                return false;
            }
        }

        if (count($segments) < 2) {
            // assumes all resources have a component and class name, minimum
            return false;
        }

        $final     = array_pop($segments);
        $component = $namespace;
        $lastMatch = false;
        do {
            $segment    = array_shift($segments);
            $component .= empty($component) ? $segment : '_' . $segment;
            if (isset($this->_components[$component])) {
                $lastMatch = $component;
            }
        } while (count($segments));

        if (!$lastMatch) {
            return false;
        }

        $final = substr($class, strlen($lastMatch) + 1);
        $path = $this->_components[$lastMatch];
        $classPath = $path . '/' . str_replace('_', '/', $final) . '.php';

        if (Zend_Loader::isReadable($classPath)) {
            return $classPath;
        }

        return false;
    }

    /**
     * Attempt to autoload a class
     *
     * @param  string $class
     * @return mixed False if not matched, otherwise result if include operation
     */
    public function autoload($class)
    {
        $classPath = $this->getClassPath($class);
        if (false !== $classPath) {
            return include $classPath;
        }
        return false;
    }

    /**
     * Set class state from options
     *
     * @param  array $options
     * @return Zend_Loader_Autoloader_Resource
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set namespace that this autoloader handles
     *
     * @param  string $namespace
     * @return Zend_Loader_Autoloader_Resource
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = rtrim((string) $namespace, '_');
        return $this;
    }

    /**
     * Get namespace this autoloader handles
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set base path for this set of resources
     *
     * @param  string $path
     * @return Zend_Loader_Autoloader_Resource
     */
    public function setBasePath($path)
    {
        $this->_basePath = (string) $path;
        return $this;
    }

    /**
     * Get base path to this set of resources
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Add resource type
     *
     * @param  string $type identifier for the resource type being loaded
     * @param  string $path path relative to resource base path containing the resource types
     * @param  null|string $namespace sub-component namespace to append to base namespace that qualifies this resource type
     * @return Zend_Loader_Autoloader_Resource
     */
    public function addResourceType($type, $path, $namespace = null)
    {
        $type = strtolower($type);
        if (!isset($this->_resourceTypes[$type])) {
            if (null === $namespace) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception('Initial definition of a resource type must include a namespace');
            }
            $namespaceTopLevel = $this->getNamespace();
            $namespace = ucfirst(trim($namespace, '_'));
            $this->_resourceTypes[$type] = array(
                'namespace' => empty($namespaceTopLevel) ? $namespace : $namespaceTopLevel . '_' . $namespace,
            );
        }
        if (!is_string($path)) {
            require_once 'Zend/Loader/Exception.php';
            throw new Zend_Loader_Exception('Invalid path specification provided; must be string');
        }
        $this->_resourceTypes[$type]['path'] = $this->getBasePath() . '/' . rtrim($path, '\/');

        $component = $this->_resourceTypes[$type]['namespace'];
        $this->_components[$component] = $this->_resourceTypes[$type]['path'];
        return $this;
    }

    /**
     * Add multiple resources at once
     *
     * $types should be an associative array of resource type => specification
     * pairs. Each specification should be an associative array containing
     * minimally the 'path' key (specifying the path relative to the resource
     * base path) and optionally the 'namespace' key (indicating the subcomponent
     * namespace to append to the resource namespace).
     *
     * As an example:
     * <code>
     * $loader->addResourceTypes(array(
     *     'model' => array(
     *         'path'      => 'models',
     *         'namespace' => 'Model',
     *     ),
     *     'form' => array(
     *         'path'      => 'forms',
     *         'namespace' => 'Form',
     *     ),
     * ));
     * </code>
     *
     * @param  array $types
     * @return Zend_Loader_Autoloader_Resource
     */
    public function addResourceTypes(array $types)
    {
        foreach ($types as $type => $spec) {
            if (!is_array($spec)) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception('addResourceTypes() expects an array of arrays');
            }
            if (!isset($spec['path'])) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception('addResourceTypes() expects each array to include a paths element');
            }
            $paths  = $spec['path'];
            $namespace = null;
            if (isset($spec['namespace'])) {
                $namespace = $spec['namespace'];
            }
            $this->addResourceType($type, $paths, $namespace);
        }
        return $this;
    }

    /**
     * Overwrite existing and set multiple resource types at once
     *
     * @see    Zend_Loader_Autoloader_Resource::addResourceTypes()
     * @param  array $types
     * @return Zend_Loader_Autoloader_Resource
     */
    public function setResourceTypes(array $types)
    {
        $this->clearResourceTypes();
        return $this->addResourceTypes($types);
    }

    /**
     * Retrieve resource type mappings
     *
     * @return array
     */
    public function getResourceTypes()
    {
        return $this->_resourceTypes;
    }

    /**
     * Is the requested resource type defined?
     *
     * @param  string $type
     * @return bool
     */
    public function hasResourceType($type)
    {
        return isset($this->_resourceTypes[$type]);
    }

    /**
     * Remove the requested resource type
     *
     * @param  string $type
     * @return Zend_Loader_Autoloader_Resource
     */
    public function removeResourceType($type)
    {
        if ($this->hasResourceType($type)) {
            $namespace = $this->_resourceTypes[$type]['namespace'];
            unset($this->_components[$namespace]);
            unset($this->_resourceTypes[$type]);
        }
        return $this;
    }

    /**
     * Clear all resource types
     *
     * @return Zend_Loader_Autoloader_Resource
     */
    public function clearResourceTypes()
    {
        $this->_resourceTypes = array();
        $this->_components    = array();
        return $this;
    }

    /**
     * Set default resource type to use when calling load()
     *
     * @param  string $type
     * @return Zend_Loader_Autoloader_Resource
     */
    public function setDefaultResourceType($type)
    {
        if ($this->hasResourceType($type)) {
            $this->_defaultResourceType = $type;
        }
        return $this;
    }

    /**
     * Get default resource type to use when calling load()
     *
     * @return string|null
     */
    public function getDefaultResourceType()
    {
        return $this->_defaultResourceType;
    }

    /**
     * Object registry and factory
     *
     * Loads the requested resource of type $type (or uses the default resource
     * type if none provided). If the resource has been loaded previously,
     * returns the previous instance; otherwise, instantiates it.
     *
     * @param  string $resource
     * @param  string $type
     * @return object
     * @throws Zend_Loader_Exception if resource type not specified or invalid
     */
    public function load($resource, $type = null)
    {
        if (null === $type) {
            $type = $this->getDefaultResourceType();
            if (empty($type)) {
                require_once 'Zend/Loader/Exception.php';
                throw new Zend_Loader_Exception('No resource type specified');
            }
        }
        if (!$this->hasResourceType($type)) {
            require_once 'Zend/Loader/Exception.php';
            throw new Zend_Loader_Exception('Invalid resource type specified');
        }
        $namespace = $this->_resourceTypes[$type]['namespace'];
        $class     = $namespace . '_' . ucfirst($resource);
        if (!isset($this->_resources[$class])) {
            $this->_resources[$class] = new $class;
        }
        return $this->_resources[$class];
    }
}
