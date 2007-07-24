<?php
/**
 * Data structures used in parsing XML DocBook-based tutorials
 *
 * Conversion of DocBook-based tutorials is performed using special
 * {@link Converter} class methods.  By default, these methods simply retrieve
 * simple rules for replacement of tags and slight re-ordering from the
 * options.ini file present for every template.
 *
 * In future versions, there may be utilization of xslt or other more powerful
 * protocols.  However, for most situations, the power of these classes will
 * be more than sufficient to handle very complex documentation.
 *
 * Note that an entire tutorial is contained in a single parserXMLDocBookTag,
 * matching the document model for DocBook.  The top-level tag, <refentry>,
 * contains every other tag and all text.
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2002-2006 Gregory Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @subpackage Tutorial
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: PackagePageElements.inc,v 1.4 2006/05/22 01:07:28 cellog Exp $
 * @tutorial tutorials.pkg
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2.0
 */
/**
 * Represents <![CDATA[ ]]> sections.
 *
 * These sections are interpreted as plain text
 * @package phpDocumentor
 * @subpackage Tutorial
 */
class parserCData extends parserStringWithInlineTags
{
    /**
     * @uses Converter::getCData() convert contents to text
     * @param Converter
     */
    function Convert(&$c, $postprocess = true)
    {
        $val = $this->value;
        if ($postprocess)
        foreach($this->value as $key => $value)
        {
            if (is_string($value)) $this->value[$key] = $c->getCData($value);
        }
        $this->cache = false;
        $x = parent::Convert($c, false);
        $this->value = $val;
        return $x;
    }
}
/**
 * a standard XML DocBook Tag
 *
 * This class is designed to represent all DocBook tags.  It is intelligent
 * enough to understand the <title> tag, and also the <refname> tag for
 * as title for <refentry>
 * @since 1.2
 * @package phpDocumentor
 * @subpackage Tutorial
 */
class parserXMLDocBookTag extends parserStringWithInlineTags
{
    /**
     * Attributes from the XML tag
     *
     * Format: array(attrname => attrvalue, attrname => attrvalue,...)
     * @var array
     */
    var $attributes = array();
    /**
     * Name of the tag
     * @var string
     */
    var $name;
    /**#@+ @access private */
    /** @var parserCData */
    var $_cdata;
    /** @var parserTag */
    var $_title;
    /** @var parserIdLineTag */
    var $_id;
    /**
     * Set to <refpurpose> in <refsynopsisdiv>
     * @var parserTag
     */
    var $_description;
    /**#@-*/
    /**
     * @param string tag name
     */
    function parserXMLDocBookTag($name)
    {
        $this->name = $name;
    }
    
    /**
     * @param Converter
     * @param boolean
     * @uses Converter::TranslateTag() Calls this to enclose the contents of the
     *       DocBook tag based on the values in template options.ini file
     */
    function Convert(&$c, $postprocess = true)
    {
        $value = parent::Convert($c, $postprocess);
        $simvalue = parent::Convert($c, false);
        foreach($this->attributes as $a => $v)
        {
            $this->attributes[$a] = (is_string($v) ? $v : $v->Convert($c, $postprocess));
        }
        if (isset($this->_title))
        {
            list($this->attributes,$value) = $c->ConvertTitle($this->name, $this->attributes, $this->_title->Convert($c, $postprocess), $value);
        }
        return $c->TranslateTag($this->name,$this->attributes,$value,$simvalue);
    }
    
    /**
     * Begin a new CData section
     * @see addCData()
     */
    function startCData()
    {
        $this->_cdata = new parserCData;
    }
    
    /**
     * Adds {@link $_cdata} to {@link $value}
     */
    function endCData()
    {
        $this->value[] = $this->_cdata;
        unset($this->_cdata);
    }
    
    /**
     * Retrieve either the table of contents index, or the location that
     * the TOC will go
     * @see setTOC()
     * @param false|integer either an index of the {@}toc} tag in $this->value
     *                      or false, if the next index value of $this->value
     *                      is needed
     */
    function getTOC($state = false)
    {
        if ($state !== false) return $this->value[$state];
        return count($this->value);
    }
    
    /**
     * @param integer index of the TOC in $this->value
     * @param parserTocInlineTag
     */
    function setTOC($state, $val)
    {
        $this->value[$state] = $val;
    }
    
    /**
     * add a word to CData
     * @param string
     */
    function addCData($word)
    {
        $this->_cdata->add($word);
    }
    
    /**
     * Add an xml tag attribute name="value" pair
     *
     * if the attribute is id, value must be a {@link parserIdInlineTag}
     * @param string attribute name
     * @param string|parserIdInlineTag value of attribute
     */
    function addAttribute($name,$value)
    {
        $this->attributes[$name] = $value;
        if ($name == 'id')
        {
            // fix 1153593
            if (is_string($value))
            {
                addWarning(PDERROR_ID_MUST_BE_INLINE,$this->name,$value,$this->name,$value);
            } else {
                $this->setId($value);
            }
        }
    }
    
    /**
     * Set the title of a DocBook tag section.
     *
     * For most DocBook tags, the title is represented with a <title></title>
     * tag pair.  The <refentry> top-level tag is a little different.  Instead
     * of using <title></title>, phpDocumentor uses the contents of the
     * <refname> tag in the <refnamediv> tag
     * @param parserXMLDocBookTag the title element
     */
    function setTitle($title)
    {
        $this->_title = $title;
    }
    
    /**
     * If the id attribute is present, this method will set its id
     * @param parserIdInlineTag
     */
    function setId($id)
    {
        $this->_id = $id;
    }
    
    /**
     * Return converter-specific formatting of ID.
     *
     * Passes $c to {@link parserIdInlineTag::Convert()}
     * @param Converter
     * @return string
     */
    function getId(&$c)
    {
        if ($this->_id) return trim($this->_id->Convert($c));
    }
    
    /**
     * Determine whether the docbook element has a title
     * @return boolean
     */
    function hasTitle()
    {
        return isset($this->_title);
    }
    
    /**
     * Retrieve Converter-specific formatting of the title of this element
     * @return string
     * @param Converter
     */
    function getTitle(&$c)
    {
        if ($this->name == 'refentry')
        {
            foreach($this->value as $tag)
            {
                if (is_object($tag) && $tag->name == 'refnamediv')
                {
                    return $tag->getTitle($c);
                }
            }
        }
        if ($this->name == 'refnamediv')
        {
            foreach($this->value as $tag)
            {
                if (is_object($tag) && is_a($tag, 'parserXMLDocBookTag') && $tag->name == 'refname')
                {
                    $t = new parserStringWithInlineTags;
                    foreach($tag->value as $val) $t->add($val);
                    $this->_title = $t;
                }
                if (is_object($tag) && is_a($tag, 'parserXMLDocBookTag') && $tag->name == 'refpurpose')
                {
                    $t = new parserStringWithInlineTags;
                    foreach($tag->value as $val) $t->add($val);
                    $this->_description = $t;
                }
            }
        }
        if (isset($this->_title))
        return $this->_title->Convert($c);
        if (is_object($this->value[0]) && is_a($tag, 'parserXMLDocBookTag')) {
            return $this->value[0]->getTitle($c);
        }
        if (isset($this->value[1])) {
            if (is_object($this->value[1]) && is_a($tag, 'parserXMLDocBookTag')) {
                return $this->value[1]->getTitle($c);
            }
        }
        return '';
    }
    
    /**
     * Retrieve the contents of a subsection
     *
     * This method uses the $_id members of nested docbook tags to retrieve
     * the section defined by $subsection
     * @param Converter
     * @param string converter-specific subsection
     */
    function getSubsection(&$c,$subsection)
    {
        if (!is_object($this->_id)) {
            return false;
        }
        $search = phpDocumentor_clone($this->_id);
        if (is_string($this->_id)) return false;
        if (phpDocumentor_get_class($search) != 'parseridinlinetag') return false;
        $search->id = $subsection;
        foreach($this->value as $el)
        {
            if (phpDocumentor_get_class($el) == 'parserxmldocbooktag')
            {
                if ($el->getId($c) == $search->Convert($c))
                {
                    return $el;
                } elseif ($a = $el->getSubsection($c,$subsection))
                {
                    return $a;
                }
            }
        }
        return false;
    }
    
    /**
     * Add contents to this tag.
     *
     * There are four kinds of data in a DocBook tutorial:
     *  1. <b>tags</b> - normal tags like <refentry>
     *  2. <b>entities</b> - normal entities like &rdquo;
     *  3. <b><![CDATA[</b> - character data that should not be interpreted,
     *     like <programlisting> contents
     *  4. <b>text</b> - normal non-markup text
     *
     * All four kinds of data are added here
     * @param parserEntity|parserCData|parserXMLDocBookTag|string nested tag,
     *        entity, or text
     */
    function add($el)
    {
        if (is_string($el)) return parent::add($el);
        if (phpDocumentor_get_class($el) == 'parserxmldocbooktag')
        {
            if ($el->name == 'title')
            {
                $this->setTitle($el);
            } else return parent::add($el);
        } else return parent::add($el);
    }
}

/**
 * a standard entity like &rdquo;
 *
 * This class is designed to represent all DocBook entities.
 * @since 1.2
 * @package phpDocumentor
 * @subpackage Tutorial
 */
class parserEntity
{
    /**
     * @param string entity name
     */
    function parserEntity($name)
    {
        $this->value = $name;
    }
    
    /**
     * @uses Converter::TranslateEntity() convert contents to text
     * @param Converter
     * @return string
     */
    function Convert(&$c, $postprocess = true)
    {
        if ($postprocess)
        return $c->TranslateEntity($this->value);
        else
        {
            $trans_tbl = get_html_translation_table (HTML_ENTITIES);
            $trans_tbl = array_flip ($trans_tbl);
            $ret = strtr ('&'.$this->value.';', $trans_tbl);
            return $ret;
        }
    }
}
?>