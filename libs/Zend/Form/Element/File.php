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

/** Zend_Form_Element_Xhtml */
// require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Zend_Form_Element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 22372 2010-06-04 20:17:58Z thomas $
 */
class Zend_Form_Element_File extends Zend_Form_Element_Xhtml
{
    /**
     * Plugin loader type
     */
    const TRANSFER_ADAPTER = 'TRANSFER_ADAPTER';

    /**
     * @var string Default view helper
     */
    public $helper = 'formFile';

    /**
     * @var Zend_File_Transfer_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * @var boolean Already validated ?
     */
    protected $_validated = false;

    /**
     * @var boolean Disable value to be equal to file content
     */
    protected $_valueDisabled = false;

    /**
     * @var integer Internal multifile counter
     */
    protected $_counter = 1;

    /**
     * @var integer Maximum file size for MAX_FILE_SIZE attribut of form
     */
    protected static $_maxFileSize = -1;

    /**
     * Load default decorators
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
            $this->addDecorator('File')
                 ->addDecorator('Errors')
                 ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
                 ->addDecorator('HtmlTag', array('tag' => 'dd'))
                 ->addDecorator('Label', array('tag' => 'dt'));
        }
        return $this;
    }

    /**
     * Set plugin loader
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @param  string $type
     * @return Zend_Form_Element_File
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type)
    {
        $type = strtoupper($type);

        if ($type != self::TRANSFER_ADAPTER) {
            return parent::setPluginLoader($loader, $type);
        }

        $this->_loaders[$type] = $loader;
        return $this;
    }

    /**
     * Get Plugin Loader
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);

        if ($type != self::TRANSFER_ADAPTER) {
            return parent::getPluginLoader($type);
        }

        if (!array_key_exists($type, $this->_loaders)) {
            // require_once 'Zend/Loader/PluginLoader.php';
            $loader = new Zend_Loader_PluginLoader(array(
                'Zend_File_Transfer_Adapter' => 'Zend/File/Transfer/Adapter/',
            ));
            $this->setPluginLoader($loader, self::TRANSFER_ADAPTER);
        }

        return $this->_loaders[$type];
    }

    /**
     * Add prefix path for plugin loader
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form_Element_File
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        if (!empty($type) && ($type != self::TRANSFER_ADAPTER)) {
            return parent::addPrefixPath($prefix, $path, $type);
        }

        if (empty($type)) {
            $pluginPrefix = rtrim($prefix, '_') . '_Transfer_Adapter';
            $pluginPath   = rtrim($path, DIRECTORY_SEPARATOR) . '/Transfer/Adapter/';
            $loader       = $this->getPluginLoader(self::TRANSFER_ADAPTER);
            $loader->addPrefixPath($pluginPrefix, $pluginPath);
            return parent::addPrefixPath($prefix, $path, null);
        }

        $loader = $this->getPluginLoader($type);
        $loader->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * Set transfer adapter
     *
     * @param  string|Zend_File_Transfer_Adapter_Abstract $adapter
     * @return Zend_Form_Element_File
     */
    public function setTransferAdapter($adapter)
    {
        if ($adapter instanceof Zend_File_Transfer_Adapter_Abstract) {
            $this->_adapter = $adapter;
        } elseif (is_string($adapter)) {
            $loader = $this->getPluginLoader(self::TRANSFER_ADAPTER);
            $class  = $loader->load($adapter);
            $this->_adapter = new $class;
        } else {
            // require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid adapter specified');
        }

        foreach (array('filter', 'validate') as $type) {
            $loader = $this->getPluginLoader($type);
            $this->_adapter->setPluginLoader($loader, $type);
        }

        return $this;
    }

    /**
     * Get transfer adapter
     *
     * Lazy loads HTTP transfer adapter when no adapter registered.
     *
     * @return Zend_File_Transfer_Adapter_Abstract
     */
    public function getTransferAdapter()
    {
        if (null === $this->_adapter) {
            $this->setTransferAdapter('Http');
        }
        return $this->_adapter;
    }

    /**
     * Add Validator; proxy to adapter
     *
     * @param  string|Zend_Validate_Interface $validator
     * @param  bool $breakChainOnFailure
     * @param  mixed $options
     * @return Zend_Form_Element_File
     */
    public function addValidator($validator, $breakChainOnFailure = false, $options = array())
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addValidator($validator, $breakChainOnFailure, $options, $this->getName());
        $this->_validated = false;

        return $this;
    }

    /**
     * Add multiple validators at once; proxy to adapter
     *
     * @param  array $validators
     * @return Zend_Form_Element_File
     */
    public function addValidators(array $validators)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addValidators($validators, $this->getName());
        $this->_validated = false;

        return $this;
    }

    /**
     * Add multiple validators at once, overwriting; proxy to adapter
     *
     * @param  array $validators
     * @return Zend_Form_Element_File
     */
    public function setValidators(array $validators)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->setValidators($validators, $this->getName());
        $this->_validated = false;

        return $this;
    }

    /**
     * Retrieve validator by name; proxy to adapter
     *
     * @param  string $name
     * @return Zend_Validate_Interface|null
     */
    public function getValidator($name)
    {
        $adapter    = $this->getTransferAdapter();
        return $adapter->getValidator($name);
    }

    /**
     * Retrieve all validators; proxy to adapter
     *
     * @return array
     */
    public function getValidators()
    {
        $adapter = $this->getTransferAdapter();
        $validators = $adapter->getValidators($this->getName());
        if ($validators === null) {
            $validators = array();
        }

        return $validators;
    }

    /**
     * Remove validator by name; proxy to adapter
     *
     * @param  string $name
     * @return Zend_Form_Element_File
     */
    public function removeValidator($name)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->removeValidator($name);
        $this->_validated = false;

        return $this;
    }

    /**
     * Remove all validators; proxy to adapter
     *
     * @return Zend_Form_Element_File
     */
    public function clearValidators()
    {
        $adapter = $this->getTransferAdapter();
        $adapter->clearValidators();
        $this->_validated = false;

        return $this;
    }

    /**
     * Add Filter; proxy to adapter
     *
     * @param  string|array $filter  Type of filter to add
     * @param  string|array $options Options to set for the filter
     * @return Zend_Form_Element_File
     */
    public function addFilter($filter, $options = null)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addFilter($filter, $options, $this->getName());

        return $this;
    }

    /**
     * Add Multiple filters at once; proxy to adapter
     *
     * @param  array $filters
     * @return Zend_Form_Element_File
     */
    public function addFilters(array $filters)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addFilters($filters, $this->getName());

        return $this;
    }

    /**
     * Sets a filter for the class, erasing all previous set; proxy to adapter
     *
     * @param  string|array $filter Filter to set
     * @return Zend_Form_Element_File
     */
    public function setFilters(array $filters)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->setFilters($filters, $this->getName());

        return $this;
    }

    /**
     * Retrieve individual filter; proxy to adapter
     *
     * @param  string $name
     * @return Zend_Filter_Interface|null
     */
    public function getFilter($name)
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getFilter($name);
    }

    /**
     * Returns all set filters; proxy to adapter
     *
     * @return array List of set filters
     */
    public function getFilters()
    {
        $adapter = $this->getTransferAdapter();
        $filters = $adapter->getFilters($this->getName());

        if ($filters === null) {
            $filters = array();
        }
        return $filters;
    }

    /**
     * Remove an individual filter; proxy to adapter
     *
     * @param  string $name
     * @return Zend_Form_Element_File
     */
    public function removeFilter($name)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->removeFilter($name);

        return $this;
    }

    /**
     * Remove all filters; proxy to adapter
     *
     * @return Zend_Form_Element_File
     */
    public function clearFilters()
    {
        $adapter = $this->getTransferAdapter();
        $adapter->clearFilters();

        return $this;
    }

    /**
     * Validate upload
     *
     * @param  string $value   File, can be optional, give null to validate all files
     * @param  mixed  $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if ($this->_validated) {
            return true;
        }

        $adapter    = $this->getTransferAdapter();
        $translator = $this->getTranslator();
        if ($translator !== null) {
            $adapter->setTranslator($translator);
        }

        if (!$this->isRequired()) {
            $adapter->setOptions(array('ignoreNoFile' => true), $this->getName());
        } else {
            $adapter->setOptions(array('ignoreNoFile' => false), $this->getName());
            if ($this->autoInsertNotEmptyValidator() && !$this->getValidator('NotEmpty')) {
                $this->addValidator = array('validator' => 'NotEmpty', 'breakChainOnFailure' => true);
            }
        }

        if($adapter->isValid($this->getName())) {
            $this->_validated = true;
            return true;
        }

        $this->_validated = false;
        return false;
    }

    /**
     * Receive the uploaded file
     *
     * @return boolean
     */
    public function receive()
    {
        if (!$this->_validated) {
            if (!$this->isValid($this->getName())) {
                return false;
            }
        }

        $adapter = $this->getTransferAdapter();
        if ($adapter->receive($this->getName())) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve error codes; proxy to transfer adapter
     *
     * @return array
     */
    public function getErrors()
    {
        return parent::getErrors() + $this->getTransferAdapter()->getErrors();
    }

    /**
     * Are there errors registered?
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (parent::hasErrors() || $this->getTransferAdapter()->hasErrors());
    }

    /**
     * Retrieve error messages; proxy to transfer adapter
     *
     * @return array
     */
    public function getMessages()
    {
        return parent::getMessages() + $this->getTransferAdapter()->getMessages();
    }

    /**
     * Set the upload destination
     *
     * @param  string $path
     * @return Zend_Form_Element_File
     */
    public function setDestination($path)
    {
        $this->getTransferAdapter()->setDestination($path, $this->getName());
        return $this;
    }

    /**
     * Get the upload destination
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->getTransferAdapter()->getDestination($this->getName());
    }

    /**
     * Get the final filename
     *
     * @param  string  $value (Optional) Element or file to return
     * @param  boolean $path  (Optional) Return also the path, defaults to true
     * @return string
     */
    public function getFileName($value = null, $path = true)
    {
        if (empty($value)) {
            $value = $this->getName();
        }

        return $this->getTransferAdapter()->getFileName($value, $path);
    }

    /**
     * Get internal file informations
     *
     * @param  string $value (Optional) Element or file to return
     * @return array
     */
    public function getFileInfo($value = null)
    {
        if (empty($value)) {
            $value = $this->getName();
        }

        return $this->getTransferAdapter()->getFileInfo($value);
    }

    /**
     * Set a multifile element
     *
     * @param integer $count Number of file elements
     * @return Zend_Form_Element_File Provides fluent interface
     */
    public function setMultiFile($count)
    {
        if ((integer) $count < 2) {
            $this->setIsArray(false);
            $this->_counter = 1;
        } else {
            $this->setIsArray(true);
            $this->_counter = (integer) $count;
        }

        return $this;
    }

    /**
     * Returns the multifile element number
     *
     * @return integer
     */
    public function getMultiFile()
    {
        return $this->_counter;
    }

    /**
     * Sets the maximum file size of the form
     *
     * @return integer
     */
    public function getMaxFileSize()
    {
        if (self::$_maxFileSize < 0) {
            $ini = $this->_convertIniToInteger(trim(ini_get('post_max_size')));
            $max = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));
            $min = max($ini, $max);
            if ($ini > 0) {
                $min = min($min, $ini);
            }

            if ($max > 0) {
                $min = min($min, $max);
            }

            self::$_maxFileSize = $min;
        }

        return self::$_maxFileSize;
    }

    /**
     * Sets the maximum file size of the form
     *
     * @param  integer $size
     * @return integer
     */
    public function setMaxFileSize($size)
    {
        $ini = $this->_convertIniToInteger(trim(ini_get('post_max_size')));
        $max = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));

        if (($max > -1) && ($size > $max)) {
            trigger_error("Your 'upload_max_filesize' config setting limits the maximum filesize to '$max'. You tried to set '$size'.", E_USER_NOTICE);
            $size = $max;
        }

        if (($ini > -1) && ($size > $ini)) {
            trigger_error("Your 'post_max_size' config setting limits the maximum filesize to '$ini'. You tried to set '$size'.", E_USER_NOTICE);
            $size = $ini;
        }

        self::$_maxFileSize = $size;
        return $this;
    }

    /**
     * Converts a ini setting to a integer value
     *
     * @param  string $setting
     * @return integer
     */
    private function _convertIniToInteger($setting)
    {
        if (!is_numeric($setting)) {
            $type = strtoupper(substr($setting, -1));
            $setting = (integer) substr($setting, 0, -1);

            switch ($type) {
                case 'K' :
                    $setting *= 1024;
                    break;

                case 'M' :
                    $setting *= 1024 * 1024;
                    break;

                case 'G' :
                    $setting *= 1024 * 1024 * 1024;
                    break;

                default :
                    break;
            }
        }

        return (integer) $setting;
    }

    /**
     * Set if the file will be uploaded when getting the value
     * This defaults to false which will force receive() when calling getValues()
     *
     * @param boolean $flag Sets if the file is handled as the elements value
     * @return Zend_Form_Element_File
     */
    public function setValueDisabled($flag)
    {
        $this->_valueDisabled = (bool) $flag;
        return $this;
    }

    /**
     * Returns if the file will be uploaded when calling getValues()
     *
     * @return boolean Receive the file on calling getValues()?
     */
    public function isValueDisabled()
    {
        return $this->_valueDisabled;
    }

    /**
     * Processes the file, returns null or the filename only
     * For the complete path, use getFileName
     *
     * @return null|string
     */
    public function getValue()
    {
        if ($this->_value !== null) {
            return $this->_value;
        }

        $content = $this->getTransferAdapter()->getFileName($this->getName());
        if (empty($content)) {
            return null;
        }

        if (!$this->isValid(null)) {
            return null;
        }

        if (!$this->_valueDisabled && !$this->receive()) {
            return null;
        }

        return $this->getFileName(null, false);
    }

    /**
     * Disallow setting the value
     *
     * @param  mixed $value
     * @return Zend_Form_Element_File
     */
    public function setValue($value)
    {
        return $this;
    }

    /**
     * Set translator object for localization
     *
     * @param  Zend_Translate|null $translator
     * @return Zend_Form_Element_File
     */
    public function setTranslator($translator = null)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->setTranslator($translator);
        parent::setTranslator($translator);

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

        $translator = $this->getTransferAdapter()->getTranslator();
        if (null === $translator) {
            // require_once 'Zend/Form.php';
            return Zend_Form::getDefaultTranslator();
        }

        return $translator;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form_Element_File
     */
    public function setDisableTranslator($flag)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->setDisableTranslator($flag);
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
        $adapter = $this->getTransferAdapter();
        return $adapter->translatorIsDisabled();
    }

    /**
     * Was the file received?
     *
     * @return bool
     */
    public function isReceived()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->isReceived($this->getName());
    }

    /**
     * Was the file uploaded?
     *
     * @return bool
     */
    public function isUploaded()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->isUploaded($this->getName());
    }

    /**
     * Has the file been filtered?
     *
     * @return bool
     */
    public function isFiltered()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->isFiltered($this->getName());
    }

    /**
     * Returns the hash for this file element
     *
     * @param string $hash (Optional) Hash algorithm to use
     * @return string|array Hashstring
     */
    public function getHash($hash = 'crc32')
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getHash($hash, $this->getName());
    }

    /**
     * Returns the filesize for this file element
     *
     * @return string|array Filesize
     */
    public function getFileSize()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getFileSize($this->getName());
    }

    /**
     * Returns the mimetype for this file element
     *
     * @return string|array Mimetype
     */
    public function getMimeType()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getMimeType($this->getName());
    }

    /**
     * Render form element
     * Checks for decorator interface to prevent errors
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $marker = false;
        foreach ($this->getDecorators() as $decorator) {
            if ($decorator instanceof Zend_Form_Decorator_Marker_File_Interface) {
                $marker = true;
            }
        }

        if (!$marker) {
            // require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('No file decorator found... unable to render file element');
        }

        return parent::render($view);
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
        $value      = $this->getFileName();
        foreach ($messages as $key => $message) {
            if (null !== $translator) {
                $message = $translator->translate($message);
            }

            if ($this->isArray() || is_array($value)) {
                $aggregateMessages = array();
                foreach ($value as $val) {
                    $aggregateMessages[] = str_replace('%value%', $val, $message);
                }

                if (!empty($aggregateMessages)) {
                    $messages[$key] = $aggregateMessages;
                }
            } else {
                $messages[$key] = str_replace('%value%', $value, $message);
            }
        }

        return $messages;
    }
}
