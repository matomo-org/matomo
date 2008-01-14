<?php
/**
 * File and input handling routines
 * 
 * This class parses command-line options, and works with files to
 * generate lists of files to parse based on the ignore/include options
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
 * @package    phpDocumentor
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2000-2006 Joshua Eichorn, Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      0.1
 */
/**
 * Class to handle file and user io opperations
 *
 * @author    Joshua Eichorn <jeichorn@phpdoc.org>
 * @author     Gregory Beaver <cellog@php.net>
 * @version    $Id$
 * @package     phpDocumentor
 */
class Io 
{
    
    /**
     * Holds all the options that are avaible to the cmd line interface
     * and to the different web interfaces
     */
    var $phpDocOptions;
    /**
     * Format: array(array(regexp-ready string to search for whole path,
     * regexp-ready string to search for basename of ignore strings),...)
     * @var false|array
     */
    var $ignore = false;
    /**
     * A specific array of values that boolean-based arguments can understand,
     * aided by the {@link decideOnOrOff()} helper method.
     * @var array 
     */
    var $valid_booleans = array
    (
        '', ' ', 'on', 'On', 'ON', 'y', 'Y', 'yes', 'Yes', 'YES', 'true', 'True', 'TRUE', '1', 
        'off', 'Off', 'OFF', 'n', 'N', 'no', 'No', 'NO', 'false', 'False', 'FALSE', '0'
    );
    
    /**
     * creates an array $this->phpDocOptions and sets program options in it.
     * Array is in the format of:
     * <pre>
     * [filename][tag][] = "f";
     * [filename][tag][] = "-file";
     * [filename][desc] "name of file to parse"
     * </pre>
     */
    function Io()
    {
        $this->phpDocOptions['filename']['tag'] = array( "-f", "--filename");
        $this->phpDocOptions['filename']['desc'] = "name of file(s) to parse ',' file1,file2.  Can contain complete path and * ? wildcards";
        $this->phpDocOptions['filename']['type'] = "path";

        $this->phpDocOptions['directory']['tag'] = array( "-d", "--directory");
        $this->phpDocOptions['directory']['desc'] = "name of a directory(s) to parse directory1,directory2";
        $this->phpDocOptions['directory']['type'] = "path";

        $this->phpDocOptions['examplesdir']['tag'] = array( "-ed", "--examplesdir");
        $this->phpDocOptions['examplesdir']['desc'] = "full path of the directory to look for example files from @example tags";
        $this->phpDocOptions['examplesdir']['type'] = "path";

        $this->phpDocOptions['templatebase']['tag'] = array( "-tb", "--templatebase");
        $this->phpDocOptions['templatebase']['desc'] = "base location of all templates for this parse.";
        $this->phpDocOptions['templatebase']['type'] = "path";

        $this->phpDocOptions['target']['tag'] = array("-t", "--target");
        $this->phpDocOptions['target']['desc'] = "path where to save the generated files";
        $this->phpDocOptions['target']['type'] = "path";
        
        $this->phpDocOptions['ignore']['tag'] = array("-i", "--ignore");
        $this->phpDocOptions['ignore']['desc'] = "file(s) that will be ignored, multiple separated by ','.  Wildcards * and ? are ok";
        $this->phpDocOptions['ignore']['type'] = "path";

        $this->phpDocOptions['ignoretags']['tag'] = array("-it", "--ignore-tags");
        $this->phpDocOptions['ignoretags']['desc'] = "tags to ignore for this parse.  @package, @subpackage, @access and @ignore may not be ignored.";
        $this->phpDocOptions['ignoretags']['type'] = "value";

        $this->phpDocOptions['hidden']['tag'] = array("-dh", "--hidden");
        $this->phpDocOptions['hidden']['desc'] = "set equal to on (-dh on) to descend into hidden directories (directories starting with '.'), default is off";
        $this->phpDocOptions['hidden']['type'] = "set";
        $this->phpDocOptions['hidden']['validvalues'] = $this->valid_booleans;

        $this->phpDocOptions['quiet']['tag'] = array("-q", "--quiet");
        $this->phpDocOptions['quiet']['desc'] = "do not display parsing/conversion messages.  Useful for cron jobs on/off default off";
        $this->phpDocOptions['quiet']['type'] = "set";
        $this->phpDocOptions['quiet']['validvalues'] = $this->valid_booleans;

        $this->phpDocOptions['title']['tag'] = array("-ti","--title");
        $this->phpDocOptions['title']['desc'] = "title of generated documentation, default is 'Generated Documentation'";
        $this->phpDocOptions['title']['type'] = "value";

        $this->phpDocOptions['help']['tag'] = array("-h", "--help");
        $this->phpDocOptions['help']['desc'] = "    show this help message";

        $this->phpDocOptions['useconfig']['tag'] = array("-c","--useconfig");
        $this->phpDocOptions['useconfig']['desc'] = "Use a Config file in the users/ subdirectory for all command-line options";
        $this->phpDocOptions['useconfig']['type'] = "value";

        $this->phpDocOptions['parseprivate']['tag'] = array("-pp","--parseprivate");
        $this->phpDocOptions['parseprivate']['desc'] = "parse @internal and elements marked private with @access.  Use on/off, default off";
        $this->phpDocOptions['parseprivate']['type'] = "set";
        $this->phpDocOptions['parseprivate']['validvalues'] = array('on', 'off');

        $this->phpDocOptions['packageoutput']['tag'] = array("-po","--packageoutput");
        $this->phpDocOptions['packageoutput']['desc'] = "output documentation only for selected packages.  Use a comma-delimited list";
        $this->phpDocOptions['packageoutput']['type'] = "value";

        $this->phpDocOptions['defaultpackagename']['tag'] = array("-dn","--defaultpackagename");
        $this->phpDocOptions['defaultpackagename']['desc'] = "name to use for the default package.  If not specified, uses 'default'";
        $this->phpDocOptions['defaultpackagename']['type'] = "value";

        $this->phpDocOptions['defaultcategoryname']['tag'] = array("-dc","--defaultcategoryname");
        $this->phpDocOptions['defaultcategoryname']['desc'] = "name to use for the default category.  If not specified, uses 'default'";
        $this->phpDocOptions['defaultcategoryname']['type'] = "value";

        $this->phpDocOptions['output']['tag'] = array("-o","--output");
        $this->phpDocOptions['output']['desc'] = "output information to use separated by ','.  Format: output:converter:templatedir like \"HTML:frames:phpedit\"";
        $this->phpDocOptions['output']['type'] = "value";

        $this->phpDocOptions['converterparams']['tag'] = array("-cp","--converterparams");
        $this->phpDocOptions['converterparams']['desc'] = "dynamic parameters for a converter, separate values with commas";
        $this->phpDocOptions['converterparams']['type'] = "value";

        $this->phpDocOptions['customtags']['tag'] = array("-ct","--customtags");
        $this->phpDocOptions['customtags']['desc'] = "custom tags, will be recognized and put in tags[] instead of unknowntags[]";
        $this->phpDocOptions['customtags']['type'] = "value";

        $this->phpDocOptions['sourcecode']['tag'] = array("-s","--sourcecode");
        $this->phpDocOptions['sourcecode']['desc'] = "generate highlighted sourcecode for every parsed file (PHP 4.3.0+ only) on/off default off";
        $this->phpDocOptions['sourcecode']['type'] = "set";
        $this->phpDocOptions['sourcecode']['validvalues'] = array('on', 'off');

        $this->phpDocOptions['javadocdesc']['tag'] = array("-j","--javadocdesc");
        $this->phpDocOptions['javadocdesc']['desc'] = "JavaDoc-compliant description parsing.  Use on/off, default off (more flexibility)";
        $this->phpDocOptions['javadocdesc']['type'] = "set";
        $this->phpDocOptions['javadocdesc']['validvalues'] = array('on', 'off');

        $this->phpDocOptions['pear']['tag'] = array("-p","--pear");
        $this->phpDocOptions['pear']['desc'] = "Parse a PEAR-style repository (package is directory, _members are @access private) on/off default off";
        $this->phpDocOptions['pear']['type'] = "set";
        $this->phpDocOptions['pear']['validvalues'] = array('on', 'off');

        $this->phpDocOptions['readmeinstallchangelog']['tag'] = array("-ric","--readmeinstallchangelog");
        $this->phpDocOptions['readmeinstallchangelog']['desc'] = "Specify custom filenames to parse like README, INSTALL or CHANGELOG files";
        $this->phpDocOptions['readmeinstallchangelog']['type'] = "value";

        $this->phpDocOptions['general']['message'] ="You can have multiple directories and multiple files, as well as a combination of both options";
    }

    
    /**
     * create the help message for display on the command-line
     * @return string a string containing a help message
     */
    function displayHelpMsg()
    {
        unset($ret);
        $ret = "\n";
        foreach($this->phpDocOptions as $data)
        {
            unset($tag);
            $tag = "";
            if (isset($data['tag']))
            {
                if (is_array($data['tag'])) {
                    foreach($data['tag'] as $param) {
                        $tag .= "$param    ";
                    }
                }
		$taglen = 34;
		$outputwidth = 79;
		$tagspace = str_repeat(" ",$taglen);
                $tmp = "  ".trim($tag).$tagspace;
                $tmp = substr($tmp,0,$taglen);
                $d = wordwrap(ltrim($data['desc']),($outputwidth-$taglen));
		$dt = explode("\n",$d);
		$dt[0] = $tmp .$dt[0];
		for($i=1;$i<count($dt);$i++)
		{
			$dt[$i] = $tagspace.$dt[$i];
		}
		$ret .= implode("\n",$dt)."\n\n";
		
            }
        }
        $ret .= "\n".wordwrap($data['message'],$outputwidth)."\n";
        return $ret; 
    }
    
    /**
     * calls {@link file_exists()} for each value in include_path,
     * then calls {@link is_readable()} when it finds the file
     * @param string
     * @return boolean
     */
    function isIncludeable($filename)
    {
        $test = realpath($filename);
        if ($test && is_readable($test)) {
            return true; // for absolute paths
        }
        $ip = get_include_path();
        if (PHPDOCUMENTOR_WINDOWS)
        {
            $ip = explode(';', $ip);
        } else {
            $ip = explode(':', $ip);
        }
        foreach($ip as $path)
        {
            if ($a = realpath($path . DIRECTORY_SEPARATOR . $filename))
            {
                if (is_readable($a))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Parses $_SERVER['argv'] and creates a setup array
     * @return array a setup array
     * @global array command-line arguments
     */
    function parseArgv()
    {
        global $argv;
        // defaults for setting
        $setting['hidden'] = "off";
        $setting['template'] = 'templates' . PATH_DELIMITER .'default' . PATH_DELIMITER;

        $valnext = "junk";
        $data = array();
        if(isset($argv) && is_array($argv))
        {
            foreach ($argv as $cmd)
            {
                //if($cmd === 'phpdoc' || basename($cmd)==='phpdoc')
                //    continue;

                if ($cmd == '--') {
                    continue;
                }
                if ($cmd == '-h' || $cmd == '--help')
                {
                    echo $this->displayHelpMsg();
                    die();
                }
                if (isset($data['type']) && $data['type'] == 'set') {
                    if (!in_array(strtolower($cmd), $data['validvalues'], true)) {
                        addErrorDie(PDERROR_INVALID_VALUES, $valnext, $cmd,
                            '(' . implode(', ', $data['validvalues']) . ')');
                    }
                }
                $setting[$valnext] = $cmd;
                foreach( $this->phpDocOptions as $name => $data )
                {
                    if (!empty($data['tag']))
                    {
                        if (in_array($cmd,$data['tag']))
                        {
                            $valnext = $name;
                            break;
                        } 
                        else
                        {
                            $valnext = "junk";
                        }
                    }
                }
                if ($valnext == 'junk' && (strpos(trim($cmd),'-') === 0))
                {
                    addErrorDie(PDERROR_UNKNOWN_COMMANDLINE,$cmd);
                }
            }
        } else
        {
            echo "Please use php-cli.exe in windows, or set register_argc_argv On";
            die;
        }
        /* $setting will always have at least 2 elements
        [hidden] => off
        [template] => templates/default
         */
        if (count($setting) < 3) {
            echo $this->displayhelpMsg();
            die();
        }
        return $setting;
    }


    /**
     * @return array list of files in a directory
     * @param string $directory full path to the directory you want the list of
     * @param off|on whether to list files that begin with . like .bash_history
     */
    function dirList($directory, $hidden = "off")
    {
        $directory = realpath($directory);
        $ret = false;
        if (@is_dir($directory))
        {
            $ret = array();
            $d = @dir($directory); // thanks to Jason E Sweat (jsweat@users.sourceforge.net) for fix
            while($d && ($entry=$d->read()) !== false) {
                if (strcmp($entry,".") != 0 && strcmp($entry,"..") != 0) {
                if ($hidden == "off")
                {
                    if (substr($entry,0,1) == ".")
                    {
                        $getentry = false;
                    } else {
                        $getentry = true;
                    }
        
                } else {
                    $getentry = true;
                }
                
                if ($getentry == true)
                {
                    if (is_file($directory . PATH_DELIMITER . $entry)) {
                        $ret[] = $directory . PATH_DELIMITER . $entry;
                    }
                    if (is_dir($directory . PATH_DELIMITER . $entry)) {
                        $tmp = $this->dirList($directory . PATH_DELIMITER . $entry, $hidden);
                        if (is_array($tmp)) {
                            foreach($tmp as $ent) {
                                $ret[] = $ent;
                            }
                        }
                    }
                }
            }
        }
        if ($d) $d->close();
        } else {
            die("directory: '$directory'  not found\n");
        }
        return $ret;
    }

    /**
     * Retrieve common directory (case-insensitive in windows)
     *
     * takes the list of files, and returns the subdirectory they share in common,
     * so in this list:
     *
     * <code>
     * array(
     * "/dir1/dir2/subdir/dir3/filename.ext",
     * "/dir1/dir2/subdir/dir4/filename.ext",
     * "/dir1/dir2/mydir/dir5/filename.ext");
     * </code>
     *
     * getBase will return "/dir1/dir2"
     * @param array array of strings
     */
    function getBase($filelist)
    {
        $masterPath = false;
        foreach($filelist as $path)
        {
            if (!$masterPath)
            {
                $masterPath = str_replace('\\','/',dirname($path));
            } else
            {
                if (dirname($path) != $masterPath)
                {
                    $mp = split(PATH_DELIMITER,$masterPath);
                    $np = split(PATH_DELIMITER,str_replace('\\','/',dirname($path)));
                    if (count($np) < count($mp))
                    {
                        $masterPath = join($np, PATH_DELIMITER);
                    } else
                    {
                        $test = false;
                        $found = false;
                        for($i=0;$i < count($mp) && $i < count($np);$i++)
                        {
                            if (PHPDOCUMENTOR_WINDOWS)
                            {
                                if (strtolower($mp[$i]) != strtolower($np[$i])) $found = $i;
                            } else
                            {
                                if ($mp[$i] != $np[$i]) $found = $i;
                            }
                        }
                        if ($found !== false)
                        {
                            $mp = array_slice($mp,0,$found);
                            $masterPath = join($mp,PATH_DELIMITER);
                        }
                    }
                }
            }
        }
        return $masterPath;
    }
    
    /**
     * Retrieve tutorial subdirectories and their contents from the list of
     * files to parse
     * @param array array of paths (strings)
     * @return array array(filelist - tutorials, tutorials)
     */
    function getTutorials($filelist)
    {
        $list = $tutorials = array();
        foreach($filelist as $file)
        {
            if (strpos($file,'tutorials/') !== false)
            {
                $tutedir = explode('/',substr($file,strpos($file,'tutorials/')));
                array_shift($tutedir);
                if (count($tutedir) <= 3)
                {
                    $res = array();
                    // kludge - will need to fix for 2.0
                    $res['category'] = $GLOBALS['phpDocumentor_DefaultCategoryName'];
                    $res['package'] = array_shift($tutedir);
                    $res['subpackage'] = '';
                    if (count($tutedir) > 1)
                    $res['subpackage'] = array_shift($tutedir);
                    $f = array_shift($tutedir);
                    $res['tutename'] = $f;
                    $f = explode('.',$f);
                    $res['tutetype'] = array_pop($f);
                    if ($res['tutetype'] == 'ini') continue;
                    $res['path'] = $file;
                    if (@file_exists($file . '.ini'))
                    {
                        $res['ini'] = phpDocumentor_parse_ini_file($file . '.ini', true);
                    } else
                    {
                        $res['ini'] = false;
                    }
                    $tutorials[] = $res;
                }
            } else $list[] = $file;
        }
        return array($list,$tutorials);
    }
    
    /**
     * @param string base directory from {@link getBase()}
     * @param array file list from {@link dirList()}
     * @return array array(filelist - README/INSTALL/CHANGELOG,
     *                     README/INSTALL/CHANGELOG)
     */
    function getReadmeInstallChangelog($base,$filelist)
    {
        $list = $ric = array();
        $names = $GLOBALS['_phpDocumentor_RIC_files'];
        foreach($filelist as $file)
        {
            if ((dirname($file) == $base) && in_array(strtoupper(basename($file)), $names))
            { // be sure to change $this->checkIgnore() if any other files are added here!!
                $ric[] = $file;
            } else
            {
                $list[] = $file;
            }
        }
        return array($list,$ric);
    }
  
    function getDirTree($dir, $base_dir, $ignore = array(), $hidden="off")
    {
        $allfiles = $this->dirList($dir,$hidden);
        $struc = array();
        foreach($allfiles as $file)
        {
            if ($this->checkIgnore(basename($file),dirname($file),$ignore)) continue;
            $path = substr(dirname($file),strlen(str_replace('\\','/',realpath($base_dir)))+1);
            if (!$path) $path = '/';
            $parts = pathinfo($file);
            if (!isset($parts['extension']))
            {
                $parts['extension'] = '';
            }
            $struc[$path][] = array(
                'file' => $parts['basename'],
                'ext' => $parts['extension'],
                'path' => $file);
        }
        uksort($struc,'strnatcasecmp');
        foreach($struc as $key => $ind)
        {
            usort($ind,'Ioinc_sortfiles');
            $struc[$key] = $ind;
            $save = $key;
            if ($key != '/')
            {
                $key = explode('/',$key);
                while (count($key))
                {
                    array_pop($key);
                    if (isset($struc[join('/',$key)]))
                    {
                        $struc[join('/',$key)][substr($save,strlen(join('/',$key)) + 1)] = $ind;
                        unset($struc[$save]);
                    }
                }
            }
        }
        foreach($struc as $key => $ind)
        {
            if ($key != '/')
            {
                if (count(explode('/',$key)) == 1)
                {
                    $struc['/'][$key] = $struc[$key];
                    unset($struc[$key]);
                }
            }
        }
        $tempstruc = $struc;
        unset($tempstruc['/']);
        $leftover_dirs = array_keys($tempstruc);
        $splitdirs = array();
        foreach($leftover_dirs as $dir)
        {
            $splitdirs[] = explode('/',$dir);
        }
        $leftover_dirs = array();

        foreach($splitdirs as $dir)
        {
            $save = join($dir,'/');
            $struc['/'] = setup_dirs($struc['/'], $dir, $tempstruc[$save]);
            unset($struc[$save]);
        }
        @uksort($struc['/'],'Ioinc_mystrucsort');
        return $struc;
    }
    
    /**
     * Reads a file and returns it as a string
     * Does basic error checking
     *
     * file extensions are set in {@link phpdoc.inc}
     *
     * @global array PHP File extensions, used to validate that $path is a PHP File
     * @global array PHP File extensions in a CVS repository, used to validate that $path is a PHP File
     * @param    string    $path
     */
    function readPhpFile($path, $quietMode = false)
    {
        global $_phpDocumentor_cvsphpfile_exts, $_phpDocumentor_phpfile_exts;
        // tiberiusblue addition
        $cvsExt = $_phpDocumentor_cvsphpfile_exts; 
        $ext = $_phpDocumentor_phpfile_exts;
        if (file_exists($path))
        {
            if (is_file($path))
            {
                // check extension
                $tmp = explode(".",$path);
                // tiberiusblue addition
                $tmp2 = $tmp;
                if (in_array(array_pop($tmp),$ext))
                {
                    phpDocumentor_out(" -- Parsing file\n");
                    flush();
                    if (function_exists('file_get_contents')) {
                        return file_get_contents($path);
                    }
                    $fp = fopen($path,"r");
                    $ret = fread($fp,filesize($path));
                    fclose($fp);
                    return $ret;
                } elseif (in_array(array_pop($tmp2),$cvsExt)) 
                { 
                    phpDocumentor_out(" CVS file [EXPERIMENTAL]\n"); 
                    flush();
                    if (function_exists('file_get_contents')) {
                        $ret = file_get_contents($path);
                    } else {
                        $fp = fopen($path,"r"); 
                        $ret = fread($fp,filesize($path)); 
                        fclose($fp); 
                    }
                    $ret = strstr($ret,"<?");
                    $ret = substr($ret,0,strpos($ret,"@\n"));
                    $ret = str_replace("@@","@",$ret); 
                    return $ret; 
                } else
                {
                    phpDocumentor_out(" -- File not parsed, not a php file\n");
                    flush();
                }
            } else {
                phpDocumentor_out(" -- Unable to read file, not a file\n");
                flush();
            }
        } else {
            phpDocumentor_out(" -- Unable to read file, file does not exist\n");
            flush();
           }
    }

    /**
     * Tell whether to ignore a file or a directory
     * allows * and ? wildcards
     *
     * @author Greg Beaver <cellog@users.sourceforge.net>
     * @param    string  $file    just the file name of the file or directory,
     *                          in the case of directories this is the last dir
     * @param    string  $path    the full path
     * @param    array   $ignore
     * @return   bool    true if $path should be ignored, false if it should not
     */
    function checkIgnore($file,$path,$ignore,$ignore_no_ext = true)
    {
        global $_phpDocumentor_RIC_files;
        $path = realpath($path);
        if (!count($ignore)) return false;
        if ($ignore_no_ext && 
            !in_array(strtoupper($file), $_phpDocumentor_RIC_files))
        {
            if (!is_numeric(strpos($file,'.'))) return true;
        }
        if (!isset($this->ignore) || !$this->ignore)
        {
            $this->_setupIgnore($ignore);
            if (!$this->ignore)
            {
                return false;
            }
        }
        if (is_array($this->ignore))
        {
            foreach($this->ignore as $match)
            {
                // match is an array if the ignore parameter was a /path/to/pattern
                if (is_array($match))
                {
                    // check to see if the path matches with a path delimiter appended
                    preg_match('/^' . strtoupper($match[0]).'$/', strtoupper($path) . PATH_DELIMITER,$find);
                    if (!count($find))
                    {
                        // check to see if it matches without an appended path delimiter
                        preg_match('/^' . strtoupper($match[0]).'$/', strtoupper($path), $find);
                    }
                    if (count($find))
                    {
                        // check to see if the file matches the file portion of the regex string
                        preg_match('/^' . strtoupper($match[1]).'$/', strtoupper($file), $find);
                        if (count($find))
                        {
                            return true;
                        }
                    }
                    // check to see if the full path matches the regex
                    preg_match('/^' . strtoupper($match[0]).'$/',
                               strtoupper($path . DIRECTORY_SEPARATOR . $file), $find);
                    if (count($find))
                    {
                        return true;
                    }
                } else
                {
                    // ignore parameter was just a pattern with no path delimiters
                    // check it against the path
                    preg_match('/^' . strtoupper($match).'$/', strtoupper($path), $find);
                    if (count($find))
                    {
                        return true;
                    }
                    // check it against the file only
                    preg_match('/^' . strtoupper($match).'$/', strtoupper($file), $find);
                    if (count($find))
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * Construct the {@link $ignore} array
     * @author Greg Beaver <cellog@users.sourceforge.net>
     * @param array strings of files/paths/wildcards to ignore
     * @access protected
     */
    function _setupIgnore($ignore)
    {
        $ig = array();
        if (is_array($ignore))
        {
            for($i=0; $i<count($ignore);$i++)
            {
                $ignore[$i] = strtr($ignore[$i], "\\", "/");
                $ignore[$i] = str_replace('//','/',$ignore[$i]);
                if (!empty($ignore[$i]))
                {
                    if (!is_numeric(strpos($ignore[$i],PATH_DELIMITER)))
                    {
                        $ig[] = $this->getRegExpableSearchString($ignore[$i]);
                    } else
                    {
                        if (basename($ignore[$i]) . PATH_DELIMITER == $ignore[$i])
                        $ig[] = $this->getRegExpableSearchString($ignore[$i]);
                        else
                        $ig[] = array($this->getRegExpableSearchString($ignore[$i]),$this->getRegExpableSearchString(basename($ignore[$i])));
                    }
                }
            }
            if (count($ig)) $this->ignore = $ig;
        } else $this->ignore = false;
    }
    
    /**
     * Converts $s into a string that can be used with preg_match
     * @param string $s string with wildcards ? and *
     * @author Greg Beaver <cellog@users.sourceforge.net>
     * @return string converts * to .*, ? to ., etc.
     */
    function getRegExpableSearchString($s)
    {
        $y = '\/';
        if (DIRECTORY_SEPARATOR == '\\')
        {
            $y = '\\\\';
        }
        $s = str_replace('/', DIRECTORY_SEPARATOR, $s);
        $x = strtr($s, array('?' => '.','*' => '.*','.' => '\\.','\\' => '\\\\','/' => '\\/',
                                '[' => '\\[',']' => '\\]','-' => '\\-'));
        if (strpos($s, DIRECTORY_SEPARATOR) !== false &&
            strrpos($s, DIRECTORY_SEPARATOR) === strlen($s) - 1)
        {
            $x = "(?:.*$y$x?.*|$x.*)";
        }
        return $x;
    }
    
    /**
     * Removes files from the $dir array that do not match the search string in
     * $match
     * @param array $dir array of filenames (full path)
     * @param string $match search string with wildcards
     * @author Greg Beaver <cellog@users.sourceforge.net>
     * @return string|array listing of every file in a directory that matches
     *                      the search string
     */
    function removeNonMatches($dir, $match)
    {
        $match = $this->getRegExpableSearchString($match);
        $nodir = false;
        if (!is_array($dir))
        {
            $dir = array($dir);
            $nodir = true;
        }
        foreach($dir as $i => $file)
        {
            preg_match('/^'.$match.'$/',basename($file),$find);
            if (!count($find)) unset($dir[$i]);
        }
        if ($nodir) return $dir[0];
        return $dir;
    }
    
    /**
     * Take a filename with wildcards and return all files that match the
     * wildcards
     * @param string $file a full path from the -f command-line parameter, with
     * potential * and ? wildcards.
     * @return mixed if $file contains wildcards, returns an array of matching
     *               files, otherwise returns false
     * @author Greg Beaver <cellog@users.sourceforge.net>
     */
    function getAllFiles($file)
    {
        $path = realpath(dirname($file));
        $file = basename($file);
        // any wildcards?
        if (is_numeric(strpos($file,'?')) || is_numeric(strpos($file,'*')))
        {
            $files = $this->dirList($path);
            $a = $this->removeNonMatches($files,$file);
            return $a;
        }
        return false;
    }
}

/**#@+
 * Sorting functions for the file list
 * @param string
 * @param string
 */
function Ioinc_sortfiles($a, $b)
{
    return strnatcasecmp($a['file'],$b['file']);
}

function Ioinc_mystrucsort($a, $b)
{
    if (is_numeric($a) && is_string($b)) return 1;
    if (is_numeric($b) && is_string($a)) return -1;
    if (is_numeric($a) && is_numeric($b))
    {
        if ($a > $b) return 1;
        if ($a < $b) return -1;
        if ($a == $b) return 0;
    }
    return strnatcasecmp($a,$b);
}
/**#@-*/

/**
 * Recursively add all the subdirectories of $contents to $dir without erasing anything in
 * $dir
 * @param array
 * @param array
 * @return array processed $dir
 */
function set_dir($dir,$contents)
{
    while(list($one,$two) = each($contents))
    {
        if (isset($dir[$one]))
        {
            $dir[$one] = set_dir($dir[$one],$contents[$one]);
        } else $dir[$one] = $two;
    }
    return $dir;
}

/**
 * Recursively move contents of $struc into associative array
 *
 * The contents of $struc have many indexes like 'dir/subdir/subdir2'.
 * This function converts them to
 * array('dir' => array('subdir' => array('subdir2')))
 * @param array struc is array('dir' => array of files in dir,'dir/subdir' => array of files in dir/subdir,...)
 * @param array array form of 'dir/subdir/subdir2' array('dir','subdir','subdir2')
 * @return array same as struc but with array('dir' => array(file1,file2,'subdir' => array(file1,...)))
 */
function setup_dirs($struc,$dir,$contents)
{
    if (!count($dir))
    {
        foreach($contents as $dir => $files)
        {
            if (is_string($dir))
            {
                if (strpos($dir,'/'))
                {
                    $test = true;
                    $a = $contents[$dir];
                    unset($contents[$dir]);
                    $b = explode('/',$dir);
                    $c = array_shift($b);
                    if (isset($contents[$c]))
                    {
                        $contents[$c] = set_dir($contents[$c],setup_dirs(array(),$b,$a));
                    } else $contents[$c] = setup_dirs(array(),$b,$a);
                }
            }
        }
        return $contents;
    }
    $me = array_shift($dir);
    if (!isset($struc[$me])) $struc[$me] = array();
    $struc[$me] = setup_dirs($struc[$me],$dir,$contents);
    return $struc;
}

if (!function_exists('get_include_path')) {
function get_include_path()
{
    return ini_get('include_path');
}
}
?>
