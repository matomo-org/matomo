<?php

/**
 * Config_File class.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com 
 *
 * @link http://www.smarty.net/
 * @version 2.6.26
 * @copyright Copyright: 2001-2005 New Digital Group, Inc.
 * @author Andrei Zmievski <andrei@php.net>
 * @access public
 * @package Smarty
 */

/* $Id$ */

/**
 * Config file reading class
 * @package Smarty
 */
class Config_File {
    /**#@+
     * Options
     * @var boolean
     */
    /**
     * Controls whether variables with the same name overwrite each other.
     */
    var $overwrite        =    true;

    /**
     * Controls whether config values of on/true/yes and off/false/no get
     * converted to boolean values automatically.
     */
    var $booleanize        =    true;

    /**
     * Controls whether hidden config sections/vars are read from the file.
     */
    var $read_hidden     =    true;

    /**
     * Controls whether or not to fix mac or dos formatted newlines.
     * If set to true, \r or \r\n will be changed to \n.
     */
    var $fix_newlines =    true;
    /**#@-*/

    /** @access private */
    var $_config_path    = "";
    var $_config_data    = array();
    /**#@-*/

    /**
     * Constructs a new config file class.
     *
     * @param string $config_path (optional) path to the config files
     */
    function Config_File($config_path = NULL)
    {
        if (isset($config_path))
            $this->set_path($config_path);
    }


    /**
     * Set the path where configuration files can be found.
     *
     * @param string $config_path path to the config files
     */
    function set_path($config_path)
    {
        if (!empty($config_path)) {
            if (!is_string($config_path) || !file_exists($config_path) || !is_dir($config_path)) {
                $this->_trigger_error_msg("Bad config file path '$config_path'");
                return;
            }
            if(substr($config_path, -1) != DIRECTORY_SEPARATOR) {
                $config_path .= DIRECTORY_SEPARATOR;
            }

            $this->_config_path = $config_path;
        }
    }


    /**
     * Retrieves config info based on the file, section, and variable name.
     *
     * @param string $file_name config file to get info for
     * @param string $section_name (optional) section to get info for
     * @param string $var_name (optional) variable to get info for
     * @return string|array a value or array of values
     */
    function get($file_name, $section_name = NULL, $var_name = NULL)
    {
        if (empty($file_name)) {
            $this->_trigger_error_msg('Empty config file name');
            return;
        } else {
            $file_name = $this->_config_path . $file_name;
            if (!isset($this->_config_data[$file_name]))
                $this->load_file($file_name, false);
        }

        if (!empty($var_name)) {
            if (empty($section_name)) {
                return $this->_config_data[$file_name]["vars"][$var_name];
            } else {
                if(isset($this->_config_data[$file_name]["sections"][$section_name]["vars"][$var_name]))
                    return $this->_config_data[$file_name]["sections"][$section_name]["vars"][$var_name];
                else
                    return array();
            }
        } else {
            if (empty($section_name)) {
                return (array)$this->_config_data[$file_name]["vars"];
            } else {
                if(isset($this->_config_data[$file_name]["sections"][$section_name]["vars"]))
                    return (array)$this->_config_data[$file_name]["sections"][$section_name]["vars"];
                else
                    return array();
            }
        }
    }


    /**
     * Retrieves config info based on the key.
     *
     * @param $file_name string config key (filename/section/var)
     * @return string|array same as get()
     * @uses get() retrieves information from config file and returns it
     */
    function &get_key($config_key)
    {
        list($file_name, $section_name, $var_name) = explode('/', $config_key, 3);
        $result = &$this->get($file_name, $section_name, $var_name);
        return $result;
    }

    /**
     * Get all loaded config file names.
     *
     * @return array an array of loaded config file names
     */
    function get_file_names()
    {
        return array_keys($this->_config_data);
    }


    /**
     * Get all section names from a loaded file.
     *
     * @param string $file_name config file to get section names from
     * @return array an array of section names from the specified file
     */
    function get_section_names($file_name)
    {
        $file_name = $this->_config_path . $file_name;
        if (!isset($this->_config_data[$file_name])) {
            $this->_trigger_error_msg("Unknown config file '$file_name'");
            return;
        }

        return array_keys($this->_config_data[$file_name]["sections"]);
    }


    /**
     * Get all global or section variable names.
     *
     * @param string $file_name config file to get info for
     * @param string $section_name (optional) section to get info for
     * @return array an array of variables names from the specified file/section
     */
    function get_var_names($file_name, $section = NULL)
    {
        if (empty($file_name)) {
            $this->_trigger_error_msg('Empty config file name');
            return;
        } else if (!isset($this->_config_data[$file_name])) {
            $this->_trigger_error_msg("Unknown config file '$file_name'");
            return;
        }

        if (empty($section))
            return array_keys($this->_config_data[$file_name]["vars"]);
        else
            return array_keys($this->_config_data[$file_name]["sections"][$section]["vars"]);
    }


    /**
     * Clear loaded config data for a certain file or all files.
     *
     * @param string $file_name file to clear config data for
     */
    function clear($file_name = NULL)
    {
        if ($file_name === NULL)
            $this->_config_data = array();
        else if (isset($this->_config_data[$file_name]))
            $this->_config_data[$file_name] = array();
    }


    /**
     * Load a configuration file manually.
     *
     * @param string $file_name file name to load
     * @param boolean $prepend_path whether current config path should be
     *                              prepended to the filename
     */
    function load_file($file_name, $prepend_path = true)
    {
        if ($prepend_path && $this->_config_path != "")
            $config_file = $this->_config_path . $file_name;
        else
            $config_file = $file_name;

        ini_set('track_errors', true);
        $fp = @fopen($config_file, "r");
        if (!is_resource($fp)) {
            $this->_trigger_error_msg("Could not open config file '$config_file'");
            return false;
        }

        $contents = ($size = filesize($config_file)) ? fread($fp, $size) : '';
        fclose($fp);

        $this->_config_data[$config_file] = $this->parse_contents($contents);
        return true;
    }

    /**
     * Store the contents of a file manually.
     *
     * @param string $config_file file name of the related contents
     * @param string $contents the file-contents to parse
     */
    function set_file_contents($config_file, $contents)
    {
        $this->_config_data[$config_file] = $this->parse_contents($contents);
        return true;
    }

    /**
     * parse the source of a configuration file manually.
     *
     * @param string $contents the file-contents to parse
     */
    function parse_contents($contents)
    {
        if($this->fix_newlines) {
            // fix mac/dos formatted newlines
            $contents = preg_replace('!\r\n?!', "\n", $contents);
        }

        $config_data = array();
        $config_data['sections'] = array();
        $config_data['vars'] = array();

        /* reference to fill with data */
        $vars =& $config_data['vars'];

        /* parse file line by line */
        preg_match_all('!^.*\r?\n?!m', $contents, $match);
        $lines = $match[0];
        for ($i=0, $count=count($lines); $i<$count; $i++) {
            $line = $lines[$i];
            if (empty($line)) continue;

            if ( substr($line, 0, 1) == '[' && preg_match('!^\[(.*?)\]!', $line, $match) ) {
                /* section found */
                if (substr($match[1], 0, 1) == '.') {
                    /* hidden section */
                    if ($this->read_hidden) {
                        $section_name = substr($match[1], 1);
                    } else {
                        /* break reference to $vars to ignore hidden section */
                        unset($vars);
                        $vars = array();
                        continue;
                    }
                } else {                    
                    $section_name = $match[1];
                }
                if (!isset($config_data['sections'][$section_name]))
                    $config_data['sections'][$section_name] = array('vars' => array());
                $vars =& $config_data['sections'][$section_name]['vars'];
                continue;
            }

            if (preg_match('/^\s*(\.?\w+)\s*=\s*(.*)/s', $line, $match)) {
                /* variable found */
                $var_name = rtrim($match[1]);
                if (strpos($match[2], '"""') === 0) {
                    /* handle multiline-value */
                    $lines[$i] = substr($match[2], 3);
                    $var_value = '';
                    while ($i<$count) {
                        if (($pos = strpos($lines[$i], '"""')) === false) {
                            $var_value .= $lines[$i++];
                        } else {
                            /* end of multiline-value */
                            $var_value .= substr($lines[$i], 0, $pos);
                            break;
                        }
                    }
                    $booleanize = false;

                } else {
                    /* handle simple value */
                    $var_value = preg_replace('/^([\'"])(.*)\1$/', '\2', rtrim($match[2]));
                    $booleanize = $this->booleanize;

                }
                $this->_set_config_var($vars, $var_name, $var_value, $booleanize);
            }
            /* else unparsable line / means it is a comment / means ignore it */
        }
        return $config_data;
    }

    /**#@+ @access private */
    /**
     * @param array &$container
     * @param string $var_name
     * @param mixed $var_value
     * @param boolean $booleanize determines whether $var_value is converted to
     *                            to true/false
     */
    function _set_config_var(&$container, $var_name, $var_value, $booleanize)
    {
        if (substr($var_name, 0, 1) == '.') {
            if (!$this->read_hidden)
                return;
            else
                $var_name = substr($var_name, 1);
        }

        if (!preg_match("/^[a-zA-Z_]\w*$/", $var_name)) {
            $this->_trigger_error_msg("Bad variable name '$var_name'");
            return;
        }

        if ($booleanize) {
            if (preg_match("/^(on|true|yes)$/i", $var_value))
                $var_value = true;
            else if (preg_match("/^(off|false|no)$/i", $var_value))
                $var_value = false;
        }

        if (!isset($container[$var_name]) || $this->overwrite)
            $container[$var_name] = $var_value;
        else {
            settype($container[$var_name], 'array');
            $container[$var_name][] = $var_value;
        }
    }

    /**
     * @uses trigger_error() creates a PHP warning/error
     * @param string $error_msg
     * @param integer $error_type one of
     */
    function _trigger_error_msg($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Config_File error: $error_msg", $error_type);
    }
    /**#@-*/
}

?>
