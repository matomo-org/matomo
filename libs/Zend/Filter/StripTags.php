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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: StripTags.php 5417 2007-06-22 19:24:07Z darby $
 */


/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_StripTags implements Zend_Filter_Interface
{
    /**
     * Unique ID prefix used for allowing comments
     */
    const UNIQUE_ID_PREFIX = '__Zend_Filter_StripTags__';

    /**
     * Whether comments are allowed
     *
     * If false (the default), then comments are removed from the input string.
     *
     * @var boolean
     */
    public $commentsAllowed;

    /**
     * Array of allowed tags and allowed attributes for each allowed tag
     *
     * Tags are stored in the array keys, and the array values are themselves
     * arrays of the attributes allowed for the corresponding tag.
     *
     * @var array
     */
    protected $_tagsAllowed = array();

    /**
     * Array of allowed attributes for all allowed tags
     *
     * Attributes stored here are allowed for all of the allowed tags.
     *
     * @var array
     */
    protected $_attributesAllowed = array();

    /**
     * Sets the filter options
     *
     * @param  array|string $tagsAllowed
     * @param  array|string $attributesAllowed
     * @param  boolean      $allowComments
     * @return void
     */
    public function __construct($tagsAllowed = null, $attributesAllowed = null, $commentsAllowed = false)
    {
        $this->setTagsAllowed($tagsAllowed);
        $this->setAttributesAllowed($attributesAllowed);
        $this->commentsAllowed = (boolean) $commentsAllowed;
    }

    /**
     * Returns the tagsAllowed option
     *
     * @return array
     */
    public function getTagsAllowed()
    {
        return $this->_tagsAllowed;
    }

    /**
     * Sets the tagsAllowed option
     *
     * @param  array|string $tagsAllowed
     * @return Zend_Filter_StripTags Provides a fluent interface
     */
    public function setTagsAllowed($tagsAllowed)
    {
        if (!is_array($tagsAllowed)) {
            $tagsAllowed = array($tagsAllowed);
        }

        foreach ($tagsAllowed as $index => $element) {
            // If the tag was provided without attributes
            if (is_int($index) && is_string($element)) {
                // Canonicalize the tag name
                $tagName = strtolower($element);
                // Store the tag as allowed with no attributes
                $this->_tagsAllowed[$tagName] = array();
            }
            // Otherwise, if a tag was provided with attributes
            else if (is_string($index) && (is_array($element) || is_string($element))) {
                // Canonicalize the tag name
                $tagName = strtolower($index);
                // Canonicalize the attributes
                if (is_string($element)) {
                    $element = array($element);
                }
                // Store the tag as allowed with the provided attributes
                $this->_tagsAllowed[$tagName] = array();
                foreach ($element as $attribute) {
                    if (is_string($attribute)) {
                        // Canonicalize the attribute name
                        $attributeName = strtolower($attribute);
                        $this->_tagsAllowed[$tagName][$attributeName] = null;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Returns the attributesAllowed option
     *
     * @return array
     */
    public function getAttributesAllowed()
    {
        return $this->_attributesAllowed;
    }

    /**
     * Sets the attributesAllowed option
     *
     * @param  array|string $attributesAllowed
     * @return Zend_Filter_StripTags Provides a fluent interface
     */
    public function setAttributesAllowed($attributesAllowed)
    {
        if (!is_array($attributesAllowed)) {
            $attributesAllowed = array($attributesAllowed);
        }

        // Store each attribute as allowed
        foreach ($attributesAllowed as $attribute) {
            if (is_string($attribute)) {
                // Canonicalize the attribute name
                $attributeName = strtolower($attribute);
                $this->_attributesAllowed[$attributeName] = null;
            }
        }

        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * @todo improve docblock descriptions
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $valueCopy = (string) $value;

        // If comments are allowed, then replace them with unique identifiers
        if ($this->commentsAllowed) {
            preg_match_all('/<\!--.*?--\s*>/s' , (string) $valueCopy, $matches);
            $comments = array_unique($matches[0]);
            foreach ($comments as $k => $v) {
                $valueCopy = str_replace($v, self::UNIQUE_ID_PREFIX . $k, $valueCopy);
            }
        }

        // Initialize accumulator for filtered data
        $dataFiltered = '';
        // Parse the input data iteratively as regular pre-tag text followed by a
        // tag; either may be empty strings
        preg_match_all('/([^<]*)(<?[^>]*>?)/', (string) $valueCopy, $matches);
        // Iterate over each set of matches
        foreach ($matches[1] as $index => $preTag) {
            // If the pre-tag text is non-empty, strip any ">" characters from it
            if (strlen($preTag)) {
                $preTag = str_replace('>', '', $preTag);
            }
            // If a tag exists in this match, then filter the tag
            $tag = $matches[2][$index];
            if (strlen($tag)) {
                $tagFiltered = $this->_filterTag($tag);
            } else {
                $tagFiltered = '';
            }
            // Add the filtered pre-tag text and filtered tag to the data buffer
            $dataFiltered .= $preTag . $tagFiltered;
        }

        // If comments are allowed, then replace the unique identifiers with the corresponding comments
        if ($this->commentsAllowed) {
            foreach ($comments as $k => $v) {
                $dataFiltered = str_replace(self::UNIQUE_ID_PREFIX . $k, $v, $dataFiltered);
            }
        }

        // Return the filtered data
        return $dataFiltered;
    }

    /**
     * Filters a single tag against the current option settings
     *
     * @param  string $tag
     * @return string
     */
    protected function _filterTag($tag)
    {
        // Parse the tag into:
        // 1. a starting delimiter (mandatory)
        // 2. a tag name (if available)
        // 3. a string of attributes (if available)
        // 4. an ending delimiter (if available)
        $isMatch = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);
        // If the tag does not match, then strip the tag entirely
        if (!$isMatch) {
            return '';
        }
        // Save the matches to more meaningfully named variables
        $tagStart = $matches[1];
        $tagName = strtolower($matches[2]);
        $tagAttributes = $matches[3];
        $tagEnd = $matches[5];
        // If the tag is not an allowed tag, then remove the tag entirely
        if (!isset($this->_tagsAllowed[$tagName])) {
            return '';
        }
        // Trim the attribute string of whitespace at the ends
        $tagAttributes = trim($tagAttributes);
        // If there are non-whitespace characters in the attribute string
        if (strlen($tagAttributes)) {
            // Parse iteratively for well-formed attributes
            preg_match_all('/(\w+)=([\'"])((.(?!=\2))+)\2/s', $tagAttributes, $matches);
            // Initialize valid attribute accumulator
            $tagAttributes = '';
            // Iterate over each matched attribute
            foreach ($matches[1] as $index => $attributeName) {
                $attributeName = strtolower($attributeName);
                $attributeDelimiter = $matches[2][$index];
                $attributeValue = $matches[3][$index];
                // If the attribute is not allowed, then remove it entirely
                if (!array_key_exists($attributeName, $this->_tagsAllowed[$tagName])
                    && !array_key_exists($attributeName, $this->_attributesAllowed)) {
                    continue;
                }
                // Add the attribute to the accumulator
                $tagAttributes .= " $attributeName=" . $attributeDelimiter
                                . $attributeValue . $attributeDelimiter;
            }
        }
        // Reconstruct tags ending with "/>" as backwards-compatible XHTML tag
        if (strpos($tagEnd, '/') !== false) {
            $tagEnd = " $tagEnd";
        }
        // Return the filtered tag
        return $tagStart . $tagName . $tagAttributes . $tagEnd;
    }
}
