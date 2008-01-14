<?php
/**
 * Base class for all Converters
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2001-2006 Gregory Beaver
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
 * @category   documentation
 * @package    Converters
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2001-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        parserDocBlock, parserInclude, parserPage, parserClass
 * @see        parserDefine, parserFunction, parserMethod, parserVar
 * @since      1.0rc1
 */
/**
 * Smarty template files
 */
include_once("phpDocumentor/Smarty-2.6.0/libs/Smarty.class.php");
/**
 * Base class for all output converters.
 *
 * The Converter marks the final stage in phpDocumentor.  phpDocumentor works
 * in this order:
 *
 * <pre>Parsing => Intermediate Parsing organization => Conversion to output</pre>
 *
 * A Converter takes output from the {@link phpDocumentor_IntermediateParser} and
 * converts it to output.  With version 1.2, phpDocumentor includes a variety
 * of output converters:
 * <ul>
 *  <li>{@link HTMLframesConverter}</li>
 *  <li>{@link HTMLSmartyConverter}</li>
 *  <li>{@link PDFdefaultConverter}</li>
 *  <li>{@link CHMdefaultConverter}</li>
 *  <li>{@link CSVdia2codeConverter}</li>
 *  <li>{@link XMLDocBookConverter}</li>
 * </ul>
 * {@internal
 * The converter takes output directly from {@link phpDocumentor_IntermediateParser}
 * and using {@link walk()} or {@link walk_everything} (depending on the value of
 * {@link $sort_absolutely_everything}) it "walks" over an array of phpDocumentor elements.}}
 *
 * @package Converters
 * @abstract
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id$
 */
class Converter
{
    /**
     * This converter knows about the new root tree processing
     * In order to fix PEAR Bug #6389
     * @var boolean
     */
    var $processSpecialRoots = false;
    /**
     * output format of this converter
     *
     * in Child converters, this will match the first part of the -o command-line
     * as in -o HTML:frames:default "HTML"
     * @tutorial phpDocumentor.howto.pkg#using.command-line.output
     * @var string
     */
    var $outputformat = 'Generic';
    /**
     * package name currently being converted
     * @var string
     */
    var $package = 'default';
    /**
     * subpackage name currently being converted
     * @var string
     */
    var $subpackage = '';
    /**
     * set to a classname if currently parsing a class, false if not
     * @var string|false
     */
    var $class = false;
    /**#@+
     * @access private
     */
    /**
     * the workhorse of linking.
     *
     * This array is an array of link objects of format:
     * [package][subpackage][eltype][elname] = descendant of {@link abstractLink}
     * eltype can be page|function|define|class|method|var
     * if eltype is method or var, the array format is:
     * [package][subpackage][eltype][class][elname]
     * @var array
     * @see functionLink, pageLink, classLink, defineLink, methodLink, varLink, globalLink
     */
    var $links = array();

    /**
     * the workhorse of linking, with allowance for support of multiple
     * elements in different files.
     *
     * This array is an array of link objects of format:
     * [package][subpackage][eltype][file][elname] = descendant of {@link abstractLink}
     * eltype can be function|define|class|method|var
     * if eltype is method or var, the array format is:
     * [package][subpackage][eltype][file][class][elname]
     * @var array
     * @see functionLink, pageLink, classLink, defineLink, methodLink, varLink, globalLink
    */
    var $linkswithfile = array();
    /**#@-*/
    /**
     * set to value of -po commandline
     * @tutorial phpDocumentor.howto.pkg#using.command-line.packageoutput
     * @var mixed
     */
    var $package_output;

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
     * template for the procedural page currently being processed
     * @var Smarty
     */
    var $page_data;

    /**
     * template for the class currently being processed
     * @var Smarty
     */
    var $class_data;

    /**
     * current procedural page being processed
     * @var parserPage
     */
    var $curpage;
    /**
     * alphabetical index of all elements sorted by package, subpackage, page,
     * and class.
     * @var array Format: array(package => array(subpackage => array('page'|'class' => array(path|classname => array(element, element,...)))))
     * @uses $sort_absolutely_everything if true, then $package_elements is used,
     *       otherwise, the {@link ParserData::$classelements} and
     *       {@link ParserData::$pageelements} variables are used
     */
    var $package_elements = array();
    /**
     * alphabetical index of all elements
     *
     * @var array Format: array(first letter of element name => array({@link parserElement} or {@link parserPage},...))
     * @see formatIndex(), HTMLframesConverter::formatIndex()
     */
    var $elements = array();
    /**
     * alphabetized index of procedural pages by package
     *
     * @see $leftindex
     * @var array Format: array(package => array(subpackage => array({@link pageLink} 1,{@link pageLink} 2,...)
     */
    var $page_elements = array();
    /**
     * alphabetized index of defines by package
     *
     * @see $leftindex
     * @var array Format: array(package => array(subpackage => array({@link defineLink} 1,{@link defineLink} 2,...)
     */
    var $define_elements = array();
    /**
     * alphabetized index of classes by package
     *
     * @see $leftindex
     * @var array Format: array(package => array(subpackage => array({@link classLink} 1,{@link classLink} 2,...)
     */
    var $class_elements = array();
    /**
     * alphabetized index of global variables by package
     *
     * @see $leftindex
     * @var array Format: array(package => array(subpackage => array({@link globalLink} 1,{@link globalLink} 2,...)
     */
    var $global_elements = array();
    /**
     * alphabetized index of functions by package
     *
     * @see $leftindex
     * @var array Format: array(package => array(subpackage => array({@link functionLink} 1,{@link functionLink} 2,...)
     */
    var $function_elements = array();
    /**
     * alphabetical index of all elements, indexed by package/subpackage
     *
     * @var array Format: array(first letter of element name => array({@link parserElement} or {@link parserPage},...))
     * @see formatPkgIndex(), HTMLframesConverter::formatPkgIndex()
     */
    var $pkg_elements = array();

    /**
     * alphabetical index of all elements on a page by package/subpackage
     *
     * The page itself has a link under ###main
     * @var array Format: array(package => array(subpackage => array(path => array({@link abstractLink} descendant 1, ...)))
     * @see formatLeftIndex()
     */
    var $page_contents = array();

    /**
     * This determines whether the {@link $page_contents} array should be sorted by element type as well as alphabetically by name
     * @see sortPageContentsByElementType()
     * @var boolean
     */
    var $sort_page_contents_by_type = false;
    /**
     * This is used if the content must be passed in the order it should be read, i.e. by package, procedural then classes
     *
     * This fixes bug 637921, and is used by {@link PDFdefaultConverter}
     */
    var $sort_absolutely_everything = false;
    /**
     * alphabetical index of all methods and vars in a class by package/subpackage
     *
     * The class itself has a link under ###main
     * @var array
     * Format:<pre>
     * array(package =>
     *       array(subpackage =>
     *             array(path =>
     *                   array(class =>
     *                         array({@link abstractLink} descendant 1, ...
     *                        )
     *                  )
     *            )
     *      )</pre>
     * @see formatLeftIndex()
     */
    var $class_contents = array();
    /**
     * controls processing of elements marked private with @access private
     *
     * defaults to false.  Set with command-line --parseprivate or -pp
     * @var bool
     */
    var $parseprivate;
    /**
     * controls display of progress information while parsing.
     *
     * defaults to false.  Set to true for cron jobs or other situations where no visual output is necessary
     * @var bool
     */
    var $quietmode;

    /**
     * directory that output is sent to. -t command-line sets this.
     * @tutorial phpDocumentor.howto.pkg#using.command-line.target
     */
    var $targetDir = '';

    /**
     * Directory that the template is in, relative to phpDocumentor root directory
     * @var string
     */
    var $templateDir = '';

    /**
     * Directory that the smarty templates are in
     * @var string
     */
    var $smarty_dir = '';

    /**
     * Name of the template, from last part of -o
     * @tutorial phpDocumentor.howto.pkg#using.command-line.output
     * @var string
     */
    var $templateName = '';

    /**
     * full path of the current file being converted
     */
    var $curfile;

    /**
     * All class information, organized by path, and by package
     * @var Classes
     */
    var $classes;

    /**
     * Flag used to help converters determine whether to do special source highlighting
     * @var boolean
     */
    var $highlightingSource = false;

    /**
     * Hierarchy of packages
     *
     * Every package that contains classes may have parent or child classes
     * in other packages.  In other words, this code is legal:
     *
     * <code>
     * /**
     *  * @package one
     *  * /
     * class one {}
     *
     * /**
     *  * @package two
     *  * /
     * class two extends one {}
     * </code>
     *
     * In this case, package one is a parent of package two
     * @var array
     * @see phpDocumentor_IntermediateParser::$package_parents
     */
    var $package_parents;

    /**
     * Packages associated with categories
     *
     * Used by the XML:DocBook/peardoc2 converter, and available to others, to
     * group many packages into categories
     * @see phpDocumentor_IntermediateParser::$packagecategories
     * @var array
     */
    var $packagecategories;

    /**
     * All packages encountered in parsing
     * @var array
     * @see phpDocumentor_IntermediateParser::$all_packages
     */
    var $all_packages;

    /**
     * A list of files that have had source code generated
     * @var array
     */
    var $sourcePaths = array();

    /**
     * Controls which of the one-element-only indexes are generated.
     *
     * Generation of these indexes for large packages is time-consuming.  This is an optimization feature.  An
     * example of how to use this is in {@link HTMLframesConverter::$leftindex}, and in {@link HTMLframesConverter::formatLeftIndex()}.
     * These indexes are intended for use as navigational aids through documentation, but can be used for anything by converters.
     * @see $class_elements, $page_elements, $function_elements, $define_elements, $global_elements
     * @see formatLeftIndex()
     * @var array
     */
    var $leftindex = array('classes' => true, 'pages' => true, 'functions' => true, 'defines' => true, 'globals' => true);

    /** @access private */
    var $killclass = false;
    /**
     * @var string
     * @see phpDocumentor_IntermediateParser::$title
     */
    var $title = 'Generated Documentation';

    /**
     * Options for each template, parsed from the options.ini file in the template base directory
     * @tutorial phpDocumentor/tutorials.pkg#conversion.ppage
     * @var array
     */
    var $template_options;

    /**
     * Tutorials and Extended Documentation parsed from a tutorials/package[/subpackage] directory
     * @tutorial tutorials.pkg
     * @access private
     */
    var $tutorials = array();

    /**
     * tree-format structure of tutorials and their child tutorials, if any
     * @var array
     * @access private
     */
    var $tutorial_tree = false;

    /**
     * list of tutorials that have already been processed. Used by @link _setupTutorialTree()
     * @var array
     * @access private
     */
    var $processed_tutorials;

    /**
     * List of all @todo tags and a link to the element with the @todo
     *
     * Format: array(package => array(link to element, array(todo {@link parserTag},...)),...)
     * @tutorial tags.todo.pkg
     * @var array
     */
    var $todoList = array();

    /**
     * Directory where compiled templates go - will be deleted on exit
     *
     * @var string
     * @access private
     */
     var $_compiledDir = array();

    /**
     * Initialize Converter data structures
     * @param array {@link $all_packages} value
     * @param array {@link $package_parents} value
     * @param Classes {@link $classes} value
     * @param ProceduralPages {@link $proceduralpages} value
     * @param array {@link $package_output} value
     * @param boolean {@link $parseprivate} value
     * @param boolean {@link $quietmode} value
     * @param string {@link $targetDir} value
     * @param string {@link $templateDir} value
     * @param string (@link $title} value
     */
    function Converter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $template, $title)
    {
        $this->all_packages = $allp;
        $this->package_parents = $packp;
        $this->package = $GLOBALS['phpDocumentor_DefaultPackageName'];
        $this->proceduralpages = &$procpages;
        $this->package_output = $po;
        if (is_array($po))
        {
            $a = $po[0];
            $this->all_packages = array_flip($po);
            $this->all_packages[$a] = 1;
        }
        $this->parseprivate = $pp;
        $this->quietmode = $qm;
        $this->classes = &$classes;
        $this->roots = $classes->getRoots($this->processSpecialRoots);
        $this->title = $title;
        $this->setTemplateDir($template);
        $this->setTargetdir($targetDir);
    }

    /**
     * Called by IntermediateParser after creation
     * @access private
     */
    function setTutorials($tutorials)
    {
        $this->tutorials = $tutorials;
    }

    /**
     * @param pkg|cls|proc the tutorial type to search for
     * @param tutorial name
     * @param string package name
     * @param string subpackage name, if any
     * @return false|parserTutorial if the tutorial exists, return it
     */
    function hasTutorial($type, $name, $package, $subpackage = '')
    {
        if (isset($this->tutorials[$package][$subpackage][$type][$name . '.' . $type]))
            return $this->tutorials[$package][$subpackage][$type][$name . '.' . $type];
        return false;
    }

    /**
     * Called by {@link walk()} while converting, when the last class element
     * has been parsed.
     *
     * A Converter can use this method in any way it pleases. HTMLframesConverter
     * uses it to complete the template for the class and to output its
     * documentation
     * @see HTMLframesConverter::endClass()
     * @abstract
     */
    function endClass()
    {
    }

    /**
    * Called by {@link walk()} while converting, when the last procedural page
    * element has been parsed.
    *
    * A Converter can use this method in any way it pleases. HTMLframesConverter
    * uses it to complete the template for the procedural page and to output its
    * documentation
    * @see HTMLframesConverter::endClass()
    * @abstract
    */
    function endPage()
    {
    }

    /**
    * Called by {@link walk()} while converting.
    *
    * This method is intended to be the place that {@link $pkg_elements} is
    * formatted for output.
    * @see HTMLframesConverter::formatPkgIndex()
    * @abstract
    */
    function formatPkgIndex()
    {
    }

    /**
    * Called by {@link walk()} while converting.
    *
    * This method is intended to be the place that {@link $elements} is
    * formatted for output.
    * @see HTMLframesConverter::formatIndex()
    * @abstract
    */
    function formatIndex()
    {
    }

    /**
    * Called by {@link walk()} while converting.
    *
    * This method is intended to be the place that any of
    * {@link $class_elements, $function_elements, $page_elements},
    * {@link $define_elements}, and {@link $global_elements} is formatted for
    * output, depending on the value of {@link $leftindex}
    * @see HTMLframesConverter::formatLeftIndex()
    * @abstract
    */
    function formatLeftIndex()
    {
    }

    /**
     * Called by {@link parserSourceInlineTag::stringConvert()} to allow
     * converters to format the source code the way they'd like.
     *
     * default returns it unchanged (html with xhtml tags)
     * @param string output from highlight_string() - use this function to
     * reformat the returned data for Converter-specific output
     * @return string
     * @deprecated in favor of tokenizer-based highlighting.  This will be
     *             removed for 2.0
     */
    function unmangle($sourcecode)
    {
        return $sourcecode;
    }

    /**
     * Initialize highlight caching
     */
    function startHighlight()
    {
        $this->_highlightCache = array(false, false);
        $this->_appendHighlight = '';
    }

    function getHighlightState()
    {
        return $this->_highlightCache;
    }

    function _setHighlightCache($type, $token)
    {
        $test = ($this->_highlightCache[0] === $type && $this->_highlightCache[1] == $token);
        if (!$test) {
            $this->_appendHighlight = $this->flushHighlightCache();
        } else {
            $this->_appendHighlight = '';
        }
        $this->_highlightCache = array($type, $token);
        return $test;
    }

    /**
     * Return the close text for the current token
     * @return string
     */
    function flushHighlightCache()
    {
        $hc = $this->_highlightCache;
        $this->_highlightCache = array(false, false);
        if ($hc[0]) {
            if (!isset($this->template_options[$hc[0]]['/'.$hc[1]])) {
                return '';
            }
    		return $this->template_options[$hc[0]]['/'.$hc[1]];
        }
        return '';
    }

    /**
     * Used to allow converters to format the source code the way they'd like.
     *
     * default returns it unchanged.  Mainly used by the {@link HighlightParser}
     * {@internal
     * The method takes information from options.ini, the template options
     * file, specifically the [highlightSourceTokens] and [highlightSource]
     * sections, and uses them to enclose tokens.
     *
     * {@source}}}
     * @param integer token value from {@link PHP_MANUAL#tokenizer tokenizer constants}
     * @param string contents of token
     * @param boolean whether the contents are preformatted or need modification
     * @return string
     */
    function highlightSource($token, $word, $preformatted = false)
    {
        if ($token !== false)
        {
            if (!$preformatted) $word = $this->postProcess($word);
            if (isset($this->template_options['highlightSourceTokens'][token_name($token)]))
            {
                if ($this->_setHighlightCache('highlightSourceTokens', token_name($token))) {
                    return $word;
                }
                $e = $this->_appendHighlight;
                return $e . $this->template_options['highlightSourceTokens'][token_name($token)] . $word;
            } else
            {
                $this->_setHighlightCache(false, false);
                $e = $this->_appendHighlight;
                return $e . $word;
            }
        } else
        {
            if (isset($this->template_options['highlightSource'][$word]))
            {
                $newword = ($preformatted ? $word : $this->postProcess($word));
                if ($this->_setHighlightCache('highlightSource', $word)) {
                    return $newword;
                }
                $e = $this->_appendHighlight;
                return $e . $this->template_options['highlightSource'][$word] . $newword;
            } else
            {
                $this->_setHighlightCache(false, false);
                $e = $this->_appendHighlight;
                return $e . ($preformatted ? $word : $this->postProcess($word));
            }
        }
    }

    /**
     * Used to allow converters to format the source code of DocBlocks the way
     * they'd like.
     *
     * default returns it unchanged.  Mainly used by the {@link HighlightParser}
     * {@internal
     * The method takes information from options.ini, the template options
     * file, specifically the [highlightDocBlockSourceTokens] section, and uses
     * it to enclose tokens.
     *
     * {@source}}}
     * @param string name of docblock token type
     * @param string contents of token
     * @param boolean whether the contents are preformatted or need modification
     * @return string
     */
    function highlightDocBlockSource($token, $word, $preformatted = false)
    {
        if (empty($word)) {
            $this->_setHighlightCache(false, false);
            $e = $this->_appendHighlight;
            return $e . $word;
        }
        if (isset($this->template_options['highlightDocBlockSourceTokens'][$token]))
        {
            if (!$preformatted) $word = $this->postProcess($word);
            if ($this->_setHighlightCache('highlightDocBlockSourceTokens', $token)) {
                return $word;
            }
            $e = $this->_appendHighlight;
            return $e . $this->template_options['highlightDocBlockSourceTokens'][$token] . $word;
        } else {
            $this->_setHighlightCache(false, false);
            $e = $this->_appendHighlight;
            return $e . ($preformatted ? $word : $this->postProcess($word));
        }
    }

    /**
     * Used to allow converters to format the source code of Tutorial XML the way
     * they'd like.
     *
     * default returns it unchanged.  Mainly used by the {@link HighlightParser}
     * {@internal
     * The method takes information from options.ini, the template options
     * file, specifically the [highlightDocBlockSourceTokens] section, and uses
     * it to enclose tokens.
     *
     * {@source}}}
     * @param string name of docblock token type
     * @param string contents of token
     * @param boolean whether the contents are preformatted or need modification
     * @return string
     */
    function highlightTutorialSource($token, $word, $preformatted = false)
    {
        if (empty($word)) {
            $this->_setHighlightCache(false, false);
            $e = $this->_appendHighlight;
            return $e . $word;
        }
        if (isset($this->template_options['highlightTutorialSourceTokens'][$token]))
        {
            if (!$preformatted) $word = $this->postProcess($word);
            if ($this->_setHighlightCache('highlightTutorialSourceTokens', $token)) {
                return $word;
            }
            $e = $this->_appendHighlight;
            return $e . $this->template_options['highlightTutorialSourceTokens'][$token] . $word;
        } else {
            $this->_setHighlightCache(false, false);
            $e = $this->_appendHighlight;
            return $e . ($preformatted ? $word : $this->postProcess($word));
        }
    }

    /**
     * Called by {@link parserReturnTag::Convert()} to allow converters to
     * change type names to desired formatting
     *
     * Used by {@link XMLDocBookConverter::type_adjust()} to change true and
     * false to the peardoc2 values
     * @param string
     * @return string
     */
    function type_adjust($typename)
    {
        return $typename;
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
        return $this->ProgramExample($example, $tutorial, $inlinesourceparse, $class, $linenum, $filesourcepath);
    }

    /**
     * Used to convert the <<code>> tag in a docblock
     * @param string
     * @param boolean true if this is to highlight a tutorial <programlisting>
     * @return string
     */
    function ProgramExample($example, $tutorial = false, $inlinesourceparse = null/*false*/,
                            $class = null/*false*/, $linenum = null/*false*/, $filesourcepath = null/*false*/)
    {
        $this->highlightingSource = true;
        if (tokenizer_ext)
        {
            $e = $example;
            if (!is_array($example))
            {
                $obj = new phpDocumentorTWordParser;
                $obj->setup($example);
                $e = $obj->getFileSource();
                $bOpenTagFound = false;
                foreach ($e as $ke => $ee)
                {
                    foreach ($ee as $kee => $eee)
                    {
                        if ((int) $e[$ke][$kee][0] == T_OPEN_TAG)
                        {
                            $bOpenTagFound = true;
                        }
                    }
                }
                if (!$bOpenTagFound) {
                    $example = "<?php\n".$example;
                    $obj->setup($example);
                    $e = $obj->getFileSource();
                    unset($e[0]);
                    $e = array_values($e);
                }
                unset($obj);
            }
            $saveclass = $this->class;
            $parser = new phpDocumentor_HighlightParser;
            if (!isset($inlinesourceparse))
            {
                $example = $parser->parse($e, $this, true); // force php mode
            } else
            {
                if (isset($filesourcepath))
                {
                    $example = $parser->parse($e, $this, $inlinesourceparse, $class, $linenum, $filesourcepath);
                } elseif (isset($linenum))
                {
                    $example = $parser->parse($e, $this, $inlinesourceparse, $class, $linenum);
                } elseif (isset($class))
                {
                    $example = $parser->parse($e, $this, $inlinesourceparse, $class);
                } else
                {
                    $example = $parser->parse($e, $this, $inlinesourceparse);
                }
            }
            $this->class = $saveclass;
        } else
        {
            $example = $this->postProcess($example);
        }
        $this->highlightingSource = false;

        if ($tutorial)
        {
            return $example;
        }

        if (!isset($this->template_options['desctranslate'])) return $example;
        if (!isset($this->template_options['desctranslate']['code'])) return $example;
        $example = $this->template_options['desctranslate']['code'] . $example;
        if (!isset($this->template_options['desctranslate']['/code'])) return $example;
        return $example . $this->template_options['desctranslate']['/code'];
    }

    /**
     * @param string
     */
    function TutorialExample($example)
    {
        $this->highlightingSource = true;
        $parse = new phpDocumentor_TutorialHighlightParser;
        $x = $parse->parse($example, $this);
        $this->highlightingSource = false;
        return $x;
    }

    /**
     * Used to convert the contents of <<li>> in a docblock
     * @param string
     * @return string
     */
    function ListItem($item)
    {
        if (!isset($this->template_options['desctranslate'])) return $item;
        if (!isset($this->template_options['desctranslate']['li'])) return $item;
        $item = $this->template_options['desctranslate']['li'] . $item;
        if (!isset($this->template_options['desctranslate']['/li'])) return $item;
        return $item . $this->template_options['desctranslate']['/li'];
    }

    /**
     * Used to convert the contents of <<ol>> or <<ul>> in a docblock
     * @param string
     * @return string
     */
    function EncloseList($list,$ordered)
    {
        $listname = ($ordered ? 'ol' : 'ul');
        if (!isset($this->template_options['desctranslate'])) return $list;
        if (!isset($this->template_options['desctranslate'][$listname])) return $list;
        $list = $this->template_options['desctranslate'][$listname] . $list;
        if (!isset($this->template_options['desctranslate']['/'.$listname])) return $list;
        return $list . $this->template_options['desctranslate']['/'.$listname];
    }

    /**
     * Used to convert the contents of <<pre>> in a docblock
     * @param string
     * @return string
     */
    function PreserveWhiteSpace($string)
    {
        if (!isset($this->template_options['desctranslate'])) return $string;
        if (!isset($this->template_options['desctranslate']['pre'])) return $string;
        $string = $this->template_options['desctranslate']['pre'] . $string;
        if (!isset($this->template_options['desctranslate']['/pre'])) return $string;
        return $string . $this->template_options['desctranslate']['/pre'];
    }

    /**
     * Used to enclose a paragraph in a docblock
     * @param string
     * @return string
     */
    function EncloseParagraph($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['p'])) return $para;
        $para = $this->template_options['desctranslate']['p'] . $para;
        if (!isset($this->template_options['desctranslate']['/p'])) return $para;
        return $para . $this->template_options['desctranslate']['/p'];
    }

    /**
     * Used to convert the contents of <<b>> in a docblock
     * @param string
     * @return string
     */
    function Bolden($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['b'])) return $para;
        $para = $this->template_options['desctranslate']['b'] . $para;
        if (!isset($this->template_options['desctranslate']['/b'])) return $para;
        return $para . $this->template_options['desctranslate']['/b'];
    }

    /**
     * Used to convert the contents of <<i>> in a docblock
     * @param string
     * @return string
     */
    function Italicize($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['i'])) return $para;
        $para = $this->template_options['desctranslate']['i'] . $para;
        if (!isset($this->template_options['desctranslate']['/i'])) return $para;
        return $para . $this->template_options['desctranslate']['/i'];
    }

    /**
     * Used to convert the contents of <<var>> in a docblock
     * @param string
     * @return string
     */
    function Varize($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['var'])) return $para;
        $para = $this->template_options['desctranslate']['var'] . $para;
        if (!isset($this->template_options['desctranslate']['/var'])) return $para;
        return $para . $this->template_options['desctranslate']['/var'];
    }

    /**
     * Used to convert the contents of <<kbd>> in a docblock
     * @param string
     * @return string
     */
    function Kbdize($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['kbd'])) return $para;
        $para = $this->template_options['desctranslate']['kbd'] . $para;
        if (!isset($this->template_options['desctranslate']['/kbd'])) return $para;
        return $para . $this->template_options['desctranslate']['/kbd'];
    }

    /**
     * Used to convert the contents of <<samp>> in a docblock
     * @param string
     * @return string
     */
    function Sampize($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['samp'])) return $para;
        $para = $this->template_options['desctranslate']['samp'] . $para;
        if (!isset($this->template_options['desctranslate']['/samp'])) return $para;
        return $para . $this->template_options['desctranslate']['/samp'];
    }

    /**
     * Used to convert <<br>> in a docblock
     * @param string
     * @return string
     */
    function Br($para)
    {
        if (!isset($this->template_options['desctranslate'])) return $para;
        if (!isset($this->template_options['desctranslate']['br'])) return $para;
        $para = $this->template_options['desctranslate']['br'] . $para;
        return $para;
    }

    /**
     * This version does nothing
     *
     * Perform necessary post-processing of string data.  For example, the HTML
     * Converters should escape < and > to become &lt; and &gt;
     * @return string
     */
    function postProcess($text)
    {
        return $text;
    }

    /**
     * Creates a table of contents for a {@}toc} inline tag in a tutorial
     *
     * This function should return a formatted table of contents.  By default, it
     * does nothing, it is up to the converter to format the TOC
     * @abstract
     * @return string table of contents formatted for use in the current output format
     * @param array format: array(array('tagname' => section, 'link' => returnsee link, 'id' => anchor name, 'title' => from title tag),...)
     */
    function formatTutorialTOC($toc)
    {
        return '';
    }

    /**
     * Write out the formatted source code for a php file
     *
     * This function provides the primary functionality for the
     * {@tutorial tags.filesource.pkg} tag.
     * @param string full path to the file
     * @param string fully highlighted/linked source code of the file
     * @abstract
     */
    function writeSource($filepath, $source)
    {
        debug($source);
        return;
    }

    /**
     * Write out the formatted source code for an example php file
     *
     * This function provides the primary functionality for the
     * {@tutorial tags.example.pkg} tag.
     * @param string example title
     * @param string example filename (no path)
     * @param string fully highlighted/linked source code of the file
     * @abstract
     */
    function writeExample($title, $path, $source)
    {
        return;
    }

    /** Translate the path info into a unique file name for the highlighted
     * source code.
     * @param string $pathinfo
     * @return string
     */
    function getFileSourceName($path)
    {
        global $_phpDocumentor_options;
        $pathinfo = $this->proceduralpages->getPathInfo($path, $this);
        $pathinfo['source_loc'] = str_replace($_phpDocumentor_options['Program_Root'].'/','',$pathinfo['source_loc']);
        $pathinfo['source_loc'] = str_replace('/','_',$pathinfo['source_loc']);
        return "fsource_{$pathinfo['package']}_{$pathinfo['subpackage']}_{$pathinfo['source_loc']}";
    }

    /** Return the fixed path to the source-code file folder.
     * @param string $base Path is relative to this folder
     * @return string
     */
    function getFileSourcePath($base)
    {
        if (substr($base, strlen($base) - 1) != PATH_DELIMITER) {
            $base .= PATH_DELIMITER;
        }
        return $base . '__filesource';
    }

    /** Return the path to the current
     * @param string $pathinfo
     * @return string
     */
    function getCurrentPageURL()
    {
        return '{$srcdir}' . PATH_DELIMITER . $this->page_dir;
    }

    /**
     * @return string an output-format dependent link to phpxref-style highlighted
     * source code
     * @abstract
     */
    function getSourceLink($path)
    {
        return '';
    }

    /**
     * @return string Link to the current page being parsed.
     * Should return {@link $curname} and a converter-specific extension.
     * @abstract
     */
    function getCurrentPageLink()
    {
    }

    /**
     * Return a line of highlighted source code with formatted line number
     *
     * If the $path is a full path, then an anchor to the line number will be
     * added as well
     * @param integer line number
     * @param string highlighted source code line
     * @param false|string full path to @filesource file this line is a part of,
     *        if this is a single line from a complete file.
     * @return string formatted source code line with line number
     */
    function sourceLine($linenumber, $line, $path = false)
    {
        if ($path)
        {
            return $this->getSourceAnchor($path, $linenumber) .
                   $this->Br(sprintf('%-6u',$linenumber).str_replace("\n",'',$line));
        } else
        {
            return $this->Br(sprintf('%-6u',$linenumber).str_replace("\n",'',$line));
        }
    }

    /**
     * Determine whether an element's file has generated source code, used for
     * linking to line numbers of source.
     *
     * Wrapper for {@link $sourcePaths} in this version
     * @param string full path to the source code file
     * @return boolean
     */
    function hasSourceCode($path)
    {
        return isset($this->sourcePaths[$path]);
    }

    /**
     * Mark a file as having had source code highlighted
     * @param string full path of source file
     */
    function setSourcePaths($path)
    {
        $this->sourcePaths[$path] = true;
    }

    /**
     * Used to translate an XML DocBook entity like &rdquo; from a tutorial by
     * reading the options.ini file for the template.
     * @param string entity name
     */
    function TranslateEntity($name)
    {
        if (!isset($this->template_options['ppage']))
        {
            if (!$this->template_options['preservedocbooktags'])
            return '';
            else
            return '&'.$name.';';
        }
        if (isset($this->template_options['ppage']['&'.$name.';']))
        {
            return $this->template_options['ppage']['&'.$name.';'];
        } else
        {
            if (!$this->template_options['preservedocbooktags'])
            return '';
            else
            return '&'.$name.';';
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
        if (!isset($this->template_options['ppage']))
        {
            if (!$this->template_options['preservedocbooktags'])
            return $cdata;
            else
            return '<'.$name.$this->AttrToString($name,$attr,true).'>'.$cdata.'</'.$name.'>'."\n";
        }
        // make sure this template transforms the tag into something
        if (isset($this->template_options['ppage'][$name]))
        {
            // test for global attribute transforms like $attr$role = class, changing
            // all role="*" attributes to class="*" in html, for example
            foreach($attr as $att => $val)
            {
                if (isset($this->template_options['$attr$'.$att]))
                {
                    $new = '';
                    if (!isset($this->template_options['$attr$'.$att]['close']))
                    {
                        $new .= '<'.$this->template_options['$attr$'.$att]['open'];
                        if (isset($this->template_options['$attr$'.$att]['cdata!']))
                        {
                            if (isset($this->template_options['$attr$'.$att]['separateall']))
                            $new .= $this->template_options['$attr$'.$att]['separator'];
                            else
                            $new .= ' ';
                            $new .= $this->template_options['$attr$'.$att]['$'.$att];
                            $new .= $this->template_options['$attr$'.$att]['separator'];
                            if ($this->template_options['$attr$'.$att]['quotevalues']) $val = '"'.$val.'"';
                            $new .= $val.'>';
                        } else
                        {
                            $new .= '>'.$val;
                        }
                        $new .= '</'.$this->template_options['$attr$'.$att]['open'].'>';
                    } else
                    {
                        $new .= $this->template_options['$attr$'.$att]['open'] . $val . $this->template_options['$attr$'.$att]['close'];
                    }
                    unset($attr[$att]);
                    $cdata = $new . $cdata;
                }
            }

            if (!isset($this->template_options['ppage']['/'.$name]))
            {// if the close tag isn't specified, we put opening and closing tags around it, with translated attributes
                if (isset($this->template_options['ppage'][$name.'/']))
                $cdata = '<'.$this->template_options['ppage'][$name].$this->AttrToString($name,$attr).'/>' . $cdata;
                else
                $cdata = '<'.$this->template_options['ppage'][$name].$this->AttrToString($name,$attr).'>' . $cdata .
                         '</'.$this->template_options['ppage'][$name].'>';
            } else
            { // if close tag is specified, use the open and close as literal
                if ($name == 'programlisting' && isset($attr['role']) &&
                      ($attr['role'] == 'php' || $attr['role'] == 'tutorial' || $attr['role'] == 'html'))
                { // highlight PHP source
//                    var_dump($unconvertedcdata, $cdata);exit;
                    if ($attr['role'] == 'php') {
                        $cdata = $this->ProgramExample($unconvertedcdata, true);
                    } elseif ($attr['role'] == 'tutorial') {
                        $cdata = $this->TutorialExample($unconvertedcdata);
                    } elseif ($attr['role'] == 'html') {
                        $cdata = $unconvertedcdata;
                    }
                } else
                {// normal case below
                    $cdata = $this->template_options['ppage'][$name].$this->AttrToString($name,$attr). $cdata .$this->template_options['ppage']['/'.$name];
                }
            }
            return $cdata;
        } else
        {
            if ($this->template_options['preservedocbooktags'])
            {
                return '<'.$name.$this->AttrToString($name,$attr,true).'>' . $cdata .
                         '</'.$name.'>'."\n";
            } else
            {
                return $cdata;
            }
        }
    }

    /**
     * Convert the attribute of a Tutorial docbook tag's attribute list
     * to a string based on the template options.ini
     * @param string tag name
     * @param attribute array
     * @param boolean if true, returns attrname="value"...
     * @return string
     */
    function AttrToString($tag,$attr,$unmodified = false)
    {
        $ret = '';
        if ($unmodified)
        {
            $ret = ' ';
            foreach($attr as $n => $v)
            {
                $ret .= $n.' = "'.$v.'"';
            }
            return $ret;
        }
        // no_attr tells us to ignore all attributes
        if (isset($this->template_options['no_attr'])) return $ret;
        // tagname! tells us to ignore all attributes for this tag
        if (isset($this->template_options['ppage'][$tag.'!'])) return $ret;
        if (count($attr)) $ret = ' ';
        // pass 1, check to see if any attributes add together
        $same = array();
        foreach($attr as $n => $v)
        {
            if (isset($this->template_options['ppage'][$tag.'->'.$n]))
            {
                $same[$this->template_options['ppage'][$tag.'->'.$n]][] = $n;
            }
        }
        foreach($attr as $n => $v)
        {
            if (isset($this->template_options['ppage'][$tag.'->'.$n]))
            {
                if (count($same[$this->template_options['ppage'][$tag.'->'.$n]]) == 1)
                { // only 1 attribute translated for this one
                    // this is useful for equivalent value names
                    if (isset($this->template_options['ppage'][$tag.'->'.$n.'+'.$v])) $v = $this->template_options['ppage'][$tag.'->'.$n.'+'.$v];
                } else
                { // more than 1 attribute combines to make the new attribute
                    $teststrtemp = array();
                    foreach($same[$this->template_options['ppage'][$tag.'->'.$n]] as $oldattr)
                    {
                        $teststrtemp[] = $oldattr.'+'.$attr[$oldattr];
                    }
                    $teststrs = array();
                    $num = count($same[$this->template_options['ppage'][$tag.'->'.$n]]);
                    for($i=0;$i<$num;$i++)
                    {
                        $started = false;
                        $a = '';
                        for($j=$i;!$started || $j != $i;$j = ($j + $i) % $num)
                        {
                            if (!empty($a)) $a .= '|';
                            $a .= $teststrtemp[$j];
                        }
                        $teststrs[$i] = $a;
                    }
                    $done = false;
                    foreach($teststrs as $test)
                    {
                        if ($done) break;
                        if (isset($this->template_options['ppage'][$tag.'->'.$test]))
                        {
                            $done = true;
                            $v = $this->template_options['ppage'][$tag.'->'.$test];
                        }
                    }
                }
                $ret .= $this->template_options['ppage'][$tag.'->'.$n].' = "'.$v.'"';
            } else
            {
                if (!isset($this->template_options['ppage'][$tag.'!'.$n]))
                {
                    if (isset($this->template_options['ppage']['$attr$'.$n]))
                    $ret .= $this->template_options['ppage']['$attr$'.$n].' = "'.$v.'"';
                    else
                    $ret .= $n.' = "'.$v.'"';
                }
            }
        }
        return $ret;
    }

    /**
     * Convert the title of a Tutorial docbook tag section
     * to a string based on the template options.ini
     * @param string tag name
     * @param array
     * @param string title text
     * @param string
     * @return string
     */
    function ConvertTitle($tag,$attr,$title,$cdata)
    {
        if (!isset($this->template_options[$tag.'_title'])) return array($attr,$cdata);
        if (isset($this->template_options[$tag.'_title']['tag_attr']))
        {
            $attr[$this->template_options[$tag.'_title']['tag_attr']] = urlencode($cdata);
            $cdata = '';
        } elseif(isset($this->template_options[$tag.'_title']['cdata_start']))
        {
            $cdata = $this->template_options[$tag.'_title']['open'] . $title .
                     $this->template_options[$tag.'_title']['close'] . $cdata;
        } else $cdata = $title.$cdata;
        return array($attr,$cdata);
    }

    /**
     * Return a converter-specific id to distinguish tutorials and their
     * sections
     *
     * Used by {@}id}
     * @return string
     */
    function getTutorialId($package,$subpackage,$tutorial,$id)
    {
        return $package.$subpackage.$tutorial.$id;
    }

    /**
     * Create the {@link $elements, $pkg_elements} and {@link $links} arrays
     * @access private
     * @todo version 2.0 - faulty package_output logic should be removed
     *
     *       in this version, if the parent file isn't in the package, all
     *       the procedural elements are simply shunted to another package!
     */
    function _createPkgElements(&$pages)
    {
        if (empty($this->elements))
        {
            $this->elements = array();
            $this->pkg_elements = array();
            $this->links = array();
            phpDocumentor_out('Building indexes...');
            flush();
            foreach($pages as $j => $flub)
            {
                $this->package = $pages[$j]->parent->package;
                $this->subpackage = $pages[$j]->parent->subpackage;
                $this->class = false;
                $this->curfile = $pages[$j]->parent->getFile();
                $this->curname = $this->getPageName($pages[$j]->parent);
                $this->curpath = $pages[$j]->parent->getPath();
                $use = true;
                if ($this->package_output)
                {
                    if (in_array($this->package,$this->package_output))
                    {
                        $this->addElement($pages[$j]->parent,$pages[$j]);
                    } else
                    {
                        if (count($pages[$j]->classelements))
                        {
                            list(,$pages[$j]->parent->package) = each($this->package_output);
                            reset($this->package_output);
                            $pages[$j]->parent->subpackage = '';
                            $this->addElement($pages[$j]->parent,$pages[$j]);
                        } else
                        {
                            unset($pages[$j]);
                            continue;
                        }
                    }
                } else
                {
                    $this->addElement($pages[$j]->parent,$pages[$j]);
                }
                if ($use)
                for($i=0; $i<count($pages[$j]->elements); $i++)
                {
                    $pages[$j]->elements[$i]->docblock->package = $this->package;
                    $pages[$j]->elements[$i]->docblock->subpackage = $this->subpackage;
                    $this->proceduralpages->replaceElement($pages[$j]->elements[$i]);
                    $this->addElement($pages[$j]->elements[$i]);
                }
                for($i=0; $i<count($pages[$j]->classelements); $i++)
                {
                    if ($this->class)
                    {
                        if ($pages[$j]->classelements[$i]->type == 'class')
                        {
                            if ($this->checkKillClass($pages[$j]->classelements[$i]->getName(),$pages[$j]->classelements[$i]->getPath())) continue;
                            $this->package = $pages[$j]->classelements[$i]->docblock->package;
                            if ($this->package_output) if (!in_array($this->package,$this->package_output)) continue;
                            $this->subpackage = $pages[$j]->classelements[$i]->docblock->subpackage;
                            $this->class = $pages[$j]->classelements[$i]->name;
                        } else
                        {
                            if ($this->killclass) continue;
                            // force all contained elements to have parent package/subpackage
                            $pages[$j]->classelements[$i]->docblock->package = $this->package;
                            $pages[$j]->classelements[$i]->docblock->subpackage = $this->subpackage;
                        }
                    }
                    if ($pages[$j]->classelements[$i]->type == 'class')
                    {
                        if ($this->checkKillClass($pages[$j]->classelements[$i]->getName(),$pages[$j]->classelements[$i]->getPath())) continue;
                        $this->package = $pages[$j]->classelements[$i]->docblock->package;
                        if ($this->package_output) if (!in_array($this->package,$this->package_output)) continue;
                        $this->subpackage = $pages[$j]->classelements[$i]->docblock->subpackage;
                        $this->class = $pages[$j]->classelements[$i]->name;
                    }
                    if (!$this->killclass) $this->addElement($pages[$j]->classelements[$i]);
                }
            }
            phpDocumentor_out("done\n");
            flush();
        }
        $this->sortIndexes();
        $this->sortTodos();
        if ($this->sort_page_contents_by_type) $this->sortPageContentsByElementType($pages);
    }

    /**
     * Process the {@link $tutorials} array
     *
     * Using the tutorialname.ext.ini files, this method sets up tutorial
     * hierarchy.  There is some minimal error checking to make sure that no
     * tutorial links to itself, even two levels deep as in tute->next->tute.
     *
     * If all tests pass, it creates the hierarchy
     * @uses generateTutorialOrder()
     * @uses _setupTutorialTree()
     * @access private
     */
    function _processTutorials()
    {
        $parents = $all = array();
        foreach($this->tutorials as $package => $els)
        {
            if ($this->package_output)
            {
                if (!in_array($package,$this->package_output))
                {
                    unset($this->tutorials[$package]);
                    continue;
                }
            }
            if (!isset($this->pkg_elements[$package]))
            {
                unset($this->tutorials[$package]);
                continue;
            }
            foreach($els as $subpackage => $els2)
            {
                foreach($els2 as $type => $tutorials)
                {
                    foreach($tutorials as $tutorial)
                    {
                        if ($tutorial->ini)
                        {
                            if (isset($tutorial->ini['Linked Tutorials']))
                            {
                                foreach($tutorial->ini['Linked Tutorials'] as $child)
                                {
                                    $sub = (empty($tutorial->subpackage) ? '' : $tutorial->subpackage . '/');
                                    $kid = $tutorial->package . '/' . $sub . $child . '.' . $tutorial->tutorial_type;
                                    // parent includes self as a linked tutorial?
                                    $kidlink = $this->getTutorialLink($kid,false,false,array($tutorial->package));
                                    if (is_object($kidlink) && $this->returnSee($kidlink) == $tutorial->getLink($this))
                                    { // bad!
                                        addErrorDie(PDERROR_TUTORIAL_IS_OWN_CHILD,$tutorial->name,$tutorial->name.'.ini');
                                    }
                                }
                                $parents[] = $tutorial;
                            }
                        }
                        $all[$package][$subpackage][$type][] = $tutorial;
                    }
                }
            }
        }
        // loop error-checking, use this to eliminate possibility of accidentally linking to a parent as a child
        $testlinks = array();
        foreach($parents as $parent)
        {
            $testlinks[$parent->name]['links'][] = $parent->getLink($this);
            $testlinks[$parent->name]['name'][$parent->getLink($this)] = $parent->name;
        }
        // generate the order of tutorials, and link them together
        foreach($parents as $parent)
        {
            foreach($parent->ini['Linked Tutorials'] as $child)
            {
                $sub = (empty($parent->subpackage) ? '' : $parent->subpackage . '/');
                $kid = $parent->package . '/' . $sub . $child . '.' . $parent->tutorial_type;
                // child tutorials must be in the same package AND subpackage
                // AND have the same extension as the parent, makes things clearer for both ends
                if (in_array($this->returnSee($this->getTutorialLink($kid,false,false,array($parent->package))),$testlinks[$parent->name]['links']))
                    addErrorDie(PDERROR_TUTORIAL_IS_OWN_GRANDPA,$testlinks[$parent->name][$this->returnSee($this->getTutorialLink($kid,false,false,array($parent->package)))],$kid->name,$testlinks[$parent->name][$this->returnSee($this->getTutorialLink($kid,false,false,array($parent->package)))],$kid->name.'.ini');
                if ($this->returnSee($this->getTutorialLink($kid,false,false,array($parent->package))) == $kid)
                {
                    addWarning(PDERROR_CHILD_TUTORIAL_NOT_FOUND, $child . '.' . $parent->tutorial_type, $parent->name .'.ini',$parent->package, $parent->subpackage);
                }
            }
        }
        $new = $tree = $roots = array();
        // build a list of all 'root' tutorials (tutorials without parents).
        foreach($parents as $i => $parent)
        {
            if (! $parent->isChildOf($parents)) {
                $roots[] = $parent;
            }
        }
        $parents = $roots;
        // add the parents and all child tutorials in order to the list of tutorials to process
        foreach($parents as $parent)
        {
            $this->generateTutorialOrder($parent,$all,$new);
        }
        if (count($all))
        {
            // add the leftover tutorials
            foreach($all as $package => $els)
            {
                foreach($els as $subpackage => $els2)
                {
                    foreach($els2 as $type => $tutorials)
                    {
                        foreach($tutorials as $tutorial)
                        {
                            $new[$package][$subpackage][$type][] = $tutorial;
                        }
                    }
                }
            }
        }
        // remove the old, unprocessed tutorials, and set it up with the next code
        $this->tutorials = array();
        // reset integrity of the tutorial list
        $prev = false;
        uksort($new, 'tutorialcmp');
//        debug($this->vardump_tree($new));exit;
        foreach($new as $package => $els)
        {
            foreach($els as $subpackage => $els2)
            {
                foreach($els2 as $type => $tutorials)
                {
                    foreach($tutorials as $tutorial)
                    {
                        if ($prev)
                        {
                            $this->tutorials[$prevpackage][$prevsubpackage][$prevtype][$prevname]->setNext($tutorial,$this);
                            $tutorial->setPrev($prev,$this);
                        }
                        $this->tutorials[$package][$subpackage][$type][$tutorial->name] = $tutorial;
                        $prev = $tutorial->getLink($this,true);
                        $prevpackage = $package;
                        $prevsubpackage = $subpackage;
                        $prevtype = $type;
                        $prevname = $tutorial->name;
                    }
                }
            }
        }
        $this->tutorial_tree = $this->_setupTutorialTree();
        return $new;
    }

    /**
    * called by {@link phpDocumentor_IntermediateParser::Convert()} to traverse
    * the array of pages and their elements, converting them to the output format
    *
    * The walk() method should be flexible enough such that it never needs
    * modification.  walk() sets up all of the indexes, and sorts everything in
    * logical alphabetical order.  It then passes each element individually to
    * {@link Convert()}, which then passes to the Convert*() methods.  A child
    * Converter need not override any of these unless special functionality must
    * be added. see {@tutorial Converters/template.vars.cls} for details.
    * {@internal
    * walk() first creates all of the indexes {@link $elements, $pkg_elements}
    * and the left indexes specified by {@link $leftindexes},
    * and then sorts them by calling {@link sortIndexes()}.
    *
    * Next, it converts all README/CHANGELOG/INSTALL-style files, using
    * {@link Convert_RIC}.
    *
    * After this, it
    * passes all package-level docs to Convert().  Then, it calls the index
    * sorting functions {@link formatPkgIndex(), formatIndex()} and
    * {@link formatLeftIndex()}.
    *
    * Finally, it converts each procedural page in alphabetical order.  This
    * stage passes elements from the physical file to Convert() in alphabetical
    * order.  First, procedural page elements {@link parserDefine, parserInclude}
    * {@link parserGlobal}, and {@link parserFunction} are passed to Convert().
    *
    * Then, class elements are passed in this order: {@link parserClass}, then
    * all of the {@link parserVar}s in the class and all of the
    * {@link parserMethod}s in the class.  Classes are in alphabetical order,
    * and both vars and methods are in alphabetical order.
    *
    * Finally, {@link ConvertErrorLog()} is called and the data walk is complete.}}
    * @param array Format: array(fullpath => {@link parserData} structure with full {@link parserData::$elements}
    *                                         and {@link parserData::$class_elements}.
    * @param array Format: array({@link parserPackagePage} 1, {@link parserPackagePage} 2,...)
    * @uses Converter::_createPkgElements() sets up {@link $elements} and
    *       {@link $pkg_elements} array, as well as {@link $links}
    */
    function walk(&$pages,&$package_pages)
    {
        if (empty($pages))
        {
            die("<b>ERROR</b>: nothing parsed");
        }
        $this->_createPkgElements($pages);
        if (count($this->ric))
        {
            phpDocumentor_out("Converting README/INSTALL/CHANGELOG contents...\n");
            flush();
            foreach($this->ric as $name => $contents)
            {
                phpDocumentor_out("$name...");
                flush();
                $this->Convert_RIC($name,$contents);
            }
            phpDocumentor_out("\ndone\n");
            flush();
        }
        foreach($package_pages as $i => $perp)
        {
            if ($this->package_output)
            {
                if (!in_array($package_pages[$i]->package,$this->package_output)) continue;
            }
            phpDocumentor_out('Converting package page for package '.$package_pages[$i]->package.'... ');
            flush();
            $this->package = $package_pages[$i]->package;
            $this->subpackage = '';
            $this->class = false;
            $this->Convert($package_pages[$i]);
            phpDocumentor_out("done\n");
            flush();
        }
        phpDocumentor_out("Converting tutorials/extended docs\n");
        flush();
        // get tutorials into the order they will display, and set next/prev links
        $new = $this->_processTutorials();
        foreach($this->tutorials as $package => $els)
        {
            foreach($els as $subpackage => $els2)
            {
                foreach($els2 as $type => $tutorials)
                {
                    foreach($tutorials as $tutorial)
                    {
                        switch ($type)
                        {
                            case 'pkg' :
                                $a = '';
                                if ($tutorial->ini)
                                $a .= 'Top-level ';
                                if (!empty($tutorial->subpackage))
                                $a .= 'Sub-';
                                $ptext = "Converting ${a}Package-level tutorial ".$tutorial->name.'...';
                            break;
                            case 'cls' :
                                $a = '';
                                if ($tutorial->ini)
                                $a .= 'Top-level ';
                                $ptext = "Converting ${a}Class-level tutorial " . $tutorial->name ." and associating...";
                                $link = Converter::getClassLink(str_replace('.cls','',$tutorial->name), $tutorial->package);
                                if (is_object($link))
                                {
                                    if ($this->sort_absolutely_everything)
                                    {
                                        $addend = 'unsuccessful ';
                                        if (isset($this->package_elements[$tutorial->package][$tutorial->subpackage]['class'][$link->name]))
                                        {
                                            $this->package_elements[$tutorial->package][$tutorial->subpackage]['class'][$link->name][0]->addTutorial($tutorial,$this);
                                            $addend = 'success ';
                                        }
                                    } else
                                    {
                                        $addend = 'unsuccessful ';
                                        if (!isset($this->classes->killclass[str_replace('.cls','',$tutorial->name)]) && !isset($this->classes->killclass[str_replace('.cls','',$tutorial->name)][$tutorial->path]))
                                        {
                                            foreach($pages as $j => $inf)
                                            {
                                                foreach($inf->classelements as $i => $class)
                                                {
                                                    if ($class->type == 'class' && $class->name == str_replace('.cls','',$tutorial->name) && $class->path == $link->path)
                                                    {
                                                        $pages[$j]->classelements[$i]->addTutorial($tutorial,$this);
                                                        $addend = 'success ';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ptext .= $addend;
                                } else $ptext .= "unsuccessful ";
                            break;
                            case 'proc' :
                                $a = '';
                                if ($tutorial->ini)
                                $a .= 'Top-level ';
                                $ptext = "Converting ${a}Procedural-level tutorial ".$tutorial->name." and associating...";
                                $link = Converter::getPageLink(str_replace('.proc','',$tutorial->name), $tutorial->package);
                                if (is_object($link))
                                {
                                    $addend = 'unsuccessful ';
                                    if ($this->sort_absolutely_everything)
                                    {
                                        if (isset($this->package_elements[$tutorial->package][$tutorial->subpackage]['page'][$link->path]))
                                        {
                                            $this->package_elements[$tutorial->package][$tutorial->subpackage]['page'][$link->path][0]->addTutorial($tutorial,$this);
                                            $addend = "success ";
                                        }
                                    } else
                                    {
                                        foreach($pages as $j => $info)
                                        {
                                            if ($j == $link->path)
                                            {
                                                $pages[$j]->addTutorial($tutorial,$this);
                                                $addend = "success ";
                                            }
                                        }
                                    }
                                    $ptext .= $addend;
                                } else $ptext .= "unsuccessful ";
                            break;
                        }
                        phpDocumentor_out($ptext);
                        flush();
                        $this->package = $tutorial->package;
                        $this->subpackage = $tutorial->subpackage;
                        $this->Convert($tutorial);
                        phpDocumentor_out("done\n");
                        flush();
                    }
                }
            }
        }
        phpDocumentor_out("Formatting Package Indexes...");
        flush();
        $this->formatPkgIndex();
        phpDocumentor_out("done\n");
        flush();
        phpDocumentor_out("Formatting Index...");
        flush();
        $this->formatIndex();
        phpDocumentor_out("done\n\n");
        flush();
        phpDocumentor_out("Formatting Left Quick Index...");
        flush();
        $this->formatLeftIndex();
        phpDocumentor_out("done\n\n");
        flush();
        if ($this->sort_absolutely_everything) return $this->walk_everything();
        foreach($pages as $j => $flub)
        {
            phpDocumentor_out('Converting '.$pages[$j]->parent->getPath());
            flush();
            $this->package = $pages[$j]->parent->package;
            $this->subpackage = $pages[$j]->parent->subpackage;
            $this->class = false;
            $this->curfile = $pages[$j]->parent->getFile();
            $this->curname = $this->getPageName($pages[$j]->parent);
            $this->curpath = $pages[$j]->parent->getPath();
            $use = true;
            if ($this->package_output)
            {
                if (in_array($this->package,$this->package_output))
                {
                    $this->Convert($pages[$j]);
                } else
                {
                    $use = false;
                }
            } else
            {
                $this->Convert($pages[$j]);
            }
            phpDocumentor_out(" Procedural Page Elements...");
            flush();
            if ($use)
            for($i=0; $i<count($pages[$j]->elements); $i++)
            {
                $a = $pages[$j]->elements[$i]->docblock->getKeyword('access');
                if (is_object($a)) $a = $a->getString();
                if (!$this->parseprivate && ($a == 'private'))
                    continue;
//                phpDocumentor_out("    ".$pages[$j]->elements[$i]->name."\n");
                $pages[$j]->elements[$i]->docblock->package = $this->package;
                $pages[$j]->elements[$i]->docblock->subpackage = $this->subpackage;
                $this->Convert($pages[$j]->elements[$i]);
            }
            phpDocumentor_out(" Classes...");
            $this->class = false;
            flush();
            for($i=0; $i<count($pages[$j]->classelements); $i++)
            {
                if ($this->class)
                {
                    if ($pages[$j]->classelements[$i]->type == 'class')
                    {
                        if (!$this->killclass) $this->endClass();
                        $this->killclass = false;
                        if ($this->checkKillClass($pages[$j]->classelements[$i]->getName(),$pages[$j]->classelements[$i]->getPath())) continue;
                        $this->package = $pages[$j]->classelements[$i]->docblock->package;
                        if ($this->package_output) if (!in_array($this->package,$this->package_output)) continue;
                        $this->subpackage = $pages[$j]->classelements[$i]->docblock->subpackage;
                        $this->class = $pages[$j]->classelements[$i]->name;
                    } else
                    {
                        $a = $pages[$j]->classelements[$i]->docblock->getKeyword('access');
                        if (is_object($a)) $a = $a->getString();
                        if (!$this->parseprivate && ($a == 'private'))
                            continue;
                        if ($this->killclass) continue;
                        // force all contained elements to have parent package/subpackage
                        $pages[$j]->classelements[$i]->docblock->package = $this->package;
                        $pages[$j]->classelements[$i]->docblock->subpackage = $this->subpackage;
                    }
                }
                if ($pages[$j]->classelements[$i]->type == 'class')
                {
                    $this->killclass = false;
                    if ($this->checkKillClass($pages[$j]->classelements[$i]->getName(),$pages[$j]->classelements[$i]->getPath())) continue;
                    $this->package = $pages[$j]->classelements[$i]->docblock->package;
                    if ($this->package_output) if (!in_array($this->package,$this->package_output)) continue;
                    $this->subpackage = $pages[$j]->classelements[$i]->docblock->subpackage;
                    $this->class = $pages[$j]->classelements[$i]->name;
                }
                if ($this->killclass) continue;
//                phpDocumentor_out("    ".$pages[$j]->classelements[$i]->name."\n");
                $this->Convert($pages[$j]->classelements[$i]);
            }
            if (count($pages[$j]->classelements) && !$this->killclass) $this->endClass();
            phpDocumentor_out(" done\n");
            flush();
            $this->endPage();
        }
        phpDocumentor_out("\nConverting @todo List...");
        flush();
        if (count($this->todoList))
        {
            $this->ConvertTodoList();
        }
        phpDocumentor_out("done\n");
        flush();
        phpDocumentor_out("\nConverting Error Log...");
        flush();
        $this->ConvertErrorLog();
        phpDocumentor_out("done\n");
        flush();
    }


    /**
     * Get a tree structure representing the hierarchy of tutorials
     *
     * Returns an array in format:
     * <pre>
     * array('tutorial' => {@link parserTutorial},
     *       'kids' => array( // child tutorials
     *                   array('tutorial' => child {@link parserTutorial},
     *                         'kids' => array(...)
     *                        )
     *                      )
     *      )
     * </pre>
     * @param parserTutorial|array
     * @tutorial tutorials.pkg
     * @return array
     */
    function getTutorialTree($tutorial)
    {
        if (is_object($tutorial))
        {
            $path = $this->_tutorial_path($tutorial,$tutorial,$tutorial);
            if (isset($this->tutorial_tree[$path])) {
                $tutorial = $this->tutorial_tree[$path];
            } else {
                return false;
            }
        }
        $tree = array();
        if (isset($tutorial['tutorial']))
        {
            $tree['tutorial'] = $tutorial['tutorial'];
            if (isset($tutorial['child']))
            {
                foreach($tutorial['child'] as $a => $b)
                {
                    $btut = $b['tutorial'];
                    $res['tutorial'] = $this->tutorials[$btut->package][$btut->subpackage][$btut->tutorial_type][$btut->name];
                    if (isset($b['child']))
                    {
                         $tempres = Converter::getTutorialTree($b);
                         $res['kids'] = $tempres['kids'];
                    }
                    $tree['kids'][] = $res;
                }
            }
        }
        return $tree;
    }

    /**
     * Remove tutorials one by one from $all, and transfer them into $new in the
     * order they should be parsed
     * @param parserTutorial
     * @param array
     * @param array
     * @access private
     */
    function generateTutorialOrder($parent,&$all,&$new)
    {
        // remove from the list of tutorials to process
        foreach($all[$parent->package][$parent->subpackage][$parent->tutorial_type] as $ind => $t)
        {
            if ($t->name == $parent->name) {
                unset($all[$parent->package][$parent->subpackage][$parent->tutorial_type][$ind]);
            }
        }
        // add to the new ordered list of tutorials
        $x = &$new[$parent->package][$parent->subpackage][$parent->tutorial_type];
        if (!is_object($x[count($x) - 1]) || $x[count($x) - 1]->name != $parent->name)
        { // only add if the parent isn't also a child
            $new[$parent->package][$parent->subpackage][$parent->tutorial_type][] = $parent;
            // add a new branch to the tree
        }
        // process all child tutorials, and insert them in order
//        debug("processing parent ".$parent->name);
        if ($parent->ini)
        {
            foreach($parent->ini['Linked Tutorials'] as $child)
            {
                $sub = (empty($parent->subpackage) ? '' : $parent->subpackage . '/');
                $kid = $parent->package . '/' . $sub . $child . '.' . $parent->tutorial_type;
                $_klink = $this->getTutorialLink($kid,false,false,array($parent->package));
                if (is_object($_klink)) {
                    $klink = $this->returnSee($_klink);
                } else {
                    $klink = false;
                }
                // remove the child from the list of remaining tutorials
                foreach($all[$parent->package][$parent->subpackage][$parent->tutorial_type] as $ind => $tute)
                {
                    if ($klink && $tute->getLink($this) == $klink)
                    {
                        // set up parent, next and prev links
                        $tute->setParent($parent, $this);
                        // remove the child from the list of tutorials to process
                        foreach($all[$parent->package][$parent->subpackage][$parent->tutorial_type] as $ind => $t)
                        {
                            if ($t->name == $tute->name)
                            unset($all[$parent->package][$parent->subpackage][$parent->tutorial_type][$ind]);
                        }
                        // add to the new ordered list of tutorials
                        $new[$parent->package][$parent->subpackage][$parent->tutorial_type][] = $tute;
                        if ($tute->ini)
                        {
                            // add all the child's child tutorials to the list
                            $this->generateTutorialOrder($tute,$all,$new);
                        }
                    }
                }
            }
        }
        return;
    }

		/** Returns the path to this tutorial as a string
		 * @param parserTutorial $pkg
		 * @param parserTutorial $subpkg
		 * @param parserTutorial $namepkg
		 * @return string */
		function _tutorial_path($pkg, $subpkg = 0, $namepkg = 0)
		{
			if (!$subpkg) {
				$subpkg = $pkg;
			}
			if (!$namepkg) {
				$namepkg = $pkg;
			}
			$subpackagename = ($subpkg->subpackage ? '/' . $subpkg->subpackage : '');
			return $pkg->package . $subpackagename . '/' . $namepkg->name;
		}


    /**
     * Creates a tree structure of tutorials
     *
     * Format:
     * <pre>
     * array('package/subpackage/tutorial1.ext' =>
     *          array('tutorial' => {@link parserTutorial},
     *                'child'    =>
     *                    array('package/subpackage/child1tutorial.ext' => ...,
     *                          'package/subpackage/child2tutorial.ext' => ...,
     *                          ...
     *                         )
     *       'package/subpackage/tutorial2.ext' => ...,
     *       ...
     *       )
     * </pre>
     * @return array the tutorial tree
     * @access private
     */
    function _setupTutorialTree($parent = false)
    {
        if (! isset($this->processed_tutorials)) {
            $this->processed_tutorials = array();
        }
        $tree = array();
        if (!$parent)
        {
            foreach($this->tutorials as $package => $s)
            {
                foreach($s as $subpackage => $t)
                {
                    foreach($t as $type => $n)
                    {
                        foreach($n as $name => $tutorial)
                        {
                            if ($tutorial->parent) {
                                continue;
                            }
                            
                            $child_path = $this->_tutorial_path($tutorial,$tutorial,$tutorial);
                            if (isset($this->processed_tutorials[$child_path])) {
                                continue;
                            }
                            $this->processed_tutorials[$child_path] = $tutorial;
                            //debug("parent ".$tutorial->name);
                            $ret = $this->_setupTutorialTree($tutorial);
                            if (!count($tree)) {
                                $tree = $ret;
                            } else {
                                $tree = array_merge($tree,$ret);
                            }
                        }
                    }
                }
            }
            return $tree;
        }
        $parent_path = $this->_tutorial_path($parent);
        $tree[$parent_path]['tutorial'] = $parent;
        // process all child tutorials, and insert them in order
        if ($parent->ini)
        {
            foreach($parent->ini['Linked Tutorials'] as $child)
            {
                if (isset($this->tutorials[$parent->package][$parent->subpackage]
                                          [$parent->tutorial_type][$child . '.' .
                                           $parent->tutorial_type])) {
                    // remove the child from the list of remaining tutorials
                    $tute = $this->tutorials[$parent->package][$parent->subpackage]
                                            [$parent->tutorial_type][$child . '.' .
                                             $parent->tutorial_type];
                } else {
                    $tute = false;
                }

                if (!$tute) {
                    continue;
                }
                $child_path = $this->_tutorial_path($parent,$parent,$tute);
                if (isset($this->processed_tutorials[$child_path])) {
                    continue;
                }
                $this->processed_tutorials[$child_path] = $tute;
                if ($tute->name != $child . '.' . $parent->tutorial_type) {
                    continue;
                }
                //echo "Adding [$child_path] to [$parent_path]<br>";
                $tree[$parent_path]['child'][$this->_tutorial_path($parent,$parent,$tute)]['tutorial']
                    = $tute;
                if (!$tute->ini) {
                    continue;
                }
                // add all the child's child tutorials to the list
                if (!isset($tree[$parent_path]['child'])) {
                    $tree[$parent_path]['child'] = $this->_setupTutorialTree($tute);
                } else {
                    $tree[$parent_path]['child'] = array_merge($tree[$parent_path]['child'],
                        $this->_setupTutorialTree($tute));
                }
            }
        }
        return $tree;
    }

    /**
     * Debugging function for dumping {@link $tutorial_tree}
     * @return string
     */
    function vardump_tree($tree,$indent='')
    {
        if (phpDocumentor_get_class($tree) == 'parsertutorial') return $tree->name.' extends '.($tree->parent? $tree->parent->name : 'nothing');
        $a = '';
        foreach($tree as $ind => $stuff)
        {
            $x = $this->vardump_tree($stuff,"$indent   ");
            $a .= $indent.'['.$ind." => \n   ".$indent.$x."]\n";
        }
        return substr($a,0,strlen($a) - 1);
    }

    /**
     * @access private
     */
    function sort_package_elements($a,$b)
    {
        if (($a->type == $b->type) && (isset($a->isConstructor) && $a->isConstructor)) return -1;
        if (($a->type == $b->type) && (isset($b->isConstructor) && $b->isConstructor)) return 1;
        if ($a->type == $b->type) return strnatcasecmp($a->name,$b->name);
        if ($a->type == 'class') return -1;
        if ($b->type == 'class') return 1;
        if ($a->type == 'const') return -1;
        if ($b->type == 'const') return 1;
        if ($a->type == 'var') return -1;
        if ($b->type == 'var') return 1;
        if ($a->type == 'page') return -1;
        if ($b->type == 'page') return 1;
        if ($a->type == 'include') return -1;
        if ($b->type == 'include') return 1;
        if ($a->type == 'define') return -1;
        if ($b->type == 'define') return 1;
        if ($a->type == 'global') return -1;
        if ($b->type == 'global') return 1;
        if ($a->type == 'function') return -1;
        if ($b->type == 'function') return 1;
    }

    /**
     * @access private
     */
    function defpackagesort($a,$b)
    {
        if ($a == $GLOBALS['phpDocumentor_DefaultPackageName']) return -1;
        if ($b == $GLOBALS['phpDocumentor_DefaultPackageName']) return 0;
        return strnatcasecmp($a,$b);
    }

    /**
     * @access private
     */
    function Pc_sort($a,$b)
    {
        return strnatcasecmp(key($a),key($b));
    }

    /**
     * walk over elements by package rather than page
     *
     * This method is designed for converters like the PDF converter that need
     * everything passed in alphabetical order by package/subpackage and by
     * procedural and then class information
     * @see PDFdefaultConverter
     * @see walk()
     */
    function walk_everything()
    {
        global $hooser;
        $hooser = false;
        uksort($this->package_elements,array($this,'defpackagesort'));
        foreach($this->package_elements as $package => $r)
        {
            if ($this->package_output)
            {
                if (!in_array($this->package,$this->package_output))
                {
                    unset($this->package_elements[$package]);
                    continue;
                }
            }
            uksort($this->package_elements[$package],'strnatcasecmp');
        }
        foreach($this->package_elements as $package => $r)
        {
            foreach($this->package_elements[$package] as $subpackage => $r)
            {
                if (isset($r['page']))
                {
                    uksort($r['page'],'strnatcasecmp');
                    foreach($r['page'] as $page => $oo)
                    {
                        usort($this->package_elements[$package][$subpackage]['page'][$page],array($this,'sort_package_elements'));
                    }
                }
                if (isset($r['class']))
                {
                    uksort($r['class'],'strnatcasecmp');
                    foreach($r['class'] as $page => $oo)
                    {
                        usort($r['class'][$page],array($this,'sort_package_elements'));
                    }
                }
                $this->package_elements[$package][$subpackage] = $r;
            }
        }
        foreach($this->package_elements as $package => $s)
        {
            $notyet = false;
            foreach($s as $subpackage => $r)
            {
                $this->package = $package;
                $this->subpackage = $subpackage;
                if (isset($r['page']))
                {
                    $this->class = false;
                    foreach($r['page'] as $page => $elements)
                    {
                        if (is_array($elements))
                        {
                            foreach($elements as $element)
                            {
                                if ($element->type == 'page')
                                {
                                    phpDocumentor_out('Converting '.$element->parent->getPath());
                                    flush();
                                    $this->curfile = $element->parent->getFile();
                                    $this->curname = $this->getPageName($element->parent);
                                    $this->curpath = $element->parent->getPath();
                                    $notyet = true;
                                } else
                                {
                                    // force all contained elements to have parent package/subpackage
                                    $element->docblock->package = $this->package;
                                    $element->docblock->subpackage = $this->subpackage;
                                    $a = $element->docblock->getKeyword('access');
                                    if (is_object($a)) $a = $a->getString();
                                    if (!$this->parseprivate && ($a == 'private'))
                                        continue;
                                }
                                if ($notyet)
                                {
                                    phpDocumentor_out(" Procedural Page Elements...");
                                    flush();
                                    $notyet = false;
                                }
                                $this->Convert($element);
                            }
                        }
                        $this->endPage();
                        phpDocumentor_out("done\n");
                        flush();
                    }
                }
                $start_classes = true;
                if (isset($r['class']))
                {
                    foreach($r['class'] as $class => $elements)
                    {
                        foreach($elements as $element)
                        {
                            if ($element->type == 'class')
                            {
                                if (!$start_classes)
                                {
                                    if (count($elements) && !$this->killclass) $this->endClass();
                                    phpDocumentor_out("done\n");
                                    flush();
                                }
                                $start_classes = false;
                                $this->class = $element->getName();
                                $this->killclass = false;
                                if ($this->checkKillClass($element->getName(),$element->getPath())) continue;
                                if (!$this->killclass)
                                {
                                    phpDocumentor_out('Converting '.$this->class."...");
                                    flush();
                                    $notyet = true;
                                }
                            } else
                            {
                                if ($notyet)
                                {
                                    phpDocumentor_out("Variables/methods/Class constants...\n");
                                    flush();
                                    $notyet = false;
                                }
                                $a = $element->docblock->getKeyword('access');
                                if (is_object($a)) $a = $a->getString();
                                if (!$this->parseprivate && ($a == 'private'))
                                    continue;
                                if ($this->killclass) continue;
                                // force all contained elements to have parent package/subpackage
                                $element->docblock->package = $this->package;
                                $element->docblock->subpackage = $this->subpackage;
                            }
                            if ($this->killclass) continue;
                            $this->Convert($element);
                        }
                    }
                    if (count($elements) && !$this->killclass) $this->endClass();
                    phpDocumentor_out("done\n");
                    flush();
                } // if isset($r['class'])
            } // foreach($s
        } // foreach($this->package_elements)
        phpDocumentor_out("\nConverting @todo List...");
        flush();
        if (count($this->todoList))
        {
            $this->ConvertTodoList();
        }
        phpDocumentor_out("done\n");
        flush();
        phpDocumentor_out("\nConverting Error Log...");
        flush();
        $this->ConvertErrorLog();
        phpDocumentor_out("done\n");
        flush();
    }

    /**
     * Convert the phpDocumentor parsing/conversion error log
     * @abstract
     */
    function ConvertErrorLog()
    {
    }

    /**
     * Convert the list of all @todo tags
     * @abstract
     */
    function ConvertTodoList()
    {
    }

    /**
     * Sorts the @todo list - do not override or modify this function
     * @access private
     * @uses _sortTodos passed to {@link usort()} to sort the todo list
     */
    function sortTodos()
    {
        phpDocumentor_out("\nSorting @todo list...");
        flush();
        foreach($this->todoList as $package => $r) {
            usort($this->todoList[$package], array('Converter', '_sortTodoPackage'));
            foreach ($r as $a => $sub) {
                if (is_array($this->todoList[$package][$a][1])) {
                    usort($this->todoList[$package][$a][1],array('Converter', '_sortTodos'));
                }
            }
        }
        phpDocumentor_out("done\n");
    }

    /** @access private */
    function _sortTodoPackage($a, $b)
    {
        return strnatcasecmp($a[0]->name, $b[0]->name);
    }

    /** @access private */
    function _sortTodos($a, $b)
    {
        if (!is_object($a)) {
            var_dump($a);
        }
        return strnatcasecmp($a->getString(), $b->getString());
    }

    /**
     * Sorts all indexes - do not override or modify this function
     * @uses $leftindex based on the value of leftindex, sorts link arrays
     * @uses $class_elements sorts with {@link compareLink}
     * @uses $page_elements sorts with {@link compareLink}
     * @uses $define_elements sorts with {@link compareLink}
     * @uses $global_elements sorts with {@link compareLink}
     * @uses $function_elements sorts with {@link compareLink}
     * @uses $elements sorts with {@link elementCmp}
     * @uses $pkg_elements sorts with {@link elementCmp} after sorting by
     *                     package/subpackage alphabetically
     * @access private
     */
    function sortIndexes()
    {
        phpDocumentor_out("\nSorting Indexes...");
        flush();
        uksort($this->elements,'strnatcasecmp');
        if ($this->leftindex['classes'])
        {
            foreach($this->class_elements as $package => $o1)
            {
                foreach($o1 as $subpackage => $links)
                {
                    usort($this->class_elements[$package][$subpackage],array($this,'compareLink'));
                }
            }
        }
        if ($this->leftindex['pages'])
        {
            foreach($this->page_elements as $package => $o1)
            {
                uksort($this->page_elements[$package],'strnatcasecmp');
                foreach($o1 as $subpackage => $links)
                {
                    usort($this->page_elements[$package][$subpackage],array($this,'compareLink'));
                }
            }
        }
        if ($this->leftindex['defines'])
        {
            foreach($this->define_elements as $package => $o1)
            {
                uksort($this->define_elements[$package],'strnatcasecmp');
                foreach($o1 as $subpackage => $links)
                {
                    usort($this->define_elements[$package][$subpackage],array($this,'compareLink'));
                }
            }
        }
        if ($this->leftindex['globals'])
        {
            foreach($this->global_elements as $package => $o1)
            {
                uksort($this->global_elements[$package],'strnatcasecmp');
                foreach($o1 as $subpackage => $links)
                {
                    usort($this->global_elements[$package][$subpackage],array($this,'compareLink'));
                }
            }
        }
        if ($this->leftindex['functions'])
        {
            foreach($this->function_elements as $package => $o1)
            {
                uksort($this->function_elements[$package],'strnatcasecmp');
                foreach($o1 as $subpackage => $links)
                {
                    usort($this->function_elements[$package][$subpackage],array($this,'compareLink'));
                }
            }
        }
        foreach($this->elements as $letter => $nothuing)
        {
            uasort($this->elements[$letter],array($this,"elementCmp"));
        }
        foreach($this->pkg_elements as $package => $els)
        {
            uksort($this->pkg_elements[$package],'strnatcasecmp');
            foreach($this->pkg_elements[$package] as $subpackage => $els)
            {
                if (empty($els)) continue;
                uksort($this->pkg_elements[$package][$subpackage],'strnatcasecmp');
                foreach($els as $letter => $yuh)
                {
                    usort($this->pkg_elements[$package][$subpackage][$letter],array($this,"elementCmp"));
                }
            }
        }
        phpDocumentor_out("done\n");
        flush();
    }

    /**
     * sorts {@link $page_contents} by element type as well as alphabetically
     * @see $sort_page_contents_by_element_type
     */
    function sortPageContentsByElementType(&$pages)
    {
        foreach($this->page_contents as $package => $els)
        {
            foreach($this->page_contents[$package] as $subpackage => $els)
            {
                if (empty($els)) continue;
                foreach($this->page_contents[$package][$subpackage] as $path => $stuff)
                {
                    if (!count($pages[$path]->elements)) continue;
                    usort($pages[$path]->elements,array($this,'eltypecmp'));
                    usort($this->page_contents[$package][$subpackage][$path],array($this,'eltypecmp'));
                    if (isset($this->page_contents[$package][$subpackage][$path][0]))
                    $this->page_contents[$package][$subpackage][$path]['###main'] = $this->page_contents[$package][$subpackage][$path][0];
                    unset($this->page_contents[$package][$subpackage][$path][0]);
                }
            }
        }
    }

    /**
     * @access private
     * @see Converter::sortIndexes()
     */
    function compareLink($a, $b)
    {
         return strnatcasecmp($a->name,$b->name);
    }

    /**
     * @access private
     * @see Converter::sortPageContentsByElementType()
     */
    function eltypecmp($a, $b)
    {
        if ($a->type == 'page') return -1;
        if ($b->type == 'page') return 1;
         return strnatcasecmp($a->type.$a->name,$b->type.$b->name);
    }

    /**
     * does a nat case sort on the specified second level value of the array
     *
     * @param    mixed    $a
     * @param    mixed    $b
     * @return    int
     * @access private
     */
    function elementCmp ($a, $b)
    {
        return strnatcasecmp($a->getName(), $b->getName());
    }

    /**
     * Used to stop conversion of @ignored or private @access classes
     * @uses $killclass sets killclass based on the value of {@link Classes::$killclass}
     *       and {@link $package_output}
     * @access private
     */
    function checkKillClass($class, $path)
    {
        $this->killclass = false;
        if (isset($this->classes->killclass[$class]) && isset($this->classes->killclass[$class][$path])) $this->killclass = true;
        if ($this->package_output)
        {
            $a = $this->classes->getClass($class, $path);
            if (!in_array($a->docblock->package,$this->package_output)) $this->killclass = true;
        }
        if (PHPDOCUMENTOR_DEBUG && $this->killclass) debug("$class $path killed");
        return $this->killclass;
    }

    /**
     * @param abstractLink descendant of abstractLink
     * @param array|parserTag list of @todos|@todo tag
     * @access private
     */
    function addTodoLink($link, $todos)
    {
        $this->todoList[$link->package][] = array($link, $todos);
    }

    /**
     * Adds all elements to the {@link $elements, $pkg_elements, $links},
     * {@link $linkswithfile} and left indexes - Do not modify or override
     * @access private
     * @param parserBase any documentable element descendant of parserBase
     *                   except parserTutorial
     * @param false|parserPage only used to add a {@link parserPage} if the
     *                         $element passed is a parserPage
     * @staticvar string path of current page, used for {@link $page_contents} setup
     */
    function addElement(&$element,$pageel=false)
    {
        static $curpath = '';
        if ($this->package_output)
        {
            if (!in_array($this->package, $this->package_output)) return;
        }
        if ($pageel && phpDocumentor_get_class($pageel) == 'parserdata')
        {
            if (isset($pageel->docblock) && phpDocumentor_get_class($pageel->docblock) == 'parserdocblock')
            {
                $a = $pageel->docblock->getKeyword('todo');
                if ($a)
                {
                    $this->addTodoLink($this->addLink($element),$a);
                }
            }
        }
        if (isset($element->docblock))
        {
            $a = $element->docblock->getKeyword('access');
            if (is_object($a)) $a = $a->getString();
            if (!$this->parseprivate && ($a == 'private'))
                return;
            $a = $element->docblock->getKeyword('todo');
            if ($a)
            {
                if ($element->type != 'include') {
                    $this->addTodoLink($this->addLink($element),$a);
                } else {
                    addWarning(PDERROR_NOTODO_INCLUDE, $element->getLineNumber(),
                        $element->getPath());
                }
            }
        }
        $startPositionOfElementName = 0;    // which character of the element name actually starts its textual name
        switch($element->type)
        {
            case 'page' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->package][$element->subpackage]['page'][$element->getPath()][] = $pageel;
                }
                $link = $this->addLink($element);
                $curpath = $element->getPath();
                if ($this->leftindex['pages'])
                $this->page_elements[$element->package][$element->subpackage][] = $link;
                $this->page_contents[$element->package][$element->subpackage][$curpath]['###main'] = $link;
            break;
            case 'class' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['class'][$this->class][] = $element;
                }
                $link = $this->addLink($element);
                if ($this->leftindex['classes'])
                $this->class_elements[$element->docblock->package][$element->docblock->subpackage][] = $link;
                $this->class_contents[$element->docblock->package][$element->docblock->subpackage][$this->class]['###main'] = $link;
            break;
            case 'include' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['page'][$curpath][] = $element;
                }
                $link = $this->addLink($element);
            break;
            case 'define' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['page'][$curpath][] = $element;
                }
                $link = $this->addLink($element);
                if ($this->leftindex['defines'])
                $this->define_elements[$element->docblock->package][$element->docblock->subpackage][] = $link;
                $this->page_contents[$element->docblock->package][$element->docblock->subpackage][$curpath][] = $link;
            break;
            case 'global' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['page'][$curpath][] = $element;
                }
                $link = $this->addLink($element);
                $startPositionOfElementName = 1;    // lose the leading "$" character
                if ($this->leftindex['globals'])
                $this->global_elements[$element->docblock->package][$element->docblock->subpackage][] = $link;
                $this->page_contents[$element->docblock->package][$element->docblock->subpackage][$curpath][] = $link;
            break;
            case 'var' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['class'][$this->class][] = $element;
                }
                $link = $this->addLink($element);
                $startPositionOfElementName = 1;    // lose the leading "$" character
                $this->class_contents[$element->docblock->package][$element->docblock->subpackage][$this->class][] = $link;
            break;
            case 'const' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['class'][$this->class][] = $element;
                }
                $link = $this->addLink($element);
                $this->class_contents[$element->docblock->package][$element->docblock->subpackage][$this->class][] = $link;
            break;
            case 'method' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['class'][$this->class][] = $element;
                }
                $link = $this->addLink($element);
                $this->class_contents[$element->docblock->package][$element->docblock->subpackage][$this->class][] = $link;
            break;
            case 'function' :
                if ($this->sort_absolutely_everything)
                {
                    $this->package_elements[$element->docblock->package][$element->docblock->subpackage]['page'][$curpath][] = $element;
                }
                $link = $this->addLink($element);
                if ($this->leftindex['functions'])
                $this->function_elements[$element->docblock->package][$element->docblock->subpackage][] = $link;
                $this->page_contents[$element->docblock->package][$element->docblock->subpackage][$curpath][] = $link;
            break;
            default :
            break;
        }
        if ($element->getType() != 'include')
        {
            if ($element->getType() == 'var' || $element->getType() == 'method'|| $element->getType() == 'const')
            {
                $this->links[$this->package][$this->subpackage][$element->getType()][$element->class][$element->getName()] = $link;
                $this->linkswithfile[$this->package][$this->subpackage][$element->getType()][$element->getPath()][$element->class][$element->getName()] = $link;
            } else
            {
                if ($element->type == 'page')
                {
                    $this->links[$this->package][$this->subpackage][$element->getType()][$element->getFile()] = $link;
                    $this->linkswithfile[$this->package][$this->subpackage][$element->getType()][$element->getPath()][$element->getFile()] = $link;
                } else
                {
                    $this->links[$this->package][$this->subpackage][$element->getType()][$element->getName()] = $link;
                    $this->linkswithfile[$this->package][$this->subpackage][$element->getType()][$element->getPath()][$element->getName()] = $link;
                }
            }
        }
        if ($element->type == 'page')
        {
            $this->elements[substr(strtolower($element->getFile()),$startPositionOfElementName,1)][] = $element;
            $this->pkg_elements[$this->package][$this->subpackage][substr(strtolower($element->getFile()),$startPositionOfElementName,1)][] = $element;
        } else
        {
            $this->elements[substr(strtolower($element->getName()),$startPositionOfElementName,1)][] = $element;
            $this->pkg_elements[$this->package][$this->subpackage][substr(strtolower($element->getName()),$startPositionOfElementName,1)][] = $element;
        }
    }

    /**
     * returns an abstract link to element.  Do not modify or override
     *
     * This method should only be called in process of Conversion, unless
     * $element is a parserPage, or $page is set to true, and $element is
     * not a parserPage
     * @return abstractLink abstractLink descendant
     * @access private
     * @param parserElement element to add a new link (descended from
     *                      {@link abstractLink})to the {@link $links} array
     * @param string classname for elements that are class-based (this may be
     *               deprecated in the future, as the classname
     *               should be contained within the element.  if $element is a
     *               page, this parameter is a package name
     * @param string subpackage name for page elements
     */
    function addLink(&$element,$page = false)
    {
        if ($page)
        {
            // create a fake parserPage to extract the fileAlias for this link
            $fakepage = new parserPage;
            $fakepage->setPath($element->getPath());
            $fakepage->setFile(basename($element->getPath()));
            $this->curname = $this->getPageName($fakepage);
        }
        switch($element->type)
        {
            case 'function':
                $x = new functionLink;
                $x->addLink($element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'define':
                $x = new defineLink;
                $x->addLink($element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'global':
                $x = new globalLink;
                $x->addLink($element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'class':
                $x = new classLink;
                $x->addLink($element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'method':
                $x = new methodLink;
                $x->addLink($this->class, $element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'var':
                $x = new varLink;
                $x->addLink($this->class, $element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'const':
                $x = new constLink;
                $x->addLink($this->class, $element->getPath(), $this->curname, $element->name, $element->docblock->package, $element->docblock->subpackage, $element->docblock->category);
                return $x;
            break;
            case 'page':
                $x = new pageLink;
                $x->addLink($element->getPath(),$this->getPageName($element),$element->file,$element->package, $element->subpackage, $element->category);
                return $x;
            break;
        }
    }

    /**
     * Return a tree of all classes that extend this class
     *
     * The data structure returned is designed for a non-recursive algorithm,
     * and is somewhat complex.
     * In most cases, the array returned is:
     *
     * <pre>
     * array('#root' =>
     *         array('link' => {@link classLink} to $class,
     *               'parent' => false,
     *               'children' => array(array('class' => 'childclass1',
     *                                         'package' => 'child1package'),
     *                                    array('class' => 'childclass2',
     *                                         'package' => 'child2package'),...
     *                                  )
     *               ),
     *       'child1package#childclass1' =>
     *         array('link' => {@link classLink} to childclass1,
     *               'parent' => '#root',
     *               'children' => array(array('class' => 'kidclass',
     *                                         'package' => 'kidpackage'),...
     *                                  )
     *              ),
     *       'kidpackage#kidclass' =>
     *         array('link' => {@link classLink} to kidclass,
     *               'parent' => 'child1package#childclass1',
     *               'children' => array() // no children
     *              ),
     *      ....
     *      )
     *</pre>
     *
     * To describe this format using language, every class in the tree has an
     * entry in the first level of the array.  The index for all child
     * classes that extend the root class is childpackage#childclassname.
     * Each entry in the array has 3 elements: link, parent, and children.
     * <ul>
     *  <li>link - a {@link classLink} to the current class</li>
     *  <li>parent - a {@link classLink} to the class's parent, or false (except for one special case described below)</li>
     *  <li>children - an array of arrays, each entry has a 'class' and 'package' index to the child class,
     * used to find the entry in the big array</li>
     * </ul>
     *
     * special cases are when the #root class has a parent in another package,
     * or when the #root class extends a class not found
     * by phpDocumentor.  In the first case, parent will be a
     * classLink to the parent class.  In the second, parent will be the
     * extends clause, as in:
     * <code>
     * class X extends Y
     * {
     * ...
     * }
     * </code>
     * in this case, the #root entry will be array('link' => classLink to X, 'parent' => 'Y', children => array(...))
     *
     * The fastest way to design a method to process the array returned
     * is to copy HTMLframesConverter::getRootTree() into
     * your converter and to modify the html to whatever output format you are going to use
     * @see HTMLframesConverter::getRootTree()
     * @param string class name
     * @param string
     * @param string
     * @return array Format: see docs
     */
    function getSortedClassTreeFromClass($class,$package,$subpackage)
    {
        $my_tree = array();
        $root = $this->classes->getClassByPackage($class,$package);
        if (!$root) return false;
        $class_children = $this->classes->getDefiniteChildren($class,$root->curfile);
        if (!$class_children)
        {
            // special case: parent class is found, but is not part of this package, class has no children
            if (is_array($root->parent))
            {
                $x = $root->getParent($this);
                if ($x->docblock->package != $package)
                {
                    $v = Converter::getClassLink($root->getName(),$package,$root->getPath());
                    return array('#root' => array('link' => $v,'parent' => Converter::getClassLink($x->getName(),$x->docblock->package,$x->getPath()), 'children' => array()));
                }
            } else
            { // class has normal situation, no children
                if (is_string($root->getParent($this)))
                return array('#root' => array('link' => Converter::getClassLink($root->getName(),$package,$root->getPath()), 'parent' => $root->getExtends(),'children' => array()));
                else
                return array('#root' => array('link' => Converter::getClassLink($root->getName(),$package,$root->getPath()), 'parent' => false, 'children' => array()));
            }
        }
        // special case: parent class is found, but is not part of this package, class has children
        if (is_array($root->parent))
        {
            $x = $root->getParent($this);
            if ($x->docblock->package != $package)
            {
                $v = Converter::getClassLink($root->getName(),$package,$root->getPath());
                $my_tree = array('#root' => array('link' => $v, 'parent' => Converter::getClassLink($x->getName(),$x->docblock->package,$x->getPath()), 'children' => array()));
            } else
            {
            }
        } else
        $my_tree = array('#root' => array('link' => Converter::getClassLink($root->getName(),$package,$root->getPath()), 'parent' => false, 'children' => array()));
        // location of tree walker
        $cur = '#root';
        $lastcur = array(array(false,0));
        $childpos = 0;
        if (isset($class_children))
        {
            do
            {
                if (!$class_children)
                {
                    list($cur, $childpos) = array_pop($lastcur);
                    if (isset($my_tree[$cur]['children'][$childpos + 1]))
                    {
                        array_push($lastcur, array($cur, $childpos + 1));
                        $par = $cur;
                        $cur = $my_tree[$cur]['children'][$childpos + 1];
                        $x = $this->classes->getClassByPackage($cur['class'],$cur['package']);
                        $childpos = 0;
                        $cur = $cur['package'] . '#' . $cur['class'];
                        $my_tree[$cur]['link'] = Converter::getClassLink($x->getName(),$x->docblock->package,$x->getPath());
                        $my_tree[$cur]['parent'] = $par;
                        $my_tree[$cur]['children'] = array();
                        $class_children = $this->classes->getDefiniteChildren($x->getName(), $x->curfile);
                        continue;
                    } else
                    {
                        $class_children = false;
                        continue;
                    }
                }
                foreach($class_children as $chileclass => $chilefile)
                {
                    $ch = $this->classes->getClass($chileclass,$chilefile);
                    $my_tree[$cur]['children'][] = array('class' => $ch->getName(), 'package' => $ch->docblock->package);
                }
                usort($my_tree[$cur]['children'],'rootcmp');
                if (isset($my_tree[$cur]['children'][$childpos]))
                {
                    array_push($lastcur, array($cur, $childpos));
                    $par = $cur;
                    $cur = $my_tree[$cur]['children'][$childpos];
                    $x = $this->classes->getClassByPackage($cur['class'],$cur['package']);
                    $cur = $cur['package'] . '#' . $cur['class'];
                    $my_tree[$cur]['link'] = Converter::getClassLink($x->getName(),$x->docblock->package,$x->getPath());
                    $my_tree[$cur]['parent'] = $par;
                    $my_tree[$cur]['children'] = array();
                    $childpos = 0;
                    $class_children = $this->classes->getDefiniteChildren($x->getName(), $x->curfile);
                } else
                {
                    list($cur, $childpos) = array_pop($lastcur);
                }
            } while ($cur);
        }
        return $my_tree;
    }

    /**
     * do not override
     * @return bool true if a link to this class exists in package $package and subpackage $subpackage
     * @param string $expr class name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedClass($expr,$package,$subpackage,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['class'][$file][$expr]);
        return isset($this->links[$package][$subpackage]['class'][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this function exists in package $package and subpackage $subpackage
     * @param string $expr function name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedFunction($expr,$package,$subpackage,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['function'][$file][$expr]);
        return isset($this->links[$package][$subpackage]['function'][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this define exists in package $package and subpackage $subpackage
     * @param string $expr define name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedDefine($expr,$package,$subpackage,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['define'][$file][$expr]);
        return isset($this->links[$package][$subpackage]['define'][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this define exists in package $package and subpackage $subpackage
     * @param string $expr define name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedGlobal($expr,$package,$subpackage,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['global'][$file][$expr]);
        return isset($this->links[$package][$subpackage]['global'][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this procedural page exists in package $package and subpackage $subpackage
     * @param string $expr procedural page name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedPage($expr,$package,$subpackage,$path=false)
    {
        if ($path)
        return isset($this->linkswithfile[$package][$subpackage]['page'][$path][$expr]);
        return isset($this->links[$package][$subpackage]['page'][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this method exists in package $package, subpackage $subpackage and class $class
     * @param string $expr method name
     * @param string $class class name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedMethod($expr,$package,$subpackage,$class,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['method'][$file][$class][$expr]);
        return isset($this->links[$package][$subpackage]['method'][$class][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this method exists in package $package, subpackage $subpackage and class $class
     * @param string $expr var name
     * @param string $class class name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedVar($expr,$package,$subpackage,$class,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['var'][$file][$class][$expr]);
        return isset($this->links[$package][$subpackage]['var'][$class][$expr]);
    }

    /**
     * do not override
     * @return bool true if a link to this method exists in package $package, subpackage $subpackage and class $class
     * @param string $expr constant name
     * @param string $class class name
     * @param string $package package to search in
     * @param string $subpackage subpackage to search in
     * @access private
     */
    function isLinkedConst($expr,$package,$subpackage,$class,$file=false)
    {
        if ($file)
        return isset($this->linkswithfile[$package][$subpackage]['const'][$file][$class][$expr]);
        return isset($this->links[$package][$subpackage]['const'][$class][$expr]);
    }

    /**
     * return false or a {@link classLink} to $expr
     * @param string $expr class name
     * @param string $package package name
     * @return mixed returns a {@link classLink} or false if the element is not found in package $package
     * @see classLink
     */
    function getClassLink($expr,$package,$file=false, $text = false)
    {
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedClass($expr,$package,$subpackage,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['class'][$file][$expr];
                }
                return $this->links[$package][$subpackage]['class'][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link functionLink} to $expr
     * @param string $expr function name
     * @param string $package package name
     * @return mixed returns a {@link functionLink} or false if the element is not found in package $package
     * @see functionLink
     */
    function getFunctionLink($expr,$package,$file=false, $text = false)
    {
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedFunction($expr,$package,$subpackage,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['function'][$file][$expr];
                }
                return $this->links[$package][$subpackage]['function'][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link defineLink} to $expr
     * @param string $expr constant name
     * @param string $package package name
     * @return mixed returns a {@link defineLink} or false if the element is not found in package $package
     * @see defineLink
     */
    function getDefineLink($expr,$package,$file=false, $text = false)
    {
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedDefine($expr,$package,$subpackage,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['define'][$file][$expr];
                }
                return $this->links[$package][$subpackage]['define'][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link globalLink} to $expr
     * @param string $expr global variable name (with leading $)
     * @param string $package package name
     * @return mixed returns a {@link defineLink} or false if the element is not found in package $package
     * @see defineLink
     */
    function getGlobalLink($expr,$package,$file=false, $text = false)
    {
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedGlobal($expr,$package,$subpackage,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['global'][$file][$expr];
                }
                return $this->links[$package][$subpackage]['global'][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link pageLink} to $expr
     * @param string $expr procedural page name
     * @param string $package package name
     * @return mixed returns a {@link pageLink} or false if the element is not found in package $package
     * @see pageLink
     */
    function getPageLink($expr,$package,$path = false, $text = false, $packages = false)
    {
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedPage($expr,$package,$subpackage,$path))
            {
                if ($path)
                {
                    return $this->linkswithfile[$package][$subpackage]['page'][$path][$expr];
                }
                return $this->links[$package][$subpackage]['page'][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link methodLink} to $expr in $class
     * @param string $expr method name
     * @param string $class class name
     * @param string $package package name
     * @return mixed returns a {@link methodLink} or false if the element is not found in package $package, class $class
     * @see methodLink
     */
    function getMethodLink($expr,$class,$package,$file=false, $text = false)
    {
        $expr = trim($expr);
        $class = trim($class);
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedMethod($expr,$package,$subpackage,$class,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['method'][$file][$class][$expr];
                }
                return $this->links[$package][$subpackage]['method'][$class][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link varLink} to $expr in $class
     * @param string $expr var name
     * @param string $class class name
     * @param string $package package name
     * @return mixed returns a {@link varLink} or false if the element is not found in package $package, class $class
     * @see varLink
     */
    function getVarLink($expr,$class,$package,$file=false, $text = false)
    {
        $expr = trim($expr);
        $class = trim($class);
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedVar($expr,$package,$subpackage,$class,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['var'][$file][$class][$expr];
                }
                return $this->links[$package][$subpackage]['var'][$class][$expr];
            }
        }
        return false;
    }

    /**
     * return false or a {@link constLink} to $expr in $class
     * @param string $expr constant name
     * @param string $class class name
     * @param string $package package name
     * @return mixed returns a {@link varLink} or false if the element is not found in package $package, class $class
     * @see constLink
     */
    function getConstLink($expr,$class,$package,$file=false, $text = false)
    {
        $expr = trim($expr);
        $class = trim($class);
        if (!isset($this->links[$package])) return false;
        foreach($this->links[$package] as $subpackage => $notused)
        {
            if ($this->isLinkedConst($expr,$package,$subpackage,$class,$file))
            {
                if ($file)
                {
                    return $this->linkswithfile[$package][$subpackage]['const'][$file][$class][$expr];
                }
                return $this->links[$package][$subpackage]['const'][$class][$expr];
            }
        }
        return false;
    }

    /**
     * The meat of the @tutorial tag and inline {@}tutorial} tag
     *
     * Take a string and return an abstract link to the tutorial it represents.
     * Since tutorial naming literally works like the underlying filesystem, the
     * way to reference the tutorial is similar.  Tutorials are located in a
     * subdirectory of any directory parsed, which is named 'tutorials/' (we
     * try to make things simple when we can :).  They are further organized by
     * package and subpackage as:
     *
     * tutorials/package/subpackage
     *
     * and the files are named *.cls, *.pkg, or *.proc, and so a link to a tutorial
     * named file.cls can be referenced (depending on context) as any of:
     *
     * <code>
     * * @tutorial package/subpackage/file.cls
     * * @tutorial package/file.cls
     * * @tutorial file.cls
     * </code>
     *
     * The first case will only be needed if file.cls exists in both the current
     * package, in anotherpackage/file.cls and in anotherpackage/subpackage/file.cls
     * and you wish to reference the one in anotherpackage/subpackage.
     * The second case is only needed if you wish to reference file.cls in another
     * package and it is unique in that package. the third will link to the first
     * file.cls it finds using this search method:
     *
     * <ol>
     *    <li>current package/subpackage</li>
     *    <li>all other subpackages of current package</li>
     *    <li>parent package, if this package has classes that extend classes in
     *    another package</li>
     *    <li>all other packages</li>
     * </ol>
     * @return tutorialLink|string returns either a link, or the original text, if not found
     * @param string the original expression
     * @param string package to look in first
     * @param string subpackage to look in first
     * @param array array of package names to search in if not found in parent packages.
     *              This is used to limit the search, phpDocumentor automatically searches
     *              all packages
     * @since 1.2
     */
    function getTutorialLink($expr, $package = false, $subpackage = false, $packages = false)
    {
        // is $expr a comma-delimited list?
        if (strpos($expr,','))
        {
            $a = explode(',',$expr);
            $b = array();
            for($i=0;$i<count($a);$i++)
            {
                // if so return each component with a link
                $b[] = Converter::getTutorialLink(trim($a[$i]));
            }
            return $b;
        }
        $subsection = '';
        if (strpos($expr,'#'))
        {
            $a = explode('#',$expr);
            $org = $expr;
            $expr = $a[0];
            $subsection = $a[1];
        }
        if (strpos($expr,'/'))
        {
            $a = explode('/',$expr);
            if (count($a) == 3)
            {
                return Converter::getTutorialLink($a[2],$a[0],$a[1],array());
            }
            if (count($a) == 2)
            {
                return Converter::getTutorialLink($a[1],$a[0],false,array());
            }
        }
        if (!$package) $package = $this->package;
        if (!$subpackage) $subpackage = $this->subpackage;
        if (!isset($this->all_packages[$package])) return $expr;
        elseif (isset($packages[$package])) unset($packages[$package]);
        $ext = pathinfo($expr, PATHINFO_EXTENSION);
        if (isset($this->tutorials[$package][$subpackage][$ext][$expr]))
        {
            $a = $this->tutorials[$package][$subpackage][$ext][$expr];
            $link = new tutorialLink;
            $link->addLink($subsection,$a->path,$a->name,$a->package,$a->subpackage,$a->getTitle($this,$subsection));
            return $link;
        }
        do
        {
            if (!is_array($packages))
            {
                $packages = $this->all_packages;
                if (isset($packages[$package])) unset($packages[$package]);
            }
            if (isset($this->tutorials[$package]))
            {
                if (isset($this->tutorials[$package][$subpackage][$ext][$expr]))
                {
                    $a = $this->tutorials[$package][$subpackage][$ext][$expr];
                    $link = new tutorialLink;
                    $link->addLink($subsection,$a->path,$a->name,$a->package,$a->subpackage,$a->getTitle($this));
                    return $link;
                } else
                {
                    foreach($this->tutorials[$package] as $subpackage => $stuff)
                    {
                        if (isset($stuff[$ext][$expr]))
                        {
                            $a = $stuff[$ext][$expr];
                            $link = new tutorialLink;
                            $link->addLink($subsection,$a->path,$a->name,$a->package,$a->subpackage,$a->getTitle($this));
                            return $link;
                        }
                    }
                }
            }
            // try other packages
            // look in parent package first, if found
            if (isset($this->package_parents[$package]))
            {
                $p1 = $package;
                $package = $this->package_parents[$package];
            } else
            {
                // no parent package, so start with the first one that's left
                list($package,) = @each($packages);
            }
            if ($package)
            {
                if (isset($packages[$package])) unset($packages[$package]);
            }
        } while (count($packages) || $package);
        addWarning(PDERROR_TUTORIAL_NOT_FOUND,$expr);
        return $expr;
    }

    /**
     * The meat of the @see tag and inline {@}link} tag
     *
     * $expr is a string with many allowable formats:
     * <ol>
     *  <li>proceduralpagename.ext</li>
     *  <li>constant_name</li>
     *  <li>classname::function()</li>
     *  <li>classname::constantname</li> (new 1.2.4)
     *  <li>classname::$variablename</li>
     *  <li>classname</li>
     *  <li>object classname</li>
     *  <li>function functionname()</li>
     *  <li>global $globalvarname</li>
     *  <li>packagename#expr where expr is any of the above</li>
     * </ol>
     *
     * New in version 1.1, you can explicitly specify a package to link to that
     * is different from the current package.  Use the # operator
     * to specify a new package, as in tests#bug-540368.php (which should appear
     * as a link like: "{@link tests#bug-540368.php}").  This
     * example links to the procedural page bug-540368.php in package
     * tests.  Also, the "function" operator is now used to specifically
     * link to a function instead of a method in the current class.
     *
     * <code>
     * class myclass
     * {
     *  // from inside the class definition, use "function conflict()" to refer to procedural function "conflict()"
     *    function conflict()
     *    {
     *    }
     * }
     *
     * function conflict()
     * {
     * }
     * </code>
     *
     * If classname:: is not present, and the see tag is in a documentation
     * block within a class, then the function uses the classname to
     * search for $expr as a function or variable within classname, or any of its parent classes.
     * given an $expr without '$', '::' or '()' getLink first searches for
     * classes, procedural pages, constants, global variables, and then searches for
     * methods and variables within the default class, and finally for any function
     *
     * @param string $expr expression to search for a link
     * @param string $package package to start searching in
     * @param array $packages list of all packages to search in
     * @return mixed getLink returns a descendant of {@link abstractLink} if it finds a link, otherwise it returns a string
     * @see getPageLink(), getDefineLink(), getVarLink(), getFunctionLink(), getClassLink()
     * @see pageLink, functionLink, defineLink, classLink, methodLink, varLink
     */
    function &getLink($expr, $package = false, $packages = false)
    {
        // is $expr a comma-delimited list?
        if (strpos($expr,','))
        {
            $a = explode(',',$expr);
            $b = array();
            for($i=0;$i<count($a);$i++)
            {
                // if so return each component with a link
                $b[] = Converter::getLink(trim($a[$i]));
            }
            return $b;
        }
        if (strpos($expr,'#'))
        {
            $a = explode('#',$expr);
            if (count($a) == 2)
            { // can have exactly 1 package override, otherwise it's ignored
                // feature 564991, link to php manual
                if ($a[0] == 'PHP_MANUAL') {
                    $s = 'http://www.php.net/'.$a[1];
                    return $s;
                }
                $s = &Converter::getLink($a[1],$a[0],array());
                return $s;
            }
        }
        $a = &$this->_getLink($expr, $package, $packages);
        return $a;
    }

    /**
     * @access private
     */
    function &_getLink($expr, $package = false, $packages = false)
    {
        if (!$package) $package = $this->package;
        //
        if (!isset($this->all_packages[$package])) return $expr;
        elseif (isset($packages[$package])) unset($packages[$package]);
        $links = &$this->links;
        $class = $this->class;
        if (strpos($expr,'function ') === 0)
        { // asking for a function, not a method
            if ($test = Converter::getFunctionLink(str_replace('function ','',str_replace('()','',$expr)), $package)) return $test;
            else return $expr;
        }
        if (strpos($expr,'global ') === 0)
        { // asking for a global variable
            if ($test = Converter::getGlobalLink(str_replace('global ','',$expr), $package)) return $test;
            else return $expr;
        }
        if (strpos($expr,'object ') === 0)
        { // asking for a class
            if ($test = Converter::getClassLink(str_replace('object ','',$expr), $package)) return $test;
            else return $expr;
        }
        if (strpos($expr,'constant ') === 0)
        { // asking for a class
            if ($test = Converter::getDefineLink(str_replace('constant ','',$expr), $package)) return $test;
            else return $expr;
        }
        // are we in a class?
        if ($class)
        {
            // is $expr simply a word? see if it is the class
            if (trim($expr) == $class)
            {
                if ($test = Converter::getClassLink(trim(str_replace('object ','',$expr)),$package)) return $test;
            }
            // if not, check to see if it is a method or variable of this class tree
            if (!strpos($expr,'::'))
            {
                // if get is neither get() nor $get, assume get is a function, add () to make get()
                if (strpos($expr,'$') !== 0 && !strpos($expr,'()')) //$get = $get.'()';
                {
                    if ($a = $this->getLinkMethod($expr,$class,$package)) return $a;
                    if ($a = $this->getLinkConst($expr,$class,$package)) return $a;
                    if ($a = $this->getLinkVar('$'.$expr,$class,$package)) return $a;
                }
                if (strpos($expr,'()')) if ($a = $this->getLinkMethod($expr,$class,$package)) return $a;
                if (is_numeric(strpos($expr,'$'))) if ($a = $this->getLinkVar($expr,$class,$package)) return $a;
            }
        }
        if ($test = Converter::getClassLink(trim(str_replace('object ','',$expr)),$package)) return $test;
        if ($test = Converter::getPageLink(trim($expr),$package)) return $test;
        if ($test = Converter::getDefineLink(trim($expr),$package)) return $test;
        if ($test = Converter::getGlobalLink(trim($expr),$package)) return $test;
//        if (strpos($expr,'.'))
        // package specified

        if (!is_array($packages))
        {
            $packages = $this->all_packages;
        }
        do
        {
            if (isset($packages[$package])) unset($packages[$package]);
            if ($test = Converter::getClassLink(str_replace('object ','',$expr),$package)) return $test;
            if ($test = Converter::getPageLink($expr,$package)) return $test;
            if ($test = Converter::getDefineLink($expr,$package)) return $test;
            if ($test = Converter::getGlobalLink($expr,$package)) return $test;
            // is $expr in class::method() or class::$variable format?
            if (strpos($expr,'function ') === 0)
            { // asking for a function, not a method
                if ($test = Converter::getFunctionLink(str_replace('function','',str_replace('()','',$expr)), $package)) return $test;
                else return $expr;
            }
            $test = $this->_getDoubleColon($expr, $package, $packages, $class, $links);
            if (!is_string($test)) return $test;
            if (strpos($test, 'parent::') === 0) return $test;
            // $expr does not have ::
            if (is_numeric(@strpos('$',$expr)))
            {
                // default to current class, whose name is contained in $this->render->parent
                if ($test = Converter::getVarLink($expr, $class, $package)) return $test;
            }
            // $expr is a function? (non-method)
            if (@strpos($expr,'()'))
            {
                // otherwise, see if it is a method
                if ($class)
                {
                    if ($test = Converter::getMethodLink(str_replace('()','',$expr), $class, $package)) return $test;
                }
                // extract the function name, use it to retrieve the file that the function is in
    //            $page = $this->func_page[str_replace('function ','',str_replace('()','',$expr))];
                // return the link
                if ($test = Converter::getFunctionLink(str_replace('function ','',str_replace('()','',$expr)), $package)) return $test;
            }
            // $expr is just a word.  First, test to see if it is a function of the current package
            if ($test = Converter::getFunctionLink(str_replace('function ','',str_replace('()','',$expr)), $package)) return $test;
            // try other packages
            // look in parent package first, if found
            if (isset($this->package_parents[$package]) && in_array($this->package_parents[$package], $packages))
            {
                $p1 = $package;
                $package = $this->package_parents[$package];
                if ($package)
                {
                    if (isset($packages[$package])) unset($packages[$package]);
                }
                continue;
            }
            // no parent package, so start with the first one that's left
            $package = @array_shift(@array_keys($packages));
            if ($package && isset($packages[$package]))
            {
                unset($packages[$package]);
            }
        } while (count($packages) || $package);
        $funcs = get_defined_functions();
        // feature 564991, link to php manual
        if (in_array(str_replace(array('(',')'),array('',''),$expr),$funcs['internal']))
        {
            $return = 'http://www.php.net/'.str_replace(array('(',')'),array('',''),$expr);
            return $return;
        }
        // no links found
        return $expr;
    }

    /**
     * Split up getLink to make it easier to debug
     * @access private
     */
    function _getDoubleColon(&$expr, &$package, &$packages, $class, $links)
    {
        if (@strpos($expr,'::'))
        {
            $class_method = explode('::',$expr);
            if ($class_method[0] == 'parent')
            {
                // can only have parent in the same package as the class!  subtle bug
                $package = $this->package;
                $packages = array();
                $cl = $this->classes->getClassByPackage($class,$package);
                if (!$cl)
                { // this is possible if an example file has parent::method()
                    return $expr;
                }
                $par = $cl->getParent($this);
                $phpparent = false;
                if (is_object($par))
                {
                    $package = $par->docblock->package;
                    $phpparent = $par->getName();
                } else
                {
                    addWarning(PDERROR_CLASS_PARENT_NOT_FOUND,$class,$package,$class_method[1]);
                    return $expr;
                }
                if ($phpparent) $class_method[0] = $phpparent;
            }
            if (strpos($class_method[1],'()'))
            {
                // strip everything but the function name, return a link
                if ($test = Converter::getMethodLink(str_replace('()','',$class_method[1]), $class_method[0], $package)) return $test;
            }
            if ($test = Converter::getVarLink($class_method[1], $class_method[0], $package)) return $test;
            if ($test = Converter::getConstLink($class_method[1], $class_method[0], $package)) return $test;
        }
        return $expr;
    }

    /**
     * cycle through parent classes to retrieve a link to a method
     * do not use or override, used by getLink
     * @access private
     */
    function &getLinkMethod($expr, $class, $package)
    {
        $links = &$this->links;
        do
        {
            // is $expr in class::method() or class::$variable format?
            if (@strpos($expr,'::'))
            {
                $class_method = explode('::',$expr);
                if ($class_method[0] == 'parent')
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                    $par = $cl->getParent($this);
                    $phpparent = false;
                    if (is_object($par))
                    {
                        $package = $par->docblock->package;
                        $phpparent = $par->getName();
                    } else addWarning(PDERROR_CLASSPARENT_NOTFOUND,$class,$package,$class_method[1]);
                    if ($phpparent) $class_method[0] = $phpparent;
                } else
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                }
                if (strpos($class_method[1],'()'))
                {
                    // strip everything but the function name, return a link
                    if ($test = Converter::getMethodLink(str_replace('function ','',str_replace('()','',$class_method[1])), $class_method[0], $package)) return $test;
                }
            }
            if ($test = Converter::getMethodLink(str_replace('()','',$expr), $class, $package)) return $test;
            $cl = $this->classes->getClassByPackage($class,$package);
            if ($cl)
            {
                $par = $cl->getParent($this);
                if (is_object($par))
                {
                    $package = $par->docblock->package;
                    $class = $par->getName();
                } else $class = $par;
            } else $class = false;
        } while ($class);
        // no links found
        $flag = false;
        return $flag;
    }

    /**
     * cycle through parent classes to retrieve a link to a var
     * do not use or override, used by getLink
     * @access private
     */
    function &getLinkVar($expr, $class, $package)
    {
        $links = &$this->links;
        do
        {
            // is $expr in class::method() or class::$variable format?
            if (@strpos($expr,'::'))
            {
                $class_method = explode('::',$expr);
                if ($class_method[0] == 'parent')
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                    $phpparent = false;
                    $par = $cl->getParent($this);
                    if (is_object($par))
                    {
                        $package = $par->docblock->package;
                        $phpparent = $par->getName();
                    } else addWarning(PDERROR_CLASSPARENT_NOTFOUND,$class,$package,$class_method[1]);
                    if ($phpparent) $class_method[0] = $phpparent;
                } else
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                }
                if ($test = Converter::getVarLink($class_method[1], $class_method[0], $package)) return $test;
                if ($test = Converter::getVarLink('$'.$class_method[1], $class_method[0], $package)) return $test;
            }
            if ($test = Converter::getVarLink($expr, $class, $package)) return $test;
            if ($test = Converter::getVarLink('$'.$expr, $class, $package)) return $test;
            $cl = $this->classes->getClassByPackage($class,$package);
            if ($cl)
            {
                $par = $cl->getParent($this);
                if (is_object($par))
                {
                    $package = $par->docblock->package;
                    $class = $par->getName();
                } else $class = $par;
            } else $class = false;
        } while ($class);
        // no links found
        $class = false;
        return $class;
    }

    /**
     * cycle through parent classes to retrieve a link to a class constant
     * do not use or override, used by getLink
     * @access private
     * @since 1.2.4
     */
    function &getLinkConst($expr, $class, $package)
    {
        $links = &$this->links;
        do
        {
            // is $expr in class::method() or class::$variable format?
            if (@strpos($expr,'::'))
            {
                $class_method = explode('::',$expr);
                if ($class_method[0] == 'parent')
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                    $phpparent = false;
                    $par = $cl->getParent($this);
                    if (is_object($par))
                    {
                        $package = $par->docblock->package;
                        $phpparent = $par->getName();
                    } else addWarning(PDERROR_CLASSPARENT_NOTFOUND,$class,$package,$class_method[1]);
                    if ($phpparent) $class_method[0] = $phpparent;
                } else
                {
                    $cl = $this->classes->getClassByPackage($class,$package);
                }
                if ($test = Converter::getConstLink($class_method[1], $class_method[0], $package)) return $test;
            }
            if ($test = Converter::getConstLink($expr, $class, $package)) return $test;
            $cl = $this->classes->getClassByPackage($class,$package);
            if ($cl)
            {
                $par = $cl->getParent($this);
                if (is_object($par))
                {
                    $package = $par->docblock->package;
                    $class = $par->getName();
                } else $class = $par;
            } else $class = false;
        } while ($class);
        // no links found
        $flag = false;
        return $flag;
    }

    /**
     * take URL $link and text $text and return a link in the format needed for the Converter
     * @param string URL
     * @param string text to display
     * @return string link to $link
     * @abstract
     */
    function returnLink($link,$text)
    {
    }

    /**
     * take {@link abstractLink} descendant and text $eltext and return a link
     * in the format needed for the Converter
     * @param abstractLink
     * @param string
     * @return string link to $element
     * @abstract
     */
    function returnSee(&$link, $eltext = false)
    {
    }

    /**
     * take {@link abstractLink} descendant and text $eltext and return a
     * unique ID in the format needed for the Converter
     * @param abstractLink
     * @return string unique identifier of $element
     * @abstract
     */
    function getId(&$link)
    {
    }

    /**
     * Convert README/INSTALL/CHANGELOG file contents to output format
     * @param README|INSTALL|CHANGELOG
     * @param string contents of the file
     * @abstract
     */
    function Convert_RIC($name, $contents)
    {
    }

    /**
     * Convert all elements to output format
     *
     * This will call ConvertXxx where Xxx is {@link ucfirst}($element->type).
     * It is expected that a child converter defines a handler for every
     * element type, even if that handler does nothing.  phpDocumentor will
     * terminate with an error if a handler doesn't exist.
     * {@internal
     * Since 1.2.0 beta 3, this function has been moved from child converters
     * to the parent, because it doesn't really make sense to put it in the
     * child converter, and we can add error handling.
     *
     * {@source}}}
     * @throws {@link PDERROR_NO_CONVERT_HANDLER}
     * @param mixed {@link parserElement} descendant or {@link parserPackagePage} or {@link parserData}
     */
    function Convert(&$element)
    {
        $handler = 'convert'.ucfirst($element->type);
        if (method_exists($this,$handler))
        {
            $this->$handler($element);
        } else
        {
            addErrorDie(PDERROR_NO_CONVERTER_HANDLER,$element->type,$handler,phpDocumentor_get_class($this));
        }
    }
    /**#@+
     * Conversion Handlers
     *
     * All of the convert* handlers set up template variables for the Smarty
     * template.{@internal  In addition, the {@link newSmarty()} method is
     * called to retrieve the global Smarty template}}
     */
    /**
     * Default Tutorial Handler
     *
     * Sets up the tutorial template, and its prev/next/parent links
     * {@internal
     * Retrieves the title using {@link parserTutorial::getTitle()} and uses the
     * {@link parserTutorial::prev, parserTutorial::next, parserTutorial::parent}
     * links to set up those links.}}
     * @param parserTutorial
     */
    function &convertTutorial(&$element)
    {
        $this->package = $element->package;
        $this->subpackage = $element->subpackage;
        $x = $element->Convert($this);
        $template = &$this->newSmarty();
        $template->assign('contents',$x);
        $template->assign('title',$element->getTitle($this));
        $template->assign('nav',$element->parent || $element->prev || $element->next);
        if ($element->parent)
        {
            $template->assign('up',$this->getId($element->parent));
            $template->assign('uptitle',$element->parent->title);
        }
        if ($element->prev)
        {
            $template->assign('prev',$this->getId($element->prev));
            $template->assign('prevtitle',$element->prev->title);
        }
        if ($element->next)
        {
            $template->assign('next',$this->getId($element->next));
            $template->assign('nexttitle',$element->next->title);
        }
        return $template;
    }
    /**
     * Default Class Handler
     *
     * Sets up the class template.
     * {@internal special methods
     * {@link generateChildClassList(), generateFormattedClassTree()},
     * {@link getFormattedConflicts, getFormattedInheritedMethods},
     * and {@link getFormattedInheritedVars} are called to complete vital
     * template setup.}}
     */
    function convertClass(&$element)
    {
        $this->class = $element->getName();
        $this->class_data = &$this->newSmarty();
        $this->class_data->assign("class_name",$element->getName());
        $this->class_data->assign("vars",array());
        $this->class_data->assign("methods",array());
        $this->class_data->assign("consts",array());
        $this->class_data->assign("is_interface", $element->isInterface());
        $this->class_data->assign("implements", $this->getFormattedImplements($element));
        $this->class_data->assign("package",$element->docblock->package);
        $this->class_data->assign("line_number",$element->getLineNumber());
        $this->class_data->assign("source_location",$element->getSourceLocation($this));
				$this->class_data->assign("page_link",$this->getCurrentPageLink());
        $docblock = $this->prepareDocBlock($element, false);
        $this->class_data->assign("sdesc",$docblock['sdesc']);
        $this->class_data->assign("desc",$docblock['desc']);
				$this->class_data->assign("access", $docblock['access']);
				$this->class_data->assign("abstract", $docblock['abstract']);
        $this->class_data->assign("tags",$docblock['tags']);
        $this->class_data->assign("api_tags",$docblock['api_tags']);
        $this->class_data->assign("info_tags",$docblock['info_tags']);
        $this->class_data->assign("utags",$docblock['utags']);
        if ($this->hasSourceCode($element->getPath())) {
        $this->class_data->assign("class_slink",$this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true));
        }

        else
        $this->class_data->assign("class_slink",false);
        $this->class_data->assign("children", $this->generateChildClassList($element));
        $this->class_data->assign("class_tree", $this->generateFormattedClassTree($element));
        $this->class_data->assign("conflicts", $this->getFormattedConflicts($element,"classes"));
        $inherited_methods = $this->getFormattedInheritedMethods($element);
        if (!empty($inherited_methods))
        {
            $this->class_data->assign("imethods",$inherited_methods);
        } else
        {
            $this->class_data->assign("imethods",false);
        }
        $inherited_vars = $this->getFormattedInheritedVars($element);
        if (!empty($inherited_vars))
        {
            $this->class_data->assign("ivars",$inherited_vars);
        } else
        {
            $this->class_data->assign("ivars",false);
        }
        $inherited_consts = $this->getFormattedInheritedConsts($element);
        if (!empty($inherited_consts))
        {
            $this->class_data->assign("iconsts",$inherited_consts);
        } else
        {
            $this->class_data->assign("iconsts",false);
        }
    }


    /**
     * Converts method for template output
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * @param parserMethod
     */
    function convertMethod(&$element, $additions = array())
    {
        $fname = $element->getName();
        $docblock = $this->prepareDocBlock($element);
        $returntype = 'void';
        if ($element->isConstructor) $returntype = $element->class;
        if ($element->docblock->return)
        {
            $a = $element->docblock->return->Convert($this);
            $returntype = $element->docblock->return->converted_returnType;
        }
        $params = $param_i = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $params[] = $param_i[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }

        if ($element->docblock->hasaccess) {
            $acc = $docblock['access'];
        } else {
            $acc = 'public';
        }

        if ($this->hasSourceCode($element->getPath()))
        $additions["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->class_data->append('methods',array_merge(
                                             array('sdesc' => $docblock['sdesc'],
                                                   'desc' => $docblock['desc'],
                                                   'static' => $docblock['static'],
                                                   'abstract' => $docblock['abstract'],
                                                   'tags' => $docblock['tags'],
                                                   'api_tags' => $docblock['api_tags'],
                                                   'see_tags' => $docblock['see_tags'],
                                                   'info_tags_sorted' => $docblock['info_tags_sorted'],
                                                   'info_tags' => $docblock['info_tags'],
                                                   'utags' => $docblock['utags'],
                                                   'constructor' => $element->isConstructor,
                                                   'access' => $acc,
                                                   'function_name'     => $fname,
                                                   'function_return'    => $returntype,
                                                   'function_call'     => $element->getFunctionCall(),
                                                   'ifunction_call'     => $element->getIntricateFunctionCall($this, $param_i),
                                                   'descmethod'    => $this->getFormattedDescMethods($element),
                                                   'method_overrides'    => $this->getFormattedOverrides($element),
                                                   'method_implements'    => $this->getFormattedMethodImplements($element),
                                                   'line_number' => $element->getLineNumber(),
                                                   'id' => $this->getId($element),
                                                   'params' => $params),
                                             $additions));
    }

    /**
     * Converts class variables for template output.
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * @param parserVar
     */
    function convertVar(&$element, $additions = array())
    {
        $docblock = $this->prepareDocBlock($element);
        $b = 'mixed';

        if ($element->docblock->hasaccess)
            $acc = $element->docblock->tags['access'][0]->value;
        else
            $acc = 'public';

        if ($element->docblock->var)
        {
            $b = $element->docblock->var->converted_returnType;
        }
        if ($this->hasSourceCode($element->getPath()))
        $additions["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->class_data->append('vars',array_merge(
                                          array('sdesc' => $docblock['sdesc'],
                                                'desc' => $docblock['desc'],
                                                'static' => $docblock['static'],
                                                'abstract' => $docblock['abstract'],
                                                'utags' => $docblock['utags'],
                                                'tags' => $docblock['tags'],
                                                'api_tags' => $docblock['api_tags'],
                                                'info_tags' => $docblock['info_tags'],
                                                'var_name' => $element->getName(),
                                                'has_default' => strlen($element->getValue()),
                                                'var_default' => $this->postProcess($element->getValue()),
                                                'var_type' => $b,
                                                'access' => $acc,
                                                'line_number' => $element->getLineNumber(),
                                                'descvar'    => $this->getFormattedDescVars($element),
                                                'var_overrides' => $this->getFormattedOverrides($element),
                                                'id' => $this->getId($element)),
                                          $additions));
    }

    /**
     * Converts class constants for template output.
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * @param parserConst
     */
    function convertConst(&$element, $additions = array())
    {
        $docblock = $this->prepareDocBlock($element);

        if ($element->docblock->hasaccess)
        	$acc = $element->docblock->tags['access'][0]->value;
        else
        	$acc = 'public';

        if ($this->hasSourceCode($element->getPath()))
        $additions["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->class_data->append('consts',array_merge(
                                          array('sdesc' => $docblock['sdesc'],
                                                'desc' => $docblock['desc'],
                                                'access' => $docblock['access'],
                                                'abstract' => $docblock['abstract'],
                                                'utags' => $docblock['utags'],
                                                'tags' => $docblock['tags'],
                                                'api_tags' => $docblock['api_tags'],
                                                'info_tags' => $docblock['info_tags'],
                                                'const_name' => $element->getName(),
                                                'const_value' => $this->postProcess($element->getValue()),
                                                'access' => $acc,
                                                'line_number' => $element->getLineNumber(),
                                                'id' => $this->getId($element)),
                                          $additions));
    }

    /**
     * Default Page Handler
     *
     * {@internal In addition to setting up the smarty template with {@link newSmarty()},
     * this class uses {@link getSourceLocation()} and {@link getClassesOnPage()}
     * to set template variables.  Also used is {@link getPageName()}, to get
     * a Converter-specific name for the page.}}
     * @param parserPage
     */
    function convertPage(&$element)
    {
        $this->page_data = &$this->newSmarty(true);
        $this->page = $this->getPageName($element->parent);
        $this->path = $element->parent->getPath();
        $this->curpage = &$element->parent;
        $this->page_data->assign("source_location",$element->parent->getSourceLocation($this));
        $this->page_data->assign("functions",array());
        $this->page_data->assign("includes",array());
        $this->page_data->assign("defines",array());
        $this->page_data->assign("globals",array());
        $this->page_data->assign("classes",$this->getClassesOnPage($element));
        $this->page_data->assign("hasclasses",$element->hasClasses());
        $this->page_data->assign("hasinterfaces",$element->hasInterfaces());
        $this->page_data->assign("name", $element->parent->getFile());
        if ($t = $element->getTutorial())
        {
            $this->page_data->assign("tutorial",$this->returnSee($t));
        } else
        {
            $this->page_data->assign("tutorial",false);
        }
        if ($element->docblock)
        {
            $docblock = $this->prepareDocBlock($element, false);
            $this->page_data->assign("sdesc",$docblock['sdesc']);
            $this->page_data->assign("desc",$docblock['desc']);
            $this->page_data->assign("tags",$docblock['tags']);
            $this->page_data->assign("api_tags",$docblock['api_tags']);
            $this->page_data->assign("info_tags",$docblock['info_tags']);
            $this->page_data->assign("utags",$docblock['utags']);
        }
    }

    /**
     * Converts global variables for template output
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * {@internal
     * In addition to using {@link prepareDocBlock()}, this method also
     * uses utility functions {@link getGlobalValue(), getFormattedConflicts()}}}
     * @param parserGlobal
     * @uses postProcess() on global_value template value, makes it displayable
     * @param array any additional template variables should be in this array
     */
    function convertGlobal(&$element, $addition = array())
    {
        $docblock = $this->prepareDocBlock($element);
        $value = $this->getGlobalValue($element->getValue());
        if ($this->hasSourceCode($element->getPath()))
        $addition["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->page_data->append('globals',array_merge(
                                            array('sdesc' => $docblock['sdesc'],
                                                  'desc' => $docblock['desc'],
                                                  'tags' => $docblock['tags'],
                                                  'api_tags' => $docblock['api_tags'],
                                                  'info_tags' => $docblock['info_tags'],
                                                  'utags' => $docblock['utags'],
                                                  'global_name'     => $element->getName(),
                                                  'global_type' => $element->getDataType($this),
                                                  'global_value'    => $value,
                                                  'line_number' => $element->getLineNumber(),
                                                  'global_conflicts'    => $this->getFormattedConflicts($element,"global variables"),
                                                  'id' => $this->getId($element)),
                                            $addition));
    }

    /**
     * Converts defines for template output
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * {@internal
     * In addition to using {@link prepareDocBlock()}, this method also
     * uses utility functions {@link getGlobalValue(), getFormattedConflicts()}}}
     * @param parserDefine
     * @uses postProcess() on define_value template value, makes it displayable
     * @param array any additional template variables should be in this array
     */
    function convertDefine(&$element, $addition = array())
    {
        $docblock = $this->prepareDocBlock($element);
        if ($this->hasSourceCode($element->getPath()))
        $addition["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->page_data->append('defines',array_merge(
                                            array('sdesc' => $docblock['sdesc'],
                                                  'desc' => $docblock['desc'],
                                                  'tags' => $docblock['tags'],
                                                  'api_tags' => $docblock['api_tags'],
                                                  'info_tags' => $docblock['info_tags'],
                                                  'utags' => $docblock['utags'],
                                                  'define_name'     => $element->getName(),
                                                  'line_number' => $element->getLineNumber(),
                                                  'define_value'    => $this->postProcess($element->getValue()),
                                                  'define_conflicts'    => $this->getFormattedConflicts($element,"defines"),
                                                  'id' => $this->getId($element)),
                                            $addition));
    }


    /**
     * Converts includes for template output
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * @see prepareDocBlock()
     * @param parserInclude
     */
    function convertInclude(&$element, $addition = array())
    {
        $docblock = $this->prepareDocBlock($element);
        $per = $this->getIncludeValue($element->getValue(), $element->getPath());

        if ($this->hasSourceCode($element->getPath()))
        $addition["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->page_data->append('includes',array_merge(
                                             array('sdesc' => $docblock['sdesc'],
                                                   'desc' => $docblock['desc'],
                                                   'tags' => $docblock['tags'],
                                                   'api_tags' => $docblock['api_tags'],
                                                   'info_tags' => $docblock['info_tags'],
                                                   'utags' => $docblock['utags'],
                                                   'include_name'     => $element->getName(),
                                                   'line_number' => $element->getLineNumber(),
                                                   'include_value'    => $per),
                                             $addition));
    }

    /**
     * Converts function for template output
     *
     * This function must be called by a child converter with any extra
     * template variables needed in the parameter $addition
     * @see prepareDocBlock()
     * @param parserFunction
     */
    function convertFunction(&$element, $addition = array())
    {
        $docblock = $this->prepareDocBlock($element);
        $fname = $element->getName();
        $params = $param_i = array();
        if (count($element->docblock->params))
        foreach($element->docblock->params as $param => $val)
        {
            $a = $val->Convert($this);
            $params[] = $param_i[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }
        $returntype = 'void';
        if ($element->docblock->return)
        {
            $a = $element->docblock->return->Convert($this);
            $returntype = $element->docblock->return->converted_returnType;
        }

        if ($this->hasSourceCode($element->getPath()))
        $addition["slink"] = $this->getSourceAnchor($element->getPath(),$element->getLineNumber(),$element->getLineNumber(),true);
        $this->page_data->append('functions',array_merge(
                                              array('sdesc' => $docblock['sdesc'],
                                                    'desc' => $docblock['desc'],
                                                    'tags' => $docblock['tags'],
                                                    'api_tags' => $docblock['api_tags'],
                                                    'info_tags' => $docblock['info_tags'],
                                                    'utags' => $docblock['utags'],
                                                    'function_name'     => $fname,
                                                    'function_return'    => $returntype,
                                                    'function_conflicts'    => $this->getFormattedConflicts($element,"functions"),
                                                    'ifunction_call'     => $element->getIntricateFunctionCall($this, $param_i),
                                                    'function_call'     => $element->getFunctionCall(),
                                                    'line_number' => $element->getLineNumber(),
                                                    'id' => $this->getId($element),
                                                    'params' => $params),
                                              $addition));
    }
    /**#@-*/

    /**
     * convert the element's DocBlock for output
     *
     * This function converts all tags and descriptions for output
     * @param mixed any descendant of {@link parserElement}, or {@link parserData}
     * @param array used to translate tagnames into other tags
     * @param boolean set to false for pages and classes, the only elements allowed to specify @package
     * @return array
     *
     * Format:
     * <pre>
     * array('sdesc' => DocBlock summary
     *       'desc' => DocBlock detailed description
     *       'tags' => array('keyword' => tagname, 'data' => tag description)
     *                 known tags
     *       'api_tags' => array('keyword' => tagname, 'data' => tag description)
     *                 known api documentation tags
     *       'info_tags' => array('keyword' => tagname, 'data' => tag description)
     *                 known informational tags
     *     [ 'utags' => array('keyword' => tagname, 'data' => tag description
     *                 unknown tags ]
     *     [ 'vartype' => type from @var/@return tag ]
     *     [ 'var_descrip' => description from @var/@return tag ]
     *      )
     * </pre>
     */
    function prepareDocBlock(&$element, $names = array(),$nopackage = true)
    {
        $tagses = $element->docblock->listTags();
        $tags = $ret = $api_tags = $info_tags = array();
        $api_tags_arr = array("abstract", "access", "deprecated", "example", "filesource",
                             "global", "internal", "name", "return", "see",
                             "staticvar", "usedby", "uses", "var");
        if (!$nopackage)
        {
            $tags[] = array('keyword' => 'package','data' => $element->docblock->package);
            if (!empty($element->docblock->subpackage)) $tags[] = array('keyword' => 'subpackage','data' => $element->docblock->subpackage);
        }
        if ($element->docblock->var)
        {
            $a = $element->docblock->var->Convert($this);
            $ret['vartype'] = $element->docblock->var->converted_returnType;
            if (!empty($a))
            {
                $tags[] = array('keyword' => 'var', 'data' => $a);
                $ret["var_descrip"] = $a;
            }
        }
        if ($element->docblock->return)
        {
            $a = $element->docblock->return->Convert($this);
            $ret['vartype'] = $element->docblock->return->converted_returnType;
            if (!empty($a))
            {
                $tags[] = $api_tags[] = array('keyword' => 'return', 'data' => $a);
                $ret["var_descrip"] = $a;
            }
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
            $tags[] = $api_tags[] = array('keyword' => 'global','data' => $val[0].' '.$global.': '.$val[1]->Convert($this));
        }
        if ($element->docblock->statics)
        foreach($element->docblock->statics as $static => $val)
        {
            $a = $val->Convert($this);
            $tags[] = $api_tags[] = array('keyword' => 'staticvar','data' => $val->converted_returnType.' '.$static.': '.$a);
        }
        $info_tags_sorted = array();
        $ret['static'] = false;
        foreach($tagses as $tag)
        {
            if (isset($names[$tag->keyword])) $tag->keyword = $names[$tag->keyword];
            if ($tag->keyword == 'static') {
                $ret['static'] = true;
                continue;
            }
            if ($tag->keyword)
                $tags[] = array("keyword" => $tag->keyword,"data" => $tag->Convert($this));
            if (in_array($tag->keyword, $api_tags_arr)) {
                $api_tags[] = array("keyword" => $tag->keyword,"data" => $tag->Convert($this));
            } else {
                $info_tags[] = array("keyword" => $tag->keyword,"data" => $tag->Convert($this));
                @list( $className, $desc ) = explode( " ", $tag->Convert($this), 2 );
                $info_tags_sorted[ $tag->keyword ][] = array( 'keyword' => $className, 'data' => $desc );
            }
        }
        $utags = array();
        foreach($element->docblock->unknown_tags as $keyword => $tag)
        {
            foreach($tag as $t)
            $utags[] = array('keyword' => $keyword, 'data' => $t->Convert($this));
        }
        $ret['abstract'] = false;
        $ret['access'] = 'public';
        $see_tags = array();
        foreach($tags as $tag)
        {
            if ($tag['keyword'] == 'access') {
                $ret['access'] = $tag['data'];
            }
            if ($tag['keyword'] == 'abstract') {
                $ret['abstract'] = true;
            }
            if ($tag['keyword'] == 'see' || $tag['keyword'] == 'uses' ||
                  $tag['keyword'] == 'usedby') {
                $see_tags[] = $tag['data'];
            }
        }
        $ret['sdesc'] = $element->docblock->getSDesc($this);
        $ret['desc'] = $element->docblock->getDesc($this);
        $ret['tags'] = $tags;
        $ret['see_tags'] = $see_tags;
        $ret['info_tags_sorted'] = $info_tags_sorted;
        $ret['api_tags'] = $api_tags;
        $ret['info_tags'] = $info_tags;
        $ret['utags'] = $utags;
        return $ret;
    }

    /**
     * gets a list of all classes declared on a procedural page represented by
     * $element, a {@link parserData} class
     * @param parserData &$element
     * @return array links to each classes documentation
     *
     * Format:
     * <pre>
     * array('name' => class name,
     *       'sdesc' => summary of the class
     *       'link' => link to the class's documentation)
     * </pre>
     */
    function getClassesOnPage(&$element)
    {
        global $_phpDocumentor_setting;
        $a = $element->getClasses($this);
        $classes = array();
        foreach($a as $package => $clas)
        {
            if (!empty($_phpDocumentor_setting['packageoutput']))
            {
                $packages = explode(',',$_phpDocumentor_setting['packageoutput']);
                if (!in_array($package, $packages)) continue;
            }
            for($i=0; $i<count($clas); $i++)
            {
                if ($this->parseprivate || ! ($clas[$i]->docblock && $clas[$i]->docblock->hasaccess && $clas[$i]->docblock->tags['access'][0]->value == 'private'))
                {
                    $sdesc = '';
                    $r = array();
                    $sdesc = $clas[$i]->docblock->getSDesc($this);
                    if ($clas[$i]->docblock->hasaccess)
											$r['access'] = $clas[$i]->docblock->tags['access'][0]->value;
                    else
											$r['access'] = 'public';
										if (isset ($clas[$i]->docblock->tags['abstract']))
											$r['abstract'] = TRUE;
										else
											$r['abstract'] = FALSE;
                    $r['name'] = $clas[$i]->getName();
                    $r['sdesc'] = $sdesc;
                    $r['link'] = $this->getClassLink($clas[$i]->getName(),$package,$clas[$i]->getPath());
                    $classes[] = $r;
                }
            }
        }
        return $classes;
    }

    /**
     * returns an array containing the class inheritance tree from the root
     * object to the class.
     *
     * This method must be overridden, or phpDocumentor will halt with a fatal
     * error
     * @return string Converter-specific class tree for an individual class
     * @param parserClass    class variable
     * @abstract
     */

    function generateFormattedClassTree($class)
    {
        addErrorDie(PDERROR_CONVERTER_OVR_GFCT,phpDocumentor_get_class($this));
    }

    /**
     * returns an array containing the class inheritance tree from the root
     * object to the class.
     *
     * This method must be overridden, or phpDocumentor will halt with a fatal
     * error
     * @return string Converter-specific class tree for an individual class
     * @param parserClass    class variable
     * @abstract
     */

    function getFormattedImplements($el)
    {
        $ret = array();
        foreach ($el->getImplements() as $interface)
        {
            $link = $this->getLink($interface);
            if ($link && is_object($link)) {
                $ret[] = $this->returnSee($link);
            } else {
                if (class_exists('ReflectionClass')) {
                    if (interface_exists($interface)) {
                        $inter = new ReflectionClass($interface);
                        if ($inter->isInternal()) {
                            $ret[] = $interface . ' (internal interface)';
                        } else {
                            $ret[] = $interface;
                        }
                    }
                } else {
                    $ret[] = $interface;
                }
            }
        }
        return $ret;
    }

    /**
     * @param mixed {@link parserClass, parserFunction, parserDefine} or
     * {@link parserGlobal}
     * @param string type to display.  either 'class','function','define'
     *               or 'global variable'
     * @return array links to conflicting elements, or empty array
     * @uses parserClass::getConflicts()
     * @uses parserFunction::getConflicts()
     * @uses parserDefine::getConflicts()
     * @uses parserGlobal::getConflicts()
     */
    function getFormattedConflicts(&$element,$type)
    {
        $conflicts = $element->getConflicts($this);
        $r = array();
        if (!$conflicts) return false;
        foreach($conflicts as $package => $class)
        {
            $r[] = $class->getLink($this,$class->docblock->package);
        }
        if (!empty($r)) $r = array('conflicttype' => $type, 'conflicts' => $r);
        return $r;
    }

    /**
     * Get a list of methods in child classes that override this method
     * @return array empty array or array(array('link'=>link to method,
     * 'sdesc'=>short description of the method),...)
     * @uses parserMethod::getOverridingMethods()
     * @param parserMethod
     */
    function getFormattedDescMethods(&$element)
    {
        $meths = $element->getOverridingMethods($this);
        $r = array();
        for($i=0; $i<count($meths); $i++)
        {
            $ms = array();
            $ms['link'] = $meths[$i]->getLink($this);
            $ms['sdesc'] = $meths[$i]->docblock->getSDesc($this);
            $r[] = $ms;
        }
        return $r;
    }

    /**
     * Get a list of vars in child classes that override this var
     * @return array empty array or array('link'=>link to var,
     * 'sdesc'=>short description of the method
     * @uses parserVar::getOverridingVars()
     * @param parserVar
     */
    function getFormattedDescVars(&$element)
    {
        $vars = $element->getOverridingVars($this);
        $r = array();
        for($i=0; $i<count($vars); $i++)
        {
            $vs = array();
            $vs['link'] = $vars[$i]->getLink($this);
            $vs['sdesc'] = $vars[$i]->docblock->getSDesc($this);
            $r[] = $vs;
        }
        return $r;
    }

    /**
     * Get the method this method overrides, if any
     * @return array|false array('link'=>link to overridden method,
     * 'sdesc'=>short description
     * @see parserMethod::getOverrides()
     * @param parserMethod
     */
    function getFormattedOverrides(&$element)
    {
        $ovr = $element->getOverrides($this);
        if (!$ovr) return false;
        $sdesc = $ovr->docblock->getSDesc($this);
        $name = method_exists($ovr, 'getFunctionCall') ? $ovr->getFunctionCall() : $ovr->getName();
        $link = ($link = $ovr->getLink($this)) ? $link : $ovr->getClass() . '::' . $name;
        return array('link' => $link,'sdesc' => $sdesc);
    }

    /**
     * Get the method this method(s) implemented from an interface, if any
     * @return array|false array('link'=>link to implemented method,
     * 'sdesc'=>short description
     * @uses parserMethod::getImplements()
     * @param parserMethod
     */
    function getFormattedMethodImplements(&$element)
    {
        $ovr = $element->getImplements($this);
        if (!$ovr) return false;
        $ret = array();
        foreach ($ovr as $impl) {
            $sdesc = $impl->docblock->getSDesc($this);
            $name = $impl->getName();
            $link = ($link = $impl->getLink($this)) ? $link : $impl->getClass() . '::' . $name;
            $ret[] = array('link' => $link,'sdesc' => $sdesc);
        }
        return $ret;
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
                $lt['link'] = $kids[$i]->getLink($this);
                $lt['sdesc'] = $kids[$i]->docblock->getSDesc($this);

								if ($kids[$i]->docblock->hasaccess)
									$lt['access'] = $kids[$i]->docblock->tags['access'][0]->value;
								else
									$lt['access'] = 'public';

								$lt['abstract'] = isset ($kids[$i]->docblock->tags['abstract'][0]);

                $list[] = $lt;
            }
        } else return false;
        return $list;
    }

    /**
     * Return template-enabled list of inherited variables
     *
     * uses parserVar helper function getInheritedVars and generates a
     * template-enabled list using getClassLink()
     * @param parserVar $child class var
     * @see getClassLink(), parserVar::getInheritedVars()
     * @return array Format:
     * <pre>
     * array(
     *   array('parent_class' => link to parent class's documentation,
     *         'ivars' =>
     *            array(
     *              array('name' => inherited variable name,
     *                    'link' => link to inherited variable's documentation,
     *                    'default' => default value of inherited variable,
     *                    'sdesc' => summary of inherited variable),
     *              ...),
     *   ...)
     * </pre>
     */

    function getFormattedInheritedVars($child)
    {
        $package = $child->docblock->package;
        $subpackage = $child->docblock->subpackage;
        $ivars = $child->getInheritedVars($this);
        $results = array();
        if (!count($ivars)) return $results;
        foreach($ivars as $parent => $vars)
        {
            $file = $vars['file'];
            $vars = $vars['vars'];
            $par = $this->classes->getClass($parent,$file);
            if ($par) {
                $package = $par->docblock->package;
            }
            usort($vars,array($this,"sortVar"));
            $result['parent_class'] = $this->getClassLink($parent, $package);
            if (!$result['parent_class']) {
                $result['parent_class'] = $parent . ' (Internal Class)';
            }
            foreach($vars as $var)
            {
                $info = array();

                if ($var->docblock->hasaccess) {
                    $info['access'] = $var->docblock->tags['access'][0]->value;
                } else {
                    $info['access'] = 'public';
                }

                $info['abstract'] = isset ($var->docblock->tags['abstract'][0]);

                $info['name'] = $var->getName();
                $info['link'] = $var->getLink($this);
                if (!$info['link']) {
                    $info['link'] = $info['name'];
                }
                $info['default'] = $this->postProcess($var->getValue());
                if ($var->docblock)
                $info['sdesc'] = $var->docblock->getSDesc($this);
                $result["ivars"][] = $info;
            }
            $results[] = $result;
            $result = array();
        }
        return $results;
    }

    /**
     * Return template-enabled list of inherited methods
     *
     * uses parserMethod helper function getInheritedMethods and generates a
     * template-enabled list using getClassLink()
     * @param parserMethod $child class method
     * @see getClassLink(), parserMethod::getInheritedMethods()
     * @return array Format:
     * <pre>
     * array(
     *   array('parent_class' => link to parent class's documentation,
     *         'ivars' =>
     *            array(
     *              array('name' => inherited variable name,
     *                    'link' => link to inherited variable's documentation,
     *                    'function_call' => {@link parserMethod::getIntricateFunctionCall()}
     *                                       returned array,
     *                    'sdesc' => summary of inherited variable),
     *              ...),
     *   ...)
     * </pre>
     */

    function getFormattedInheritedMethods($child)
    {
        $package = $child->docblock->package;
        $subpackage = $child->docblock->subpackage;
        $imethods = $child->getInheritedMethods($this);
        $results = array();
        if (!count($imethods)) return $results;
        foreach($imethods as $parent => $methods)
        {
            $file = $methods['file'];
            $methods = $methods['methods'];
            $par = $this->classes->getClass($parent,$file);
            if ($par) {
                $package = $par->docblock->package;
            }
            usort($methods,array($this,"sortMethod"));
            $result['parent_class'] = $this->getClassLink($parent,$package);
            if (!$result['parent_class']) {
                $result['parent_class'] = $parent . ' (Internal Class)';
            }
            foreach($methods as $method)
            {
                $info = array();

                if ($method->docblock->hasaccess) {
                    $info['access'] = $method->docblock->tags['access'][0]->value;
                } else {
                    $info['access'] = 'public';
                }
                $info['abstract'] = isset ($method->docblock->tags['abstract'][0]);

                if ($method->isConstructor) $info['constructor'] = 1;
                $returntype = 'void';
                if ($method->isConstructor) {
                    $returntype = $method->getClass();
                }
                if ($method->docblock->return) {
                    $a = $method->docblock->return->Convert($this);
                    $returntype = $method->docblock->return->converted_returnType;
                }
                $info['function_return'] = $returntype;
                $info['static'] = isset ($method->docblock->tags['static'][0]);
                $info['link'] = $method->getLink($this);
                if (!$info['link']) {
                    $info['link'] = $method->getFunctionCall();
                }
                $info['name'] = $method->getName();
                if ($method->docblock)
                $info['sdesc'] = $method->docblock->getSDesc($this);
                $params = array();
                if (count($method->docblock->params))
                foreach($method->docblock->params as $param => $val)
                {
                    $a = $val->Convert($this);
                    $params[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
                }

                $info['function_call'] = $method->getIntricateFunctionCall($this,$params);
                $result["imethods"][] = $info;
            }
            $results[] = $result;
            $result = array();
        }
        return $results;
    }

    /**
     * Return template-enabled list of inherited class constants
     *
     * uses parserConst helper function getInheritedConsts and generates a
     * template-enabled list using getClassLink()
     * @param parserConst $child class constant
     * @see getClassLink(), parserMethod::getInheritedConsts()
     * @return array Format:
     * <pre>
     * array(
     *   array('parent_class' => link to parent class's documentation,
     *         'ivars' =>
     *            array(
     *              array('name' => inherited constant name,
     *                    'link' => link to inherited constant's documentation,
     *                    'value' => constant value,
     *                    'sdesc' => summary of inherited constant),
     *              ...),
     *   ...)
     * </pre>
     */

    function getFormattedInheritedConsts($child)
    {
        $package = $child->docblock->package;
        $subpackage = $child->docblock->subpackage;
        $ivars = $child->getInheritedConsts($this);
        $results = array();
        if (!count($ivars)) return $results;
        foreach($ivars as $parent => $vars)
        {
            $file = $vars['file'];
            $vars = $vars['consts'];
            $par = $this->classes->getClass($parent,$file);
            if ($par) {
                $package = $par->docblock->package;
            }
            usort($vars,array($this,"sortVar"));
            $result['parent_class'] = $this->getClassLink($parent,$package);
            if (!$result['parent_class']) {
                $result['parent_class'] = $parent . ' (Internal Class)';
            }
            foreach($vars as $var)
            {
                $info = array();

                if ($var->docblock->hasaccess) {
                	$info['access'] = $var->docblock->tags['access'][0]->value;
                } else {
                	$info['access'] = 'public';
                }
                $info['name'] = $var->getName();
                $info['link'] = $var->getLink($this);
                if (!$info['link']) {
                    $info['link'] = $info['name'] . ' = ' . $var->getValue();
                }
                $info['value'] = $this->postProcess($var->getValue());
                if ($var->docblock)
                $info['sdesc'] = $var->docblock->getSDesc($this);
                $result["iconsts"][] = $info;
            }
            $results[] = $result;
            $result = array();
        }
        return $results;
    }

    /**
     * Return a Smarty template object to operate with
     *
     * This returns a Smarty template with pre-initialized variables for use.
     * If the method "SmartyInit()" exists, it is called.
     * @return Smarty
     */
    function &newSmarty()
    {
        $templ = new Smarty;
        $templ->use_sub_dirs = false;
        $templ->template_dir = realpath($this->smarty_dir . PATH_DELIMITER . 'templates');
        $templatename = get_class($this) . $this->templateName;
        if (!file_exists($this->targetDir . DIRECTORY_SEPARATOR . md5($templatename))) {
            // we'll delete this on finishing conversion
            $this->_compiledDir[$this->targetDir . DIRECTORY_SEPARATOR . md5($templatename)] = 1;
            mkdir($this->targetDir . DIRECTORY_SEPARATOR . md5($templatename),0775);
        }
        $templ->compile_dir = realpath($this->targetDir . PATH_DELIMITER . md5($templatename));
        $templ->config_dir = realpath($this->smarty_dir . PATH_DELIMITER . 'configs');
        $templ->assign("date",date("r",time()));
        $templ->assign("maintitle",$this->title);
        $templ->assign("package",$this->package);
        $templ->assign("phpdocversion",PHPDOCUMENTOR_VER);
        $templ->assign("phpdocwebsite",PHPDOCUMENTOR_WEBSITE);
        $templ->assign("subpackage",$this->subpackage);
        if (method_exists($this,'SmartyInit')) return $this->SmartyInit($templ);
        return $templ;
    }

    /**
     * Finish up parsing/cleanup directories
     */
    function cleanup()
    {
        foreach ($this->_compiledDir as $dir => $one) {
            $this->_rmdir($dir);
        }
    }

    /**
     * Completely remove a directory and its contents
     *
     * @param string $directory
     */
    function _rmdir($directory)
    {
        $handle = @opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    $this->_rmdir($directory . DIRECTORY_SEPARATOR . $file);
                }
                @unlink($directory . DIRECTORY_SEPARATOR . $file);
            }
            closedir($handle);
            @rmdir($directory);
        }
    }

    /**
     * do all necessary output
     * @see Converter
     * @abstract
     */
    function Output($title)
    {
        phpDocumentor_out("WARNING: Generic Converter::Output was used, no output will be generated");
    }

    /**
     * Set the template directory with a different template base directory
     * @tutorial phpDocumentor.howto.pkg#using.command-line.templatebase
     * @param string template base directory
     * @param string template name
     */
    function setTemplateBase($base, $dir)
    {
        // remove trailing /'s from the base path, if any
        $base = str_replace('\\','/',$base);
        while ($base{strlen($base) - 1} == '/') $base = substr($base,0,strlen($base) - 1);
        $this->templateName = substr($dir,0,strlen($dir) - 1);
        $this->templateDir =  $base . "/Converters/" . $this->outputformat . "/" . $this->name . "/templates/" . $dir;
        if (!is_dir($this->templateDir))
        {
            addErrorDie(PDERROR_TEMPLATEDIR_DOESNT_EXIST, $this->templateDir);
        }

        $this->smarty_dir = $this->templateDir;
        if (file_exists($this->templateDir . PATH_DELIMITER . 'options.ini'))
        {
            // retrieve template options, allow array creation
            $this->template_options = phpDocumentor_parse_ini_file($this->templateDir . PATH_DELIMITER . 'options.ini',true);
        }
    }

    /**
     * sets the template directory based on the {@link $outputformat} and {@link $name}
     * Also sets {@link $templateName} to the $dir parameter
     * @param string subdirectory
     */
    function setTemplateDir($dir)
    {
        if ('@DATA-DIR@' != '@'.'DATA-DIR@') {
            $templateBase = str_replace('\\', '/', '@DATA-DIR@/PhpDocumentor/phpDocumentor');
        } else {
            $templateBase = str_replace('\\','/',$GLOBALS['_phpDocumentor_install_dir']) . '/phpDocumentor';
        }
        $this->setTemplateBase($templateBase, $dir);
    }

    /**
     * Get the absolute path to the converter's base directory
     * @return string
     */
    function getConverterDir()
    {
        if ('@DATA-DIR@' != '@' . 'DATA-DIR@') {
            return str_replace('\\', '/', "@DATA-DIR@/PhpDocumentor/phpDocumentor/Converters/") . $this->outputformat . "/" . $this->name;
        } else {
            return str_replace('\\','/',$GLOBALS['_phpDocumentor_install_dir']) ."/phpDocumentor/Converters/" . $this->outputformat . "/" . $this->name;
        }
    }

    /**
     * Parse a global variable's default value for class initialization.
     *
     * If a global variable's default value is "new class" as in:
     * <code>
     * $globalvar = new Parser
     * </code>
     * This method will document it not as "new Parser" but instead as
     * "new {@link Parser}".    For examples, see {@link phpdoc.inc}.
     * Many global variables are classes, and phpDocumentor links to their
     * documentation
     * @return string default global variable value with link to class if
     *                it's "new Class"
     * @param string default value of a global variable.
     */
    function getGlobalValue($value)
    {
        if (strpos($value,'new') === 0)
        {
            preg_match('/new([^(]*)((?:.|\r|\n)*)/',$value,$newval);
            if (isset($newval[1]))
            {
                $a = Converter::getLink(trim($newval[1]));
                if (!isset($newval[2])) $newval[2] = '';
                if ($a && phpDocumentor_get_class($a) == 'classlink') $value = 'new '.$this->returnSee($a) .
                    $this->postProcess($newval[2]);
            }
            return $value;
        }
        return $this->postProcess($value);
    }

    /**
     * Parse an include's file to see if it is a file documented in this project
     *
     * Although not very smart yet, this method will try to look for the
     * included file file.ext:
     *
     * <code>
     * include ("file.ext");
     * </code>
     *
     * If it finds it, it will return a link to the file's documentation.  As of
     * 1.2.0rc1, phpDocumentor is smarty enough to find these cases:
     * <ul>
     *  <li>absolute path to file</li>
     *  <li>./file.ext or ../file.ext</li>
     *  <li>relpath/to/file.ext if relpath is a subdirectory of the base parse
     *      directory</li>
     * </ul>
     * For examples, see {@link Setup.inc.php} includes.
     * Every include auto-links to the documentation for the file that is included
     * @return string included file with link to docs for file, if found
     * @param string file included by include statement.
     * @param string path of file that has the include statement
     */
    function getIncludeValue($value, $ipath)
    {
        preg_match('/"([^"\']*\.[^"\']*)"/',$value,$match);
        if (!isset($match[1]))
        preg_match('/\'([^"\']*\.[^"\']*)\'/',$value,$match);
        if (isset($match[1]))
        {
            $fancy_per = $this->proceduralpages->pathMatchesParsedFile($match[1],$ipath);
            if ($fancy_per)
            {
                $link = $this->addLink($fancy_per);
                if (is_object($link) && phpDocumentor_get_class($link) == 'pagelink' &&
                    isset($this->all_packages[$link->package]))
                {
                    $value = $this->returnSee($link,$value);
                }
            } else
            {
                $per = Converter::getLink($match[1]);
                if (is_object($per) && phpDocumentor_get_class($per) == 'pagelink')
                $value = $this->returnSee($per);
            }
        }
        return $value;
    }

    /**
     * Recursively creates all subdirectories that don't exist in the $dir path
     * @param string $dir
     */
    function createParentDir($dir)
    {
        if (empty($dir)) return;
        $tmp = explode(SMART_PATH_DELIMITER,$dir);
        array_pop($tmp);
        $parent = implode(SMART_PATH_DELIMITER,$tmp);
        if ($parent != '' && !file_exists($parent))
        {
            $test = @mkdir($parent,0775);
            if (!$test)
            {
                $this->createParentDir($parent);
                $test = @mkdir($parent,0775);
                phpDocumentor_out("Creating Parent Directory $parent\n");
            } else
            {
                phpDocumentor_out("Creating Parent Directory $parent\n");
            }
        }
    }

    /**
     * Sets the output directory for generated documentation
     * 
     * As of 1.3.0RC6, this also sets the compiled templates directory inside
     * the target directory
     * @param string $dir the output directory
     */
    function setTargetDir($dir)
    {
        if (strlen($dir) > 0)
        {
            $this->targetDir = $dir;
            // if directory does exist create it, this should have more error checking in the future
            if (!file_exists($dir))
            {
                $tmp = str_replace(array("/","\\"),SMART_PATH_DELIMITER,$dir);
                if (substr($tmp,-1) == SMART_PATH_DELIMITER)
                {
                    $tmp = substr($tmp,0,(strlen($tmp)-1));
                }
                $this->createParentDir($tmp);
                phpDocumentor_out("Creating Directory $dir\n");
                mkdir($dir,0775);
            } elseif (!is_dir($dir))
            {
                echo "Output path: '$dir' is not a directory\n";
                die();
            }
        } else {
            echo "a target directory must be specified\n try phpdoc -h\n";
            die();
        }
    }

    /**
     * Writes a file to target dir
     * @param string
     * @param string
     * @param boolean true if the data is binary and not text
     */
    function writeFile($file,$data,$binary = false)
    {
        if (!file_exists($this->targetDir))
        {
            mkdir($this->targetDir,0775);
        }
        $string = '';
        if ($binary) $string = 'binary file ';
        phpDocumentor_out("    Writing $string".$this->targetDir . PATH_DELIMITER . $file . "\n");
        flush();
        $write = 'w';
        if ($binary) $write = 'wb';
        $fp = fopen($this->targetDir . PATH_DELIMITER . $file,$write);
        set_file_buffer( $fp, 0 );
        fwrite($fp,$data,strlen($data));
        fclose($fp);
    }

    /**
     * Copies a file from the template directory to the target directory
     * thanks to Robert Hoffmann for this fix
     * @param string
     */
    function copyFile($file, $subdir = '')
    {
        if (!file_exists($this->targetDir))
        {
            mkdir($this->targetDir,0775);
        }
        copy($this->templateDir . $subdir  . PATH_DELIMITER . $file, $this->targetDir . PATH_DELIMITER . $file);
    }

    /**
     * Return parserStringWithInlineTags::Convert() cache state
     * @see parserStringWithInlineTags::Convert()
     * @abstract
     */
    function getState()
    {
        return true;
    }

    /**
     * Compare parserStringWithInlineTags::Convert() cache state to $state
     * @param mixed
     * @see parserStringWithInlineTags::Convert()
     * @abstract
     */
    function checkState($state)
    {
        return true;
    }

}

/**
 * @access private
 * @see Converter::getSortedClassTreeFromClass()
 */
function rootcmp($a, $b)
{
    return strnatcasecmp($a['class'],$b['class']);
}

/**
 * @access private
 * @global string used to make the first tutorials converted the default package tutorials
 */
function tutorialcmp($a, $b)
{
    global $phpDocumentor_DefaultPackageName;
    if ($a == $phpDocumentor_DefaultPackageName) return -1;
    if ($b == $phpDocumentor_DefaultPackageName) return 1;
    return strnatcasecmp($a, $b);
}

/**
 * smart htmlentities, doesn't entity the allowed tags list
 * Since version 1.1, this function uses htmlspecialchars instead of
 * htmlentities, for international support
 * This function has been replaced by functionality in {@link ParserDescCleanup.inc}
 * @param string $s
 * @return string browser-displayable page
 * @deprecated As of v1.2, No longer needed, as valid tags are parsed out of the source,
 *   and everything else is {@link Converter::postProcess()} handled
 */
function adv_htmlentities($s)
{
    return;
    global $phpDocumentor___html,$_phpDocumentor_html_allowed;
    $result = htmlspecialchars($s);
    $entities = array_flip(get_html_translation_table(HTML_SPECIALCHARS));
    $result = strtr($result,$phpDocumentor___html);
    $matches = array();
    preg_match_all('/(&lt;img.*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    preg_match_all('/(&lt;font.*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    preg_match_all('/(&lt;ol.*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    preg_match_all('/(&lt;ul.*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    preg_match_all('/(&lt;li.*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    preg_match_all('/(&lt;a .*&gt;)/U',$result,$matches);
    for($i=0;$i<count($matches[1]);$i++)
    {
        $result = str_replace($matches[1][$i],strtr($matches[1][$i],array_flip(get_html_translation_table(HTML_SPECIALCHARS))),$result);
    }
    return $result;
}

/**
 * Used solely for setting up the @uses list
 * @package ignore
 * @ignore
 */
class __dummyConverter extends Converter
{
    function setTemplateDir(){}
    function setTargetDir(){}
    function getPageName(&$element)
    {
        if (phpDocumentor_get_class($element) == 'parserpage') return '_'.$element->getName();
        return '_'.$element->parent->getName();
    }
}
?>
