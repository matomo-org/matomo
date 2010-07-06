<?php
/**
 * Rule for required elements
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
 * @version    SVN: $Id: Required.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Rule checking that the form field is not empty
 */
// require_once 'HTML/QuickForm2/Rule/Nonempty.php';

/**
 * Rule for required elements
 *
 * The main difference from "nonempty" Rule is that
 * - elements to which this Rule is attached will be considered required
 *   ({@link HTML_QuickForm2_Node::isRequired()} will return true for them) and
 *   marked accordingly when outputting the form
 * - this Rule can only be added directly to the element and other Rules can
 *   only be added to it via and_() method
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Required extends HTML_QuickForm2_Rule_Nonempty
{
   /**
    * Disallows adding a rule to the chain with an "or" operator
    *
    * Required rules are different from all others because they affect the
    * visual representation of an element ("* denotes required field").
    * Therefore we cannot allow chaining other rules to these via or_(), since
    * this will effectively mean that the field is not required anymore and the
    * visual difference is bogus.
    *
    * @param    HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_Exception
    */
    public function or_(HTML_QuickForm2_Rule $next)
    {
        throw new HTML_QuickForm2_Exception(
            'or_(): Cannot add a rule to "required" rule'
        );
    }
}
?>
