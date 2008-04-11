<?php
/**
 * This was all in {@link phpdoc.inc}, and now encapsulates the complexity
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
 * @category   documentation
 * @package    phpDocumentor
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
error_reporting(E_ALL);
/** common settings */
include_once("phpDocumentor/common.inc.php");

include_once("phpDocumentor/Io.inc");
include_once("phpDocumentor/Publisher.inc");
include_once("phpDocumentor/Classes.inc");
include_once("phpDocumentor/ProceduralPages.inc");
include_once("phpDocumentor/IntermediateParser.inc");
include_once("phpDocumentor/WordParser.inc");
include_once("phpDocumentor/EventStack.inc");
include_once("phpDocumentor/ParserData.inc");
include_once("phpDocumentor/InlineTags.inc");
include_once("phpDocumentor/DocBlockTags.inc");
include_once("phpDocumentor/DescHTML.inc");
include_once("phpDocumentor/ParserDocBlock.inc");
include_once("phpDocumentor/ParserElements.inc");
include_once("phpDocumentor/Parser.inc");
include_once("phpDocumentor/phpDocumentorTWordParser.inc");
include_once("phpDocumentor/phpDocumentorTParser.inc");
include_once("phpDocumentor/HighlightParser.inc");
include_once("phpDocumentor/TutorialHighlightParser.inc");
include_once("phpDocumentor/ParserDescCleanup.inc");
include_once("phpDocumentor/PackagePageElements.inc");
include_once("phpDocumentor/XMLpackagePageParser.inc");
include_once("phpDocumentor/LinkClasses.inc");
include_once("phpDocumentor/Converter.inc");
include_once("phpDocumentor/Errors.inc");
if (isset($_GET))
{
/**
 * $interface is either 'web' or is not set at all
 * @global array $interface
 */
    if (isset($_GET['interface'])) $interface = $_GET['interface'];
/**
 * $_phpDocumentor_setting is either the value from the web interface, or is set up by {@link Io::parseArgv()}
 * @global array $_phpDocumentor_setting
 */
    if (isset($_GET['setting'])) $_phpDocumentor_setting = $_GET['setting'];
}

/**
 * default package name, set using -dn --defaultpackagename
 * @global string $GLOBALS['phpDocumentor_DefaultPackageName']
 * @name $phpDocumentor_DefaultPackageName
 */
$GLOBALS['phpDocumentor_DefaultPackageName'] = 'default';

/**
 * default package name, set using -dn --defaultcategoryname
 * @global string $GLOBALS['phpDocumentor_DefaultCategoryName']
 * @name $phpDocumentor_DefaultCategoryName
 */
$GLOBALS['phpDocumentor_DefaultCategoryName'] = 'default';

/**
 * @package phpDocumentor
 */
class phpDocumentor_setup
{
    /**
     * The main parser
     * @var Parser|phpDocumentorTParser
     */
    var $parse;
    /**
     * Used to parse command-line options
     * @var Io
     */
    var $setup;
    /**
     * Used to organize output from the Parser before Conversion
     * @var phpDocumentor_IntermediateParser
     */
    var $render = false;
    /**
     * Packages to create documentation for
     * @var string
     */
    var $packages = false;
    /**
     * contents of --filename commandline
     * @tutorial phpDocumentor.howto.pkg#using.command-line.filename
     * @var string
     */
    var $files = '';
    /**
     * contents of --directory commandline
     * @tutorial phpDocumentor.howto.pkg#using.command-line.directory
     * @var string
     */
    var $dirs = '';
    /**
     * contents of --hidden commandline
     * @tutorial phpDocumentor.howto.pkg#using.command-line.hidden
     * @var boolean
     */
    var $hidden = false;
    /**
     * time that parsing was started, used for informative timing of output
     * @access private
     */
    var $parse_start_time;
    /**
     * contents of --ignore commandline
     * @tutorial phpDocumentor.howto.pkg#using.command-line.ignore
     * @var string
     */
    var $ignore_files = array();
    /**
     * Checks PHP version, makes sure it is 4.2.0+, and chooses the
     * phpDocumentorTParser if version is 4.3.0+
     * @uses parseIni()
     */
    function phpDocumentor_setup()
    {
        global $_phpDocumentor_cvsphpfile_exts, $_phpDocumentor_setting;
        if (!function_exists('is_a'))
        {
            print "phpDocumentor requires PHP version 4.2.0 or greater to function";
            exit;
        }

        $this->setup = new Io;
        if (!isset($interface) && !isset($_GET['interface']) && !isset($_phpDocumentor_setting))
        {
            // Parse the argv settings
            $_phpDocumentor_setting = $this->setup->parseArgv();
        }
        if (isset($_phpDocumentor_setting['useconfig']) &&
             !empty($_phpDocumentor_setting['useconfig'])) {
            $this->readConfigFile($_phpDocumentor_setting['useconfig']);
        }

        // set runtime to a large value since this can take quite a while
        // we can only set_time_limit when not in safe_mode bug #912064
        if (!ini_get('safe_mode'))
        {
            set_time_limit(0);    // unlimited runtime
        } else
        {
            phpDocumentor_out("time_limit cannot be set since your in safe_mode, please edit time_limit in your php.ini to allow enough time for phpDocumentor to run"); 
        }
        $x = str_replace('M', '', ini_get('memory_limit'));
        if ($x < 256) {
            ini_set("memory_limit","256M");
        }

        $phpver = phpversion();
        $phpdocver = PHPDOCUMENTOR_VER;
        if (isset($_GET['interface'])) {
            $phpver = "<b>$phpver</b>";
            $phpdocver = "<b>$phpdocver</b>";
        }
        phpDocumentor_out("PHP Version $phpver\n");
        phpDocumentor_out("phpDocumentor version $phpdocver\n\n");

        $this->parseIni();

        if (tokenizer_ext)
        {
            phpDocumentor_out("using tokenizer Parser\n");
            $this->parse = new phpDocumentorTParser;
        } else
        {
            phpDocumentor_out("using default (slower) Parser - get PHP 4.3.0+
and load the tokenizer extension for faster parsing (your version is ".phpversion()."\n");
            $this->parse = new Parser;
        }
    }
    
    /**
     * Get phpDocumentor settings from a user configuration file
     * @param string user configuration file
     */
    function readConfigFile($file)
    {
        global $_phpDocumentor_setting, $_phpDocumentor_options;
        // security
        $file = str_replace(array('..','.ini','\\'),array('','','/'),$file);
        if (is_file($file . '.ini'))
        {
            $_phpDocumentor_setting = phpDocumentor_parse_ini_file($file.'.ini');
        } else
        {
            if ('@DATA-DIR@' != '@'.'DATA-DIR@')
            {
                $configdir = str_replace('\\','/', '@DATA-DIR@/PhpDocumentor') . PATH_DELIMITER . 'user' . PATH_DELIMITER;
            } else {
                $configdir = str_replace('\\','/',$GLOBALS['_phpDocumentor_install_dir']) . PATH_DELIMITER . 'user' . PATH_DELIMITER;
            }
            if (isset($_phpDocumentor_options['userdir'])) $configdir = $_phpDocumentor_options['userdir'];
            if (substr($configdir,-1) != '/')
            {
                $configdir .= '/';
            }
            $_phpDocumentor_setting = phpDocumentor_parse_ini_file( $configdir . $file . '.ini');
            if (empty($_phpDocumentor_setting['defaultpackagename']))
            {
                $_phpDocumentor_setting['defaultpackagename'] = 'default';
            }
        }
        // don't want a loop condition!
        unset($_phpDocumentor_setting['useconfig']);
    }
    
    /**
     * Get phpDocumentor settings from command-line or web interface
     */
    function readCommandLineSettings()
    {
        global $_phpDocumentor_setting,$interface,$_phpDocumentor_RIC_files;
        // subscribe $render class to $parse class events
        if (!isset($_phpDocumentor_setting['junk'])) $_phpDocumentor_setting['junk'] = '';
        if (!isset($_phpDocumentor_setting['title'])) $_phpDocumentor_setting['title'] = 'Generated Documentation';
        $temp_title = $_phpDocumentor_setting['title'];
        $this->render = new phpDocumentor_IntermediateParser($temp_title);
        if (isset($_phpDocumentor_setting['help']) || $_phpDocumentor_setting['junk'] == "-h" || $_phpDocumentor_setting['junk'] == "--help")
        {
            echo $this->setup->displayHelpMsg();
            die();
        }

        // set to parse hidden files
        $this->hidden = (isset($_phpDocumentor_setting['hidden'])) ? decideOnOrOff($_phpDocumentor_setting['hidden']) : false;

        // set to parse elements marked private with @access private
        $this->render->setParsePrivate((isset($_phpDocumentor_setting['parseprivate'])) ? decideOnOrOff($_phpDocumentor_setting['parseprivate']) : false);

        if (isset($_phpDocumentor_setting['ignoretags']))
        {
            $ignoretags = explode(',', $_phpDocumentor_setting['ignoretags']);
            $ignoretags = array_map('trim', $ignoretags);
            $tags = array();
            foreach($ignoretags as $tag)
            {
                if (!in_array($tag,array('@global', '@access', '@package', '@ignore', '@name', '@param', '@return', '@staticvar', '@var')))
                    $tags[] = $tag;
            }
            $_phpDocumentor_setting['ignoretags'] = $tags;
        }
        
        if (isset($_phpDocumentor_setting['readmeinstallchangelog']))
        {
            $_phpDocumentor_setting['readmeinstallchangelog'] = explode(',',str_replace(' ','',$_phpDocumentor_setting['readmeinstallchangelog']));
            $rics = array();
            foreach($_phpDocumentor_setting['readmeinstallchangelog'] as $ric)
            {
                $rics[] = strtoupper(trim($ric));
            }
            $_phpDocumentor_RIC_files = $rics;
        }
        
        if (isset($_phpDocumentor_setting['javadocdesc']) && $_phpDocumentor_setting['javadocdesc'] == 'on')
        {
            $this->parse->eventHandlers[PARSER_EVENT_DOCBLOCK] = 'JavaDochandleDocblock';
        }
        if (tokenizer_ext)
        {
            if (isset($_phpDocumentor_setting['sourcecode']) && $_phpDocumentor_setting['sourcecode'] == 'on')
            {
                $_phpDocumentor_setting['sourcecode'] = true;
            } else
            {
                $_phpDocumentor_setting['sourcecode'] = false;
            }
        } else
        {
            if (isset($_phpDocumentor_setting['sourcecode']) && $_phpDocumentor_setting['sourcecode'] == 'on')
            {
                addWarning(PDERROR_SOURCECODE_IGNORED);
            }
            $_phpDocumentor_setting['sourcecode'] = false;
        }
        if (isset($_phpDocumentor_setting['converterparams']))
        {
            $_phpDocumentor_setting['converterparams'] = explode($_phpDocumentor_setting['converterparams']);
            foreach($_phpDocumentor_setting['converterparams'] as $i => $p)
            {
                $_phpDocumentor_setting['converterparams'][$i] = trim($p);
            }
        }
        if (isset($_phpDocumentor_setting['customtags']) && !empty($_phpDocumentor_setting['customtags']))
        {
            $c = explode(',',$_phpDocumentor_setting['customtags']);
            for($i=0;$i<count($c); $i++)
            {
                $GLOBALS['_phpDocumentor_tags_allowed'][] = trim($c[$i]);
            }
        }
        if (isset($_phpDocumentor_setting['pear']))
        {
            if ($_phpDocumentor_setting['pear'] === 'off') $_phpDocumentor_setting['pear'] = false;
            if ($_phpDocumentor_setting['pear'] === 'on') $_phpDocumentor_setting['pear'] = true;
        }
        if (!isset($_phpDocumentor_setting['pear'])) $_phpDocumentor_setting['pear'] = false;
        // set to change the default package name from "default" to whatever you want
        if (isset($_phpDocumentor_setting['defaultpackagename']))
        {
            $GLOBALS['phpDocumentor_DefaultPackageName'] = trim($_phpDocumentor_setting['defaultpackagename']);
        }
        // set to change the default category name from "default" to whatever you want
        if (isset($_phpDocumentor_setting['defaultcategoryname']))
        {
            $GLOBALS['phpDocumentor_DefaultCategoryName'] = trim($_phpDocumentor_setting['defaultcategoryname']);
        }
        
        // set the mode (quiet or verbose)
        $this->render->setQuietMode((isset($_phpDocumentor_setting['quiet'])) ? decideOnOrOff($_phpDocumentor_setting['quiet']) : false);

        // Setup the different classes
        if (isset($_phpDocumentor_setting['templatebase']))
        {
            $this->render->setTemplateBase(trim($_phpDocumentor_setting['templatebase']));
        }
        if (isset($_phpDocumentor_setting['target']) && !empty($_phpDocumentor_setting['target']))
        {
            $this->render->setTargetDir(trim($_phpDocumentor_setting['target']));
        }
        else
        {
            echo "a target directory must be specified\n try phpdoc -h\n";
            die();
        }
        if (!empty($_phpDocumentor_setting['packageoutput']))
        {
            $this->packages = explode(",",trim($_phpDocumentor_setting['packageoutput']));
            foreach($this->packages as $p => $v)
            {
                $this->packages[$p] = trim($v);
            }
        }
        if (!empty($_phpDocumentor_setting['filename'])) {
            $this->files = trim($_phpDocumentor_setting['filename']);
        }
        if (!empty($_phpDocumentor_setting['directory'])) {
            $this->dirs = trim($_phpDocumentor_setting['directory']);
        }
    }
    
    function checkIgnoreTag($tagname, $inline = false)
    {
        global $_phpDocumentor_setting;
        $tagname = '@'.$tagname;
        if (!isset($_phpDocumentor_setting['ignoretags'])) return false;
        if ($inline) $tagname = '{'.$tagname.'}';
        return in_array($tagname, $_phpDocumentor_setting['ignoretags']);
    }

     function setJavadocDesc()
    {
           $this->parse->eventHandlers[PARSER_EVENT_DOCBLOCK] = 'JavaDochandleDocblock';
    }
    
    function setParsePrivate($flag = true)
    {
        $this->render->setParsePrivate($flag);
    }
    
    function setQuietMode($flag = true)
    {
        $this->render->setQuietMode($flag);
    }
 
    function setTargetDir($target)
    {
        $this->render->setTargetDir($target);
    }
    
    function setTemplateBase($dir)
    {
        $this->render->setTemplateBase($dir);
    }
    
    function setPackageOutput($po)
    {
        $this->packages = explode(",",$po);
        array_map('trim', $this->packages);
    }
    
    function setTitle($ti)
    {
        $this->render = new phpDocumentor_IntermediateParser($ti);
    }
    
    function setFilesToParse($files)
    {
        $this->files = $files;
    }
    
    function setDirectoriesToParse($dirs)
    {
        $this->dirs = $dirs;
    }
    
    function parseHiddenFiles($flag = true)
    {
        $this->hidden = $flag;
    }
    
    function setIgnore($ig)
    {
        if (strstr($ig,","))
        {
            $this->ignore_files = explode(",",$ig);
        } else {
            if (!empty($ig))
            $this->ignore_files = array($ig);
        }
        $this->ignore_files = array_map('trim', $this->ignore_files);
    }
    
    function createDocs($title = false)
    {
        $this->parse_start_time = time();
        global $_phpDocumentor_setting;
        if (!$this->render)
        {
            $this->render = new phpDocumentor_IntermediateParser($title);
        }
        // setup ignore  list
        $this->ignore_files =array();
        if(isset($_phpDocumentor_setting['ignore']))
        {
            $this->setIgnore($_phpDocumentor_setting['ignore']);
        }
        $this->parse->subscribe("*",$this->render);
        // parse the directory
        if (!empty($this->files))
        {
            $files = explode(",",$this->files);
            foreach($files as $file)
            {
                $file = trim($file);
                $test = $this->setup->getAllFiles($file);
                if ($test)
                {
                    foreach($test as $file)
                    {
                        $file = trim($file);
                        $dir = realpath(dirname($file));
                        $dir = strtr($dir, "\\", "/");
                        $dir = str_replace('//','/',$dir);
                        // strip trailing directory seperator
                        if (substr($dir,-1) == "/" || substr($dir,-1) == "\\")
                        {
                            $dir = substr($dir,0,-1);
                        }
                        $file = strtr(realpath($file), "\\", "/");
                        $file = str_replace('//','/',$file);

                        if (!$this->setup->checkIgnore(basename($file),dirname($file),$this->ignore_files))
                        {
                            $filelist[] = str_replace('\\','/',$file);
                        } else {
                            phpDocumentor_out("File $file Ignored\n");
                            flush();
                        }
                    }
                } else
                {
                    $dir = dirname(realpath($file));
                    $dir = strtr($dir, "\\", "/");
                    $dir = str_replace('//','/',$dir);
                    // strip trailing directory seperator
                    if (substr($dir,-1) == "/" || substr($dir,-1) == "\\")
                    {
                        $dir = substr($dir,0,-1);
                    }
                    $base = count(explode("/",$dir));
                    $file = strtr(realpath($file), "\\", "/");
                    $file = str_replace('//','/',$file);
                    flush();

                    if (!$this->setup->checkIgnore(basename($file),dirname($file),$this->ignore_files))
                    {
                        $filelist[] = str_replace('\\','/',$file);
                    } else {
                        phpDocumentor_out("File $file Ignored\n");
                        flush();
                    }
                }
            }
        }
        if (!empty($this->dirs))
        {
            $dirs = explode(",",$this->dirs);
            foreach($dirs as $dir)
            {
                $olddir = $dir;
                $dir = realpath($dir);
                if (!$dir) {
                    phpDocumentor_out('ERROR: "' . $olddir . '" does not exist, skipping');
                    continue;
                }
                $dir = trim($dir);
                $dir = strtr($dir, "\\", "/");
                $dir = str_replace('//','/',$dir);
                // strip trailing directory seperator
                if (substr($dir,-1) == "/" || substr($dir,-1) == "\\")
                {
                    $dir = substr($dir,0,-1);
                }
                $files = $this->setup->dirList($dir,$this->hidden);
                if (is_array($files))
                {
                    foreach($files as $file)
                    {
                        // Make sure the file isn't a hidden file
                        $file = strtr($file, "\\", "/");
                        if (substr(basename($file),0,1) != ".")
                        {
                            if (!$this->setup->checkIgnore(basename($file),str_replace('\\','/',dirname($file)),$this->ignore_files))
                            {
                                $filelist[] = str_replace('\\','/',$file);
                            } else {
                                phpDocumentor_out("File $file Ignored\n");
                                flush();
                            }
                        }
                    }
                }
            }
        }
        if (isset($filelist))
        {
            if (PHPDOCUMENTOR_WINDOWS)
            {
                // case insensitive array_unique
                usort($filelist,'strnatcasecmp');
                reset($filelist);
                
                $newarray = array();
                $i = 0;
                
                $element = current($filelist);
                for ($n=0;$n<sizeof($filelist);$n++)
                {
                    if (strtolower(next($filelist)) != strtolower($element))
                    {
                        $newarray[$i] = $element;
                        $element = current($filelist);
                        $i++;
                    }
                }
                $filelist = $newarray; 
            } else $filelist = array_unique($filelist);

            $base = count(explode("/",$source_base = $this->setup->getBase($filelist)));
            define("PHPDOCUMENTOR_BASE",$source_base);
            list($filelist,$ric) = $this->setup->getReadmeInstallChangelog($source_base, $filelist);
            phpDocumentor_out("\n\nGrabbing README/INSTALL/CHANGELOG\n");
            flush();
            foreach($ric as $file)
            {
                phpDocumentor_out(basename($file).'...');
                flush();
                $fp = fopen($file,'r');
                $contents = fread($fp,filesize($file));
                $this->render->HandleEvent(PHPDOCUMENTOR_EVENT_README_INSTALL_CHANGELOG, array(basename($file),$contents));
                fclose($fp);
            }
            phpDocumentor_out("\ndone\n");
            flush();
            list($filelist,$tutorials) = $this->setup->getTutorials($filelist);
            phpDocumentor_out("\n\nTutorial/Extended Documentation Parsing Stage\n\n");
            flush();
            if (count($tutorials))
            {
                $tuteparser = new XMLPackagePageParser;
                $tuteparser->subscribe('*',$this->render);
                foreach($tutorials as $tutorial)
                {
                    switch($tutorial['tutetype'])
                    {
                        case 'pkg' :
                        case 'cls' :
                        case 'proc' :
                        switch($tutorial['tutetype'])
                        {
                            case 'pkg' :
                                $ptext = 'Package-level Docs ';
                                if (!empty($tutorial['subpackage']))
                                $ptext = 'Sub-Package Docs ';
                            break;
                            case 'cls' :
                                $ptext = 'Class-level Docs ';
                            break;
                            case 'proc' :
                                $ptext = 'Procedural-level Docs ';
                            break;
                        }
                        $fp = @fopen($tutorial['path'],"r");
                        if ($fp)
                        {
                            $ret = fread($fp,filesize($tutorial['path']));
                            // fix 1151650
                            if (stristr($ret, "utf-8") !== "")
                            {
                                $ret = utf8_decode($ret);
                            }
                            fclose($fp);
                            unset($fp);
                            phpDocumentor_out('Parsing '.$ptext.$tutorial['path'].'...');
                            flush();
                            $tuteparser->parse($ret,$tutorial);
                            phpDocumentor_out("done\n");
                            flush();
                        } else
                        {
                            phpDocumentor_out('Error '.$ptext.$tutorial['path'].' doesn\'t exist'."\n");
                            flush();
                        }
                        default :
                        break;
                    }
                }
            }
            phpDocumentor_out("done\n");
            flush();
            phpDocumentor_out("\n\nGeneral Parsing Stage\n\n");
            flush();
            foreach($filelist as $file)
            {
                phpDocumentor_out("Reading file $file");
                flush();
                $this->parse->parse($a = $this->setup->readPhpFile($file, $this->render->quietMode),$file,$base,$this->packages);
    
            }
            $b = (time() - $this->parse_start_time);
            phpDocumentor_out("done\n");
            flush();
            // render output
            phpDocumentor_out("\nConverting From Abstract Parsed Data\n");
            flush();
            $this->render->output();
            $a = (time() - $this->parse_start_time);
            $c = ($a - $b);
            phpDocumentor_out("\nParsing time: $b seconds\n");
            phpDocumentor_out("\nConversion time: $c seconds\n");
            phpDocumentor_out("\nTotal Documentation Time: $a seconds\n");
            phpDocumentor_out("done\n");
            flush();
        } else
        {
            print "ERROR: nothing parsed";
            exit;
        }
    }
    /**
     * Parse configuration file phpDocumentor.ini
     */
    function parseIni()
    {
        phpDocumentor_out("Parsing configuration file phpDocumentor.ini...");
        flush();
        if ('@DATA-DIR@' != '@'.'DATA-DIR@')
        {
            $options = phpDocumentor_parse_ini_file(str_replace('\\','/', '@DATA-DIR@/PhpDocumentor') . PATH_DELIMITER . 'phpDocumentor.ini',true);
        } else {
            $options = phpDocumentor_parse_ini_file(str_replace('\\','/',$GLOBALS['_phpDocumentor_install_dir']) . PATH_DELIMITER . 'phpDocumentor.ini',true);
        }

        if (!$options)
        {
            print "ERROR: cannot open phpDocumentor.ini in directory " . $GLOBALS['_phpDocumentor_install_dir']."\n";
            print "-Is phpdoc in either the path or include_path in your php.ini file?";
            exit;
        }
        
        foreach($options as $var => $values)
        {
            if ($var != 'DEBUG')
            {
//                phpDocumentor_out("\n$var");
                if ($var != '_phpDocumentor_setting' && $var != '_phpDocumentor_options' && $var != '_phpDocumentor_install_dir' ) $values = array_values($values);
//                fancy_debug("\n$var",$values);
                $GLOBALS[$var] = $values;
            }
        }
        phpDocumentor_out("\ndone\n");
        flush();
        /** Debug Constant */
        if (!defined('PHPDOCUMENTOR_DEBUG')) define("PHPDOCUMENTOR_DEBUG",$options['DEBUG']['PHPDOCUMENTOR_DEBUG']);
        if (!defined('PHPDOCUMENTOR_KILL_WHITESPACE')) define("PHPDOCUMENTOR_KILL_WHITESPACE",$options['DEBUG']['PHPDOCUMENTOR_KILL_WHITESPACE']);
        $GLOBALS['_phpDocumentor_cvsphpfile_exts'] = $GLOBALS['_phpDocumentor_phpfile_exts'];
        foreach($GLOBALS['_phpDocumentor_cvsphpfile_exts'] as $key => $val)
        {
            $GLOBALS['_phpDocumentor_cvsphpfile_exts'][$key] = "$val,v";
        }
        // none of this stuff is used anymore
        if (isset($GLOBALS['_phpDocumentor_html_allowed']))
        {
            $___htmltemp = array_flip($GLOBALS['_phpDocumentor_html_allowed']);
            $___html1 = array();
            foreach($___htmltemp as $tag => $trans)
            {
                $___html1['<'.$tag.'>'] = htmlentities('<'.$tag.'>');
                $___html1['</'.$tag.'>'] = htmlentities('</'.$tag.'>');
            }
            $GLOBALS['phpDocumentor___html'] = array_flip($___html1);
        }
    }
    

    function cleanConverterNamePiece($name, $extra_characters_to_allow = '')
    {
        $name = str_replace("\\", "/", $name);
        // security:  ensure no opportunity exists to use "../.." pathing in this value
        $name = preg_replace('/[^a-zA-Z0-9' . $extra_characters_to_allow . '_]/', "", $name);
        
        // absolutely positively do NOT allow two consecutive dots ".."
        if (strpos($name, '..') > -1) $name = false;
        return $name;
    }
    
    function setupConverters($output = false)
    {
        global $_phpDocumentor_setting;
        if ($output)
        {
            $_phpDocumentor_setting['output'] = $output;
        }
        if (isset($_phpDocumentor_setting['output']) && !empty($_phpDocumentor_setting['output']))
        {
            $c = explode(',',$_phpDocumentor_setting['output']);
            for($i=0; $i< count($c); $i++)
            {
                $c[$i] = explode(':',$c[$i]);
                $a = $c[$i][0];
                if (isset($c[$i][0]))
                {
                    $a = $this->cleanConverterNamePiece($c[$i][0]);
                }
                else
                {
                    $a = false;
                }
                if (isset($c[$i][1]))
                {
                    $b = $this->cleanConverterNamePiece($c[$i][1], '\/');  // must allow "/" due to options like "DocBook/peardoc2"
                }
                else
                {
                    $b = false;
                }
                if (isset($c[$i][2]))
                {
                    /**
                     * must allow "." due to options like "phpdoc.de"
                     * must allow "/" due to options like "DOM/default"
                     */
                    $d = $this->cleanConverterNamePiece($c[$i][2], '.\/');
                    if (substr($d,-1) != "/")
                    {
                        $d .= "/";
                    }
                    else 
                    {
                        $d = 'default/';
                    }
                }
                if (strtoupper(trim($a)) == 'HTML' && (trim($b) == 'default'))
                {
                    phpDocumentor_out("WARNING: HTMLdefaultConverter is deprecated, using HTMLframesConverter.\n");
                    phpDocumentor_out("WARNING: template output is identical, HTMLframes is more flexible.\n");
                    phpDocumentor_out("WARNING: please adjust your usage\n");
                    flush();
                    $b = 'frames'; // change default to frames.
                }
                $this->render->addConverter(strtoupper(trim($a)),trim($b),trim($d));
            }
        } else
        {
            $this->render->addConverter('HTML','frames','default/');
        }
        if (empty($this->render->converters)) addErrorDie(PDERROR_NO_CONVERTERS);
    }
}

/**
 * Fuzzy logic to interpret the boolean args' intent
 * @param string the command-line option to analyze
 * @return boolean our best guess of the value's boolean intent
 */
function decideOnOrOff($value_to_guess = 'NO VALUE WAS PASSED')
{
    $these_probably_mean_yes = array(
        '',             // "--hidden" with no value 
        'on',           // "--hidden on"
        'y', 'yes',     // "--hidden y"
        'true',         // "--hidden true"
        '1'             // "--hidden 1"
    );
    $best_guess = false;    // default to "false", "off", "no", "take a hike"

    if (in_array(strtolower(trim($value_to_guess)), $these_probably_mean_yes))
    {
        $best_guess = true;
    }
    return $best_guess;
}

/**
 * Print parse information if quiet setting is off
 */
function phpDocumentor_out($string)
{
    global $_phpDocumentor_setting;
    if ((isset($_phpDocumentor_setting['quiet'])) ? !decideOnOrOff($_phpDocumentor_setting['quiet']) : true)
    {
        print $string;
    }

}

/**
 * Crash in case of known, dangerous bug condition
 * 
 * Checks the PHP version that is executing PhpDocumentor,
 * in case a known PHP/PEAR bug condition could be triggered
 * by the PhpDocumentor execution.
 * @param string $php_version the PHP version that contains the bug
 * @param string $php_bug_number the PHP bug number (if any)
 * @param string $pear_bug_number the PEAR bug number (if any)
 */
function checkForBugCondition($php_version, $php_bug_number = 'none', $pear_bug_number = 'none')
{
    if (version_compare(phpversion(), $php_version) == 0)
    {
        addErrorDie(PDERROR_DANGEROUS_PHP_BUG_EXISTS, $php_version, $php_bug_number, $pear_bug_number);
    }
}
?>
