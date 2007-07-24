<?php
/**
 * Parser Data Structures
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
 * @subpackage ParserData
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: ParserData.inc,v 1.6 2006/10/23 04:02:46 cellog Exp $
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.0rc1
 */
/**
 * Contains information about a PHP file, used to group procedural elements
 * together.
 * @package phpDocumentor
 * @subpackage ParserData
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id: ParserData.inc,v 1.6 2006/10/23 04:02:46 cellog Exp $
 */
class parserPage
{
    /**
     * Type is used by many functions to skip the hassle of if
     * <code>phpDocumentor_get_class($blah) == 'parserBlah'</code>
     * @var string
     */
    var $type = 'page';
    /**
     * not implemented in this version, will be used to link xml output pages
     * @var string
     */
    var $id = '';
    /**
     * filename.ext (no path)
     * @var string
     */
    var $file = '';
    /**
     * relative source location
     * @var string
     */
    var $sourceLocation = '';
    /**
     * phpdoc-safe name (only letters, numbers and _)
     * @var string
     */
    var $name = '';
    /**
     * original phpdoc-safe name (only letters, numbers and _)
     * 
     * This fixes [ 1391432 ] Too many underscores in include links.
     * @var string
     */
    var $origName = '';
    /**
     * @var string
     */
    var $category = 'default';
    /**
     * @var string
     */
    var $package = 'default';
    /**
     * @var string
     */
    var $subpackage = '';
    /**
     * @var string
     */
    var $parserVersion = PHPDOCUMENTOR_VER;
    /**
     * not implemented yet
     * file modification date, will be used for makefiles
     * @var string
     */
    var $modDate = '';
    /**
     * @var string full path this page represents
     */
    var $path = '';
    /**
     * Tokenized source code of the file
     * @var array
     */
    var $source = array();
    /**
     * Used to limit output, contains contents of --packageoutput commandline.
     * Does not increase parsing time.  Use --ignore for that
     * @see phpDocumentor_IntermediateParser::$packageoutput, Converter::$package_output
     * @var mixed either false or an array of packages
     */
    var $packageOutput = false;
    
    /**
     * sets package to default package
     * @global string default package name
     */
    function parserPage()
    {
        global $phpDocumentor_DefaultPackageName;
        $this->package = $GLOBALS['phpDocumentor_DefaultPackageName'];
    }
    
    /**
     * @return string always "page"
     */
    function getType()
    {
        return 'page';
    }
    
    /**
     * Sets the source code of the file for highlighting.
     *
     * PHP 4.3.0+ passes an array of tokenizer tokens by line number.  PHP
     * 4.2.3- passes a string to be passed to {@link highlight_string()}
     * @param string|array
     */
    function setSource($source)
    {
        $this->source = $source;
    }
    
    /**
     * Sets the name to display in documentation (can be an alias set with @name)
     * @param string $file
     */
    function setFile($file)
    {
        $this->file = $file;
    }
    
    /**
     * @return string filename.ext or @name alias
     */
    function getFile()
    {
        if (!isset($this->file)) return false;
        return $this->file;
    }
    
    /**
     * @param string $path full path to file
     */
    function setPath($path)
    {
        // look for special windows case
        if(SMART_PATH_DELIMITER === '\\')
            $this->path = strtr($path,'/','\\');
        else
            $this->path = $path;
    }
    
    /**
     * @return string fully delimited path (OS-dependent format)
     */
    function getPath()
    {
        if (!isset($this->path)) return false;
        return $this->path;
    }
    
    /**
     * @param array $packages array of packages to display in documentation (package1,package2,...)
     * @see phpDocumentor_IntermediateParser::$packageoutput
     */
    function setPackageOutput($packages)
    {
        $this->packageOutput = $packages;
    }
    
    /**
     * @return array array of packages (package1,package2,...)
     * @see phpDocumentor_IntermediateParser::$packageoutput
     */
    function getPackageOutput()
    {
        return $this->packageOutput;
    }
    
    /**
     * @param string $name phpdoc-safe name (only _, numbers and letters) set by Parser::parse()
     * @see Parser::parse()
     */
    function setName($name)
    {
        $this->origName = $name;
        $this->name = $name;
    }
    
    /**
     * @return string phpdoc-safe name (only _, numbers and letters)
     */
    function getName()
    {
        if (!isset($this->name)) return false;
        return $this->name;
    }
    
    /**
     * @param string $source path of this file relative to program root
     */
    function setSourceLocation($source)
    {
        $this->sourceLocation = $source;
    }
    
    /**
    * @param Converter
    * @param boolean if this parameter is true, it will truncate the source location to the
    * subdirectory of pear
     * @return string path of this file relative to program root
     */
    function getSourceLocation ($c,$pearize = false)
    {
        global $_phpDocumentor_options;
        if (!isset($this->sourceLocation)) return false;
        if ($pearize)
        {
            $sl = $this->sourceLocation;
            if (strpos($sl,'pear/'))
            {
                $sl = substr($sl,strpos($sl,'pear/') + 5);
                return $sl;
            } else
            {
                return str_replace($_phpDocumentor_options['Program_Root'] . PATH_DELIMITER,'',$sl);
            }
            return $sl;
        }
        return $this->sourceLocation;
    }
    /**
     * Not implemented in this version
     * @return boolean tell the parser whether to parse the file, otherwise
     *                   this function will retrieve the parsed data from external file
     */
    function getParseData()
    {
        return true;
    }
}

/**
 * Contains an in-memory representation of all documentable elements
 * ({@link parserPage}, {@link parserFunction}, {@link parserDefine},
 * {@link parserInclude}, {@link parserClass}, {@link parserMethod},
 * {@link parserVar}) and their DocBlocks ({@link parserDocBlock}).
 *
 * This class works in coordination with {@link phpDocumentor_IntermediateParser}
 * to take output from {@link Parser::handleEvent()} and create indexes, links,
 * and other assorted things (all documented in phpDocumentor_IntermediateParser
 * and {@link Converter})
 * @package phpDocumentor
 * @subpackage ParserData
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id: ParserData.inc,v 1.6 2006/10/23 04:02:46 cellog Exp $
 */
class parserData
{
    /**
     * {@link parserPage} element that is this parserData's parent, or false if
     * not set.
     * @var false|parserPage
     */
    var $parent = false;
    /**
     * array of parsed elements
     * @var array
     */
    var $elements = array();
    /**
     * @var boolean
     * @access private
     */
    var $_hasclasses = false;
    /**
     * @var boolean
     * @access private
     */
    var $_hasinterfaces = false;
    /**
     * array of parsed elements with @access private
     * @var array
     */
    var $privateelements = array();
    /**
     * array of parsed class elements
     * @var array
     */
    var $classelements = array();
    
    /**
     * @var parserTutorial|false
     */
    var $tutorial = false;
    /**
     * array of parsed class elements with @access private
     * @var array
     */
    var $privateclasselements = array();
    /**
     * array of links descended from {@link abstractLink}
     * @var array
     * @see pageLink, defineLink, classLink, functionLink, methodLink, varLink
     */
    var $links = array();
    /**
     * used by {@link phpDocumentor_IntermediateParser::handleDocBlock()} to
     * determine whether a docblock is a page-level docblock or not.  $clean is
     * true as long as only 0 or 1 docblock has been parsed, and no element
     * other than parserPage has been parsed
     * @var boolean
     */
    var $clean = true;
    /**
     * DocBlock ({@link parserDocBlock}) for this page, or false if not set
     * @var mixed
     */
    var $docblock = false;
    /**
     * Flag used to determine whether a page-level docblock is present
     * @var boolean
     * @access private
     */
    var $_explicitdocblock = false;
    /**
     * Type is used by many functions to skip the hassle of if
     * <code>phpDocumentor_get_class($blah) == 'parserBlah'</code>
     * always 'page', used in element indexing and conversion functions found in
     * {@link Converter}
     * @var string
     */
    var $type = 'page';
    
    /**
     * @param parserElement add a parsed element to the {@link $elements} array,
     *                      also sets {@link $clean} to false
     */
    function addElement(&$element)
    {
        $element->setPath($this->parent->path);
        if ($element->getType() == 'class' || $element->getType() == 'method' || $element->getType() == 'var'
            || $element->getType() == 'const')
        {
            if ($element->getType() == 'class') {
                if ($element->isInterface()) {
                    $this->_hasinterfaces = true;
                } else {
                    $this->_hasclasses = true;
                }
            }
            $this->classelements[] = $element;
        } else
        {
            $this->elements[] = $element;
        }
        $this->clean = false;
    }

    /**
     * Does this package have interfaces?
     *
     * @return boolean
     */
    function hasInterfaces()
    {
        return $this->_hasinterfaces;
    }

    /**
     * Does this package have classes?
     *
     * @return boolean
     */
    function hasClasses()
    {
        return $this->_hasclasses;
    }

    /**
     * @param parserTutorial
     * @param Converter
     */
    function addTutorial($t,&$c)
    {
        $this->tutorial = new tutorialLink;
        $this->tutorial->addLink('',$t->path,$t->name,$t->package,$t->subpackage,$t->getTitle($c));
    }
    
    /**
     * If this file has a tutorial associated with it, returns a link to the
     * tutorial.
     * @return tutorialLink
     */
    function getTutorial()
    {
        return $this->tutorial;
    }
    
    /**
     * If the page-level DocBlock was present in the source, returns true
     * @return boolean
     */
    function hasExplicitDocBlock()
    {
        return $this->_explicitdocblock;
    }
    
    /**
     * Tells this page that its DocBlock was not implicit
     */
    function explicitDocBlock()
    {
        $this->_explicitdocblock = true;
    }
    
    /**
     * @param parserElement element to add a new link (descended from
     * {@link abstractLink})to the {@link $links} array
     * @param string classname for elements that are class-based (this may be
     *               deprecated in the future, as the classname should be
     *               contained within the element.  if $element is a page, this
     *               parameter is a package name
     * @param string subpackage name for page elements
     */
    function addLink(&$element,$classorpackage = '', $subpackage = '')
    {
        switch($element->type)
        {
            case 'function':
                $x = new functionLink;
                $x->addLink($this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'define':
                $x = new defineLink;
                $x->addLink($this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'global':
                $x = new globalLink;
                $x->addLink($this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'class':
                $x = new classLink;
                $x->addLink($this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'method':
                $x = new methodLink;
                $x->addLink($classorpackage, $this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'var':
                $x = new varLink;
                $x->addLink($classorpackage, $this->parent->path, $this->parent->name, $element->name, $element->docblock->package, $element->docblock->subpackage);
                return $x;
            break;
            case 'page':
                if (empty($classorpackage)) $classorpackage = $GLOBALS['phpDocumentor_DefaultPackageName'];
                $x = new pageLink;
                $x->addLink($element->path,$element->name,$element->file,$classorpackage, $subpackage);
                return $x;
            break;
        }
    }
    
    function &getLink(&$c, $text = false)
    {
        $a = $c->getPageLink($this->parent->file, $this->docblock->package, $this->parent->path, $text);
        return $a;
    }
    
    /**
     * returns a list of all classes declared in a file
     * @param Converter &$c
     * @return array Format: array(packagename => parserClass,packagename => parserClass,...)
     */
    function getClasses(&$c)
    {
        $r = $c->classes->getClassesInPath($this->parent->path);
        $rr = array();
        if ($r)
        foreach($r as $class => $obj)
        {
            $rr[$obj->docblock->package][] = $obj;
        }
        return $rr;
    }
    
    /**
     * Get the output-safe filename (. changed to _)
     * @return string
     */
    function getName()
    {
        if (isset($this->parent) && $this->parent)
        return $this->parent->getName();
    }
    
    /**
     * @param parserPage parent element of this parsed data
     */
    function setParent(&$parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * @return bool returns the value of {@link $clean}
     */
    function isClean()
    {
        return $this->clean;
    }
    
    /**
     * @param parserDocBlock
     * @see parserDocBlock
     */
    function setDocBlock(&$docblock)
    {
        $this->docblock = $docblock;
    }
}

/**
 * Base class for all elements
 * @package phpDocumentor
 * @subpackage ParserData
 * @abstract
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id: ParserData.inc,v 1.6 2006/10/23 04:02:46 cellog Exp $
 */
class parserBase
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * always base
     * @var string
     */
    var $type = 'base';
    /**
     * set to different things by its descendants
     * @abstract
     * @var mixed
     */
    var $value = false;

    /**
     * @return string returns value of {@link $type}
     */
    function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed set the value of this element
     */
    function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * @return mixed get the value of this element (element-dependent)
     */
    function getValue()
    {
        return $this->value;
    }
}


/**
 * Used to represent strings that contain inline tags, so that they can be properly parsed at link time
 * @package phpDocumentor
 * @subpackage ParserData
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id: ParserData.inc,v 1.6 2006/10/23 04:02:46 cellog Exp $
 */
class parserStringWithInlineTags extends parserBase
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * always '_string'
     * @var string
     */
    var $type = '_string';
    /** @access private */
    var $cache = false;
    /**
     * array of strings and {@link parserInlineTag}s
     * Format:
     * array(string1,string2,parserInlineTag1,string3,parserInlineTag2,...)
     * @var array
     */
    var $value = array();

    /**
     * equivalent to the . operator ($a = $b . $c)
     * @param mixed either a string or a {@link parserInlineTag}
     */
    function add($stringOrInlineTag)
    {
        if (is_string($stringOrInlineTag))
        {
            if (!count($this->value))
            {
                $this->value[] = $stringOrInlineTag;
                return;
            }
            if (is_string($this->value[count($this->value) - 1]))
            {
                $this->value[count($this->value) - 1] .= $stringOrInlineTag;
                return;
            } else
            {
                $this->value[] = $stringOrInlineTag;
                return;
            }
        } else
        {
            if (is_a($stringOrInlineTag,'parserinlinetag') && phpDocumentor_setup::checkIgnoreTag($stringOrInlineTag->inlinetype, true)) return;
            $this->value[] = $stringOrInlineTag;
        }
    }
    
    /**
     * Determine whether the string contains any inline tags
     * @tutorial inlinetags.pkg
     * @return boolean
     */
    function hasInlineTag()
    {
        for($i=0;$i<count($this->value);$i++)
        {
            if (is_a($this->value[$i],'parserinlinetag')) return true;
        }
        return false;
    }
    
    /**
     * Pass source code to any {@}source} tags contained within the string
     * for later conversion.
     * @param string|array source code ready to be highlighted
     */
    function setSource($source)
    {
        for($i=0;$i<count($this->value);$i++)
        {
            if (phpDocumentor_get_class($this->value[$i]) == 'parsersourceinlinetag')
            {
                $this->value[$i]->setSource($source);
            }
        }
    }

    /**
     * equivalent to trim(strlen($string))
     * @return integer length of the string this object represents
     */
    function trimmedStrlen()
    {
        $a = 0;
        for($i=0;$i<count($this->value);$i++)
        {
            if (is_string($this->value[$i]))
            {
                if ($i == 0)
                {
                    $a += strlen(ltrim($this->value[$i]));
                } elseif ($i == count($this->value[$i]) - 1)
                {
                    $a += strlen(chop($this->value[$i]));
                }
            } else
            {
                $a += $this->value[$i]->Strlen();
            }
        }
        return $a;
    }
    
    /**
     * return the string unconverted (all inline tags are taken out - this
     * should only be used in pre-parsing to see if any other text
     * is in the string)
     * @uses parserInlineTag::getString() removes inline tag length, as it is
     *       indeterminate until conversion.
     * @return string trimmed value
     */
    function getString($trim = true)
    {
        $a = '';
        for($i=0; $i<count($this->value); $i++)
        {
            if (is_string($this->value[$i]))
            {
                $a .= $this->value[$i];
            } else
            {
                $a .= $this->value[$i]->getString();
            }
        }
        if ($trim) $a = trim($a);
        return $a;
    }
    
    /**
     * Use to convert the string to a real string with all inline tags parsed and linked
     * @see Converter::returnSee()
     * @param Converter
     * @param boolean true if one needs to postprocess
     * @param boolean false if the output should not be trimmed
     */
    function Convert(&$converter,$postprocess = true, $trim = true)
    {
        if ($this->cache)
        {
            if ($converter->name == $this->cache['name'] && $converter->outputformat == $this->cache['output'] && $converter->checkState($this->cache['state']) && $this->cache['postprocess'] === $postprocess) return $this->cache['contents'];
            if ($converter->name != $this->cache['name']) {
                $this->cache = false;
            }
        }
        if (is_string($this->value)) return $this->value;
        $a = '';
        for($i=0; $i<count($this->value); $i++)
        {
            if (is_string($this->value[$i]))
            {
                if ($postprocess && !method_exists($converter,'postProcess')) var_dump('a',$converter);
                if ($postprocess) $a .= $converter->postProcess($this->value[$i]);
                else $a .= $this->value[$i];
            } else
            {
                $a .= $this->value[$i]->Convert($converter, $postprocess);
            }
        }
        if ($trim) {
            $a = trim($a);
        }
        $this->cache = array('name' => $converter->name,'output' => $converter->outputformat, 'contents' => $a, 'state' => $converter->getState(), 'postprocess' => $postprocess);
        return $a;
    }
}

?>