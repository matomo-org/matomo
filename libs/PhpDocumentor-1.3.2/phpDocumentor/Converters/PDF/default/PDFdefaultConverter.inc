<?php
/**
 * Outputs documentation in PDF format
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
 * @package    Converters
 * @subpackage PDFdefault
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: PDFdefaultConverter.inc,v 1.4 2007/02/20 14:25:23 ashnazg Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
/**
 * The Cezpdf class library
 */
include_once('phpDocumentor/Converters/PDF/default/class.phpdocpdf.php');
/**
 * PDF output converter.
 * This Converter takes output from the {@link Parser} and converts it to PDF-ready output for use with {@link Cezpdf}.
 * This is now beta code
 *
 * @package Converters
 * @subpackage PDFdefault
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.1
 * @version $Id: PDFdefaultConverter.inc,v 1.4 2007/02/20 14:25:23 ashnazg Exp $
 * @todo Implement links to conflicts/inheritance
 */
class PDFdefaultConverter extends Converter
{
    /**
     * default PDF Converter wants elements sorted by type as well as alphabetically
     * @see Converter::$sort_page_contents_by_type
     * @var boolean
     */
    var $sort_absolutely_everything = true;
    var $leftindex = array('classes' => false, 'pages' => false, 'functions' => false, 'defines' => false, 'globals' => false);
    var $pagepackage_pagenums = array();
    var $classpackage_pagenums = array();
    /** @var string always PDF */
    var $outputformat = 'PDF';
    /** @var string always default */
    var $name = 'default';
    var $curpagepackage = false;
    var $curclasspackage = false;
    var $smarty_dir;
    /**
     * @var Cezpdf
     */
    var $pdf = false;
    var $ric_set = array();
    /**
     * Source files for appendix C are stored here
     *
     * Format: array(array(package => packagename, code => array(highlightedsource code 1, ...)))
     * @var array
     */
    var $_sourcecode;
    /**
     * @see Converter::Converter()
     */
    function PDFdefaultConverter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $templateDir, $title)
    {
        Converter::Converter($allp, $packp, $classes, $procpages, $po, $pp, $qm, $targetDir, $templateDir, $title);
        $this->pdf =& new phpdocpdf($this, $this->getConverterDir() . PATH_DELIMITER .'templates/fonts/','letter');
        $this->pdf->selectFont($this->getConverterDir() . PATH_DELIMITER .'templates/fonts/Helvetica.afm');
// put a line top and bottom on all the pages
        $this->pdf->ezSetMargins(50,70,50,50);
        $template = &$this->newSmarty();
        $this->pdf->ezText($template->fetch('footer.tpl'));
        $template->assign('title',$title);
        if (file_exists($this->templateDir . 'templates' . PATH_DELIMITER . 'media'. PATH_DELIMITER .'logo.jpg'))
        {
            $template->assign('logo',$this->templateDir . 'templates' . PATH_DELIMITER . 'media'. PATH_DELIMITER .'logo.jpg');
        }
        $this->pdf->ezText($template->fetch('title_page.tpl'));
        unset($template);
    }
    
    function writeSource($path, $value)
    {
        $templ = &$this->newSmarty();
        $pathinfo = $this->proceduralpages->getPathInfo($path, $this);
        $templ->assign('source',$value);
        $templ->assign('package',$pathinfo['package']);
        $templ->assign('subpackage',$pathinfo['subpackage']);
        $templ->assign('name',$pathinfo['name']);
        $templ->assign('source_loc',$pathinfo['source_loc']);
        $templ->assign('docs',$pathinfo['docs']);
        $templ->assign('dest', $this->getFileSourceName($path));
        $this->setSourcePaths($path);
        $this->_sourcecode[$pathinfo['package']][] = $templ->fetch('filesource.tpl');
    }
    
    function postProcess($text)
    {
        return htmlspecialchars($text);
    }
    
    function writeExample($title, $path, $source)
    {
        $templ = &$this->newSmarty();
        $templ->assign('source',$source);
        if (empty($title))
        {
            $title = 'example';
            addWarning(PDERROR_EMPTY_EXAMPLE_TITLE, $path, $title);
        }
        $templ->assign('title',$title);
        $templ->assign('file',$path);
        $this->pdf->ezText($templ->fetch('examplesource.tpl'));
    }
    
    function getExampleLink($path, $title)
    {
        return '';
        return $this->returnLink('{$subdir}__examplesource' . PATH_DELIMITER . 'exsource_'.$path.'.html',$title);
    }
    
    function getSourceLink($path)
    {
//        var_dump(htmlentities('<c:ilink:'.$this->getFileSourceName($path).'>Source Code for this file</c:ilink>'));
        return '<c:ilink:'.$this->getFileSourceName($path).'>Source Code for this file</c:ilink>';
    }
    
    function getFileSourceName($path, $anchor = '')
    {
        return urlencode($anchor . parent::getFileSourceName($path));
    }
    
    /**
     * Retrieve a Converter-specific anchor to a segment of a source code file
     * parsed via a {@tutorial tags.filesource.pkg} tag.
     * @param string full path to source file
     * @param string name of anchor
     * @param string link text, if this is a link
     * @param boolean returns either a link or a destination based on this
     *                parameter
     * @return string link to an anchor, or the anchor
     */
    function getSourceAnchor($sourcefile,$anchor,$text = '',$link = false)
    {
        if ($link)
        {
            return '<c:ilink:' . $this->getFileSourceName($sourcefile, $anchor). '>' . $text . '</c:ilink>';
        } else
        {
            return '</text><pdffunction:addDestination arg="'.$this->getFileSourceName($sourcefile, $anchor).'" arg="FitH" arg=$this->y /><text size="8">';
        }
    }
    
    /**
     * Returns a bookmark using Cezpdf 009
     *
     * @param abstractLink a descendant of abstractlink should be passed, and never text
     * @param string text to display in the link
     */
    function returnSee(&$element, $eltext = false)
    {
        if (!$element) return false;
        if (!$eltext)
        {
            $eltext = '';
            switch($element->type)
            {
                case 'tutorial' :
                $eltext = $element->title;
                break;
                case 'method' :
                case 'var' :
                case 'const' :
                $eltext .= $element->class.'::';
                case 'page' :
                case 'define' :
                case 'class' :
                case 'function' :
                case 'global' :
                default :
                $eltext .= $element->name;
                if ($element->type == 'function' || $element->type == 'method') $eltext .= '()';
                break;
            }
        }
        switch ($element->type)
        {
            case 'tutorial' :
            return '<c:ilink:'.urlencode($element->type.$element->package.$element->subpackage.$element->name.$element->section).'>'.$eltext.'</c:ilink>';
            case 'page' :
            return '<c:ilink:'.urlencode($element->type.$element->package.$element->path).'>'.$eltext.'</c:ilink>';
            case 'define' :
            case 'global' :
            case 'class' :
            case 'function' :
            return '<c:ilink:'.urlencode($element->type.$element->package.$element->name).'>'.$eltext.'</c:ilink>';
            case 'method' :
            case 'var' :
            case 'const' :
            return '<c:ilink:'.urlencode($element->type.$element->package.$element->class.'::'.$element->name).'>'.$eltext.'</c:ilink>';
        }
        return $element;
    }
    
    /**
     * @param string
     * @param string
     * @return string <c:alink:$link>$text</c:alink>
     */
    function returnLink($link,$text)
    {
        return "<c:alink:$link>$text</c:alink>";
    }
    
    /**
     * Convert README/INSTALL/CHANGELOG file contents to output format
     * @param README|INSTALL|CHANGELOG
     * @param string contents of the file
     */
    function Convert_RIC($name, $contents)
    {
        $this->ric_set[$name] = $contents;
    }

    function convertDocBlock(&$element)
    {
        if (!$element->docblock) return;
        $template = &$this->newSmarty();
        
        $nopackage = true;
        if ($element->type == 'page' || $element->type == 'class') $nopackage = false;
        $tagses = $element->docblock->listTags();
        $tags = array();
        $names = array('staticvar' => 'Static Variable','deprec' => 'Deprecated','abstract' => 'Abstract Element','todo' => 'TODO');
        if (!$nopackage)
        {
            $tags[] = array('keyword' => 'Package','data' => $element->docblock->package);
            if (!empty($element->docblock->subpackage)) $tags[] = array('keyword' => 'Sub-Package','data' => $element->docblock->subpackage);
        }
        if ($element->docblock->var)
        {
            $a = $element->docblock->var->Convert($this);
            if (!empty($a))
            $tags[] = array('keyword' => 'Var', 'data' => $a);
        }
        if ($element->docblock->funcglobals)
        foreach($element->docblock->funcglobals as $global => $val)
        {
            if ($a = $this->getGlobalLink($global,$element->docblock->package))
            {
                $global = $a;
            }
            $b = Converter::getLink($val[0]);
            if (is_object($b) && phpDocumentor_get_class($b) == 'classlink')
            {
                $val[0] = $this->returnSee($b);
            }
            $tags[] = array('keyword' => 'Global Variable Used','data' => $val[0].' '.$global.': '.$val[1]->Convert($this));
        }
        if ($element->docblock->statics)
        foreach($element->docblock->statics as $static => $val)
        {
            $a = $val->Convert($this);
            $tags[] = array('keyword' => 'Static Variable Used','data' => $val->converted_returnType.' '.$static.': '.$a);
        }
        foreach($tagses as $tag)
        {
            if (isset($names[$tag->keyword])) $tag->keyword = $names[$tag->keyword];
            $tags[] = array("keyword" => ucfirst($tag->keyword),"data" => $tag->Convert($this));
        }
        $utags = array();
        foreach($element->docblock->unknown_tags as $keyword => $t)
        {
            foreach($t as $tag)
            $utags[] = array('keyword' => $keyword, 'data' => $tag->Convert($this));
        }
        if ($element->type == 'packagepage') return;
        $sdesc = $element->docblock->getSDesc($this);
        $desc = $element->docblock->getDesc($this);
        $template->assign('utags',$utags);
        $template->assign('tags',$tags);
        $template->assign('sdesc',$sdesc);
        $template->assign('desc',$desc);
        if (false) // $element->type != 'page')
        {
            if ($element->type != 'var' && $element->type != 'method')
            {
                $this->pdf->addDestination(urlencode($element->type.$element->docblock->package.$element->name),'FitH',$this->pdf->y);
            } else
            {
                $this->pdf->addDestination(urlencode($element->type.$element->docblock->package.$element->class.'::'.$element->name),'FitH',$this->pdf->y);
            }
        } elseif (false)
        {
            $this->pdf->addDestination(urlencode('page'.$element->parent->package.$element->parent->getPath()),'FitH',$this->pdf->y);
        }
        $this->convertParams($element);
        $this->pdf->ezText($template->fetch('docblock.tpl'));
    }
    
    function convertParams(&$element)
    {
        if ($element->type != 'function' && $element->type != 'method') return;
        if (count($element->docblock->params))
        {
            $template = &$this->newSmarty();
            $params = array();
            if (count($element->docblock->params))
            foreach($element->docblock->params as $param => $val)
            {
                $a = $val->Convert($this);
                $params[] = array("name" => $param,"type" => $val->converted_returnType,"description" => $a);
            }
            $template->assign('params',$params);
            $this->pdf->ezText($template->fetch('params.tpl'));
        }
    }
    
    function convertGlobal(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $template = &$this->newSmarty();
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink', false);
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->name));
        $template->assign('type',$element->getDataType($this));
        $template->assign('name',$element->name);
        $template->assign('value',$this->getGlobalValue($element->getValue()));
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('global.tpl'));
        $this->convertDocBlock($element);
    }
    
    function getGlobalValue($value)
    {
        return parent::getGlobalValue(htmlspecialchars($value));
    }
    
    function convertMethod(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $params = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $params[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }
        if ($element->docblock->return)
        {
            if (!$element->docblock->return->returnType) $element->docblock->return->returnType = 'void';
        }
        $template = &$this->newSmarty();
        $template->assign('class',$this->class);
        $template->assign('constructor',$element->isConstructor);
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->class.'::'.$element->name));
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $ret = 'void';
        if ($element->docblock->return)
        {
            $ret = $element->docblock->return->returnType;
        }
        $template->assign('return',$ret);
        $template->assign('functioncall',$element->getFunctionCall());
        $template->assign('intricatefunctioncall',$element->getIntricateFunctionCall($this,$params));
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('method.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertVar(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $template = &$this->newSmarty();
        $template->assign('class',$this->class);
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('type',$element->docblock->var->returnType);
        $template->assign('name',$element->name);
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->class.'::'.$element->name));
        $template->assign('value',$element->value);
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('var.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertConst(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $template = &$this->newSmarty();
        $template->assign('class',$this->class);
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('name',$element->name);
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->class.'::'.$element->name));
        $template->assign('value',$element->value);
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('const.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertClass(&$element)
    {
        $template = &$this->newSmarty();
        if ($this->curclasspackage != $element->docblock->package)
        {
            $template->assign('includeheader',true);
            if (isset($this->package_pages[$element->docblock->package]))
            {
                $template->assign('ppage',$this->package_pages[$element->docblock->package]);
                $template->assign('isclass',true);
                unset($this->package_pages[$element->docblock->package]);
            }
            $template->assign('classeslink',rawurlencode("Package ".$element->docblock->package." Classes"));
        }
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $this->curclasspackage = $element->docblock->package;
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->name));
        $template->assign('package',$element->docblock->package);
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('name',$element->name);
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('class.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertInclude(&$element)
    {
        $template = &$this->newSmarty();
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('name',$element->name);
        $template->assign('value',$this->getIncludeValue($element->getValue(), $element->getPath()));
        $this->pdf->ezText($template->fetch('include.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertFunction(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $params = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $params[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }
        if ($element->docblock->return)
        {
            if (!$element->docblock->return->returnType) $element->docblock->return->returnType = 'void';
        }
        $template = &$this->newSmarty();
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->name));
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('return',$element->docblock->return->returnType);
        $template->assign('functioncall',$element->getFunctionCall());
        $template->assign('intricatefunctioncall',$element->getIntricateFunctionCall($this,$params));
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('function.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertDefine(&$element)
    {
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        $template = &$this->newSmarty();
        $template->assign('linenumber',$element->getLineNumber());
        if ($this->hasSourceCode($element->getPath()))
        $template->assign('slink',$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        else
        $template->assign('slink',false);
        $template->assign('name',$element->name);
        $template->assign('dest', urlencode($element->type.$element->docblock->package.$element->name));
        $template->assign('value',$element->value);
        $template->assign('sdesc',$sdesc);
        $this->pdf->ezText($template->fetch('define.tpl'));
        $this->convertDocBlock($element);
    }
    
    function convertPage(&$element)
    {
        $template = &$this->newSmarty();
        $template->assign('includeheader',false);
        $sdesc = '';
        if ($element->docblock->sdesc)
        {
            $sdesc = $element->docblock->sdesc->Convert($this);
        }
        if (count($element->elements) || ($sdesc) || count($element->docblock->tags))
        {
            if ($this->curpagepackage != $element->parent->package)
            {
                $template->assign('includeheader',true);
                if (isset($this->package_pages[$element->parent->package]))
                {
                    $template->assign('ppage',$this->package_pages[$element->parent->package]);
                    unset($this->package_pages[$element->parent->package]);
                }
            }
            $this->curpagepackage = $element->parent->package;
            $template->assign('dest', urlencode('page'.$element->parent->package.$element->parent->getPath()));
            $template->assign('sdesc',$sdesc);
            $template->assign('package',$element->parent->package);
            $template->assign('name',$element->parent->file);
            $this->pdf->ezText($template->fetch('page.tpl'));
            $this->convertDocBlock($element);
        }
    }
    
        
    /**
     * Used to translate an XML DocBook tag from a tutorial by reading the
     * options.ini file for the template.
     * @param string tag name
     * @param string any attributes Format: array(name => value)
     * @param string the tag contents, if any
     * @param string the tag contents, if any, unpost-processed
     * @return string
     */
    function TranslateTag($name,$attr,$cdata,$unconvertedcdata)
    {
        if ($name == 'example' && @$attr['role'] == 'html')
        {
            $cdata = htmlspecialchars($cdata);
            $unconvertedcdata = htmlspecialchars($unconvertedcdata);
        }
        if ($name == 'programlisting' && @$attr['role'] == 'php')
        {
            $unconvertedcdata = strtr($unconvertedcdata, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
            $a = parent::TranslateTag($name, $attr, $cdata, $unconvertedcdata);
//            var_dump(htmlspecialchars($cdata), htmlspecialchars($unconvertedcdata), htmlspecialchars($a));
            return $a;
        }
        return parent::TranslateTag($name, $attr, $cdata, $unconvertedcdata);
    }

    function getPageName(&$element)
    {
        if (phpDocumentor_get_class($element) == 'parserpage') return $element->getName();
        return $element->parent->getName();
    }

    function getTutorialId($package,$subpackage,$tutorial,$id)
    {
        return 'tutorial'.$package.$subpackage.$tutorial.$id;
    }
    
    function getCData($value)
    {
        return str_replace(array('<c:','<C:'),array("&lt;c:","&lt;C:"),$value);
    }
    
    /**
     * @deprecated html package pages just don't work with PDF, use {@tutorial tutorials.pkg}
     */
    function convertPackagepage(&$element)
    {
        $x = $element->Convert($this);
        $x = substr($x,strpos($x,'<body'));
        $this->package_pages[$element->package] = trim(substr($x,strpos($x,'>') + 1,strpos($x,'</body>') - 6));
    }
    
    function convertTutorial(&$element)
    {
        $x = $element->Convert($this, true);
        $template = &$this->newSmarty();
        $template->assign('package',$element->package);
        $template->assign('subpackage',$element->subpackage);
        $template->assign('contents',$x);
        $template->assign('title',$element->getTitle($this));
        $template->assign('child',$element->parent);
        if (isset($element->parent->parent)) $template->assign('hasparent',$element->parent->parent);
        $template->assign('element',$element);
        $this->pdf->ezText($template->fetch('tutorial.tpl'));
    }
    
    /**
     * returns a template-enabled array of class trees
     * 
     * @param    string    $package    package to generate a class tree for
     * @see $roots, getRootTree()
     */
    function generateFormattedClassTrees($package)
    {
        if (!isset($this->roots[$package])) return array();
        $roots = $trees = array();
        $roots = $this->roots[$package];
        for($i=0;$i<count($roots);$i++)
        {
            $trees[] = array('class' => $roots[$i],'class_tree' => "<ul>\n".$this->getRootTree($this->getSortedClassTreeFromClass($roots[$i],$package,''),$package)."</ul>\n");
        }
        return $trees;
    }
    
    /**
     * return formatted class tree for the Class Trees page
     *
     * @param array $tree output from {@link getSortedClassTreeFromClass()}
     * @see Classes::$definitechild, generateFormattedClassTrees()
     * @return string
     */
    function getRootTree($tree,$package)
    {
        if (!$tree) return '';
        $my_tree = '';
        $cur = '#root';
        $lastcur = array(false);
        $kids = array();
        $dopar = false;
        if ($tree[$cur]['parent'])
        {
            $dopar = true;
            if (!is_object($tree[$cur]['parent']))
            {
//                debug("parent ".$tree[$cur]['parent']." not found");
                $my_tree .= '<li>' . $tree[$cur]['parent'] .'<ul>';
            }
            else
            {
//                        debug("parent ".$this->returnSee($tree[$cur]['parent'], false, false)." in other package");
                $my_tree .= '<li>' . $this->returnSee($tree[$cur]['parent'], false, false);
                if ($tree[$cur]['parent']->package != $package) $my_tree .= ' <b>(Different package)</b><ul>';
            }
        }
        do
        {
//            fancy_debug($cur,$lastcur,$kids);
            if (count($tree[$cur]['children']))
            {
//                debug("$cur has children");
                if (!isset($kids[$cur]))
                {
//                    debug("set $cur kids");
                    $kids[$cur] = 1;
                    $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link'], false, false);
                    $my_tree .= '<ul>'."\n";
                }
                array_push($lastcur,$cur);
                list(,$cur) = each($tree[$cur]['children']);
//                var_dump('listed',$cur);
                if ($cur)
                {
                    $cur = $cur['package'] . '#' . $cur['class'];
//                    debug("set cur to child $cur");
//                    $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link'], false, false);
                    continue;
                } else
                {
//                    debug("end of children for $cur");
                    $cur = array_pop($lastcur);
                    $cur = array_pop($lastcur);
                    $my_tree .= '</ul></li>'."\n";
                    if ($dopar && ($cur == '#root' || !$cur)) $my_tree .= '</ul></li>';
                }
            } else 
            {
//                debug("$cur has no children");
                $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link'], false, false)."</li>";
                if ($dopar && $cur == '#root') $my_tree .= '</ul></li>';
                $cur = array_pop($lastcur);
            }
        } while ($cur);
        return $my_tree;
    }
    
    /**
     * calls {@link Cezpdf::ezOutput()} and writes documentation.pdf to targetDir
     */
    function Output($title = 'Generated Documentation')
    {
        phpDocumentor_out("Generating PDF file..");
        flush();
        $template = &$this->newSmarty();
        $this->pdf->ezText($template->fetch('appendix_title_page.tpl'));
        $trees = array();
        foreach($this->all_packages as $package => $rest)
        {
            if (!isset($this->pkg_elements[$package])) continue;
            $a = array();
            phpDocumentor_out('.');
            flush();
            $a['package'] = $package;
            $a["trees"] = $this->generateFormattedClassTrees($package);
            $trees[] = $a;
        }
        $template->assign('trees',$trees);
        $this->pdf->ezText($template->fetch('classtree.tpl'));
        phpDocumentor_out('.');
        if (count($this->ric_set))
        $this->pdf->ezText($template->fetch('ric_title_page.tpl'));
        foreach($this->ric_set as $name => $contents)
        {
            $template->assign('contents',$contents);
            $template->assign('name',$name);
            $this->pdf->ezText($template->fetch('ric.tpl'));
        }
        if (count($this->_sourcecode))
        {
            $this->pdf->ezText($template->fetch('source_title_page.tpl'));
            $template->assign('source',$this->_sourcecode);
            $this->pdf->ezText($template->fetch('source_loop.tpl'));
        }
        flush();
        if (count($this->todoList))
        {
            $todolist = array();
            foreach($this->todoList as $package => $alltodos)
            {
                foreach($alltodos as $todos)
                {
                    $converted = array();
                    $converted['link'] = $this->returnSee($todos[0]);
                    if (!is_array($todos[1]))
                    {
                        $converted['todos'][] = $todos[1]->Convert($this);
                    } else
                    {
                        foreach($todos[1] as $todo)
                        {
                            $converted['todos'][] = $todo->Convert($this);
                        }
                    }
                    $todolist[$package][] = $converted;
                }
            }
            $template->assign('todos',$todolist);
            
            $this->pdf->ezText($template->fetch('todolist.tpl'));
        }
        if (false) {
        $fp = @fopen("C:/Documents and Settings/Owner/Desktop/pdfsourceorig.txt",'w');
        if ($fp)
        {
            $a = $this->pdf->ezOutput(true); // debug
            fwrite($fp, $a, strlen($a));
            fclose($fp);
        }
        }
        $this->pdf->setupTOC();
        $template->assign('contents',$this->pdf->reportContents);
        $this->pdf->ezText($template->fetch('toc.tpl'));
        $x = $this->pdf->ezOutput(false, $template);
        phpDocumentor_out("done\n");
        flush();
        $this->writeFile("documentation.pdf", $x, true);
    }
    
    function mystrnatcasecmp($a,$b)
    {
        return strnatcasecmp($a[0],$b[0]);
    }
    
    
    /**
     * @param string name of class
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @return mixed false if not found, or an html a link to the class's documentation
     * @see parent::getClassLink()
     */
    function getClassLink($expr,$package, $file = false,$text = false)
    {
        $a = Converter::getClassLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of function
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @return mixed false if not found, or an html a link to the function's documentation
     * @see parent::getFunctionLink()
     */
    function getFunctionLink($expr,$package, $file = false,$text = false)
    {
        $a = Converter::getFunctionLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of define
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @return mixed false if not found, or an html a link to the define's documentation
     * @see parent::getDefineLink()
     */
    function getDefineLink($expr,$package, $file = false,$text = false)
    {
        $a = Converter::getDefineLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of global variable
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @return mixed false if not found, or an html a link to the global variable's documentation
     * @see parent::getGlobalLink()
     */
    function getGlobalLink($expr,$package, $file = false,$text = false)
    {
        $a = Converter::getGlobalLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of procedural page
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @return mixed false if not found, or an html a link to the procedural page's documentation
     * @see parent::getPageLink()
     */
    function getPageLink($expr,$package, $path = false,$text = false)
    {
        $a = Converter::getPageLink($expr,$package,$path);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of method
     * @param string class containing method
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @return mixed false if not found, or an html a link to the method's documentation
     * @see parent::getMethodLink()
     */
    function getMethodLink($expr,$class,$package, $file = false,$text = false)
    {
        $a = Converter::getMethodLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of var
     * @param string class containing var
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the var's documentation
     * @see parent::getVarLink()
     */
    function getVarLink($expr,$class,$package, $file = false,$text = false)
    {
        $a = Converter::getVarLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    /**
     * @param string name of class constant
     * @param string class containing class constant
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the var's documentation
     * @see parent::getConstLink()
     */
    function getConstLink($expr,$class,$package, $file = false,$text = false)
    {
        $a = Converter::getConstLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
    }

    function setTemplateDir($dir)
    {
        Converter::setTemplateDir($dir);
        $this->smarty_dir = $this->templateDir;
    }
    
    /** @return 1 always the same */
    function getState()
    {
        return 1;
    }
    
    /**
     * @see parent::unmangle()
     */
    function unmangle($notused,$source)
    {
//        $source = str_replace("\n","<mybr>",$source);
        return $source;
    }
}
?>