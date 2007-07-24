<?php
/**
 * Outputs documentation in XML DocBook format, in the version expected by
 * pear.php.net's documentation team
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
 * @subpackage XMLDocBook
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: XMLDocBookpeardoc2Converter.inc,v 1.6 2006/10/24 04:18:14 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
/**
 * XML DocBook converter.
 * This Converter takes output from the {@link Parser} and converts it to DocBook
 * output for PEAR documentation.
 *
 * This Converter differs from the parent DocBook Converter in that it does not
 * recognize the possibility of procedural pages or of functions!  All functions
 * must be defined as static methods for namespace purposes.  In addition, all
 * constants and global variables for a package are grouped together as per
 * peardoc2 requirements.  Include statements are not documented.  If you want
 * to document a normal project, don't use the peardoc2 converter, use the
 * DocBook converter.
 * @package Converters
 * @subpackage XMLDocBook
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.2
 * @version $Id: XMLDocBookpeardoc2Converter.inc,v 1.6 2006/10/24 04:18:14 cellog Exp $
 */
class XMLDocBookpeardoc2Converter extends Converter
{
    /**
     * This converter knows about the new root tree processing
     * In order to fix PEAR Bug #6389
     * @var boolean
     */
    var $processSpecialRoots = true;
    /**
     * XMLDocBookConverter wants elements sorted by type as well as alphabetically
     * @see Converter::$sort_page_contents_by_type
     * @var boolean
     */
    var $sort_page_contents_by_type = true;
    /** @var string */
    var $outputformat = 'XML';
    /** @var string */
    var $name = 'DocBook/peardoc2';
    /**
     * indexes of elements by package that need to be generated
     * @var array
     */
    var $leftindex = array('classes' => true, 'pages' => false, 'functions' => false, 'defines' => true, 'globals' => true);
    /**
     * whether a @see is going to be in the {@link $base_dir}, or in a package/subpackage subdirectory of $base_dir
     * @var boolean
     */
    var $local = true;
    
    /**
     * name of current page being converted
     * @var string
     */
    var $page;
    
    /**
     * path of current page being converted
     * @var string
     */
    var $path;
    
    /**
     * name of current class being converted
     * @var string
     */
    var $class;
    
    /**
     * template for the procedural page currently being processed
     * @var Template
     */
    var $page_data;
    
    /**
     * output directory for the current procedural page being processed
     * @var string
     */
    var $page_dir;

    /**
     * Constants, used for constants.tpl
     * @var array
     */
    var $_peardoc2_constants = false;
    
    /**
     * Global Variables, used for globals.tpl
     * @var array
     */
    var $_peardoc2_globals = false;
    
    /**
     * target directory passed on the command-line.
     * {@link $targetDir} is malleable, always adding package/ and package/subpackage/ subdirectories onto it.
     * @var string
     */
    var $base_dir;
    
    /**
     * output directory for the current class being processed
     * @var string
     */
    var $class_dir;
    
    /**
     * template for the class currently being processed
     * @var Template
     */
    var $class_data;
    
    /**
     * array of converted package page names.
     * Used to link to the package page in the left index
     * @var array Format: array(package => 1)
     */
    var $package_pages = array();
    
    /**
     * Contents of the packagename.xml file are stored in this template variable
     * @var Smarty
     */
    var $packagexml;
    /**
     * controls formatting of parser informative output
     * 
     * Converter prints:
     * "Converting /path/to/file.php... Procedural Page Elements... Classes..."
     * Since HTMLdefaultConverter outputs files while converting, it needs to send a \n to start a new line.  However, if there
     * is more than one class, output is messy, with multiple \n's just between class file output.  This variable prevents that
     * and is purely cosmetic
     * @var boolean
     */
    var $juststarted = false;
    
    /**
     * contains all of the template procedural page element loop data needed for the current template
     * @var array
     */
    var $current;
    
    /**
     * contains all of the template class element loop data needed for the current template
     * @var array
     */
    var $currentclass;
    
    /** 
     * Pass elements by package, simplifies generation of package.xml/category.xml
     */
    var $sort_absolutely_everything = true;
    /**
     * template options.  Currently only 1 recognized option usepear
     *
     * usepear tells the getLink() function to return a package link to PEAR and PEAR_ERROR if possible, and to link directly
     * to the fully-delimited link package#class.method or package#file.method in PEAR style, if possible, even if the
     * package is not parsed.  This will allow parsing of separate PEAR packages without parsing the entire thing at once!
     * @var array
     */
    var $template_options = array('usepear' => false);
    
    var $function_data = array();
    var $method_data = array();
    var $_write_constants_xml = array();
    var $_write_globals_xml = array();
    var $sourceloc = '';
    /**
     * peardoc2 Category
     * @var string
     */
    var $category;
    /**
     * Used to re-format output so that it's easy for translators to handle
     * 
     * @var XML_Beautifier|false
     * @access private
     */
    var $_beautifier = false;

    /**
     * sets {@link $base_dir} to $targetDir
     * @see Converter()
     */
    function XMLDocBookpeardoc2Converter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $templateDir, $title)
    {
        if (!class_exists('XML_Beautifier')) {
            @include_once 'XML/Beautifier.php';
        }
        Converter::Converter($allp, $packp, $classes, $procpages,$po, $pp, $qm, $targetDir, $templateDir, $title);
        if (class_exists('XML_Beautifier')) {
            require_once 'phpDocumentor/Converters/XML/DocBook/peardoc2/Beautifier.php';
            $this->_beautifier = new phpDocumentor_peardoc2_XML_Beautifier;
            $this->_beautifier->setOption('indent', ' ');
        }
        $this->base_dir = $targetDir;
    }
    
    /**
     * do that stuff in $template_options
     */
    function &getLink($expr, $package = false, $packages = false)
    {
        return Converter::getLink($expr, $package, $packages);
    }
    
    function unmangle($s,$sourcecode)
    {
        return '<programlisting role="php"><![CDATA[
'.$sourcecode.']]></programlisting>';
    }

    /**
     * Writes a file to target dir, beautify any .xml files first
     * @param string filename
     * @param string file contents
     * @param boolean true if the data is binary and not text
     */
    function writeFile($file,$data,$binary = false)
    {
        if ($this->_beautifier && substr($file, -4) == '.xml') {
            $ret = $this->_beautifier->formatString($data);
            if (PEAR::isError($ret)) {
                addWarning(PDERROR_BEAUTIFYING_FAILED, $ret->getMessage());
                $ret = $data;
            }
            $data = $ret;
        }
        return parent::writeFile($file, $data, $binary);
    }
    
    /**
     * Used to convert the {@}example} inline tag in a docblock.
     * 
     * By default, this just wraps ProgramExample
     * @see XMLDocBookpeardoc2Converter::exampleProgramExample
     * @param string
     * @param boolean true if this is to highlight a tutorial <programlisting>
     * @return string
     */
    function exampleProgramExample($example, $tutorial = false, $inlinesourceparse = null/*false*/,
                            $class = null/*false*/, $linenum = null/*false*/, $filesourcepath = null/*false*/)
    {
        return '<example><title>Example</title><programlisting role="php"><![CDATA[' .
         $example . ']]></programlisting></example>';
        $this->ProgramExample($example, $tutorial, $inlinesourceparse, $class, $linenum, $filesourcepath)
        . '</example>';
    }
    
    function writeExample($title, $path, $source)
    {
        $this->_save_example = array($title, $source);
    }
    
    function getExampleLink($unused, $title)
    {
        $source = $this->_save_example[1];
        return '<para><example><title>' . $title . '</title>' . $source . '</example></para>';
    }
    
    function type_adjust($typename)
    {
        if (isset($this->template_options['typechanging'][trim($typename)]))
        return $this->template_options['typechanging'][trim($typename)];
        $a = $this->getLink($typename);
        if (is_object($a))
        {
            if (phpDocumentor_get_class($a) == 'classlink')
            return '<classname>'.$typename.'</classname>';
            if (phpDocumentor_get_class($a) == 'functionlink' || phpDocumentor_get_class($a) == 'methodlink')
            return '<function>'.$typename.'</function>';
            if (phpDocumentor_get_class($a) == 'definelink')
            return '<constant>'.$typename.'</constant>';
            if (phpDocumentor_get_class($a) == 'varlink')
            return '<varname>'.$typename.'</varname>';
        }
        return $typename;
    }
    
    /**
     * Writes out the template file of {@link $class_data} and unsets the template to save memory
     * @see registerCurrentClass()
     * @see parent::endClass()
     * @todo move class summary into an array to be written out at the end
     *       of parsing each package
     */
    function endClass()
    {
        $a = '../';
        if (!empty($this->subpackage)) $a .= '../';
        if ($this->juststarted)
        {
            $this->juststarted = false;
            phpDocumentor_out("\n");
            flush();
        }
        foreach($this->method_data as $func)
        {
            $func[0]->assign("phpdocversion",PHPDOCUMENTOR_VER);
            $func[0]->assign("phpdocwebsite",PHPDOCUMENTOR_WEBSITE);
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->category) . PATH_DELIMITER . strtolower($this->class_dir . PATH_DELIMITER . str_replace(array('_','.'),array('-','--'),$this->class)));
            $this->writefile(strtolower($func[1] ). '.xml','<!-- $' . "Revision$ -->\n" . $func[0]->fetch('method.tpl'));
        }
        // code below is in packagename.xml handling, see Output()
/*        $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->category) . PATH_DELIMITER . strtolower($this->class_dir));
        $this->writefile(str_replace(array('_','.'),array('-','--'),strtolower($this->class)) . '.xml',$this->class_data->fetch('class.tpl'));*/
        unset($this->class_data);
    }
    
    function addSummaryToPackageXml($template_output)
    {
        $this->packagexml->append('ids',$template_output);
    }
    
    /**
     * @param parserClass|false $element is false if this is the end of all conversion
     */
    function flushPackageXml($element)
    {
        if (isset($this->packagexml))
        {
            if (!$element || $element->docblock->package != $this->package) // finished with package
            {
                if (isset($this->_write_constants_xml[$this->category][$this->package]) &&
                    $this->_write_constants_xml[$this->category][$this->package])
                {
                    $this->packagexml->append('ids',
                        '&package.' .
                         strtolower($this->category.'.' .
                         str_replace(array('_','.'),array('-','--'),$this->package).'.constants;'));
                    $this->_write_constants_xml[$this->category][$this->package] = false;
                }
                if (isset($this->_write_globals_xml[$this->category][$this->package]) &&
                    $this->_write_globals_xml[$this->category][$this->package])
                {
                    $this->packagexml->append('ids',
                            '&package.'.strtolower($this->category.'.' . 
                             str_replace(array('_','.'),array('-','--'),$this->package).'.globals;'));
                    $this->_write_globals_xml[$this->category][$this->package] = false;
                }
                $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->category));
                $this->writefile(str_replace('_','-',strtolower($this->package)).'.xml',
                    '<!-- $' . "Revision$ -->\n" . $this->packagexml->fetch('package.tpl'));
                $this->packagexml->clear_all_assign();
                if ($element) {
                    $this->packagexml->assign('package',$element->docblock->package);
                    $this->packagexml->assign('ids',array());
                    $this->packagexml->assign('id',$this->getId($element, true));
                }
            }
        } else
        {
            $this->packagexml = $this->newSmarty();
            $this->packagexml->assign('package',$element->docblock->package);
            $this->packagexml->assign('ids',array());
            $this->packagexml->assign('id',$this->getId($element, true));
        }
    }
    
    /**
     * @param string
     * @param string
     * @return string <ulink url="'.$link.'">'.$text.'</ulink>
     */
    function returnLink($link,$text)
    {
        return '<ulink url="'.$link.'">'.$text.'</ulink>';
    }
    
    function makeLeft()
    {
    }
    
    /**
     * Does nothing
     */
    function formatPkgIndex()
    {
    }
    
    /**
     * Does nothing
     */
    function formatIndex()
    {
    }

    /**
     * Does nothing
     */
    function writeNewPPage($key)
    {
    }
    
    /**
     * Does nothing
     */
    function writeSource()
    {
    }
    
    /**
     * Creates package/lang/categoryname/packagename.xml for each package
     */
    function formatLeftIndex()
    {
        $this->makeLeft();
    }
    
    /**
     * This function takes an {@link abstractLink} descendant and returns an html link
     *
     * @param abstractLink a descendant of abstractlink should be passed, and never text
     * @param string text to display in the link
     * @param boolean this parameter is not used, and is deprecated
     * @param boolean determines whether the returned text is enclosed in an <link> tag
     */
    function returnSee(&$element, $eltext = false, $local = true, $with_a = true)
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
                case 'class' :
                $eltext = '<classname>'.$element->name.'</classname>';
                break;
                case 'method' :
                $eltext .= '<function>';
                case 'var' :
                if ($element->type == 'var') $eltext .= '<varname>';
                $eltext .= $element->class.'::';
                case 'page' :
                case 'define' :
                if ($element->type == 'define')
                $eltext .= '<constant>';
                case 'function' :
                if ($element->type == 'function')
                $eltext .= '<function>';
                case 'global' :
                default :
                $eltext .= $element->name;
                if ($element->type == 'function' || $element->type == 'method') $eltext .= '</function>';
                if ($element->type == 'var') $eltext .= '</varname>';
                if ($element->type == 'define') $eltext .= '</constant>';
                break;
            }
        } elseif (!is_object($element)) {
            return false;
        } elseif ($element->type == 'method')
        {
            $eltext = str_replace($element->name . '()', $element->name, $eltext);
        }

        if ($element->type == 'page' || $element->type == 'function' || $element->type == 'var')
        { // we ignore all procedural pages, instead, constant, function and
          // global variable pages are output
            return $eltext;
        }
        if ($element->type == 'class')
        {
            return '<link linkend="'.$this->getId($element).'-summary">'.$eltext.'</link>';
        }
        return '<link linkend="'.$this->getId($element).'">'.$eltext.'</link>';
    }
    
    /**
     * Get the id value needed to allow linking
     * @param mixed descendant of parserElement or parserData/parserPage
     * @param boolean true to return the id for the package page
     * @see parserElement, parserData, parserPage
     * @return string the id value for this element type
     */
    function getId(&$el, $returnpackage = false)
    {
        if (phpDocumentor_get_class($el) == 'parserdata')
        {
            $element = $this->addLink($el->parent);
            $elp = $el->parent;
        } elseif (!is_a($el,'abstractlink'))
        {
            $elp = $el;
            $element = $this->addLink($el);
        } else $element = $el;
        $a = '';
        if (!empty($element->subpackage))
        {
            $a = str_replace(array('_','.'),array('-','--'),$element->subpackage).'.';
        }
        if ($returnpackage) return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package));
        switch ($element->type)
        {
            case 'page' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.$element->fileAlias);
            break;
            case 'define' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.constants.details.'.$element->fileAlias);
            break;
            case 'global' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.globals.details.'.$element->fileAlias);
            break;
            case 'class' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name));
            break;
            case 'function' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.$element->fileAlias.'.'.str_replace('_','-',$element->name));
            break;
            case 'method' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.str_replace('_','-',$element->name));
            break;
            case 'var' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'-summary.vars.'.str_replace(array('$','_'),array('var--','-'),$element->name));
            break;
            case 'tutorial' :
            return 'package.'.strtolower($element->category.'.'.str_replace(array('_','.'),array('-','--'),$element->package).'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name)).'-tutorial';
            break;
        }
    }

    /**
     * Create errors.html template file output
     *
     * This method takes all parsing errors and warnings and spits them out ordered by file and line number.
     * @global ErrorTracker We'll be using it's output facility
     */
    function ConvertErrorLog()
    {
        global $phpDocumentor_errors;
        $allfiles = array();
        $files = array();
        $warnings = $phpDocumentor_errors->returnWarnings();
        $errors = $phpDocumentor_errors->returnErrors();
        $template = &$this->newSmarty();
        foreach($warnings as $warning)
        {
            $file = '##none';
            $linenum = 'Warning';
            if ($warning->file)
            {
                $file = $warning->file;
                $allfiles[$file] = 1;
                $linenum .= ' on line '.$warning->linenum;
            }
            $files[$file]['warnings'][] = array('name' => $linenum, 'listing' => $warning->data);
        }
        foreach($errors as $error)
        {
            $file = '##none';
            $linenum = 'Error';
            if ($error->file)
            {
                $file = $error->file;
                $allfiles[$file] = 1;
                $linenum .= ' on line '.$error->linenum;
            }
            $files[$file]['errors'][] = array('name' => $linenum, 'listing' => $error->data);
        }
        $i=1;
        $af = array();
        foreach($allfiles as $file => $num)
        {
            $af[$i++] = $file;
        }
        $allfiles = $af;
        usort($allfiles,'strnatcasecmp');
        $allfiles[0] = "Post-parsing";
        foreach($allfiles as $i => $a)
        {
            $allfiles[$i] = array('file' => $a);
        }
        $out = array();
        foreach($files as $file => $data)
        {
            if ($file == '##none') $file = 'Post-parsing';
            $out[$file] = $data;
        }
        $template->assign("files",$allfiles);
        $template->assign("all",$out);
        $template->assign("title","phpDocumentor Parser Errors and Warnings");
        $this->setTargetDir($this->base_dir);
        $this->writefile("errors.html",$template->fetch('errors.tpl'));
        unset($template);
        phpDocumentor_out("\n\nTo view errors and warnings, look at ".$this->base_dir. PATH_DELIMITER . "errors.html\n");
        flush();
    }
    
    function postProcess($text)
    {
        return str_replace("'", '&apos;', htmlentities($text));
    }

    function prepareDocBlock(&$element, $nopackage = true)
    {
        $a = new parserStringWithInlineTags;
        $a->add('no exceptions thrown');
        if (!$element->docblock->getKeyword('throws')) $element->docblock->addKeyword('throws',$a);
        $tags = parent::prepareDocBlock($element,
                array('staticvar' => 'note','deprec' => 'deprecated',
                      'abstract' => 'abstract','TODO' => 'note', 'link' => 'see',
                      'uses' => 'see', 'usedby' => 'see', 'tutorial' => 'see',
                      'return' => 'returns', 'access' => false), $nopackage);
        $ret = array();
        foreach($tags['tags'] as $tag)
        {
            if ($tag['keyword'] == 'return')
            {
                // hack because stupid Converter isn't doing its job
                $tag['keyword'] = 'returns';
            }
            $ret[$tag['keyword']][] = $tag;
        }
        $tags['tags'] = $ret;
        $tags['sdesc'] = $this->wordwrap($tags['sdesc']);
        return $tags;
    }
    
    function getTutorialId($package,$subpackage,$tutorial,$id,$category)
    {
        $subpackage = (empty($subpackage) ? '' : '.'.$subpackage);
        $id = (empty($id) ? '' : '.'.$id);
        return 'package.'.strtolower($category.'.'.$package.$subpackage.str_replace(array('_','.'),array('-','--'),$tutorial).$id);
    }
    
    
    /**
     * Retrieve a Converter-specific anchor to a segment of a source code file
     * parsed via a {@tutorial tags.filesource.pkg} tag.
     *
     * NOTE: unused
     * @param string full path to source file
     * @param string name of anchor
     * @param string link text, if this is a link
     * @param boolean returns either a link or a destination based on this
     *                parameter
     * @return string link to an anchor, or the anchor
     */
    function getSourceAnchor($sourcefile,$anchor,$text = '',$link = false)
    {
        return '';
    }
    
    function Br($input)
    {
        return "$input\n";
    }

    function getCData($value)
    {
        return '<![CDATA['.$value.']]>';
    }
    
    function ProgramExample($listing, $tutorial = false, $inlinesourceparse = null/*false*/,
                            $class = null/*false*/, $linenum = null/*false*/, $filesourcepath = null/*false*/, $origsource = null)
    {
        if ($origsource !== null) {
            $listing = $origsource;
        }
        if (!tokenizer_ext)
        {
            $listing = $this->getCData($listing);
        }
        return '<programlisting role="php">' . $this->getCData($listing) . '</programlisting>';
        return parent::ProgramExample($listing, false, $inlinesourceparse, $class, $linenum, $filesourcepath);
    }
    
    /**
     * Does nothing - use tutorials for DocBook
     * @param parserPackagePage
     */
    function convertPackagePage(&$element)
    {
    }
    
    /**
     * Convert tutorials for output
     * @param parserTutorial
     */
    function convertTutorial(&$element)
    {
        $template = &parent::convertTutorial($element);
        phpDocumentor_out("\n");
        flush();
        $x = $element->Convert($this,false);
        if ($element->ini)
        { // add child tutorial list to the tutorial through a slight hack :)
            $subtutorials = '';
            $b = '';
            if (!empty($element->subpackage)) $b = '.'.$element->subpackage;
            foreach($element->ini['Linked Tutorials'] as $child)
            {
                $subtutorials .= '      &'.$element->category.'.'.$element->package.$b.'.'.str_replace(array('_','.'),array('-','--'),$child).'-'.$element->tutorial_type."-tutorial;\n";
            }
            $x = str_replace('</refsect1></refentry>','</refsect1>
    <refsect1>
     <title>Related Docs</title>
     <para>
'.$subtutorials.
'     </para>
    </refsect1></refentry>',$x);
        }
        $template->assign('contents',$x);
        $contents = $template->fetch('tutorial.tpl');
        $a = '';
        if ($element->subpackage) $a = PATH_DELIMITER . $element->subpackage;
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . str_replace(array('_','.'),array('-','--'),strtolower($element->category))
          . PATH_DELIMITER . strtolower(str_replace(array('_','.'),array('-','--'),$element->package) . $a));
        $this->writeFile(str_replace(array('_','.'),array('-','--'),strtolower($element->name)).'-tutorial.xml',
            '<!-- $' . "Revision$ -->\n" . $contents);
    }
    
    /**
     * Does nothing in this converter
     * @param parserVar
     */
    function convertVar(&$element)
    {
        return;
        $docblock = $this->prepareDocBlock($element);
        $b = 'mixed';
        if ($element->docblock->var)
        {
            $b = $element->docblock->var->converted_returnType;
        }
//        var_dump($this->getFormattedOverrides($element));
        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        $this->class_summary->append('vars',array('sdesc' => $docblock['sdesc'],
                                               'desc' => $docblock['desc'],
                                               'tags' => $docblock['tags'],
                                               'var_name' => $this->type_adjust($element->getName()),
                                               'var_default' => htmlspecialchars($element->getValue()),
                                               'var_type' => $b,
                                               'var_overrides' => $this->getFormattedOverrides($element),
                                               'line_number' => $element->getLineNumber(),
                                               'id' => $this->getId($element)));
        else
        $this->class_data->append('vars',array('sdesc' => $docblock['sdesc'],
                                               'desc' => $docblock['desc'],
                                               'tags' => $docblock['tags'],
                                               'var_name' => $this->type_adjust($element->getName()),
                                               'var_default' => htmlspecialchars($element->getValue()),
                                               'var_type' => $b,
                                               'var_overrides' => $this->getFormattedOverrides($element),
                                               'line_number' => $element->getLineNumber(),
                                               'id' => $this->getId($element)));
    }
    
    /**
     * Converts class for template output
     * @param parserClass
     * @uses flushPackageXml() creates packagename.xml file when all classes in
     *       a package have been converted
     */
    function convertClass(&$element)
    {
        $this->flushPackageXml($element);
        parent::convertClass($element);
        $docblock = $this->prepareDocBlock($element);
        $this->method_data = array();
        $this->class_dir = str_replace(array('_','.'),array('-','--'),$element->docblock->package);
        $this->package = $element->docblock->package;
        $this->category = strtolower($element->docblock->category);
        if (!empty($element->docblock->subpackage)) $this->class_dir .= PATH_DELIMITER . $element->docblock->subpackage;
        $docblock = $this->prepareDocBlock($element,false);
        $this->class_data->assign("sdesc",$docblock['sdesc']);
        $this->class_data->assign("desc",$docblock['desc']);
        $this->class_data->assign("tags",$docblock['tags']);

        $this->class_data->assign("source_location",$element->getSourceLocation($this,$this->template_options['usepear']));
        $this->class_data->assign("id",$this->getId($element));
        $this->class_data->assign("method_ids",array());
        $this->left[$this->package][] = array('link' => $this->getId($element).'-summary');
        if ($t = $element->getTutorial())
        {
            $this->class_data->append("method_ids",$this->getId($t));
        }

        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        {
            $this->class_summary = &$this->newSmarty(true);
            if ($t = $element->getTutorial())
            {
                $this->class_summary->assign("tutorial",$this->returnSee($t));
            }

            $this->class_summary->assign("class_name",$this->type_adjust($element->getName()));
            $this->class_summary->assign("sdesc",$docblock['sdesc']);
            $this->class_summary->assign("desc",$docblock['desc']);
            $this->class_summary->assign("tags",$docblock['tags']);
            $this->class_summary->assign("vars",array());
            $this->class_summary->assign("methods",array());
            $this->class_summary->assign("package",$element->docblock->package);

            $this->class_summary->assign("children", $this->generateChildClassList($element));
            $this->class_summary->assign("class_tree", $this->generateFormattedClassTree($element));
            $this->class_summary->assign("conflicts", $this->getFormattedConflicts($element,"classes"));
        
            $this->class_summary->assign("source_location",$element->getSourceLocation($this,$this->template_options['usepear']));
            $this->class_summary->assign("id",$this->getId($element).'-summary');
            $this->class_data->append("method_ids",$this->getId($element).'.'.strtolower(str_replace('_','-',$element->getName())).'-summary');
            $inherited_methods = $this->getFormattedInheritedMethods($element);
            if (!empty($inherited_methods))
            {
                $this->class_summary->assign("imethods",$inherited_methods);
            }
            $inherited_vars = $this->getFormattedInheritedVars($element);
            // variables are irrelevant in peardoc2
            if (false)//!empty($inherited_vars))
            {
                $this->class_summary->assign("ivars",$inherited_vars);
            }
            $this->addSummaryToPackageXml($this->class_summary->fetch('class_summary.tpl'));
        }
        $this->sourceloc = $element->getSourceLocation($this,$this->template_options['usepear']);
    }
    
    /**
     * Converts method for template output
     * @see prepareDocBlock(), parserMethod::getFunctionCall(), getFormattedDescMethods(), getFormattedOverrides()
     * @param parserMethod
     */
    function convertMethod(&$element)
    {
        $docblock = $this->prepareDocBlock($element);
        $returntype = 'void';
        if ($element->docblock->return)
        {
            $a = $element->docblock->return->Convert($this);
            $returntype = $element->docblock->return->converted_returnType;
            if ($returntype != $element->docblock->return->returnType)
            {
                $returntype = "<replaceable>$returntype</replaceable>";
            }
        }
        $params = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $b = explode(' ',$a);
            $c = '';
            foreach($b as $blah) {
                if (!empty($c)) {
                    $c .= ' ';
                }
                $c .= str_replace(array('true', 'false', 'null'), array('&true;', '&false;', '&null;'), $blah);
            }
            $params[$param] = array("var" => $param,"datatype" => str_replace(array('true', 'false', 'null'), array('&true;', '&false;', '&null;'), 
                $val->returnType), "cdatatype" => $val->converted_returnType,"data" => $this->wordwrap($c));
        }

        $call = $element->getIntricateFunctionCall($this, $params);
        if (isset($call['params']))
        {
            foreach($call['params'] as $i => $param)
            {
                if (!is_string($call['params'][$i]['default']))
                {
                    continue;
                }
                $call['params'][$i]['default'] = str_replace(array('true', 'false', 'null'), array('&true;', '&false;', '&null;'), $param['default']);
            }
        }
        $this->packagexml->append('ids','&'.$this->getId($element).';');
        $this->class_data->append('method_ids',$this->getId($element));
        $this->class_summary->append('methods',array('id' => $this->getId($element),
                                                  'sdesc' => $docblock['sdesc'],
                                                  'desc' => $docblock['desc'],
                                                  'tags' => $docblock['tags'],
                                                  'is_constructor' => $element->isConstructor,
                                                  'function_name' => $element->getName(),
                                                  'function_return' => $returntype,
                                                  'function_call' => $call,
                                                  'descmethod' => $this->getFormattedDescMethods($element),
                                                  'method_overrides' => $this->getFormattedOverrides($element),
                                                  'line_number' => $element->getLineNumber(),
                                                  'params' => $params));
        $this->method_data[$i = count($this->method_data) - 1][0] = &$this->newSmarty(true);
        $this->method_data[$i][1] = str_replace(array('_','.'),array('-','--'),$element->getName());
        $this->method_data[$i][0]->assign('class',$this->class);
        $this->method_data[$i][0]->assign('source_location',$this->returnSee($this->getLink(basename($this->curpage->getFile())),$this->sourceloc));
        $this->method_data[$i][0]->assign('sdesc',$docblock['sdesc']);
        $this->method_data[$i][0]->assign('desc',$docblock['desc']);
        $this->method_data[$i][0]->assign('tags',$docblock['tags']);
        $this->method_data[$i][0]->assign('function_name',$element->getName());
        $this->method_data[$i][0]->assign('function_return',$returntype);
        $this->method_data[$i][0]->assign('function_call',$call);
        $this->method_data[$i][0]->assign('descmethod',$this->getFormattedDescMethods($element));
        $this->method_data[$i][0]->assign('method_overrides',$this->getFormattedOverrides($element));
        $this->method_data[$i][0]->assign('params',$params);
        $this->method_data[$i][0]->assign('id',$this->getId($element));
    }
    
    /**
     * Converts function for template output - does nothing in peardoc2!
     * @param parserFunction
     */
    function convertFunction(&$element)
    {
/*        parent::convertFunction($element);
        $docblock = $this->prepareDocBlock($element);
        $fname = $element->getName();
        $params = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $params[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }
        $returntype = 'void';
        if ($element->docblock->return)
        {
            $a = $element->docblock->return->Convert($this);
            $returntype = $element->docblock->return->converted_returnType;
        }

        $this->page_data->append("function_ids",$this->getId($element));
        $this->page_summary->append("function_ids",$this->getId($element));
        $this->page_summary->append('functions',array('id' => $this->getId($element),
                                                   'sdesc' => $docblock['sdesc'],
                                                   'desc' => $docblock['desc'],
                                                   'tags' => $docblock['tags'],
                                                   'function_name' => $element->getName(),
                                                   'line_number' => $element->getLineNumber(),
                                                   'function_return' => $returntype,
                                                   'function_call' => $element->getIntricateFunctionCall($this,$params),
                                                   'function_conflicts' => $this->getFormattedConflicts($element,'functions'),
                                                   'params' => $params));
        $this->function_data[$i = count($this->function_data) - 1][0] = $this->newSmarty(true);
        $this->function_data[$i][1] = $element->getName();
        $this->function_data[$i][0]->assign('sdesc',$docblock['sdesc']);
        $this->function_data[$i][0]->assign('desc',$docblock['desc']);
        $this->function_data[$i][0]->assign('tags',$docblock['tags']);
        $this->function_data[$i][0]->assign('function_name',$fname);
        $this->function_data[$i][0]->assign('line_number',$element->getLineNumber());
        $this->function_data[$i][0]->assign('function_return',$returntype);
        $this->function_data[$i][0]->assign('function_call',$element->getIntricateFunctionCall($this,$params));
        $this->function_data[$i][0]->assign('function_conflicts',$this->getFormattedConflicts($element,"functions"));
        $this->function_data[$i][0]->assign('params',$params);
        $this->function_data[$i][0]->assign('source_location',$this->returnSee($this->getLink(basename($this->curpage->getFile())),$this->sourceloc));
        $this->function_data[$i][0]->assign('id',$this->getId($element));*/
    }
    
    /**
     * Converts include elements for template output
     *
     * Completely ignored by this converter
     * @param parserInclude
     */
    function convertInclude(&$element)
    {
/*        parent::convertInclude($element, array('include_file'    => '-'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '-'))));
        $docblock = $this->prepareDocBlock($element);
        $per = $this->getIncludeValue($element->getValue(), $element->getPath());
        $this->page_summary->append('includes',array('sdesc' => $docblock['sdesc'],
                                                   'desc' => $docblock['desc'],
                                                  'tags' => $docblock['tags'],
                                                  'utags' => $docblock['utags'],
                                                  'include_name'     => $element->getName(),
                                                  'include_value'    => $per,
                                                  'line_number' => $element->getLineNumber(),
                                                  'include_file'    => '-'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '-'))));*/
    }
    
    /**
     * Converts defines for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertDefine(&$element)
    {
        $docblock = $this->prepareDocBlock($element);
        $this->_appendDefines(array('sdesc' => $docblock['sdesc'],
                                   'desc' => $docblock['desc'],
                                   'tags' => $docblock['tags'],
                                   'name'     => $this->postProcess($element->getName()),
                                   'value'    => $this->postProcess($element->getValue()),
                                   'conflicts'    => $this->getFormattedConflicts($element,"defines"),
                                   'line_number' => $element->getLineNumber(),
                                   'id' => $this->getId($element)));
    }
    
    /**
     * Append the constant information to the Smarty information
     *
     * Uses category, package, and current file to organize constants defined
     * in a package for the constants.xml output file
     * @param array
     * @uses $_peardoc2_constants appends $define to them
     * @access private
     */
    function _appendDefines($define)
    {
        if (!isset($this->_peardoc2_constants[$this->category][$this->package][$this->sourceloc]))
        {
            $this->_peardoc2_constants[$this->category][$this->package][$this->sourceloc]['name'] = 
                $this->sourceloc;
            $this->_peardoc2_constants[$this->category][$this->package][$this->sourceloc]['page'] =
                $this->page;
        }
        $this->_write_constants_xml[$this->category][$this->package] = true;
        $this->_peardoc2_constants[$this->category][$this->package][$this->sourceloc]['defines'][] = $define;
    }
    
    /**
     * Converts global variables for template output
     * @param parserGlobal
     * @see prepareDocBlock(), getFormattedConflicts()
     */
    function convertGlobal(&$element)
    {
        $docblock = $this->prepareDocBlock($element);
        $value = $this->getGlobalValue($element->getValue());
        if ($value == $element->getValue())
        {
            $value = $this->ProgramExample($value);
        } else
        {
            $value = $this->getGlobalValue('<![CDATA[' .$element->getValue() . ']]>');
        }
        $this->_appendGlobals(array('sdesc' => $docblock['sdesc'],
                                   'desc' => $docblock['desc'],
                                   'tags' => $docblock['tags'],
                                   'name'     => $this->postProcess($element->getName()),
                                   'link'    => $element->getName(),
                                   'value'    => $value,
                                   'type' => $element->getDataType($this),
                                   'line_number' => $element->getLineNumber(),
                                   'conflicts'    => $this->getFormattedConflicts($element,"global variables"),
                                   'id' => $this->getId($element)));
    }
    
    /**
     * Append the global variable information to the Smarty information
     *
     * Uses category, package, and current file to organize globals defined
     * in a package for the globals.xml output file
     * @param array
     * @uses $_peardoc2_globals appends $global to them
     * @access private
     */
    function _appendGlobals($global)
    {
        if (!isset($this->_peardoc2_globals[$this->category][$this->package][$this->sourceloc]))
        {
            $this->_peardoc2_globals[$this->category][$this->package][$this->sourceloc]['name'] = 
                $this->sourceloc;
            $this->_peardoc2_globals[$this->category][$this->package][$this->sourceloc]['page'] =
                $this->page;
        }
        $this->_write_globals_xml[$this->category][$this->package] = true;
        $this->_peardoc2_globals[$this->category][$this->package][$this->sourceloc]['globals'][] = $global;
    }
    
    /**
     * converts procedural pages for template output
     * @see prepareDocBlock(), getClassesOnPage()
     * @param parserData
     */
    function convertPage(&$element)
    {
        parent::convertPage($element);
        $this->juststarted = true;
        $this->page_dir = $element->parent->package;
        $this->page = $this->getPageName($element->parent);
        $this->category = strtolower($element->parent->category);
        $this->sourceloc = $element->parent->getSourceLocation($this,true);
        if (!empty($element->parent->subpackage)) $this->page_dir .= PATH_DELIMITER . $element->parent->subpackage;
        // registering stuff on the template
    }
    
    function getPageName(&$element)
    {
        return str_replace(array('/','_','.'),array('-','-','---'),$element->getSourceLocation($this,true));
    }

    /**
     * returns an array containing the class inheritance tree from the root object to the class
     *
     * @param parserClass    class variable
     * @return array Format: array(root,child,child,child,...,$class)
     * @uses parserClass::getParentClassTree()
     */
    
    function generateFormattedClassTree($class)
    {
        $tree = $class->getParentClassTree($this);
        $out = '';
        if (count($tree) - 1)
        {
            $result = array($class->getName());
            $parent = $tree[$class->getName()];
            while ($parent)
            {
                if (is_string($parent)) {
                    $result[] = $parent;
                    break;
                }
                $subpackage = $parent->docblock->subpackage;
                $package = $parent->docblock->package;
                $x = $parent;
                if (is_object($parent))
                $x = $parent->getLink($this);
                if (!$x) $x = $parent->getName();
                $result[] = 
                    $x;
                if (is_object($parent))
                $parent = $tree[$parent->getName()];
                elseif (isset($tree[$parent]))
                $parent = $tree[$parent];
            }
            return array_reverse($result);
        } else
        {
            return array($class->getName());
        }
    }
    
    /**
     * returns a list of child classes
     *
     * @param parserClass class variable
     * @uses parserClass::getChildClassList()
     */
    
    function generateChildClassList($class)
    {
        $kids = $class->getChildClassList($this);
        $list = array();
        if (count($kids))
        {
            for($i=0; $i<count($kids); $i++)
            {
                $lt['link'] = '<link linkend="'.$this->getId($kids[$i]) . '-summary">'. $kids[$i]->getName().'</link>';
                $lt['sdesc'] = $kids[$i]->docblock->getSDesc($this);
                $list[] = $lt;
            }
        } else return false;
        return $list;
    }

    /** @access private */
    function sortVar($a, $b)
    {
        return strnatcasecmp($a->getName(),$b->getName());
    }
    
    /** @access private */
    function sortMethod($a, $b)
    {
        if ($a->isConstructor) return -1;
        if ($b->isConstructor) return 1;
        return strnatcasecmp($a->getName(),$b->getName());
    }

    /**
     * returns a template-enabled array of class trees
     * 
     * @param    string    $package    package to generate a class tree for
     * @see $roots, HTMLConverter::getRootTree()
     */
    function generateFormattedClassTrees($package)
    {
        if (!isset($this->roots['normal'][$package]) &&
              !isset($this->roots['special'][$package])) {
            return array();
        }
        $trees = array();
        if (isset($this->roots['normal'][$package])) {
            $roots = $this->roots['normal'][$package];
            for($i=0;$i<count($roots);$i++)
            {
                $root = $this->classes->getClassByPackage($roots[$i], $package);
                if ($root && $root->isInterface()) {
                    continue;
                }
                $trees[] = array('class' => $roots[$i],'class_tree' => "<ul>\n".$this->getRootTree($this->getSortedClassTreeFromClass($roots[$i],$package,''),$package)."</ul>\n");
            }
        }
        if (isset($this->roots['special'][$package])) {
            $roots = $this->roots['special'][$package];
            foreach ($roots as $parent => $classes) {
                $thistree = '';
                foreach ($classes as $classinfo) {
                    $root = $this->classes->getClassByPackage($classinfo, $package);
                    if ($root && $root->isInterface()) {
                        continue;
                    }
                    $thistree .=
                        $this->getRootTree(
                            $this->getSortedClassTreeFromClass(
                                $classinfo,
                                $package,
                                ''),
                            $package,
                            true);
                }
                if (!$thistree) {
                    continue;
                }
                $trees[] = array(
                    'class' => $parent,
                    'class_tree' => "<ul>\n" . $thistree . "</ul>\n"
                );
            }
        }
        return $trees;
    }

    /**
     * returns a template-enabled array of interface inheritance trees
     * 
     * @param    string    $package    package to generate a class tree for
     * @see $roots, HTMLConverter::getRootTree()
     */
    function generateFormattedInterfaceTrees($package)
    {
        if (!isset($this->roots['normal'][$package]) &&
              !isset($this->roots['special'][$package])) {
            return array();
        }
        $trees = array();
        if (isset($this->roots['normal'][$package])) {
            $roots = $this->roots['normal'][$package];
            for($i=0;$i<count($roots);$i++)
            {
                $root = $this->classes->getClassByPackage($roots[$i], $package);
                if ($root && !$root->isInterface()) {
                    continue;
                }
                $trees[] = array('class' => $roots[$i],'class_tree' => "<ul>\n".$this->getRootTree($this->getSortedClassTreeFromClass($roots[$i],$package,''),$package)."</ul>\n");
            }
        }
        if (isset($this->roots['special'][$package])) {
            $roots = $this->roots['special'][$package];
            foreach ($roots as $parent => $classes) {
                $thistree = '';
                foreach ($classes as $classinfo) {
                    $root = $this->classes->getClassByPackage($classinfo, $package);
                    if ($root && !$root->isInterface()) {
                        continue;
                    }
                    $thistree .=
                        $this->getRootTree(
                            $this->getSortedClassTreeFromClass(
                                $classinfo,
                                $package,
                                ''),
                            $package,
                            true);
                }
                if (!$thistree) {
                    continue;
                }
                $trees[] = array(
                    'class' => $parent,
                    'class_tree' => "<ul>\n" . $thistree . "</ul>\n"
                );
            }
        }
        return $trees;
    }
    
    /**
     * return formatted class tree for the Class Trees page
     *
     * @param array $tree output from {@link getSortedClassTreeFromClass()}
     * @param string $package  package
     * @param boolean $nounknownparent if true, an object's parent will not be checked
     * @see Classes::$definitechild, generateFormattedClassTrees()
     * @return string
     */
    function getRootTree($tree, $package, $noparent = false)
    {
        if (!$tree) return '';
        $my_tree = '';
        $cur = '#root';
        $lastcur = array(false);
        $kids = array();
        $dopar = false;
        if (!$noparent && $tree[$cur]['parent'])
        {
            $dopar = true;
            if (!is_object($tree[$cur]['parent']))
            {
//                debug("parent ".$tree[$cur]['parent']." not found");
                $my_tree .= '<listitem>' . $tree[$cur]['parent'] .'<itemizedlist>';
            }
            else
            {
//                        debug("parent ".$this->returnSee($tree[$cur]['parent'], false, false)." in other package");
                $my_tree .= '<listitem>' . $this->returnSee($tree[$cur]['parent'], false, false);
                if ($tree[$cur]['parent']->package != $package) $my_tree .= ' <emphasis>(Different package)</emphasis><itemizedlist>';
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
                    $my_tree .= '<listitem>'.$this->returnSee($tree[$cur]['link'], false, false);
                    $my_tree .= '<itemizedlist>'."\n";
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
                    $my_tree .= '</itemizedlist></listitem>'."\n";
                    if ($dopar && ($cur == '#root' || !$cur)) $my_tree .= '</itemizedlist></listitem>';
                }
            } else 
            {
//                debug("$cur has no children");
                $my_tree .= '<listitem>'.$this->returnSee($tree[$cur]['link'], false, false)."</listitem>";
                if ($dopar && $cur == '#root') $my_tree .= '</itemizedlist></listitem>';
                $cur = array_pop($lastcur);
            }
        } while ($cur);
        return $my_tree;
    }
    /**
     * does nothing
     */
    function generateElementIndex()
    {
    }
    
    function setTemplateDir($dir)
    {
        Converter::setTemplateDir($dir);
        $this->smarty_dir = $this->templateDir;
    }
    
    /**
     * Generate alphabetical index of all elements by package and subpackage
     *
     * @param string $package name of a package
     * @see $pkg_elements, walk(), generatePkgElementIndexes()
     */
    function generatePkgElementIndex($package)
    {
    }
    
    /**
     *
     * @see generatePkgElementIndex()
     */
    function generatePkgElementIndexes()
    {
    }
    
    /**
     * @param string name of class
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the class's documentation
     * @see parent::getClassLink()
     */
    function getClassLink($expr,$package, $file = false,$text = false, $local = true, $with_a = true)
    {
        $a = Converter::getClassLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local, $with_a);
    }

    /**
     * @param string name of function
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the function's documentation
     * @see parent::getFunctionLink()
     */
    function getFunctionLink($expr,$package, $file = false,$text = false, $local = true)
    {
        $a = Converter::getFunctionLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
    }

    /**
     * @param string name of define
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the define's documentation
     * @see parent::getDefineLink()
     */
    function getDefineLink($expr,$package, $file = false,$text = false, $local = true)
    {
        $a = Converter::getDefineLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
    }

    /**
     * @param string name of global variable
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the global variable's documentation
     * @see parent::getGlobalLink()
     */
    function getGlobalLink($expr,$package, $file = false,$text = false, $local = true)
    {
        $a = Converter::getGlobalLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
    }

    /**
     * @param string name of procedural page
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the procedural page's documentation
     * @see parent::getPageLink()
     */
    function getPageLink($expr,$package, $path = false,$text = false, $local = true)
    {
        $a = Converter::getPageLink($expr,$package,$path);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
    }

    /**
     * @param string name of method
     * @param string class containing method
     * @param string package name
     * @param string full path to look in (used in index generation)
     * @param boolean deprecated
     * @param boolean return just the URL, or enclose it in an html a tag
     * @return mixed false if not found, or an html a link to the method's documentation
     * @see parent::getMethodLink()
     */
    function getMethodLink($expr,$class,$package, $file = false,$text = false, $local = true)
    {
        $a = Converter::getMethodLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
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
    function getVarLink($expr,$class,$package, $file = false,$text = false, $local = true)
    {
        $a = Converter::getVarLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $local);
    }
    
    /**
     * does a nat case sort on the specified second level value of the array
     *
     * @param    mixed    $a
     * @param    mixed    $b
     * @return    int
     */
    function rcNatCmp ($a, $b)
    {
        $aa = strtoupper($a[$this->rcnatcmpkey]);
        $bb = strtoupper($b[$this->rcnatcmpkey]);
        
        return strnatcasecmp($aa, $bb);
    }
    
    /**
     * does a nat case sort on the specified second level value of the array.
     * this one puts constructors first
     *
     * @param    mixed    $a
     * @param    mixed    $b
     * @return    int
     */
    function rcNatCmp1 ($a, $b)
    {
        $aa = strtoupper($a[$this->rcnatcmpkey]);
        $bb = strtoupper($b[$this->rcnatcmpkey]);
        
        if (strpos($aa,'CONSTRUCTOR') === 0)
        {
            return -1;
        }
        if (strpos($bb,'CONSTRUCTOR') === 0)
        {
            return 1;
        }
        if (strpos($aa,strtoupper($this->class)) === 0)
        {
            return -1;
        }
        if (strpos($bb,strtoupper($this->class)) === 0)
        {
            return -1;
        }
        return strnatcasecmp($aa, $bb);
    }
    
    function wordwrap($string)
    {
        return wordwrap($string);
    }
    
    /**
     * Generate the constants.xml, packagename.xml, and globals.xml files
     */
    function Output()
    {
        $this->flushPackageXml(false);
        $templ = &$this->newSmarty();
        $categories = array();
        $packages = array_flip($this->all_packages);
        foreach($this->packagecategories as $package => $category)
        {
            $categories[$category]['package.'.$category.'.'.str_replace('_','-',strtolower($package ))] = 1;
            if (isset($packages[$package])) unset($packages[$package]);
        }
        $category = $GLOBALS['phpDocumentor_DefaultCategoryName'];
        foreach($packages as $package)
        {
            $categories[$category]['package.'.$category.'.'.str_replace('_','-',strtolower($package ))] = 1;
        }
        foreach($categories as $category => $ids)
        {
            $templ->assign('id','package.'.$category);
            $templ->assign('ids',array());
            $templ->assign('category',$category);
            $this->setTargetDir($this->base_dir);
            if (file_exists($this->base_dir . PATH_DELIMITER . strtolower($category ) . '.xml'))
            {
                $contents = @file($this->base_dir . PATH_DELIMITER . strtolower($category ) . '.xml');
                if (is_array($contents))
                {
                    $found = false;
                    foreach($contents as $i => $line)
                    {
                        $line = trim($line);
                        if (strlen($line) && $line{0} == '&')
                        {
                            $found = $i;
                            if (in_array(str_replace(array ('&', ';'), array ('', ''), trim($line )), array_keys($ids )))
                            {
                                unset($ids[str_replace(array('&', ';'), array('', ''), trim($line))]);
                            }
                        }
                        if ($found !== false && (!strlen($line) || $line{0} != '&'))
                        {
                            break;
                        }
                    }
                    $newids = array();
                    foreach($ids as $id => $unll)
                    {
                        $newids[] = ' &' . $id . ";\n";
                    }
                    $newcontents = array_merge(array_slice($contents, 0, $i), $newids);
                    $newcontents = array_merge($newcontents, array_slice($contents, $i));
                }
                $categorycontents = implode($newcontents, '');
            } else
            {
                foreach($ids as $id => $unll)
                {
                    if (!in_array($id, $templ->_tpl_vars['ids']))
                    {
                        $templ->append('ids',$id);
                    }
                }
                $categorycontents = '<!-- $' . "Revision$ -->\n" . $templ->fetch('category.tpl');
            }
            $this->writefile(strtolower($category) . '.xml',
                $categorycontents);
            phpDocumentor_out("\n");
            flush();
        }
        $my = &$this->newSmarty();
        if ($this->_peardoc2_constants)
        {
            foreach($this->_peardoc2_constants as $category => $r)
            {
                foreach($r as $package => $s)
                {
                    $my->assign('id','package.'.strtolower($category.'.'.str_replace('_','-',strtolower($package ))).'.constants');
                    $my->assign('package',$package);
                    $defines = array();
                    foreach($s as $file => $t)
                    {
                        $arr = array();
                        $arr['name'] = $file;
                        $arr['page'] = strtolower($t['page']);
                        $arr['defines'] = $t['defines'];
                        $defines[] = $arr;
                    }
                    $my->assign('defines',$defines);
                    $this->setTargetDir($this->base_dir . PATH_DELIMITER . $category
                       . PATH_DELIMITER . strtolower(str_replace('_','-',strtolower($package ))));
                    $this->writefile('constants.xml',
                        '<!-- $' . "Revision$ -->\n" . $my->fetch('constants.tpl'));
                    $my->clear_all_assign();
                }
            }
            $this->_peardoc2_constants = false;
        }
        if ($this->_peardoc2_globals)
        {
            foreach($this->_peardoc2_globals as $category => $r)
            {
                foreach($r as $package => $s)
                {
                    $my->assign('id','package.'.strtolower($category.'.'.str_replace('_','-',strtolower($package ))).'.globals');
                    $my->assign('package',$package);
                    $defines = array();
                    foreach($s as $file => $t)
                    {
                        $arr = array();
                        $arr['name'] = $file;
                        $arr['page'] = strtolower($t['page']);
                        $arr['globals'] = $t['globals'];
                        $defines[] = $arr;
                    }
                    $my->assign('globals',$defines);
                    $this->setTargetDir($this->base_dir . PATH_DELIMITER . $category
                       . PATH_DELIMITER . strtolower(str_replace('_','-',strtolower($package ))));
                    $this->writefile('globals.xml',
                        '<!-- $' . "Revision$ -->\n" . $my->fetch('globals.tpl'));
                    $my->clear_all_assign();
                }
            }
            $this->_peardoc2_globals = false;
        }
    }
}
?>
