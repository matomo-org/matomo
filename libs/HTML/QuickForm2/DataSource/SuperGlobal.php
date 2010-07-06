<?php
/**
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: SuperGlobal.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Interface for data sources containing submitted values
 */
// require_once 'HTML/QuickForm2/DataSource/Submit.php';

/**
 * Array-based data source for HTML_QuickForm2 objects
 */
// require_once 'HTML/QuickForm2/DataSource/Array.php';

/**
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_DataSource_SuperGlobal
    extends HTML_QuickForm2_DataSource_Array
    implements HTML_QuickForm2_DataSource_Submit
{
   /**
    * Information on file uploads (from $_FILES)
    * @var array
    */
    protected $files = array();

   /**
    * Keys present in the $_FILES array
    * @var array
    */
    private static $_fileKeys = array('name', 'type', 'size', 'tmp_name', 'error');

   /**
    * Class constructor, intializes the internal arrays from superglobals
    *
    * @param    string  Request method (GET or POST)
    * @param    bool    Whether magic_quotes_gpc directive is on
    */
    public function __construct($requestMethod = 'POST', $magicQuotesGPC = false)
    {
        if (!$magicQuotesGPC) {
            if ('GET' == strtoupper($requestMethod)) {
                $this->values = $_GET;
            } else {
                $this->values = $_POST;
                $this->files  = $_FILES;
            }
        } else {
            if ('GET' == strtoupper($requestMethod)) {
                $this->values = $this->arrayMapRecursive('stripslashes', $_GET);
            } else {
                $this->values = $this->arrayMapRecursive('stripslashes', $_POST);
                foreach ($_FILES as $key1 => $val1) {
                    foreach ($val1 as $key2 => $val2) {
                        if ('name' == $key2) {
                            $this->files[$key1][$key2] = $this->arrayMapRecursive(
                                                             'stripslashes', $val2
                                                         );
                        } else {
                            $this->files[$key1][$key2] = $val2;
                        }
                    }
                }
            }
        }
    }

   /**
    * A recursive version of array_map() function
    *
    * @param     callback   Callback function to apply
    * @param     mixed      Input array
    * @return    array with callback applied
     */
    protected function arrayMapRecursive($callback, $arr)
    {
        if (!is_array($arr)) {
            return call_user_func($callback, $arr);
        }
        $mapped = array();
        foreach ($arr as $k => $v) {
            $mapped[$k] = is_array($v)?
                          $this->arrayMapRecursive($callback, $v):
                          call_user_func($callback, $v);
        }
        return $mapped;
    }

    public function getUpload($name)
    {
        if (empty($this->files)) {
            return null;
        }
        if (false !== ($pos = strpos($name, '['))) {
            $tokens = explode('[', str_replace(']', '', $name));
            $base   = array_shift($tokens);
            $value  = array();
            if (!isset($this->files[$base]['name'])) {
                return null;
            }
            foreach (self::$_fileKeys as $key) {
                $value[$key] = $this->files[$base][$key];
            }

            do {
                $token = array_shift($tokens);
                if (!isset($value['name'][$token])) {
                    return null;
                }
                foreach (self::$_fileKeys as $key) {
                    $value[$key] = $value[$key][$token];
                }
            } while (!empty($tokens));
            return $value;
        } elseif(isset($this->files[$name])) {
            return $this->files[$name];
        } else {
            return null;
        }
    }
}
?>
