<?php
/**
 * Class with static methods for loading classes and files 
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
 * @version    SVN: $Id: Loader.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Exception classes for HTML_QuickForm2
 */
// require_once 'HTML/QuickForm2/Exception.php';
require_once dirname(__FILE__) . '/Exception.php';

/**
 * Class with static methods for loading classes and files 
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Loader
{
   /**
    * Tries to load a given class
    *
    * If no $includeFile was provided, $className will be used with underscores
    * replaced with path separators and '.php' extension appended
    *
    * @param    string  Class name to load
    * @param    string  Name of the file (supposedly) containing the given class
    * @throws   HTML_QuickForm2_NotFoundException   If the file either can't be
    *               loaded or doesn't contain the given class
    */
    public static function loadClass($className, $includeFile = null)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }

        if (empty($includeFile)) {
            $includeFile = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        }
        // Do not silence the errors with @, parse errors will not be seen
        include $includeFile;

        // Still no class?
        if (!class_exists($className, false) && !interface_exists($className, false)) {
            if (!self::fileExists($includeFile)) {
                throw new HTML_QuickForm2_NotFoundException(
                    "File '$includeFile' was not found"
                );
            } else {
                throw new HTML_QuickForm2_NotFoundException(
                    "Class '$className' was not found within file '$includeFile'"
                );
            }
        }
    }

   /**
    * Checks whether the file exists in the include path
    *
    * @param    string  file name
    * @return   bool
    */
    public static function fileExists($fileName)
    {
        $fp = @fopen($fileName, 'r', true);
        if (is_resource($fp)) {
            fclose($fp);
            return true;
        }
        return false;
    }

   /**
    * Loading of HTML_QuickForm2_* classes suitable for SPL autoload mechanism
    *
    * This method will only try to load a class if its name starts with
    * HTML_QuickForm2. Register with the following:
    * <code>
    * spl_autoload_register(array('HTML_QuickForm2_Loader', 'autoload'));
    * </code>
    *
    * @param    string  Class name
    * @return   bool    Whether class loaded successfully
    */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'HTML_QuickForm2')) {
            return false;
        }
        try {
            @self::loadClass($class);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
