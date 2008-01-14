<?php
/**
 * Parser Elements, all classes representing documentable elements
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
 * @subpackage ParserElements
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        Parser, WordParser
 * @since      1.1
 */

/**
 * all elements except {@link parserPackagePage} descend from this abstract class
 * @abstract
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserElement extends parserBase
{
    /**
     * @var mixed either false or a {@link parserDocBlock}
     */
    var $docblock = false;
    /**
     * name of this element, or include type if element is a {@link parserInclude}
     */
    var $name;
    
    /**
     * @var mixed either false or an array of paths to files with conflicts
     */
    var $conflicts = false;
    
    /**
     * location of this element (filename)
     * @var string
     */
    var $file = '';
    
    /**
     * full path location of this element (filename)
     * @var string
     */
    var $path = '';
    
    /**
     * line number on file where this element stops
     * @since 1.2
     * @var false|integer
     */
    var $endlinenumber = 0;
    
    /**
     * Line number in the source on which this element appears
     * @since 1.2
     * @var false|integer
     */
    var $linenumber = false;
    
    /**
     * @param parserDocBlock
     */
    function setDocBlock($docblock)
    {
        $this->docblock = $docblock;
    }
    
    /**
     * @param string
     */
    function setName($name)
    {
        $this->name = trim($name);
    }
    
    /**
     * Set starting line number
     * @param integer
     */
    function setLineNumber($number)
    {
        $this->linenumber = $number;
    }
    
    /**
     * Sets the ending line number of elements
     * @param integer
     */
    function setEndLineNumber($l)
    {
        $this->endlinenumber = $l;
    }
    
    /**
     * @return integer
     */
    function getLineNumber()
    {
        return $this->linenumber;
    }
    
    /**
     * @return integer
     */
    function getEndLineNumber()
    {
        return $this->endlinenumber;
    }
    
    /** @return string package containing this element */
    function getPackage()
    {
        if ($this->docblock)
        {
            return $this->docblock->package;
        } else return $GLOBALS['phpDocumentor_DefaultPackageName'];
    }
    
    /** @param string */
    function setFile($file)
    {
        $this->file = $file;
    }
    
    /** @param string */
    function setPath($file)
    {
        // look for special windows case
        if(SMART_PATH_DELIMITER === '\\')
            $this->path = strtr($file,'/','\\');
        else
            $this->path = $file;
        $this->path = $file;
    }
    
    /**
     * @return string
     */
    function getName()
    {
        if (!isset($this->name)) return false;
        return $this->name;
    }
    
    /**
     * @return string
     */
    function getFile()
    {
        if (!isset($this->file)) return false;
        return $this->file;
    }
    
    /**
     * @return string
     */
    function getPath()
    {
        if (!isset($this->path)) return false;
        return $this->path;
    }
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserInclude extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'include'
     */
    var $type = 'include';
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.1
 * @version $Id$
 */
class parserGlobal extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'global'
     */
    var $type = 'global';
    
    /**
     * Name of the global's data type
     * @var string
     */
    var $datatype = 'mixed';

    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this global variable
     * @param Converter
     * @param string text to display for the link or false for default text
     */
    function getLink(&$c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink('global ' . $this->name, $this->docblock->package);
        }
        return $c->getGlobalLink($this->name, $this->docblock->package, $this->path, $text);
    }

    /**
     * Returns all global variables in other packages that have the same name as this global variable
     * @return mixed false or an array Format: (package => {@link parserGlobal} of conflicting global variable)
     * @param Converter
     */
    function getConflicts(&$c)
    {
        $a = $c->proceduralpages->getGlobalConflicts($this->name);
        unset($a[$this->docblock->package]);
        return $a;
    }
    
    /**
     * Sets the name of the global variable's type
     * @param string
     */
    function setDataType($type)
    {
        $this->datatype = $type;
    }
    
    /**
     * Retrieve converter-specific representation of the data type
     *
     * If the data type is a documented class name, then this function will
     * return a Converter-specific link to that class's documentation, so users
     * can click/browse to the documentation directly from the global variable
     * declaration
     * @return string
     * @param Converter
     */
    function getDataType(&$converter)
    {
        $converted_datatype = $this->datatype;
        if (strpos($this->datatype,'|'))
        {
            $my_types = '';
            $types = explode('|',$this->datatype);
            foreach($types as $returntype)
            {
                $a = $converter->getLink($returntype);
                if (is_object($a) && phpDocumentor_get_class($a) == 'classlink')
                {
                    if (!empty($my_types)) $my_types .= '|';
                    $my_types .= $converter->returnSee($a,$converter->type_adjust($returntype));
                } else
                {
                    if (!empty($my_types)) $my_types .= '|';
                    $my_types .= $converter->type_adjust($returntype);
                }
            }
            $converted_datatype = $my_types;
        } else
        {
            $a = $converter->getLink($this->datatype);
            if (is_object($a) && phpDocumentor_get_class($a) == 'classlink')
            {
                $converted_datatype = $converter->returnSee($a,$converter->type_adjust($this->datatype));
            } else
            {
                $converted_dataype = $converter->type_adjust($this->datatype);
            }
        }
        return $converted_datatype;
    }

}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserFunction extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'function'
     */
    var $type = 'function';
    /**
     * parameters parsed from function definition.
     *
     * param name may be null, in which case, updateParams() must be called from the Converter
     * @var array Format: array(param name => default value parsed from function definition)
     * @see updateParams()
     */
    var $params = false;
    /**
     * Function returns a reference to an element, instead of a value
     *
     * set to true if function is declared as:
     * <code>
     * function &func(...
     * </code>
     * @var boolean
     */
    var $returnsreference = false;
    /**
     * global declarations parsed from function definition
     *
     * @var array Format: array(globalname1, globalname2,....)
     */
    var $globals = false;
    /**
     * static variable declarations parsed from function definition
     * @var array Format: array(array('name' => staticvar1,'val' => '' or default val of staticvar1),...)
     */
    var $statics = false;
    
    var $source = '';
    
    /**
     * @param string
     * @param string default value parsed from function definition
     * @param boolean indicates whether this parameter has a default value
     * @param null|string class type hint
     */
    function addParam($name, $value, $has_default = true, $typehint = null)
    {
        $this->params[$name] = array($value, $has_default);
        if (isset($typehint))
        {
            $this->params[$name][2] = $typehint;
        }
    }
    
    /**
     * Set the source code.  Always array in PHP 4.3.0+
     * @param string|array
     */
    function addSource($source)
    {
        $this->source = $source;
    }
    
    /**
     * Determine whether the source code has been requested via {@}source}
     * @return boolean
     */
    function hasSource()
    {
        if (is_array($this->source)) return true;
        return strlen($this->source);
    }
    
    /**
     * @return string|array source code ready for highlighting
     */
    function getSource()
    {
        return $this->source;
    }
    
    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this function
     * @param Converter
     * @param string text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink('function ' . $this->name, $this->docblock->package);
        }
        return $c->getFunctionLink($this->name, $this->docblock->package, $this->path, $text);
    }

    /**
     * Returns all functions in other packages that have the same name as this function
     * @return mixed false or an array Format: (package => {@link parserFunction} of conflicting functions)
     * @param Converter
     */
    function getConflicts(&$c)
    {
        $a = $c->proceduralpages->getFuncConflicts($this->name);
        unset($a[$this->docblock->package]);
        return $a;
    }
    
    /**
     * Add all "global $var, $var2" declarations to this function
     * @param array $globals Format: array(globalname1, globalname2,....)
     */
    function addGlobals($globals)
    {
        $this->globals = $globals;
    }

    /**
     * Add all "static $var, $var2 = 6" declarations to this function
     * @param array Format: array(varname1, varname2,...)
     * @param array Format: array(default val of var 1, default val of var 2,...) if var 1 has no default, array(default val of var 2,...)
     */
    function addStatics($static,$vals)
    {
        if (count($static))
        {
            $this->statics = array();
            for($i=0;$i<count($static);$i++)
            {
                if (isset($static[$i]))
                {
                    $a = '';
                    if (isset($vals[$i])) $a = $vals[$i];
                    $this->statics[] = array('name' => $static[$i],'val' => $a);
                }
            }
        }
    }

    /**
     * @return string default value of param $name
     * @param string
     */
    function getParam ($name)
    {
        if (!isset($this->params[$name])) return false;
        $test = $this->params[$name];
        if ($test[1])
        {
            return $this->params[$name];
        } else
        {
            return false;
        }
    }

    /**
     * @return array format: array(array(paramname, default value),...)
     */
    function listParams ()
    {
        if (isset($this->params))
        {
            $ret = array();
            if ($this->params)
            foreach($this->params as $key => $val)
            {
                if ($val[1])
                {
                    $arr = array($key,$val[0]);
                    if (isset($val[2]))
                    {
                        $arr[2] = $val[2];
                    }
                    $ret[$key] = $arr;
                } else
                {
                    $arr = array($key,false);
                    if (isset($val[2]))
                    {
                        $arr[2] = $val[2];
                    }
                    $ret[$key] = $arr;
                }
            }
            return $ret;
        } else {
            return array();
        }
    }
    
    /**
     * @return array format: array(array(index, globalname),...)
     */
    function listGlobals ()
    {
        if (isset($this->globals))
        {
            $ret = array();
            if ($this->globals)
            foreach($this->globals as $key => $val)
            {
                $ret[] = array($key,$val);
            }
            return $ret;
        } else {
            return array();
        }
    }
    
    /**
     * @return array format: array(array(static var name, static var default value),...)
     */
    function listStatics ()
    {
        if (isset($this->statics))
        {
            $ret = array();
            if ($this->statics)
            foreach($this->statics as $key => $val)
            {
                $ret[] = array($val['name'],$val['val']);
            }
            return $ret;
        } else {
            return array();
        }
    }
    
    /**
     * sets {@link $returnsreference} to true
     */
    function setReturnsReference()
    {
        $this->returnsreference = true;
    }
    
    /**
     * @return boolean returns value of {@link $returnsreference}
     */
    function getReturnsReference()
    {
        return $this->returnsreference;
    }
    
    /**
     * Get a human-friendly description of the function call
     *
     * takes declaration like:
     * <code>
     * /** @returns string ... {rest of docblock}
     * function &func($param1, $param2 = 6,
     *                $param3 = array('20',9 => "heroo"))
     * {...}
     * </code>
     * and returns:
     * string &func( $param1, [$param2 = 6], [$param3 = array('20',9 => "heroo")] )
     * @return string stylized function declaration
     */
    function getFunctionCall()
    {
        $a = '';
        if ($this->getReturnsReference()) $a = '&';
        $function_call = $a.$this->getName() . " ( ";
        $tmp = 0;
        foreach($this->listParams() as $param)
        {
            if ($tmp == 0)
            {
                $tmp = 1;
            } else {
                $function_call .= ", ";
            }
            if ($param[1] !== false)
            {
                $function_call .= "[$param[0] = $param[1]]";
            } else {
                $function_call .= $param[0];
            }
            $update_params[] = $param[0];
        }
        $function_call .= " )";
        return $function_call;
    }
    
    /**
     * Like getFunctionCall(), but has no English or pre-determined formatting.
     *
     * Much more flexible.
     * @return array Format:
     * <code>
     * array('name' => function name,
     *       'returnsref' => boolean if declared as "function &name()"
     *       'params' => array('type' => data type of parameter,
     *                         'description' => from @param tag,
     *                         'name' => variable name,
     *                         'default' => default value if any))
     * </code>
     * @see getFunctionCall()
     */
    function getIntricateFunctionCall($converter,$paramtags)
    {
        $a = array();
        if ($this->getReturnsReference()) $a['returnsref'] = true;
        $a['name'] = $converter->type_adjust($this->getName());
        $c = $this->listParams();
        foreach($c as $param)
        {
            $b = array();
            $b['type'] = 'mixed';
            if (isset($paramtags[$param[0]]))
            {
                $b['type'] = $paramtags[$param[0]]['datatype'];
                $b['description'] = $paramtags[$param[0]]['data'];
                unset($paramtags[$param[0]]);
            } elseif(isset($paramtags[substr($param[0],1)]))
            {
                $b['type'] = $paramtags[substr($param[0],1)]['datatype'];
                $b['description'] = $paramtags[substr($param[0],1)]['data'];
                unset($paramtags[substr($param[0],1)]);
            }
            if (isset($param[2]))
            {
                $link = $converter->getLink('object ' . $param[2]);
                if ($link) {
                    $link = $converter->returnSee($link, $param[2], true);
                    $b['type'] = $link;
                } else {
                    $b['type'] = $param[2];
                }
            }
            $b['name'] = $param[0];
            $b['default'] = $converter->postProcess($param[1]);
            $b['hasdefault'] = ($param[1] !== false);
            $a['params'][] = $b;
        }
        // @param tags that don't correspond to actual parameters (like extra function values)
        if (count($paramtags))
        {
            foreach($paramtags as $param)
            {
                $b = array();
                $b['type'] = $param['datatype'];
                $b['description'] = $param['data'];
                $b['name'] = $param['var'];
                $b['default'] = '';
                $b['hasdefault'] = false;
                $a['params'][] = $b;
            }
        }
        return $a;
    }
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserClass extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'class'
     */
    var $type = 'class';
    /** @var string
     * @see parserPage::$sourceLocation */
    var $sourceLocation = '';
    /**
     * @var mixed false or contents of extends clause in class declaration
     */
    var $extends = false;
    /**
     * @var array a list of interfaces this class implements
     */
    var $_implements = array();
    /**
     * @var array a list of interfaces this class implements
     * @access private
     */
    var $_modifiers = false;
    /**
     * @var boolean determines whether a class is considered to be an interface
     * @access private
     */
    var $_isInterface = false;
    /**
     * Format: array(file, parent) where parent class is found or false if no parent
     * @var mixed
     */
    var $parent = false;
    /**
     * Used to determine whether a class should be ignored or not.  Helps maintain integrity of parsing
     * @var boolean
     * @see Classes::getParentClass()
     */
    var $ignore = false;

    /**
     * @var string same as {@link parserElement::$path}
     */
    var $curfile = false;
    /**
     * @var tutorialLink|false either a link to the tutorial associated with this class, or false
     */
    var $tutorial = false;
    
    /**
     * Get the PHP5+ modifiers for this class
     * (abstract/final/static/private/protected/public)
     * @return array|false
     */
    function getModifiers()
    {
        return $this->_modifiers;
    }

    /**
     * Set the PHP5+ modifiers for this class
     * (abstract/final/static/private/protected/public)
     * @param string $m
     */
    function setModifiers($m)
    {
        $this->_modifiers = $m;
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
     * Get the associated tutorial for this class, if any
     * @tutorial tutorials.pkg
     * @return parserTutorial
     */
    function getTutorial()
    {
        return $this->tutorial;
    }
    
    /**
     * Returns all classes in other packages that have the same name as this class
     * @return mixed false or an array Format: (package => {@link parserClass} of conflicting classes)
     * @param Converter
     */
    function getConflicts(&$c)
    {
        $a = $c->classes->getConflicts($this->name);
        unset($a[$this->docblock->package]);
        return $a;
    }
    
    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this class
     * @param Converter
     * @param string text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink('object ' . $this->name, $this->docblock->package);
        }
        return $c->getClassLink($this->name, $this->docblock->package, $this->curfile, $text);
    }

    /**
     * @param string parent class name
     * @param string parent class file
     * @param Classes {@link Classes} object currently calling setParent
     * @see Classes::setClassParent()
     */
    
    function setParent($p,$f, &$c)
    {
        $this->parent = array($f, $p);
        $p = $c->getClass($p, $f);
        // inherit package if no @package tag is in the docblock, fixes 591396
        if (!$this->docblock->getExplicitPackage())
        {
            $this->docblock->package = $p->docblock->package;
        }
        if ($this->docblock->package == $p->docblock->package)
        {
            if ($this->docblock->subpackage == '')
            $this->docblock->subpackage = $p->docblock->subpackage;
        }
        $author = $p->docblock->getKeyword('author');
        $version = $p->docblock->getKeyword('version');
        $copyright = $p->docblock->getKeyword('copyright');
        // inherit tags
        if (!$this->docblock->getKeyword('author'))
        {
            if ($author && !is_array($author)) $author = array($author);
            if ($author) $this->docblock->tags['author'] = $author;
        }
        if (!$this->docblock->getKeyword('version'))
        {
            if ($version && !is_array($version)) $version = array($version);
            if ($version) $this->docblock->tags['version'] = $version;
        }
        if (!$this->docblock->getKeyword('copyright'))
        {
            if ($copyright && !is_array($copyright)) $copyright = array($copyright);
            if ($copyright) $this->docblock->tags['copyright'] = $copyright;
        }
        if (!$this->docblock->sdesc)
        {
            $this->docblock->setShortDesc($p->docblock);
            $this->docblock->setDesc($p->docblock);
        } else
        {
            if ($this->docblock->hasInheritDoc())
            {
                $this->docblock->replaceInheritDoc($p->docblock);
            }
        }
    }
    
    /**
     * @param string $par parent class name (used by {@link Classes::setClassParent()} if parent class not found
     */
    function setParentNoClass($par)
    {
        $this->parent = $par;
    }
    
    /**
     * Use this method to set the type of class to be an interface
     */
    function setInterface()
    {
        $this->_isInterface = true;
    }
    
    /**
     * @return boolean true if this is an interface class
     */
    function isInterface()
    {
        return $this->_isInterface;
    }
    
    /**
     * Use this method to set access modifiers for a class
     * @param array 
     */
    function setAccessModifiers($modifiers)
    {
        $this->_modifiers = $modifiers;
    }
    
    /**
     * retrieve object that represents the parent class
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return mixed returns the {@link parserClass} representation of the parent class, or false if no parent class
     */
    function &getParent(&$c)
    {
        $a = false;
        if (!$this->parent) return $a;
        if (is_array($this->parent))
        {
            return $c->classes->getClass($this->parent[1],$this->parent[0]);
        } else return $this->parent;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of method objects
     */
    function getMethods(&$c)
    {
        return $c->classes->getMethods($this->name,$this->curfile);
    }
    
    /**
     * @return mixed {@link parserMethod} or false if not found
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param string method name in this class
     * @param boolean determines whether to search inherited methods as well
     */
    function getMethod(&$c, $name, $inherited = false)
    {
        $ret = $c->classes->getMethod($this->name, $this->curfile, $name);
        if ($ret) return $ret;
        if ($inherited) {
            $x = $this;
            while ($x->parent && is_array($x->parent)) {
                $par = $x->getParent($c);
                $x = $par;
                if ($meth = $x->getMethod($c, $name)) return $meth;
            }
        }
        return false;
    }
    
    /**
     * @return mixed {@link parserVar} or false if not found
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param string var name in this class
     */
    function getVar(&$c, $name)
    {
        return $c->classes->getVar($this->name,$this->curfile,$name);
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of method name strings
     */
    function getMethodNames(&$c)
    {
        if (!$c->classes->hasMethods($this->curfile, $this->name)) return array();
        $arr = array();
        $arr1 = $this->getMethods($c);
        for($i=0; $i < count($arr1); $i++)
        {
            $arr[] = $arr1[$i]->name;
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param string method name
     * @param boolean determines whether to search inherited methods as well
     * @return boolean whether this class has a method of name $name
     */
    function hasMethod(&$c, $name, $inherited = false)
    {
        $ret = $c->classes->hasMethod($this->name, $this->curfile, $name);
        if ($ret) return $ret;
        if ($inherited) {
            $x = $this;
            while ($x->parent && is_array($x->parent)) {
                $par = $x->getParent($c);
                $x = $par;
                if ($x->hasMethod($c, $name)) return true;
            }
        }
        return false;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param string var name
     * @return boolean whether this class has a var of name $name
     */
    function hasVar(&$c,$name)
    {
        return $c->classes->hasVar($this->name, $this->curfile, $name);
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param string class constant name
     * @return boolean whether this class has a constant of name $name
     */
    function hasConst(&$c,$name)
    {
        return $c->classes->hasConst($this->name, $this->curfile, $name);
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of var objects
     */
    function getVars(&$c)
    {
        return $c->classes->getVars($this->name,$this->curfile);
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of const objects
     */
    function getConsts(&$c)
    {
        return $c->classes->getConsts($this->name,$this->curfile);
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of var name strings
     */
    function getVarNames(&$c)
    {
        if (!$c->classes->hasVars($this->curfile, $this->name)) return array();
        $arr = array();
        $arr1 = $this->getVars($c);
        for($i=0; $i < count($arr1); $i++)
        {
            $arr[] = $arr1[$i]->name;
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array returns a simple array of const name strings
     */
    function getConstNames(&$c)
    {
        if (!$c->classes->hasConsts($this->curfile, $this->name)) return array();
        $arr = array();
        $arr1 = $this->getConsts($c);
        for($i=0; $i < count($arr1); $i++)
        {
            $arr[] = $arr1[$i]->name;
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param boolean determines whether overriden methods should be included in the list of inherited methods
     * @return array returns an array of methods by parent classname array(name => array(method1,method2..),name2 => array(method1....))
     */
    function getInheritedMethods(&$c,$override = false)
    {
        $x = $oldx = $this;
        $methods = array();
        $arr = array();
        while ($x->parent && is_array($x->parent))
        {
            $methods = array_merge($methods,$x->getMethodNames($c));
            $par = $x->getParent($c);
            $parmethodnames = $par->getMethodNames($c);
            $parmethods = $par->getMethods($c);
            for($i=0; $i<count($parmethodnames); $i++)
            {
                if ($override)
                {
                    if (!in_array($parmethodnames[$i],$methods))
                    {
                        // fix for bug 587733
                        if ($parmethods[$i]->docblock && $parmethods[$i]->docblock->hasaccess && !$c->parseprivate && $parmethods[$i]->docblock->tags['access'][0]->value == 'private')
                        {
                            continue;
                        }
                        $methods[] = $parmethodnames[$i];
                        $arr[$par->getName()]['methods'][] = $parmethods[$i];
                        $arr[$par->getName()]['file'] = $par->curfile;
                    }
                } else
                {
                    // fix for bug 587733
                    if ($parmethods[$i]->docblock && $parmethods[$i]->docblock->hasaccess && !$c->parseprivate && $parmethods[$i]->docblock->tags['access'][0]->value == 'private')
                    {
                        continue;
                    }
                    $arr[$par->getName()]['methods'][] = $parmethods[$i];
                    $arr[$par->getName()]['file'] = $par->curfile;
                }
            }
            $oldx = $x;
            $x = &$par;
        }
        if (is_a($oldx, 'parserClass') && is_a($oldx->getExtends(true), 'ReflectionClass')) {
            $extends = $oldx->getExtends(true);
            foreach ($extends->getMethods() as $method) {
                $var = new parserMethod($oldx->getExtends());
                if ($method->returnsReference()) {
                    $var->setReturnsReference();
                }
                $doc = new parserDocBlock;
                foreach ($method->getParameters() as $param) {
                    $value = $param->isDefaultValueAvailable() ? var_export($param->getDefaultValue(), true) : null;
                    if ($param->isPassedByReference()) {
                        $var->addParam('&$' . $param->getName(), $value, $param->isOptional(),
                            $param->getClass());
                    } else {
                        $var->addParam('$' . $param->getName(), $value, $param->isOptional(),
                            $param->getClass());
                    }
                }
                $var->setName($method->getName());
                $doc->addPackage('package', $oldx->getPackage());
                $var->setDocBlock($doc);
                $par = $method->getDeclaringClass();
                $var->setLineNumber($par->getStartLine());
                $modifiers = array();
                if ($method->isPrivate()) {
                    $modifiers[] = 'private';
                }
                if ($method->isAbstract()) {
                    $modifiers[] = 'abstract';
                }
                if ($method->isFinal()) {
                    $modifiers[] = 'final';
                }
                if ($method->isProtected()) {
                    $modifiers[] = 'protected';
                }
                if ($method->isPublic()) {
                    $modifiers[] = 'public';
                }
                if ($method->isStatic()) {
                    $modifiers[] = 'static';
                }
                if ($method->isConstructor()) {
                    $var->setConstructor();
                }
                $var->setModifiers($modifiers);
                $arr[$oldx->getExtends()]['methods'][] = $var;
                $arr[$oldx->getExtends()]['file'] = '(internal)';
            }
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param boolean determines whether overriden vars should be included in the list of inherited vars
     * @return array returns an array of vars by parent classname array(name => array(var1,var1..),name2 => array(var1....))
     */
    function getInheritedVars(&$c,$override = true, $vars = false)
    {
        $x = $oldx = $this;
        $vars = array();
        $arr = array();
        while ($x->parent && is_array($x->parent))
        {
            $vars = array_merge($vars,$x->getVarNames($c));
            $par = $x->getParent($c);
            $parvarnames = $par->getVarNames($c);
            $parvars = $par->getVars($c);
            for($i=0; $i<count($parvarnames); $i++)
            {
                if ($override)
                {
                    if (!in_array($parvarnames[$i],$vars))
                    {
                        // fix for bug 587733
                        if ($parvars[$i]->docblock && $parvars[$i]->docblock->hasaccess && !$c->parseprivate && $parvars[$i]->docblock->tags['access'][0]->value == 'private')
                        {
                            continue;
                        }
                        $vars[] = $parvarnames[$i];
                        $arr[$par->getName()]['vars'][] = $parvars[$i];
                        $arr[$par->getName()]['file'] = $par->curfile;
                    }
                } else
                {
                    // fix for bug 587733
                    if ($parvars[$i]->docblock && $parvars[$i]->docblock->hasaccess && !$c->parseprivate && $parvars[$i]->docblock->tags['access'][0]->value == 'private')
                    {
                        continue;
                    }
                    $arr[$par->getName()]['vars'][] = $parvars[$i];
                    $arr[$par->getName()]['file'] = $par->curfile;
                }
            }
            $oldx = $x;
            $x = &$par;
        }
        if (is_a($oldx, 'parserClass') && is_a($oldx->getExtends(true), 'ReflectionClass')) {
            $extends = $oldx->getExtends(true);
            foreach ($extends->getProperties() as $property) {
                $var = new parserVar($oldx->getExtends());
                $doc = new parserDocBlock;
                $var->setName('$' . $property->getName());
                $doc->addPackage('package', $oldx->getPackage());
                $par = $property->getDeclaringClass();
                $var->setLineNumber($par->getStartLine());
                $modifiers = array();
                if ($property->isPrivate()) {
                    $modifiers[] = 'private';
                    $doc->addAccess('private');
                }
                if ($property->isProtected()) {
                    $modifiers[] = 'protected';
                    $doc->addAccess('protected');
                }
                if ($property->isPublic()) {
                    $modifiers[] = 'public';
                    $doc->addAccess('public');
                }
                $var->setDocBlock($doc);
                $var->setModifiers($modifiers);
                $arr[$oldx->getExtends()]['vars'][] = $var;
                $arr[$oldx->getExtends()]['file'] = '(internal)';
            }
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @param boolean determines whether overriden vars should be included in the list of inherited vars
     * @return array returns an array of consts by parent classname array(name => array(const1,const2..),name2 => array(const1....))
     */
    function getInheritedConsts(&$c,$override = false, $consts = false)
    {
        $x = $oldx = $this;
        $consts = array();
        $arr = array();
        while ($x->parent && is_array($x->parent))
        {
            $consts = array_merge($consts,$x->getConstNames($c));
            $par = $x->getParent($c);
            $parvarnames = $par->getConstNames($c);
            $parvars = $par->getConsts($c);
            for($i=0; $i<count($parvarnames); $i++)
            {
                if ($override)
                {
                    if (!in_array($parvarnames[$i],$consts))
                    {
                        // fix for bug 587733
                        if ($parvars[$i]->docblock && $parvars[$i]->docblock->hasaccess && !$c->parseprivate && $parvars[$i]->docblock->tags['access'][0]->value == 'private')
                        {
                            continue;
                        }
                        $consts[] = $parvarnames[$i];
                        $arr[$par->getName()]['consts'][] = $parvars[$i];
                        $arr[$par->getName()]['file'] = $par->curfile;
                    }
                } else
                {
                    // fix for bug 587733
                    if ($parvars[$i]->docblock && $parvars[$i]->docblock->hasaccess && !$c->parseprivate && $parvars[$i]->docblock->tags['access'][0]->value == 'private')
                    {
                        continue;
                    }
                    $arr[$par->getName()]['consts'][] = $parvars[$i];
                    $arr[$par->getName()]['file'] = $par->curfile;
                }
            }
            $oldx = $x;
            $x = &$par;
        }
        if (is_a($oldx, 'parserClass') && is_a($oldx->getExtends(true), 'ReflectionClass')) {
            $extends = $oldx->getExtends(true);
            if (!$extends->getConstants()) {
                return $arr;
            }
            foreach ($extends->getConstants() as $property => $value) {
                $var = new parserConst($oldx->getExtends());
                $doc = new parserDocBlock;
                $var->setName($property);
                $var->setValue(var_export($value, true));
                $doc->addPackage('package', $oldx->getPackage());
                $var->setLineNumber($extends->getStartLine());
                $var->setDocBlock($doc);
                $arr[$oldx->getExtends()]['consts'][] = $var;
                $arr[$oldx->getExtends()]['file'] = '(internal)';
            }
        }
        return $arr;
    }
    
    /**
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array Format: array(parentclassname => parserClass/false if no parent, parentclassname2 => ...)
     */
    function getParentClassTree(&$c)
    {
        $result = array();
        $result[$this->name] = $arr = $this->getParent($c);
        if (is_string($arr)) $result[$arr] = false;
        while ($arr && is_object($arr))
        {
            $result[$arr->name] = $arr->getParent($c);
            $arr = $arr->getParent($c);
            if (is_string($arr)) $result[$arr] = false;
        }
        return $result;
    }
    
    /**
     * returns a list of all child classes of this class
     * @param Converter this function will not work before the Conversion stage of parsing
     * @return array Format: array(parserClass child1,parserClass child2,...)
     */
    function getChildClassList(&$c)
    {
        $list = array();
        $kids = $c->classes->getDefiniteChildren($this->name,$this->curfile);
        if ($kids)
        {
            foreach($kids as $chile => $file)
            {
                $list[] = $c->classes->getClass($chile,$file);
            }
        }
        return $list;
    }
    
    /**
     * @param string
     * @see $sourceLocation
     */
    function setSourceLocation($sl)
    {
        $this->sourceLocation = $sl;
    }
    
    /**
     * @param Converter
     * @param boolean
     * @return string
     * @see $sourceLocation
     */
    function getSourceLocation($c,$pearize = false)
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
                return $sl;
            }
            return $sl;
        }
        return $this->sourceLocation;
    }
    
    /**
     * @param string
     * @see $extends
     */
    function setExtends($extends)
    {
        $this->extends = $extends;
        if (!class_exists('ReflectionClass') || !class_exists($extends)) {
            return;
        }
        // this may throw an exception.  Hopefully it won't if the class exists
        $parent = new ReflectionClass($extends);
        if (!$parent->isInternal()) {
            return;
        }
        $this->extends = $parent;
    }
    
    /**
     * @param string
     */
    function addImplements($implements)
    {
        $this->_implements[] = $implements;
    }
    
    /**
     * @return array
     */
    function getImplements()
    {
        return $this->_implements;
    }
    
    /**
     * @return boolean
     * @see $extends
     */
    function getExtends($raw = false)
    {
        if (!isset($this->extends)) return false;
        if (!$raw) {
            if (is_a($this->extends, 'ReflectionClass')) {
                return $this->extends->getName();
            }
        }
        return $this->extends;
    }
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserVar extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'var'
     */
    var $type = 'var';
    /** @var string class that contains this var */
    var $class = '';
    /** @var array */
    var $_modifiers;
    
    /**
     * @param string
     */
    function parserVar($class)
    {
        $this->class = $class;
    }
    
    /**
     * Retrieve the class name
     * @return string Class name that this var belongs to
     */
    function getClass()
    {
        return $this->class;
    }
    
    /**
     * Return a list of access modifiers (static/private/etc.)
     * @return array
     */
    function getModifiers()
    {
        return $this->_modifiers;
    }
    
    /**
     * Return name of the class that contains this method
     * @return string
     */
    function setModifiers($m)
    {
        $this->_modifiers = $m;
    }

    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this var
     * @param Converter $c
     * @param string $text text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink($this->class . '::' . $this->name, $this->docblock->package);
        }
        return $c->getVarLink($this->name, $this->class, $this->docblock->package, false, $text);
    }

    /**
     * @param Converter
     * @return mixed {@link parserVar} representing var this var overrides from the parent class, or false if none
     */
    function getOverrides(&$c)
    {
        $class = $c->classes->getClass($this->class,$this->path);
        $par = $class->getParent($c);
        
        if (!is_object($par)) {
            if (is_a($class->getExtends(true), 'ReflectionClass')) {
                $pare = $class->getExtends(true);
                if (method_exists($pare, 'hasProperty') &&
                      $pare->hasProperty(substr($this->name, 1))) {
                    $par = $pare;
                    $property = $par->getProperty(substr($this->name, 1));
                    $ret = new parserVar($par->getName());
                    $doc = new parserDocBlock;
                    $ret->setName('$' . $property->getName());
                    $doc->addPackage('package', $class->getPackage());
                    $ret->setLineNumber($par->getStartLine());
                    $modifiers = array();
                    if ($property->isPrivate()) {
                        if ($c->parseprivate) {
                            return false;
                        }
                        $modifiers[] = 'private';
                        $doc->addAccess('private');
                    }
                    if ($property->isProtected()) {
                        $modifiers[] = 'protected';
                        $doc->addAccess('protected');
                    }
                    if ($property->isPublic()) {
                        $modifiers[] = 'public';
                        $doc->addAccess('public');
                    }
                    $ret->setDocBlock($doc);
                    $ret->setModifiers($modifiers);
                    return $ret;
                }
            }
        }
        while (is_object($par))
        {
            if ($par->hasVar($c,$this->name))
            {
                $var = $par->getVar($c,$this->name);
                if (!($var->docblock && $var->docblock->hasaccess &&
                      !$c->parseprivate && $var->docblock->tags['access'][0]->value == 'private')) {
                    return $var;
                }
            }
            $par = $par->getParent($c);
        }
        
        return false;
    }

    /**
     * @param Converter
     * @return array an array of parserVars from ALL child classes that override this var
     */
    function getOverridingVars(&$c)
    {
        $class = $c->classes->getClass($this->class,$this->path);

				return $this->getOverridingVarsForClass($c, $class);
    }

    /**
     * @param Converter
		 * @param parserClass
     * @return array an array of parserVars from ALL child classes that override this var in the given class
     */
		function getOverridingVarsForClass(&$c, &$class)
		{
			$vars = array();
			if (!$class) return $meths;
			$kids = $class->getChildClassList($c);
			for($i=0; $i<count($kids); $i++)
			{
					if ($kids[$i]->hasVar($c, $this->name))
					{
						$var = $kids[$i]->getVar($c,$this->name);
						if (!($var->docblock && $var->docblock->hasaccess && !$c->parseprivate && $var->docblock->tags['access'][0]->value == 'private'))
							$vars[] = $var;
					}

					$vars = array_merge($vars, $this->getOverridingVarsForClass($c, $kids[$i]));
			}
			return $vars;
		}
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.2.4
 */
class parserConst extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'const'
     */
    var $type = 'const';
    /** @var string class that contains this var */
    var $class = '';
    
    /**
     * @param string
     */
    function parserConst($class)
    {
        $this->class = $class;
    }
    
    /**
     * Retrieve the class name
     * @return string Class name that this var belongs to
     */
    function getClass()
    {
        return $this->class;
    }

    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this var
     * @param Converter $c
     * @param string $text text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink($this->class . '::'. $this->name, $this->docblock->package);
        }
        return $c->getConstLink($this->name, $this->class, $this->docblock->package, false, $text);
    }
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserMethod extends parserFunction
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'method'
     */
    var $type = 'method';
    /** @var boolean whether this method is a constructor */
    var $isConstructor = false;
    /** @var boolean whether this method is a destructor by PEAR standards */
    var $isDestructor = false;
    /** @var string class that contains this method */
    var $class = '';
    var $_modifiers = array();
    
    /**
     * @param string
     */
    function parserMethod($class)
    {
        $this->class = $class;
    }
    
    /**
     * @param string
     * @param string default value parsed from function definition
     * @param boolean indicates whether this parameter has a default value
     * @param null|string class type hint
     */
    function addParam($name, $value, $has_default = true, $typehint = null)
    {
        $this->params[$name] = array($value, $has_default);
        if (isset($typehint))
        {
            $this->params[$name][2] = $typehint;
        }
    }
    
    /**
     * adds "constructor " to start of function call if {@link $isConstructor} is true
     * @return string
     * @see parent::getFunctionCall()
     */
    function getFunctionCall()
    {
        $a = parserFunction::getFunctionCall();
        if ($this->isConstructor) $a = "constructor $a";
        return $a;
    }
    
    function getIntricateFunctionCall($converter,$paramtags)
    {
        $a = parserFunction::getIntricateFunctionCall($converter,$paramtags);
        if ($this->isConstructor) $a['constructor'] = true;
        if ($this->isDestructor) $a['destructor'] = true;
        return $a;
    }
    
    /**
     * Return name of the class that contains this method
     * @return string
     */
    function getClass()
    {
        return $this->class;
    }
    
    /**
     * Return name of the class that contains this method
     * @return string
     */
    function getModifiers()
    {
        return $this->_modifiers;
    }
    
    /**
     * Return name of the class that contains this method
     * @return string
     */
    function setModifiers($m)
    {
        $this->_modifiers = $m;
    }
    
    /**
     * @param Converter
     * @return mixed {@link parserMethod} representing method this method
     * overrides from the parent class, or false if none
     */
    function getOverrides(&$c)
    {
        $class = $c->classes->getClass($this->class,$this->path);
        
        $par = $class->getParent($c);
        if (!is_object($par)) {
            if (is_a($class->getExtends(true), 'ReflectionClass')) {
                $pare = $class->getExtends(true);
                if (method_exists($pare, 'hasMethod') && $pare->hasMethod($this->name)) {
                    $par = $pare;
                    $method = $par->getMethod($this->name);
                    $var = new parserMethod($par->getName());
                    if ($method->returnsReference()) {
                        $var->setReturnsReference();
                    }
                    $doc = new parserDocBlock;
                    foreach ($method->getParameters() as $param) {
                        $value = ($param->isOptional() && !$method->isInternal()) ? var_export($param->getDefaultValue(), true) : null;
                        if ($param->isPassedByReference()) {
                            $var->addParam('&$' . $param->getName(), $value, $param->isOptional(),
                                $param->getClass());
                        } else {
                            $var->addParam('$' . $param->getName(), $value, $param->isOptional(),
                                $param->getClass());
                        }
                    }
                    $var->setName($method->getName());
                    $doc->addPackage('package', $this->getPackage());
                    $par = $method->getDeclaringClass();
                    $var->setLineNumber($par->getStartLine());
                    $modifiers = array();
                    if ($method->isPrivate()) {
                        $modifiers[] = 'private';
                        $doc->addAccess('private');
                    }
                    $blank = new parserStringWithInlineTags;
                    if ($method->isAbstract()) {
                        $modifiers[] = 'abstract';
                        $doc->addKeyword('abstract', $blank);
                    }
                    if ($method->isFinal()) {
                        $modifiers[] = 'final';
                        $doc->addKeyword('final', $blank);
                    }
                    if ($method->isProtected()) {
                        $modifiers[] = 'protected';
                        $doc->addAccess('protected');
                    }
                    if ($method->isPublic()) {
                        $modifiers[] = 'public';
                        $doc->addAccess('public');
                    }
                    if ($method->isStatic()) {
                        $modifiers[] = 'static';
                        $doc->addKeyword('static', $blank);
                    }
                    if ($method->isConstructor()) {
                        $var->setConstructor();
                    }
                    $var->setDocBlock($doc);
                    $var->setModifiers($modifiers);
                    return $var;
                }
            }
        }

        while (is_object($par))
        {
            if ($par->hasMethod($c,$this->name))
            {
                $meth = $par->getMethod($c,$this->name);
                if (!($meth->docblock && $meth->docblock->hasaccess &&
                      !$c->parseprivate && $meth->docblock->tags['access'][0]->value == 'private')) {
                    return $meth;
                }
            }
            
            $par = $par->getParent($c);
        }
        
        return false;
    }
    /**
     * @param Converter
     * @return mixed {@link parserMethod} representing method this method implements
     * from an interface, or false if none
     */
    function getImplements(&$c)
    {
        $class = $c->classes->getClass($this->class,$this->path);
        
        $implements = $class->getImplements();
        if (!count($implements)) {
            return false;
        }
        $ret = array();
        $haveAlready = array();
        foreach ($implements as $interface) {
            $interface_link = $c->getLink('object ' . $interface);
            if (is_a($interface_link, 'classlink')) {
                $par = $c->classes->getClass($interface_link->name,
                    $interface_link->path);
                if (is_object($par)) {
                    if ($par->hasMethod($c, $this->name, true))
                    {
                        $meth = $par->getMethod($c, $this->name);
                        if (!$meth) {
                            $meth = $par->getMethod($c, $this->name, true);
                        }
                        if (!($meth->docblock && $meth->docblock->hasaccess &&
                              !$c->parseprivate && $meth->docblock->tags['access'][0]->value == 'private')) {
                            if (isset($haveAlready[$meth->getClass()])) {
                                // this ensures extended interfaces don't cause
                                // 2 links to the same method
                                if ($haveAlready[$meth->getClass()] == $this->name) {
                                    continue;
                                }
                            }
                            $ret[] = $meth;
                            $haveAlready = array($meth->getClass() => $this->name);
                        }
                    }
                }
                continue;
            }
            if (class_exists('ReflectionClass')) {
                if (interface_exists($interface)) {
                    $info = new ReflectionClass($interface);
                    if (method_exists($info, 'hasMethod') && $info->hasMethod($this->name)) {
                        $par = $info;
                        $method = $par->getMethod($this->name);
                        $var = new parserMethod($par->getName());
                        if ($method->returnsReference()) {
                            $var->setReturnsReference();
                        }
                        $doc = new parserDocBlock;
                        foreach ($method->getParameters() as $param) {
                            $value = $param->isOptional() ? var_export($param->getDefaultValue(), true) : null;
                            if ($param->isPassedByReference()) {
                                $var->addParam('&$' . $param->getName(), $value, $param->isOptional(),
                                    $param->getClass());
                            } else {
                                $var->addParam('$' . $param->getName(), $value, $param->isOptional(),
                                    $param->getClass());
                            }
                        }
                        $var->setName($method->getName());
                        $doc->addPackage('package', $this->getPackage());
                        $par = $method->getDeclaringClass();
                        $var->setLineNumber($par->getStartLine());
                        $modifiers = array();
                        if ($method->isPrivate()) {
                            $modifiers[] = 'private';
                            $doc->addAccess('private');
                        }
                        $blank = new parserStringWithInlineTags;
                        if ($method->isAbstract()) {
                            $modifiers[] = 'abstract';
                            $doc->addKeyword('abstract', $blank);
                        }
                        if ($method->isFinal()) {
                            $modifiers[] = 'final';
                            $doc->addKeyword('final', $blank);
                        }
                        if ($method->isProtected()) {
                            $modifiers[] = 'protected';
                            $doc->addAccess('protected');
                        }
                        if ($method->isPublic()) {
                            $modifiers[] = 'public';
                            $doc->addAccess('public');
                        }
                        if ($method->isStatic()) {
                            $modifiers[] = 'static';
                            $doc->addKeyword('static', $blank);
                        }
                        if ($method->isConstructor()) {
                            $var->setConstructor();
                        }
                        $var->setDocBlock($doc);
                        $var->setModifiers($modifiers);
                        $ret[] = $var;
                        continue;
                    }
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this method
     * @param Converter $c
     * @param string $text text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink($this->class . '::' . $this->name . '()', $this->docblock->package);
        }
        return $c->getMethodLink($this->name, $this->class, $this->docblock->package, false, $text);
    }

    /**
     * Use this method to tell the parser that this method is the class constructor
     */
    function setConstructor()
    {
        $this->isConstructor = true;
    }
    
    /**
     * Use this method to tell the parser that this method is the class constructor
     */
    function setDestructor()
    {
        $this->isDestructor = true;
    }
    
    /**
     * @param Converter
     * @return array an array of parserMethods from child classes that override this method
     */
    function getOverridingMethods(&$c)
    {
        $class = $c->classes->getClass($this->class,$this->path);

				return $this->getOverridingMethodsForClass($c, $class);
    }

    /**
     * @param Converter
		 * @param parserClass
     * @return array an array of parserMethods from ALL child classes that override this method in the given class
     */
		function getOverridingMethodsForClass(&$c, &$class)
		{
			$meths = array();
			if (!$class) return $meths;
			$kids = $class->getChildClassList($c);
			for($i=0; $i<count($kids); $i++)
			{
					if ($kids[$i]->hasMethod($c, $this->name))
					{
						$meth = $kids[$i]->getMethod($c,$this->name);
						if (!($meth->docblock && $meth->docblock->hasaccess && !$c->parseprivate && $meth->docblock->tags['access'][0]->value == 'private'))
							$meths[] = $meth;
					}

					$meths = array_merge($meths, $this->getOverridingMethodsForClass($c, $kids[$i]));
			}
			return $meths;
		}
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserDefine extends parserElement
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'define'
     */
    var $type = 'define';

    /**
     * quick way to link to this element
     * @return mixed converter-specific link to this define
     * @param Converter $c
     * @param string $text text to display for the link or false for default text
     */
    function getLink($c, $text = false, $returnobj = false)
    {
        if ($returnobj)
        {
            return $c->getLink('constant ' . $this->name, $this->docblock->package);
        }
        return $c->getDefineLink($this->name, $this->docblock->package, false, $text);
    }

    /**
     * Returns all defines in other packages that have the same name as this define
     * @return mixed false or an array Format: (package => {@link parserDefine} of conflicting defines)
     * @param Converter
     */
    function getConflicts(&$c)
    {
        $a = $c->proceduralpages->getDefineConflicts($this->name);
        unset($a[$this->docblock->package]);
        return $a;
    }
    
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserPackagePage extends parserStringWithInlineTags
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'packagepage'
     */
    var $type = 'packagepage';
    /** @var string */
    var $package = 'default';
    
    /**
     * @param string
     */
    function parserPackagePage($package)
    {
        $this->package = $package;
    }
    
    /**
     * @param Converter
     */
    function Convert(&$c)
    {
        return parent::Convert($c,false);
    }
}

/**
 * @package phpDocumentor
 * @subpackage ParserElements
 * @since 1.2
 */
class parserTutorial extends parserPackagePage
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * @var string always 'tutorial'
     */
    var $type = 'tutorial';
    /** @var string */
    var $package = 'default';
    /**
     * Either cls, pkg, or proc
     * @var string
     */
    var $tutorial_type;
    /**
     * The documentable element this tutorial is linked to
     *
     * Can be a parserData, parserClass, or nothing for package/subpackage docs
     */
    var $linked_element;
    /**
     * path to the tutorial page
     * @var string
     */
    var $path;
    /**
     * filename minus extension of this tutorial (used for @tutorial tag)
     * @var string
     */
    var $name;
    /** @var boolean */
    var $_xml = true;
    /**
     * output from tutorialname.ext.ini
     *
     * an array generated by {@link phpDocumentor_parse_ini_file()} containing
     * an index 'Linked Tutorials' with an array of tutorial names in the order
     * they should appear.  This is used to generate a linked list of tutorials like
     * {@tutorial phpDocumentor/tags.pkg}
     * @var array
     */
    var $ini = false;
    /**
     * link to the next tutorial in a document series, or false if none
     * @var tutorialLink
     */
    var $next = false;
    /**
     * link to the previous tutorial in a document series, or false if none
     * @var tutorialLink
     */
    var $prev = false;
    /**
     * link to the parent tutorial in a document series, or false if none
     *
     * This is used to generate an "Up" or "Home" link like the php manual.
     * The parent is defined as a tutorial that has a parenttutorialname.ext.ini
     * file and is not contained by any other tutorial's tutorialname.ext.ini
     * @var tutorialLink
     */
    var $parent = false;
    /**
     * links to the child tutorials, or false if none
     * @var array
     */
    var $children = false;
    
    /**
     * @param parserXMLDocBookTag top-level tag (<refentry> for 1.2.0)
     * @param information about the tutorial file.  Format:
     *
     * <pre>
     * array('tutename' => tutorial name,
     *       'path' => relative path of tutorial to tutorials/ directory
     *       'ini' => contents of the tutorial .ini file, if any)
     * </pre>
     */
    function parserTutorial($data, $info)
    {
        $this->value = $data;
        $this->package = $info['package'];
        $this->subpackage = $info['subpackage'];
        $this->tutorial_type = $info['tutetype'];
        $this->name = $info['tutename'];
        $this->path = $info['path'];
        $this->ini = $info['ini'];
    }
    
    /**
     * Retrieve the title of the tutorial, or of any subsection
     * @param Converter
     * @param string which subsection to retrieve the title from, if any
     * @uses parserXMLDocBookTag::getSubSection() retrieve the subsection to
     *       to get a title from
     */
    function getTitle(&$c,$subsection = '')
    {
        if (!empty($subsection))
        {
            $z = $this->value->getSubSection($c,$subsection);
            if (!$z)
            {
                addWarning(PDERROR_TUTORIAL_SUBSECTION_NOT_FOUND,$this->name,$subsection);
                return $subsection;
            }
            return $z->getTitle($c);
        }
        return $this->value->getTitle($c);
    }
    
    /**
     * @param Converter
     * @param boolean determines whether character data is postprocessed to be
     *                Converter-friendly or not.
     */
    function Convert(&$c, $postprocess = true)
    {
        return $this->value->Convert($c, $postprocess);
    }
    
    /**
     * @uses $parent creates a link to the documentation for the parent tutorial
     * @param parserTutorial
     * @param Converter
     */
    function setParent($parent,&$c)
    {
        $this->parent = new tutorialLink;
        $this->parent->addLink('', $parent->path, $parent->name, $parent->package, $parent->subpackage, $parent->getTitle($c));
    }
    
    /**
     * Determine if this parserTutorial object is a child of another
     * 
     * WARNING:  This method can enter an infinite loop when run on PHP v5.2.1...
     * see {@link http://bugs.php.net/bug.php?id=40608 PHP Bug #40608}
     * and {@link http://pear.php.net/bugs/bug.php?id=10289 PEAR Bug #10289}
     * @param array $parents array of parserTutorials that have child tutorials
     * @return boolean whether or not this tutorial is a child of the any of the parents
     */
    function isChildOf($parents)
    {
        // avoid infinite loop PHP bug #40608 in PHP v5.2.1, see PEAR #10289
        checkForBugCondition('5.2.1', '40608', '10289');

        foreach($parents as $i => $parent)
        {
            if ($parent->path == $this->path) continue;
            if ($parent->ini && ($parent->package == $this->package) && ($parent->subpackage == $this->subpackage) && ($parent->tutorial_type == $this->tutorial_type))
            {
                foreach($parent->ini['Linked Tutorials'] as $child)
                {
                    if ($child . '.' . $this->tutorial_type == $this->name) return true;
                }
            }
        }
    }
    
    /**
     * Retrieve converter-specific link to the parent tutorial's documentation
     * @param Converter
     */
    function getParent(&$c)
    {
        if (!$this->parent) return false;
        return $c->returnSee($this->parent);
    }
    
    /**
     * @uses $next creates a link to the documentation for the next tutorial
     * @param parserTutorial
     * @param Converter
     */
    function setNext($next,&$c)
    {
        if (phpDocumentor_get_class($next) == 'tutoriallink') return $this->next = $next;
        $this->next = new tutorialLink;
        $this->next->addLink('', $next->path, $next->name, $next->package, $next->subpackage, $next->getTitle($c));
    }
    
    /**
     * Retrieve converter-specific link to the next tutorial's documentation
     * @param Converter
     */
    function getNext(&$c)
    {
        if (!$this->next) return false;
        return $c->returnSee($this->next);
    }
    
    /**
     * @uses $prev creates a link to the documentation for the previous tutorial
     * @param parserTutorial
     * @param Converter
     */
    function setPrev($prev,&$c)
    {
        if (phpDocumentor_get_class($prev) == 'tutoriallink') return $this->prev = $prev;
        $this->prev = new tutorialLink;
        $this->prev->addLink('', $prev->path, $prev->name, $prev->package, $prev->subpackage, $prev->getTitle($c));
    }
    
    /**
     * Retrieve converter-specific link to the previous tutorial's documentation
     * @param Converter
     */
    function getPrev(&$c)
    {
        if (!$this->prev) return false;
        return $c->returnSee($this->prev);
    }
    
    /**
     * Get a link to this tutorial, or to any subsection of this tutorial
     * @param Converter
     * @param boolean if true, returns a {@link tutorialLink} instead of a string
     * @param string section name to link to
     * @return string|tutorialLink
     */
    function getLink(&$c,$pure = false,$section = '')
    {
        $link = new tutorialLink;
        $link->addLink($section, $this->path, $this->name, $this->package, $this->subpackage, $this->getTitle($c), $this->category);
        if ($pure) return $link;
        return $c->returnSee($link);
    }
}

?>