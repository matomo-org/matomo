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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Validate_Interface */
// require_once 'Zend/Validate/Interface.php';

/**
 * Zend_Form
 *
 * @category   Zend
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 22465 2010-06-19 17:41:03Z alab $
 */
class Zend_Form implements Iterator, Countable, Zend_Validate_Interface
{
    /**#@+
     * Plugin loader type constants
     */
    const DECORATOR = 'DECORATOR';
    const ELEMENT = 'ELEMENT';
    /**#@-*/

    /**#@+
     * Method type constants
     */
    const METHOD_DELETE = 'delete';
    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PUT    = 'put';
    /**#@-*/

    /**#@+
     * Encoding type constants
     */
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    const ENCTYPE_MULTIPART  = 'multipart/form-data';
    /**#@-*/

    /**
     * Form metadata and attributes
     * @var array
     */
    protected $_attribs = array();

    /**
     * Decorators for rendering
     * @var array
     */
    protected $_decorators = array();

    /**
     * Default display group class
     * @var string
     */
    protected $_defaultDisplayGroupClass = 'Zend_Form_DisplayGroup';

    /**
     * Form description
     * @var string
     */
    protected $_description;

    /**
     * Should we disable loading the default decorators?
     * @var bool
     */
    protected $_disableLoadDefaultDecorators = false;

    /**
     * Display group prefix paths
     * @var array
     */
    protected $_displayGroupPrefixPaths = array();

    /**
     * Groups of elements grouped for display purposes
     * @var array
     */
    protected $_displayGroups = array();

    /**
     * Global decorators to apply to all elements
     * @var null|array
     */
    protected $_elementDecorators;

    /**
     * Prefix paths to use when creating elements
     * @var array
     */
    protected $_elementPrefixPaths = array();

    /**
     * Form elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Array to which elements belong (if any)
     * @var string
     */
    protected $_elementsBelongTo;

    /**
     * Custom form-level error messages
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Are there errors in the form?
     * @var bool
     */
    protected $_errorsExist = false;

    /**
     * Has the form been manually flagged as an error?
     * @var bool
     */
    protected $_errorsForced = false;

    /**
     * Form order
     * @var int|null
     */
    protected $_formOrder;

    /**
     * Whether or not form elements are members of an array
     * @var bool
     */
    protected $_isArray = false;

    /**
     * Form legend
     * @var string
     */
    protected $_legend;

    /**
     * Plugin loaders
     * @var array
     */
    protected $_loaders = array();

    /**
     * Allowed form methods
     * @var array
     */
    protected $_methods = array('delete', 'get', 'post', 'put');

    /**
     * Order in which to display and iterate elements
     * @var array
     */
    protected $_order = array();

    /**
     * Whether internal order has been updated or not
     * @var bool
     */
    protected $_orderUpdated = false;

    /**
     * Sub form prefix paths
     * @var array
     */
    protected $_subFormPrefixPaths = array();

    /**
     * Sub forms
     * @var array
     */
    protected $_subForms = array();

    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Global default translation adapter
     * @var Zend_Translate
     */
    protected static $_translatorDefault;

    /**
     * is the translator disabled?
     * @var bool
     */
    protected $_translatorDisabled = false;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * @var bool
     */
    protected $_isRendered = false;

    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        // Extensions...
        $this->init();

        $this->loadDefaultDecorators();
    }

    /**
     * Clone form object and all children
     *
     * @return void
     */
    public function __clone()
    {
        $elements = array();
        foreach ($this->getElements() as $name => $element) {
            $elements[] = clone $element;
        }
        $this->setElements($elements);

        $subForms = array();
        foreach ($this->getSubForms() as $name => $subForm) {
            $subForms[$name] = clone $subForm;
        }
        $this->setSubForms($subForms);

        $displayGroups = array();
        foreach ($this->_displayGroups as $group)  {
            $clone    = clone $group;
            $elements = array();
            foreach ($clone->getElements() as $name => $e) {
                $elements[] = $this->getElement($name);
            }
            $clone->setElements($elements);
            $displayGroups[] = $clone;
        }
        $this->setDisplayGroups($displayGroups);
    }

    /**
     * Reset values of form
     *
     * @return Zend_Form
     */
    public function reset()
    {
        foreach ($this->getElements() as $element) {
            $element->setValue(null);
        }
        foreach ($this->getSubForms() as $subForm) {
            $subForm->reset();
        }

        return $this;
    }

    /**
     * Initialize form (used by extending classes)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set form state from options array
     *
     * @param  array $options
     * @return Zend_Form
     */
    public function setOptions(array $options)
    {
        if (isset($options['prefixPath'])) {
            $this->addPrefixPaths($options['prefixPath']);
            unset($options['prefixPath']);
        }

        if (isset($options['elementPrefixPath'])) {
            $this->addElementPrefixPaths($options['elementPrefixPath']);
            unset($options['elementPrefixPath']);
        }

        if (isset($options['displayGroupPrefixPath'])) {
            $this->addDisplayGroupPrefixPaths($options['displayGroupPrefixPath']);
            unset($options['displayGroupPrefixPath']);
        }

        if (isset($options['elementDecorators'])) {
            $this->_elementDecorators = $options['elementDecorators'];
            unset($options['elementDecorators']);
        }

        if (isset($options['elements'])) {
            $this->setElements($options['elements']);
            unset($options['elements']);
        }

        if (isset($options['defaultDisplayGroupClass'])) {
            $this->setDefaultDisplayGroupClass($options['defaultDisplayGroupClass']);
            unset($options['defaultDisplayGroupClass']);
        }

        if (isset($options['displayGroupDecorators'])) {
            $displayGroupDecorators = $options['displayGroupDecorators'];
            unset($options['displayGroupDecorators']);
        }

        if (isset($options['elementsBelongTo'])) {
            $elementsBelongTo = $options['elementsBelongTo'];
            unset($options['elementsBelongTo']);
        }

        if (isset($options['attribs'])) {
            $this->addAttribs($options['attribs']);
            unset($options['attribs']);
        }

        $forbidden = array(
            'Options', 'Config', 'PluginLoader', 'SubForms', 'View', 'Translator',
            'Attrib', 'Default',
        );

        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);
            if (in_array($normalized, $forbidden)) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->setAttrib($key, $value);
            }
        }

        if (isset($displayGroupDecorators)) {
            $this->setDisplayGroupDecorators($displayGroupDecorators);
        }

        if (isset($elementsBelongTo)) {
            $this->setElementsBelongTo($elementsBelongTo);
        }

        return $this;
    }

    /**
     * Set form state from config object
     *
     * @param  Zend_Config $config
     * @return Zend_Form
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }


    // Loaders

    /**
     * Set plugin loaders for use with decorators and elements
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @param  string $type 'decorator' or 'element'
     * @return Zend_Form
     * @throws Zend_Form_Exception on invalid type
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type = null)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::ELEMENT:
                $this->_loaders[$type] = $loader;
                return $this;
            default:
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to setPluginLoader()', $type));
        }
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     * - element
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Form_Decorator';
                    $pathSegment   = 'Form/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Form_Element';
                    $pathSegment   = 'Form/Element';
                    break;
                default:
                    // require_once 'Zend/Form/Exception.php';
                    throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }

            // require_once 'Zend/Loader/PluginLoader.php';
            $this->_loaders[$type] = new Zend_Loader_PluginLoader(
                array('Zend_' . $prefixSegment . '_' => 'Zend/' . $pathSegment . '/')
            );
        }

        return $this->_loaders[$type];
    }

    /**
     * Add prefix path for plugin loader
     *
     * If no $type specified, assumes it is a base path for both filters and
     * validators, and sets each according to the following rules:
     * - decorators: $prefix = $prefix . '_Decorator'
     * - elements: $prefix = $prefix . '_Element'
     *
     * Otherwise, the path prefix is set on the appropriate plugin loader.
     *
     * If $type is 'decorator', sets the path in the decorator plugin loader
     * for all elements. Additionally, if no $type is provided,
     * the prefix and path is added to both decorator and element
     * plugin loader with following settings:
     * $prefix . '_Decorator', $path . '/Decorator/'
     * $prefix . '_Element', $path . '/Element/'
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form
     * @throws Zend_Form_Exception for invalid type
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::ELEMENT:
                $loader = $this->getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            case null:
                $prefix = rtrim($prefix, '_');
                $path   = rtrim($path, DIRECTORY_SEPARATOR);
                foreach (array(self::DECORATOR, self::ELEMENT) as $type) {
                    $cType        = ucfirst(strtolower($type));
                    $pluginPath   = $path . DIRECTORY_SEPARATOR . $cType . DIRECTORY_SEPARATOR;
                    $pluginPrefix = $prefix . '_' . $cType;
                    $loader       = $this->getPluginLoader($type);
                    $loader->addPrefixPath($pluginPrefix, $pluginPath);
                }
                return $this;
            default:
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Add many prefix paths at once
     *
     * @param  array $spec
     * @return Zend_Form
     */
    public function addPrefixPaths(array $spec)
    {
        if (isset($spec['prefix']) && isset($spec['path'])) {
            return $this->addPrefixPath($spec['prefix'], $spec['path']);
        }
        foreach ($spec as $type => $paths) {
            if (is_numeric($type) && is_array($paths)) {
                $type = null;
                if (isset($paths['prefix']) && isset($paths['path'])) {
                    if (isset($paths['type'])) {
                        $type = $paths['type'];
                    }
                    $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
                }
            } elseif (!is_numeric($type)) {
                if (!isset($paths['prefix']) || !isset($paths['path'])) {
                    continue;
                }
                $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
            }
        }
        return $this;
    }

    /**
     * Add prefix path for all elements
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form
     */
    public function addElementPrefixPath($prefix, $path, $type = null)
    {
        $this->_elementPrefixPaths[] = array(
            'prefix' => $prefix,
            'path'   => $path,
            'type'   => $type,
        );

        foreach ($this->getElements() as $element) {
            $element->addPrefixPath($prefix, $path, $type);
        }

        foreach ($this->getSubForms() as $subForm) {
            $subForm->addElementPrefixPath($prefix, $path, $type);
        }

        return $this;
    }

    /**
     * Add prefix paths for all elements
     *
     * @param  array $spec
     * @return Zend_Form
     */
    public function addElementPrefixPaths(array $spec)
    {
        $this->_elementPrefixPaths = $this->_elementPrefixPaths + $spec;

        foreach ($this->getElements() as $element) {
            $element->addPrefixPaths($spec);
        }

        return $this;
    }

    /**
     * Add prefix path for all display groups
     *
     * @param  string $prefix
     * @param  string $path
     * @return Zend_Form
     */
    public function addDisplayGroupPrefixPath($prefix, $path)
    {
        $this->_displayGroupPrefixPaths[] = array(
            'prefix' => $prefix,
            'path'   => $path,
        );

        foreach ($this->getDisplayGroups() as $group) {
            $group->addPrefixPath($prefix, $path);
        }

        return $this;
    }

    /**
     * Add multiple display group prefix paths at once
     *
     * @param  array $spec
     * @return Zend_Form
     */
    public function addDisplayGroupPrefixPaths(array $spec)
    {
        foreach ($spec as $key => $value) {
            if (is_string($value) && !is_numeric($key)) {
                $this->addDisplayGroupPrefixPath($key, $value);
                continue;
            }

            if (is_string($value) && is_numeric($key)) {
                continue;
            }

            if (is_array($value)) {
                $count = count($value);
                if (array_keys($value) === range(0, $count - 1)) {
                    if ($count < 2) {
                        continue;
                    }
                    $prefix = array_shift($value);
                    $path   = array_shift($value);
                    $this->addDisplayGroupPrefixPath($prefix, $path);
                    continue;
                }
                if (array_key_exists('prefix', $value) && array_key_exists('path', $value)) {
                    $this->addDisplayGroupPrefixPath($value['prefix'], $value['path']);
                }
            }
        }
        return $this;
    }

    // Form metadata:

    /**
     * Set form attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return Zend_Form
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    /**
     * Add multiple form attributes at once
     *
     * @param  array $attribs
     * @return Zend_Form
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    /**
     * Set multiple form attributes at once
     *
     * Overwrites any previously set attributes.
     *
     * @param  array $attribs
     * @return Zend_Form
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    /**
     * Retrieve a single form attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    /**
     * Retrieve all form attributes/metadata
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Remove attribute
     *
     * @param  string $key
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (isset($this->_attribs[$key])) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all form attributes
     *
     * @return Zend_Form
     */
    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    /**
     * Set form action
     *
     * @param  string $action
     * @return Zend_Form
     */
    public function setAction($action)
    {
        return $this->setAttrib('action', (string) $action);
    }

    /**
     * Get form action
     *
     * Sets default to '' if not set.
     *
     * @return string
     */
    public function getAction()
    {
        $action = $this->getAttrib('action');
        if (null === $action) {
            $action = '';
            $this->setAction($action);
        }
        return $action;
    }

    /**
     * Set form method
     *
     * Only values in {@link $_methods()} allowed
     *
     * @param  string $method
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, $this->_methods)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('"%s" is an invalid form method', $method));
        }
        $this->setAttrib('method', $method);
        return $this;
    }

    /**
     * Retrieve form method
     *
     * @return string
     */
    public function getMethod()
    {
        if (null === ($method = $this->getAttrib('method'))) {
            $method = self::METHOD_POST;
            $this->setAttrib('method', $method);
        }
        return strtolower($method);
    }

    /**
     * Set encoding type
     *
     * @param  string $value
     * @return Zend_Form
     */
    public function setEnctype($value)
    {
        $this->setAttrib('enctype', $value);
        return $this;
    }

    /**
     * Get encoding type
     *
     * @return string
     */
    public function getEnctype()
    {
        if (null === ($enctype = $this->getAttrib('enctype'))) {
            $enctype = self::ENCTYPE_URLENCODED;
            $this->setAttrib('enctype', $enctype);
        }
        return $this->getAttrib('enctype');
    }

    /**
     * Filter a name to only allow valid variable characters
     *
     * @param  string $value
     * @param  bool $allowBrackets
     * @return string
     */
    public function filterName($value, $allowBrackets = false)
    {
        $charset = '^a-zA-Z0-9_\x7f-\xff';
        if ($allowBrackets) {
            $charset .= '\[\]';
        }
        return preg_replace('/[' . $charset . ']/', '', (string) $value);
    }

    /**
     * Set form name
     *
     * @param  string $name
     * @return Zend_Form
     */
    public function setName($name)
    {
        $name = $this->filterName($name);
        if ('' === (string)$name) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        return $this->setAttrib('name', $name);
    }

    /**
     * Get name attribute
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->getAttrib('name');
    }

    /**
     * Get fully qualified name
     *
     * Places name as subitem of array and/or appends brackets.
     *
     * @return string
     */
    public function getFullyQualifiedName()
    {
        return $this->getName();
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getId()
    {
        if (null !== ($id = $this->getAttrib('id'))) {
            return $id;
        }

        $id = $this->getFullyQualifiedName();

        // Bail early if no array notation detected
        if (!strstr($id, '[')) {
            return $id;
        }

        // Strip array notation
        if ('[]' == substr($id, -2)) {
            $id = substr($id, 0, strlen($id) - 2);
        }
        $id = str_replace('][', '-', $id);
        $id = str_replace(array(']', '['), '-', $id);
        $id = trim($id, '-');

        return $id;
    }

    /**
     * Set form legend
     *
     * @param  string $value
     * @return Zend_Form
     */
    public function setLegend($value)
    {
        $this->_legend = (string) $value;
        return $this;
    }

    /**
     * Get form legend
     *
     * @return string
     */
    public function getLegend()
    {
        return $this->_legend;
    }

    /**
     * Set form description
     *
     * @param  string $value
     * @return Zend_Form
     */
    public function setDescription($value)
    {
        $this->_description = (string) $value;
        return $this;
    }

    /**
     * Retrieve form description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set form order
     *
     * @param  int $index
     * @return Zend_Form
     */
    public function setOrder($index)
    {
        $this->_formOrder = (int) $index;
        return $this;
    }

    /**
     * Get form order
     *
     * @return int|null
     */
    public function getOrder()
    {
        return $this->_formOrder;
    }

    /**
     * When calling renderFormElements or render this method
     * is used to set $_isRendered member to prevent repeatedly
     * merging belongsTo setting
     */
    protected function _setIsRendered()
    {
        $this->_isRendered = true;
        return $this;
    }

    /**
     * Get the value of $_isRendered member
     */
    protected function _getIsRendered()
    {
        return (bool)$this->_isRendered;
    }

    // Element interaction:

    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Zend_Form_Element $element
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Zend_Form
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (is_string($element)) {
            if (null === $name) {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Elements specified by string must have an accompanying name');
            }

            if (is_array($this->_elementDecorators)) {
                if (null === $options) {
                    $options = array('decorators' => $this->_elementDecorators);
                } elseif ($options instanceof Zend_Config) {
                    $options = $options->toArray();
                }
                if (is_array($options)
                    && !array_key_exists('decorators', $options)
                ) {
                    $options['decorators'] = $this->_elementDecorators;
                }
            }

            $this->_elements[$name] = $this->createElement($element, $name, $options);
        } elseif ($element instanceof Zend_Form_Element) {
            $prefixPaths              = array();
            $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
            if (!empty($this->_elementPrefixPaths)) {
                $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
            }

            if (null === $name) {
                $name = $element->getName();
            }

            $this->_elements[$name] = $element;
            $this->_elements[$name]->addPrefixPaths($prefixPaths);
        }

        $this->_order[$name] = $this->_elements[$name]->getOrder();
        $this->_orderUpdated = true;
        $this->_setElementsBelongTo($name);

        return $this;
    }

    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this
     * method will not be attached to the form, but will contain element
     * settings as specified in the form object (including plugin loader
     * prefix paths, default decorators, etc.).
     *
     * @param  string $type
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Zend_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if (!is_string($type)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Element type must be a string indicating type');
        }

        if (!is_string($name)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Element name must be a string');
        }

        $prefixPaths              = array();
        $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
        if (!empty($this->_elementPrefixPaths)) {
            $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if ((null === $options) || !is_array($options)) {
            $options = array('prefixPath' => $prefixPaths);
        } elseif (is_array($options)) {
            if (array_key_exists('prefixPath', $options)) {
                $options['prefixPath'] = array_merge($prefixPaths, $options['prefixPath']);
            } else {
                $options['prefixPath'] = $prefixPaths;
            }
        }

        $class = $this->getPluginLoader(self::ELEMENT)->load($type);
        $element = new $class($name, $options);

        return $element;
    }

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Zend_Form
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $key => $spec) {
            $name = null;
            if (!is_numeric($key)) {
                $name = $key;
            }

            if (is_string($spec) || ($spec instanceof Zend_Form_Element)) {
                $this->addElement($spec, $name);
                continue;
            }

            if (is_array($spec)) {
                $argc = count($spec);
                $options = array();
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                    if (isset($spec['name'])) {
                        $name = $spec['name'];
                    }
                    if (isset($spec['options'])) {
                        $options = $spec['options'];
                    }
                    $this->addElement($type, $name, $options);
                } else {
                    switch ($argc) {
                        case 0:
                            continue;
                        case (1 <= $argc):
                            $type = array_shift($spec);
                        case (2 <= $argc):
                            if (null === $name) {
                                $name = array_shift($spec);
                            } else {
                                $options = array_shift($spec);
                            }
                        case (3 <= $argc):
                            if (empty($options)) {
                                $options = array_shift($spec);
                            }
                        default:
                            $this->addElement($type, $name, $options);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Set form elements (overwrites existing elements)
     *
     * @param  array $elements
     * @return Zend_Form
     */
    public function setElements(array $elements)
    {
        $this->clearElements();
        return $this->addElements($elements);
    }

    /**
     * Retrieve a single element
     *
     * @param  string $name
     * @return Zend_Form_Element|null
     */
    public function getElement($name)
    {
        if (array_key_exists($name, $this->_elements)) {
            return $this->_elements[$name];
        }
        return null;
    }

    /**
     * Retrieve all elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Remove element
     *
     * @param  string $name
     * @return boolean
     */
    public function removeElement($name)
    {
        $name = (string) $name;
        if (isset($this->_elements[$name])) {
            unset($this->_elements[$name]);
            if (array_key_exists($name, $this->_order)) {
                unset($this->_order[$name]);
                $this->_orderUpdated = true;
            } else {
                foreach ($this->_displayGroups as $group) {
                    if (null !== $group->getElement($name)) {
                        $group->removeElement($name);
                    }
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Remove all form elements
     *
     * @return Zend_Form
     */
    public function clearElements()
    {
        foreach (array_keys($this->_elements) as $key) {
            if (array_key_exists($key, $this->_order)) {
                unset($this->_order[$key]);
            }
        }
        $this->_elements     = array();
        $this->_orderUpdated = true;
        return $this;
    }

    /**
     * Set default values for elements
     *
     * Sets values for all elements specified in the array of $defaults.
     *
     * @param  array $defaults
     * @return Zend_Form
     */
    public function setDefaults(array $defaults)
    {
        $eBelongTo = null;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $defaults = $this->_dissolveArrayValue($defaults, $eBelongTo);
        }
        foreach ($this->getElements() as $name => $element) {
            $check = $defaults;
            if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                $check = $this->_dissolveArrayValue($defaults, $belongsTo);
            }
            if (array_key_exists($name, $check)) {
                $this->setDefault($name, $check[$name]);
                $defaults = $this->_dissolveArrayUnsetKey($defaults, $belongsTo, $name);
            }
        }
        foreach ($this->getSubForms() as $name => $form) {
            if (!$form->isArray() && array_key_exists($name, $defaults)) {
                $form->setDefaults($defaults[$name]);
            } else {
                $form->setDefaults($defaults);
            }
        }
        return $this;
    }

    /**
     * Set default value for an element
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Form
     */
    public function setDefault($name, $value)
    {
        $name = (string) $name;
        if ($element = $this->getElement($name)) {
            $element->setValue($value);
        } else {
            if (is_scalar($value)) {
                foreach ($this->getSubForms() as $subForm) {
                    $subForm->setDefault($name, $value);
                }
            } elseif (is_array($value) && ($subForm = $this->getSubForm($name))) {
                $subForm->setDefaults($value);
            }
        }
        return $this;
    }

    /**
     * Retrieve value for single element
     *
     * @param  string $name
     * @return mixed
     */
    public function getValue($name)
    {
        if ($element = $this->getElement($name)) {
            return $element->getValue();
        }

        if ($subForm = $this->getSubForm($name)) {
            return $subForm->getValues(true);
        }

        foreach ($this->getSubForms() as $subForm) {
            if ($name == $subForm->getElementsBelongTo()) {
                return $subForm->getValues(true);
            }
        }
        return null;
    }

    /**
     * Retrieve all form element values
     *
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getValues($suppressArrayNotation = false)
    {
        $values = array();
        $eBelongTo = null;
        
        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
        }
        
        foreach ($this->getElements() as $key => $element) {
            if (!$element->getIgnore()) {
                $merge = array();
                if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                    if ('' !== (string)$belongsTo) {
                        $key = $belongsTo . '[' . $key . ']';
                    }
                }
                $merge = $this->_attachToArray($element->getValue(), $key);
                $values = $this->_array_replace_recursive($values, $merge);
            }
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            $merge = array();
            if (!$subForm->isArray()) {
                $merge[$key] = $subForm->getValues();
            } else {
                $merge = $this->_attachToArray($subForm->getValues(true),
                                               $subForm->getElementsBelongTo());
            }
            $values = $this->_array_replace_recursive($values, $merge);
        }

        if (!$suppressArrayNotation &&
            $this->isArray() &&
            !$this->_getIsRendered()) {
            $values = $this->_attachToArray($values, $this->getElementsBelongTo());
        }

        return $values;
    }

    /**
     * Returns only the valid values from the given form input.
     *
     * For models that can be saved in a partially valid state, for example when following the builder,
     * prototype or state patterns it is particularly interessting to retrieve all the current valid
     * values to persist them.
     *
     * @param  array $data
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getValidValues($data, $suppressArrayNotation = false)
    {
        $values = array();
        $eBelongTo = null;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $data = $this->_dissolveArrayValue($data, $eBelongTo);
        }
        $context = $data;
        foreach ($this->getElements() as $key => $element) {
            if (!$element->getIgnore()) {
                $check = $data;
                if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                    $check = $this->_dissolveArrayValue($data, $belongsTo);
                }
                if (isset($check[$key])) {
                    if($element->isValid($check[$key], $context)) {
                        $merge = array();
                        if ($belongsTo !== $eBelongTo && '' !== (string)$belongsTo) {
                            $key = $belongsTo . '[' . $key . ']';
                        }
                        $merge = $this->_attachToArray($element->getValue(), $key);
                        $values = $this->_array_replace_recursive($values, $merge);
                    }
                    $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
                }
            }
        }
        foreach ($this->getSubForms() as $key => $form) {
            $merge = array();
            if (isset($data[$key]) && !$form->isArray()) {
                $tmp = $form->getValidValues($data[$key]);
                if (!empty($tmp)) {
                    $merge[$key] = $tmp;
                }
            } else {
                $tmp = $form->getValidValues($data, true);
                if (!empty($tmp)) {
                    $merge = $this->_attachToArray($tmp, $form->getElementsBelongTo());
                }
            }
            $values = $this->_array_replace_recursive($values, $merge);
        }
        if (!$suppressArrayNotation &&
            $this->isArray() &&
            !empty($values) &&
            !$this->_getIsRendered()) {
            $values = $this->_attachToArray($values, $this->getElementsBelongTo());
        }

        return $values;
    }

    /**
     * Get unfiltered element value
     *
     * @param  string $name
     * @return mixed
     */
    public function getUnfilteredValue($name)
    {
        if ($element = $this->getElement($name)) {
            return $element->getUnfilteredValue();
        }
        return null;
    }

    /**
     * Retrieve all unfiltered element values
     *
     * @return array
     */
    public function getUnfilteredValues()
    {
        $values = array();
        foreach ($this->getElements() as $key => $element) {
            $values[$key] = $element->getUnfilteredValue();
        }

        return $values;
    }

    /**
     * Set all elements' filters
     *
     * @param  array $filters
     * @return Zend_Form
     */
    public function setElementFilters(array $filters)
    {
        foreach ($this->getElements() as $element) {
            $element->setFilters($filters);
        }
        return $this;
    }

    /**
     * Set name of array elements belong to
     *
     * @param  string $array
     * @return Zend_Form
     */
    public function setElementsBelongTo($array)
    {
        $origName = $this->getElementsBelongTo();
        $name = $this->filterName($array, true);
        if ('' === $name) {
            $name = null;
        }
        $this->_elementsBelongTo = $name;

        if (null === $name) {
            $this->setIsArray(false);
            if (null !== $origName) {
                $this->_setElementsBelongTo();
            }
        } else {
            $this->setIsArray(true);
            $this->_setElementsBelongTo();
        }

        return $this;
    }

    /**
     * Set array to which elements belong
     *
     * @param  string $name Element name
     * @return void
     */
    protected function _setElementsBelongTo($name = null)
    {
        $array = $this->getElementsBelongTo();

        if (null === $array) {
            return;
        }

        if (null === $name) {
            foreach ($this->getElements() as $element) {
                $element->setBelongsTo($array);
            }
        } else {
            if (null !== ($element = $this->getElement($name))) {
                $element->setBelongsTo($array);
            }
        }
    }

    /**
     * Get name of array elements belong to
     *
     * @return string|null
     */
    public function getElementsBelongTo()
    {
        if ((null === $this->_elementsBelongTo) && $this->isArray()) {
            $name = $this->getName();
            if ('' !== (string)$name) {
                return $name;
            }
        }
        return $this->_elementsBelongTo;
    }

    /**
     * Set flag indicating elements belong to array
     *
     * @param  bool $flag Value of flag
     * @return Zend_Form
     */
    public function setIsArray($flag)
    {
        $this->_isArray = (bool) $flag;
        return $this;
    }

    /**
     * Get flag indicating if elements belong to an array
     *
     * @return bool
     */
    public function isArray()
    {
        return $this->_isArray;
    }

    // Element groups:

    /**
     * Add a form group/subform
     *
     * @param  Zend_Form $form
     * @param  string $name
     * @param  int $order
     * @return Zend_Form
     */
    public function addSubForm(Zend_Form $form, $name, $order = null)
    {
        $name = (string) $name;
        foreach ($this->_loaders as $type => $loader) {
            $loaderPaths = $loader->getPaths();
            foreach ($loaderPaths as $prefix => $paths) {
                foreach ($paths as $path) {
                    $form->addPrefixPath($prefix, $path, $type);
                }
            }
        }

        if (!empty($this->_elementPrefixPaths)) {
            foreach ($this->_elementPrefixPaths as $spec) {
                list($prefix, $path, $type) = array_values($spec);
                $form->addElementPrefixPath($prefix, $path, $type);
            }
        }

        if (!empty($this->_displayGroupPrefixPaths)) {
            foreach ($this->_displayGroupPrefixPaths as $spec) {
                list($prefix, $path) = array_values($spec);
                $form->addDisplayGroupPrefixPath($prefix, $path);
            }
        }

        if (null !== $order) {
            $form->setOrder($order);
        }

        if (($oldName = $form->getName()) &&
            $oldName !== $name &&
            $oldName === $form->getElementsBelongTo()) {
            $form->setElementsBelongTo($name);
        }

        $form->setName($name);
        $this->_subForms[$name] = $form;
        $this->_order[$name]    = $order;
        $this->_orderUpdated    = true;
        return $this;
    }

    /**
     * Add multiple form subForms/subforms at once
     *
     * @param  array $subForms
     * @return Zend_Form
     */
    public function addSubForms(array $subForms)
    {
        foreach ($subForms as $key => $spec) {
            $name = null;
            if (!is_numeric($key)) {
                $name = $key;
            }

            if ($spec instanceof Zend_Form) {
                $this->addSubForm($spec, $name);
                continue;
            }

            if (is_array($spec)) {
                $argc  = count($spec);
                $order = null;
                switch ($argc) {
                    case 0:
                        continue;
                    case (1 <= $argc):
                        $subForm = array_shift($spec);
                    case (2 <= $argc):
                        $name  = array_shift($spec);
                    case (3 <= $argc):
                        $order = array_shift($spec);
                    default:
                        $this->addSubForm($subForm, $name, $order);
                }
            }
        }
        return $this;
    }

    /**
     * Set multiple form subForms/subforms (overwrites)
     *
     * @param  array $subForms
     * @return Zend_Form
     */
    public function setSubForms(array $subForms)
    {
        $this->clearSubForms();
        return $this->addSubForms($subForms);
    }

    /**
     * Retrieve a form subForm/subform
     *
     * @param  string $name
     * @return Zend_Form|null
     */
    public function getSubForm($name)
    {
        $name = (string) $name;
        if (isset($this->_subForms[$name])) {
            return $this->_subForms[$name];
        }
        return null;
    }

    /**
     * Retrieve all form subForms/subforms
     *
     * @return array
     */
    public function getSubForms()
    {
        return $this->_subForms;
    }

    /**
     * Remove form subForm/subform
     *
     * @param  string $name
     * @return boolean
     */
    public function removeSubForm($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_subForms)) {
            unset($this->_subForms[$name]);
            if (array_key_exists($name, $this->_order)) {
                unset($this->_order[$name]);
                $this->_orderUpdated = true;
            }
            return true;
        }

        return false;
    }

    /**
     * Remove all form subForms/subforms
     *
     * @return Zend_Form
     */
    public function clearSubForms()
    {
        foreach (array_keys($this->_subForms) as $key) {
            if (array_key_exists($key, $this->_order)) {
                unset($this->_order[$key]);
            }
        }
        $this->_subForms     = array();
        $this->_orderUpdated = true;
        return $this;
    }


    // Display groups:

    /**
     * Set default display group class
     *
     * @param  string $class
     * @return Zend_Form
     */
    public function setDefaultDisplayGroupClass($class)
    {
        $this->_defaultDisplayGroupClass = (string) $class;
        return $this;
    }

    /**
     * Retrieve default display group class
     *
     * @return string
     */
    public function getDefaultDisplayGroupClass()
    {
        return $this->_defaultDisplayGroupClass;
    }

    /**
     * Add a display group
     *
     * Groups named elements for display purposes.
     *
     * If a referenced element does not yet exist in the form, it is omitted.
     *
     * @param  array $elements
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Zend_Form
     * @throws Zend_Form_Exception if no valid elements provided
     */
    public function addDisplayGroup(array $elements, $name, $options = null)
    {
        $group = array();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
                    $group[] = $add;
                }
            }
        }
        if (empty($group)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for display group');
        }

        $name = (string) $name;

        if (is_array($options)) {
            $options['elements'] = $group;
        } elseif ($options instanceof Zend_Config) {
            $options = $options->toArray();
            $options['elements'] = $group;
        } else {
            $options = array('elements' => $group);
        }

        if (isset($options['displayGroupClass'])) {
            $class = $options['displayGroupClass'];
            unset($options['displayGroupClass']);
        } else {
            $class = $this->getDefaultDisplayGroupClass();
        }

        // if (!class_exists($class)) {
            // require_once 'Zend/Loader.php';
            // Zend_Loader::loadClass($class);
        // }
        $this->_displayGroups[$name] = new $class(
            $name,
            $this->getPluginLoader(self::DECORATOR),
            $options
        );

        if (!empty($this->_displayGroupPrefixPaths)) {
            $this->_displayGroups[$name]->addPrefixPaths($this->_displayGroupPrefixPaths);
        }

        $this->_order[$name] = $this->_displayGroups[$name]->getOrder();
        $this->_orderUpdated = true;
        return $this;
    }

    /**
     * Add a display group object (used with cloning)
     *
     * @param  Zend_Form_DisplayGroup $group
     * @param  string|null $name
     * @return Zend_Form
     */
    protected function _addDisplayGroupObject(Zend_Form_DisplayGroup $group, $name = null)
    {
        if (null === $name) {
            $name = $group->getName();
            if ('' === (string)$name) {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid display group added; requires name');
            }
        }

        $this->_displayGroups[$name] = $group;

        if (!empty($this->_displayGroupPrefixPaths)) {
            $this->_displayGroups[$name]->addPrefixPaths($this->_displayGroupPrefixPaths);
        }

        $this->_order[$name] = $this->_displayGroups[$name]->getOrder();
        $this->_orderUpdated = true;
        return $this;
    }

    /**
     * Add multiple display groups at once
     *
     * @param  array $groups
     * @return Zend_Form
     */
    public function addDisplayGroups(array $groups)
    {
        foreach ($groups as $key => $spec) {
            $name = null;
            if (!is_numeric($key)) {
                $name = $key;
            }

            if ($spec instanceof Zend_Form_DisplayGroup) {
                $this->_addDisplayGroupObject($spec);
            }

            if (!is_array($spec) || empty($spec)) {
                continue;
            }

            $argc    = count($spec);
            $options = array();

            if (isset($spec['elements'])) {
                $elements = $spec['elements'];
                if (isset($spec['name'])) {
                    $name = $spec['name'];
                }
                if (isset($spec['options'])) {
                    $options = $spec['options'];
                }
                $this->addDisplayGroup($elements, $name, $options);
            } else {
                switch ($argc) {
                    case (1 <= $argc):
                        $elements = array_shift($spec);
                        if (!is_array($elements) && (null !== $name)) {
                            $elements = array_merge((array) $elements, $spec);
                            $this->addDisplayGroup($elements, $name);
                            break;
                        }
                    case (2 <= $argc):
                        if (null !== $name) {
                            $options = array_shift($spec);
                            $this->addDisplayGroup($elements, $name, $options);
                            break;
                        }
                        $name = array_shift($spec);
                    case (3 <= $argc):
                        $options = array_shift($spec);
                    default:
                        $this->addDisplayGroup($elements, $name, $options);
                }
            }
        }
        return $this;
    }

    /**
     * Add multiple display groups (overwrites)
     *
     * @param  array $groups
     * @return Zend_Form
     */
    public function setDisplayGroups(array $groups)
    {
        return $this->clearDisplayGroups()
                    ->addDisplayGroups($groups);
    }

    /**
     * Return a display group
     *
     * @param  string $name
     * @return Zend_Form_DisplayGroup|null
     */
    public function getDisplayGroup($name)
    {
        $name = (string) $name;
        if (isset($this->_displayGroups[$name])) {
            return $this->_displayGroups[$name];
        }

        return null;
    }

    /**
     * Return all display groups
     *
     * @return array
     */
    public function getDisplayGroups()
    {
        return $this->_displayGroups;
    }

    /**
     * Remove a display group by name
     *
     * @param  string $name
     * @return boolean
     */
    public function removeDisplayGroup($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_displayGroups)) {
            foreach ($this->_displayGroups[$name] as $key => $element) {
                if (array_key_exists($key, $this->_elements)) {
                    $this->_order[$key]  = $element->getOrder();
                    $this->_orderUpdated = true;
                }
            }
            unset($this->_displayGroups[$name]);

            if (array_key_exists($name, $this->_order)) {
                unset($this->_order[$name]);
                $this->_orderUpdated = true;
            }
            return true;
        }

        return false;
    }

    /**
     * Remove all display groups
     *
     * @return Zend_Form
     */
    public function clearDisplayGroups()
    {
        foreach ($this->_displayGroups as $key => $group) {
            if (array_key_exists($key, $this->_order)) {
                unset($this->_order[$key]);
            }
            foreach ($group as $name => $element) {
                if (isset($this->_elements[$name])) {
                    $this->_order[$name] = $element->getOrder();
                }
                $this->_order[$name] = $element->getOrder();
            }
        }
        $this->_displayGroups = array();
        $this->_orderUpdated  = true;
        return $this;
    }


    // Processing

    /**
     * Populate form
     *
     * Proxies to {@link setDefaults()}
     *
     * @param  array $values
     * @return Zend_Form
     */
    public function populate(array $values)
    {
        return $this->setDefaults($values);
    }

    /**
     * Determine array key name from given value
     *
     * Given a value such as foo[bar][baz], returns the last element (in this case, 'baz').
     *
     * @param  string $value
     * @return string
     */
    protected function _getArrayName($value)
    {
        if (!is_string($value) || '' === $value) {
            return $value;
        }

        if (!strstr($value, '[')) {
            return $value;
        }

        $endPos = strlen($value) - 1;
        if (']' != $value[$endPos]) {
            return $value;
        }

        $start = strrpos($value, '[') + 1;
        $name = substr($value, $start, $endPos - $start);
        return $name;
    }

    /**
     * Extract the value by walking the array using given array path.
     *
     * Given an array path such as foo[bar][baz], returns the value of the last
     * element (in this case, 'baz').
     *
     * @param  array $value Array to walk
     * @param  string $arrayPath Array notation path of the part to extract
     * @return string
     */
    protected function _dissolveArrayValue($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, 0, $arrayPos), ']');

            // Set the potentially final value or the next search point in the array
            if (isset($value[$arrayKey])) {
                $value = $value[$arrayKey];
            }

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, $arrayPos + 1), ']');
        }

        if (isset($value[$arrayPath])) {
            $value = $value[$arrayPath];
        }

        return $value;
    }

    /**
     * Given an array, an optional arrayPath and a key this method
     * dissolves the arrayPath and unsets the key within the array
     * if it exists.
     * 
     * @param array $array 
     * @param string|null $arrayPath
     * @param string $key
     * @return array
     */
    protected function _dissolveArrayUnsetKey($array, $arrayPath, $key)
    {
        $unset =& $array;
        $path  = trim(strtr((string)$arrayPath, array('[' => '/', ']' => '')), '/');
        $segs  = ('' !== $path) ? explode('/', $path) : array();
        
        foreach ($segs as $seg) {
            if (!array_key_exists($seg, (array)$unset)) {
                return $array;
            }
            $unset =& $unset[$seg];
        }
        if (array_key_exists($key, (array)$unset)) {
            unset($unset[$key]);
        }
        return $array;
    }

    /**
     * Converts given arrayPath to an array and attaches given value at the end of it.
     *
     * @param  mixed $value The value to attach
     * @param  string $arrayPath Given array path to convert and attach to.
     * @return array
     */
    protected function _attachToArray($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strrpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, $arrayPos + 1), ']');

            // Attach
            $value = array($arrayKey => $value);

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, 0, $arrayPos), ']');
        }

        $value = array($arrayPath => $value);

        return $value;
    }

    /**
     * Returns a one dimensional numerical indexed array with the
     * Elements, SubForms and Elements from DisplayGroups as Values.
     *
     * Subitems are inserted based on their order Setting if set,
     * otherwise they are appended, the resulting numerical index
     * may differ from the order value.
     * 
     * @access protected
     * @return array 
     */
    public function getElementsAndSubFormsOrdered()
    {
        $ordered = array();
        foreach ($this->_order as $name => $order) {
            $order = isset($order) ? $order : count($ordered);
            if ($this->$name instanceof Zend_Form_Element ||
                $this->$name instanceof Zend_Form) {
                array_splice($ordered, $order, 0, array($this->$name));
            } else if ($this->$name instanceof Zend_Form_DisplayGroup) {
                $subordered = array();
                foreach ($this->$name->getElements() as $element) {
                    $suborder = $element->getOrder();
                    $suborder = (null !== $suborder) ? $suborder : count($subordered);
                    array_splice($subordered, $suborder, 0, array($element));
                }
                if (!empty($subordered)) {
                    array_splice($ordered, $order, 0, $subordered);
                }
            }
        }
        return $ordered;
    }

    /**
     * This is a helper function until php 5.3 is widespreaded 
     * 
     * @param array $into
     * @access protected
     * @return void
     */
    protected function _array_replace_recursive(array $into)
    {
        $fromArrays = array_slice(func_get_args(),1);

        foreach ($fromArrays as $from) {
            foreach ($from as $key => $value) {
                if (is_array($value)) {
                    if (!isset($into[$key])) {
                        $into[$key] = array();
                    }
                    $into[$key] = $this->_array_replace_recursive($into[$key], $from[$key]);
                } else {
                    $into[$key] = $value;
                }
            }
        }
        return $into;
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if (!is_array($data)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(__METHOD__ . ' expects an array');
        }
        $translator = $this->getTranslator();
        $valid      = true;
        $eBelongTo  = null;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $data = $this->_dissolveArrayValue($data, $eBelongTo);
        }
        $context = $data;
        foreach ($this->getElements() as $key => $element) {
            if (null !== $translator && !$element->hasTranslator()) {
                $element->setTranslator($translator);
            }
            $check = $data;
            if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                $check = $this->_dissolveArrayValue($data, $belongsTo);
            }
            if (!isset($check[$key])) {
                $valid = $element->isValid(null, $context) && $valid;
            } else {
                $valid = $element->isValid($check[$key], $context) && $valid;
                $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
            }
        }
        foreach ($this->getSubForms() as $key => $form) {
            if (null !== $translator && !$form->hasTranslator()) {
                $form->setTranslator($translator);
            }
            if (isset($data[$key]) && !$form->isArray()) {
                $valid = $form->isValid($data[$key]) && $valid;
            } else {
                $valid = $form->isValid($data) && $valid;
            }
        }

        $this->_errorsExist = !$valid;

        // If manually flagged as an error, return invalid status
        if ($this->_errorsForced) {
            return false;
        }

        return $valid;
    }

    /**
     * Validate a partial form
     *
     * Does not check for required flags.
     *
     * @param  array $data
     * @return boolean
     */
    public function isValidPartial(array $data)
    {
        $eBelongTo  = null;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $data = $this->_dissolveArrayValue($data, $eBelongTo);
        }

        $translator = $this->getTranslator();
        $valid      = true;
        $context    = $data;

        foreach ($this->getElements() as $key => $element) {
            $check = $data;
            if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                $check = $this->_dissolveArrayValue($data, $belongsTo);
            }
            if (isset($check[$key])) {
                if (null !== $translator && !$element->hasTranslator()) {
                    $element->setTranslator($translator);
                }
                $valid = $element->isValid($check[$key], $context) && $valid;
                $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
            }
        }
        foreach ($this->getSubForms() as $key => $form) {
            if (null !== $translator && !$form->hasTranslator()) {
                $form->setTranslator($translator);
            }
            if (isset($data[$key]) && !$form->isArray()) {
                $valid = $form->isValidPartial($data[$key]) && $valid;
            } else {
                $valid = $form->isValidPartial($data) && $valid;
            }
        }

        $this->_errorsExist = !$valid;
        return $valid;
    }

    /**
     * Process submitted AJAX data
     *
     * Checks if provided $data is valid, via {@link isValidPartial()}. If so,
     * it returns JSON-encoded boolean true. If not, it returns JSON-encoded
     * error messages (as returned by {@link getMessages()}).
     *
     * @param  array $data
     * @return string JSON-encoded boolean true or error messages
     */
    public function processAjax(array $data)
    {
        // require_once 'Zend/Json.php';
        if ($this->isValidPartial($data)) {
            return Zend_Json::encode(true);
        }
        $messages = $this->getMessages();
        return Zend_Json::encode($messages);
    }

    /**
     * Add a custom error message to return in the event of failed validation
     *
     * @param  string $message
     * @return Zend_Form
     */
    public function addErrorMessage($message)
    {
        $this->_errorMessages[] = (string) $message;
        return $this;
    }

    /**
     * Add multiple custom error messages to return in the event of failed validation
     *
     * @param  array $messages
     * @return Zend_Form
     */
    public function addErrorMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->addErrorMessage($message);
        }
        return $this;
    }

    /**
     * Same as addErrorMessages(), but clears custom error message stack first
     *
     * @param  array $messages
     * @return Zend_Form
     */
    public function setErrorMessages(array $messages)
    {
        $this->clearErrorMessages();
        return $this->addErrorMessages($messages);
    }

    /**
     * Retrieve custom error messages
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }

    /**
     * Clear custom error messages stack
     *
     * @return Zend_Form
     */
    public function clearErrorMessages()
    {
        $this->_errorMessages = array();
        return $this;
    }

    /**
     * Mark the element as being in a failed validation state
     *
     * @return Zend_Form
     */
    public function markAsError()
    {
        $this->_errorsExist  = true;
        $this->_errorsForced = true;
        return $this;
    }

    /**
     * Add an error message and mark element as failed validation
     *
     * @param  string $message
     * @return Zend_Form
     */
    public function addError($message)
    {
        $this->addErrorMessage($message);
        $this->markAsError();
        return $this;
    }

    /**
     * Add multiple error messages and flag element as failed validation
     *
     * @param  array $messages
     * @return Zend_Form
     */
    public function addErrors(array $messages)
    {
        foreach ($messages as $message) {
            $this->addError($message);
        }
        return $this;
    }

    /**
     * Overwrite any previously set error messages and flag as failed validation
     *
     * @param  array $messages
     * @return Zend_Form
     */
    public function setErrors(array $messages)
    {
        $this->clearErrorMessages();
        return $this->addErrors($messages);
    }


    public function persistData()
    {
    }

    /**
     * Are there errors in the form?
     *
     * @return bool
     */
    public function isErrors()
    {
        return $this->_errorsExist;
    }

    /**
     * Get error codes for all elements failing validation
     *
     * @param  string $name
     * @return array
     */
    public function getErrors($name = null, $suppressArrayNotation = false)
    {
        $errors = array();
        if (null !== $name) {
            if (isset($this->_elements[$name])) {
                return $this->getElement($name)->getErrors();
            } else if (isset($this->_subForms[$name])) {
                return $this->getSubForm($name)->getErrors(null, true);
            }
        }
        
        foreach ($this->_elements as $key => $element) {
            $errors[$key] = $element->getErrors();
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            $merge = array();
            if (!$subForm->isArray()) {
                $merge[$key] = $subForm->getErrors();
            } else {
                $merge = $this->_attachToArray($subForm->getErrors(null, true),
                                               $subForm->getElementsBelongTo());
            }
            $errors = $this->_array_replace_recursive($errors, $merge);
        }

        if (!$suppressArrayNotation &&
            $this->isArray() &&
            !$this->_getIsRendered()) {
            $errors = $this->_attachToArray($errors, $this->getElementsBelongTo());
        }

        return $errors;
    }

    /**
     * Retrieve error messages from elements failing validations
     *
     * @param  string $name
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getMessages($name = null, $suppressArrayNotation = false)
    {
        if (null !== $name) {
            if (isset($this->_elements[$name])) {
                return $this->getElement($name)->getMessages();
            } else if (isset($this->_subForms[$name])) {
                return $this->getSubForm($name)->getMessages(null, true);
            }
            foreach ($this->getSubForms() as $key => $subForm) {
                if ($subForm->isArray()) {
                    $belongTo = $subForm->getElementsBelongTo();
                    if ($name == $this->_getArrayName($belongTo)) {
                        return $subForm->getMessages(null, true);
                    }
                }
            }
        }

        $customMessages = $this->_getErrorMessages();
        if ($this->isErrors() && !empty($customMessages)) {
            return $customMessages;
        }

        $messages = array();

        foreach ($this->getElements() as $name => $element) {
            $eMessages = $element->getMessages();
            if (!empty($eMessages)) {
                $messages[$name] = $eMessages;
            }
        }

        foreach ($this->getSubForms() as $key => $subForm) {
            $merge = $subForm->getMessages(null, true);
            if (!empty($merge)) {
                if (!$subForm->isArray()) {
                    $merge = array($key => $merge);
                } else {
                    $merge = $this->_attachToArray($merge,
                                                   $subForm->getElementsBelongTo());
                }
                $messages = $this->_array_replace_recursive($messages, $merge);
            }
        }

        if (!$suppressArrayNotation &&
            $this->isArray() &&
            !$this->_getIsRendered()) {
            $messages = $this->_attachToArray($messages, $this->getElementsBelongTo());
        }

        return $messages;
    }

    /**
     * Retrieve translated custom error messages
     * Proxies to {@link _getErrorMessages()}.
     * 
     * @return array
     */
    public function getCustomMessages()
    {
        return $this->_getErrorMessages();
    }


    // Rendering

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Form
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if (null === $this->_view) {
            // require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    /**
     * Instantiate a decorator based on class name or class name fragment
     *
     * @param  string $name
     * @param  null|array $options
     * @return Zend_Form_Decorator_Interface
     */
    protected function _getDecorator($name, $options)
    {
        $class = $this->getPluginLoader(self::DECORATOR)->load($name);
        if (null === $options) {
            $decorator = new $class;
        } else {
            $decorator = new $class($options);
        }

        return $decorator;
    }

    /**
     * Add a decorator for rendering the element
     *
     * @param  string|Zend_Form_Decorator_Interface $decorator
     * @param  array|Zend_Config $options Options with which to initialize decorator
     * @return Zend_Form
     */
    public function addDecorator($decorator, $options = null)
    {
        if ($decorator instanceof Zend_Form_Decorator_Interface) {
            $name = get_class($decorator);
        } elseif (is_string($decorator)) {
            $name      = $decorator;
            $decorator = array(
                'decorator' => $name,
                'options'   => $options,
            );
        } elseif (is_array($decorator)) {
            foreach ($decorator as $name => $spec) {
                break;
            }
            if (is_numeric($name)) {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid alias provided to addDecorator; must be alphanumeric string');
            }
            if (is_string($spec)) {
                $decorator = array(
                    'decorator' => $spec,
                    'options'   => $options,
                );
            } elseif ($spec instanceof Zend_Form_Decorator_Interface) {
                $decorator = $spec;
            }
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid decorator provided to addDecorator; must be string or Zend_Form_Decorator_Interface');
        }

        $this->_decorators[$name] = $decorator;

        return $this;
    }

    /**
     * Add many decorators at once
     *
     * @param  array $decorators
     * @return Zend_Form
     */
    public function addDecorators(array $decorators)
    {
        foreach ($decorators as $decoratorName => $decoratorInfo) {
            if (is_string($decoratorInfo) ||
                $decoratorInfo instanceof Zend_Form_Decorator_Interface) {
                if (!is_numeric($decoratorName)) {
                    $this->addDecorator(array($decoratorName => $decoratorInfo));
                } else {
                    $this->addDecorator($decoratorInfo);
                }
            } elseif (is_array($decoratorInfo)) {
                $argc    = count($decoratorInfo);
                $options = array();
                if (isset($decoratorInfo['decorator'])) {
                    $decorator = $decoratorInfo['decorator'];
                    if (isset($decoratorInfo['options'])) {
                        $options = $decoratorInfo['options'];
                    }
                    $this->addDecorator($decorator, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $decorator  = array_shift($decoratorInfo);
                        case (2 <= $argc):
                            $options = array_shift($decoratorInfo);
                        default:
                            $this->addDecorator($decorator, $options);
                            break;
                    }
                }
            } else {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid decorator passed to addDecorators()');
            }
        }

        return $this;
    }

    /**
     * Overwrite all decorators
     *
     * @param  array $decorators
     * @return Zend_Form
     */
    public function setDecorators(array $decorators)
    {
        $this->clearDecorators();
        return $this->addDecorators($decorators);
    }

    /**
     * Retrieve a registered decorator
     *
     * @param  string $name
     * @return false|Zend_Form_Decorator_Abstract
     */
    public function getDecorator($name)
    {
        if (!isset($this->_decorators[$name])) {
            $len = strlen($name);
            foreach ($this->_decorators as $localName => $decorator) {
                if ($len > strlen($localName)) {
                    continue;
                }

                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($decorator)) {
                        return $this->_loadDecorator($decorator, $localName);
                    }
                    return $decorator;
                }
            }
            return false;
        }

        if (is_array($this->_decorators[$name])) {
            return $this->_loadDecorator($this->_decorators[$name], $name);
        }

        return $this->_decorators[$name];
    }

    /**
     * Retrieve all decorators
     *
     * @return array
     */
    public function getDecorators()
    {
        foreach ($this->_decorators as $key => $value) {
            if (is_array($value)) {
                $this->_loadDecorator($value, $key);
            }
        }
        return $this->_decorators;
    }

    /**
     * Remove a single decorator
     *
     * @param  string $name
     * @return bool
     */
    public function removeDecorator($name)
    {
        $decorator = $this->getDecorator($name);
        if ($decorator) {
            if (array_key_exists($name, $this->_decorators)) {
                unset($this->_decorators[$name]);
            } else {
                $class = get_class($decorator);
                if (!array_key_exists($class, $this->_decorators)) {
                    return false;
                }
                unset($this->_decorators[$class]);
            }
            return true;
        }

        return false;
    }

    /**
     * Clear all decorators
     *
     * @return Zend_Form
     */
    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    /**
     * Set all element decorators as specified
     *
     * @param  array $decorators
     * @param  array|null $elements Specific elements to decorate or exclude from decoration
     * @param  bool $include Whether $elements is an inclusion or exclusion list
     * @return Zend_Form
     */
    public function setElementDecorators(array $decorators, array $elements = null, $include = true)
    {
        if (is_array($elements)) {
            if ($include) {
                $elementObjs = array();
                foreach ($elements as $name) {
                    if (null !== ($element = $this->getElement($name))) {
                        $elementObjs[] = $element;
                    }
                }
            } else {
                $elementObjs = $this->getElements();
                foreach ($elements as $name) {
                    if (array_key_exists($name, $elementObjs)) {
                        unset($elementObjs[$name]);
                    }
                }
            }
        } else {
            $elementObjs = $this->getElements();
        }

        foreach ($elementObjs as $element) {
            $element->setDecorators($decorators);
        }

        $this->_elementDecorators = $decorators;

        return $this;
    }

    /**
     * Set all display group decorators as specified
     *
     * @param  array $decorators
     * @return Zend_Form
     */
    public function setDisplayGroupDecorators(array $decorators)
    {
        foreach ($this->getDisplayGroups() as $group) {
            $group->setDecorators($decorators);
        }

        return $this;
    }

    /**
     * Set all subform decorators as specified
     *
     * @param  array $decorators
     * @return Zend_Form
     */
    public function setSubFormDecorators(array $decorators)
    {
        foreach ($this->getSubForms() as $form) {
            $form->setDecorators($decorators);
        }

        return $this;
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        $this->_setIsRendered();
        return $content;
    }

    /**
     * Serialize as string
     *
     * Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            $message = "Exception caught by form: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }


    // Localization:

    /**
     * Set translator object
     *
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator
     * @return Zend_Form
     */
    public function setTranslator($translator = null)
    {
        if (null === $translator) {
            $this->_translator = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid translator specified');
        }

        return $this;
    }

    /**
     * Set global default translator object
     *
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator
     * @return void
     */
    public static function setDefaultTranslator($translator = null)
    {
        if (null === $translator) {
            self::$_translatorDefault = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            self::$_translatorDefault = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            self::$_translatorDefault = $translator->getAdapter();
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid translator specified');
        }
    }

    /**
     * Retrieve translator object
     *
     * @return Zend_Translate|null
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            return self::getDefaultTranslator();
        }

        return $this->_translator;
    }
    
    /**
     * Does this form have its own specific translator?
     * 
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool)$this->_translator;
    }    

    /**
     * Get global default translator object
     *
     * @return null|Zend_Translate
     */
    public static function getDefaultTranslator()
    {
        if (null === self::$_translatorDefault) {
            // require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $translator = Zend_Registry::get('Zend_Translate');
                if ($translator instanceof Zend_Translate_Adapter) {
                    return $translator;
                } elseif ($translator instanceof Zend_Translate) {
                    return $translator->getAdapter();
                }
            }
        }
        return self::$_translatorDefault;
    }

    /**
     * Is there a default translation object set?
     * 
     * @return boolean
     */
    public static function hasDefaultTranslator()
    { 
        return (bool)self::$_translatorDefault;
    }
    
    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form
     */
    public function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool) $flag;
        return $this;
    }

    /**
     * Is translation disabled?
     *
     * @return bool
     */
    public function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }

    /**
     * Overloading: access to elements, form groups, and display groups
     *
     * @param  string $name
     * @return Zend_Form_Element|Zend_Form|null
     */
    public function __get($name)
    {
        if (isset($this->_elements[$name])) {
            return $this->_elements[$name];
        } elseif (isset($this->_subForms[$name])) {
            return $this->_subForms[$name];
        } elseif (isset($this->_displayGroups[$name])) {
            return $this->_displayGroups[$name];
        }

        return null;
    }

    /**
     * Overloading: access to elements, form groups, and display groups
     *
     * @param  string $name
     * @param  Zend_Form_Element|Zend_Form $value
     * @return void
     * @throws Zend_Form_Exception for invalid $value
     */
    public function __set($name, $value)
    {
        if ($value instanceof Zend_Form_Element) {
            $this->addElement($value, $name);
            return;
        } elseif ($value instanceof Zend_Form) {
            $this->addSubForm($value, $name);
            return;
        } elseif (is_array($value)) {
            $this->addDisplayGroup($value, $name);
            return;
        }

        // require_once 'Zend/Form/Exception.php';
        if (is_object($value)) {
            $type = get_class($value);
        } else {
            $type = gettype($value);
        }
        throw new Zend_Form_Exception('Only form elements and groups may be overloaded; variable of type "' . $type . '" provided');
    }

    /**
     * Overloading: access to elements, form groups, and display groups
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->_elements[$name])
            || isset($this->_subForms[$name])
            || isset($this->_displayGroups[$name]))
        {
            return true;
        }

        return false;
    }

    /**
     * Overloading: access to elements, form groups, and display groups
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->_elements[$name])) {
            unset($this->_elements[$name]);
        } elseif (isset($this->_subForms[$name])) {
            unset($this->_subForms[$name]);
        } elseif (isset($this->_displayGroups[$name])) {
            unset($this->_displayGroups[$name]);
        }
    }

    /**
     * Overloading: allow rendering specific decorators
     *
     * Call renderDecoratorName() to render a specific decorator.
     *
     * @param  string $method
     * @param  array $args
     * @return string
     * @throws Zend_Form_Exception for invalid decorator or invalid method call
     */
    public function __call($method, $args)
    {
        if ('render' == substr($method, 0, 6)) {
            $decoratorName = substr($method, 6);
            if (false !== ($decorator = $this->getDecorator($decoratorName))) {
                $decorator->setElement($this);
                $seed = '';
                if (0 < count($args)) {
                    $seed = array_shift($args);
                }
                if ($decoratorName === 'FormElements' ||
                    $decoratorName === 'PrepareElements') {
                        $this->_setIsRendered();
                }
                return $decorator->render($seed);
            }

            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Decorator by name %s does not exist', $decoratorName));
        }

        // require_once 'Zend/Form/Exception.php';
        throw new Zend_Form_Exception(sprintf('Method %s does not exist', $method));
    }

    // Interfaces: Iterator, Countable

    /**
     * Current element/subform/display group
     *
     * @return Zend_Form_Element|Zend_Form_DisplayGroup|Zend_Form
     */
    public function current()
    {
        $this->_sort();
        current($this->_order);
        $key = key($this->_order);

        if (isset($this->_elements[$key])) {
            return $this->getElement($key);
        } elseif (isset($this->_subForms[$key])) {
            return $this->getSubForm($key);
        } elseif (isset($this->_displayGroups[$key])) {
            return $this->getDisplayGroup($key);
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Corruption detected in form; invalid key ("%s") found in internal iterator', (string) $key));
        }
    }

    /**
     * Current element/subform/display group name
     *
     * @return string
     */
    public function key()
    {
        $this->_sort();
        return key($this->_order);
    }

    /**
     * Move pointer to next element/subform/display group
     *
     * @return void
     */
    public function next()
    {
        $this->_sort();
        next($this->_order);
    }

    /**
     * Move pointer to beginning of element/subform/display group loop
     *
     * @return void
     */
    public function rewind()
    {
        $this->_sort();
        reset($this->_order);
    }

    /**
     * Determine if current element/subform/display group is valid
     *
     * @return bool
     */
    public function valid()
    {
        $this->_sort();
        return (current($this->_order) !== false);
    }

    /**
     * Count of elements/subforms that are iterable
     *
     * @return int
     */
    public function count()
    {
        return count($this->_order);
    }

    /**
     * Set flag to disable loading default decorators
     *
     * @param  bool $flag
     * @return Zend_Form
     */
    public function setDisableLoadDefaultDecorators($flag)
    {
        $this->_disableLoadDefaultDecorators = (bool) $flag;
        return $this;
    }

    /**
     * Should we load the default decorators?
     *
     * @return bool
     */
    public function loadDefaultDecoratorsIsDisabled()
    {
        return $this->_disableLoadDefaultDecorators;
    }

    /**
     * Load the default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form'))
                 ->addDecorator('Form');
        }
        return $this;
    }

    /**
     * Sort items according to their order
     *
     * @return void
     */
    protected function _sort()
    {
        if ($this->_orderUpdated) {
            $items = array();
            $index = 0;
            foreach ($this->_order as $key => $order) {
                if (null === $order) {
                    if (null === ($order = $this->{$key}->getOrder())) {
                        while (array_search($index, $this->_order, true)) {
                            ++$index;
                        }
                        $items[$index] = $key;
                        ++$index;
                    } else {
                        $items[$order] = $key;
                    }
                } else {
                    $items[$order] = $key;
                }
            }

            $items = array_flip($items);
            asort($items);
            $this->_order = $items;
            $this->_orderUpdated = false;
        }
    }

    /**
     * Lazy-load a decorator
     *
     * @param  array $decorator Decorator type and options
     * @param  mixed $name Decorator name or alias
     * @return Zend_Form_Decorator_Interface
     */
    protected function _loadDecorator(array $decorator, $name)
    {
        $sameName = false;
        if ($name == $decorator['decorator']) {
            $sameName = true;
        }

        $instance = $this->_getDecorator($decorator['decorator'], $decorator['options']);
        if ($sameName) {
            $newName            = get_class($instance);
            $decoratorNames     = array_keys($this->_decorators);
            $order              = array_flip($decoratorNames);
            $order[$newName]    = $order[$name];
            $decoratorsExchange = array();
            unset($order[$name]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $newName) {
                    $decoratorsExchange[$key] = $instance;
                    continue;
                }
                $decoratorsExchange[$key] = $this->_decorators[$key];
            }
            $this->_decorators = $decoratorsExchange;
        } else {
            $this->_decorators[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Retrieve optionally translated custom error messages
     *
     * @return array
     */
    protected function _getErrorMessages()
    {
        $messages   = $this->getErrorMessages();
        $translator = $this->getTranslator();
        if (null !== $translator) {
            foreach ($messages as $key => $message) {
                $messages[$key] = $translator->translate($message);
            }
        }
        return $messages;
    }
}
