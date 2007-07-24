<?php
/**
 * CHM (Compiled Help Manual) output converter for Smarty Template.
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2003-2006 Andrew Eddie, Greg Beaver
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
 * @subpackage CHMdefault
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2000-2006 Joshua Eichorn, Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: CHMdefaultConverter.inc,v 1.11 2007/03/13 20:47:35 ashnazg Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.0rc1
 */
/**
 * Generates files that MS HTML Help Worshop can use to create a MS Windows
 * compiled help file (CHM)
 *
 * The free MS HTML Help compiler takes the project file (phpdoc.hhp) and reads
 * the table of contents file specified in the project (which is always contents.hhc
 * in phpDocumentor).  When the converter reaches stable state, it will also
 * output an index file index.hhk.  The free download for MS HTML Help Workshop
 * is available below
 * @link http://www.microsoft.com/downloads/release.asp?releaseid=33071 MS HTML Help Workshop download
 * @package Converters
 * @subpackage CHMdefault
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Revision: 1.11 $
 */
class CHMdefaultConverter extends Converter
{
    /**
     * CHMdefaultConverter wants elements sorted by type as well as alphabetically
     * @see Converter::$sort_page_contents_by_type
     * @var boolean
     */
    var $sort_page_contents_by_type = true;
    /** @var string */
    var $outputformat = 'CHM';
    /** @var string */
    var $name = 'default';
    /**
     * indexes of elements by package that need to be generated
     * @var array
     */
    var $leftindex = array('classes' => true, 'pages' => true, 'functions' => true, 'defines' => false, 'globals' => false);
    
    /**
     * output directory for the current procedural page being processed
     * @var string
     */
    var $page_dir;
    
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
     * array of converted package page names.
     * Used to link to the package page in the left index
     * @var array Format: array(package => 1)
     */
    var $package_pages = array();
    
    /**
     * controls formatting of parser informative output
     * 
     * Converter prints:
     * "Converting /path/to/file.php... Procedural Page Elements... Classes..."
     * Since CHMdefaultConverter outputs files while converting, it needs to send a \n to start a new line.  However, if there
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
    var $wrote = false;
    var $ric_set = array();
    /**
     * Table of Contents entry for index.hhk
     * @var array
     */
    var $KLinks = array();

    /**
     * sets {@link $base_dir} to $targetDir
     * @see Converter()
     */
    function CHMdefaultConverter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $templateDir, $title)
    {
        Converter::Converter($allp, $packp, $classes, $procpages,$po, $pp, $qm, $targetDir, $templateDir, $title);
        $this->base_dir = $targetDir;
    }
    
    /**
     * @deprecated in favor of PHP 4.3.0+ tokenizer-based source highlighting
     */
    function unmangle($sourcecode)
    {
        $sourcecode = str_replace('<code>','<pre>',$sourcecode);
        $sourcecode = str_replace('</code>','</pre>',$sourcecode);
        $sourcecode = str_replace('<br />',"\n",$sourcecode);
        $sourcecode = str_replace('&nbsp;',' ',$sourcecode);
        $sourcecode = str_replace('&lt;','<',$sourcecode);
        $sourcecode = str_replace('&gt;','>',$sourcecode);
        $sourcecode = str_replace('&amp;','&',$sourcecode);
        return $sourcecode;
    }

    /**
     * @param string full path to the source file
     * @param string fully highlighted source code
     */
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
        $templ->assign("subdir",'../');
        $templ->register_outputfilter('CHMdefault_outputfilter');
        $this->setTargetDir($this->getFileSourcePath($this->base_dir));
        $this->addSourceTOC($pathinfo['name'],$this->getFileSourceName($path),$pathinfo['package'],$pathinfo['subpackage'], true);
        phpDocumentor_out("\n");
        $this->setSourcePaths($path);
        $this->writefile($this->getFileSourceName($path).'.html',$templ->fetch('filesource.tpl'));
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
        $templ->assign("subdir",'../');
        $templ->register_outputfilter('CHMdefault_outputfilter');
        $pathinfo = $this->proceduralpages->getPathInfo($path, $this);
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . '__examplesource');
        $this->addSourceTOC($title,'exsource_'.$path,$pathinfo['package'],$pathinfo['subpackage'], false);
        phpDocumentor_out("\n");
        $this->writefile('exsource_'.$path.'.html',$templ->fetch('examplesource.tpl'));
    }

    function getExampleLink($path, $title)
    {
        return $this->returnLink('{$subdir}__examplesource' . PATH_DELIMITER . 'exsource_'.$path.'.html',$title);
    }
    
    function getSourceLink($path)
    {
        return $this->returnLink('{$subdir}__filesource/' .
        $this->getFileSourceName($path).'.html','Source Code for this file');
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
        if ($link) {
            return $this->returnLink('{$subdir}__filesource/' .
                $this->getFileSourceName($sourcefile) . '.html#a' . $anchor, $text);
        } else {
            return '<a name="a'.$anchor.'"></a>';
        }
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
        $extra = '';
        if (strlen(str_replace("\n", '', $line)) == 0) {
            $extra = '&nbsp;';
        }
        if ($path)
        {
            return '<li><div class="src-line">' . $this->getSourceAnchor($path, $linenumber) .
                   str_replace("\n",'',$line) . $extra .
                   "</div></li>\n";
        } else
        {
            return '<li><div class="src-line">' . str_replace("\n",'',$line) .
                "$extra</div></li>\n";
        }
    }

    /**
     * Used to convert the <<code>> tag in a docblock
     * @param string
     * @param boolean
     * @return string
     */
    function ProgramExample($example, $tutorial = false, $inlinesourceparse = null/*false*/,
                            $class = null/*false*/, $linenum = null/*false*/, $filesourcepath = null/*false*/)
    {
        return $this->PreserveWhiteSpace(parent::ProgramExample($example, $tutorial, $inlinesourceparse, $class, $linenum, $filesourcepath));
    }
    
    /**
     * @param string
     */
    function TutorialExample($example)
    {
        $trans = $this->template_options['desctranslate'];
        $this->template_options['desctranslate'] = array();
        $example = '<ol>' . parent::TutorialExample($example)
               .'</ol>';
        $this->template_options['desctranslate'] = $trans;
        if (!isset($this->template_options['desctranslate'])) return $example;
        if (!isset($this->template_options['desctranslate']['code'])) return $example;
        $example = $this->template_options['desctranslate']['code'] . $example;
        if (!isset($this->template_options['desctranslate']['/code'])) return $example;
        return $example . $this->template_options['desctranslate']['/code'];
    }
    
    function getCurrentPageLink()
    {
        return $this->curname . '.html';
    }

    /**
     * Uses htmlspecialchars() on the input
     */
    function postProcess($text)
    {
        if ($this->highlightingSource) {
            return str_replace(array(' ',"\t"), array('&nbsp;', '&nbsp;&nbsp;&nbsp;'),
                htmlspecialchars($text));
        }
        return htmlspecialchars($text);
    }
    
    /**
     * Use the template tutorial_toc.tpl to generate a table of contents for HTML
     * @return string table of contents formatted for use in the current output format
     * @param array format: array(array('tagname' => section, 'link' => returnsee link, 'id' => anchor name, 'title' => from title tag),...)
     */
    function formatTutorialTOC($toc)
    {
        $template = &$this->newSmarty();
        $template->assign('toc',$toc);
        return $template->fetch('tutorial_toc.tpl');
    }
    
    function &SmartyInit(&$templ)
    {
        if (!isset($this->package_index))
        foreach($this->all_packages as $key => $val)
        {
            if (isset($this->pkg_elements[$key]))
            {
                if (!isset($start)) $start = $key;
                $this->package_index[] = array('link' => "li_$key.html", 'title' => $key);
            }
        }
        $templ->assign("packageindex",$this->package_index);
        $templ->assign("subdir",'');
        return $templ;
    }
    
    
    /**
     * Writes out the template file of {@link $class_data} and unsets the template to save memory
     * @see registerCurrentClass()
     * @see parent::endClass()
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
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->class_dir);
        $this->class_data->assign("subdir",$a);
        $this->class_data->register_outputfilter('CHMdefault_outputfilter');
        $this->addTOC($this->class,$this->class,$this->package,$this->subpackage, true);
        $this->writefile($this->class . '.html',$this->class_data->fetch('class.tpl'));
        unset($this->class_data);
    }
    
    /**
     * Writes out the template file of {@link $page_data} and unsets the template to save memory
     * @see registerCurrent()
     * @see parent::endPage()
     */
    function endPage()
    {
        $this->package = $this->curpage->package;
        $this->subpackage = $this->curpage->subpackage;
        $a = '../';
        if (!empty($this->subpackage)) $a .= '../';
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->page_data->assign("package",$this->package);
        $this->page_data->assign("subdir",$a);
        $this->page_data->register_outputfilter('CHMdefault_outputfilter');
        $this->addTOC($this->curpage->file,$this->page,$this->package,$this->subpackage);
        $this->writefile($this->page . '.html',$this->page_data->fetch('page.tpl'));
        unset($this->page_data);
    }
    
    /**
     * @param string
     * @param string
     * @return string &lt;a href="'.$link.'">'.$text.'</a&gt;
     */
    function returnLink($link,$text)
    {
        return '<a href="'.$link.'">'.$text.'</a>';
    }
    
    /**
     * CHMdefaultConverter chooses to format both package indexes and the complete index here
     *
     * This function formats output for the elementindex.html and pkgelementindex.html template files.  It then
     * writes them to the target directory
     * @see generateElementIndex(), generatePkgElementIndex()
     */
    function formatPkgIndex()
    {
        list($package_indexes,$packages,$mletters) = $this->generatePkgElementIndexes();
        for($i=0;$i<count($package_indexes);$i++)
        {
            $template = &$this->newSmarty();
            $this->package = $package_indexes[$i]['package'];
            $this->subpackage = '';
            $template->assign("index",$package_indexes[$i]['pindex']);
            $template->assign("package",$package_indexes[$i]['package']);
            $template->assign("letters",$mletters[$package_indexes[$i]['package']]);
            $template->assign("title","Package ".$package_indexes[$i]['package']." Element Index");
            $template->assign("subdir",'../');
            $template->register_outputfilter('CHMdefault_outputfilter');
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . $package_indexes[$i]['package']);
            $this->addTOC($package_indexes[$i]['package']." Alphabetical Index",'elementindex_'.$package_indexes[$i]['package'],$package_indexes[$i]['package'],'');
            $this->writefile('elementindex_'.$package_indexes[$i]['package'].'.html',$template->fetch('pkgelementindex.tpl'));
        }
        phpDocumentor_out("\n");
        flush();
        }
    
    /**
     * CHMdefaultConverter uses this function to format template index.html and packages.html
     *
     * This function generates the package list from {@link $all_packages}, eliminating any
     * packages that don't have any entries in their package index (no files at all, due to @ignore
     * or other factors).  Then it uses the default package name as the first package index to display.
     * It sets the right pane to be either a blank file with instructions on making package-level docs,
     * or the package-level docs for the default package.
     * @global string Used to set the starting package to display
     */
    function formatIndex()
    {
        global $phpDocumentor_DefaultPackageName;
        list($elindex,$mletters) = $this->generateElementIndex();
        $template = &$this->newSmarty();
        $template->assign("index",$elindex);
        $template->assign("letters",$mletters);
        $template->assign("title","Element Index");
        $template->assign("date",date("r",time()));
        $template->register_outputfilter('CHMdefault_outputfilter');
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        $this->addTOC("Alphabetical Index Of All Elements",'elementindex',"Index",'');
        $this->writefile('elementindex.html',$template->fetch('elementindex.tpl'));
        usort($this->package_index,"CHMdefault_pindexcmp");
        $index = &$this->newSmarty();
        foreach($this->all_packages as $key => $val)
        {
            if (isset($this->pkg_elements[$key]))
            {
                if (!isset($start)) $start = $key;
                if (!isset($this->package_pages[$key])) $this->writeNewPPage($key);
            }
        }
        $this->setTargetDir($this->base_dir);
        // Created index.html
        if (isset($this->pkg_elements[$phpDocumentor_DefaultPackageName])) $start = $phpDocumentor_DefaultPackageName;
        $this->package = $start;
        $this->subpackage = '';
        $setalready = false;
        if (isset($this->tutorials[$start]['']['pkg']))
        {
            foreach($this->tutorials[$start]['']['pkg'] as $tute)
            {
                if ($tute->name == $start . '.pkg')
                {
                    $setalready = true;
                       $this->addTOC("Start page",$start.'/tutorial_'.$tute->name,"Index",'');
                }
            }
        }
        if (!$setalready)
        {
            if (isset($this->package_pages[$start]))
            {
                   $this->addTOC("Start page",'package_'.$start,"Index",'');
            }
            else
            {
                $index->assign("blank","blank");
                $blank = &$this->newSmarty();
                $blank->assign('package',$phpDocumentor_DefaultPackageName);
                $this->addTOC("Start page",'blank',"Index",'');
                $this->writefile("blank.html",$blank->fetch('blank.tpl'));
                Converter::writefile('index.html',$blank->fetch('tutorial.tpl'));
            }
        }
        phpDocumentor_out("\n");
        flush();

        unset($index);
    }
    
    function writeNewPPage($key)
    {
        return;
        $template = &$this->newSmarty();
        $this->package = $key;
        $this->subpackage = '';
        $template->assign("date",date("r",time()));
        $template->assign("title",$this->title);
        $template->assign("package",$key);
        $template->register_outputfilter('CHMdefault_outputfilter');
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
    
        $this->addTOC("$key Index","li_$key",$key,'');
        $this->writefile("li_$key.html",$template->fetch('index.tpl'));
        unset($template);
    }
    
    /**
     * Generate indexes for li_package.html and classtree output files
     *
     * This function generates the li_package.html files from the template file left.html.  It does this by
     * iterating through each of the $page_elements, $class_elements and  $function_elements arrays to retrieve
     * the pre-sorted {@link abstractLink} descendants needed for index generation.  Conversion of these links to
     * text is done by {@link returnSee()}.  The {@link $local} parameter is set to false to ensure that paths are correct.
     * 
     * Then it uses {@link generateFormattedClassTrees()} to create class trees from the template file classtrees.html.  Output
     * filename is classtrees_packagename.html.  This function also unsets {@link $elements} and {@link $pkg_elements} to free
     * up the considerable memory these two class vars use
     * @see $page_elements, $class_elements, $function_elements
     */
    function formatLeftIndex()
    {
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        if (0)//!isset($this->left))
        {
            debug("Nothing parsed, check the command-line");
            die();
        }
        foreach($this->all_packages as $package => $rest)
        {
            if (!isset($this->pkg_elements[$package])) continue;
            
            // Create class tree page
            $template = &$this->newSmarty();
            $template->assign("classtrees",$this->generateFormattedClassTrees($package));
            $template->assign("package",$package);
            $template->assign("date",date("r",time()));
            $template->register_outputfilter('CHMdefault_outputfilter');
            $this->addTOC("$package Class Trees","classtrees_$package",$package,'');
            $this->writefile("classtrees_$package.html",$template->fetch('classtrees.tpl'));
            phpDocumentor_out("\n");
            flush();
        }
        // free up considerable memory
        unset($this->elements);
        unset($this->pkg_elements);
    }
    
    /**
     * This function takes an {@link abstractLink} descendant and returns an html link
     *
     * @param abstractLink a descendant of abstractlink should be passed, and never text
     * @param string text to display in the link
     * @param boolean this parameter is not used, and is deprecated
     * @param boolean determines whether the returned text is enclosed in an <a> tag
     */
    function returnSee(&$element, $eltext = false, $with_a = true)
    {
        if (!$element) return false;
        if (!$with_a) return $this->getId($element, false);
        if (!$eltext)
        {
            $eltext = '';
            switch($element->type)
            {
                case 'tutorial' :
                $eltext = strip_tags($element->title);
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
        return '<a href="'.$this->getId($element).'">'.$eltext.'</a>';
    }
    
    function getId($element, $fullpath = true)
    {
        if (phpDocumentor_get_class($element) == 'parserdata')
        {
            $element = $this->addLink($element->parent);
            $elp = $element->parent;
        } elseif (is_a($element, 'parserbase'))
        {
            $elp = $element;
            $element = $this->addLink($element);
        }
        $c = '';
        if (!empty($element->subpackage))
        {
            $c = '/'.$element->subpackage;
        }
        $b = '{$subdir}';
        switch ($element->type)
        {
            case 'page' :
            if ($fullpath)
            return $b.$element->package.$c.'/'.$element->fileAlias.'.html';
            return 'top';
            break;
            case 'define' :
            case 'global' :
            case 'function' :
            if ($fullpath)
            return $b.$element->package.$c.'/'.$element->fileAlias.'.html#'.$element->type.$element->name;
            return $element->type.$element->name;
            break;
            case 'class' :
            if ($fullpath)
            return $b.$element->package.$c.'/'.$element->name.'.html';
            return 'top';
            break;
            case 'method' :
            case 'var' :
            case 'const' :
            if ($fullpath)
            return $b.$element->package.$c.'/'.$element->class.'.html#'.$element->type.$element->name;
            return $element->type.$element->name;
            break;
            case 'tutorial' :
            $d = '';
            if ($element->section)
            {
                $d = '#'.$element->section;
            }
            return $b.$element->package.$c.'/tutorial_'.$element->name.'.html'.$d;
        }
    }
    
    function ConvertTodoList()
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
        $templ = &$this->newSmarty();
        $templ->assign('todos',$todolist);
        $templ->register_outputfilter('CHMdefault_outputfilter');
        $this->setTargetDir($this->base_dir);
        $this->addTOC('Todo List','todolist','Index','',false,true);
        $this->addKLink('Todo List', 'todolist', '', 'Development');
        $this->writefile('todolist.html',$templ->fetch('todolist.tpl'));
    }
    
    /**
     * Convert README/INSTALL/CHANGELOG file contents to output format
     * @param README|INSTALL|CHANGELOG
     * @param string contents of the file
     */
    function Convert_RIC($name, $contents)
    {
        $template = &$this->newSmarty();
        $template->assign('contents',$contents);
        $template->assign('name',$name);
        $this->setTargetDir($this->base_dir);
        $this->addTOC($name,'ric_'.$name,'Index','',false,true);
        $this->addKLink($name, 'ric_'.$name, '', 'Development');
        $this->writefile('ric_'.$name . '.html',$template->fetch('ric.tpl'));
        $this->ric_set[$name] = true;
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
    
    function getCData($value)
    {
        return '<pre>'.htmlentities($value).'</pre>';
    }
    
    function getTutorialId($package,$subpackage,$tutorial,$id)
    {
        return $id;
    }

    /**
     * Converts package page and sets its package as used in {@link $package_pages}
     * @param parserPackagePage
     */
    function convertPackagepage(&$element)
    {
        phpDocumentor_out("\n");
        flush();
        $this->package = $element->package;
        $this->subpackage = '';
        $contents = $element->Convert($this);
        $this->package_pages[$element->package] = str_replace('{$subdir}','../',$contents);
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $element->package);
        $this->addTOC($element->package." Tutorial",'package_'.$element->package,$element->package,'');
        $this->writeFile('package_'.$element->package.'.html',str_replace('{$subdir}','../',$contents));
        $this->setTargetDir($this->base_dir);
        Converter::writefile('index.html',str_replace('{$subdir}','',$contents));
        $this->addKLink($element->package." Tutorial", 'package_'.$element->package, '', 'Tutorials');
    }
    
    /**
     * @param parserTutorial
     */
    function convertTutorial(&$element)
    {
        phpDocumentor_out("\n");
        flush();
        $template = &parent::convertTutorial($element);
        $a = '../';
        if ($element->subpackage) $a .= '../';
        $template->assign('subdir',$a);
        $template->register_outputfilter('CHMdefault_outputfilter');
        $contents = $template->fetch('tutorial.tpl');
        if ($element->package == $GLOBALS['phpDocumentor_DefaultPackageName'] && empty($element->subpackage) && ($element->name == $element->package . '.pkg'))
        {
            $template->assign('subdir','');
            $this->setTargetDir($this->base_dir);
            Converter::writefile('index.html',$template->fetch('tutorial.tpl'));
        }
        $a = '';
        if ($element->subpackage) $a = PATH_DELIMITER . $element->subpackage;
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $element->package . $a);
        $this->addTOC($a = strip_tags($element->getTitle($this)), 'tutorial_'.$element->name,
            $element->package, $element->subpackage, false, true);
        $this->writeFile('tutorial_'.$element->name.'.html',$contents);
        $this->addKLink($element->getTitle($this), $element->package . $a . PATH_DELIMITER . 'tutorial_'.$element->name,
            '', 'Tutorials');
    }
    
    /**
     * Converts class for template output
     * @see prepareDocBlock(), generateChildClassList(), generateFormattedClassTree(), getFormattedConflicts()
     * @see getFormattedInheritedMethods(), getFormattedInheritedVars()
     * @param parserClass
     */
    function convertClass(&$element)
    {
        parent::convertClass($element);
        $this->class_dir = $element->docblock->package;
        if (!empty($element->docblock->subpackage)) $this->class_dir .= PATH_DELIMITER . $element->docblock->subpackage;
        $a = '../';
        if ($element->docblock->subpackage != '') $a = "../$a";
        
        $this->class_data->assign('subdir',$a);
        $this->class_data->assign("title","Docs For Class " . $element->getName());
        $this->class_data->assign("page",$element->getName() . '.html');
        $this->addKLink($element->name, $this->class_dir . PATH_DELIMITER . $this->class, '', 'Classes');
    }
    

    /**
     * Converts class variables for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertVar(&$element)
    {
        parent::convertVar($element, array('var_dest' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->class_dir);
        $this->addKLink($element->name, $this->class_dir . PATH_DELIMITER .$this->class, $this->getId($element,false), $element->class.' Properties');
    }

    /**
     * Converts class constants for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertConst(&$element)
    {
        parent::convertConst($element, array('const_dest' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->class_dir);
        $this->addKLink($element->name, $this->class_dir . PATH_DELIMITER .$this->class, $this->getId($element,false), $element->class.' Constants');
    }

    /**
     * Converts class methods for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertMethod(&$element)
    {
        parent::convertMethod($element, array('method_dest' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->class_dir);
        $this->addKLink($element->name, $this->class_dir . PATH_DELIMITER .$this->class, $this->getId($element,false), $element->class.' Methods');
    }
    
    /**
     * Converts function for template output
     * @see prepareDocBlock(), parserFunction::getFunctionCall(), getFormattedConflicts()
     * @param parserFunction
     */
    function convertFunction(&$element)
    {
        $funcloc = $this->getId($this->addLink($element));
        parent::convertFunction($element,array('function_dest' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->addKLink($element->name, $this->page_dir . PATH_DELIMITER . $this->page, $this->getId($element,false), 'Functions');
    }
    
    /**
     * Converts include elements for template output
     * @see prepareDocBlock()
     * @param parserInclude
     */
    function convertInclude(&$element)
    {
        parent::convertInclude($element, array('include_file'    => '_'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '_'))));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->addKLink(str_replace('"', '', $element->getValue()), $this->page_dir . PATH_DELIMITER . $this->page, '', ucfirst($element->name));
    }
    
    /**
     * Converts defines for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertDefine(&$element)
    {
        parent::convertDefine($element, array('define_link' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->addKLink($element->name, $this->page_dir . PATH_DELIMITER . $this->page, $this->getId($element,false), 'Constants');
    }
    
    /**
     * Converts global variables for template output
     * @param parserGlobal
     */
    function convertGlobal(&$element)
    {
        parent::convertGlobal($element, array('global_link' => $this->getId($element,false)));
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->addKLink($element->name, $this->page_dir . PATH_DELIMITER . $this->page, $this->getId($element,false), 'Global Variables');
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
        if (!empty($element->parent->subpackage)) $this->page_dir .= PATH_DELIMITER . $element->parent->subpackage;
        // registering stuff on the template
        $this->page_data->assign("page",$this->getPageName($element) . '.html');
        $this->page_data->assign("title","Docs for page ".$element->parent->getFile());
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->addKLink($element->parent->file, $this->page_dir . PATH_DELIMITER . $this->page, '', 'Files');
    }
    
    function getPageName(&$element)
    {
        if (phpDocumentor_get_class($element) == 'parserpage') return '_'.$element->getName();
        return '_'.$element->parent->getName();
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
            $distance[] = '';
            while ($parent)
            {
                $x = $parent;
                if (is_object($parent))
                {
                    $subpackage = $parent->docblock->subpackage;
                    $package = $parent->docblock->package;
                    $x = $parent;
                    $x = $parent->getLink($this);
                    if (!$x) $x = $parent->getName();
                }
                $result[] = 
                    $x;
                $distance[] =
                    "\n%s|\n" .
                    "%s--";
                if (is_object($parent))
                $parent = $tree[$parent->getName()];
                elseif (isset($tree[$parent]))
                $parent = $tree[$parent];
            }
            $nbsp = '   ';
            for($i=count($result) - 1;$i>=0;$i--)
            {
                $my_nbsp = '';
                for($j=0;$j<count($result) - $i;$j++) $my_nbsp .= $nbsp;
                $distance[$i] = sprintf($distance[$i],$my_nbsp,$my_nbsp);
            }
            return array('classes'=>array_reverse($result),'distance'=>array_reverse($distance));
        } else
        {
            return array('classes'=>$class->getName(),'distance'=>array(''));
        }
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
                $my_tree .= '<li>' . $tree[$cur]['parent'] .'<ul>';
            }
            else
            {
//                        debug("parent ".$this->returnSee($tree[$cur]['parent'])." in other package");
                $my_tree .= '<li>' . $this->returnSee($tree[$cur]['parent']);
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
                    $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link']);
                    $my_tree .= '<ul>'."\n";
                }
                array_push($lastcur,$cur);
                list(,$cur) = each($tree[$cur]['children']);
//                var_dump('listed',$cur);
                if ($cur)
                {
                    $cur = $cur['package'] . '#' . $cur['class'];
//                    debug("set cur to child $cur");
//                    $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link']);
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
                $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link'])."</li>";
                if ($dopar && $cur == '#root') $my_tree .= '</ul></li>';
                $cur = array_pop($lastcur);
            }
        } while ($cur);
        return $my_tree;
    }
        /**
         * Generate indexing information for given element
         * 
         * @param parserElement descendant of parserElement
         * @see generateElementIndex()
         * @return array
         */
        function getIndexInformation($elt)
        {
            $Result['type'] = $elt->type;
            $Result['file_name'] = $elt->file;
            $Result['path'] = $elt->getPath();
            
            if (isset($elt->docblock))
						{
							$Result['description'] = $elt->docblock->getSDesc($this);
							
							if ($elt->docblock->hasaccess)
								$Result['access'] = $elt->docblock->tags['access'][0]->value;
							else
								$Result['access'] = 'public';

							$Result['abstract'] = isset ($elt->docblock->tags['abstract'][0]);
						}
            else
                $Result['description'] = '';
            
            $aa = $Result['description'];
            if (!empty($aa)) $aa = "<br>&nbsp;&nbsp;&nbsp;&nbsp;$aa";

            switch($elt->type)
            {
                    case 'class':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Class';
                            $Result['link'] = $this->getClassLink($elt->getName(),
                                                                  $elt->docblock->package,
                                                                  $elt->getPath(),
                                                                  $elt->getName());
                            $Result['listing'] = 'in file '.$elt->file.', class '.$Result['link']."$aa";
                    break;
                    case 'define':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Constant';
                            $Result['link'] = $this->getDefineLink($elt->getName(),
                                                                   $elt->docblock->package,
                                                                   $elt->getPath(),
                                                                   $elt->getName());
                            $Result['listing'] = 'in file '.$elt->file.', constant '.$Result['link']."$aa";
                    break;
                    case 'global':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Global';
                            $Result['link'] = $this->getGlobalLink($elt->getName(),
                                                                   $elt->docblock->package,
                                                                   $elt->getPath(),
                                                                   $elt->getName());
                            $Result['listing'] = 'in file '.$elt->file.', global variable '.$Result['link']."$aa";
                    break;
                    case 'function':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Function';
                            $Result['link'] = $this->getFunctionLink($elt->getName(),
                                                                     $elt->docblock->package,
                                                                     $elt->getPath(),
                                                                     $elt->getName().'()');
                            $Result['listing'] = 'in file '.$elt->file.', function '.$Result['link']."$aa";
                    break;
                    case 'method':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Method';
                            $Result['link'] = $this->getMethodLink($elt->getName(),
                                                                   $elt->class,
                                                                   $elt->docblock->package,
                                                                   $elt->getPath(),
                                                                   $elt->class.'::'.$elt->getName().'()'
                                                                             );
														if ($elt->isConstructor) $Result['constructor'] = 1;
                            $Result['listing'] = 'in file '.$elt->file.', method '.$Result['link']."$aa";
                    break;
                    case 'var':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Variable';
                            $Result['link'] = $this->getVarLink($elt->getName(),
                                                                $elt->class,
                                                                $elt->docblock->package,
                                                                $elt->getPath(),
                                                                $elt->class.'::'.$elt->getName());
                            $Result['listing'] = 'in file '.$elt->file.', variable '.$Result['link']."$aa";
                    break;
                    case 'const':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Variable';
                            $Result['link'] = $this->getConstLink($elt->getName(),
                                                                $elt->class,
                                                                $elt->docblock->package,
                                                                $elt->getPath(),
                                                                $elt->class.'::'.$elt->getName());
                            $Result['listing'] = 'in file '.$elt->file.', class constant '.$Result['link']."$aa";
                    break;
                    case 'page':
                            $Result['name'] = $elt->getFile();
                            $Result['title'] = 'Page';
                            $Result['link'] = $this->getPageLink($elt->getFile(),
                                                                 $elt->package,
                                                                 $elt->getPath(),
                                                                 $elt->getFile());
                            $Result['listing'] = 'procedural page '.$Result['link'];
                    break;
                    case 'include':
                            $Result['name'] = $elt->getName();
                            $Result['title'] = 'Include';
                            $Result['link'] = $elt->getValue();
                            $Result['listing'] = 'include '.$Result['name'];
                    break;
            }

            return $Result;
        }
    /**
     * Generate alphabetical index of all elements
     *
     * @see $elements, walk()
     */
    function generateElementIndex()
    {
        $elementindex = array();
        $letters = array();
        $used = array();
        foreach($this->elements as $letter => $nutoh)
        {
            foreach($this->elements[$letter] as $i => $yuh)
            {
                if ($this->elements[$letter][$i]->type != 'include')
                {
                    if (!isset($used[$letter]))
                    {
                        $letters[]['letter'] = $letter;
                        $elindex['letter'] = $letter;
                        $used[$letter] = 1;
                    }

                    $elindex['index'][] = $this->getIndexInformation($this->elements[$letter][$i]);
                }
            }
            if (isset($elindex['index']))
            {
                $elementindex[] = $elindex;
            } else
            {
                unset($letters[count($letters) - 1]);
            }
            $elindex = array();
        }
        return array($elementindex,$letters);
    }
    
    function setTemplateDir($dir)
    {
        Converter::setTemplateDir($dir);
        $this->smarty_dir = $this->templateDir;
    }
    
    function copyMediaRecursively($media,$targetdir,$subdir = '')
    {
        $versionControlDirectories = array ('CVS', 'media/CVS', 'media\\CVS', '.svn', 'media/.svn', 'media\\.svn');
        if (!is_array($media)) {
            return;
        }
        foreach($media as $dir => $files)
        {
            if ($dir === '/')
            {
                $this->copyMediaRecursively($files,$targetdir);
            } else
            {
                if (!is_numeric($dir))
                {
                    if (in_array($dir, $versionControlDirectories))
                    {
                        // skip it entirely
                    }
                    else
                    {
                        // create the subdir
                        phpDocumentor_out("creating $targetdir" . PATH_DELIMITER . "$dir\n");
                        Converter::setTargetDir($targetdir . PATH_DELIMITER . $dir);
                        if (!empty($subdir)) 
                        {
                            $subdir .= PATH_DELIMITER;
                        }
                        $this->copyMediaRecursively($files,"$targetdir/$dir",$subdir . $dir);
                    }
                } 
                else
                {
                    // copy the file
                    phpDocumentor_out("copying $targetdir" . PATH_DELIMITER . $files['file']."\n");
                    $this->copyFile($files['file'],$subdir);
                }
            }
        }
    }
    
    /**
     * calls the converter setTargetDir, and then copies any template images and the stylesheet if they haven't been copied
     * @see Converter::setTargetDir()
     */
    function setTargetDir($dir)
    {
        Converter::setTargetDir($dir);
        if ($this->wrote) return;
        $this->wrote = true;
        $template_images = array();
        $stylesheets = array();
        $tdir = $dir;
        $dir = $this->templateDir;
        $this->templateDir = $this->templateDir.'templates/';
        $info = new Io;
        $this->copyMediaRecursively($info->getDirTree($this->templateDir.'media',$this->templateDir),$tdir);
    }
    
    /**
     * Generate alphabetical index of all elements by package and subpackage
     *
     * @param string $package name of a package
     * @see $pkg_elements, walk(), generatePkgElementIndexes()
     */
    function generatePkgElementIndex($package)
    {
//        var_dump($this->pkg_elements[$package]);
        $elementindex = array();
        $letters = array();
        $letterind = array();
        $used = array();
        $subp = '';
        foreach($this->pkg_elements[$package] as $subpackage => $els)
        {
            if (empty($els)) continue;
            if (!empty($subpackage)) $subp = " (<b>subpackage:</b> $subpackage)"; else $subp = '';
            foreach($els as $letter => $yuh)
            {
                foreach($els[$letter] as $i => $yuh)
                {
                    if ($els[$letter][$i]->type != 'include')
                    {
                        if (!isset($used[$letter]))
                        {
                            $letters[]['letter'] = $letter;
                            $letterind[$letter] = count($letters) - 1;
                            $used[$letter] = 1;
                        }
                        $elindex[$letter]['letter'] = $letter;

                        $elindex[$letter]['index'][] = $this->getIndexInformation($els[$letter][$i]);
                    }
                }
            }
        }
        ksort($elindex);
        usort($letters,'CHMdefault_lettersort');
        if (isset($elindex))
        {
            while(list($letter,$tempel) = each($elindex))
            {
                if (!isset($tempel))
                {
                    unset($letters[$letterind[$tempel['letter']]]);
                } else
                $elementindex[] = $tempel;
            }
        } else $letters = array();
        return array($elementindex,$letters);
    }
    
    /**
     *
     * @see generatePkgElementIndex()
     */
    function generatePkgElementIndexes()
    {
        $packages = array();
        $package_names = array();
        $pkg = array();
        $letters = array();
        foreach($this->pkg_elements as $package => $trash)
        {
            $pkgs['package'] = $package;
            $pkg['package'] = $package;
            list($pkg['pindex'],$letters[$package]) = $this->generatePkgElementIndex($package);
            if (count($pkg['pindex']))
            {
                $packages[] = $pkg;
                $package_names[] = $pkgs;
            }
            unset($pkgs);
            unset($pkg);
        }
        foreach($packages as $i => $package)
        {
            $pnames = array();
            for($j=0;$j<count($package_names);$j++)
            {
                if ($package_names[$j]['package'] != $package['package']) $pnames[] = $package_names[$j];
            }
            $packages[$i]['packageindexes'] = $pnames;
        }
        return array($packages,$package_names,$letters);
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
    function getClassLink($expr,$package, $file = false,$text = false, $with_a = true)
    {
        $a = Converter::getClassLink($expr,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text, $with_a);
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
     * @param boolean return just the URL, or enclose it in an html a tag
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
     * @param boolean return just the URL, or enclose it in an html a tag
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
     * @param boolean return just the URL, or enclose it in an html a tag
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
     * @param boolean return just the URL, or enclose it in an html a tag
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
     * @see parent::getVarLink()
     */
    function getConstLink($expr,$class,$package, $file = false,$text = false)
    {
        $a = Converter::getConstLink($expr,$class,$package,$file);
        if (!$a) return false;
        return $this->returnSee($a, $text);
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
    
    /**
     * Write a file to disk, and add it to the {@link $hhp_files} list of files
     * to include in the generated CHM
     *
     * {@source}
     */
    function writefile($file,$contents)
    {
        $this->addHHP($this->targetDir . PATH_DELIMITER . $file);
        Converter::writefile($file,$contents);
    }
    
    /**
     * @uses $hhp_files creates the array by adding parameter $file
     */
    function addHHP($file)
    {
        $file = str_replace($this->base_dir . PATH_DELIMITER, '', $file);
        $file = str_replace('\\',PATH_DELIMITER,$file);
        $file = str_replace('//',PATH_DELIMITER,$file);
        $file = str_replace(PATH_DELIMITER,'\\',$file);
        $this->hhp_files[]['name'] = $file;
    }
    
    function generateTOC()
    {
        $comppack = '';
        $templ = &$this->newSmarty();
        foreach($this->TOC as $package => $TOC1)
        {
            $comp_subs = '';
            $comp_subs1 = false;
            foreach($TOC1 as $subpackage => $types)
            {
                $comp_types = '';
                foreach($types as $type => $files)
                {
                    $comp = '';
                    $templ1 = &$this->newSmarty();
                    $templ1->assign('entry', array());
                    foreach($files as $file)
                    {
					// use book icon for classes
						if ($type == 'Classes') {
	                        $templ1->append('entry', array('paramname' => $file[0],'outputfile' => $file[1],'isclass' => 1));
						} else {
	                        $templ1->append('entry', array('paramname' => $file[0],'outputfile' => $file[1]));
						}
                    }
                    $templ = &$this->newSmarty();
                    $templ->assign('tocsubentries',$templ1->fetch('tocentry.tpl'));
                    $templ->assign('entry', array(array('paramname' => $type)));
                    $comp_types .= $templ->fetch('tocentry.tpl');
                }
                if (!empty($subpackage))
                {
                    $templ = &$this->newSmarty();
                    $templ->assign('tocsubentries',$comp_types);
                    $templ->assign('entry', array(array('paramname' => $subpackage)));
                    $comp_subs .= $templ->fetch('tocentry.tpl');
                } else
                {
                    $comp_subs1 = $comp_types;
                }
            }
            if ($comp_subs1)
            $templ->assign('tocsubentries',$comp_subs1);
            if (!empty($comp_subs))
            $templ->assign('entry', array(array('paramname' => $package, 'tocsubentries' => $comp_subs)));
            else
            $templ->assign('entry', array(array('paramname' => $package)));
            $comppack .= $templ->fetch('tocentry.tpl');
        }
        return $comppack;
    }
    
    function addSourceTOC($name, $file, $package, $subpackage, $source = false)
    {
        $file = str_replace($this->base_dir . PATH_DELIMITER, '', $this->targetDir)
             . PATH_DELIMITER . $file . '.html';
        $file = str_replace('\\',PATH_DELIMITER,$file);
        $file = str_replace('//',PATH_DELIMITER,$file);
        $file = str_replace(PATH_DELIMITER,'\\',$file);
        $sub = $source ? 'Source Code' : 'Examples';
        $this->TOC[$package][$subpackage][$sub][] = array($name, $file);
    }
    
    function addTOC($name,$file,$package,$subpackage,$class = false,$tutorial = false)
    {
        $file = str_replace($this->base_dir . PATH_DELIMITER, '', $this->targetDir . PATH_DELIMITER)
             . $file . '.html';
        $file = str_replace('\\',PATH_DELIMITER,$file);
        $file = str_replace('//',PATH_DELIMITER,$file);
        $file = str_replace(PATH_DELIMITER,'\\',$file);
        $file = str_replace($this->base_dir . '\\', '', $file);
        $sub = $class ? 'Classes' : 'Files';
        if ($tutorial) $sub = 'Manual';
        $this->TOC[$package][$subpackage][$sub][] = array($name,$file);
    }
    
    /**
     * Add an item to the index.hhk file
     * @param string $name index entry name
     * @param string $file filename containing index
     * @param string $bookmark html anchor of location in file, if any
     * @param string $group group this entry with a string
     * @uses $KLinks tracks the index
     * @author Andrew Eddie <eddieajau@users.sourceforge.net>
     */
    function addKLink($name, $file, $bookmark='', $group='')
    {
        $file = $file . '.html';
        $file = str_replace('\\',PATH_DELIMITER,$file);
        $file = str_replace('//',PATH_DELIMITER,$file);
        $file = str_replace(PATH_DELIMITER,'\\',$file);
//        debug("added $name, $file, $bookmark, $group ");
		$link = $file;
		$link .= $bookmark ? "#$bookmark" :'';
		if ($group) {
	        $this->KLinks[$group]['grouplink'] = $file;
	        $this->KLinks[$group][] = array($name,$link);
		}
	    $this->KLinks[] = array($name,$link);
    }

    /**
     * Get the table of contents for index.hhk
     * @return string contents of tocentry.tpl generated from $KLinks
     * @author Andrew Eddie <eddieajau@users.sourceforge.net>
     */
    function generateKLinks()
    {
        $templ = &$this->newSmarty();
		$templ->assign('entry', array());
        foreach($this->KLinks as $group=>$link)
        {
			if (isset($link['grouplink'])) {
				$templg = &$this->newSmarty();
				$templg->assign('entry', array());
				foreach($link as $k=>$sublink)
				{
					if ($k != 'grouplink') {
						$templg->append('entry', array('paramname' => $sublink[0],'outputfile' => $sublink[1]));
					}
				}
				$templ->append('entry', array('paramname' => $group, 'outputfile' => $link['grouplink'], 'tocsubentries' => $templg->fetch('tocentry.tpl') ));
			} else {
				$templ->append('entry', array('paramname' => $link[0],'outputfile' => $link[1]));
			}
		}
        return $templ->fetch('tocentry.tpl');
    }
    
    /**
     * Create the phpdoc.hhp, contents.hhc files needed by MS HTML Help Compiler
     * to create a CHM file
     *
     * The output function generates the table of contents (contents.hhc)
     * and file list (phpdoc.hhp) files used to create a .CHM by the
     * free MS HTML Help compiler.
     * {@internal
     * Using {@link $hhp_files}, a list of all separate .html files
     * is created in CHM format, and written to phpdoc.hhp.  This list was
     * generated by {@link writefile}.
     *
     * Next, a call to the table of contents:
     *
     * {@source 12 2}
     *
     * finishes things off}}
     * @todo use to directly call html help compiler hhc.exe
     * @link http://www.microsoft.com/downloads/release.asp?releaseid=33071
     * @uses generateTOC() assigns to the toc template variable
     */
    function Output()
    {
        $templ = &$this->newSmarty();
        $templ->assign('files',$this->hhp_files);
        $this->setTargetDir($this->base_dir);
        Converter::writefile('phpdoc.hhp',$templ->fetch('hhp.tpl'));
        $templ = &$this->newSmarty();
        $templ->assign('toc',$this->generateTOC());
        Converter::writefile('contents.hhc',$templ->fetch('contents.hhc.tpl'));
		$templ->assign('klinks',$this->generateKLinks());
        Converter::writefile('index.hhk',$templ->fetch('index.hhk.tpl'));
        phpDocumentor_out("NOTE: to create the documentation.chm file, you must now run Microsoft Help Workshop on phpdoc.hhp\n");
        phpDocumentor_out("To get the free Microsoft Help Workshop, browse to: http://go.microsoft.com/fwlink/?LinkId=14188\n");
        flush();
    }
}

/**
 * @access private
 * @global string name of the package to set as the first package
 */
function CHMdefault_pindexcmp($a, $b)
{
    global $phpDocumentor_DefaultPackageName;
    if ($a['title'] == $phpDocumentor_DefaultPackageName) return -1;
    if ($b['title'] == $phpDocumentor_DefaultPackageName) return 1;
    return strnatcasecmp($a['title'],$b['title']);
}

/** @access private */
function CHMdefault_lettersort($a, $b)
{
    return strnatcasecmp($a['letter'],$b['letter']);
}

/** @access private */
function CHMdefault_outputfilter($src, &$smarty)
{
    return str_replace('{$subdir}',$smarty->_tpl_vars['subdir'],$src);
}
?>
