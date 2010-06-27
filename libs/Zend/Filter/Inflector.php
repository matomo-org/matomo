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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Inflector.php 21372 2010-03-07 19:58:08Z thomas $
 */

/**
 * @see Zend_Filter
 * @see Zend_Filter_Interface
 */
// require_once 'Zend/Filter.php';

/**
 * @see Zend_Loader_PluginLoader
 */
// require_once 'Zend/Loader/PluginLoader.php';

/**
 * Filter chain for string inflection
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Inflector implements Zend_Filter_Interface
{
    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected $_pluginLoader = null;

    /**
     * @var string
     */
    protected $_target = null;

    /**
     * @var bool
     */
    protected $_throwTargetExceptionsOn = true;

    /**
     * @var string
     */
    protected $_targetReplacementIdentifier = ':';

    /**
     * @var array
     */
    protected $_rules = array();

    /**
     * Constructor
     *
     * @param string|array $options Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();

            if (!empty($options)) {
                $temp['target'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['rules'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['throwTargetExceptionsOn'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['targetReplacementIdentifier'] = array_shift($options);
            }

            $options = $temp;
        }

        $this->setOptions($options);
    }

    /**
     * Retreive PluginLoader
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if (!$this->_pluginLoader instanceof Zend_Loader_PluginLoader_Interface) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array('Zend_Filter_' => 'Zend/Filter/'), __CLASS__);
        }

        return $this->_pluginLoader;
    }

    /**
     * Set PluginLoader
     *
     * @param Zend_Loader_PluginLoader_Interface $pluginLoader
     * @return Zend_Filter_Inflector
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $pluginLoader)
    {
        $this->_pluginLoader = $pluginLoader;
        return $this;
    }

    /**
     * Use Zend_Config object to set object state
     *
     * @deprecated Use setOptions() instead
     * @param  Zend_Config $config
     * @return Zend_Filter_Inflector
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config);
    }

    /**
     * Set options
     *
     * @param  array $options
     * @return Zend_Filter_Inflector
     */
    public function setOptions($options) {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        // Set Präfix Path
        if (array_key_exists('filterPrefixPath', $options)) {
            if (!is_scalar($options['filterPrefixPath'])) {
                foreach ($options['filterPrefixPath'] as $prefix => $path) {
                    $this->addFilterPrefixPath($prefix, $path);
                }
            }
        }

        if (array_key_exists('throwTargetExceptionsOn', $options)) {
            $this->setThrowTargetExceptionsOn($options['throwTargetExceptionsOn']);
        }

        if (array_key_exists('targetReplacementIdentifier', $options)) {
            $this->setTargetReplacementIdentifier($options['targetReplacementIdentifier']);
        }

        if (array_key_exists('target', $options)) {
            $this->setTarget($options['target']);
        }

        if (array_key_exists('rules', $options)) {
            $this->addRules($options['rules']);
        }

        return $this;
    }

    /**
     * Convienence method to add prefix and path to PluginLoader
     *
     * @param string $prefix
     * @param string $path
     * @return Zend_Filter_Inflector
     */
    public function addFilterPrefixPath($prefix, $path)
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * Set Whether or not the inflector should throw an exception when a replacement
     * identifier is still found within an inflected target.
     *
     * @param bool $throwTargetExceptions
     * @return Zend_Filter_Inflector
     */
    public function setThrowTargetExceptionsOn($throwTargetExceptionsOn)
    {
        $this->_throwTargetExceptionsOn = ($throwTargetExceptionsOn == true) ? true : false;
        return $this;
    }

    /**
     * Will exceptions be thrown?
     *
     * @return bool
     */
    public function isThrowTargetExceptionsOn()
    {
        return $this->_throwTargetExceptionsOn;
    }

    /**
     * Set the Target Replacement Identifier, by default ':'
     *
     * @param string $targetReplacementIdentifier
     * @return Zend_Filter_Inflector
     */
    public function setTargetReplacementIdentifier($targetReplacementIdentifier)
    {
        if ($targetReplacementIdentifier) {
            $this->_targetReplacementIdentifier = (string) $targetReplacementIdentifier;
        }

        return $this;
    }

    /**
     * Get Target Replacement Identifier
     *
     * @return string
     */
    public function getTargetReplacementIdentifier()
    {
        return $this->_targetReplacementIdentifier;
    }

    /**
     * Set a Target
     * ex: 'scripts/:controller/:action.:suffix'
     *
     * @param string
     * @return Zend_Filter_Inflector
     */
    public function setTarget($target)
    {
        $this->_target = (string) $target;
        return $this;
    }

    /**
     * Retrieve target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Set Target Reference
     *
     * @param reference $target
     * @return Zend_Filter_Inflector
     */
    public function setTargetReference(&$target)
    {
        $this->_target =& $target;
        return $this;
    }

    /**
     * SetRules() is the same as calling addRules() with the exception that it
     * clears the rules before adding them.
     *
     * @param array $rules
     * @return Zend_Filter_Inflector
     */
    public function setRules(Array $rules)
    {
        $this->clearRules();
        $this->addRules($rules);
        return $this;
    }

    /**
     * AddRules(): multi-call to setting filter rules.
     *
     * If prefixed with a ":" (colon), a filter rule will be added.  If not
     * prefixed, a static replacement will be added.
     *
     * ex:
     * array(
     *     ':controller' => array('CamelCaseToUnderscore','StringToLower'),
     *     ':action'     => array('CamelCaseToUnderscore','StringToLower'),
     *     'suffix'      => 'phtml'
     *     );
     *
     * @param array
     * @return Zend_Filter_Inflector
     */
    public function addRules(Array $rules)
    {
        $keys = array_keys($rules);
        foreach ($keys as $spec) {
            if ($spec[0] == ':') {
                $this->addFilterRule($spec, $rules[$spec]);
            } else {
                $this->setStaticRule($spec, $rules[$spec]);
            }
        }

        return $this;
    }

    /**
     * Get rules
     *
     * By default, returns all rules. If a $spec is provided, will return those
     * rules if found, false otherwise.
     *
     * @param  string $spec
     * @return array|false
     */
    public function getRules($spec = null)
    {
        if (null !== $spec) {
            $spec = $this->_normalizeSpec($spec);
            if (isset($this->_rules[$spec])) {
                return $this->_rules[$spec];
            }
            return false;
        }

        return $this->_rules;
    }

    /**
     * getRule() returns a rule set by setFilterRule(), a numeric index must be provided
     *
     * @param string $spec
     * @param int $index
     * @return Zend_Filter_Interface|false
     */
    public function getRule($spec, $index)
    {
        $spec = $this->_normalizeSpec($spec);
        if (isset($this->_rules[$spec]) && is_array($this->_rules[$spec])) {
            if (isset($this->_rules[$spec][$index])) {
                return $this->_rules[$spec][$index];
            }
        }
        return false;
    }

    /**
     * ClearRules() clears the rules currently in the inflector
     *
     * @return Zend_Filter_Inflector
     */
    public function clearRules()
    {
        $this->_rules = array();
        return $this;
    }

    /**
     * Set a filtering rule for a spec.  $ruleSet can be a string, Filter object
     * or an array of strings or filter objects.
     *
     * @param string $spec
     * @param array|string|Zend_Filter_Interface $ruleSet
     * @return Zend_Filter_Inflector
     */
    public function setFilterRule($spec, $ruleSet)
    {
        $spec = $this->_normalizeSpec($spec);
        $this->_rules[$spec] = array();
        return $this->addFilterRule($spec, $ruleSet);
    }

    /**
     * Add a filter rule for a spec
     *
     * @param mixed $spec
     * @param mixed $ruleSet
     * @return void
     */
    public function addFilterRule($spec, $ruleSet)
    {
        $spec = $this->_normalizeSpec($spec);
        if (!isset($this->_rules[$spec])) {
            $this->_rules[$spec] = array();
        }

        if (!is_array($ruleSet)) {
            $ruleSet = array($ruleSet);
        }

        if (is_string($this->_rules[$spec])) {
            $temp = $this->_rules[$spec];
            $this->_rules[$spec] = array();
            $this->_rules[$spec][] = $temp;
        }

        foreach ($ruleSet as $rule) {
            $this->_rules[$spec][] = $this->_getRule($rule);
        }

        return $this;
    }

    /**
     * Set a static rule for a spec.  This is a single string value
     *
     * @param string $name
     * @param string $value
     * @return Zend_Filter_Inflector
     */
    public function setStaticRule($name, $value)
    {
        $name = $this->_normalizeSpec($name);
        $this->_rules[$name] = (string) $value;
        return $this;
    }

    /**
     * Set Static Rule Reference.
     *
     * This allows a consuming class to pass a property or variable
     * in to be referenced when its time to build the output string from the
     * target.
     *
     * @param string $name
     * @param mixed $reference
     * @return Zend_Filter_Inflector
     */
    public function setStaticRuleReference($name, &$reference)
    {
        $name = $this->_normalizeSpec($name);
        $this->_rules[$name] =& $reference;
        return $this;
    }

    /**
     * Inflect
     *
     * @param  string|array $source
     * @return string
     */
    public function filter($source)
    {
        // clean source
        foreach ( (array) $source as $sourceName => $sourceValue) {
            $source[ltrim($sourceName, ':')] = $sourceValue;
        }

        $pregQuotedTargetReplacementIdentifier = preg_quote($this->_targetReplacementIdentifier, '#');
        $processedParts = array();

        foreach ($this->_rules as $ruleName => $ruleValue) {
            if (isset($source[$ruleName])) {
                if (is_string($ruleValue)) {
                    // overriding the set rule
                    $processedParts['#'.$pregQuotedTargetReplacementIdentifier.$ruleName.'#'] = str_replace('\\', '\\\\', $source[$ruleName]);
                } elseif (is_array($ruleValue)) {
                    $processedPart = $source[$ruleName];
                    foreach ($ruleValue as $ruleFilter) {
                        $processedPart = $ruleFilter->filter($processedPart);
                    }
                    $processedParts['#'.$pregQuotedTargetReplacementIdentifier.$ruleName.'#'] = str_replace('\\', '\\\\', $processedPart);
                }
            } elseif (is_string($ruleValue)) {
                $processedParts['#'.$pregQuotedTargetReplacementIdentifier.$ruleName.'#'] = str_replace('\\', '\\\\', $ruleValue);
            }
        }

        // all of the values of processedParts would have been str_replace('\\', '\\\\', ..)'d to disable preg_replace backreferences
        $inflectedTarget = preg_replace(array_keys($processedParts), array_values($processedParts), $this->_target);

        if ($this->_throwTargetExceptionsOn && (preg_match('#(?='.$pregQuotedTargetReplacementIdentifier.'[A-Za-z]{1})#', $inflectedTarget) == true)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A replacement identifier ' . $this->_targetReplacementIdentifier . ' was found inside the inflected target, perhaps a rule was not satisfied with a target source?  Unsatisfied inflected target: ' . $inflectedTarget);
        }

        return $inflectedTarget;
    }

    /**
     * Normalize spec string
     *
     * @param  string $spec
     * @return string
     */
    protected function _normalizeSpec($spec)
    {
        return ltrim((string) $spec, ':&');
    }

    /**
     * Resolve named filters and convert them to filter objects.
     *
     * @param  string $rule
     * @return Zend_Filter_Interface
     */
    protected function _getRule($rule)
    {
        if ($rule instanceof Zend_Filter_Interface) {
            return $rule;
        }

        $rule = (string) $rule;

        $className  = $this->getPluginLoader()->load($rule);
        $ruleObject = new $className();
        if (!$ruleObject instanceof Zend_Filter_Interface) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('No class named ' . $rule . ' implementing Zend_Filter_Interface could be found');
        }

        return $ruleObject;
    }
}
