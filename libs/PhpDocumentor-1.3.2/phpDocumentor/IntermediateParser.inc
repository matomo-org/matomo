<?php
/**
 * The phpDocumentor_IntermediateParser Class
 *
 * The Intermediary Data Parser (intermediate between Parse and Converter)
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
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: IntermediateParser.inc,v 1.19.2.1 2007/03/29 18:51:11 ashnazg Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.1
 */
/** The phpDocumentor_IntermediateParser Class
 *
 * This class performs the work of organizing raw data from the parser in the
 * format of descendants of the {@link parserElement} class.  This is also where
 * processing of package pages occurs, in
 * {@link phpDocumentor_IntermediateParser::handleClass()} for class-level
 * packages and {@link phpDocumentor_IntermediateParser::handleDocBlock()} for
 * page-level packages.
 *
 * Most of the work of this parser goes to matching up
 * DocBlocks with the elements that they are documenting.  Since DocBlocks are
 * passed before the element they document, the last DocBlock is stored in
 * {@link phpDocumentor_IntermediateParser::$last} and then placed into the
 * $docblock parameter of the parserElement
 * descendant object.
 *  @author Gregory Beaver
 *  @version $Id: IntermediateParser.inc,v 1.19.2.1 2007/03/29 18:51:11 ashnazg Exp $
 *  @copyright 2002 Gregory Beaver
 *  @package     phpDocumentor
 */
class phpDocumentor_IntermediateParser
{
    /**
     * @var parserDocBlock
     */
    var $last;
    
    /**
     * type of the last parser Element handled
     *
     * This is used in handleDocBlock to determine whether a DocBlock is a
     * page-level DocBlock in conjunction with the {@link parserData::$clean}
     * var.  A page-level DocBlock is alwaysthe first DocBlock in a file, and
     * must be followed by another DocBlock.  The first test is handled by
     * parserData::$clean, which is set to false on the first encounter of an
     * element, and the second test is handled by this variable, which must be
     * equal to "docblock"
     * @see handleDocBlock()
     * @var string
     */
    var $lasttype = '';
    
    /**
     * Name of the class currently being parsed.
     * It is only used (and only valid) when phpDocumentor_IntermediateParser is
     * parsing a class
     * @var string
     */
    var $cur_class = '';
    
    /**
     * type of the current parser Element being handled
     * 
     * This is used by {@link HandleEvent()} to set the {@link $lasttype} var,
     * which is used to detect page-level DocBlocks
     * @var string
     */
    var $type = '';
    
    /**
     * set in {@link Setup.inc.php} to the value of the parseprivate commandline
     * option.  If this option is true, elements with an @access private tag
     * will be parsed and displayed
     * @tutorial phpDocumentor.howto.pkg#using.command-line.parseprivate
     * @var boolean
     */
    var $parsePrivate = false;
    
    /**
     * this variable is used to prevent parsing of elements with an @ignore tag
     * @see $packageoutput
     * @see $parsePrivate
     */
    var $private_class = false;
    
    /**
     * used to set the output directory
     * @see setTargetDir()
     */
    var $targetDir;
    
    /**
     * used to set the template base directory
     * @see setTemplateBase()
     */
    var $templateBase;
    
    /**
     * array of parsed package pages
     *
     * used by {@link Convert()} to convert all package pages into output
     * @var array
     */
    var $package_pages = array();
    
    /**
     * @var array array of all {@link parserData} containing page information
     */
    var $pages = array();
    /**
     * Put away a page that has been @ignored or @access private if
     * !{@link $parsePrivate}
     *
     * When a page has @access private in its DocBlock, it is placed here
     * instead of in {@link $pages}, to allow for proper Class parsing.  Since
     * classes and pages are parsed as if they were separate, this array allows
     * public classes on private pages to retrieve information needed about the
     * page that holds the class and to {@link addPageIfNecessary()} to the
     * $pages array
     * @var array
     */
    var $privatepages = array();
    /**
     * Keeps track of packages of classes that have parent classes in another
     * package.  Used in automatic linking.
     *
     * This array is updated by {@link addPackageParent()}, which is called in
     * {@link Classes::processChild()} to keep track of classes that descend
     * from classes in different packages.  In other words, if class foo is in
     * package one, and class bar is in package two, an entry
     * $package_parents['two'] = 'one' will be made.
     * @var array Format: packagename => parentpackagename
     * @see Converter::getLink()
     */
    var $package_parents = array();
    
    /**
     * Used to determine the category for tutorials.
     *
     * <b>WARNING:</b> If more than one category exists, the last category
     * encountered will overwrite the previous and will raise a big warning
     * @var array Format: packagename => categoryname
     */
    var $packagecategories = array();
    
    /**
     * list of all packages encountered while documenting.  Used in automatic
     * linking.
     * 
     * Converter::getLink() first checks if an ambiguous link is found in the
     * current package.  If not, it then checks in parent packages, and if still
     * not found, uses this array to check in the rest of the packages before
     * giving up
     * @var array Format: array(packagename => 1, packagename => 1,...)
     * @see Converter::getLink()
     */
    var $all_packages = array();
    
    /**
     * array of packages to parser and output documentation for, if not all
     * packages should be documented
     *
     * Format:<br />
     * array(package1,package2,...)<br />
     * or false if not set
     *
     * Use this option to limit output similar to ignoring files.  If you have
     * some temporary files that you don't want to specify by name but don't
     * want included in output, set a package name for all the elements in your
     * project, and set packageoutput to that name.  the default package will be
     * ignored.  Parsing speed does not improve.  If you want to ignore files
     * for speed reasons, use the ignore command-line option
     * @tutorial phpDocumentor.howto.pkg#using.command-line.packageoutput
     * @see Io
     * @var false|array
     */
    var $packageoutput = false;
    
    /**
     * the functions which handle output from the {@link Parser}
     * @see handleEvent(), handleDocBlock(), handlePage(), handleClass()
     * @see handleDefine(), handleFunction(), handleMethod(), handleVar()
     * @see handlePackagePage(), handleInclude(), handleTutorial()
     */
    var $event_handlers = array(
            'docblock' => 'handleDocBlock',
            'page' => 'handlePage',
            'class' => 'handleClass',
            'define' => 'handleDefine',
            'function' => 'handleFunction',
            'method' => 'handleMethod',
            'var' => 'handleVar',
            'const' => 'handleConst',
            'packagepage' => 'handlePackagePage',
            'include' => 'handleInclude',
            'global' => 'handleGlobal',
            'tutorial' => 'handleTutorial',
            );
    
    /**
     * $data contains parsed structures for the current page being parsed
     *
     * In version 1.1+, $data is only used to store the current page information.
     * All handling of documented elements is handled by the
     * {@link ProceduralPages} and {@link Classes} classes.
     * @var parserData
     */
    var $data;
    
    /**
     * set in {@link Setup.inc.php} to the value of the quitemode commandline
     * option.
     *
     * If this option is true, informative output while parsing will not be
     * displayed (documentation is unaffected)
     * @var boolean
     * @tutorial phpDocumentor.howto.pkg#using.command-line.quiet
     */
    var $quietMode = false;
        
    /**
     * used to keep track of inheritance at the smartest level possible for a
     * dumb computer
     * @var Classes
     */
    var $classes = false;
    
    /**
     * used to keep track of all elements in a procedural page.  Handles name
     * conflicts with elegance
     * @since 1.1
     * @var ProceduralPages
     */
    var $proceduralpages = false;
    
    /**
     * an array of template names indexed by converter name
     *
     * For example, if the default HTMLframesConverter is using the DOM/l0l33t
     * template, the array will be
     * <code>$converters['frames'] = 'DOM/l0l33t'</code>
     * @var array Format: array(Convertername1 => templatename)
     * @see Converter
     */
    var $converters = false;
    /** 
     * @var string Title of generated documentation, passed to Converters
     */
    var $title = '';
    
    var $uses = array();

    var $db_template;
    
    /**
     * Stores parsed CHANGELOG/INSTALL/README files
     * @var array Format: array(CHANGELOG => contents,
     *                          INSTALL => contents,
     *                          README => contents)
     */
    var $ric = array();
    
    /**
     * Flag used to determine whether the last docblock
     * was a page-level docblock.
     * @var boolean
     * @access private
     */
    var $_lastDocBlockWasPageLevel = false;
    
    /**
     * Flag used to determine whether the Page-level
     * DocBlock was declared in old or new style
     * @var boolean
     * @access private
     */
    var $_oldPageLevel = false;

    /**
     * sets up basic data structures
     * @param string Title of generated documentation, passed to Converters
     * @see $title, $data, $classes, $proceduralpages
     */
    function phpDocumentor_IntermediateParser($title='Generated Documentation')
    {
        $this->title = $title;
        $this->data = new parserData;
        $this->classes = new Classes;
        $this->proceduralpages = new ProceduralPages;
    }
    
    /**
     * Retrieve the relative path.  If the path contains "pear/" it will
     * be used as the base, otherwise the Program_Root string will be used.
     * @global array uses 'Program_Root' option to replace it with '' for
     *               retrieving the source location of a file
     * @param string path to file
     * @return string
     * @see $sourceLocation
     * @access private
     */
    function _getSourceLocation($sl, $sourceloc)
    {
        global $_phpDocumentor_options;
        if (empty($sl)) return false;
        $sl = str_replace('\\','/',$sl);
        if (strpos($sl,'pear/'))
        {
            $sl = substr($sl,strpos($sl,'pear/') + 5);
            if (dirname($sl) == '.')
            {
                return 'PEAR';
            }
            return dirname($sl);
        } else
        {
            if (strpos(str_replace($_phpDocumentor_options['Program_Root'] . PATH_DELIMITER,'',$sourceloc),PATH_DELIMITER) === false)
                return '';
            return dirname(str_replace($_phpDocumentor_options['Program_Root'] . PATH_DELIMITER,'',$sourceloc));
        }
    }
    
    /**
     * Guess the package/subpackage based on subdirectory if the --pear option
     *
     * A file in pear/dir/file.php will be in package "dir."  A file in
     * pear/dir/subdir/file.php will be in package "dir," subpackage "subdir."
     * @param string full path of file
     * @param template-ready source location Program_Root/dir/file.php
     * @global array uses the 'pear' option to determine whether to guess based
     *               on subdirectory
     * @tutorial phpDocumentor.howto.pkg#using.command-line.pear
     */
    function _guessPackage($path, $sourceloc)
    {
        global $_phpDocumentor_setting;
        if ($_phpDocumentor_setting['pear'])
        {
            $subpath = explode(PATH_DELIMITER, $this->_getSourceLocation($path, $sourceloc));
            if (!empty($subpath[0]))
            { // can only have package and subpackage in this version
                $package = $subpath[0];
                $subpackage = '';
                if (isset($subpath[1])) $subpackage = $subpath[1];
                return array($package,$subpackage);
            } else return array($this->package, $this->subpackage);
        } else return array($this->package, $this->subpackage);
    }
    
    /**
     * handles post-parsing of include/require/include_once/require_once
     *
     * This function sets {@link $data}->clean to false to tell the
     * phpDocumentor_IntermediateParser that a page-level DocBlock can't be
     * found after this point on this page.  It then sets the package
     * to be the same as the page, and adds itself to the
     * {@link ProceduralPages} class
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserInclude $data
     */
    function handleInclude($event,$data)
    {
        if ($this->_lastDocBlockWasPageLevel)
        {
            addWarning(PDERROR_DOCBLOCK_CONFLICT, $data->getName(), $data->getValue());
            if (!$this->_oldPageLevel)
            {
                unset($this->last);
            }
        }
        $this->_lastDocBlockWasPageLevel = false;
        $this->data->clean = false;
        // page was @ignored
        if ($this->private_page)
        {
            unset($this->last);
            return;
        }
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        if ($this->last->getKeyword('ignore'))
        {
            $this->last = false;
            return;
//            addWarning(PDERROR_IGNORE_TAG_IGNORED,'include',$data->getName().'('.$data->getValue().')');
        }

        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'include');
        $data->setDocBlock($this->last);
        $this->proceduralpages->addInclude($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of global variables
     *
     * This function sets {@link $data}->clean to false to tell the
     * phpDocumentor_IntermediateParser that a page-level DocBlock can't be
     * found after this point on this page.  It then sets the package
     * to be the same as the page, and adds itself to the
     * {@link ProceduralPages} class
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserGlobal $data
     */
    function handleGlobal($event,$data)
    {
        if ($this->_lastDocBlockWasPageLevel)
        {
            addWarning(PDERROR_DOCBLOCK_CONFLICT, 'global variable', $data->getName());
            if (!$this->_oldPageLevel)
            {
                unset($this->last);
            }
        }
        $this->_lastDocBlockWasPageLevel = false;
        $this->data->clean = false;
        if ($this->private_page)
        {
            unset($this->last);
            return;
        }
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        if ($this->last->getKeyword('ignore'))
        {
            addWarning(PDERROR_IGNORE_TAG_IGNORED,'global variable - just don\'t document the',$data->getName());
            $this->last = false;
            return;
        }
        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'global');
        $data->setDocBlock($this->last);
        if ($data->docblock->getKeyword('name'))
        {
            $a = $data->docblock->getKeyword('name');
            if (is_object($a)) $a = $a->value;
            $data->setName($a);
        }
        $this->proceduralpages->addGlobal($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of Package-level documentation pages.
     *
     * sets the {@link $package_pages}[$data->package] to $data
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserPackagePage $data
     */
    function handlePackagePage($event,$data)
    {
        $this->package_pages[$data->package] = &$data;
        $this->last = false;
    }
    
    /**
     * handle post-parsing of Tutorials.
     *
     * This adds the parsed tutorial to the tutorial tree
     * @uses $tutorials sets the value of tutorials to parameter $data
     * @param integer $event Event Number
     * @param parserTutorial $data
     * @since 1.2
     */
    function handleTutorial($event,$data)
    {
        if (isset($this->packagecategories[$data->package]))
        {
            $data->category = $this->packagecategories[$data->package];
        } else
        {
            $data->category = $GLOBALS['phpDocumentor_DefaultCategoryName'];
        }
        $this->tutorials[$data->package][$data->subpackage][$data->tutorial_type][$data->name] = $data;
    }
    
    /**
     * handles post-parsing of class vars
     *
     * This function sets up a @var tag if none is found, and aligns $data's
     * $path var and packages to match the parent object
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserVar $data
     */
    function handleVar($event,$data)
    {
        global $_phpDocumentor_setting;
        if ($this->private_class)
        {
            unset($this->last);
            return;
        }
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            } 
            else 
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'var');
        $this->last->updateModifiers($data->getModifiers());

        if ($this->last->getKeyword('ignore'))
        {
            $this->last = false;
            return;
//            addWarning(PDERROR_IGNORE_TAG_IGNORED,'var',$this->cur_class.'::'.$data->getName());
        }
        if (!$this->last->var)
        {
            $this->last->addVar('mixed',new parserStringWithInlineTags);
        }
        
        if ($_phpDocumentor_setting['pear'])
        {
            if (strpos($data->getName(), '_') == 1 && !$this->last->getKeyword('access'))
            {
                addWarning(PDERROR_PRIVATE_ASSUMED,'class variable',$data->class.'::'.$data->getName());
                $this->last->addKeyword('access','private');
                $data->setDocBlock($this->last);
            }
        }
        $data->setDocBlock($this->last);
        $data->path = $this->data->parent->path;
        $this->classes->addVar($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of class constants
     *
     * This function aligns $data's
     * $path var and packages to match the parent object
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserVar $data
     */
    function handleConst($event,$data)
    {
        global $_phpDocumentor_setting;
        if ($this->private_class)
        {
            unset($this->last);
            return;
        }
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'const');

        if ($this->last->getKeyword('ignore'))
        {
            $this->last = false;
            return;
//            addWarning(PDERROR_IGNORE_TAG_IGNORED,'var',$this->cur_class.'::'.$data->getName());
        }
        $data->setDocBlock($this->last);
        $data->path = $this->data->parent->path;
        $this->classes->addConst($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of class methods
     *
     * This function first aligns $data's path and package to match the parent
     * object, and also aligns the docblock's @param, @global, and @staticvar
     * tags with the information parsed from the method source code.  It also
     * checks to see if the method is a constructor and sets the $isConstructor
     * flag.  If source code has been parsed by a {@}source} tag, the source is
     * added to its docblock
     *
     * Finally, it adds the method to the {@link Classes} class.
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserMethod $data
     */
    function handleMethod($event,$data)
    {
        global $_phpDocumentor_setting;
        if ($this->private_class)
        {
            unset($this->last);
            return;
        }

        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        if ($this->last->getKeyword('ignore'))
        {
            $this->last = false;
            return;
//            addWarning(PDERROR_IGNORE_TAG_IGNORED,'method',$this->cur_class.'::'.$data->getName());
        }
        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'method');
        if ($data->hasSource())
        {
            $this->last->setSource($data->getSource(), $data->getClass());
        }
        foreach($data->listParams() as $key => $param)
        {
            $update_params[$key] = $param;
        }
        foreach($data->listGlobals() as $param)
        {
            $update_globals[] = $param[1];
        }
        foreach($data->listStatics() as $param)
        {
            $update_statics[] = $param[0];
        }
        if (isset($update_params))
        $this->last->updateParams($update_params);
        if (isset($update_globals))
        $this->last->updateGlobals($update_globals);
        if (isset($update_statics))
        $this->last->updateStatics($update_statics);
        $this->last->updateModifiers($data->getModifiers());
        unset($update_params);
        unset($update_globals);
        unset($update_statics);

        if ($data->getName() == $this->cur_class) $data->setConstructor();
        if ($data->getName() == '__construct') {
            $data->setConstructor();
        }
        if ($data->getName() == '__destruct') {
            $data->setDestructor();
        }

        if ($_phpDocumentor_setting['pear'])
        {
            if (strpos($data->getName(), '_') === 0 && substr($data->getName(), 1) == $data->class)
            { // is destructor
                $data->setDestructor();
            } elseif (strpos($data->getName(), '_') === 0 && !$this->last->getKeyword('access'))
            {
                if (strpos($data->getName(), '__') !== 0) {
                    addWarning(PDERROR_PRIVATE_ASSUMED,'method',$data->class.'::'.$data->getName().'()');
                    $this->last->addKeyword('access','private');
                    $data->setDocBlock($this->last);
                }
            }
        }
        $data->setDocBlock($this->last);
        $data->path = $this->data->parent->path;
        $this->classes->addMethod($data);
        $this->last = false;
    }

    /**
     * handles post-parsing of functions
     *
     * This function sets {@link $data}->clean to false to tell the
     * phpDocumentor_IntermediateParser that a page-level DocBlock can't be
     * found after this point on this page.  It then sets the package to be the
     * same as the page, aligns the docblock's @param, @global, and @staticvar
     * tags with the information parsed from the function source code.
     *
     * If source code has been parsed by a {@}source} tag, the source is added
     * to its docblock, and then the parserFunction adds itself to the
     * {@link ProceduralPages} class
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserFunction $data
     */
    function handleFunction($event,$data)
    {
        if ($this->_lastDocBlockWasPageLevel)
        {
            addWarning(PDERROR_DOCBLOCK_CONFLICT, 'function', $data->getName());
            if (!$this->_oldPageLevel)
            {
                unset($this->last);
            }
        }
        $this->_lastDocBlockWasPageLevel = false;
        $this->data->clean = false;
        if ($this->private_page)
        {
            unset($this->last);
            return;
        }

        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        if ($this->last->getKeyword('ignore'))
        {
            unset($this->last);
            return;
        }
        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'function');

        foreach($data->listParams() as $key => $param)
        {
            $update_params[$key] = $param;
        }
        foreach($data->listGlobals() as $param)
        {
            $update_globals[] = $param[1];
        }
        foreach($data->listStatics() as $param)
        {
            $update_statics[] = $param[0];
        }
        if (isset($update_params))
        $this->last->updateParams($update_params);
        if (isset($update_globals))
        $this->last->updateGlobals($update_globals);
        if (isset($update_statics))
        $this->last->updateStatics($update_statics);
        unset($update_params);
        unset($update_globals);
        unset($update_statics);

        if ($data->hasSource())
        {
            $this->last->setSource($data->getSource());
        }
        if (count($this->last->params) == 1 && !count($data->listParams()))
        {
            // if the function has no parameters, and 1 @param, add it to the list as optional, default value is description from @param
            $pars = $this->last->listParams();
            $data->addParam($pars[0]['var'],$pars[0]['data']->getString());
        }
        $data->setDocBlock($this->last);
        $this->proceduralpages->addFunction($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of defines
     *
     * This function sets {@link $data}->clean to false to tell the
     * phpDocumentor_IntermediateParser that a page-level DocBlock can't be
     * found after this point on this page.  It then sets the package to be the
     * same as the page and adds itself to the {@link ProceduralPages} class
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserDefine $data
     */
    function handleDefine($event,$data)
    {
        if ($this->_lastDocBlockWasPageLevel)
        {
            addWarning(PDERROR_DOCBLOCK_CONFLICT, 'define', $data->getName());
            if (!$this->_oldPageLevel)
            {
                unset($this->last);
            }
        }
        $this->_lastDocBlockWasPageLevel = false;
        $this->data->clean = false;
        if ($this->private_page)
        {
            unset($this->last);
            return;
        }
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
        }
//        $this->last->setLineNumber($data->getLineNumber());
        if ($this->last->getKeyword('ignore'))
        {
            unset($this->last);
            return;
        }

        $this->last->overridePackage($this->category,$this->package,$this->subpackage,$data->getName(),'define');
        $data->setDocBlock($this->last);
        $this->proceduralpages->addDefine($data);
        $this->last = false;
    }
    
    /**
     * handles post-parsing of classes
     *
     * This function sets {@link $data}->clean to false to tell the
     * phpDocumentor_IntermediateParser that a page-level DocBlock can't be
     * found after this point on this page.  It sets {@link $cur_class} to its
     * name, and if an @ignore tag is found in the DocBlock, it sets
     * {@link $private_class} to true, to prevent post-parsing of any of the
     * class's vars or methods.  Then it checks for the existence of a package
     * page for the class's package
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserClass $data
     */
    function handleClass($event,$data)
    {
        global $_phpDocumentor_setting;
        if ($data->isInterface()) 
        {
            $objectType = 'interface';
        }
        else
        {
            $objectType = 'class';
        }
        if ($this->_lastDocBlockWasPageLevel)
        {
            if (!$this->_oldPageLevel)
            {
                addWarning(PDERROR_DOCBLOCK_GOES_CLASS, $data->getName());
                $doc = new parserDocBlock;
                $doc->category = $this->category;
                $doc->package = $this->package;
                $doc->subpackage = $this->subpackage;
                if ($_phpDocumentor_setting['sourcecode']) {
                    $doc->canSource();
                    $doc->addFileSource($this->data->parent->path, $this->data->parent->source);
                }
                $this->data->setDocBlock($doc);
                unset($doc);
                if ($this->last) {
                    $this->last->cantSource();
                }
            }
        }
        $this->_lastDocBlockWasPageLevel = false;
        $this->data->clean = false;
        if (empty($this->last))
        {
            if (isset($this->db_template))
            {
                // use the docblock template
                $this->last = phpDocumentor_clone($this->db_template);
            }
            else
            {
                // we don't have a docblock, create an empty one to get rid of errors
                $this->last = new parserDocblock();
            }
            list($this->last->package, $this->last->subpackage) = $this->_guessPackage($this->data->parent->path, $this->data->parent->getSourceLocation('dummy'));
            addWarning(PDERROR_NO_PACKAGE_TAG,$objectType,$data->getName(),$this->last->package);
        } else
        {
            if (!$this->last->getExplicitPackage())
            {
                list($this->last->package, $this->last->subpackage) = $this->_guessPackage($this->data->parent->path, $this->data->parent->getSourceLocation('dummy'));
                addWarning(PDERROR_NO_PACKAGE_TAG,$objectType,$data->getName(),$this->last->package);
            } else
            {
                if (isset($this->packagecategories[$this->package])
                    && $this->packagecategories[$this->package] != $this->category)
                    addWarning(PDERROR_PACKAGECAT_SET,$this->package,
                                $this->packagecategories[$this->package],
                                $this->category);
                $this->packagecategories[$this->package] = $this->category;
            }
        }
        $this->last->updateModifiers($data->getModifiers());
//        $this->last->setLineNumber($data->getLineNumber());
        $data->setDocBlock($this->last);
        $this->cur_class = $name = $data->getName();
        if ($this->last->getKeyword('ignore'))
        {
            $this->private_class = true;
            unset($this->last);
            return;
        }
        $data->path = $this->data->parent->path;
        $this->classes->addClass($data);
        $this->private_class = false;
        if ($this->last->package)
        {
            $this->parsePackagePage($this->last->package, $this->data->parent->getPath());
        }
        $this->last = false;
    }
    
    /**
     * handles post-parsing of procedural pages
     *
     * this event is called at the start of a new page, before the Parser knows
     * whether the page will contain any procedural pages or not
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserPage $data
     */
    function handlePage($event,$data)
    {
        $type = 'page';
        $this->private_page = false;
        $this->data = new parserData;
        $data->category = $this->category = $GLOBALS['phpDocumentor_DefaultCategoryName'];
        $this->package = $GLOBALS['phpDocumentor_DefaultPackageName'];
        $this->subpackage = '';
        $this->proceduralpages->addPage($data);
        $this->data->setParent($data);
        $this->pages[$data->getPath()] = $this->data;
        $this->classes->nextFile($data->getPath());
        $this->packageoutput = $data->getPackageOutput();
    }
    
    /**
     * handles post-parsing of DocBlocks
     *
     * This function sets {@link $last} to the DocBlock represented by $data, to
     * allow the next documentable element passed to
     * phpDocumentor_IntermediateParser to link the DocBlock into its $docblock
     * property.  This function also checks for two special cases of DocBlocks:
     * <ol>
     *    <li>First DocBlock in the file contains a @package tag</li>
     *    <li>First DocBlock in the file is immediately followed by another
     *        DocBlock</li>
     * </ol>
     * In both cases, the function extracts this tag and uses it as the
     * page-level package.  If the @package tag is in the DocBlock of an
     * element (function, global variable, whatever) that isn't a page-level
     * DocBlock, a warning will be raised to notify the author that a @package
     * tag belongs in a page-level DocBlock.
     *
     * <b>New</b> in version 1.2.2, if the first DocBlock in a file contains
     * a @package tag, it is a page-level DocBlock.
     *
     * If the DocBlock is page-level, it is processed with
     * {@link _processPageLevelDocBlock}
     *
     * Finally, the function replaces the old parserPage in
     * {@link parserData::$data}->parent with the new one containing information
     * from the DocBlock by calling {@link addPage()}, and checks for
     * package-level docs.
     * @param integer $event Event number from {@link Parser.inc}
     * @param parserDocBlock $data
     */
    function handleDocBlock($event,$data)
    {
        $type = 'docblock';
        $data->postProcess();
        // Zend desc support
        if ($tdesc = $data->getKeyword('desc'))
        {
            $data->setShortDesc($tdesc);
            unset($data->tags['desc']);
        }
        $this->_lastDocBlockWasPageLevel = false;
        // 1st docblock in file, check for @package
        if ($this->data->isClean() && !isset($this->last))
        {
            if ($data->getExplicitPackage())
            {
                // new with 1.2.2:
                // if the first docblock in a file
                // contains a @package tag, then it is
                // a page-level docblock
                $this->_processPageLevelDocBlock($data);
                $this->_lastDocBlockWasPageLevel = true;
                $this->all_packages[$data->package] = 1;
                $this->last = $data;
                return;
            }
            $doc = new parserDocBlock;
            $doc->category = $this->category;
            $doc->package = $this->package;
            $doc->subpackage = $this->subpackage;
            $this->data->setDocBlock($doc);
            $this->proceduralpages->addPagePackage($this->data->parent->getPath(),$this->package,$this->subpackage);
            unset($doc);
        }
        // 2nd docblock in a row, and it's at the top of the file, page-level docblock
        if ($this->lasttype == "docblock" && $this->data->isClean())
        {
            $this->_processPageLevelDocBlock($this->last);
            $this->_oldPageLevel = true;
            $this->_lastDocBlockWasPageLevel = false;
        }
        $this->all_packages[$data->package] = 1;
        $this->last = $data;
    }
    
    /**
     * Process a Page-level DocBlock
     *
     * First, it checks for an @ignore tag,
     * and if found, calls {@link ProceduralPages::ignorePage()}.  An @ignore
     * tag in a page-level DocBlock will ignore all functions, defines, global
     * variables, and includes.  It will not ignore classes!  The function next
     * checks for an @access private, and if --parseprivate is off, performs the
     * same actions as @ignore.
     * Next, it checks for the @name tag, which is used to rename the page.
     * This is also a PEAR compatibility issue, and may not be very useful in
     * the long run.  Documentation is best when it refers to real entities in
     * the package, and not to aliases.
     * @access private
     */
    function _processPageLevelDocBlock($data)
    {
        global $_phpDocumentor_setting;
        // can only have 1 package-level docblock, others are ignored
        if (!$this->data->isClean())
        {
            return;
        }
        $this->data->clean = false;
        $this->data->explicitDocBlock();
        $data->canSource();
        if ($_phpDocumentor_setting['sourcecode'])
        {
            $data->addFileSource($this->data->parent->path, $this->data->parent->source);
        }
        if (!$data->getExplicitPackage())
        {
            list($data->package,$data->subpackage) = $this->_guessPackage($this->data->parent->getPath(), $this->data->parent->getSourceLocation('dummy'));
            addWarning(PDERROR_NO_PACKAGE_TAG,'file',$this->data->parent->getPath(),$this->last->package);
        }
        if (isset($this->packagecategories[$this->package])
            && $this->packagecategories[$this->package] != $this->category)
            addWarning(PDERROR_PACKAGECAT_SET,$this->package,
                        $this->packagecategories[$this->package],
                        $data->category);
        $this->packagecategories[$this->package] = $data->category;
        $this->category = $this->data->parent->category = $data->category;
        $this->packagecategories[$this->package] = $this->category;
        $this->subpackage = $this->data->parent->subpackage = $data->subpackage;
        $this->data->setDocBlock($data);
        if ($data->getKeyword('ignore'))
        {
            $this->proceduralpages->ignorePage($this->data->parent);
            $this->private_page = true;
            unset($this->last);
            $this->privatepages[$this->data->parent->getPath()] = $this->data;
            unset($this->pages[$this->data->parent->getPath()]);
            return;
        }
        $this->package = $this->data->parent->package = $data->package;
        $this->subpackage = $this->data->parent->subpackage = $data->subpackage;
        $this->proceduralpages->addPagePackage($this->data->parent->getPath(),$this->package,$this->subpackage);
        if ($access = $data->getKeyword('access'))
        {
            if (is_object($access) && ($access->getString() == 'private') && (!$this->parsePrivate))
            {
                $this->proceduralpages->ignorePage($this->data->parent);
                $this->private_page = true;
                unset($this->last);
                $this->privatepages[$this->data->parent->getPath()] = $this->data;
                unset($this->pages[$this->data->parent->getPath()]);
                return;
            }
        }
        if ($data->getKeyword('name'))
        {
            $a = $data->getKeyword('name');
            if (is_object($a)) $a = $a->value;
            $this->data->parent->setFile($a);
            $this->proceduralpages->setName($a);
        }
        $this->addPage($this->data->parent, $this->data->parent->getPath());
        if ($this->package)
        {
            $this->parsePackagePage($this->package, $this->data->parent->getPath());
        }
    }
    
    /**
     * Backward-compatibility only, use the new tutorials for more power
     * @tutorial tutorials.pkg
     * @param string package name of package file to parse
     * @param string directory of file that contains package name
     */
    function parsePackagePage($package, $path)
    {
        if (!isset($this->package_pages[$package]))
        {
            if (file_exists(dirname($path) . SMART_PATH_DELIMITER . $package . '.html'))
            {
                if ($this->quietMode === false)
                {
                    phpDocumentor_out("Reading package-level file ".$package . '.html');
                          flush();
                }
                $fp = fopen(dirname($path) . SMART_PATH_DELIMITER . $package . '.html',"r");
                $ret = fread($fp,filesize(dirname($path) . SMART_PATH_DELIMITER . $package . '.html'));
                fclose($fp);
                unset($fp);
                if ($this->quietMode === false)
                {
                    phpDocumentor_out(" -- Parsing File\n");
                          flush();
                }
                $pageParser = new ppageParser;
                $tempp = $this->package;
                $lp = $this->last;
                $pageParser->subscribe('*',$this);
                $pageParser->parse($ret,false,$package);
                $this->package = $tempp;
                $this->last = $lp;
                unset($tempp);
                unset($pageParser);
            }
        }
    }
    
    /**
     * called via {@link Parser::parse()} and Parser's inherited method
     * {@link Publisher::publishEvent()}
     *
     * $event is one of the PHPDOC constants from Parser.inc.  If it is not
     * PHPDOCUMENTOR_EVENT_NEWSTATE, then a function name is retrieved from the
     * {@link $event_handlers} array and called to handle the $data
     * @param integer $event event number from {@link Parser.inc}
     * @param mixed $data if $event is {@link PHPDOCUMENTOR_EVENT_NEWSTATE}, $data is a {@link PHP_DOC_EVENT_END_PAGE} or {@link STATE_END_CLASS},
     *                    otherwise $data is either a {@link parserDocBlock}, {@link parserPage} or descendant of {@link parserElement}
     * @global array we use 'sourcecode' to determine whether to highlight the source
     *               of the current file if it has no file-level docblock
     */
    function HandleEvent($event,$data)
    {
        global $_phpDocumentor_setting;
        global $phpDocumentor_DefaultPackageName, $phpDocumentor_DefaultCategoryName;
        if (empty($this->packagecategories))
        $this->packagecategories[$phpDocumentor_DefaultPackageName] = $phpDocumentor_DefaultCategoryName;
        if ($event == PHPDOCUMENTOR_EVENT_NEWSTATE)
        {
            if ($data == STATE_END_CLASS)
            {
            } elseif ($data == PHPDOCUMENTOR_EVENT_END_PAGE)
            {
                if (!$this->private_page)
                {
                    $this->all_packages[$this->package] = 1;
                    if (!$this->data->hasExplicitDocBlock())
                    {
                        $doc = $this->data->docblock;
                        if (!$this->data->docblock)
                        {
                            $doc = new parserDocBlock;
                        }
                        if ($_phpDocumentor_setting['sourcecode'])
                        {
                            $doc->canSource();
                            $doc->addFileSource($this->data->parent->path, $this->data->parent->source);
                        }
                        list($doc->package,$doc->subpackage) = $this->_guessPackage($this->data->parent->getPath(), $this->data->parent->getSourceLocation('dummy'));
                        addWarning(PDERROR_NO_PAGE_LEVELDOCBLOCK,$this->data->parent->getPath());
                        $this->data->setDocBlock($doc);
                        $this->proceduralpages->addPage($this->data->parent,$doc->package,$doc->subpackage);
                    }
                    $this->pages[$this->data->parent->getPath()] = $this->data;
                }
                $this->private_page = false;
                $this->private_class = false;
                if (isset($this->db_template))
                {
                    addWarning(PDERROR_DB_TEMPLATE_UNTERMINATED);
                }
                unset($this->db_template);
                unset($this->last);
            } elseif ($data == PHPDOCUMENTOR_EVENT_END_DOCBLOCK_TEMPLATE)
            {
                unset($this->db_template);
            }
            //echo $this->state_lookup[$data] . "\n";
            //echo $data."\n";
        } 
         else 
        {
            if ($event == PHPDOCUMENTOR_EVENT_README_INSTALL_CHANGELOG)
            {
                $this->ric[$data[0]] = $data[1];
                return;
            }
            if ($event == PHPDOCUMENTOR_EVENT_DOCBLOCK_TEMPLATE)
            {
                $data->postProcess();
                $this->db_template = $data;
                $this->_lastDocBlockWasPageLevel = false;
                // 2nd docblock in a row, and it's at the top of the file, page-level docblock
                if ($this->type == "docblock" && $this->data->isClean())
                {
                    // can only have 1 package-level docblock, others are ignored
                    $this->data->clean = false;
                    if ($this->last->getKeyword('ignore'))
                    {
                        $this->proceduralpages->ignorePage($this->data->parent);
                        $this->private_page = true;
                        unset($this->last);
                        $this->privatepages[$this->data->parent->getPath()] = $this->data;
                        unset($this->pages[$this->data->parent->getPath()]);
                        return;
                    }
                    $this->data->setDocBlock($this->last);
                    $this->package = $this->data->parent->package = $this->last->package;
                    $this->subpackage = $this->data->parent->subpackage = $this->last->subpackage;
                    $this->proceduralpages->addPagePackage($this->data->parent->getPath(),$this->package,$this->subpackage);
                    if ($access = $this->last->getKeyword('access'))
                    {
                        if (is_object($access) && ($access->getString() == 'private') && (!$this->parsePrivate))
                        {
                            addWarning(PDERROR_PARSEPRIVATE, $this->data->parent->getPath());
                            $this->proceduralpages->ignorePage($this->data->parent);
                            $this->private_page = true;
                            unset($this->last);
                            $this->privatepages[$this->data->parent->getPath()] = $this->data;
                            unset($this->pages[$this->data->parent->getPath()]);
                            return;
                        }
                    }
                    if ($this->last->getKeyword('name'))
                    {
                        $a = $this->last->getKeyword('name');
                        if (is_object($a)) $a = $a->value;
                        $this->data->parent->setFile($a);
                        $this->proceduralpages->setName($a);
                    }
                    $this->addPage($this->data->parent, $this->data->parent->getPath());
                    if ($this->package)
                    {
                        $this->parsePackagePage($this->package, $this->data->parent->getPath());
                    }
                }
                unset($this->last);
            } else
            {
                $this->lasttype = $this->type;
                $type = $data->getType();
//                fancy_debug($type,$data);
                if (($type != 'page') && ($type != 'docblock') && ($type != 'packagepage') && ($type != 'tutorial'))
                {
                    $data->setFile($this->data->parent->getFile());
                }
                $this->type = $type;
                //echo $type . "\n";
                
                if (isset($this->event_handlers[$type]))
                {
                    $handle = $this->event_handlers[$type];
                    $this->$handle($event,$data);
                }
            } 
        }
    }
    
    /**
     * Replaces the {@link parserPage} represented by $this->pages[$path] with
     * $page
     *
     * Called by {@link addPageIfNecessary(), handleDocBlock()} and
     * {@link ProceduralPages::setupPages()}, this method first checks to see if
     * the page has been added.  If not, it assumes that the page has either
     * been @ignored or set with @access private with --parseprivate off, and
     * returns {@link addPrivatePage()}.  Otherwise, it sets the pages[$path] to
     * be the parserPage $page and sets the package and subpackage to that of
     * $page
     * @see $pages
     * @param parserPage
     * @param string full path to the file
     */
    function addPage($page, $path)
    {
        if (!isset($this->pages[$path])) return $this->addPrivatePage($page, $path);
        $this->pages[$path]->setParent($page);
        if ($page->package != $GLOBALS['phpDocumentor_DefaultPackageName'])
        {
            if (!$this->pages[$path]->docblock)
            {
                $docblock = new parserDocBlock;
                $docblock->package = $page->package;
                $docblock->subpackage = $page->subpackage;
                $this->pages[$path]->docblock = $docblock;
            } else
            {
                $this->pages[$path]->docblock->package = $page->package;
                $this->pages[$path]->docblock->subpackage = $page->subpackage;
            }
        }
    }
    
    /**
     * add a new {@link parserPage} to the $pages array if none is found
     *
     * This method is used when a page has been @ignored or marked with @access
     * private, and a public class is in the page (a class with no @access
     * private in its DocBlock).  The method first creates a new page in the
     * {@link $pages} array and then copies path information, and calls
     * {@link addPage()} to set up packages
     * @param string full path of page
     */
    function addPageIfNecessary($path, &$class)
    {
        global $_phpDocumentor_setting;
        if (!$this->parsePrivate)
        {
            if (!isset($this->pages[$path]))
            {
                $this->pages[$path] = new parserData;
                $this->pages[$path]->docblock = new parserDocBlock;
                $this->pages[$path]->docblock->package = $this->privatepages[$path]->docblock->package;
                $this->pages[$path]->docblock->subpackage = $this->privatepages[$path]->docblock->subpackage;
                $par = $this->privatepages[$path]->parent;
                $this->pages[$path]->setParent($par);
                $this->proceduralpages->addPage($par);
            }
        }
        if (!empty($_phpDocumentor_setting['packageoutput']))
            $packages = explode(',',$_phpDocumentor_setting['packageoutput']);
        if (!empty($_phpDocumentor_setting['packageoutput']) &&
            $this->pages[$path]->parent->package != $class->docblock->package &&
            !in_array($this->pages[$path]->parent->package,$packages))
        {
            $this->pages[$path]->parent->package = $class->docblock->package;
            $this->addPage($this->pages[$path]->parent, $path);
            $this->proceduralpages->addPage($this->pages[$path]->parent);
        }
    }
    
    /**
     * Adds a {@link parserPage} element to the {@link parserData} element in
     * $this->privatepages[$path]
     *
     * Performs a similar function to addPage, but adds to the
     * {@link $privatePages} array
     * @param parserPage $page
     * @param string $path full path to the page
     * @see addPage()
     */
    function addPrivatePage($page, $path)
    {
        $this->privatepages[$path]->setParent($page);
        if ($page->package != $GLOBALS['phpDocumentor_DefaultPackageName'])
        {
            if (!$this->privatepages[$path]->docblock)
            {
                $docblock = new parserDocBlock;
                $docblock->package = $page->package;
                $docblock->subpackage = $page->subpackage;
                $this->privatepages[$path]->docblock = $docblock;
            } else
            {
                $this->privatepages[$path]->docblock->package = $page->package;
                $this->privatepages[$path]->docblock->subpackage = $page->subpackage;
            }
        }
    }
    
    /**
     * adds a processed descendant of {@link parserElement} to the {@link $pages}
     * array or {@link $privatepages} array
     *
     * This function expects the page to exist in either $pages or $privatepages.  It calls the
     * {@link parserData::addElement()} method to add $element to the page.
     * @param parserElement $element this will actually be a descendant of parserElement
     * @param string $path
     */
    function addElementToPage($element, $path)
    {
        if (isset($this->privatepages[$path]))
        {
            if (isset($this->pages[$path]))
            {
                if ($element->type == 'class' || $element->type == 'method'
                    || $element->type == 'var' || $element->type == 'const')
                {
                    $this->pages[$path]->addElement($element);
                } else
                $this->privatepages[$path]->addElement($element);
            } else
            $this->privatepages[$path]->addElement($element);
        } else
        {
            if (isset($this->pages[$path]))
            {
                $this->pages[$path]->addElement($element);
            }
        }
    }
    
    /**
     * Add all the @uses tags from $element to the $uses array so that @usedby
     * virtual tags can be added
     * @uses parserUsesTag::getSeeElement() used to initialize {@link $uses}
     * @uses parserUsesTag::getDescription() used to initialize {@link $uses}
     * @param parserElement descendant of parserElement
     * @param string full path to the file 
     */
    function addUses($element, $path)
    {
        if ($element->type == 'page')
        {
            $element = $this->pages[$element->path];
        }
        if (!$this->parsePrivate && $element->docblock->hasaccess)
        {
            $a =  $element->docblock->getKeyword('access');
            if (is_object($a) && $a->getString() == 'private') return;
        }
        if (isset($this->privatepages[$path]))
        {
            if (isset($this->pages[$path]))
            {
//                if ($element->type == 'function' || $element->type == 'method')
                {
                    $uses = $element->docblock->getKeyword('uses');
                    if ($uses)
                    {
                        if (!is_array($uses)) $uses = array($uses);
                        foreach($uses as $use)
                        {
                            if (!is_object($use)) continue;
                            $el = $use->getSeeElement();
                            $description = $use->getDescription();
                            $this->uses[$el][] = array($element, $description);
                        }
                    }
                }
            }
        } else
        {
            if (isset($this->pages[$path]))
            {
//                if ($element->type == 'function' || $element->type == 'method')
                {
                    $uses = $element->docblock->getKeyword('uses');
                    if ($uses)
                    {
                        if (!is_array($uses)) $uses = array($uses);
                        foreach($uses as $use)
                        {
                            if (!is_object($use)) continue;
                            $el = $use->getSeeElement();
                            $description = $use->getDescription();
                            $this->uses[$el][] = array($element, $description);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Add a {@link parserUsedByTag} link to every element referred to by @uses
     * @param Converter temporary converter used to retrieve abstract links
     * @uses phpDocumentor_IntermediateParser::addUses() indirectly, as
     *       addUses() sets up $uses, which is iterated over here
     * @uses $pages sets up all @usedby tags from here
     * @access private
     */
    function _setupUsesList(&$converter)
    {
        ob_start();
        $converter->_createPkgElements($this->pages);
        ob_end_clean();
        ksort($this->uses);
        foreach($this->uses as $link => $elements)
        {
            foreach($elements as $element)
            {
                if ($element[0]->type == 'method' || $element[0]->type == 'var' ||
                    $element[0]->type == 'const')
                {
                    $converter->class = $element[0]->getClass();
                }
                if ($element[0]->type == 'class')
                {
                    $converter->class = $element[0]->getName();
                }
                $reallink = $converter->getLink($link,$element[0]->docblock->package);
                if (is_object($reallink))
                {
                    // add a used by tag to the docblock of the destination
                    switch(phpDocumentor_get_class($reallink))
                    {
                        case 'pagelink' :
                            $this->pages[$reallink->path]->docblock->addUsedBy(
                                $element[0]->getLink($converter, false, true),
                                $element[1]);
                        break;
                        case 'functionlink' :
                        case 'definelink' :
                        case 'globallink' :
                        if (isset($this->pages[$reallink->path]))
                        {
                            for ($i=0;
                                 $i<count($this->pages[$reallink->path]->elements);
                                 $i++) {
                                if ($this->pages[$reallink->path]->elements[$i]->type ==
                                      str_replace('link', '',
                                      phpDocumentor_get_class($reallink)) &&
                                      $this->pages[$reallink->path]->
                                      elements[$i]->getName() 
                                      == $reallink->name) {
                                    $this->pages[$reallink->path]->elements[$i]->
                                    docblock->addUsedBy(
                                        $element[0]->getLink($converter,false,true),
                                        $element[1]);
//                                   debug('added @usedby to '.str_replace('link','',phpDocumentor_get_class($reallink)).' '.$reallink->name);
                                }
                            }
                        }
                        break;
                        case 'classlink' :
                        case 'methodlink' :
                        case 'varlink' :
                        case 'constlink' :
                        if (isset($this->pages[$reallink->path]))
                        {
                            for ($i=0;$i<count($this->pages[$reallink->path]->classelements);$i++)
                            {
                                if ($this->pages[$reallink->path]->classelements[$i]->type ==
                                      str_replace('link','',phpDocumentor_get_class($reallink)) &&
                                      $this->pages[$reallink->path]->classelements[$i]->getName() == $reallink->name &&
                                      (!isset($reallink->class) || 
                                      $this->pages[$reallink->path]->classelements[$i]->getClass() == $reallink->class))
                                {
                                    $this->pages[$reallink->path]->classelements[$i]->docblock->addUsedBy($element[0]->getLink($converter,false,true), $element[1]);
//                                   debug('added @usedby to '.str_replace('link','',phpDocumentor_get_class($reallink)).' '.$reallink->name);
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Interface to the Converter
     *
     * This function simply passes {@link $pages} and {@link package_pages} to
     * the walk() method, and then calls the Output() method.  Note that
     * Output() is not required to do anything, and in fact doesn't in
     * HTMLframesConverter.
     * @uses Converter::walk() passes {@link $pages} and {@link $package_pages}
     * @uses Converter::Output()
     */
    function Convert($title, $converter)
    {
        $converter->walk($this->pages, $this->package_pages);
        $converter->Output($title);
        $converter->cleanup();
    }
    
    /**
     * Clean up classes
     *
     * {@source}
     * @access private
     * @uses Classes::Inherit() passes $this
     */
    function fixClasses()
    {
        $this->classes->Inherit($this);
    }
    
    /**
     * Clean up Procedural Pages
     * {@source}
     * @access private
     * @uses ProceduralPages::setupPages() passes $this
     */
    function fixProcPages()
    {
        $this->proceduralpages->setupPages($this);
    }
    
    /**
     * If the parent class of $class is in a different package, adds it to the
     * {@link $package_parents} array
     * @param parserClass &$class
     */
    function addPackageParent(&$class)
    {
        if (!is_array($class->parent)) return;
        $par = $this->classes->getClass($class->parent[1], $class->parent[0]);
        if ($class->docblock->package == $par->docblock->package) return;
        $this->package_parents[$class->docblock->package] = $par->docblock->package;
        if (!isset($this->package_parents[$par->docblock->package]) || !$this->package_parents[$par->docblock->package]) $this->package_parents[$par->docblock->package] = false;
    }
    
    /**
     * Add a converter name to use to the list of converters
     *
     * Sets up the {@link $converters} array.
     * {@internal
     * First, the Converter's file is included, and then, if successful,
     * the converter classname is tested for existance.  If all is good,
     * then the templates are added to the list of converters/templates to use}}
     * @param string $output output format (HTML, PDF, XML).  Must be all caps
     * @param string $name Converter name (frames, for example, is the name of
     *                     HTMLframesConverter)
     * @param string $template template to use, should be a relative path to the
     *                         templates dir (like DOM/default)
     */
    function addConverter($output,$name,$template)
    {
        if ($this->templateBase) {
            $templateBase = str_replace('\\','/', $this->templateBase) . '/Converters';
        } else {
            if ('@PEAR-DIR@' != '@'.'PEAR-DIR@') {
                $templateBase = 'PhpDocumentor/phpDocumentor/Converters';
            } else {
                $templateBase = str_replace('\\','/',$GLOBALS['_phpDocumentor_install_dir']) . '/phpDocumentor/Converters';
            }
        }
        if (strpos($name,PATH_DELIMITER))
        {
            // include the parent template
            $parent = explode(PATH_DELIMITER,$name);
            $parent = $parent[0];
            if (!class_exists($output . $parent . 'Converter')) {
                $filename = $templateBase . '/' . $output . '/' . $parent . '/' . $output
                    . $parent . 'Converter.inc';
                if (Io::isIncludeable($filename))
                {
                    include_once($filename);
                }
            }
            if (!class_exists($output . $parent . 'Converter'))
            {
                addError(PDERROR_CONVERTER_NOT_FOUND,"parent Converter ".$output . $parent . "Converter of child Converter ".$output . str_replace(PATH_DELIMITER,'',$name) . "Converter");
            }
        }
        $filename = $templateBase .
             PATH_DELIMITER . $output . PATH_DELIMITER . $name . PATH_DELIMITER . $output .
             str_replace(PATH_DELIMITER, '', $name) . "Converter" . ".inc";
        if (Io::isIncludeable($filename))
        {
            include_once($filename);
        }
        if (class_exists($output . str_replace(PATH_DELIMITER,'',$name) . 'Converter'))
        {
            $this->converters[$output][$output . str_replace(PATH_DELIMITER,'',$name) . "Converter"][] = $template;
        } else
        {
            addError(PDERROR_CONVERTER_NOT_FOUND,$output . str_replace(PATH_DELIMITER,'',$name) . "Converter");
        }
    }

    /**
     * does a natural case sort on two {@link parserElement} descendants
     *
     * @param    mixed    $a
     * @param    mixed    $b
     * @return    int
     * @see        generateElementIndex()
     */
    function elementCmp ($a, $b)
    {
        return strnatcasecmp($a->getName(), $b->getName());
    }
    
    /**
     * does a natural case sort on two class elements (either
     * {@link parserClass, parserMethod} or {@link parserVar}
     *
     * @param    mixed    $a
     * @param    mixed    $b
     * @return    int
     * @see        generateElementIndex()
     */
    function ClasselementCmp ($a, $b)
    {
        if (phpDocumentor_get_class($a) == 'parserclass') $atest = $a->name; else $atest = $a->class;
        if (phpDocumentor_get_class($b) == 'parserclass') $btest = $b->name; else $btest = $b->class;
        
        if(($c = strnatcasecmp($atest, $btest)) != 0) return $c;
        if (phpDocumentor_get_class($a) != 'parserclass') $atest .= $a->name;
        if (phpDocumentor_get_class($b) != 'parserclass') $btest .= $b->name;
        if (phpDocumentor_get_class($a) == 'parsermethod' && phpDocumentor_get_class($b) == 'parsermethod')
        {
            if ($a->isConstructor) return -1;
            if ($b->isConstructor) return 1;
            if ($a->isDestructor) return -1;
            if ($b->isDestructor) return 1;
        }
        return strnatcasecmp($atest,$btest);
    }
    
    /**
     * call this method once parsing has completed.
     *
     * This method calls the private methods fixClasses and fixProcPages, both
     * of which adjust inheritance and package information based on complicated
     * post-parsing rules described in {@link ProceduralPages::setupPages()}
     * and {@link Classes::Inherit()}.  Then, it sorts elements of the $pages
     * array and calls Convert for each Converter in the $converters array
     * @see $converters
     * @see $pages
     * @see Convert()
     */
    function Output ($title = "Generated Documentation")
    {
        $GLOBALS['phpDocumentor_errors']->curfile = false;
        $this->fixClasses();
        $this->fixProcPages();
//        var_dump($this->uses);
//        exit;
        phpDocumentor_out("\nSorting page elements...");
        flush();
        uasort($this->pages,'pagesort');
        foreach($this->pages as $i => $page)
        {
            usort($this->pages[$i]->elements,array($this,'elementCmp'));
            usort($this->pages[$i]->classelements,array($this,'ClasselementCmp'));
        }
        phpDocumentor_out("done\n");
        flush();
        $complicatedout = false;
        if (is_array($this->converters))
        {
            if (count($this->converters) > 1)
            {
                $complicatedout = true;
            }
            phpDocumentor_out("Formatting @uses list...");
            flush();
            $a = new __dummyConverter($this->all_packages, $this->package_parents, $this->classes, $this->proceduralpages, $this->packageoutput, $this->parsePrivate, $this->quietMode, $this->targetDir , '', $this->title);
            $this->_setupUsesList($a);
            unset($a);
            phpDocumentor_out("done\n\n");
            flush();
            foreach($this->converters as $converter => $blah)
            {
                if (is_array($blah))
                {
                    if (count($blah) > 1)
                    {
                        $complicatedout = true;
                    }
                    foreach($blah as $converter => $templates)
                    {
                        foreach($templates as $template)
                        {
                            $extraout = '';
                            if ($complicatedout)
                            {
                                $extraout = SMART_PATH_DELIMITER . $converter;
                            }
                            if (count($templates) > 1)
                            {
                                $extraout .= SMART_PATH_DELIMITER . str_replace(PATH_DELIMITER, SMART_PATH_DELIMITER, substr($template,0,strlen($template) - 1));
                            }
                            $a = new $converter($this->all_packages, $this->package_parents, $this->classes, $this->proceduralpages, $this->packageoutput, $this->parsePrivate, $this->quietMode, $this->targetDir . $extraout, $template, $this->title);
                            if (isset($this->templateBase))
                            {
                                $a->setTemplateBase($this->templateBase, $template);
                            }
                            $a->ric = $this->ric;
                            $a->packagecategories = $this->packagecategories;
                            if (isset($this->tutorials)) $a->setTutorials($this->tutorials);
                            $this->Convert($title, $a);
                            unset($a);
                        }
                    }
                }
            }
        } else
        {
            addErrorDie(PDERROR_NO_CONVERTERS);
        }
    }

    /**
     * Sets the output directory
     *
     * @param string $dir the output directory
     */
    function setTargetDir($dir)
    {
        $this->targetDir = $dir;
    }

    /**
     * Sets the template base directory
     *
     * @param string $dir the template base directory
     * @tutorial phpDocumentor.howto.pkg#using.command-line.templatebase
     */
    function setTemplateBase($dir)
    {
        $this->templateBase = $dir;
    }

    /**
     * set parsing information output mode (quiet or verbose)
     *
     * If set to false, no parsing information (parsing /php/file/thisfile.php,
     * Converting etc.) will be displayed.
     * Useful for cron jobs
     * @param    bool $quietMode
     */
    function setQuietMode($quietMode)
    {
        $this->quietMode = $quietMode;
    }
    
    /**
     * set display of elements marked with @access private
     *
     * If set to true, elements will be displayed
     * @param    bool $parse
     */
    function setParsePrivate($parse)
    {
        $this->parsePrivate = $parse;
    }
}

/** @access private */
function pagesort($a, $b)
{
    return strnatcasecmp($a->parent->file,$b->parent->file);
}
?>
