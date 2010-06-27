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
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Captcha.php 22329 2010-05-30 15:12:58Z bittarman $
 */

/** @see Zend_Form_Element_Xhtml */
// require_once 'Zend/Form/Element/Xhtml.php';

/** @see Zend_Captcha_Adapter */
// require_once 'Zend/Captcha/Adapter.php';

/**
 * Generic captcha element
 *
 * This element allows to insert CAPTCHA into the form in order
 * to validate that human is submitting the form. The actual
 * logic is contained in the captcha adapter.
 *
 * @see http://en.wikipedia.org/wiki/Captcha
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Form_Element_Captcha extends Zend_Form_Element_Xhtml
{
    /**
     * Captcha plugin type constant
     */
    const CAPTCHA = 'CAPTCHA';

    /**
     * Captcha adapter
     *
     * @var Zend_Captcha_Adapter
     */
    protected $_captcha;

    /**
     * Get captcha adapter
     *
     * @return Zend_Captcha_Adapter
     */
    public function getCaptcha()
    {
        return $this->_captcha;
    }

    /**
     * Set captcha adapter
     *
     * @param string|array|Zend_Captcha_Adapter $captcha
     * @param array $options
     */
    public function setCaptcha($captcha, $options = array())
    {
        if ($captcha instanceof Zend_Captcha_Adapter) {
            $instance = $captcha;
        } else {
            if (is_array($captcha)) {
                if (array_key_exists('captcha', $captcha)) {
                    $name = $captcha['captcha'];
                    unset($captcha['captcha']);
                } else {
                    $name = array_shift($captcha);
                }
                $options = array_merge($options, $captcha);
            } else {
                $name = $captcha;
            }

            $name = $this->getPluginLoader(self::CAPTCHA)->load($name);
            if (empty($options)) {
                $instance = new $name;
            } else {
                $r = new ReflectionClass($name);
                if ($r->hasMethod('__construct')) {
                    $instance = $r->newInstanceArgs(array($options));
                } else {
                    $instance = $r->newInstance();
                }
            }
        }

        $this->_captcha = $instance;
        $this->_captcha->setName($this->getName());
        return $this;
    }

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  string|array|Zend_Config $spec
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);
        $this->setAllowEmpty(true)
             ->setRequired(true)
             ->setAutoInsertNotEmptyValidator(false)
             ->addValidator($this->getCaptcha(), true);
    }

    /**
     * Return all attributes
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = get_object_vars($this);
        unset($attribs['helper']);
        foreach ($attribs as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Set options
     *
     * Overrides to allow passing captcha options
     *
     * @param  array $options
     * @return Zend_Form_Element_Captcha
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('captcha', $options)) {
            if (array_key_exists('captchaOptions', $options)) {
                $this->setCaptcha($options['captcha'], $options['captchaOptions']);
                unset($options['captchaOptions']);
            } else {
                $this->setCaptcha($options['captcha']);
            }
            unset($options['captcha']);
        }
        parent::setOptions($options);
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
        $captcha    = $this->getCaptcha();
        $captcha->setName($this->getFullyQualifiedName());

        $decorators = $this->getDecorators();

        $decorator  = $captcha->getDecorator();
        if (!empty($decorator)) {
            array_unshift($decorators, $decorator);
        }

        $decorator = array('Captcha', array('captcha' => $captcha));
        array_unshift($decorators, $decorator);

        $this->setDecorators($decorators);

        $this->setValue($this->getCaptcha()->generate());

        return parent::render($view);
    }

    /**
     * Retrieve plugin loader for validator or filter chain
     *
     * Support for plugin loader for Captcha adapters
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     * @throws Zend_Loader_Exception on invalid type.
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);
        if ($type == self::CAPTCHA) {
            if (!isset($this->_loaders[$type])) {
                // require_once 'Zend/Loader/PluginLoader.php';
                $this->_loaders[$type] = new Zend_Loader_PluginLoader(
                    array('Zend_Captcha' => 'Zend/Captcha/')
                );
            }
            return $this->_loaders[$type];
        } else {
            return parent::getPluginLoader($type);
        }
    }

    /**
     * Add prefix path for plugin loader for captcha adapters
     *
     * This method handles the captcha type, the rest is handled by
     * the parent
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form_Element
     * @see Zend_Form_Element::addPrefixPath
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        switch ($type) {
            case null:
                $loader = $this->getPluginLoader(self::CAPTCHA);
                $cPrefix = rtrim($prefix, '_') . '_Captcha';
                $cPath   = rtrim($path, '/\\') . '/Captcha';
                $loader->addPrefixPath($cPrefix, $cPath);
                return parent::addPrefixPath($prefix, $path);
            case self::CAPTCHA:
                $loader = $this->getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            default:
                return parent::addPrefixPath($prefix, $path, $type);
        }
    }

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
            $this->addDecorator('Errors')
                 ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
                 ->addDecorator('HtmlTag', array('tag' => 'dd'))
                 ->addDecorator('Label', array('tag' => 'dt'));
        }
        return $this;
    }

    /**
     * Is the captcha valid?
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->getCaptcha()->setName($this->getName());
        $belongsTo = $this->getBelongsTo();
        if (empty($belongsTo) || !is_array($context)) {
            return parent::isValid($value, $context);
        }

        $name     = $this->getFullyQualifiedName();
        $root     = substr($name, 0, strpos($name, '['));
        $segments = substr($name, strpos($name, '['));
        $segments = ltrim($segments, '[');
        $segments = rtrim($segments, ']');
        $segments = explode('][', $segments);
        array_unshift($segments, $root);
        array_pop($segments);
        $newContext = $context;
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $newContext)) {
                $newContext = $newContext[$segment];
            }
        }

        return parent::isValid($value, $newContext);
    }
}
