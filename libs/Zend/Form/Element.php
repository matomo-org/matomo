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

/** @see Zend_Filter */
// require_once 'Zend/Filter.php';

/** @see Zend_Form */
// require_once 'Zend/Form.php';

/** @see Zend_Validate_Interface */
// require_once 'Zend/Validate/Interface.php';

/** @see Zend_Validate_Abstract */
// require_once 'Zend/Validate/Abstract.php';

/**
 * Zend_Form_Element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Element.php 22465 2010-06-19 17:41:03Z alab $
 */
class Zend_Form_Element implements Zend_Validate_Interface
{
    /**
     * Element Constants
     */
    const DECORATOR = 'DECORATOR';
    const FILTER    = 'FILTER';
    const VALIDATE  = 'VALIDATE';

    /**
     * Default view helper to use
     * @var string
     */
    public $helper = 'formText';

    /**
     * 'Allow empty' flag
     * @var bool
     */
    protected $_allowEmpty = true;

    /**
     * Flag indicating whether or not to insert NotEmpty validator when element is required
     * @var bool
     */
    protected $_autoInsertNotEmptyValidator = true;

    /**
     * Array to which element belongs
     * @var string
     */
    protected $_belongsTo;

    /**
     * Element decorators
     * @var array
     */
    protected $_decorators = array();

    /**
     * Element description
     * @var string
     */
    protected $_description;

    /**
     * Should we disable loading the default decorators?
     * @var bool
     */
    protected $_disableLoadDefaultDecorators = false;

    /**
     * Custom error messages
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Validation errors
     * @var array
     */
    protected $_errors = array();

    /**
     * Separator to use when concatenating aggregate error messages (for
     * elements having array values)
     * @var string
     */
    protected $_errorMessageSeparator = '; ';

    /**
     * Element filters
     * @var array
     */
    protected $_filters = array();

    /**
     * Ignore flag (used when retrieving values at form level)
     * @var bool
     */
    protected $_ignore = false;

    /**
     * Does the element represent an array?
     * @var bool
     */
    protected $_isArray = false;

    /**
     * Is the error marked as in an invalid state?
     * @var bool
     */
    protected $_isError = false;

    /**
     * Has the element been manually marked as invalid?
     * @var bool
     */
    protected $_isErrorForced = false;

    /**
     * Element label
     * @var string
     */
    protected $_label;

    /**
     * Plugin loaders for filter and validator chains
     * @var array
     */
    protected $_loaders = array();

    /**
     * Formatted validation error messages
     * @var array
     */
    protected $_messages = array();

    /**
     * Element name
     * @var string
     */
    protected $_name;

    /**
     * Order of element
     * @var int
     */
    protected $_order;

    /**
     * Required flag
     * @var bool
     */
    protected $_required = false;

    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Is translation disabled?
     * @var bool
     */
    protected $_translatorDisabled = false;

    /**
     * Element type
     * @var string
     */
    protected $_type;

    /**
     * Array of initialized validators
     * @var array Validators
     */
    protected $_validators = array();

    /**
     * Array of un-initialized validators
     * @var array
     */
    protected $_validatorRules = array();

    /**
     * Element value
     * @var mixed
     */
    protected $_value;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Is a specific decorator being rendered via the magic renderDecorator()?
     *
     * This is to allow execution of logic inside the render() methods of child
     * elements during the magic call while skipping the parent render() method.
     *
     * @var bool
     */
    protected $_isPartialRendering = false;

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  string|array|Zend_Config $spec
     * @param  array|Zend_Config $options
     * @return void
     * @throws Zend_Form_Exception if no element name after initialization
     */
    public function __construct($spec, $options = null)
    {
        if (is_string($spec)) {
            $this->setName($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        } elseif ($spec instanceof Zend_Config) {
            $this->setConfig($spec);
        }

        if (is_string($spec) && is_array($options)) {
            $this->setOptions($options);
        } elseif (is_string($spec) && ($options instanceof Zend_Config)) {
            $this->setConfig($options);
        }

        if (null === $this->getName()) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Zend_Form_Element requires each element to have a name');
        }

        /**
         * Extensions
         */
        $this->init();

        /**
         * Register ViewHelper decorator by default
         */
        $this->loadDefaultDecorators();
    }

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set flag to disable loading default decorators
     *
     * @param  bool $flag
     * @return Zend_Form_Element
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
     * Load default decorators
     *
     * @return Zend_Form_Element
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper')
                ->addDecorator('Errors')
                ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
                ->addDecorator('HtmlTag', array('tag' => 'dd',
                                                'id'  => $this->getName() . '-element'))
                ->addDecorator('Label', array('tag' => 'dt'));
        }
        return $this;
    }

    /**
     * Set object state from options array
     *
     * @param  array $options
     * @return Zend_Form_Element
     */
    public function setOptions(array $options)
    {
        if (isset($options['prefixPath'])) {
            $this->addPrefixPaths($options['prefixPath']);
            unset($options['prefixPath']);
        }

        if (isset($options['disableTranslator'])) {
            $this->setDisableTranslator($options['disableTranslator']);
            unset($options['disableTranslator']);
        }

        unset($options['options']);
        unset($options['config']);

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (in_array($method, array('setTranslator', 'setPluginLoader', 'setView'))) {
                if (!is_object($value)) {
                    continue;
                }
            }

            if (method_exists($this, $method)) {
                // Setter exists; use it
                $this->$method($value);
            } else {
                // Assume it's metadata
                $this->setAttrib($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set object state from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Zend_Form_Element
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }


    // Localization:

    /**
     * Set translator object for localization
     *
     * @param  Zend_Translate|null $translator
     * @return Zend_Form_Element
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
     * Retrieve localization translator object
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            return Zend_Form::getDefaultTranslator();
        }
        return $this->_translator;
    }

    /**
     * Does this element have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool)$this->_translator;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form_Element
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

    // Metadata

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
     * Set element name
     *
     * @param  string $name
     * @return Zend_Form_Element
     */
    public function setName($name)
    {
        $name = $this->filterName($name);
        if ('' === $name) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        $this->_name = $name;
        return $this;
    }

    /**
     * Return element name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
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
        $name = $this->getName();

        if (null !== ($belongsTo = $this->getBelongsTo())) {
            $name = $belongsTo . '[' . $name . ']';
        }

        if ($this->isArray()) {
            $name .= '[]';
        }

        return $name;
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
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
     * Set element value
     *
     * @param  mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * Filter a value
     *
     * @param  string $value
     * @param  string $key
     * @return void
     */
    protected function _filterValue(&$value, &$key)
    {
        foreach ($this->getFilters() as $filter) {
            $value = $filter->filter($value);
        }
    }

    /**
     * Retrieve filtered element value
     *
     * @return mixed
     */
    public function getValue()
    {
        $valueFiltered = $this->_value;

        if ($this->isArray() && is_array($valueFiltered)) {
            array_walk_recursive($valueFiltered, array($this, '_filterValue'));
        } else {
            $this->_filterValue($valueFiltered, $valueFiltered);
        }

        return $valueFiltered;
    }

    /**
     * Retrieve unfiltered element value
     *
     * @return mixed
     */
    public function getUnfilteredValue()
    {
        return $this->_value;
    }

    /**
     * Set element label
     *
     * @param  string $label
     * @return Zend_Form_Element
     */
    public function setLabel($label)
    {
        $this->_label = (string) $label;
        return $this;
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getLabel()
    {
        $translator = $this->getTranslator();
        if (null !== $translator) {
            return $translator->translate($this->_label);
        }

        return $this->_label;
    }

    /**
     * Set element order
     *
     * @param  int $order
     * @return Zend_Form_Element
     */
    public function setOrder($order)
    {
        $this->_order = (int) $order;
        return $this;
    }

    /**
     * Retrieve element order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Set required flag
     *
     * @param  bool $flag Default value is true
     * @return Zend_Form_Element
     */
    public function setRequired($flag = true)
    {
        $this->_required = (bool) $flag;
        return $this;
    }

    /**
     * Is the element required?
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_required;
    }

    /**
     * Set flag indicating whether a NotEmpty validator should be inserted when element is required
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setAutoInsertNotEmptyValidator($flag)
    {
        $this->_autoInsertNotEmptyValidator = (bool) $flag;
        return $this;
    }

    /**
     * Get flag indicating whether a NotEmpty validator should be inserted when element is required
     *
     * @return bool
     */
    public function autoInsertNotEmptyValidator()
    {
        return $this->_autoInsertNotEmptyValidator;
    }

    /**
     * Set element description
     *
     * @param  string $description
     * @return Zend_Form_Element
     */
    public function setDescription($description)
    {
        $this->_description = (string) $description;
        return $this;
    }

    /**
     * Retrieve element description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set 'allow empty' flag
     *
     * When the allow empty flag is enabled and the required flag is false, the
     * element will validate with empty values.
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setAllowEmpty($flag)
    {
        $this->_allowEmpty = (bool) $flag;
        return $this;
    }

    /**
     * Get 'allow empty' flag
     *
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->_allowEmpty;
    }

    /**
     * Set ignore flag (used when retrieving values at form level)
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setIgnore($flag)
    {
        $this->_ignore = (bool) $flag;
        return $this;
    }

    /**
     * Get ignore flag (used when retrieving values at form level)
     *
     * @return bool
     */
    public function getIgnore()
    {
        return $this->_ignore;
    }

    /**
     * Set flag indicating if element represents an array
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setIsArray($flag)
    {
        $this->_isArray = (bool) $flag;
        return $this;
    }

    /**
     * Is the element representing an array?
     *
     * @return bool
     */
    public function isArray()
    {
        return $this->_isArray;
    }

    /**
     * Set array to which element belongs
     *
     * @param  string $array
     * @return Zend_Form_Element
     */
    public function setBelongsTo($array)
    {
        $array = $this->filterName($array, true);
        if (!empty($array)) {
            $this->_belongsTo = $array;
        }

        return $this;
    }

    /**
     * Return array name to which element belongs
     *
     * @return string
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    /**
     * Return element type
     *
     * @return string
     */
    public function getType()
    {
        if (null === $this->_type) {
            $this->_type = get_class($this);
        }

        return $this->_type;
    }

    /**
     * Set element attribute
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception for invalid $name values
     */
    public function setAttrib($name, $value)
    {
        $name = (string) $name;
        if ('_' == $name[0]) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Invalid attribute "%s"; must not contain a leading underscore', $name));
        }

        if (null === $value) {
            unset($this->$name);
        } else {
            $this->$name = $value;
        }

        return $this;
    }

    /**
     * Set multiple attributes at once
     *
     * @param  array $attribs
     * @return Zend_Form_Element
     */
    public function setAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }

        return $this;
    }

    /**
     * Retrieve element attribute
     *
     * @param  string $name
     * @return string
     */
    public function getAttrib($name)
    {
        $name = (string) $name;
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    /**
     * Return all attributes
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = get_object_vars($this);
        foreach ($attribs as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Overloading: retrieve object property
     *
     * Prevents access to properties beginning with '_'.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ('_' == $key[0]) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Cannot retrieve value for protected/private property "%s"', $key));
        }

        if (!isset($this->$key)) {
            return null;
        }

        return $this->$key;
    }

    /**
     * Overloading: set object property
     *
     * @param  string $key
     * @param  mixed $value
     * @return voide
     */
    public function __set($key, $value)
    {
        $this->setAttrib($key, $value);
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
            $this->_isPartialRendering = true;
            $this->render();
            $this->_isPartialRendering = false;
            $decoratorName = substr($method, 6);
            if (false !== ($decorator = $this->getDecorator($decoratorName))) {
                $decorator->setElement($this);
                $seed = '';
                if (0 < count($args)) {
                    $seed = array_shift($args);
                }
                return $decorator->render($seed);
            }

            // require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Decorator by name %s does not exist', $decoratorName));
        }

        // require_once 'Zend/Form/Element/Exception.php';
        throw new Zend_Form_Element_Exception(sprintf('Method %s does not exist', $method));
    }

    // Loaders

    /**
     * Set plugin loader to use for validator or filter chain
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @param  string $type 'decorator', 'filter', or 'validate'
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception on invalid type
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::FILTER:
            case self::VALIDATE:
                $this->_loaders[$type] = $loader;
                return $this;
            default:
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to setPluginLoader()', $type));
        }
    }

    /**
     * Retrieve plugin loader for validator or filter chain
     *
     * Instantiates with default rules if none available for that type. Use
     * 'decorator', 'filter', or 'validate' for $type.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     * @throws Zend_Loader_Exception on invalid type.
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::FILTER:
            case self::VALIDATE:
                $prefixSegment = ucfirst(strtolower($type));
                $pathSegment   = $prefixSegment;
            case self::DECORATOR:
                if (!isset($prefixSegment)) {
                    $prefixSegment = 'Form_Decorator';
                    $pathSegment   = 'Form/Decorator';
                }
                if (!isset($this->_loaders[$type])) {
                    // require_once 'Zend/Loader/PluginLoader.php';
                    $this->_loaders[$type] = new Zend_Loader_PluginLoader(
                        array('Zend_' . $prefixSegment . '_' => 'Zend/' . $pathSegment . '/')
                    );
                }
                return $this->_loaders[$type];
            default:
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Add prefix path for plugin loader
     *
     * If no $type specified, assumes it is a base path for both filters and
     * validators, and sets each according to the following rules:
     * - decorators: $prefix = $prefix . '_Decorator'
     * - filters: $prefix = $prefix . '_Filter'
     * - validators: $prefix = $prefix . '_Validate'
     *
     * Otherwise, the path prefix is set on the appropriate plugin loader.
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception for invalid type
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::FILTER:
            case self::VALIDATE:
                $loader = $this->getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            case null:
                $prefix = rtrim($prefix, '_');
                $path   = rtrim($path, DIRECTORY_SEPARATOR);
                foreach (array(self::DECORATOR, self::FILTER, self::VALIDATE) as $type) {
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
     * @return Zend_Form_Element
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
                    foreach ($paths as $prefix => $spec) {
                        if (is_array($spec)) {
                            foreach ($spec as $path) {
                                if (!is_string($path)) {
                                    continue;
                                }
                                $this->addPrefixPath($prefix, $path, $type);
                            }
                        } elseif (is_string($spec)) {
                            $this->addPrefixPath($prefix, $spec, $type);
                        }
                    }
                } else {
                    $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
                }
            }
        }
        return $this;
    }

    // Validation

    /**
     * Add validator to validation chain
     *
     * Note: will overwrite existing validators if they are of the same class.
     *
     * @param  string|Zend_Validate_Interface $validator
     * @param  bool $breakChainOnFailure
     * @param  array $options
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception if invalid validator type
     */
    public function addValidator($validator, $breakChainOnFailure = false, $options = array())
    {
        if ($validator instanceof Zend_Validate_Interface) {
            $name = get_class($validator);

            if (!isset($validator->zfBreakChainOnFailure)) {
                $validator->zfBreakChainOnFailure = $breakChainOnFailure;
            }
        } elseif (is_string($validator)) {
            $name      = $validator;
            $validator = array(
                'validator' => $validator,
                'breakChainOnFailure' => $breakChainOnFailure,
                'options'             => $options,
            );
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid validator provided to addValidator; must be string or Zend_Validate_Interface');
        }


        $this->_validators[$name] = $validator;

        return $this;
    }

    /**
     * Add multiple validators
     *
     * @param  array $validators
     * @return Zend_Form_Element
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $validatorInfo) {
            if (is_string($validatorInfo)) {
                $this->addValidator($validatorInfo);
            } elseif ($validatorInfo instanceof Zend_Validate_Interface) {
                $this->addValidator($validatorInfo);
            } elseif (is_array($validatorInfo)) {
                $argc                = count($validatorInfo);
                $breakChainOnFailure = false;
                $options             = array();
                if (isset($validatorInfo['validator'])) {
                    $validator = $validatorInfo['validator'];
                    if (isset($validatorInfo['breakChainOnFailure'])) {
                        $breakChainOnFailure = $validatorInfo['breakChainOnFailure'];
                    }
                    if (isset($validatorInfo['options'])) {
                        $options = $validatorInfo['options'];
                    }
                    $this->addValidator($validator, $breakChainOnFailure, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $validator  = array_shift($validatorInfo);
                        case (2 <= $argc):
                            $breakChainOnFailure = array_shift($validatorInfo);
                        case (3 <= $argc):
                            $options = array_shift($validatorInfo);
                        default:
                            $this->addValidator($validator, $breakChainOnFailure, $options);
                            break;
                    }
                }
            } else {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid validator passed to addValidators()');
            }
        }

        return $this;
    }

    /**
     * Set multiple validators, overwriting previous validators
     *
     * @param  array $validators
     * @return Zend_Form_Element
     */
    public function setValidators(array $validators)
    {
        $this->clearValidators();
        return $this->addValidators($validators);
    }

    /**
     * Retrieve a single validator by name
     *
     * @param  string $name
     * @return Zend_Validate_Interface|false False if not found, validator otherwise
     */
    public function getValidator($name)
    {
        if (!isset($this->_validators[$name])) {
            $len = strlen($name);
            foreach ($this->_validators as $localName => $validator) {
                if ($len > strlen($localName)) {
                    continue;
                }
                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($validator)) {
                        return $this->_loadValidator($validator);
                    }
                    return $validator;
                }
            }
            return false;
        }

        if (is_array($this->_validators[$name])) {
            return $this->_loadValidator($this->_validators[$name]);
        }

        return $this->_validators[$name];
    }

    /**
     * Retrieve all validators
     *
     * @return array
     */
    public function getValidators()
    {
        $validators = array();
        foreach ($this->_validators as $key => $value) {
            if ($value instanceof Zend_Validate_Interface) {
                $validators[$key] = $value;
                continue;
            }
            $validator = $this->_loadValidator($value);
            $validators[get_class($validator)] = $validator;
        }
        return $validators;
    }

    /**
     * Remove a single validator by name
     *
     * @param  string $name
     * @return bool
     */
    public function removeValidator($name)
    {
        if (isset($this->_validators[$name])) {
            unset($this->_validators[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_validators) as $validator) {
                if ($len > strlen($validator)) {
                    continue;
                }
                if (0 === substr_compare($validator, $name, -$len, $len, true)) {
                    unset($this->_validators[$validator]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all validators
     *
     * @return Zend_Form_Element
     */
    public function clearValidators()
    {
        $this->_validators = array();
        return $this;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $value = $this->getValue();

        if ((('' === $value) || (null === $value))
            && !$this->isRequired()
            && $this->getAllowEmpty()
        ) {
            return true;
        }

        if ($this->isRequired()
            && $this->autoInsertNotEmptyValidator()
            && !$this->getValidator('NotEmpty'))
        {
            $validators = $this->getValidators();
            $notEmpty   = array('validator' => 'NotEmpty', 'breakChainOnFailure' => true);
            array_unshift($validators, $notEmpty);
            $this->setValidators($validators);
        }

        // Find the correct translator. Zend_Validate_Abstract::getDefaultTranslator()
        // will get either the static translator attached to Zend_Validate_Abstract
        // or the 'Zend_Translate' from Zend_Registry.
        if (Zend_Validate_Abstract::hasDefaultTranslator() &&
            !Zend_Form::hasDefaultTranslator())
        {
            $translator = Zend_Validate_Abstract::getDefaultTranslator();
            if ($this->hasTranslator()) {
                // only pick up this element's translator if it was attached directly.
                $translator = $this->getTranslator();
            }
        } else {
            $translator = $this->getTranslator();
        }

        $this->_messages = array();
        $this->_errors   = array();
        $result          = true;
        $isArray         = $this->isArray();
        foreach ($this->getValidators() as $key => $validator) {
            if (method_exists($validator, 'setTranslator')) {
                if (method_exists($validator, 'hasTranslator')) {
                    if (!$validator->hasTranslator()) {
                        $validator->setTranslator($translator);
                    }
                } else {
                    $validator->setTranslator($translator);
                }
            }

            if (method_exists($validator, 'setDisableTranslator')) {
                $validator->setDisableTranslator($this->translatorIsDisabled());
            }

            if ($isArray && is_array($value)) {
                $messages = array();
                $errors   = array();
                foreach ($value as $val) {
                    if (!$validator->isValid($val, $context)) {
                        $result = false;
                        if ($this->_hasErrorMessages()) {
                            $messages = $this->_getErrorMessages();
                            $errors   = $messages;
                        } else {
                            $messages = array_merge($messages, $validator->getMessages());
                            $errors   = array_merge($errors,   $validator->getErrors());
                        }
                    }
                }
                if ($result) {
                    continue;
                }
            } elseif ($validator->isValid($value, $context)) {
                continue;
            } else {
                $result = false;
                if ($this->_hasErrorMessages()) {
                    $messages = $this->_getErrorMessages();
                    $errors   = $messages;
                } else {
                    $messages = $validator->getMessages();
                    $errors   = array_keys($messages);
                }
            }

            $result          = false;
            $this->_messages = array_merge($this->_messages, $messages);
            $this->_errors   = array_merge($this->_errors,   $errors);

            if ($validator->zfBreakChainOnFailure) {
                break;
            }
        }

        // If element manually flagged as invalid, return false
        if ($this->_isErrorForced) {
            return false;
        }

        return $result;
    }

    /**
     * Add a custom error message to return in the event of failed validation
     *
     * @param  string $message
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
     */
    public function clearErrorMessages()
    {
        $this->_errorMessages = array();
        return $this;
    }

    /**
     * Get errorMessageSeparator
     *
     * @return string
     */
    public function getErrorMessageSeparator()
    {
        return $this->_errorMessageSeparator;
    }

    /**
     * Set errorMessageSeparator
     *
     * @param  string $separator
     * @return Zend_Form_Element
     */
    public function setErrorMessageSeparator($separator)
    {
        $this->_errorMessageSeparator = $separator;
        return $this;
    }

    /**
     * Mark the element as being in a failed validation state
     *
     * @return Zend_Form_Element
     */
    public function markAsError()
    {
        $messages       = $this->getMessages();
        $customMessages = $this->_getErrorMessages();
        $messages       = $messages + $customMessages;
        if (empty($messages)) {
            $this->_isError = true;
        } else {
            $this->_messages = $messages;
        }
        $this->_isErrorForced = true;
        return $this;
    }

    /**
     * Add an error message and mark element as failed validation
     *
     * @param  string $message
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
     */
    public function setErrors(array $messages)
    {
        $this->clearErrorMessages();
        return $this->addErrors($messages);
    }

    /**
     * Are there errors registered?
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (!empty($this->_messages) || $this->_isError);
    }

    /**
     * Retrieve validator chain errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Retrieve error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }


    // Filtering

    /**
     * Add a filter to the element
     *
     * @param  string|Zend_Filter_Interface $filter
     * @return Zend_Form_Element
     */
    public function addFilter($filter, $options = array())
    {
        if ($filter instanceof Zend_Filter_Interface) {
            $name = get_class($filter);
        } elseif (is_string($filter)) {
            $name = $filter;
            $filter = array(
                'filter'  => $filter,
                'options' => $options,
            );
            $this->_filters[$name] = $filter;
        } else {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid filter provided to addFilter; must be string or Zend_Filter_Interface');
        }

        $this->_filters[$name] = $filter;

        return $this;
    }

    /**
     * Add filters to element
     *
     * @param  array $filters
     * @return Zend_Form_Element
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filterInfo) {
            if (is_string($filterInfo)) {
                $this->addFilter($filterInfo);
            } elseif ($filterInfo instanceof Zend_Filter_Interface) {
                $this->addFilter($filterInfo);
            } elseif (is_array($filterInfo)) {
                $argc                = count($filterInfo);
                $options             = array();
                if (isset($filterInfo['filter'])) {
                    $filter = $filterInfo['filter'];
                    if (isset($filterInfo['options'])) {
                        $options = $filterInfo['options'];
                    }
                    $this->addFilter($filter, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $filter  = array_shift($filterInfo);
                        case (2 <= $argc):
                            $options = array_shift($filterInfo);
                        default:
                            $this->addFilter($filter, $options);
                            break;
                    }
                }
            } else {
                // require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid filter passed to addFilters()');
            }
        }

        return $this;
    }

    /**
     * Add filters to element, overwriting any already existing
     *
     * @param  array $filters
     * @return Zend_Form_Element
     */
    public function setFilters(array $filters)
    {
        $this->clearFilters();
        return $this->addFilters($filters);
    }

    /**
     * Retrieve a single filter by name
     *
     * @param  string $name
     * @return Zend_Filter_Interface
     */
    public function getFilter($name)
    {
        if (!isset($this->_filters[$name])) {
            $len = strlen($name);
            foreach ($this->_filters as $localName => $filter) {
                if ($len > strlen($localName)) {
                    continue;
                }

                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($filter)) {
                        return $this->_loadFilter($filter);
                    }
                    return $filter;
                }
            }
            return false;
        }

        if (is_array($this->_filters[$name])) {
            return $this->_loadFilter($this->_filters[$name]);
        }

        return $this->_filters[$name];
    }

    /**
     * Get all filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        foreach ($this->_filters as $key => $value) {
            if ($value instanceof Zend_Filter_Interface) {
                $filters[$key] = $value;
                continue;
            }
            $filter = $this->_loadFilter($value);
            $filters[get_class($filter)] = $filter;
        }
        return $filters;
    }

    /**
     * Remove a filter by name
     *
     * @param  string $name
     * @return Zend_Form_Element
     */
    public function removeFilter($name)
    {
        if (isset($this->_filters[$name])) {
            unset($this->_filters[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_filters) as $filter) {
                if ($len > strlen($filter)) {
                    continue;
                }
                if (0 === substr_compare($filter, $name, -$len, $len, true)) {
                    unset($this->_filters[$filter]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all filters
     *
     * @return Zend_Form_Element
     */
    public function clearFilters()
    {
        $this->_filters = array();
        return $this;
    }

    // Rendering

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Form_Element
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * Retrieves from ViewRenderer if none previously set.
     *
     * @return null|Zend_View_Interface
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
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
     * @return Zend_Form_Element
     */
    public function removeDecorator($name)
    {
        if (isset($this->_decorators[$name])) {
            unset($this->_decorators[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_decorators) as $decorator) {
                if ($len > strlen($decorator)) {
                    continue;
                }
                if (0 === substr_compare($decorator, $name, -$len, $len, true)) {
                    unset($this->_decorators[$decorator]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all decorators
     *
     * @return Zend_Form_Element
     */
    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }

    /**
     * String representation of form element
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
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    /**
     * Lazy-load a filter
     *
     * @param  array $filter
     * @return Zend_Filter_Interface
     */
    protected function _loadFilter(array $filter)
    {
        $origName = $filter['filter'];
        $name     = $this->getPluginLoader(self::FILTER)->load($filter['filter']);

        if (array_key_exists($name, $this->_filters)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Filter instance already exists for filter "%s"', $origName));
        }

        if (empty($filter['options'])) {
            $instance = new $name;
        } else {
            $r = new ReflectionClass($name);
            if ($r->hasMethod('__construct')) {
                $instance = $r->newInstanceArgs((array) $filter['options']);
            } else {
                $instance = $r->newInstance();
            }
        }

        if ($origName != $name) {
            $filterNames  = array_keys($this->_filters);
            $order        = array_flip($filterNames);
            $order[$name] = $order[$origName];
            $filtersExchange = array();
            unset($order[$origName]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $name) {
                    $filtersExchange[$key] = $instance;
                    continue;
                }
                $filtersExchange[$key] = $this->_filters[$key];
            }
            $this->_filters = $filtersExchange;
        } else {
            $this->_filters[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Lazy-load a validator
     *
     * @param  array $validator Validator definition
     * @return Zend_Validate_Interface
     */
    protected function _loadValidator(array $validator)
    {
        $origName = $validator['validator'];
        $name     = $this->getPluginLoader(self::VALIDATE)->load($validator['validator']);

        if (array_key_exists($name, $this->_validators)) {
            // require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Validator instance already exists for validator "%s"', $origName));
        }

        $messages = false;
        if (isset($validator['options']) && array_key_exists('messages', (array)$validator['options'])) {
            $messages = $validator['options']['messages'];
            unset($validator['options']['messages']);
        }

        if (empty($validator['options'])) {
            $instance = new $name;
        } else {
            $r = new ReflectionClass($name);
            if ($r->hasMethod('__construct')) {
                $numeric = false;
                if (is_array($validator['options'])) {
                    $keys    = array_keys($validator['options']);
                    foreach($keys as $key) {
                        if (is_numeric($key)) {
                            $numeric = true;
                            break;
                        }
                    }
                }

                if ($numeric) {
                    $instance = $r->newInstanceArgs((array) $validator['options']);
                } else {
                    $instance = $r->newInstance($validator['options']);
                }
            } else {
                $instance = $r->newInstance();
            }
        }

        if ($messages) {
            if (is_array($messages)) {
                $instance->setMessages($messages);
            } elseif (is_string($messages)) {
                $instance->setMessage($messages);
            }
        }
        $instance->zfBreakChainOnFailure = $validator['breakChainOnFailure'];

        if ($origName != $name) {
            $validatorNames     = array_keys($this->_validators);
            $order              = array_flip($validatorNames);
            $order[$name]       = $order[$origName];
            $validatorsExchange = array();
            unset($order[$origName]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $name) {
                    $validatorsExchange[$key] = $instance;
                    continue;
                }
                $validatorsExchange[$key] = $this->_validators[$key];
            }
            $this->_validators = $validatorsExchange;
        } else {
            $this->_validators[$name] = $instance;
        }

        return $instance;
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
     * Retrieve error messages and perform translation and value substitution
     *
     * @return array
     */
    protected function _getErrorMessages()
    {
        $translator = $this->getTranslator();
        $messages   = $this->getErrorMessages();
        $value      = $this->getValue();
        foreach ($messages as $key => $message) {
            if (null !== $translator) {
                $message = $translator->translate($message);
            }
            if (($this->isArray() || is_array($value))
                && !empty($value)
            ) {
                $aggregateMessages = array();
                foreach ($value as $val) {
                    $aggregateMessages[] = str_replace('%value%', $val, $message);
                }
                $messages[$key] = implode($this->getErrorMessageSeparator(), $aggregateMessages);
            } else {
                $messages[$key] = str_replace('%value%', $value, $message);
            }
        }
        return $messages;
    }

    /**
     * Are there custom error messages registered?
     *
     * @return bool
     */
    protected function _hasErrorMessages()
    {
        return !empty($this->_errorMessages);
    }
}
