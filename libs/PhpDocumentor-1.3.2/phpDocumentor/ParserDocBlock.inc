<?php
/**
 * DocBlock Parser Classes
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
 * @subpackage ParserDocBlock
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        Parser, WordParser
 * @since      1.0rc1
 */
/**
 * represents a short or long description in a DocBlock ({@link parserDocBlock})
 * @package phpDocumentor
 * @subpackage ParserDocBlock
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserDesc extends parserStringWithInlineTags
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * always '_desc'
     * @var string
     */
    var $type = '_desc';
    
    /**
     * @param mixed like {@link parserStringWithInlineTags::add()}, this can be a string or parserInlineTag, but it can also be a
     *              parserStringWithInlineTags, and the contents will be merged
     */
    function add($stringOrClass)
    {
        if (is_object($stringOrClass))
        {
            if (phpDocumentor_get_class($stringOrClass) == 'parserstringwithinlinetags' ||
                phpDocumentor_get_class($stringOrClass) == 'parserdesc')
            {
                for($i=0;$i<count($stringOrClass->value);$i++)
                {
                    parserStringWithInlineTags::add($stringOrClass->value[$i]);
                }
            } else
            {
                parserStringWithInlineTags::add($stringOrClass);
            }
        } else return parserStringWithInlineTags::add($stringOrClass);
    }
    
    /**
     * @return boolean whether this desc has an {@}inheritdoc} inline tag
     */
    function hasInheritDoc()
    {
        for($i=0;$i<count($this->value);$i++)
        {
            if (phpDocumentor_get_class($this->value[$i])=='parserinheritdocinlinetag') return true;
        }
    }
    
    /**
     * @return boolean whether this desc has an {@}source} inline tag
     */
    function hasSource()
    {
        for($i=0;$i<count($this->value);$i++)
        {
            if (phpDocumentor_get_class($this->value[$i])=='parsersourceinlinetag') return true;
        }
    }
    
    /**
     * replaces {@}inheritdoc} with the contents of the parent DocBlock
     * @param parserDesc parent parserDesc, used to retrieve the description
     */
    function replaceInheritDoc($desc)
    {
        $value = $this->value;
        $this->value = array();
        for($i=0;$i<count($value);$i++)
        {
            if (phpDocumentor_get_class($value[$i])=='parserinheritdocinlinetag')
            {
                for($j=0;$j<count($desc->value);$j++)
                {
                    $this->add($desc->value[$j]);
                }
            } else $this->add($value[$i]);
        }
    }
}

/**
 * Represents a docblock and its components, {@link $desc}, {@link $sdesc}, {@link $tags}, and also {@link $params} for functions
 * @package phpDocumentor
 * @subpackage ParserDocBlock
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserDocBlock
{
    /**
     * @var parserDesc
     */
    var $desc = false;
    /**
     * @var array array of {@link parserDesc}s
     */
    var $processed_desc = false;
    /**
     * @var array array of {@link parserDesc}s
     */
    var $processed_sdesc = false;
    /**
     * @var parserDesc
     */
    var $sdesc = false;
    /**
     * Line number in the source on which this docblock begins
     * @since 1.2
     * @var false|integer
     */
    var $linenumber = false;
    /**
     * Line number in the source on which this docblock ends
     * @since 1.2
     * @var false|integer
     */
    var $endlinenumber = false;
    /**
     * array of {@link parserTag}s
     * @var array
     */
    var $tags = array();
    /**
     * array of unrecognized {@link parserTag}s
     * @var array
     */
    var $unknown_tags = array();
    /**
     * array of param data.
     * Format:
     * array(index of param in function parameter list -OR- parameter name =>
     *         parserStringWithInlineTags,...)
     * @var array
     */
    var $params = array();
    /**
     * array of global variable data.
     * Format:
     * array(index of global variable in @global tag list -OR- global variable name =>
     *         array(datatype,parserStringWithInlineTags),...)
     * @var array
     */
    var $funcglobals = array();
    
    /**
     * array of static variable data.
     * Format:
     * array(index of static variable in @global tag list -OR- static variable name =>
     *         {@link parserStaticvarTag},...)
     * @var array
     */
    var $statics = array();
    /**
     * This is either a {@link parserReturnTag} or false if no return tag is present
     * @var mixed
     */
    var $return = false;
    /**
     * This is either a {@link parserVarTag} or false if no var tag is present
     * @var mixed
     */
    var $var = false;
    /**
     * fix for bug 591396
     * @var boolean
     */
    var $explicitpackage = false;
    /**
     * fix for bug 708559
     * @var boolean
     */
    var $explicitcategory = false;
    /** @var string */
    var $category;
    /** @var string */
    var $package = 'default';
    /** @var string */
    var $subpackage = '';
    /**
     * whether this DocBlock has an @access tag
     * @var boolean */
    var $hasaccess = false;
    /**
     * whether this DocBlock has a @name tag
     * @var boolean */
    var $hasname = false;
    /**
     * description of package parsed from @package tag
     * Unused in this version
     * @var string
     */
    var $packagedescrip = '';
    /**
     * description of subpackage parsed from @package tag
     * Unused in this version
     * @var string
     */
    var $subpackagedescrip = '';
    /**
     * Determines whether a DocBlock can legally have a {@}source} tag
     * @tutorial tags.inlinesource.pkg
     * @var boolean
     * @access private
     */
    var $_canSource = false;
    
    /**
     * sets package to default
     * @global string default package name
     */
    function parserDocBlock()
    {
        global $phpDocumentor_DefaultPackageName;
        $this->package = $GLOBALS['phpDocumentor_DefaultPackageName'];
        $this->category = $GLOBALS['phpDocumentor_DefaultCategoryName'];
    }
    
    /**
     * Sets the starting line number for the DocBlock
     * @param integer
     */
    function setLineNumber($number)
    {
        $this->linenumber = $number;
    }
    
    /**
     * Retrieve starting line number
     * @return integer
     */
    function getLineNumber()
    {
        return $this->linenumber;
    }
    
    /**
     * Sets the ending line number for the DocBlock
     * @param integer
     */
    function setEndLineNumber($number)
    {
        $this->endlinenumber = $number;
    }
    
    /**
     * Retrieve ending line number
     * @return integer
     */
    function getEndLineNumber()
    {
        return $this->endlinenumber;
    }
    
    /**
     * Parse out any html tags from doc comments, and make them into
     * abstract structures
     * @uses parserDescParser::parse()
     */
    function postProcess()
    {
        if ($this->sdesc)
        {
            $parser = new parserDescParser;
            $parser->subscribe('*',$this);
            if ($this->desc) $parser->parse($this->desc->value);
            $parser->parse($this->sdesc->value,true);
        }
    }
    
    /**
     * Tells the DocBlock it can have a @filesource tag
     *
     * Only page-level DocBlocks may have a @filesource tag
     */
    function canSource()
    {
        $this->_canSource = true;
    }
    
    /**
     * Tells the DocBlock it can't have a @filesource tag
     *
     * Only page-level DocBlocks may have a @filesource tag
     */
    function cantSource()
    {
        $this->_canSource = false;
    }
    
    /**
     * Indirectly called after parsing by {@link postProcess}
     *
     * @param integer either 1 for long desc or 2 for short desc
     * @param array data organized into paragraphs.  Each entry is a {@link parserStringWithInlineTags}
     * @uses $processed_desc sets to the array passed from {@link parserDescParser::parse()}
     * @uses $processed_sdesc sets to the array passed from {@link parserDescParser::parse()}
     * @access private
     */
    function HandleEvent($event,$data)
    {
        if ($event == 1)
        $this->processed_desc = $data;
        else
        $this->processed_sdesc = $data;
    }
    
    /**
     * @param array
     */
    function updateModifiers($modifiers)
    {
        if (is_array($modifiers) && count($modifiers))
        {
            foreach ($modifiers as $modifier)
            {
                switch ($modifier)
                {
                    case 'private' :
                    case 'public' :
                    case 'protected' :
                        unset($this->tags['access']);
                        $x = new parserAccessTag($modifier);
                        if ($x->isvalid)
                        {
                            $this->hasaccess = true;
                            $this->tags['access'][] = $x;
                        }
                    break;
                    case 'static' :
                    case 'abstract' :
                        unset($this->tags[$modifier]);
                        $this->addKeyword($modifier, '');
                    break;
                }
            }
        }
    }
    
    /**
     * Set the short description of the DocBlock
     *
     * Setting the short description is possible by passing in one of three
     * possible parameters:
     * <ul>
     *  <li>another DocBlock's short description</li>
     *  <li>another DocBlock, the short description will be extracted</li>
     *  <li>a Zend Studio-compatible @desc tag</li>
     * </ul>
     * @param parserDesc|parserDocBlock|parserTag sets {@link $sdesc}
     */
    function setShortDesc($desc)
    {
        if (phpDocumentor_get_class($desc) == 'parsertag')
        {
            $this->sdesc = new parserDesc;
            $this->processed_sdesc = $desc->value;
            return;
        }
        if (phpDocumentor_get_class($desc) == 'parserdesc') {
            $this->sdesc = $desc;
        } else
        {
            $this->sdesc = $desc->sdesc;
            $this->processed_sdesc = $desc->processed_sdesc;
        }
        
        if ($this->sdesc && $this->sdesc->hasSource())
        {
            addWarning(PDERROR_SOURCE_TAG_IGNORED,$this->sdesc->getString());
        }
    }
    
    /**
     * Passes to {@link parserStringWithInlineTags::setSource()}
     *
     * After passing, it calls {@link postProcess()} to set up the new
     * source
     * @param string|array tokenized highlight-ready source code
     * @param false|string name of class if this is a method source
     */
    function setSource($source, $class = false)
    {
        if ($this->desc)
        {
            $this->desc->setSource($source, $class);
            $this->postProcess();
        }
    }
    
    /**
     * @param parserDesc|parserDocBlock sets {@link $desc}
     */
    function setDesc($desc)
    {
        if (phpDocumentor_get_class($desc) == 'parserdesc')
        $this->desc = $desc;
        else
        {
            $this->desc = $desc->desc;
            $this->processed_desc = $desc->processed_desc;
        }
    }
    
    /**
     * Wrapper for {@link parserDesc::hasInheritDoc()}
     * @return boolean
     */
    function hasInheritDoc()
    {
        if (!$this->desc) return false;
        return $this->desc->hasInheritDoc();
    }
    
    /**
     * Wrapper for {@link parserDesc::replaceInheritDoc()}
     *
     * Also replaces {@}inheritdoc} in the {@link $processed_desc}
     * @param parserDesc
     */
    function replaceInheritDoc($desc)
    {
        if (!$this->desc) return false;
        $this->desc->replaceInheritDoc($desc->desc);
        $this->postProcess();
    }
    
    /**
     * @param Converter takes {@link $sdesc} and converts it to a string and returns it if present, otherwise returns ''
     * @return string
     */
    function getSDesc(&$converter)
    {
        if ($this->sdesc && $this->processed_sdesc)
        {
            $result = '';
            foreach($this->processed_sdesc as $desc)
            {
                if (count($desc->value))
                $result .= $desc->Convert($converter);
            }
            return $result;
        } else
        {
//            var_dump($this->desc,$this->processed_desc);
        }
        return '';
    }
    
    /**
     * @param Converter takes {@link $desc} and converts it to a string and returns it if present, otherwise returns ''
     * @return string
     */
    function getDesc(&$converter)
    {
        if ($this->desc && $this->processed_desc)
        {
            $result = '';
            foreach($this->processed_desc as $desc)
            {
                if (count($desc->value))
                $result .= $converter->EncloseParagraph($desc->Convert($converter));
            }
            return $result;
        } else
        {
//            var_dump($this->desc,$this->processed_desc);
        }
        return '';
    }
    
    /**
     * @param string $paramVar if empty, param is indexed in the order received and set using {@link changeParam()}
     * @param parserStringWithInlineTags $value
     */
    function addParam($paramVar, $paramType, $value)
    {
        if (empty($paramVar))
        $this->params[count($this->params)] = new parserParamTag($paramType,$value);
        else
        $this->params[$paramVar] = new parserParamTag($paramType,$value);
    }

    function resetParams()
    {
        $this->params = array();
    }
    /**
     * @param integer $index index of parameter in the {@link $params} array
     * @param string $name name of the parameter to set in the $params array
     * @param string|null $type type of the parameter
     */
    function changeParam($index, $name, $type)
    {
        if ($name === $index) {
            return;
        }
        $this->params[$name] = $this->params[$index];
        unset($this->params[$index]);
    }
    
    /**
     * replaces nameless parameters in the {@link $params} array with their names
     * add @param tags for params in the function with no entry
     * @param array $params Format: array(parameter key =>
     *                      array(0 => parameter name[,1 => default value][,2 => type hint]),...)
     */
    function updateParams($params)
    {
        $countparams = array_values($params);
        reset($params);
        for($i=0;$i<count($countparams);$i++, next($params))
        {
            if (isset($this->params[$i]))
            {
                $info = current($params);
                $type = isset($info[2]) ? $info[2] : null;
                $this->changeParam($i, key($params), $type);
                $params[key($params)] = false;
            }
        }
        $blank = new parserStringWithInlineTags;
        foreach ($params as $key => $info) {
            if (!$info) {
                continue;
            }
            $type = isset($info[2]) ? $info[2] : null;
            if (!isset($this->params[$info[0]])) {
                $this->addParam($info[0], $type, $blank);
            }
        }
        reset($params);
        
        if (isset($this->tags))
        unset($this->tags['param']);
    }
    
    /**
     * Used to insert DocBlock Template tags into a docblock
     * @param parserTag tag
     * @global array used to determine whether to add ignored tags, or not
     */
    function addTag($tag)
    {
        global $_phpDocumentor_setting;
        if (phpDocumentor_setup::checkIgnoreTag($tag->keyword)) return;
        $value = $tag->value;
        if (is_array($value)) $value = $value[0];
        if ($tag->keyword == 'uses')
        {
            $this->addUses($value, $tag->_description);
        } else
        {
            $this->addKeyword($tag->keyword, $value);
        }
    }

    /**
     * @param string $keyword tag name
     * @param parserStringWithInlineTags $value the contents of the tag
     * @global array used to determine whether to add the @internal tag or not
     */
    function addKeyword($keyword, $value)
    {
        global $_phpDocumentor_setting;
        $keyword = trim($keyword);
        if (phpDocumentor_setup::checkIgnoreTag($keyword)) return;
        // don't add the tag at all if it was specified to ignore it with --ignore-tags
        if ($keyword == 'package' || $keyword == 'subpackage' || $keyword == 'category') return $this->addPackage($keyword, $value);
        if ($keyword == 'access') return $this->addAccess($value);
        if ($keyword == 'link') return $this->addLink($value);
        if ($keyword == 'see' || $keyword == 'tutorial') return $this->addSee($keyword,$value);
        if ($keyword == 'uses') return $this->addUses($keyword, $value);
        if ($keyword == 'name') return $this->addName($value);
        if (!in_array($keyword,$GLOBALS['_phpDocumentor_tags_allowed']))
        $this->addUnknownTag($keyword,$value);
        else
        {
        if ($keyword == 'internal' && (!isset($_phpDocumentor_setting['parseprivate']) || $_phpDocumentor_setting['parseprivate'] == 'off')) return;
            if (!isset($this->tags[$keyword])) {
                $this->tags[$keyword] = array();
            }
            $ptag = 'parserTag';
            if (class_exists('parser'.$keyword.'tag'))
                $ptag = 'parser'.ucfirst($keyword).'Tag';
            array_unshift($this->tags[$keyword], new $ptag($keyword, $value));
        }
    }
    
    /**
     * adds an @example tag
     * @param string contents of the tag
     * @param string path to the file containing this tag
     */
    function addExample($value, $path)
    {
        $this->tags['example'][] = new parserExampleTag($value, $path);
    }
    
    /**
     * adds an unknown tag to the {@link $unknown_tags} array for use by custom converters
     * @param string tag name
     * @param string tag value
     */
    function addUnknownTag($keyword, $value)
    {
        addWarning(PDERROR_UNKNOWN_TAG,$keyword);
        $this->unknown_tags[$keyword][] = new parserTag($keyword, $value);
    }
    
    /**
     * set the element's package to the passed values.  Used in {@link phpDocumentor_IntermediateParser} to align package of
     * elements inside a class or procedural page to the package of the class/procedural page
     * @param string
     * @param string
     * @param string
     * @param string element name
     * @param string element type (include, define, var, method, global, function, const)
     */
    function overridePackage($category, $package,$subpackage,$elname,$type)
    {
        if ($this->package != $GLOBALS['phpDocumentor_DefaultPackageName'])
        {
            addError(PDERROR_OVERRIDDEN_PACKAGE_TAGS,$elname,$type,$this->package);
            $this->explicitpackage = false;
        }
        if (!empty($this->subpackage))
        addError(PDERROR_OVERRIDDEN_SUBPACKAGE_TAGS,$type,$elname,$this->subpackage);
        $this->package = $GLOBALS['phpDocumentor_DefaultPackageName'];
        $this->subpackage = '';
        $this->category = $category;
        $this->addPackage('package',$package);
        $this->addPackage('subpackage',$subpackage);
    }
    
    /**
     * Used if this docblock has a @package tag.
     *
     * phpDocumentor will guess package for DocBlocks that don't have
     * a @package tag
     * @uses $explicitpackage
     */
    function setExplicitPackage()
    {
        $this->explicitpackage = true;
    }
    
    /**
     * If the DocBlock has a @package tag, then this returns true
     * @return boolean
     */
    function getExplicitPackage()
    {
        return $this->explicitpackage;
    }
    
    /**
     * Used if this docblock has a @category tag.
     *
     * phpDocumentor will guess category for DocBlocks that don't have
     * a @category tag
     * @uses $explicitcategory
     */
    function setExplicitCategory()
    {
        $this->explicitcategory = true;
    }
    
    /**
     * If the DocBlock has a @category tag, then this returns true
     * @return boolean
     */
    function getExplicitCategory()
    {
        return $this->explicitcategory;
    }
    
    /**
     * @param string $keyword tag name (either package or subpackage)
     * @param mixed $value either a string or a parserStringWithInlineTags.  Strips all inline tags and use the text as the package
     */
    function addPackage($keyword, $value)
    {
        if ($keyword == 'package')
        {
            if (!$this->explicitpackage)
            {
                if (!is_string($value))
                $value = $value->getString();
                $rest = '';
                $value = explode(' ',$value);
                if (count($value) - 1)
                {
                    $rest = $value;
                    $value = trim($value[0]);
                    unset($rest[0]);
                    $rest = implode($rest,' ');
                } else
                {
                    $value = explode("\t",$value[0]);
                    if (count($value) - 1)
                    {
                        $rest = $value;
                        $value = trim($value[0]);
                        unset($rest[0]);
                        $rest = implode($rest,"\t");
                    } else $value = trim($value[0]);
                }
                $value = preg_replace('/[^\[\]0-9\-a-zA-Z_\x7f-\xff]/', '-', $value);
                $this->packagedescrip = $this->package = trim($value);
                if (!empty($rest)) $this->packagedescrip = $rest;
            } else
            {
                if (is_string($value))
                addError(PDERROR_MULTIPLE_PACKAGE_TAGS,$value);
                else
                addError(PDERROR_MULTIPLE_PACKAGE_TAGS,$value->getString());
            }
        } elseif ($keyword == 'subpackage')
        {
            if (empty($this->subpackage))
            {
                if (!is_string($value))
                $value = $value->getString();
                $rest = '';
                $value = explode(' ',$value);
                if (count($value) - 1)
                {
                    $rest = $value;
                    $value = $value[0];
                    unset($rest[0]);
                    $rest = implode($rest,' ');
                } else
                {
                    $value = explode("\t",$value[0]);
                    if (count($value) - 1)
                    {
                        $rest = $value;
                        $value = $value[0];
                        unset($rest[0]);
                        $rest = implode($rest,"\t");
                    } else $value = $value[0];
                }
                if (!empty($value))
                {
                    $value = preg_replace('/[^\[\]0-9\-a-zA-Z_\x7f-\xff]/', '-', $value);
                }
                $this->subpackage = trim($value);
                if (!empty($rest)) $this->subpackagedescrip = $rest;
            } else
            {
                if (is_string($value))
                addError(PDERROR_MULTIPLE_SUBPACKAGE_TAGS,$value);
                else
                addError(PDERROR_MULTIPLE_SUBPACKAGE_TAGS,$value->getString());
            }
        } elseif ($keyword == 'category')
        {
            if (!$this->explicitcategory)
            {
                if (!is_string($value))
                $value = $value->getString();
                $value = preg_replace('/[^\[\]0-9\-a-zA-Z_\x7f-\xff]/', '-', $value);
                $this->category = $value;
            } else
            {
                if (is_string($value))
                addError(PDERROR_MULTIPLE_CATEGORY_TAGS,$value);
                else
                addError(PDERROR_MULTIPLE_CATEGORY_TAGS,$value->getString());
            }
        }
    }
    
    /**
     * Adds a @name tag to the tag list
     * @param string new name of element
     */
    function addName($value)
    {
        if (is_object($value)) $value = $value->getString();
        if (!$this->hasname)
        {
            $x = new parserNameTag('name',$value);
            $this->hasname = true;
            $this->tags['name'][] = $x;
        } else
        {
            addError(PDERROR_MULTIPLE_NAME_TAGS,$value);
        }
    }
    
    /**
     * @param string if empty, staticvar is indexed in the order received and set using {@link changeStatic()}
     * @param string data type
     * @param parserStringWithInlineTags
     */
    function addStaticVar($staticvar, $type, $descrip)
    {
        if (empty($staticvar))
        $this->statics[] = new parserStaticvarTag($type,$descrip);
        else
        $this->statics[$staticvar] = new parserStaticvarTag($type,$descrip);
    }
    
    /**
     * adds a function declaration of @global to the {@link $funcglobals} array
     * @param string global type
     * @param string description of how the global is used in the function
     */
    function addFuncGlobal($type,$value)
    {
        $this->funcglobals[] = array($type,$value);
    }
    
    /**
     * @param integer $index index of parameter in the {@link $funcglobals} array
     * @param string $name name of the parameter to set in the $funcglobals array
     */
    function changeGlobal($index,$name)
    {
        $this->funcglobals[$name] = $this->funcglobals[$index];
        unset($this->funcglobals[$index]);
    }

    /**
     * @param integer $index index of parameter in the {@link $statics} array
     * @param string $name name of the parameter to set in the $statics array
     */
    function changeStatic($index,$name)
    {
        $this->statics[$name] = $this->statics[$index];
        unset($this->statics[$index]);
    }

    /**
     * replaces nameless global variables in the {@link $funcglobals} array with their names
     * @param array
     */
    function updateGlobals($funcs)
    {
        for($i=0;$i<count($funcs);$i++)
        {
            if (isset($this->funcglobals[$i]))
            {
                $this->changeGlobal($i,$funcs[$i]);
            }
        }
    }

    /**
     * replaces nameless static variables in the {@link $statics} array with their names
     * @param array
     */
    function updateStatics($funcs)
    {
        for($i=0;$i<count($funcs);$i++)
        {
            if (isset($this->statics[$i]))
            {
                $this->changeStatic($i,$funcs[$i]);
            }
        }
    }

    /**
     * add an @access tag to the {@link tags} array
     * @param string should be either public or private
     */
    function addAccess($value)
    {
        if (is_object($value)) $value = $value->getString();
        $value = strtolower($value);
        if (!$this->hasaccess)
        {
            $x = new parserAccessTag($value);
            if ($x->isvalid)
            {
                $this->hasaccess = true;
                $this->tags['access'][] = $x;
            }
        } else
        {
            if (is_string($value))
            addError(PDERROR_MULTIPLE_ACCESS_TAGS,$value);
            else
            addError(PDERROR_MULTIPLE_ACCESS_TAGS,$value->getString());
        }
    }
    
    /**
     * Adds a new @filesource tag to the DocBlock
     * @tutorial tags.filesource.pkg
     * @param string full path to the file
     * @param array tokenized source code, ordered by line number
     */
    function addFileSource($path, $source)
    {
        if (isset($this->tags['filesource'])) return;
        $this->tags['filesource'][] = new parserFileSourceTag($path, $source);
    }
    
    /**
     * creates a {@link parserLinkTag} and adds it to the {@link $tags} array
     * @param string $link
     */
    function addLink($link)
    {
        if (phpDocumentor_setup::checkIgnoreTag('@link')) return;
        $this->tags['link'][] = new parserLinkTag($link);
    }
    
    /**
     * creates a {@link parserLinkTag} and adds it to the {@link $tags} array
     * @param string either see or uses
     * @param string $value
     */
    function addSee($keyword,$value)
    {
        if (phpDocumentor_setup::checkIgnoreTag($keyword)) return;
        $tag = 'parser'.ucfirst($keyword).'Tag';
        $this->tags[$keyword][] = new $tag($value);
    }
    
    /**
     * creates a {@link parserReturnTag} and adds it to the {@link $tags} array
     * @param string $returnType the one-word name of the return type (mixed should be used if more than one type)
     * @param parserStringWithInlineTags $value
     */
    function addReturn($returnType, $value)
    {
        // only take the first one
        if (!$this->return)
        {
            $this->return = new parserReturnTag($returnType, $value);
        } else
        {
            addError(PDERROR_MULTIPLE_RETURN_TAGS,$returnType,$value->getString());
        }
    }
    
    /**
     * creates a {@link parserVarTag} and adds it to the {@link $tags} array
     * @param string $varType the one-word name of the variable type (mixed should be used if more than one type)
     * @param parserStringWithInlineTags $value
     */
    function addVar($varType, $value)
    {
        // only take the first one
        if (!$this->var)
        {
            $this->var = new parserVarTag($varType, $value);
        } else
        {
            addError(PDERROR_MULTIPLE_VAR_TAGS,$varType,$value->getString());
        }
    }
    
    /**
     * Adds a virtual @usedby tag to output
     * @param abstractLink link to the element that has a @uses tag
     * @param parserStringWithInlinetags description of how the elements uses
     *                                   this one
     * @access private
     */
    function addUsedBy($link, $descrip)
    {
        $this->tags['usedby'][] = new parserUsedByTag($link, $descrip);
    }
    
    /**
     * Add a @uses tag to the DocBlock
     * @param string @see-style text, used for {@link Converter::getLink()}
     * @param parserStringWithInlineTags description of how the used element is
     *                                   used
     * @tutorial tags.uses.pkg
     */
    function addUses($seeel, $description)
    {
        $this->tags['uses'][] = new parserUsesTag($seeel, $description);
        usort($this->tags['uses'], array($this, '_sortUses'));
    }

    /**
     * Custom sorting function for sorting @uses tags
     *
     * @param parserTag $a
     * @param parserTag $b
     * @access private
     * @return int
     */
    function _sortUses($a, $b)
    {
        return strnatcasecmp($a->getString(), $b->getString());
    }

    /**
     * @param string
     * @return mixed false if no keyword, unconverted value if one keyword, array of unconverted values if more than one keyword
     */
    function getKeyword($keyword)
    {
        if ($keyword == 'filesource' && !$this->_canSource) return false;
        if (isset($this->tags[$keyword]))
        {
            if (count($this->tags[$keyword]) == 1)
            {
                return $this->tags[$keyword][0];
            } else return $this->tags[$keyword];
        } else return false;
    }
    
    /**
     * @return array Format: array('var' => tag name, 'data' => unconverted tag value)
     */
    function listParams()
    {
        if (isset($this->params))
        {
            $ret = array();
            foreach($this->params as $key => $val)
            {
                $ret[] = array("var" => ucfirst($key),"data" => $val);
            }
            return $ret;
        } else {
            return array();
        }
    }
    
    /**
     * @param Converter
     */
    function listTags()
    {
        $tags = array();
        foreach($this->tags as $keyword => $vals)
        {
            if ($keyword == 'filesource' && !$this->_canSource) continue;
            foreach($vals as $val)
            {
                $tags[] = $val;
            }
        }
        usort($tags,'tagsort');
        return $tags;
    }
    
    /** @return string always 'docblock' */
    function getType()
    {
        return 'docblock';
    }
}

/**
 * Determines the arbitrary tag rank value for a given tag
 * @access private
 */
function getTagRanking($tag)
{
    switch(phpDocumentor_get_class($tag))
    {
        case 'parserreturntag' :
            $o = 0;
            break;
        case 'parservartag' :
            $o = 1;
            break;
        case 'parsertutorialtag' :
            $o = 2;
            break;
        case 'parserstaticvartag' :
            $o = 3;
            break;
        case 'parserseetag' :
            $o = 10;
            break;
        case 'parserlinktag' :
            $o = 11;
            break;
        case 'parsertag' :
            switch ($tag->keyword)
            {
                case 'author' :
                    $o = 4;
                    break;
                case 'version' :
                    $o = 5;
                    break;
                case 'copyright' :
                    $o = 6;
                    break;
                case 'deprecated' :
                case 'deprec' :
                    $o = 12;
                    break;
                case 'todo' :
                case 'TODO' :
                    $o = 13;
                    break;
                case 'abstract' :
                    $o = 14;
                    break;
                default :
                    $o = 15;
                    break;
            }
            break;
        case 'parseraccesstag' :
            $o = 18;
            break;
        case 'parsernametag' :
            $o = 19;
            break;
        default :
            $o = 20;
            break;
    }
    return $o;
}

/**
 * Utilizes the getTagRanking method to determine tag sort order of two given tags
 * @access private
 */
function tagsort($a, $b)
{
    $returnval = 0;
    $o = getTagRanking($a);
    $p = getTagRanking($b);
    if ($o == $p) return 0;
    if ($o < $p) return -1;
    if ($o > $p) return 1;
}
?>
