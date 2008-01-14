<?php
//
// +------------------------------------------------------------------------+
// | phpDocumentor                                                          |
// +------------------------------------------------------------------------+
// | Copyright (c) 2000-2003 Joshua Eichorn, Gregory Beaver                 |
// | Email         jeichorn@phpdoc.org, cellog@phpdoc.org                   |
// | Web           http://www.phpdoc.org                                    |
// | Mirror        http://phpdocu.sourceforge.net/                          |
// | PEAR          http://pear.php.net/package-info.php?pacid=137           |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
/**
 * XML output converter for DocBook.
 * This Converter takes output from the {@link Parser} and converts it to DocBook output
 *
 * @package Converters
 * @subpackage XMLDocBook
 * @see parserDocBlock, parserInclude, parserPage, parserClass, parserDefine, parserFunction, parserMethod, parserVar
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0
 * @version $Id$
 */
/**
 * XML DocBook converter.
 * This Converter takes output from the {@link Parser} and converts it to DocBook output
 *
 * @package Converters
 * @subpackage XMLDocBook
 * @see parserDocBlock, parserInclude, parserPage, parserClass, parserDefine, parserFunction, parserMethod, parserVar
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.0
 * @version $Id$
 * @todo indexes for other DocBook converters not based on peardoc
 * @todo templates
 */
class XMLDocBookConverter extends Converter
{
    /**
     * XMLDocBookConverter wants elements sorted by type as well as alphabetically
     * @see Converter::$sort_page_contents_by_type
     * @var boolean
     */
    var $sort_page_contents_by_type = true;
    /** @var string */
    var $outputformat = 'XML';
    /** @var string */
    var $name = 'DocBook';
    /**
     * indexes of elements by package that need to be generated
     * @var array
     */
    var $leftindex = array('classes' => true, 'pages' => true, 'functions' => false, 'defines' => false, 'globals' => false);
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
    var $sourceloc = '';
    /**
     * peardoc2 Category
     * @var string
     */
    var $category;
    /**
     * sets {@link $base_dir} to $targetDir
     * @see Converter()
     */
    function XMLDocBookConverter(&$allp, &$packp, &$classes, &$procpages, $po, $pp, $qm, $targetDir, $templateDir, $title)
    {
        die("XMLDocBookConverter is not supported in this development version.  Use XML:DocBook/peardoc2:default");
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
    
    function &SmartyInit(&$templ)
    {
        $this->makeLeft();
        $templ->assign("packageindex",$this->package_index);
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
        foreach($this->method_data as $func)
        {
            $func[0]->assign("phpdocversion",PHPDOCUMENTOR_VER);
            $func[0]->assign("phpdocwebsite",PHPDOCUMENTOR_WEBSITE);
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->class_dir . PATH_DELIMITER . str_replace(array('_','.'),array('-','--'),$this->class)));
            $this->writefile(strtolower($func[1] ). '.xml',$func[0]->fetch('method.tpl'));
        }
        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        {
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->class_dir) . PATH_DELIMITER . str_replace(array('_','.'),array('-','--'),strtolower($this->class)));
            $this->writefile(str_replace(array('_','.'),array('-','--'),strtolower($this->class)).'-summary.xml',$this->class_summary->fetch('class_summary.tpl'));
        }
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->class_dir));
        $this->writefile(str_replace(array('_','.'),array('-','--'),strtolower($this->class)) . '.xml',$this->class_data->fetch('class.tpl'));
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
        foreach($this->function_data as $func)
        {
            $func[0]->assign("phpdocversion",PHPDOCUMENTOR_VER);
            $func[0]->assign("phpdocwebsite",PHPDOCUMENTOR_WEBSITE);
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->page_dir . PATH_DELIMITER . $this->page));
            $this->writefile(str_replace('_','-',strtolower($func[1])) . '.xml',$func[0]->fetch('function.tpl'));
        }
        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        {
            $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->page_dir . PATH_DELIMITER . $this->page));
            $this->writefile(strtolower($this->page).'-summary.xml',$this->page_summary->fetch('page_summary.tpl'));
        }
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($this->page_dir));
        $this->writefile(strtolower($this->page) . '.xml',$this->page_data->fetch('page.tpl'));
        unset($this->page_data);
    }
    
    /**
     * @param string
     * @param string
     * @return string &lt;ulink url="'.$link.'">'.$text.'</ulink&gt;
     */
    function returnLink($link,$text)
    {
        return '<ulink url="'.$link.'">'.$text.'</ulink>';
    }
    
    function makeLeft()
    {
        static $done = false;
        if ($done) return;
        $done = true;
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
                        array("link" => $this->returnSee($links[$i], false, false, false), "title" => $links[$i]->name);
                }
            }
        }
        foreach($this->class_elements as $package => $o1)
        {
            foreach($o1 as $subpackage => $links)
            {
                for($i=0;$i<count($links);$i++)
                {
                    $this->left['#class'][$package][$subpackage][] =
                        array("link" => $this->returnSee($links[$i], false, false, false), "title" => $links[$i]->name);
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
        // will implement in next release with other templates than peardoc2
        return;
        list($package_indexes,$packages,$mletters) = $this->generatePkgElementIndexes();
        for($i=0;$i<count($package_indexes);$i++)
        {
            $template = &$this->newSmarty();
            $this->package = $package_indexes[$i]['package'];
            $this->subpackage = '';
            $template->assign("compiledclassindex",$this->getClassLeft());
            $template->assign("compiledfileindex",$this->getPageLeft());
            $template->assign("index",$package_indexes[$i]['pindex']);
            $template->assign("package",$package_indexes[$i]['package']);
            $template->assign("letters",$mletters[$package_indexes[$i]['package']]);
            $template->assign("title","Package ".$package_indexes[$i]['package']." Element Index");
            $template->assign("date",date("r",time()));
//            $this->writefile('elementindex_'.$package_indexes[$i]['package'].'.html',$template->fetch('pkgelementindex.tpl'));
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
        // will implement in next release with other templates than peardoc2
        return;
        global $phpDocumentor_DefaultPackageName;
        list($elindex,$mletters) = $this->generateElementIndex();
        $template = &$this->newSmarty();
        $template->assign("index",$elindex);
        $template->assign("letters",$mletters);
        $template->assign("title","Element Index");
        $template->assign("date",date("r",time()));
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
//        $this->writefile('elementindex.html',$template->fetch('elementindex.tpl'));
        uksort($this->package_index,"strnatcasecmp");
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
        if (isset($this->package_index[$phpDocumentor_DefaultPackageName])) $start = $phpDocumentor_DefaultPackageName;
        $this->package = $start;
        $this->subpackage = '';
        $index->assign("compiledclassindex",$this->getClassLeft());
        $index->assign("compiledfileindex",$this->getPageLeft());
        $index->assign("date",date("r",time()));
        $index->assign("title",$this->title);
        $index->assign("start","li_$start.html");
        if (isset($this->package_pages[$start]))
        {
            $index->assign("contents",$this->package_pages[$start]);
        }
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
//        $this->writefile("index.html",$index->fetch('index.tpl'));
        unset($index);

    }
    
    function writeNewPPage($key)
    {
        // will implement in next release with other templates than peardoc2
        return;
        $template = &$this->newSmarty();
        $this->package = $key;
        $this->subpackage = '';
        $template->assign("compiledclassindex",$this->getClassLeft());
        $template->assign("compiledfileindex",$this->getPageLeft());
        $template->assign("date",date("r",time()));
        $template->assign("title",$this->title);
        $template->assign("package",$key);
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
//        $this->writefile("li_$key.html",$template->fetch('index.tpl'));
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
        // will implement in next release with other templates than peardoc2
        return;
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
            $template->assign("classtrees",$this->generateFormattedClassTrees($package));
            $template->assign("package",$package);
            $template->assign("date",date("r",time()));
            $template->assign("title","Class Trees for Package $package");
//            $this->writefile("classtrees_$package.html",$template->fetch('classtrees.tpl'));
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
     * @param boolean determines whether the returned text is enclosed in an &lt;a> tag
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
                if ($element->type == 'function' || $element->type == 'method') $eltext .= '()</function>';
                if ($element->type == 'var') $eltext .= '</varname>';
                if ($element->type == 'define') $eltext .= '</constant>';
                break;
            }
        }
        $a = '';
        if (!empty($element->subpackage))
        {
            $a = $element->subpackage.'.';
        }
        switch ($element->type)
        {
            case 'page' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a).$element->fileAlias.'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a).$element->fileAlias;
            break;
            case 'define' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a).$element->fileAlias.'.'.urlencode($element->name).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a).$element->fileAlias.'.'.urlencode($element->name);
            break;
            case 'global' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a).$element->fileAlias.'.'.urlencode($element->name).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a).$element->fileAlias.'.'.urlencode($element->name);
            break;
            case 'class' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name)).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name));
            break;
            case 'function' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a.$element->fileAlias.'.'.str_replace('_','-',$element->name)).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a.$element->fileAlias.'.'.str_replace('_','-',$element->name));
            break;
            case 'method' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.str_replace('_','-',$element->name)).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.str_replace('_','-',$element->name));
            break;
            case 'var' :
            if ($with_a)
            return '<link linkend="package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.$element->name).'">'.$eltext.'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.$element->name);
            break;
            case 'tutorial' :
            if ($with_a)
            return '<link linked="package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name).'-tutorial">'.$eltext).'</link>';
            else
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name)).'-tutorial';
        }
    }
    
    /**
     * Get the id value needed to allow linking
     * @param mixed descendant of parserElement or parserData/parserPage
     * @see parserElement, parserData, parserPage
     * @return string the id value for this element type
     */
    function getId(&$el)
    {
        if (phpDocumentor_get_class($el) == 'parserdata')
        {
            $element = $this->addLink($el->parent);
            $elp = $el->parent;
        } else
        {
            $elp = $el;
            $element = $this->addLink($el);
        }
        $a = '';
        if (!empty($element->subpackage))
        {
            $a = $element->subpackage.'.';
        }
        switch ($element->type)
        {
            case 'page' :
            return 'package.'.strtolower($element->package.'.'.$a.$element->fileAlias);
            break;
            case 'define' :
            return 'package.'.$element->package.'.'.strtolower($a.$element->fileAlias.'.'.str_replace(array('$','_','"',"'"),array('var--','-','-','-'),$element->name));
            break;
            case 'global' :
            return 'package.'.strtolower($element->package.'.'.$a.$element->fileAlias.'.'.str_replace(array('$','_','"',"'"),array('var--','-','-','-'),$element->name));
            break;
            case 'class' :
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name));
            break;
            case 'function' :
            return 'package.'.strtolower($element->package.'.'.$a.$element->fileAlias.'.'.str_replace('_','-',$element->name));
            break;
            case 'method' :
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'.'.str_replace('_','-',$element->name));
            break;
            case 'var' :
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->class).'-summary.vars.'.str_replace(array('$','_'),array('var--','-'),$element->name));
            break;
            case 'tutorial' :
            return 'package.'.strtolower($element->package.'.'.$a.str_replace(array('_','.'),array('-','--'),$element->name)).'-tutorial';
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
        return htmlentities($text);
    }

    function prepareDocBlock(&$element, $nopackage = true)
    {
        return parent::prepareDocBlock($element, array('staticvar' => 'staticvar','deprec' => 'deprecated','abstract' => 'abstract','TODO' => 'todo', 'uses' => 'see', 'usedby' => 'see', 'tutorial' => 'see'), $nopackage);
    }
    
    function getTutorialId($package,$subpackage,$tutorial,$id)
    {
        $subpackage = (empty($subpackage) ? '' : '.'.$subpackage);
        $id = (empty($id) ? '' : '.'.$id);
        return 'package.'.strtolower($package.$subpackage.str_replace(array('_','.'),array('-','--'),$tutorial).$id);
    }
    
    function getCData($value)
    {
        return '<!CDATA['.$value.']]>';
    }
    
    /**
     * Converts package page and sets its package as used in {@link $package_pages}
     * @param parserPackagePage
     */
    function convertPackagePage(&$element)
    {
        return;
        phpDocumentor_out("\n");
        flush();
        $template = &$this->newSmarty();
        $this->package = $element->package;
        $this->subpackage = '';
        $template->assign("compiledclassindex",$this->getClassLeft());
        $template->assign("compiledfileindex",$this->getPageLeft());
        $template->assign("date",date("r",time()));
        $template->assign("title",$this->title);
        $template->assign("package",$element->package);
        $x = $element->Convert($this);
        $x = substr($x,strpos($x,'<body'));
        $template->assign("contents",trim(substr($x,strpos($x,'>') + 1)));
        phpDocumentor_out("\n");
        flush();
        $this->setTargetDir($this->base_dir);
//        $this->writefile("li_".$element->package.".html",$template->fetch('index.tpl'));
        unset($template);
        $this->package_pages[$element->package] = trim(substr($x,strpos($x,'>') + 1));
    }
    
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
                $subtutorials .= '      &'.$element->package.$b.'.'.str_replace(array('_','.'),array('-','--'),$child).'-'.$element->tutorial_type."-tutorial;\n";
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
        $this->setTargetDir($this->base_dir . PATH_DELIMITER . strtolower($element->package . $a));
        $this->writeFile(str_replace(array('_','.'),array('-','--'),strtolower($element->name)).'-tutorial.xml',$contents);
    }
    
    /**
     * Converts class variables for template output.
     * @see prepareDocBlock(), getFormattedOverrides()
     * @param parserVar
     */
    function convertVar(&$element)
    {
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
     * @see prepareDocBlock(), generateChildClassList(), generateFormattedClassTree(), getFormattedConflicts()
     * @see getFormattedInheritedMethods(), getFormattedInheritedVars()
     * @param parserClass
     */
    function convertClass(&$element)
    {
        parent::convertClass($element);
        $docblock = $this->prepareDocBlock($element);
        $this->class_dir = $element->docblock->package;
        if (!empty($element->docblock->subpackage)) $this->class_dir .= PATH_DELIMITER . $element->docblock->subpackage;
        $docblock = $this->prepareDocBlock($element,false);
        
        $this->class_data->assign("sdesc",$docblock['sdesc']);
        $this->class_data->assign("desc",$docblock['desc']);
        $this->class_data->assign("tags",$docblock['tags']);

        $this->class_data->assign("source_location",$element->getSourceLocation($this,$this->template_options['usepear']));
        $this->class_data->assign("id",$this->getId($element));
        $this->class_data->assign("method_ids",array());
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
            if (!empty($inherited_vars))
            {
                $this->class_summary->assign("ivars",$inherited_vars);
            }
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
        $fname = $element->getName();
        if ($element->isConstructor)
        {
            $fname = 'constructor '.$element->getName();
        }
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
            $params[$param] = array("var" => $param,"datatype" => $val->converted_returnType,"data" => $a);
        }

        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        {
            $this->class_data->append('method_ids',$this->getId($element));
            $this->class_summary->append('methods',array('id' => $this->getId($element),
                                                      'sdesc' => $docblock['sdesc'],
                                                      'desc' => $docblock['desc'],
                                                      'tags' => $docblock['tags'],
                                                      'is_constructor' => $element->isConstructor,
                                                      'function_name' => $element->getName(),
                                                      'function_return' => $returntype,
                                                      'function_call' => $element->getIntricateFunctionCall($this,$params),
                                                      'descmethod' => $this->getFormattedDescMethods($element),
                                                      'method_overrides' => $this->getFormattedOverrides($element),
                                                      'line_number' => $element->getLineNumber(),
                                                      'params' => $params));
        } else
        {
            $this->class_data->append('method_ids',$this->getId($element));
            $this->class_data->append('methods',array('id' => $this->getId($element),
                                                      'sdesc' => $docblock['sdesc'],
                                                      'desc' => $docblock['desc'],
                                                      'tags' => $docblock['tags'],
                                                      'is_constructor' => $element->isConstructor,
                                                      'function_name' => $element->getName(),
                                                      'function_return' => $returntype,
                                                      'function_call' => $element->getIntricateFunctionCall($this,$params),
                                                      'descmethod' => $this->getFormattedDescMethods($element),
                                                      'method_overrides' => $this->getFormattedOverrides($element),
                                                      'line_number' => $element->getLineNumber(),
                                                      'params' => $params));
        }
        if (!isset($this->method_data)) $this->method_data = array();
        $this->method_data[$i = count($this->method_data) - 1][0] = $this->newSmarty(true);
        $this->method_data[$i][1] = $element->getName();
        $this->method_data[$i][0]->assign('class',$this->class);
        $this->method_data[$i][0]->assign('source_location',$this->returnSee($this->getLink(basename($this->curpage->getFile())),$this->sourceloc));
        $this->method_data[$i][0]->assign('sdesc',$docblock['sdesc']);
        $this->method_data[$i][0]->assign('desc',$docblock['desc']);
        $this->method_data[$i][0]->assign('tags',$docblock['tags']);
        $this->method_data[$i][0]->assign('function_name',$fname);
        $this->method_data[$i][0]->assign('function_return',$returntype);
        $this->method_data[$i][0]->assign('function_call',$element->getIntricateFunctionCall($this,$params));
        $this->method_data[$i][0]->assign('descmethod',$this->getFormattedDescMethods($element));
        $this->method_data[$i][0]->assign('method_overrides',$this->getFormattedOverrides($element));
        $this->method_data[$i][0]->assign('params',$params);
        $this->method_data[$i][0]->assign('id',$this->getId($element));
    }
    
    /**
     * Converts function for template output
     * @see prepareDocBlock(), parserFunction::getFunctionCall(), getFormattedConflicts()
     * @param parserFunction
     */
    function convertFunction(&$element)
    {
        parent::convertFunction($element);
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
        $this->function_data[$i][0]->assign('id',$this->getId($element));
    }
    
    /**
     * Converts include elements for template output
     * @see prepareDocBlock()
     * @param parserInclude
     */
    function convertInclude(&$element)
    {
        parent::convertInclude($element, array('include_file'    => '-'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '-'))));
        $this->page_summary->append('includes',array('sdesc' => $docblock['sdesc'],
                                                   'desc' => $docblock['desc'],
                                                  'tags' => $docblock['tags'],
                                                  'utags' => $docblock['utags'],
                                                  'include_name'     => $element->getName(),
                                                  'include_value'    => $per,
                                                  'line_number' => $element->getLineNumber(),
                                                  'include_file'    => '-'.strtr($element->getValue(),array('"' => '', "'" => '','.' => '-'))));
    }
    
    /**
     * Converts defines for template output
     * @see prepareDocBlock(), getFormattedConflicts()
     * @param parserDefine
     */
    function convertDefine(&$element)
    {
        $docblock = $this->prepareDocBlock($element);
        parent::convertDefine($element, array('link' => urlencode($element->getName())));
        $this->page_summary->append('defines',array('sdesc' => $docblock['sdesc'],
                                                  'desc' => $docblock['desc'],
                                                 'tags' => $docblock['tags'],
                                                 'name'     => $element->getName(),
                                                 'value'    => $element->getValue(),
                                                 'conflicts'    => $this->getFormattedConflicts($element,"defines"),
                                                 'line_number' => $element->getLineNumber(),
                                                 'id' => $this->getId($element)));
    }
    
    /**
     * Converts global variables for template output
     * @param parserGlobal
     * @see prepareDocBlock(), getFormattedConflicts()
     */
    function convertGlobal(&$element)
    {
        parent::convertGlobal($element, array('id' => $this->getId($element)));
        $docblock = $this->prepareDocBlock($element);
        $value = $this->getGlobalValue($element->getValue());
        $this->page_summary->append('globals',array('sdesc' => $docblock['sdesc'],
                                                  'desc' => $docblock['desc'],
                                                 'tags' => $docblock['tags'],
                                                 'name'     => $element->getName(),
                                                 'link'    => urlencode($element->getName()),
                                                 'value'    => $value,
                                                 'type' => $element->getDataType($this),
                                                 'line_number' => $element->getLineNumber(),
                                                 'conflicts'    => $this->getFormattedConflicts($element,"global variables"),
                                                 'id' => $this->getId($element)));
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
        $this->page_data->assign("source_location",$element->parent->getSourceLocation($this,$this->template_options['usepear']));
        $this->page_data->assign("function_ids",array());
        if ($t = $element->getTutorial())
        {
            $this->page_data->append("function_ids",$this->getId($t));
        }

        $this->sourceloc = $element->parent->getSourceLocation($this,$this->template_options['usepear']);
        $this->page_data->assign("id", $this->getId($element));
        if (isset($this->template_options['separatepage']) && $this->template_options['separatepage'])
        {
            $this->page_summary = new Smarty;
            $this->page_summary->template_dir = $this->smarty_dir . PATH_DELIMITER . 'templates';
            $this->page_summary->compile_dir = $this->smarty_dir . PATH_DELIMITER . 'templates_c';
            $this->page_summary->config_dir = $this->smarty_dir . PATH_DELIMITER . 'configs';
            // registering stuff on the template
            $this->page_summary->assign("source_location",$element->parent->getSourceLocation($this,$this->template_options['usepear']));
            $this->page_summary->assign("date",date("r",time()));
            $this->page_summary->assign("functions",array());
            $this->page_summary->assign("includes",array());
            $this->page_summary->assign("defines",array());
            $this->page_summary->assign("globals",array());
            $this->page_summary->assign("classes",$this->getClassesOnPage($element));
            $this->page_summary->assign("name",$element->parent->getFile());
            $this->page_summary->assign("function_ids",array());
            if ($element->docblock)
            {
                $docblock = $this->prepareDocBlock($element, false);
                $this->page_summary->assign("sdesc",$docblock['sdesc']);
                $this->page_summary->assign("desc",$docblock['desc']);
                $this->page_summary->assign("tags",$docblock['tags']);
                $this->page_summary->assign("utags",$docblock['utags']);
            }
            $this->sourceloc = $element->parent->getSourceLocation($this,$this->template_options['usepear']);
            $this->page_summary->assign("name", $element->parent->getFile());
            $this->page_summary->assign("id", $this->getId($element).'.'.$this->getPageName($element->parent).'-summary');
            $this->page_data->append("function_ids",$this->getId($element).'.'.strtolower($this->getPageName($element->parent)).'-summary');
        }
    }
    
    function getPageName(&$element)
    {
        return str_replace(array('/','_','.'),array('-','-','---'),$element->getSourceLocation($this,$this->template_options['usepear']));
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
     * Generate alphabetical index of all elements
     *
     * @see $elements, walk()
     */
    function generateElementIndex()
    {
        $elementindex = array();
        $letters = array();
        $i = 0;
        foreach($this->elements as $letter => $nutoh)
        {
            $letters[]['letter'] = $letter;
            $elindex['letter'] = $letter;
            foreach($this->elements[$letter] as $i => $yuh)
            {
                switch($this->elements[$letter][$i]->type)
                {
                    case 'class':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', class '.$this->getClassLink($this->elements[$letter][$i]->getName(),
                                                $this->elements[$letter][$i]->docblock->package,
                                                $this->elements[$letter][$i]->getPath(),
                                                $this->elements[$letter][$i]->getName()
                                                , false
                                                , true);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'define':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', constant '.$this->getDefineLink($this->elements[$letter][$i]->getName(),
                                                 $this->elements[$letter][$i]->docblock->package,
                                                 $this->elements[$letter][$i]->getPath(),
                                                 $this->elements[$letter][$i]->getName()
                                                 , false);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'global':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', global variable '.$this->getGlobalLink($this->elements[$letter][$i]->getName(),
                                                 $this->elements[$letter][$i]->docblock->package,
                                                 $this->elements[$letter][$i]->getPath(),
                                                 $this->elements[$letter][$i]->getName()
                                                 , false);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'function':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', function '.$this->getFunctionLink($this->elements[$letter][$i]->getName(),
                                                   $this->elements[$letter][$i]->docblock->package,
                                                   $this->elements[$letter][$i]->getPath(),
                                                   $this->elements[$letter][$i]->getName().'()'
                                                   , false);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'method':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', method '.$this->getMethodLink($this->elements[$letter][$i]->getName(),
                                                 $this->elements[$letter][$i]->class,
                                                 $this->elements[$letter][$i]->docblock->package,
                                                 $this->elements[$letter][$i]->getPath(),
                                                 $this->elements[$letter][$i]->class.'::'.$this->elements[$letter][$i]->getName().'()'
                                                 , false);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'var':
                        $aa = '';
                        $aa = $this->elements[$letter][$i]->docblock->getSDesc($this);
                        $oo['name'] = $this->elements[$letter][$i]->getName();
                        $oo['listing'] =
                            'in file '.$this->elements[$letter][$i]->file.', variable '.$this->getVarLink($this->elements[$letter][$i]->getName(),
                                              $this->elements[$letter][$i]->class,
                                              $this->elements[$letter][$i]->docblock->package,
                                              $this->elements[$letter][$i]->getPath(),
                                              $this->elements[$letter][$i]->class.'::'.$this->elements[$letter][$i]->getName()
                                              , false);
                        $oo['sdesc'] = "$aa";
                        $elindex['index'][] = $oo; 
                    break;
                    case 'page':
                        $oo['name'] = $this->elements[$letter][$i]->getFile();
                        $oo['listing'] =
                            'procedural page '.$this->getPageLink($this->elements[$letter][$i]->getFile(),
                                               $this->elements[$letter][$i]->package,
                                               $this->elements[$letter][$i]->getPath(),
                                               $this->elements[$letter][$i]->getFile()
                                               , false);
                        $elindex['index'][] = $oo; 
                    break;
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
    
    /**
     * calls the converter setTargetDir, and then copies any template images and the stylesheet if they haven't been copied
     * @see Converter::setTargetDir()
     */
    function setTargetDir($dir)
    {
        Converter::setTargetDir($dir);
        static $wrote = false;
        if ($wrote) return;
        $wrote = true;
        $template_images = array();
        $stylesheets = array();
        $dir = $this->templateDir;
        $this->templateDir = $this->templateDir.'templates/';
        $d = dir($this->templateDir); 
        $template_images = array();
        while($entry = $d->read())
        {
            $sp = explode(".", $entry); 
            if ( preg_match("/\.(gif|jpg|png)$/i", $entry) )
            {
                $template_images[] = $entry;
            }
        }
        $d = dir($this->templateDir); 
        while($entry = $d->read())
        {
            $sp = explode(".", $entry); 
            if ( preg_match("/\.css$/i", $entry) )
            {
                $stylesheets[] = $entry;
            }
        }
        phpDocumentor_out("Copying Any Stylesheets\n");
        flush();
        foreach($stylesheets as $image)
        {
            if (file_exists($this->templateDir.$image))
            {
                phpDocumentor_out("Writing $image\n");
                flush();
                $this->copyFile($image);
            }
        }
        phpDocumentor_out("Copying Any Template Images\n");
        flush();
        foreach($template_images as $image)
        {
            if (file_exists($this->templateDir.$image))
            {
                phpDocumentor_out("Writing $image\n");
                flush();
                $this->copyFile($image);
            }
        }
        $this->templateDir = $dir;
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
            foreach($els as $letter => $yuh)
            {
                if (!isset($used[$letter]))
                {
                    $letters[]['letter'] = $letter;
                    $letterind[$letter] = count($letters) - 1;
                    $used[$letter] = 1;
                }
                $elindex[$letter]['letter'] = $letter;
                foreach($els[$letter] as $i => $yuh)
                {
                    switch($els[$letter][$i]->type)
                    {
                        case 'class':
                            $aa = '';
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', class '.$this->getClassLink($els[$letter][$i]->getName(),
                                                    $els[$letter][$i]->docblock->package,
                                                    $els[$letter][$i]->getPath(),
                                                    $els[$letter][$i]->getName()
                                                    , false
                                                    , true);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'define':
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', constant '.$this->getDefineLink($els[$letter][$i]->getName(),
                                                     $els[$letter][$i]->docblock->package,
                                                     $els[$letter][$i]->getPath(),
                                                     $els[$letter][$i]->getName()
                                                     , false);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'global':
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', global variable '.$this->getGlobalLink($els[$letter][$i]->getName(),
                                                     $els[$letter][$i]->docblock->package, 
                                                     $els[$letter][$i]->getPath(),
                                                     $els[$letter][$i]->getName()
                                                     ,false);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'function':
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', function '.$this->getFunctionLink($els[$letter][$i]->getName(),
                                                       $els[$letter][$i]->docblock->package,
                                                       $els[$letter][$i]->getPath(),
                                                       $els[$letter][$i]->getName().'()'
                                                       , false);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'method':
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', method '.$this->getMethodLink($els[$letter][$i]->getName(),
                                                     $els[$letter][$i]->class,
                                                     $els[$letter][$i]->docblock->package, 
                                                     $els[$letter][$i]->getPath(),
                                                     $els[$letter][$i]->class.'::'.$els[$letter][$i]->getName().'()'
                                                     , false);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'var':
                            $aa = $els[$letter][$i]->docblock->getSDesc($this);
                            $oo['name'] = $els[$letter][$i]->getName();
                            $oo['listing'] =
                                'in file '.$els[$letter][$i]->file.', variable '.$this->getVarLink($els[$letter][$i]->getName(),
                                                  $els[$letter][$i]->class,
                                                  $els[$letter][$i]->docblock->package,
                                                  $els[$letter][$i]->getPath(),
                                                  $els[$letter][$i]->class.'::'.$els[$letter][$i]->getName()
                                                  , false);
                            $oo['subpackage'] = $subpackage;
                            $oo['sdesc'] = $aa;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                        case 'page':
                            $oo['name'] = $els[$letter][$i]->getFile();
                            $oo['listing'] =
                                'procedural page '.$this->getPageLink($els[$letter][$i]->getFile(),
                                                   $els[$letter][$i]->package,
                                                   $els[$letter][$i]->getPath(),
                                                   $els[$letter][$i]->getFile()
                                                   , false);
                            $oo['subpackage'] = $subpackage;
                            $elindex[$letter]['index'][] = $oo; 
                        break;
                    }
                }
            }
        }
        ksort($elindex);
        usort($letters,'XMLDocBook_lettersort');
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
    
    /**
     * This function is not used by HTMLdefaultConverter, but is required by Converter
     */
    function Output()
    {
    }
}

/** @access private */
function XMLDocBook_lettersort($a, $b)
{
    return strnatcasecmp($a['letter'],$b['letter']);
}
?>
