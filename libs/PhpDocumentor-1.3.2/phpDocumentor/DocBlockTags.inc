<?php
/**
 * All abstract representations of DocBlock tags are defined
 * by the classes in this file
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
 * @subpackage DocBlockTags
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        parserDocBlock, parserInclude, parserPage, parserClass
 * @see        parserDefine, parserFunction, parserMethod, parserVar
 * @since      separate file since version 1.2
 */
/**
 * used to represent standard tags like @access, etc.
 * This class is aware of inline tags, and will automatically handle them
 * using inherited functions
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserTag extends parserStringWithInlineTags
{
    /**
     * Type is used by many functions to skip the hassle of if phpDocumentor_get_class($blah) == 'parserBlah'
     * always '_tag'
     * @var string
     */
    var $type = '_tag';
    /**
     * tag name (see, access, etc.)
     * @var string
     */
    var $keyword = '';
    
    /**
     * Set up the tag
     *
     * {@source}
     * @param string $keyword tag name
     * @param parserStringWithInlineTags $value
     * @param boolean whether to parse the $value for html tags
     */
    function parserTag($keyword, $value, $noparse = false)
    {
        $this->keyword = $keyword;
        if (!$noparse)
        {
            $parser = new parserDescParser;
            $parser->subscribe('*',$this);
            $parser->parse($value->value,true,'parserstringwithinlinetags');
        } else $this->value = $value;
    }
    
    /**
     * @param Converter
     * @see Converter
     */
    function Convert(&$converter)
    {
        if (is_array($this->value))
        {
            if (count($this->value) == 1)
            {
                reset($this->value);
                list(,$val) = each($this->value);
                $a = $val->Convert($converter);
                return $a;
            }
            $result = '';
            foreach($this->value as $val)
            {
                // this is only true if we processed the description in
                // the constructor
                if (phpDocumentor_get_class($val) == 'parserstringwithinlinetags')
                $result .= $converter->EncloseParagraph($val->Convert($converter));
                else
                $result .= $val->Convert($converter);
            }
            return $result;
        } else
        {
            $a = $this->value->Convert($converter);
            return $a;
        }
    }
    
    /**
     * Gets a count of the number of paragraphs in this
     * tag's description.
     *
     * Useful in determining whether to enclose the
     * tag in a paragraph or not.
     * @access private
     * @return integer
     */
    function _valueParagraphCount()
    {
    }
    
    /**
     * Called by the {@link parserDescParser} when processing a description.
     * @param integer not used
     * @param array array of {@link parserStringWithInlineTags} representing
     *              paragraphs in the tag description
     * @see parserTag::parserTag()
     */
    function HandleEvent($a,$desc)
    {
        $this->value = $desc;
    }
    
    /**
     * @return string returns the text minus any inline tags
     * @see parserStringWithInlineTags::getString()
     */
    function getString()
    {
        if (is_array($this->value))
        {
            $result = '';
            foreach($this->value as $val)
            {
                $result .= $val->getString();
            }
            return $result;
        } else return $this->value->getString();
    }
}

/**
 * This class represents the @name tag
 * @tutorial tags.name.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 */
class parserNameTag extends parserTag
{
    /**
     * tag name
     * @var string
     */
    var $keyword = 'name';
    
    /**
     * @param string not used
     * @param string name
     */
    function parserNameTag($name, $value)
    {
        $this->value = $value;
    }
    
    /**
     * @see parserStringWithInlineTags::Convert()
     * @param Converter
     * @return string converted value of the tag
     */
    function Convert(&$c)
    {
        return $this->value;
    }
}

/**
 * This class represents the @access tag
 * @tutorial tags.access.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 */
class parserAccessTag extends parserTag
{
    /**
     * tag name
     * @var string
     */
    var $keyword = 'access';
    
    /**
     * set to true if the returned tag has a value type of private, protected
     * or public, false otherwise
     * @var boolean
     */
    var $isvalid = false;
    
    /**
     * checks $value to make sure it is private, protected or public, otherwise
     * it's not a valid @access tag
     * @see $isvalid
     * @param parserStringWithInlineTags $value
     */
    function parserAccessTag($value)
    {
        if (!is_string($value))
        {
            if (is_object($value))
            {
                if (method_exists($value,'getstring'))
                {
                    $value = $value->getString();
                }
            }
        }
        switch(trim($value))
        {
            case 'private' :
            case 'public' :
            case 'protected' :
                $this->value = $value;
                $this->isvalid = true;
            break;
            default :
            addError(PDERROR_ACCESS_WRONG_PARAM,$value);
                $this->value = 'public';
            break;
        }
    }
    
    /**
     * @see parserStringWithInlineTags::Convert()
     * @param Converter
     * @return string converted value of the tag
     */
    function Convert(&$converter)
    {
        return $this->value;
    }
    
    /**
     * No inline tags are possible, returns 'public', 'protected' or 'private'
     * @return string returns the text minus any inline tags
     */
    function getString()
    {
        return $this->value;
    }
}

/**
 * represents "@return"
 * @tutorial tags.return.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserReturnTag extends parserTag
{
    /**
     * always 'return'
     * @var string
     */
    var $keyword = 'return';
    /**
     * the type a function returns
     */
    var $returnType = 'void';
    
    /**
     * contains a link to the documentation for a class passed as a type in @return, @var or @param
     *
     * Example:
     *
     * <code>
     * class myclass
     * {
     * ...
     * }
     * /** @return myclass blahblahblah
     * ...
     * </code>
     *
     * In this case, $converted_returnType will contain a link to myclass instead of the string 'myclass'
     * @var mixed either the same as $returnType or a link to the docs for a class
     * @see $returnType
     */
    var $converted_returnType = false;
    
    /**
     * @param string
     * @param parserStringWithInlineTags
     */
    function parserReturnTag($returnType, $value)
    {
        $this->returnType = $returnType;
        parent::parserTag('return',$value);
    }
    
    /**
     * sets up $converted_returnType
     * @see parserStringWithInlineTags::Convert(), $converted_returnType
     * @param Converter
     * @return string converted value of the tag
     */
    function Convert(&$converter)
    {
        $my_types = '';
        if (strpos($this->returnType,'|'))
        {
            $types = explode('|',$this->returnType);
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
            $this->converted_returnType = $my_types;
        } else
        {
            $a = $converter->getLink($this->returnType);
            if (is_object($a) && phpDocumentor_get_class($a) == 'classlink')
            {
                $this->converted_returnType = $converter->returnSee($a,$converter->type_adjust($this->returnType));
            } else
            {
                $this->converted_returnType = $converter->type_adjust($this->returnType);
            }
        }
        return parserTag::Convert($converter);
    }
}

/**
 * represents "@var"
 * @tutorial tags.var.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserVarTag extends parserReturnTag
{
    /**
     * always 'var'
     * @var string
     */
    var $keyword = 'var';
    /**
     * the type a var has
     * @var string
     */
    var $returnType = 'mixed';
}

/**
 * Represents "@param"
 * @tutorial tags.param.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 */
class parserParamTag extends parserVarTag
{
    /**
     * always 'param'
     * @var string
     */
    var $keyword = 'param';
}

/**
 * Represents "@staticvar"
 * @tutorial tags.staticvar.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 */
class parserStaticvarTag extends parserParamTag
{
    /**
     * always 'staticvar'
     * @var string
     */
    var $keyword = 'staticvar';
}

/**
 * represents "@link"
 * @tutorial tags.link.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserLinkTag extends parserTag
{
    /**
     * always 'link'
     * @var string
     */
    var $keyword = 'link';
    
    /**
     * URL to link to
     * @param string $link
     */
    function parserLinkTag($link)
    {
        $start = $val = $link->getString();
        if (strpos($val,' '))
        {
            $start = array_shift($val = explode(' ',$val));
            $val = join($val, ' ');
        }
        $a = new parserLinkInlineTag($start,$val);
        $b = new parserStringWithInlineTags;
        $b->add($a);
        $this->value = $b;
    }
}

/**
 * represents "@see"
 * @tutorial tags.see.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class parserSeeTag extends parserLinkTag
{
    /**
     * always 'see'
     * @var string
     */
    var $keyword = 'see';
    
    /**
     * @param string element to link to
     */
    function parserSeeTag($name)
    {
        parserTag::parserTag($this->keyword,$name,true);
    }

    /**
     * @param Converter
     * @see parserStringWithInlineTags::Convert()
     */
    function Convert(&$converter)
    {
        if ($this->value->hasInlineTag())
        {
            addErrorDie(PDERROR_INLINETAG_IN_SEE);
        }
        $a = $converter->getLink(trim($this->value->Convert($converter)));
        if (is_string($a))
        {
            // feature 564991
            if (strpos($a,'://'))
            {
                // php function
                return $converter->returnLink($a,str_replace('PHP_MANUAL#','',$this->value->Convert($converter)));
            }
            return $a;
        }
        if (is_object($a)) return $converter->returnSee($a);
        // getLink parsed a comma-delimited list of linked thingies, add the commas back in
        if (is_array($a))
        {
            $b = '';
            foreach($a as $i => $bub)
            {
                if (!empty($b)) $b .= ', ';
                if (is_string($a[$i])) $b .= $a[$i];
                if (is_object($a[$i])) $b .= $converter->returnSee($a[$i]);
            }
            return $b;
        }
        return false;
    }
}

/**
 * represents "@license"
 *
 * Link to a license, instead of including lines and lines of license information
 * in every file
 * @tutorial tags.license.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 */
class parserLicenseTag extends parserLinkTag
{
    /**
     * always 'license'
     * @var string
     */
    var $keyword = 'license';
    
    /**
     * URL to link to
     * @param string $link
     */
    function parserLicenseTag($name, $link)
    {
        $a = explode(' ',$link->getString());
        $url = array_shift($a);
        $license = join($a,' ');
        if (empty($license)) $license = $url;
        $a = new parserLinkInlineTag($url, $license);
        $b = new parserStringWithInlineTags;
        $b->add($a);
        $this->value = $b;
    }
}

/**
 * represents "@uses"
 *
 * This is exactly like @see except that the element used has a @useby link to this element added to its docblock
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @tutorial tags.uses.pkg
 * @since 1.2
 */
class parserUsesTag extends parserSeeTag
{
    /**
     * Always "uses"
     * @var string
     */
    var $keyword = 'uses';
    /** @access private */
    var $_description;
    
    /**
     * @param string element to link to
     * @param parserStringWithInlineTags description of how the element is used
     */
    function parserUsesTag($seeel, $description)
    {
        if ($seeel->hasInlineTag()) {
            addErrorDie(PDERROR_DUMB_USES);
        }
        parent::parserSeeTag($seeel);
        $this->_description = $description;
    }
    
    /**
     * Return a link to documentation for other element, and description of how
     * it is used
     *
     * Works exactly like {@link parent::Convert()} except that it also includes
     * a description of how the element used is used.
     * @return string
     * @param Converter
     */
    function Convert(&$c)
    {
        $val = $this->value;
        $see = parent::Convert($c);
        $this->value = $this->_description;
		  $desc_val = parserTag::Convert($c);
        if (!empty($desc_val)) {
           $see .= ' - '.$desc_val;
        }
        $this->value = $val;
        return $see;
    }
    
    /**
     * Get the text of the link to the element that is being used
     * @return string
     * @access private
     */
    function getSeeElement()
    {
        return $this->value->getString();
    }
    
    /**
     * Get the description of how the element used is being used.
     * @return parserStringWithInlineTags
     */
    function getDescription()
    {
        return $this->_description;
    }
}

/**
 * This is a virtual tag, it is created by @uses to cross-reference the used element
 *
 * This is exactly like @uses.
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @since 1.2
 */
class parserUsedByTag extends parserUsesTag
{
    /**
     * Always "usedby"
     * @var string
     */
    var $keyword = 'usedby';
    /** @access private */
    var $_link;
    
    /**
     * @param abstractLink link of element that uses this element
     * @param string description of how the element is used
     */
    function parserUsedByTag($link, $description)
    {
        $this->value = $description;
        $this->_link = $link;
    }
    
    /**
     * @return string
     * @param Converter
     */
    function Convert(&$c)
    {
        $see = $c->returnSee($this->_link);
		  $desc_val = parserTag::Convert($c);
        if (!empty($desc_val)) {
           $see .= ' - '.$desc_val;
        }
        return $see;
    }
}

/**
 * represents "@tutorial"
 *
 * This is exactly like @see except that it only links to tutorials
 * @tutorial phpDocumentor/tutorials.pkg
 * @tutorial tags.tutorial.pkg
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @since 1.2
 */
class parserTutorialTag extends parserSeeTag
{
    /**
     * Always "tutorial"
     * @var string
     */
    var $keyword = 'tutorial';
    /**
     * @param Converter
     * @see parserStringWithInlineTags::Convert()
     */
    function Convert(&$converter)
    {
        $a = $converter->getTutorialLink(trim($this->value->Convert($converter)));
        if (is_string($a))
        {
            return $a;
        }
        if (is_object($a)) return $converter->returnSee($a);
        // getLink parsed a comma-delimited list of linked thingies, add the commas back in
        if (is_array($a))
        {
            $b = '';
            foreach($a as $i => $bub)
            {
                if (!empty($b)) $b .= ', ';
                if (is_string($a[$i])) $b .= $a[$i];
                if (is_object($a[$i])) $b .= $converter->returnSee($a[$i]);
            }
            return $b;
        }
        return false;
    }
}

/**
 * represents "@filesource"
 * 
 * Use this to create a link to a highlighted phpxref-style source file listing
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @tutorial tags.filesource.pkg
 */
class parserFileSourceTag extends parserTag
{
    /**
     * Always "filesource"
     * @var string
     */
    var $keyword = 'filesource';
    /** @var array */
    var $source;
    /** @var string */
    var $path;
    /**
     * Flag variable, controls double writes of file for each converter
     * @access private
     * @var array
     */
    var $_converted = array();
    /**
     * Set {@link $source} to $value, and set up path
     * @param string
     * @param array output from {@link phpDocumentorTWordParser::getFileSource()}
     */
    function parserFileSourceTag($filepath, $value)
    {
        parent::parserTag($this->keyword, '');
        $this->path = $filepath;
        $this->source = $value;
    }

    /**
     * Return a link to the highlighted source and generate the source
     * @uses ConvertSource() generate source code and write it out
     * @return string output from {@link getSourceLink()}
     * @param Converter
     */
    function Convert(&$c)
    {
        $this->ConvertSource($c);
        return $this->getSourceLink($c);
    }

    /**
     * @param Converter
     * @uses phpDocumentor_HighlightParser highlights source code
     * @uses writeSource()
     */
    function ConvertSource(&$c)
    {
        $this->writeSource($c, $c->ProgramExample($this->source, true, false, false, false, $this->path));
        return;
        $parser = new phpDocumentor_HighlightParser;
        $return = '';
        $return = $parser->parse($this->source,$c, false, false, false, $this->path);
        $this->writeSource($c, $return);
    }

    /**
     * @param Converter
     * @param string highlighted source code
     * @uses Converter::writeSource() export highlighted file source
     */
    function writeSource(&$c, $source)
    {
        $c->writeSource($this->path, $source);
    }

    /**
     * @uses Converter::getSourceLink()
     * @param Converter
     * @return output from getSourceLink()
     */
    function getSourceLink(&$c)
    {
        return $c->getSourceLink($this->path);
    }
}

/**
 * represents "@example"
 * 
 * The example tag 
 * @package phpDocumentor
 * @subpackage DocBlockTags
 * @tutorial tags.example.pkg
 */
class parserExampleTag extends parserFileSourceTag
{
    /**
     * always "example"
     * @var string
     */
    var $keyword = 'example';
    /**
     * Reads and parses the example file indicated
     *
     * The example tag takes one parameter: the full path to a php file that
     * should be parsed and included as an example.
     * @uses phpDocumentorTWordParser::getFileSource() uses to parse an example
     *       and retrieve all tokens by line number
     * @param parserStringWithInlineTags
     * @param string path of file containing this @example tag
     */
    function parserExampleTag($value, $current_path)
    {
        global $_phpDocumentor_setting;
        parent::parserTag('example', $value);
        $path = false;
        // code thanks to Sam Blum, modified by Greg Beaver
        $tagValue = $value->getString();
        $path = $isAbsPath = $pathOnly = $fileName = $fileExt = $original_path  = $title = FALSE;
        do
        {
            // make sure the format is stuff.ext description
            if (!preg_match('`(.*)\.(\w*)\s(.*)`', $tagValue, $match))
            {
                // or format is stuff.ext
                if (!preg_match('`(.*)\.(\w*)\s*$`', $tagValue, $match))
                {
                    // Murphy: Some funny path was given
                    $original_path = $tagValue; // used for error output
                    break; // try-block
                }
            }
            if (strlen($match[1]) === 0)
            {
                // Murphy: Some funny path was given
                $original_path = $tagValue; // used for error output
                break; // try-block
            }
            $fileExt = $match[2];
            $title = 'example';
            if (isset($match[3]))
            {
                $title = trim($match[3]);
            }
            $pathTmp = str_replace('\\', '/', $match[1]); // Replace windows '\' the path.

            // Is there a path and a file or is it just a file?
            if (strpos($pathTmp,'/') === false)
            {
                // No path part
                $pathOnly = '';
                $fileName = $pathTmp .'.'. $fileExt;
            } else
            {
                $splitPos = strrpos($pathTmp,'/'); // split the path on the last directory, find the filename
                $pathOnly = substr($match[1], 0, $splitPos+1);
                $fileName = substr($match[1], $splitPos+1) .'.'. $fileExt;
                // Is the path absolute? (i.e. does it start like an absolute path?)
                if (('/' === $pathTmp[0]) || preg_match('`^\w*:`i', $pathTmp))
                { // works for both windows 'C:' and URLs like 'http://'
                    $isAbsPath = true; // Yes
                }
            }

            $original_path = $pathOnly . $fileName;

            // Now look for the file starting with abs. path.
            if ($isAbsPath)
            {
                $tmp = realpath($original_path); // remove any weirdities like /../file.ext
                if ($tmp && is_file($tmp))
                {
                    $path = $tmp;
                }
                // Alway break if abs. path was detected; even if file was not found.
                break; // try-block
            }

            // Search for the example file some standard places 
            // 1) Look if the ini-var examplesdir is set and look there ...
            if (isset($_phpDocumentor_setting['examplesdir']))
            {
                $tmp = realpath($_phpDocumentor_setting['examplesdir'] . PATH_DELIMITER  . $original_path);
                if ($tmp && is_file($tmp))
                {
                    $path = $tmp; // Yo! found it :)
                    break; // try-block
                }
            }

            // 2) Then try to look for an 'example/'-dir below the *currently* parsed file ...
            if (!empty($current_path))
            {
                $tmp = realpath(dirname($current_path) . PATH_DELIMITER . 'examples' . PATH_DELIMITER . $fileName);
                if ($tmp && is_file($tmp))
                {
                    $path = $tmp; // Yo! found it :)
                    break; // try-block
                }
            }

            // 3) Then try to look for the example file below the subdir PHPDOCUMENTOR_BASE/examples/ ...
            if (is_dir(PHPDOCUMENTOR_BASE . PATH_DELIMITER . 'examples'))
            {
                $tmp = realpath(PHPDOCUMENTOR_BASE . PATH_DELIMITER . 'examples' . PATH_DELIMITER . $original_path);
                if ($tmp && is_file($tmp))
                {
                    $path = $tmp; // Yo! found it :)
                    break; // try-block
                }
            }

            $tmp = realpath(PHPDOCUMENTOR_BASE . PATH_DELIMITER . $original_path);
            if ($tmp && is_file($tmp))
            {
                $path = $tmp; // Yo! found it :)
                break; // try-block
            }
            // If we reach this point, nothing was found and $path is false.
        } while (false);

        if (!$path)
        {
            addWarning(PDERROR_EXAMPLE_NOT_FOUND, $original_path);
            $this->_title = 'example not found';
            $this->path = false;
        } else
        {
            $this->_title = ($title) ? $title : 'example';
            // make a unique html-filename but avoid it to get too long.
            $uniqueFileName = str_replace(array(':', DIRECTORY_SEPARATOR,'/'), array('_', '_', '_'), $path);
            $uniqueFileName = substr($uniqueFileName,-50) . '_' . md5($path);
            $this->path = $uniqueFileName;
            
            $f = @fopen($path,'r');
            if ($f)
            {
                $example = fread($f,filesize($path));
                if (tokenizer_ext)
                {
                    $obj = new phpDocumentorTWordParser;
                    $obj->setup($example);
                    $this->source = $obj->getFileSource();
                    $this->origsource = $example;
                    unset($obj);
                } else
                {
                    $this->source = $example;
                }
            }
        }
    }
    
    /**
     * @param Converter
     * @uses phpDocumentor_HighlightParser highlights source code
     * @uses writeSource()
     */
    function ConvertSource(&$c)
    {
        $this->writeSource($c, $c->ProgramExample($this->source, true, null,
                            null, null, null, $this->origsource));
        return;
        $parser = new phpDocumentor_HighlightParser;
        $return = '';
        $return = $parser->parse($this->source,$c);
        $this->writeSource($c, $return);
    }

    /**
     * @param Converter $c
     * @param string parsed example source
     * @uses Converter::writeExample() writes final example out
     * @access private
     */
    function writeSource(&$c, $source)
    {
        if ($this->path)
        $c->writeExample($this->_title, $this->path, $source);
    }

    /**
     * Retrieve a converter-specific link to the example
     *
     * @param Converter
     * @uses Converter::getExampleLink() retrieve the link to the example
     */
    function getSourceLink(&$c)
    {
        if (!$this->path) return $this->_title;
        return $c->getExampleLink($this->path, $this->_title);
    }
}

?>
