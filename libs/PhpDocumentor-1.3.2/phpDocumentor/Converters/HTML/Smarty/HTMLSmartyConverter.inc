<?php
/**
 * HTML output converter for Smarty Template.
 * This Converter takes output from the {@link Parser} and converts it to HTML-ready output for use with {@link Smarty}.
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2000-2006 Joshua Eichorn, Gregory Beaver
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
 * @subpackage HTMLframes
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2000-2006 Joshua Eichorn, Gregory Beaver
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
 * HTML output converter.
 * This Converter takes output from the {@link Parser} and converts it to HTML-ready output for use with {@link Smarty}.
 *
 * @package Converters
 * @subpackage HTMLSmarty
 * @see parserDocBlock, parserInclude, parserPage, parserClass, parserDefine, parserFunction, parserMethod, parserVar
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0rc1
 * @version $Revision: 1.14 $
 */
class HTMLSmartyConverter extends Converter
{
    /**
     * This converter knows about the new root tree processing
     * In order to fix PEAR Bug #6389
     * @var boolean
     */
    var $processSpecialRoots = true;
    /**
     * Smarty Converter wants elements sorted by type as well as alphabetically
     * @see Converter::$sort_page_contents_by_type
     * @var boolean
     */
    var $sort_page_contents_by_type = true;
    /** @var string */
    var $outputformat = 'HTML';
    /** @var string */
    var $name = 'Smarty';
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
    var $wrote = false;
    var $ric_set = array();
    
    /**
     * sets {@link $base_dir} to $targetDir
     * @see Converter()
     */
    
    /**#@+
     * @access private
     */
    var $_classleft_cache = false;
    var $_classcontents_cache = false;
    var $_pagecontents_cache = false;
    var $_pageleft_cache = false;
    var $_done_package_index = false;
    var $_ric_done = false;
    var $_wrote_tdir = false;
    var $ric_contents = array();
    /**#@-*/
    
    function HTMLSmartyConverter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $templateDir, $title)
    {
        Converter::Converter($allp, $packp, $classes, $procpages,$po, $pp, $qm, $targetDir, $templateDir, $title);
        $this->base_dir = $targetDir;
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
        $templ->assign("subdir",'../');
        $templ->register_outputfilter('HTMLSmarty_outputfilter');
        $this->setTargetDir($this->getFileSourcePath($this->base_dir));
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
        $templ->register_outputfilter('HTMLSmarty_outputfilter');
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . '__examplesource');
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
            return '<li><div class="src-line">' . str_replace("\n",'',$line) . "$extra</div></li>\n";
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
        $trans = $this->template_options['desctranslate'];
        $this->template_options['desctranslate'] = array();
        $example = '<ol>' . parent::ProgramExample($example, $tutorial, $inlinesourceparse, $class, $linenum, $filesourcepath)
               .'</ol>';
        $this->template_options['desctranslate'] = $trans;
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

    function unmangle($sourcecode)
    {
        $sourcecode = str_replace('&nbsp;',' ',$sourcecode);
        $sourcecode = str_replace('&amp;','&',$sourcecode);
        $sourcecode = str_replace('<br />',"<br>",$sourcecode);
        $sourcecode = str_replace('<code>','<pre>',$sourcecode);
        $sourcecode = str_replace('</code>','</pre>',$sourcecode);
        $sourcecode = str_replace('&lt;','<',$sourcecode);
        $sourcecode = str_replace('&gt;','>',$sourcecode);
        return $sourcecode;
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
        $this->makeLeft();
        $templ->assign("ric",$this->ric_set);
        $templ->assign("packageindex",$this->package_index);
        $templ->assign('hastodos',count($this->todoList));
        $templ->assign('todolink','todolist.html');
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
        $classleft = $this->getClassLeft();
        $this->class_data->assign("compiledfileindex",$this->getPageLeft());
        $this->class_data->assign("compiledclassindex",$classleft['class']);
        $this->class_data->assign("compiledinterfaceindex",$classleft['interface']);
        $this->class_data->assign("tutorials",$this->getTutorialList());
        $this->class_data->assign("contents",$this->getClassContents());
        $this->class_data->assign("packageindex",$this->package_index);
        $this->class_data->assign("package",$this->package);
        $this->class_data->assign("subdir",$a);
        $this->class_data->register_outputfilter('HTMLSmarty_outputfilter');
        $this->writefile($this->class . '.html',$this->class_data->fetch('class.tpl'));
        unset($this->class_data);
    }
    
    function getTutorialList()
    {
        static $cache = false;
        if ($cache)
        {
            if (isset($cache[$this->package])) return $cache[$this->package];
        }
        $package = $this->package;
        if (!isset($this->tutorials[$package])) return false;
        foreach($this->tutorials[$package] as $subpackage => $blah)
        {
            $subpackages[] = $subpackage;
        }
        $tutes = array();
        foreach($subpackages as $subpackage)
        {
            if (isset($this->tutorial_tree) && is_array($this->tutorial_tree))
            foreach($this->tutorial_tree as $root => $tr)
            {
                if ($tr['tutorial']->package == $package && $tr['tutorial']->subpackage == $subpackage)
                $tutes[$tr['tutorial']->tutorial_type][] = $this->getTutorialTree($tr['tutorial']);
            }
        }
        $cache[$this->package] = $tutes;
        return $tutes;
    }
    
    function getTutorialTree($tutorial,$k = false)
    {
        $ret = '';
        if (is_object($tutorial)) $tree = parent::getTutorialTree($tutorial); else $tree = $tutorial;
        if (!$tree)
        {
            $template = &$this->newSmarty();
            $template->assign('subtree',false);
            $template->assign('name',str_replace('.','',$tutorial->name));
            $template->assign('parent',false);
            $template->assign('haskids',false);
            $template->assign('kids','');
            $link = new tutorialLink;
            $t = $tutorial;
            $link->addLink('',$t->path,$t->name,$t->package,$t->subpackage,$t->getTitle($this));
            $main = array('link' => $this->getId($link), 'title' => $link->title);
            $template->assign('main',$main);
            return $template->fetch('tutorial_tree.tpl');
        }
        if (isset($tree['kids']))
        {
            foreach($tree['kids'] as $subtree)
            {
                $ret .= $this->getTutorialTree($subtree, true);
            }
        }
        $template = &$this->newSmarty();
        $template->assign('subtree',$k);
        $template->assign('name',str_replace('.','',$tree['tutorial']->name));
        $template->assign('parent',($k ? str_replace('.','',$tree['tutorial']->parent->name) : false));
        $template->assign('haskids',strlen($ret));
        $template->assign('kids',$ret);
        $link = new tutorialLink;
        $t = $tree['tutorial'];
        $link->addLink('',$t->path,$t->name,$t->package,$t->subpackage,$t->getTitle($this));
        $main = array('link' => $this->getId($link), 'title' => $link->title);
        $template->assign('main',$main);
        return $template->fetch('tutorial_tree.tpl');
    }
    
    function getClassLeft()
    {
        if ($this->_classleft_cache)
        {
            if (isset($this->_classleft_cache[$this->package][$this->subpackage])) return $this->_classleft_cache[$this->package][$this->subpackage];
        }
        $arr = $classarr = $interfacearr = array();
        if (isset($this->left['#class'][$this->package]))
        foreach($this->left['#class'][$this->package] as $subpackage => $pages)
        {
            for ($i = 0; $i < count($pages); $i++) {
                if ($pages[$i]['is_interface']) {
                    $interfacearr[$subpackage][] = $pages[$i];
                } else {
                    $classarr[$subpackage][] = $pages[$i];
                }
            }
        }
        $templ = &$this->newSmarty();
        $templ->assign('classleftindex',$classarr);
        $classarr = $templ->fetch('classleft.tpl');
        $this->_classleft_cache[$this->package][$this->subpackage]['class'] = $classarr;
        $templ = &$this->newSmarty();
        $templ->assign('classleftindex',$interfacearr);
        $interfacearr = $templ->fetch('classleft.tpl');
        $this->_classleft_cache[$this->package][$this->subpackage]['interface'] =
            $interfacearr;
        return $this->_classleft_cache[$this->package][$this->subpackage];
    }
    
    function getClassContents()
    {
        if ($this->_classcontents_cache)
        {
            if (isset($this->_classcontents_cache[$this->package][$this->subpackage][$this->class])) return $this->_classcontents_cache[$this->package][$this->subpackage][$this->class];
        }
        $arr = array();
        foreach($this->class_contents[$this->package][$this->subpackage][$this->class] as $i => $link)
        {
            if (is_object($link))
            $arr[$link->type][] = $this->returnSee($link,$link->name);
        }
        $this->_classcontents_cache[$this->package][$this->subpackage][$this->class] = $arr;
        return $arr;
    }
    
    function getPageContents()
    {
        if (!isset($this->path)) $this->path = '#####';
        if ($this->_pagecontents_cache)
        {
            if (isset($this->_pagecontents_cache[$this->package][$this->subpackage][$this->path])) return $this->_pagecontents_cache[$this->package][$this->subpackage][$this->path];
        }
        $arr = array();
        foreach($this->page_contents[$this->curpage->package][$this->curpage->subpackage] as $i => $link)
        {
            if (is_object($link))
            $arr[$link->type][$i] = $this->returnSee($link);
        }
        $this->_pagecontents_cache[$this->package][$this->subpackage][$this->path] = $arr;
        return $arr;
    }
    
    function getPageLeft()
    {
        if ($this->_pageleft_cache)
        {
            if (isset($this->_pageleft_cache[$this->package][$this->subpackage])) return $this->_pageleft_cache[$this->package][$this->subpackage];
        }
        $arr = array();
        if (isset($this->left[$this->package]))
        foreach($this->left[$this->package] as $subpackage => $pages)
        {
            $arr[$subpackage] = $pages;
        }
        $templ = &$this->newSmarty();
        $templ->assign('fileleftindex',$arr);
        $arr = $templ->fetch('fileleft.tpl');
        $this->_pageleft_cache[$this->package][$this->subpackage] = $arr;
        return $arr;
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
        $classleft = $this->getClassLeft();
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $this->page_dir);
        $this->page_data->assign("contents",$this->getPageContents());
        $this->page_data->assign("compiledfileindex",$this->getPageLeft());
        $this->page_data->assign("compiledclassindex",$classleft['class']);
        $this->page_data->assign("compiledinterfaceindex",$classleft['interface']);
        $this->page_data->assign("tutorials",$this->getTutorialList());
        $this->page_data->assign("packageindex",$this->package_index);
        $this->page_data->assign("package",$this->package);
        $this->page_data->assign("subdir",$a);
        $this->page_data->register_outputfilter('HTMLSmarty_outputfilter');
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
    
    function makeLeft()
    {
        if ($this->_done_package_index) return;
        $this->_done_package_index = true;
        if (!isset($this->package_index))
        foreach($this->all_packages as $key => $val)
        {
            if (isset($this->pkg_elements[$key]))
            {
                if (!isset($start)) $start = $key;
                $this->package_index[] = array('link' => "li_$key.html", 'title' => $key);
            }
        }
        foreach($this->page_elements as $package => $o1)
        {
            foreach($o1 as $subpackage => $links)
            {
                for($i=0;$i<count($links);$i++)
                {
                    $this->left[$package][$subpackage][] =
                        array("link" => $this->getId($links[$i]), "title" => $links[$i]->name);
                }
            }
        }
        foreach($this->class_elements as $package => $o1)
        {
            foreach($o1 as $subpackage => $links)
            {
                for($i=0;$i<count($links);$i++)
                {
                    $isinterface = false;
                    if ($links[$i]->type == 'class') {
                        $class = $this->classes->getClass($links[$i]->name,
                            $links[$i]->path);
                        if ($class) {
                            $isinterface = $class->isInterface();
                        }
                    }
                    $this->left['#class'][$package][$subpackage][] =
                        array("link" => $this->getId($links[$i]), "title" => $links[$i]->name, 'is_interface' => $isinterface);
                }
            }
        }
    }
    
    /**
     * HTMLdefaultConverter chooses to format both package indexes and the complete index here
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
            $classleft = $this->getClassLeft();
            $template->assign("compiledfileindex",$this->getPageLeft());
            $template->assign("compiledclassindex",$classleft['class']);
            $template->assign("compiledinterfaceindex",$classleft['interface']);
            $template->assign("tutorials",$this->getTutorialList());
            $template->assign("index",$package_indexes[$i]['pindex']);
            $template->assign("package",$package_indexes[$i]['package']);
            $template->assign("letters",$mletters[$package_indexes[$i]['package']]);
            $template->assign("title","Package ".$package_indexes[$i]['package']." Element Index");
            $template->assign("date",date("r",time()));
            $template->register_outputfilter('HTMLSmarty_outputfilter');
            $this->setTargetDir($this->base_dir);
            $this->writefile('elementindex_'.$package_indexes[$i]['package'].'.html',$template->fetch('pkgelementindex.tpl'));
        }
        phpDocumentor_out("\n");
        flush();
        }
    
    /**
     * HTMLdefaultConverter uses this function to format template index.html and packages.html
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
        if (!isset($this->package_index))
        {
            debug("\nERROR: Nothing parsed, check the command-line");
            die();
        }
        list($elindex,$mletters) = $this->generateElementIndex();
        $template = &$this->newSmarty();
        $template->assign("index",$elindex);
        $template->assign("letters",$mletters);
        $template->assign("title","Element Index");
		$template->assign("package", false);
        $template->assign("date",date("r",time()));
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        $template->register_outputfilter('HTMLSmarty_outputfilter');
        $this->writefile('elementindex.html',$template->fetch('elementindex.tpl'));
        usort($this->package_index,"HTMLSmarty_pindexcmp");
        $index = &$this->newSmarty();
        foreach($this->all_packages as $key => $val)
        {
            if (isset($this->pkg_elements[$key]))
            {
                if (!isset($start)) $start = $key;
                if (!isset($this->package_pages[$key])) $this->writeNewPPage($key);
            }
        }
        // Created index.html
        $start = $phpDocumentor_DefaultPackageName;
        if (!isset($this->pkg_elements[$key]))
        {
            // if there are no elements, use a random package as the default
            $a = array_keys($this->pkg_elements);
            $start = array_shift($a);
        }
        $this->package = $start;
        $this->subpackage = '';
        $classleft = $this->getClassLeft();
        $index->assign("compiledfileindex",$this->getPageLeft());
        $index->assign("compiledclassindex",$classleft['class']);
        $index->assign("compiledinterfaceindex",$classleft['interface']);
        $index->assign('hastodos',count($this->todoList));
        $index->assign('todolink','todolist.html');
        $index->assign("tutorials",$this->getTutorialList());
        $index->assign("date",date("r",time()));
        $index->assign("package",$this->package);
        $index->assign("title",$this->title);
        $index->assign("start","li_$start.html");
        if (isset($this->package_pages[$start]))
        {
            $index->assign("contents",$this->package_pages[$start]);
        }
        $index->register_outputfilter('HTMLSmarty_outputfilter');
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        $this->writefile("index.html",$index->fetch('index.tpl'));
        unset($index);

    }
    
    function writeNewPPage($key)
    {
        $template = &$this->newSmarty();
        $this->package = $key;
        $this->subpackage = '';
        $classleft = $this->getClassLeft();
        $template->assign("compiledfileindex",$this->getPageLeft());
        $template->assign("compiledclassindex",$classleft['class']);
        $template->assign("compiledinterfaceindex",$classleft['interface']);
        $template->assign("tutorials",$this->getTutorialList());
        $template->assign("date",date("r",time()));
        $template->assign("title",$this->title);
        $template->assign("package",$key);
        $template->register_outputfilter('HTMLSmarty_outputfilter');
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        $this->writefile("li_$key.html",$template->fetch('index.tpl'));
        unset($template);
    }
    
    /**
     * Generate indexes for li_package.html and classtree output files
     *
     * This function generates the li_package.html files from the template file left.html.  It does this by
     * iterating through each of the $page_elements, $class_elements and  $function_elements arrays to retrieve
     * the pre-sorted {@link abstractLink} descendants needed for index generation.  Conversion of these links to
     * text is done by {@link returnSee()}.
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
        if (!isset($this->left))
        {
            debug("Nothing parsed, check the command-line");
            die();
        }
        foreach($this->all_packages as $package => $rest)
        {
            if (!isset($this->pkg_elements[$package])) continue;
            // Create class tree page
            $template = &$this->newSmarty();
            $classleft = $this->getClassLeft();
            $template->assign("compiledfileindex",$this->getPageLeft());
            $template->assign("compiledclassindex",$classleft['class']);
            $template->assign("compiledinterfaceindex",$classleft['interface']);
            $template->assign("classtrees",$this->generateFormattedClassTrees($package));
            $template->assign("interfaces",$this->generateFormattedInterfaceTrees($package));
            $template->assign("package",$package);
            $template->assign("date",date("r",time()));
            $template->assign("title","Class Trees for Package $package");
            $template->register_outputfilter('HTMLSmarty_outputfilter');
            $this->writefile("classtrees_$package.html",$template->fetch('classtrees.tpl'));
            phpDocumentor_out("\n");
            flush();
        }
        $this->writeRIC();
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
     * @param boolean determines whether the returned text is enclosed in an &lt;a> tag
     */
    function returnSee(&$element, $eltext = false, $with_a = true)
    {
        if (!is_object($element) || !$element) return false;
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
    
    /**
     * Convert README/INSTALL/CHANGELOG file contents to output format
     * @param README|INSTALL|CHANGELOG
     * @param string contents of the file
     */
    function Convert_RIC($name, $contents)
    {
        $this->ric_contents[$name] = $contents;
        $this->ric_set[] = array('file' => 'ric_'.$name . '.html','name' => $name);
    }
    
    function writeRIC()
    {
        if ($this->_ric_done) return;
        $this->_ric_done = true;
        foreach($this->ric_contents as $name => $contents)
        {
            $template = &$this->newSmarty();
            $template->assign('contents',$contents);
            $template->assign('name',$name);
            $template->assign('title',$name);
            $this->setTargetDir($this->base_dir);
            $this->writefile('ric_'.$name . '.html',$template->fetch('ric.tpl'));
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
        $templ->register_outputfilter('HTMLSmarty_outputfilter');
        $this->setTargetDir($this->base_dir);
        $this->writefile('todolist.html',$templ->fetch('todolist.tpl'));
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
        $template->register_outputfilter('HTMLSmarty_outputfilter');
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
        $template = &$this->newSmarty();
        $this->package = $element->package;
        $this->subpackage = '';
        $classleft = $this->getClassLeft();
        $template->assign("compiledfileindex",$this->getPageLeft());
        $template->assign("compiledclassindex",$classleft['class']);
        $template->assign("compiledinterfaceindex",$classleft['interface']);
        $template->assign("tutorials",$this->getTutorialList());
        $template->assign("date",date("r",time()));
        $template->assign("title",$this->title);
        $template->assign("package",$element->package);
        $x = $element->Convert($this);
        $x = substr($x,strpos($x,'<body'));
        $template->assign("contents",trim(substr($x,strpos($x,'>') + 1)));
        $this->package_pages[$element->package] = trim(substr($x,strpos($x,'>') + 1));
        $template->register_outputfilter('HTMLSmarty_outputfilter');
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
        $this->writefile("li_".$element->package.".html",$template->fetch('index.tpl'));
        unset($template);
    }
    
    /**
     * @param parserTutorial
     */
    function convertTutorial(&$element)
    {
        phpDocumentor_out("\n");
        flush();
        $template = &parent::convertTutorial($element);
        $this->package = $element->package;
        $this->subpackage = $element->subpackage;
        $classleft = $this->getClassLeft();
        $template->assign("compiledfileindex",$this->getPageLeft());
        $template->assign("compiledclassindex",$classleft['class']);
        $template->assign("compiledinterfaceindex",$classleft['interface']);
        $template->assign("tutorials",$this->getTutorialList());
        $template->assign("title",strip_tags($element->getTitle($this)));
        $contents = $element->Convert($this);
        if ($element->name == $this->package . '.pkg')
        {
            $this->package_pages[$element->package] = $contents;
        }
        $a = '../';
        if (!empty($element->subpackage)) $a .= $a;
        $template->assign("subdir",$a);
        $a = '';
        if ($element->subpackage) $a = PATH_DELIMITER . $element->subpackage;
        $template->register_outputfilter('HTMLSmarty_outputfilter');
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . $element->package . $a);
        $this->writeFile('tutorial_'.$element->name.'.html',$template->fetch('tutorial.tpl'));
        if ($element->name == $element->package . '.pkg')
        {
            phpDocumentor_out("\n");
            flush();
            // package-level docs
            $this->setTargetDir($this->base_dir);
            $template->assign("subdir",'');
            $this->writeFile('li_'.$element->package.'.html',$template->fetch('tutorial.tpl'));
        }
        unset($template);
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
        $a = '../classtrees_';
        if ($element->docblock->subpackage != '') $a = "../$a";
        
        $this->class_data->assign('subdir',$a);
        $this->class_data->assign("title","Docs For Class " . $element->getName());
        $this->class_data->assign("page",$element->getName() . '.html');
    }

    /**
     * Converts class variables for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertVar(&$element)
    {
        parent::convertVar($element, array('var_dest' => $this->getId($element,false)));
    }

    /**
     * Converts class variables for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertConst(&$element)
    {
        parent::convertConst($element, array('const_dest' => $this->getId($element,false)));
    }

    /**
     * Converts class methods for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertMethod(&$element)
    {
        parent::convertMethod($element, array('method_dest' => $this->getId($element,false)));
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
    }
    
    /**
     * Converts include elements for template output
     * @see prepareDocBlock()
     * @param parserInclude
     */
    function convertInclude(&$element)
    {
        parent::convertInclude($element, array('include_file'    => '_'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '_'))));
    }
    
    /**
     * Converts defines for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertDefine(&$element)
    {
        parent::convertDefine($element, array('define_link' => $this->getId($element,false)));
    }
    
    /**
     * Converts global variables for template output
     * @param parserGlobal
     * @see prepareDocBlock(), getFormattedConflicts()
     */
    function convertGlobal(&$element)
    {
        parent::convertGlobal($element, array('global_link' => $this->getId($element,false)));
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
        $a = '../';
        if (!empty($element->docblock->subpackage)) $a = $a . $a;
        $this->page_data->assign('subdir',$a);
        $this->page_data->assign("page",$this->getPageName($element) . '.html');
        $this->page_data->assign("title","Docs for page ".$element->parent->getFile());
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
                $root = $this->classes->getClassByPackage($tree[$cur]['parent']->name,
                    $package);
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
                    $root = $this->classes->getClassByPackage(
                        $tree[$cur]['link']->name,
                        $tree[$cur]['link']->package);
                    if ($implements = $root->getImplements()) {
                        $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link']) .
                            ' (implements ';
                        foreach ($implements as $i => $interface) {
                            if ($i && $i != count($implements) - 1) $my_tree .= ', ';
                            if ($link = $this->getLink('object ' .  $interface)) {
                                $my_tree .= $this->returnSee($link);
                            } else {
                                $my_tree .= $interface;
                            }
                        }
                        $my_tree .= ')';
                    } else {
                        $my_tree .= '<li>'.$this->returnSee($tree[$cur]['link']);
                    }
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
        if ($this->_wrote_tdir) return;
        $this->_wrote_tdir = true;
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
        usort($letters,'HTMLSmarty_lettersort');
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
     * This function is not used by HTMLdefaultConverter, but is required by Converter
     */
    function Output()
    {
    }
}

/**
 * @access private
 * @global string name of the package to set as the first package
 */
function HTMLSmarty_pindexcmp($a, $b)
{
    global $phpDocumentor_DefaultPackageName;
    if ($a['title'] == $phpDocumentor_DefaultPackageName) return -1;
    if ($b['title'] == $phpDocumentor_DefaultPackageName) return 1;
    return strnatcasecmp($a['title'],$b['title']);
}

/** @access private */
function HTMLSmarty_lettersort($a, $b)
{
    return strnatcasecmp($a['letter'],$b['letter']);
}

/** @access private */
function HTMLSmarty_outputfilter($src, &$smarty)
{
    return str_replace('{$subdir}',$smarty->_tpl_vars['subdir'],$src);
}
?>
