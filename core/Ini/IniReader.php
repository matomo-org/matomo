<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Ini;

/**
 * Reads INI configuration.
 */
class IniReader
{
    /**
     * Reads a INI configuration file and returns it as an array.
     *
     * The array returned is multidimensional, indexed by section names:
     *
     * ```
     * array(
     *     'Section 1' => array(
     *         'value1' => 'hello',
     *         'value2' => 'world',
     *     ),
     *     'Section 2' => array(
     *         'value3' => 'foo',
     *     )
     * );
     * ```
     *
     * @param string $filename The file to read.
     * @throws IniReadingException
     * @return array
     */
    public function readFile($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new IniReadingException(sprintf("The file %s doesn't exist or is not readable", $filename));
        }

        $ini = $this->getFileContent($filename);

        if ($ini === false) {
            throw new IniReadingException(sprintf('Impossible to read the file %s', $filename));
        }

        return $this->readString($ini);
    }

    /**
     * Reads a INI configuration string and returns it as an array.
     *
     * The array returned is multidimensional, indexed by section names:
     *
     * ```
     * array(
     *     'Section 1' => array(
     *         'value1' => 'hello',
     *         'value2' => 'world',
     *     ),
     *     'Section 2' => array(
     *         'value3' => 'foo',
     *     )
     * );
     * ```
     *
     * @param string $ini String containing INI configuration.
     * @throws IniReadingException
     * @return array
     */
    public function readString($ini)
    {
        if (!function_exists('parse_ini_file')) {
            return $this->readIni($ini, true);
        }

        $array = @parse_ini_string($ini, true, INI_SCANNER_RAW);

        if ($array === false) {
            $e = error_get_last();
            throw new IniReadingException('Syntax error in INI configuration: ' . $e['message']);
        }

        $array = $this->decodeValues($array);

        return $array;
    }

    /**
     * Reimplementation in case `parse_ini_file()` is disabled.
     *
     * @author Andrew Sohn <asohn (at) aircanopy (dot) net>
     * @author anthon (dot) pang (at) gmail (dot) com
     *
     * @param string $ini
     * @param bool $processSections
     * @return array
     */
    private function readIni($ini, $processSections)
    {
        if (is_string($ini)) {
            $ini = explode("\n", str_replace("\r", "\n", $ini));
        }
        if (count($ini) == 0) {
            return array();
        }

        $sections = array();
        $values = array();
        $result = array();
        $globals = array();
        $i = 0;
        foreach ($ini as $line) {
            $line = trim($line);
            $line = str_replace("\t", " ", $line);

            // Comments
            if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {
                continue;
            }

            // Sections
            if ($line{0} == '[') {
                $tmp = explode(']', $line);
                $sections[] = trim(substr($tmp[0], 1));
                $i++;
                continue;
            }

            // Key-value pair
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (strstr($value, ";")) {
                $tmp = explode(';', $value);
                if (count($tmp) == 2) {
                    if ((($value{0} != '"') && ($value{0} != "'")) ||
                        preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
                        preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value)
                    ) {
                        $value = $tmp[0];
                    }
                } else {
                    if ($value{0} == '"') {
                        $value = preg_replace('/^"(.*)".*/', '$1', $value);
                    } elseif ($value{0} == "'") {
                        $value = preg_replace("/^'(.*)'.*/", '$1', $value);
                    } else {
                        $value = $tmp[0];
                    }
                }
            }

            $value = trim($value);
            $value = trim($value, "'\"");

            if ($i == 0) {
                if (substr($key, -2) == '[]') {
                    $globals[substr($key, 0, -2)][] = $value;
                } else {
                    $globals[$key] = $value;
                }
            } else {
                if (substr($key, -2) == '[]') {
                    $values[$i - 1][substr($key, 0, -2)][] = $value;
                } else {
                    $values[$i - 1][$key] = $value;
                }
            }
        }

        for ($j = 0; $j < $i; $j++) {
            if (isset($values[$j])) {
                if ($processSections === true) {
                    $result[$sections[$j]] = $values[$j];
                } else {
                    $result[] = $values[$j];
                }
            } else {
                if ($processSections === true) {
                    $result[$sections[$j]] = array();
                }
            }
        }

        return $result + $globals;
    }

    /**
     * @param string $filename
     * @return bool|string Returns false if failure.
     */
    private function getFileContent($filename)
    {
        if (function_exists('file_get_contents')) {
            return file_get_contents($filename);
        } elseif (function_exists('file')) {
            $ini = file($filename);
            if ($ini !== false) {
                return implode("\n", $ini);
            }
        } elseif (function_exists('fopen') && function_exists('fread')) {
            $handle = fopen($filename, 'r');
            if (!$handle) {
                return false;
            }
            $ini = fread($handle, filesize($filename));
            fclose($handle);
            return $ini;
        }

        return false;
    }

    private function decodeValues($config)
    {
        foreach ($config as &$section) {
            foreach ($section as $option => $value) {
                $section[$option] = $this->decodeValue($value);
            }
        }
        return $config;
    }

    /**
     * We have to decode values manually because parse_ini_file() has a poor implementation.
     *
     * @param mixed $value
     * @return mixed
     */
    private function decodeValue($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = $this->decodeValue($subValue);
            }
            return $value;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        switch (strtolower($value)) {
            case '':
            case 'null' :
                return null;
            case 'true' :
            case 'yes' :
            case 'on' :
                return true;
            case 'false':
            case 'no':
            case 'off':
                return false;
        }

        return $value;
    }
}
