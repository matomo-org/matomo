<?php
/**
 * Exception classes for HTML_QuickForm2
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
 * @version    SVN: $Id: Exception.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for exceptions in PEAR
 */
// require_once 'PEAR/Exception.php';

/**
 * Base class for exceptions in HTML_QuickForm2 package
 *
 * Such a base class is required by the Exception RFC:
 * http://pear.php.net/pepr/pepr-proposal-show.php?id=132
 * It will rarely be thrown directly, its specialized subclasses will be
 * thrown most of the time.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Exception extends PEAR_Exception
{
}

/**
 * Exception that denotes some resource was not found
 *
 * One example is trying to instantiate a nonexistent class in Factory
 * <code>
 * try {
 *     HTML_QuickForm2_Factory::registerElement('missing', 'NonExistent');
 *     $el = HTML_QuickForm2_Factory::createElement('missing');
 * } catch (HTML_QuickForm2_NotFoundException $e) {
 *     echo $e->getMessage();
 * }
 * </code>
 * This code fill output "Class 'NonExistent' does not exist and no file to load"
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_NotFoundException extends HTML_QuickForm2_Exception
{
}

/**
 * Exception that denotes invalid arguments were passed
 *
 * One example is trying to create an element of type which is unknown to Factory
 * <code>
 * try {
 *     $el = HTML_QuickForm2_Factory::createElement('unknown');
 * } catch (HTML_QuickForm2_InvalidArgumentException $e) {
 *     echo $e->getMessage();
 * }
 * </code>
 * This code will output "Element type 'unknown' is not known"
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_InvalidArgumentException extends HTML_QuickForm2_Exception
{
}
?>
