<?php
/**
 * All abstract representations of inline tags are in this file
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
 * @subpackage InlineTags
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      separate file since 1.2
 */
/**
 * Use this element to represent an {@}inline tag} like {@}link}
 * @see parserStringWithInlineTags
 * @package phpDocumentor
 * @subpackage InlineTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Revision: 1.6 $
 * @tutorial inlinetags.pkg
 */
class parserInlineTag extends parserBase
{
    /**
     * Element type
     *
     * Type is used by many functions to skip the hassle of
     *
     * <code>
     * if phpDocumentor_get_class($blah) == 'parserBlah'
     * </code>
     * always "inlinetag"
     * @var string
     */
    var $type = 'inlinetag';
    /**
     * the name of the inline tag (like link)
     * @var string
     */
    var $inlinetype = '';
    
    /**
     * @param string $type tag type (example: link)
     * @param string $value tag value (example: what to link to)
     */
    function parserInlineTag($type,$value)
    {
        $this->inlinetype = $type;
        $this->value = trim($value);
    }
    
    /**
     * @return integer length of the tag
     */
    function Strlen()
    {
        // fix 1203451
        if (is_array($this->value))
        {
            return array_reduce(create_function('$a,$b', 'return $a + strlen($b);')) +
                count($this->value);
        }
        return strlen($this->value);
    }
    
    /**
     * @return string always '', used by {@link Parser::handleDocBlock()} to
     *                calculate the short description of a DocBlock
     * @see parserStringWithInlineTags::getString()
     * @see parserStringWithInlineTags::trimmedStrlen()
     */
    function getString()
    {
        return '';
    }
}

/**
 * represents inline links
 * @tutorial tags.inlinelink.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 */
class parserLinkInlineTag extends parserInlineTag
{
    /**
     * text to display in the link, can be different from the link for standard
     * links like websites
     * @var string
     */
    var $linktext = '';
    
    /**
     * @param string $link stored in $value, see {@link parserBase::$value}
     * @param string $text see {@link $linktext}
     */
    function parserLinkInlineTag($link,$text)
    {
        if (strpos($link, ','))
        {
            $link = explode(',',$link);
            parserInlineTag::parserInlineTag('link','');
            $this->value = $link;
        } else
        {
            parserInlineTag::parserInlineTag('link',$link);
        }
        $this->linktext = trim($text);
    }
    
    /**
     * @param Converter converter used to change the abstract link into text for
     *                  display
     * @return false|string returns the converted link or false if not converted
     *                      successfully
     */
    function Convert(&$c)
    {
        if (is_array($this->value))
        {
            $ret = '';
            foreach($this->value as $text)
            {
                if (!empty($ret))
                {
                    $ret .= ', ';
                }
                $ret .= $this->ConvertPart($c, trim($text));
            }
            return $ret;
        } else
        {
            return $this->ConvertPart($c, $this->value);
        }
    }
    
    function ConvertPart(&$c, $value)
    {
        if (strpos($value,'://') || (strpos($value,'mailto:') === 0))
        {
            if (strpos($value, ' '))
            {
                $value = explode(' ', $value);
                $link = array_shift($value);
                $text = join(' ', $value);
            } else
            {
                $link = $value;
                $text = $this->linktext;
            }
            return $c->returnLink($link,htmlspecialchars($text));
        } else
        {
            $savevalue = $value;
            $descrip = false;
            if (strpos(trim($value),' '))
            {
                $v = preg_split('/\s/',trim($value));
                if (in_array(strtolower($v[0]), array('object', 'function')))
                {
                    if (!isset($v[1]) ||
                        (isset($v[1]) && strlen($v[1])
                            && !in_array($v[1]{0}, array('$','&'))
                            && $v[1] != '###commanana####'))
                    {
                        $vsave = $v[0];
                        array_shift($v);
                        $v[0] = $vsave . ' ' . $v[0];
                    }
                }
                $value = $c->getLink($v[0]);
                array_shift($v);
                $descrip = join($v,' ');
                $descrip = str_replace('###commanana####', ',', $descrip);
            } else
            {
                $value = $c->getLink($value);
            }
            if (is_string($value))
            {
                // feature 564991
                if (strpos($value,'://'))
                {
                    // php function
                    return $c->returnLink($value,
                        $descrip ? $descrip : str_replace('PHP_MANUAL#','',$value));
                }
                return $value;
            }
            if (!$descrip) $descrip = $c->type_adjust($savevalue);
            if (is_object($value)) return $c->returnSee($value, $descrip);
/*            // getLink parsed a comma-delimited list of linked thingies, add the commas back in
            if (is_array($value))
            {
                $a = '';
                foreach($value as $i => $bub)
                {
                    if (!empty($a)) $a .= ', ';
                    if (is_string($value[$i]))
                    {
                        // feature 564991
                        if (strpos($value[$i],'://'))
                        {
                            // php function
                            $a .= $c->returnLink($value[$i],str_replace('PHP_MANUAL#','',$vals[$i]));
                        } else
                        $a .= $value[$i];
                    }
                    if (is_object($value[$i])) $a .= $c->returnSee($value[$i],$descrip[$i]);
                }
                return $a;
            } */
            return $savevalue;
        }
    }
}

/**
 * Represents inline links to external tutorial documentation
 * @tutorial tags.inlinetutorial.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserTutorialInlineTag extends parserLinkInlineTag
{
    /**
     * @param string $link stored in $value, see {@link parserBase::$value}
     * @param string $text see {@link $linktext}
     */
    function parserTutorialInlineTag($link,$text)
    {
        parserInlineTag::parserInlineTag('tutorial',$link);
        $this->linktext = trim($text);
    }

    /**
     * @param Converter converter used to change the abstract link into text for display
     * @return mixed returns the converted link or false if not converted successfully
     */
    function Convert(&$c)
    {
        $descrip = false;
        if (strpos($this->value,',') === false)
        {
            if (strpos(trim($this->value),' '))
            {
                $v = split(' ',trim($this->value));
                $value = $c->getTutorialLink($v[0]);
                array_shift($v);
                $descrip = join($v,' ');
            } else $value = $c->getTutorialLink($this->value);
        } else
        {
            $vals = split(',',$this->value);
            $descrip = array();
            foreach($vals as $val)
            {
                $val = trim($val);
                if (strpos($val,' '))
                {
                    $v = split(' ',$val);
                    $value[] = $c->getTutorialLink($v[0]);
                    array_shift($v);
                    $descrip[] = join($v,' ');
                } else
                {
                    $value[] = $c->getTutorialLink($val);
                    $descrip[] = false;
                }
            }
        }
        if (is_string($value))
        {
            return $value;
        }
        if (is_object($value)) return $c->returnSee($value,$descrip);
        // getLink parsed a comma-delimited list of linked thingies, add the commas back in
        if (is_array($value))
        {
            $a = '';
            foreach($value as $i => $bub)
            {
                if (!empty($a)) $a .= ', ';
                if (is_string($value[$i]))
                {
                    $a .= $value[$i];
                }
                if (is_object($value[$i])) $a .= $c->returnSee($value[$i],$descrip[$i]);
            }
            return $a;
        }
        return false;
    }
}

/**
 * represents inline source tag, used for function/method source
 * @tutorial tags.inlinesource.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserSourceInlineTag extends parserInlineTag
{
    /**
     * always 'source'
     * @var string
     */
    var $inlinetype = 'source';
    /**
     * First line of source code to display
     * @var integer
     * @see $end
     */
    var $start = 1;
    /**
     * Last line to display
     * @var '*'|integer If '*' then the whole source will be used, otherwise
     *                  the {@link $start} to $end line numbers will be displayed
     */
    var $end = '*';
    /**
     * tokenized source organized by line numbers for php 4.3.0+, the old
     * {@}source} tag used a string
     * @var string|array
     */
    var $source = false;
    /**#@+ @access private */
    /** @var string|false */
    var $_class;
    /**#@-*/
    /**
     * @param string format "start [end]" where start and end are line numbers
     *               with the end line number optional
     */
    function parserSourceInlineTag($value)
    {
        parserInlineTag::parserInlineTag('source','');
        preg_match('/^([0-9]+)\W([0-9]*)$/',trim($value), $match);
        if (!count($match))
        {
            preg_match('/^([0-9]+)$/',trim($value),$match);
            if (count($match))
            {
                $this->start = (int) $match[1];
            }
        } else
        {
            $this->start = (int) $match[1];
            $this->end = (int) $match[2];
        }
    }
    
    /**
     * only used to determine blank lines.  {@}source} will not be blank,
     * probably
     */
    function Strlen()
    {
        return 1;
    }
    
    function getString()
    {
        return '{@source}';
    }
    
    /**
     * @param string|array source code
     * @param boolean in php 4.3.0, if this is a method this will be true
     * @param string class name if this is a method
     */
    function setSource($source, $class = false)
    {
        if (is_array($source))
        {
            $this->_class = $class;
            $this->source = $source;
        } else
        {
            $source = strstr($source,'function');
            $pos = strrpos($source,'}');
            $this->source = substr($source,0,$pos + 1);
        }
    }
    
    /**
     * @uses stringConvert() in PHP 4.2.3-, this method is used to convert
     * @uses arrayConvert() in PHP 4.3.0+, this method is used to convert
     * @param Converter
     */
    function Convert(&$c)
    {
        if (is_string($this->source)) return $this->stringConvert($c);
        return $this->arrayConvert($c);
    }
    
    /**
     * @param Converter
     * @uses phpDocumentor_HighlightParser Parses the tokenized source
     */
    function arrayConvert(&$c)
    {
        $source = $this->source;
        if ($this->end != '*')
        {
            $source = array_slice($this->source,0,$this->end + $this->start - 1);
        }
        $start = $this->start - 1;
        if ($start < 0) $start = 0;
        return $c->ProgramExample($source, true, true, $this->_class, $start);
    }
    
    /**
     * @param Converter
     * @uses Converter::unmangle() remove the extraneous stuff from
     *                             {@link highlight_string()}
     * @deprecated in favor of PHP 4.3.0+ {@link arrayConvert()}
     */
    function stringConvert(&$c)
    {
        $source = highlight_string('<?php '.$this->source.' ?>', true);
        $source = '<code>'.substr($source,strlen('<code><font color="#000000">
<font color="#0000CC">&lt;?php&nbsp;</font>') - 1);
        $source = str_replace('}&nbsp;</font><font color="#0000CC">?&gt;</font>','}</font></code>',$source);
        if ($this->start || ($this->end != '*'))
        {
            $source = explode('<br />',$source);
            $start = $this->start;
            if ($this->end != '*')
            {
                $source = array_slice($source,$start - 1,$this->end - $start + 1);
            } else
            {
                $source = array_slice($source,$start - 1);
            }
            $source = implode($source,'<br />');
            if ($start > 0) $source = "<code>$source";
            if ($this->end != '*') $source = "$source</code>";
        }
        $source = $c->unmangle($source,$this->source);
        return $source;
    }
}

/**
 * Represents the example inline tag, used to display an example file
 * inside a docblock or tutorial
 * @tutorial tags.inlineexample.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserExampleInlineTag extends parserSourceInlineTag
{
    /**
     * @param string format "filepath[ start [end]]" where start and end are line numbers
     *               with the end line number optional
     * @param string full path to the current file, used to check relative
     *               directory locations
     * @param boolean if true, then this is in a tutorial
     */
    function parserExampleInlineTag($value, $current_path, $isTutorial = false)
    {
        global $_phpDocumentor_setting;
        parserInlineTag::parserInlineTag('example','');
        $path = false;
        $tagValue = trim($value);
        $path = $isAbsPath = $pathOnly = $fileName = $fileExt = $original_path  = $title = FALSE;
        do
        {
            // make sure the format is stuff.ext startline[ endline]
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
            if (isset($match[3]))
            {
                $lines = explode(' ', trim($match[3]));
                $this->start = (int) $lines[0];
                if (isset($lines[1])) {
                    $this->end = (int) $lines[1];
                }
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
            $this->path = false;
        } else
        {
            $f = @fopen($path,'r');
            if ($f)
            {
                $example = fread($f,filesize($path));
                if (tokenizer_ext && !$isTutorial)
                {
                    $obj = new phpDocumentorTWordParser;
                    $obj->setup($example);
                    $this->setSource($obj->getFileSource());
                    unset($obj);
                } else
                {
                    $this->setSource($example);
                }
            }
        }
    }
    
    /**
     * @param string|array source code
     * @param boolean in php 4.3.0, if this is a method this will be true
     * @param string class name if this is a method
     */
    function setSource($source, $class = false)
    {
        $this->_class = $class;
        $this->source = $source;
    }
    
    /**
     * @param Converter
     * @uses phpDocumentor_HighlightParser Parses the tokenized source
     */
    function arrayConvert(&$c)
    {
        $source = $this->source;
        if ($this->end != '*')
        {
            $source = array_slice($this->source,0,$this->end + $this->start - 1);
        }
        $start = $this->start - 1;
        if ($start < 0) $start = 0;
        return $c->exampleProgramExample($source, true, true, $this->_class, $start);
    }

    /**
     * Return the source for the example file, enclosed in
     * a <programlisting> tag to use in a tutorial
     * @return string
     */
    function getProgramListing()
    {
        $source = explode("\n", $this->source);
        $start = $this->start;
        if ($this->end != '*')
        {
            $source = array_slice($source,$start - 1,$this->end - $start + 1);
        } else
        {
            $source = array_slice($source,$start - 1);
        }
        $source = join("\n", $source);
        return
        "<programlisting role=\"php\">
         <![CDATA[\n" .
          $source .
        "\n]]>\n</programlisting>";
    }
}

/**
 * Represents the inheritdoc inline tag, used by classes/methods/vars to inherit
 * documentation from the parent class if possible
 * @tutorial tags.inlineinheritdoc.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserInheritdocInlineTag extends parserInlineTag
{
    /**
     * always 'inheritdoc'
     * @var string
     */
    var $inlinetype = 'inheritdoc';
    
    /**
     * Does nothing, overrides parent constructor
     */
    function parserInheritdocInlineTag()
    {
    }
    
    function Convert()
    {
        addWarning(PDERROR_INHERITDOC_DONT_WORK_HERE);
        return '';
    }
}

/**
 * Represents the inline {@}id} tag for tutorials
 * @tutorial tags.inlineid.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserIdInlineTag extends parserInlineTag
{
    /**
     * always 'id'
     * @var string
     */
    var $inlinetype = 'id';
    /**
     * package of the {@}id}
     * @var string
     */
    var $package = 'default';
    /**
     * category of the {@}id}
     * @var string
     */
    var $category = 'default';
    /**
     * subpackage of the {@}id}
     * @var string
     */
    var $subpackage = '';
    /**
     * full name of the tutorial
     * @var string
     */
    var $tutorial;
    /**
     * section/subsection name
     * @var string
     */
    var $id;
    
    /**
     * @param string package name
     * @param string subpackage name
     * @param string tutorial name
     * @param string section/subsection name
     * @param string category name
     */
    function parserIdInlineTag($category,$package,$subpackage,$tutorial,$id = false)
    {
        $this->package = $package;
        $this->subpackage = $subpackage;
        $this->tutorial = $tutorial;
        $this->id = $id;
        $this->category = $category;
    }
    
    /**
     * @param Converter
     * @uses Converter::getTutorialId() retrieve converter-specific ID
     */
    function Convert(&$c)
    {
        if (!$this->id) return '';
        return $c->getTutorialId($this->package,$this->subpackage,$this->tutorial,$this->id,$this->category);
    }
}

/**
 * Represents {@}toc} for table of contents generation in tutorials
 * @tutorial tags.inlinetoc.pkg
 * @package phpDocumentor
 * @subpackage InlineTags
 */
class parserTocInlineTag extends parserInlineTag
{
    /**
     * always 'toc'
     * @var string
     */
    var $inlinetype = 'toc';
    /**
     * @var array format:
     * <pre>
     * array(array('tagname' => section,
     *             'link' => returnsee link,
     *             'id' => anchor name,
     *             'title' => from title tag),...)
     * </pre>
     * @access private
     */
    var $_toc = false;
    /**
     * full path to tutorial, used in conversion
     * @var string
     * @access private
     */
    var $_path = false;

    function parserTocInlineTag()
    {
        parent::parserInlineTag('toc','');
    }
    
    /**
     * @param array format:
     * <pre>
     * array(array('tag' => {@link parserXMLDocBookTag},
     *             'id' => {@link parserIdInlineTag},
     *             'title' => {@link parserXMLDocBookTag title}),...)
     * </pre>
     */
    function setTOC($toc)
    {
        $this->toc = $toc;
    }
    
    /**
     * @param string
     */
    function setPath($path)
    {
        $this->_path = $path;
    }
    
    /**
     * @uses Converter::formatTutorialTOC() passes an array of format:
     *
     * <pre>
     * array(
     *    'tagname' => string name of tag,
     *    'link' => {@link tutorialLink} to the tutorial,
     *    'id' => converter specific tutorial ID from {@link Converter::getTutorialId()}
     *    'title' => title of the tutorial)
     * </pre>
     *
     * and returns the results as the table of contents
     * @uses Converter::getTutorialId() retrieve the tutorial ID for
     * @param Converter
     */
    function Convert(&$c)
    {
        $newtoc = array();
        if (isset($this->toc) && is_array($this->toc)) {
            foreach($this->toc as $i => $toc)
            {
                if (isset($toc['title']))
                $toc['tag']->setTitle($toc['title']);
                else
                $toc['tag']->setTitle(new parserStringWithInlineTags);
                $newtoc[$i]['tagname'] = $toc['tag']->name;
                $l = new tutorialLink;
                if (!isset($toc['title'])) $title = 'section '.$toc['id']->id;
                else
                $title = $toc['title']->Convert($c);
                $l->addLink($toc['id']->id,$this->_path,basename($this->_path),$toc['id']->package, $toc['id']->subpackage, strip_tags($title));
                $newtoc[$i]['link'] = $c->returnSee($l);
                $newtoc[$i]['id'] = $c->getTutorialId($toc['id']->package, $toc['id']->subpackage, basename($this->_path), $toc['id']->id, $toc['id']->category);
                $newtoc[$i]['title'] = $title;
            }
        }
        return $c->formatTutorialTOC($newtoc);
    }
}
?>
