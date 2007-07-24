<?php
/**
 * Intermediate class parsing structure.
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
 * @package    phpDocumentor
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2001-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: Classes.inc,v 1.9 2006/04/30 22:18:13 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        parserDocBlock, parserInclude, parserPage, parserClass
 * @see        parserDefine, parserFunction, parserMethod, parserVar
 * @since      1.0rc1
 */
/**
 * Intermediate class parsing structure.
 *
 * The {@link phpDocumentor_IntermediateParser} class uses this class and its
 * cousin, {@link ProceduralPages} to organize all parsed source code elements.
 * Data is fed to each immediately after it is parsed, and at conversion time,
 * everything is organized.
 *
 * The Classes class is responsible for all inheritance, including resolving
 * name conflicts between classes, determining which classes extend other
 * classes, and is responsible for all inheritance of documentation.
 * {@internal
 * This structure parses classes, vars and methods by file, and then iterates
 * over the class tree to set up inheritance.  The {@link Inherit()}
 * method is the meat of the class, and processes the class trees from root to
 * branch, ensuring that parsing order is unimportant.}}
 * @package phpDocumentor
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Id: Classes.inc,v 1.9 2006/04/30 22:18:13 cellog Exp $
 */
class Classes
{
    /**#@+
     * @access private
     */
    /**
     * file being parsed, used in every add function to match up elements with 
     * the file that contains them
     *
     * This variable is used during parsing to associate class elements added
     * to the data structures that contain them with the file they reside in
     * @see addClass(), addMethod(), addVar(), nextFile()
     * @var string
     */
    var $curfile;
    /**
     * class being parsed, used to match up methods and vars with their parent
     * class
     *
     * This variable is used during parsing to associate class elements added
     * to the data structures that contain them with the file they reside in
     * @see addMethod(), addVar()
     * @var string
     */
    var $curclass;
    
    /**
     * Used when a definite match is made between a parent class and a child
     * class
     *
     * This variable is used in post-parsing.
     *
     * Format: array(parent => array(parentfile => array(child => childfile)))
     * @var array
     */
    var $definitechild;
    /**
     * array of parsed classes organized by the name of the file that contains
     * the class.
     *
     * Format:
     * array(filename => array(classname => {@link parserClass}))
     * @var array
     */
    var $classesbyfile = array();
    /**
     * array of file names organized by classes that are in the file.
     *
     * This structure is designed to handle name conflicts.  Two files can
     * contain classes with the same name, and this array will record both
     * filenames to help control linking and inheritance errors
     *
     * Format:<pre>
     * array(classname => array(name of file containing classname,
     *                          name of file 2 containing classname...)</pre>
     * @var array
     */
    var $classesbynamefile = array();
    /**
     * array of parsed methods organized by the file that contains them.
     *
     * Format:<pre>
     * array(filename => array(classname => array({@link parserMethod} 1, 
     *                                            {@link parserMethod} 2,...))</pre>
     * @var array
     */
    var $methodsbyfile = array();
    /**
     * array of parsed vars organized by the file that contains them.
     *
     * Format:
     * array(filename => array(classname => array({@link parserVar} 1, {@link parserVar} 2,...))
     * @var array
     */
    var $varsbyfile = array();
    /**
     * array of parsed class constants organized by the file that contains them.
     *
     * Format:
     * array(filename => array(classname => array({@link parserConst} 1, {@link parserConst} 2,...))
     * @var array
     */
    var $constsbyfile = array();
    /**
     * keeps track of extend declarations by file, used to find inheritance
     *
     * Format:
     * array(filename => array(classname => parentclassname))
     * @var array
     */
    var $extendsbyfile = array();
    /**
     * Keeps track of child classes by file.
     * Since phpDocumentor can document collections of files that contain name
     * conflicts (PHP would give a fatal error), it
     * is impossible to assume a class that declares "extends foo" necessarily
     * extends the class foo in file X.  It could be an
     * extended class of class foo in file Y.  Because of this, phpDocumentor
     * relies on packaging to resolve the name conflict
     * This array keeps track of the packages of a child class
     *
     * Format:
     * array(parentclassname => array(filename => array(childclassname =>
     *                                      array(packagename, packagename)))
     * @var array
     */
    var $classchildrenbyfile = array();
    /**
     * Keeps track of class packages found in a file.
     * This is used in {@link getParentClass()} to determine the number of
     * packages in a file, in order to resolve inheritance issues
     * Format: array(filename => array(packagename1, packagename2,...))
     * @var array
     */
    var $classpackagebyfile = array();
    /**
     * a tree of class inheritance by name.
     *
     * format:<pre>
     * array(childname => parentname,
     *       childname1 => parentname1,
     *       rootname => 0, ...
     *      )</pre>
     * @var array
     * @see Converter::generateSortedClassTreeFromClass()
     */
    var $classparents = array();
    /**
     * Keeps track of package and subpackage for each class name, organized
     * by package
     *
     * Format:<pre>
     * array(classname => array(path => array(package,subpackage),
     *                          path2 => array(package,subpackage),...))</pre>
     * @var array
     */
    var $classpathpackages = array();
    /**
     * used to delete duplicates in the same package to avoid documentation errors
     *
     * Specifically used in {@link Converter::checkKillClass()}
     */
    var $killclass = array();
    /**
     * array of methods by package and class
     *
     * format:<pre>
     * array(packagename =>
     *         array(classname =>
     *               array(methodname1 => {@link parserMethod} class,
     *                     methodname2 => {@link parserMethod} class,...)
     *                      )
     *              )
     *      )</pre>
     * @var array
     * @see Converter
     */
    var $methods = array();
    
    /**
     * array of class variables by package and class
     *
     * format:<pre>
     * array(packagename =>
     *         array(classname =>
     *                array(variablename1 => {@link parserVar} class,
     *                      variablename2 => {@link parserVar} class,...
     *                     )
     *              )
     *      )</pre>
     * @var array
     * @see Converter
     */
    var $vars = array();
    
    /**
     * array of class variables by package and class
     *
     * format:<pre>
     * array(packagename =>
     *         array(classname =>
     *                array(constname1 => {@link parserConst} class,
     *                      constname2 => {@link parserConst} class,...
     *                     )
     *              )
     *      )</pre>
     * @var array
     * @see Converter
     */
    var $consts = array();
    /**
     * Reverse class_packages_by_file, used to prevent duplicates
     * @var array Format: array(packagename => 1)
     */
    var $revcpbf = array();
    /**
     * All classes with no parents (no extends clause) are tracked in this array
     * by the file that contains them.
     *
     * Format:<pre>
     * array(classname => array(name of file1 that contains root classname,
     *                          name of file2 that contains root classname,...))</pre>
     * @var array
     */
    var $roots = array();
    /**
     * All classes with a parent that was not parsed are included in this array
     *
     * Format:<pre>
     * array(classname => array(name of file1 that contains root classname,
     *                          name of file2 that contains root classname,...))</pre>
     * @var array
     */
    var $specialRoots = array();
    
    /**
     * array of all files that contain classes with the same name
     * @var array Format: (classname => array(path1, path2,...))
     */
    var $potentialclassconflicts = array();
    
    /**
     * array of all inter-package name conflicts of classes
     *
     * This array allows documentation of PHP namespace conflicts that would
     * occur should a user try to include these files in the same file
     * @var array Format: (classname => array(path1, path2,...))
     */
    var $classconflicts = array();
    /**#@-*/
    /**
     * While parsing, add a class to the list of parsed classes
     *
     * sets up the {@link $classesbyfile, $classesbynamefile, $extendsbyfile},
     * {@link $classchildrenbyfile, $roots} arrays, and sets {@link $curclass}
     * @param parserClass &$element element is a {@link parserClass}
     * @uses addPackageToFile() marks the current class's package as being
     *                          present in a file
     */
    function addClass(&$element)
    {
        $this->curclass = $element->getName();
        $element->curfile = $this->curfile;
        if (isset($this->classesbyfile[$this->curfile][$this->curclass]))
        {
            addWarning(PDERROR_ELEMENT_IGNORED,'class',$this->curclass,$this->curfile);
            $this->curclass = false;
            return;
        }
        $this->classesbyfile[$this->curfile][$this->curclass] = $element;
        $this->classesbynamefile[$this->curclass][] = $this->curfile;
        $this->extendsbyfile[$this->curfile][$this->curclass] = $element->getExtends();
        $this->classchildrenbyfile[$element->getExtends()][$this->curfile][$this->curclass][] = $element->docblock->package;
        if ($element->docblock->getExplicitPackage())
        $this->addPackageToFile($element->docblock->package);
        if (!$element->getExtends())
        {
            $this->roots[$this->curclass][] = $this->curfile;
        }
    }
    
    /**
     * While parsing, add a method to the list of parsed methods
     *
     * sets up the {@link $methodsbyfile} array using {@link $curfile} and
     * {@link $curclass}
     * @param parserMethod &$element element is a {@link parserMethod}
     */
    function addMethod(&$element)
    {
        if (!$this->curclass) return;
        $this->methodsbyfile[$this->curfile][$this->curclass][] = $element;
    }
    
    /**
     * While parsing, add a variable to the list of parsed variables
     *
     * sets up the {@link $varsbyfile} array using {@link $curfile} and {@link $curclass}
     * @param parserVar &$element element is a {@link parserVar}
     */
    function addVar(&$element)
    {
        if (!$this->curclass) return;
        $this->varsbyfile[$this->curfile][$this->curclass][] = $element;
    }
    
    /**
     * While parsing, add a variable to the list of parsed variables
     *
     * sets up the {@link $constsbyfile} array using {@link $curfile} and {@link $curclass}
     * @param parserConst &$element element is a {@link parserConst}
     */
    function addConst(&$element)
    {
        if (!$this->curclass) return;
        $this->constsbyfile[$this->curfile][$this->curclass][] = $element;
    }
    
    /**
     * Prepare to parse a new file
     *
     * sets {@link $curfile} to $file and {@link $curclass} to false (no class being parsed)
     * @param string $file file currently being parsed
     */
    function nextFile($file)
    {
        $this->curfile = $file;
        $this->curclass = false;
    }
    
    /**
     * Mark a package as being used in a class
     *
     * {@source}
     * @param string $package package name
     */
    function addPackageToFile($package)
    {
        if (!isset($this->revcpbf[$this->curfile][$package]))
        $this->classpackagebyfile[$this->curfile][] = $package;
        $this->revcpbf[$this->curfile][$package] = 1;
    }
    
    /**
     * Find the parent class of $class, and set up structures to note this fact
     *
     * Modifies the {@link parserClass} element in {@link $classesbyfile} to use
     * the parent's package, and inherit methods/vars
     * @param string $class child class to find parent class
     * @param string $file file child class is located in
     * @uses $definitechild if a match is made between a parent class and parameter
     *                      $class in file $file, then definitechild is set here
     * @uses getParentClass() to find the parent class
     */
    function setClassParent($class,$file)
    {
        if (is_array($par = $this->getParentClass($class,$file)))
        {
//            phpDocumentor_out("$file class $class extends ".$par[1]." file ".$par[0]."\n");
            $this->classesbyfile[$file][$class]->setParent($par[1],$par[0],$this);
            $this->definitechild[$par[1]][$par[0]][$class] = $file;
        } else
        {
            $this->classesbyfile[$file][$class]->setParentNoClass($par);
        }
    }
    
    /**
     * Main processing engine for setting up class inheritance.
     *
     * This function uses {@link $roots} to traverse the inheritance tree via
     * {@link processChild()} and returns the data structures
     * phpDocumentor_IntermediateParser needs to convert parsed data
     * to output using {@link phpDocumentor_IntermediateParser::Convert()}
     * @param phpDocumentor_IntermediateParser
     * @uses processChild() set up inheritance
     */
    function Inherit(&$render)
    {
        phpDocumentor_out("\nProcessing Class Inheritance\n\n");
        flush();
        phpDocumentor_out("\nProcessing Root Trees\n\n");
        flush();
        foreach($this->roots as $class => $files)
        {
            for ($i=0; $i<count($files); $i++)
            {
                $this->processChild($render,$class,$files[$i]);
            }
        }
        if (0)
        foreach($this->classesbyfile as $i => $j)
        {
            foreach($j as $k => $m)
            {
                var_dump($i,$k);
                if ($i == 'iConverter')
                {
                    var_dump($j);
                }
            }
        }
        phpDocumentor_out("\nProcessing leftover classes (classes that extend root classes not found in the same package)\n");
        flush();
        foreach($this->classesbyfile as $i => $j)
        {
            foreach($j as $k => $m)
            {
                $this->processChild($render,$k,$i,true);
            }
        }
        phpDocumentor_out("done processing leftover classes\n");
        flush();
        $this->setupClassConflicts();
    }
    
    /**
     * Transfers actual conflicts from {@link $potentialClassconflicts} to
     * {@link $classconflicts}
     * @access private
     * @uses $potentialclassconflicts transfers values to {@link $classconflicts}
     */
    function setupClassConflicts()
    {
        foreach($this->potentialclassconflicts as $class => $paths)
        {
            if (count($paths) - 1)
            { //conflict
                $package = array();
                foreach($paths as $path)
                {
                    // create a list of conflicting classes in each package
                    if (isset($this->classpathpackages[$class][$path]))
                    $package[$this->classpathpackages[$class][$path][0]][] = $path;
                }
                foreach($package as $pathpackages)
                {
                    // if at least 2 functions exist in the same package, delete all but the first one and add warnings
                    if (count($pathpackages) - 1)
                    {
                        for($i=1; $i < count($pathpackages); $i++)
                        {
                            if (isset($this->classesbyfile[$pathpackages[$i]]))
                            {
                                addWarning(PDERROR_ELEMENT_IGNORED,'class',$class,$pathpackages[$i]);
                                $this->killClass($class,$pathpackages[$i]);
                                $oth = array_flip($paths);
                                unset($paths[$oth[$pathpackages[$i]]]);
                            }
                        }
                    }
                }
                $this->classconflicts[$class] = $paths;
            }
        }
    }
    
    /**
     * If a package contains two classes with the same name, this function finds
     * that conflict
     *
     * Returns the {@link $classconflicts} entry for class $class, minus its own path
     * @return mixed returns false if no conflicts, or an array of paths containing conflicts
     */
    function getConflicts($class)
    {
        if (!isset($this->classconflicts[$class])) return false;
        $a = array();
        foreach($this->classconflicts[$class] as $conflict)
        {
            $a[$this->classesbyfile[$conflict][$class]->docblock->package] = $this->classesbyfile[$conflict][$class];
        }
        return $a;
    }
    
    /**
     * sets up {@link $killclass} for use by Converter::checkKillClass()
     * @access private
     */
    function killClass($class,$path)
    {
        $this->killclass[$class][$path] = true;
    }
    
    /**
     * This function recursively climbs up the class tree, setting inherited
     * information like package and adds the elements to phpDocumentor_IntermediateParser.
     *
     * Using structures defined in {@link Classes}, the function first sets package information,
     * and then seeks out child classes.
     * It uses 3 tests to determine whether a class is a child class.
     * <ol>
     *    <li>child class is in the same file as the parent class and extends parent class</li>
     *    <li>child class is in a different file and specifies the parent's @package in its docblock</li>
     *    <li>child class is in a different file and is in a different @package, with one possible parent class</li>
     * </ol>
     * @param phpDocumentor_IntermediateParser &$render
     * @param string $class class to process
     * @param string $file name of file $class is located in
     * @param boolean $furb flag used privately to control informational output while parsing
     *                            (used when processing leftover classes in {@link Inherit()}
     * @global    string    default package, usually "default"
     */
    function processChild(&$render,$class,$file,$furb = false)
    {
        global $phpDocumentor_DefaultPackageName;
        if (isset($this->classesbyfile[$file][$class]->processed)) return;
        $this->potentialclassconflicts[$class][] = $file;
        if ($furb) phpDocumentor_out("Processing $class in file $file\n");
        flush();
        $this->classesbyfile[$file][$class]->processed = true;
        $db = $this->classesbyfile[$file][$class];
        $render->addUses($db,$file);
        if (!$render->parsePrivate)
        {
            // if this class has an @access private, and parse private is disabled, remove it
            if ($db->docblock->hasaccess)
            {
                $aaa = $db->docblock->getKeyword('access');
                if (is_object($aaa) && $aaa->getString() == 'private')
                {
                    if (isset($this->varsbyfile[$file]) && isset($this->varsbyfile[$file][$class]))
                    {
                        unset($this->varsbyfile[$file][$class]);
                    }
                    if (isset($this->methodsbyfile[$file]) && isset($this->methodsbyfile[$file][$class]))
                    {
                        unset($this->methodsbyfile[$file][$class]);
                    }
                    if (isset($this->constsbyfile[$file]) && isset($this->constsbyfile[$file][$class]))
                    {
                        unset($this->constsbyfile[$file][$class]);
                    }
                    $this->classesbyfile[$file][$class]->ignore = true;
                    // if this is a root class, remove it from the roots array
                    if (isset($this->roots[$class]))
                    foreach($this->roots[$class] as $i => $files)
                    {
                        // find the file key and unset
                        if ($files == $file) unset($this->roots[$class][$i]);
                    }
                    // if this is a child, remove it from the list of child classes of its parent
                    if ($db->getExtends()) unset($this->classchildrenbyfile[$db->getExtends()][$file]);
                    return;
                }
            }
        }
        if ($render->packageoutput)
        {
            if (!in_array($db->docblock->package,$render->packageoutput))
            {
                if (isset($this->varsbyfile[$file]) && isset($this->varsbyfile[$file][$class]))
                {
                    unset($this->varsbyfile[$file][$class]);
                }
                if (isset($this->methodsbyfile[$file]) && isset($this->methodsbyfile[$file][$class]))
                {
                    unset($this->methodsbyfile[$file][$class]);
                }
                if (isset($this->constsbyfile[$file]) && isset($this->constsbyfile[$file][$class]))
                {
                    unset($this->constsbyfile[$file][$class]);
                }
                $this->classesbyfile[$file][$class]->ignore = true;
                if (isset($this->roots[$class]))
                foreach($this->roots[$class] as $i => $files)
                {
                    if ($files == $file) unset($this->roots[$class][$i]);
                }
                if ($db->getExtends()) unset($this->classchildrenbyfile[$db->getExtends()][$file]);
                return;
            }
        }
        $this->setClassParent($class,$file);
        $db = $this->classesbyfile[$file][$class];
        if ($furb && !is_array($db->parent))
        {
//            debug("furb adding $class $file to roots");
            $this->specialRoots[$db->parent][] = array($class, $file);
        }
        // fix for 591396
        if (!$db->docblock->getExplicitPackage())
        {
            $a = $render->proceduralpages->pagepackages[$file];
            if ($a[0] != $phpDocumentor_DefaultPackageName)
            {
                // inherit page package
                $this->classesbyfile[$file][$class]->docblock->package = $a[0];
            }
        }
        if ($this->classesbyfile[$file][$class]->docblock->package == $render->proceduralpages->pagepackages[$file][0])
        {
            if ($this->classesbyfile[$file][$class]->docblock->subpackage == '')
                $this->classesbyfile[$file][$class]->docblock->subpackage = $render->proceduralpages->pagepackages[$file][1];
        }
        $db = $this->classesbyfile[$file][$class];
        $render->addPackageParent($db);
        $render->addPageIfNecessary($file, $db);
        if ($access = $db->docblock->getKeyword('access'))
        {
            if (!is_string($access) && is_object($access)) $access = $access->getString();
            if (!is_string($access)) $access = 'public';
            if (($access == 'private') && (!$render->parsePrivate))
            {
                if (isset($this->varsbyfile[$file]) && isset($this->varsbyfile[$file][$class]))
                foreach($this->varsbyfile[$file][$class] as $i => $vr)
                {
                    $vr->docblock->addKeyword('access','private');
                    $this->varsbyfile[$file][$class][$i] = $vr;
                }
                if (isset($this->methodsbyfile[$file]) && isset($this->methodsbyfile[$file][$class]))
                foreach($this->methodsbyfile[$file][$class] as $i => $vr)
                {
                    $vr->docblock->addKeyword('access','private');
                    $this->methodsbyfile[$file][$class][$i] = $vr;
                }
                if (isset($this->constsbyfile[$file]) && isset($this->constsbyfile[$file][$class]))
                foreach($this->constsbyfile[$file][$class] as $i => $vr)
                {
                    $vr->docblock->addKeyword('access','private');
                    $this->constsbyfile[$file][$class][$i] = $vr;
                }
            }
        }
        $this->classpathpackages[$class][$file] = array($db->docblock->package,$db->docblock->subpackage);
        if ($db->docblock->getExplicitPackage())
        $render->proceduralpages->addClassPackageToFile($file,$db->docblock->package,$db->docblock->subpackage);
        $render->addElementToPage($db,$file);
        if (isset($this->varsbyfile[$file]) && isset($this->varsbyfile[$file][$class]))
        foreach($this->varsbyfile[$file][$class] as $i => $vr)
        {
            $vr->docblock->package = $db->docblock->package;
            $vr->docblock->subpackage = $db->docblock->subpackage;
            $render->addElementToPage($vr,$file);
            $render->addUses($vr,$file);
            $this->varsbyfile[$file][$class][$i] = $vr;
            $this->vars[$db->docblock->package][$class][$vr->getName()] = $vr;
        }
        if (isset($this->methodsbyfile[$file]) && isset($this->methodsbyfile[$file][$class]))
        foreach($this->methodsbyfile[$file][$class] as $i => $vr)
        {
            $vr->docblock->package = $db->docblock->package;
            $vr->docblock->subpackage = $db->docblock->subpackage;
            $render->addElementToPage($vr,$file);
            $render->addUses($vr,$file);
            $this->methodsbyfile[$file][$class][$i] = $vr;
            $this->methods[$db->docblock->package][$class][$vr->getName()] = $vr;
        }
        if (isset($this->constsbyfile[$file]) && isset($this->constsbyfile[$file][$class]))
        foreach($this->constsbyfile[$file][$class] as $i => $vr)
        {
            $vr->docblock->package = $db->docblock->package;
            $vr->docblock->subpackage = $db->docblock->subpackage;
            $render->addElementToPage($vr,$file);
            $render->addUses($vr,$file);
            $this->constsbyfile[$file][$class][$i] = $vr;
            $this->methods[$db->docblock->package][$class][$vr->getName()] = $vr;
        }
        $this->classpackages[$class][] = array($db->docblock->package,$db->docblock->subpackage);
        if (is_array($db->parent))
        $this->classparents[$db->docblock->package][$class] = $db->parent[1];
        else
        $this->classparents[$db->docblock->package][$class] = $db->getExtends();
        if (is_array($db->parent))
        {
            $z = $this->getClass($db->parent[1],$db->parent[0]);
            $this->classchildren[$z->docblock->package][$db->parent[1]][] = $db;
        }
        if (isset($this->classchildrenbyfile[$class]))
        {
            foreach($this->classchildrenbyfile[$class] as $childfile => $other)
            {
                // test 1, inherits in same file (must be same package)
                if ($childfile == $file)
                {
                    foreach($other as $child => $packages)
                    {
//                        debug("parent $class same file $child");
                        $this->processChild($render,$child,$childfile);
                        $x = $this->getClass($child,$childfile);
                        if ($x->docblock->package != $GLOBALS['phpDocumentor_DefaultPackageName'])
                        {
                            // child package need root for class trees
                            if ($x->docblock->package != $db->docblock->package)
                            {
//                            debug("adding $child in $childfile 1");
                                $this->roots[$child][] = $childfile;
                            }
                        }
                    }
                } else
                {
                // test 2, different file, same package
                    foreach($other as $child => $packages)
                    {
                        for($j=0; $j<count($packages); $j++)
                        {
                            if ($this->classesbyfile[$file][$class]->docblock->package == $packages[$j])
                            {
                                $this->processChild($render,$child,$childfile);
//                                debug("$childfile diff file $child, parent $class, same package ".$packages[$j]);
                            } else
                            {
                                // test 3, different file, different package, only 1 parent is possible
                                if (isset($this->classesbynamefile[$child]))
                                {
                                    // 1 possible parent
                                    if (count($this->classesbynamefile[$class]) == 1)
                                    {
//                                        debug("$childfile diff file $child, diff package, 1 possible parent root $class");
                                        $this->processChild($render,$child,$childfile);
                                        $x = $this->getClass($child,$childfile);
                                        if ($x->docblock->package != $GLOBALS['phpDocumentor_DefaultPackageName'])
                                        {
                                            // child package need root for class trees
                                            if ($x->docblock->package != $db->docblock->package)
                                            {
//                                                debug("adding roots $child in $childfile 2");
                                                $this->roots[$child][] = $childfile;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Get the parserClass representation of a class from its name and file
     * @return parserClass
     * @param string $class classname
     * @param string $file file classname is located in
     */
    function &getClass($class, $file)
    {
//        debug("getClass called with class $class file $file");
        return $this->classesbyfile[$file][$class];
    }
    
    /**
     * Used by {@link parserData::getClasses()} to retrieve classes defined in file $path
     *
     * retrieves the array entry from {@link $classesbyfile} for $path
     * @param string $path full path to filename
     * @return mixed returns false if no classes defined in the file, otherwise returns an array of {@link parserClass}es
     */
    function getClassesInPath($path)
    {
        if (!isset($this->classesbyfile[$path])) return false;
        return $this->classesbyfile[$path];
    }
    
    /**
     * called by {@link parserClass::hasMethods()}.  Should not be directly called
     * @access private
     * @param string $file
     * @param string $class
     */
    function hasMethods($file,$class)
    {
        return isset($this->methodsbyfile[$file][$class]);
    }
    
    /**
     * called by {@link parserClass::hasConsts()}.  Should not be directly called
     * @access private
     * @param string $file
     * @param string $class
     */
    function hasConsts($file,$class)
    {
        return isset($this->constsbyfile[$file][$class]);
    }
    
    /**
     * called by {@link parserClass::hasVars()}.  Should not be directly called
     * @access private
     * @param string $file
     * @param string $class
     */
    function hasVars($file, $class)
    {
        return isset($this->varsbyfile[$file][$class]);
    }
    
    /**
     * called by {@link parserClass::hasMethod()}.  Should not be directly called
     * @param string $file
     * @param string $class
     * @param string $name method name
     * @access private
     */
    function hasMethod($class, $file, $name)
    {
        if (!$this->hasMethods($file, $class)) return false;
        for($i=0; $i<count($this->methodsbyfile[$file][$class]); $i++)
        {
            if ($this->methodsbyfile[$file][$class][$i]->getName() == $name) return true;
        }
        return false;
    }
    
    /**
     * called by {@link parserClass::hasVar()}.  Should not be directly called
     * @param string $file
     * @param string $class
     * @param string $name var name
     * @access private
     */
    function hasVar($class, $file, $name)
    {
        if (!$this->hasVars($file, $class)) return false;
        for($i=0; $i<count($this->varsbyfile[$file][$class]); $i++)
        {
            if ($this->varsbyfile[$file][$class][$i]->getName() == $name) return true;
        }
        return false;
    }
    
    /**
     * called by {@link parserClass::hasConst()}.  Should not be directly called
     * @param string $file
     * @param string $class
     * @param string $name var name
     * @access private
     */
    function hasConst($class, $file, $name)
    {
        if (!$this->hasConsts($file, $class)) return false;
        for($i=0; $i<count($this->constsbyfile[$file][$class]); $i++)
        {
            if ($this->constsbyfile[$file][$class][$i]->getName() == $name) return true;
        }
        return false;
    }
    
    /**
     * called by {@link parserClass::getMethods()}.  Should not be directly called
     * @access private
     * @param string $class
     * @param string $file
     */
    function &getMethods($class, $file)
    {
        if (!isset($this->methodsbyfile[$file][$class])) {
            $flag = false;
            return $flag;
        }
        return $this->methodsbyfile[$file][$class];
    }
    
    /**
     * called by {@link parserClass::getVars()}.  Should not be directly called
     * @access private
     * @param string $class
     * @param string $file
     */
    function &getVars($class, $file)
    {
        if (!isset($this->varsbyfile[$file][$class])) {
            $flag = false;
            return $flag;
        }
        return $this->varsbyfile[$file][$class];
    }
    
    /**
     * called by {@link parserClass::getConsts()}.  Should not be directly called
     * @access private
     * @param string $class
     * @param string $file
     */
    function &getConsts($class, $file)
    {
        if (!isset($this->constsbyfile[$file][$class])) {
            $flag = false;
            return $flag;
        }
        return $this->constsbyfile[$file][$class];
    }
    
    /**
     * called by {@link parserClass::getMethod()}.  Should not be directly called
     * @param string $class
     * @param string $file
     * @param string $name method name
     * @access private
     */
    function getMethod($class, $file, $name)
    {
        if (!$this->hasMethod($class, $file, $name)) return false;
        for($i=0; $i<count($this->methodsbyfile[$file][$class]); $i++)
        {
            if ($this->methodsbyfile[$file][$class][$i]->getName() == $name) return $this->methodsbyfile[$file][$class][$i];
        }
    }
    
    /**
     * called by {@link parserClass::getVar()}.  Should not be directly called
     * @param string $class
     * @param string $file
     * @param string $name var name
     * @access private
     */
    function getVar($class, $file, $name)
    {
        if (!$this->hasVar($class, $file, $name)) return false;
        for($i=0; $i<count($this->varsbyfile[$file][$class]); $i++)
        {
            if ($this->varsbyfile[$file][$class][$i]->getName() == $name) return $this->varsbyfile[$file][$class][$i];
        }
    }
    
    /**
     * called by {@link parserClass::getConst()}.  Should not be directly called
     * @param string $class
     * @param string $file
     * @param string $name var name
     * @access private
     */
    function getConst($class, $file, $name)
    {
        if (!$this->hasConst($class, $file, $name)) return false;
        for($i=0; $i<count($this->constsbyfile[$file][$class]); $i++)
        {
            if ($this->constsbyfile[$file][$class][$i]->getName() == $name) return $this->constsbyfile[$file][$class][$i];
        }
    }
    
    /**
     * Search for a class in a package
     * @return mixed returns false if no class in $package, otherwise returns a {@link parserClass}
     * @param string $class classname
     * @param string $package package classname is in
     */
    function &getClassByPackage($class,$package)
    {
        if (!isset($this->classesbynamefile[$class]))
        {
//            addWarning(PDERROR_CLASS_NOT_IN_PACKAGE,$class,$package); // removed, too many warnings, not very useful
            $flag = false;
            return $flag;
        }
        for($i=0; $i < count($this->classesbynamefile[$class]); $i++)
        {
            $cls = $this->classesbyfile[$this->classesbynamefile[$class][$i]][$class];
            $pkg = $cls->getPackage();
            if ($pkg == $package)
								return $cls;
        }
//        addWarning(PDERROR_CLASS_NOT_IN_PACKAGE,$class,$package);
        $flag = false;
        return $flag;
    }
    
    /**
     * Find the parent class of a class in file $file
     * uses 3 tests to find the parent classname:
     * <ol>
     *    <li>only one class with the parent classname</li>
     *    <li>more than one class, but only one in the same file as the child</li>
     *    <li>only one parent class in the same package as the child</li>
     * </ol>
     * @return mixed false if no parent class, a string if no parent class found by that name,
     *                and an array(file parentclass is in,parentclassname)
     */
    function getParentClass($class,$file)
    {
        if (!isset($this->classesbyfile[$file][$class]))
        {
            return false;
        }
        $element = $this->classesbyfile[$file][$class];
        if (!($ex = $element->getExtends())) return false;
        // first check to see if there is one and only one class with the parent class's name
        if (isset($this->classesbynamefile[$ex]))
        {
            if (count($this->classesbynamefile[$ex]) == 1)
            {
                if ($this->classesbyfile[$this->classesbynamefile[$ex][0]][$ex]->ignore) return $ex;
                return array($this->classesbynamefile[$ex][0],$ex);
            } else
            {
                // next check to see if there is a parent class in the same file
                if (isset($this->classesbyfile[$file][$ex]))
                {
                    if ($this->classesbyfile[$file][$ex]->ignore) return $ex;
                    return array($file,$ex);
                }
                // next check to see if there is only one package used in the file, try to resolve it that way
                if (isset($this->classpackagebyfile[$file]))
                {
                    if (count($this->classpackagebyfile[$file]) == 1)
                    {
                        for($i=0;$i<count($this->classesbynamefile[$ex]);$i++)
                        {
                            if ($this->classesbyfile[$this->classesbynamefile[$ex][$i]][$ex]->getPackage() == $this->classpackagebyfile[$file][0])
                            {
                                if ($this->classesbyfile[$this->classesbynamefile[$ex][$i]][$ex]->ignore) return $ex;
                                return array($this->classesbynamefile[$ex][$i],$ex);
                            }
                        }
                    }
                }
                // name conflict
                addWarning(PDERROR_INHERITANCE_CONFLICT, $class, $file, $ex);
                return $ex;
            }
        } else
        {
            if (class_exists('ReflectionClass') && class_exists($ex)) {
                $r = new ReflectionClass($ex);
                if ($r->isInternal()) {
                    return $ex; // no warning
                }
            }
            addWarning(PDERROR_PARENT_NOT_FOUND, $class, $ex);
            return $ex;
        }
    }
    
    /**
     * Get a list of all root classes indexed by package.  Used to generate
     * class trees by {@link Converter}
     * @param boolean $all [since phpDocumentor 1.3.0RC6] determines whether to
     *                     return class trees that extend non-parsed classes
     * @return array array(package => array(rootclassname, rootclassname,...),...)
     */
    function getRoots($all = false)
    {
        $roots = array();
        $temproots = $this->roots;
        if (!$all) {
            foreach ($this->specialRoots as $package => $root) {
                foreach ($root as $parent => $info) {
                    $temproots[$info[0]][] = $info[1];
                }
            }
        }
        foreach($temproots as $class => $files)
        {
            if (count($files))
            {
                foreach($files as $i => $boofou)
                {
                    $x = $this->getClass($class,$files[$i]);
                    $roots[$x->getPackage()][] = $class;
                }
            }
        }
        foreach($roots as $package => $root)
        {
            usort($roots[$package],"strnatcasecmp");
        }
        if ($all) {
            $specialRoots = array();
            foreach ($this->specialRoots as $parent => $classinfo) {
                if (count($classinfo)) {
                    foreach ($classinfo as $i => $info) {
                        $x = $this->getClass($info[0], $info[1]);
                        $specialRoots[$x->getPackage()][$parent][] = $info[0];
                    }
                }
            }
            foreach($specialRoots as $package => $root)
            {
                uksort($specialRoots[$package],"strnatcasecmp");
                foreach ($specialRoots[$package] as $parent => $classes) {
                    usort($specialRoots[$package][$parent], 'strnatcasecmp');
                }
            }
            return array('special' => $specialRoots, 'normal' => $roots);
        }
        return $roots;
    }
    
    /**
     * Get all classes confirmed in parsing to be descended class $parclass in file $file
     * @return mixed either false if no children, or array of format
     *         array(childname => childfile,childname2 => childfile2,...)
     * @param string $parclass name of parent class
     * @param string $file file parent class is found in
     * @see parserClass::getChildClassList()
     * @uses $definitechild
     */
    function getDefiniteChildren($parclass,$file)
    {
        if (isset($this->definitechild[$parclass][$file])) return $this->definitechild[$parclass][$file];
        return false;
    }
}
?>
