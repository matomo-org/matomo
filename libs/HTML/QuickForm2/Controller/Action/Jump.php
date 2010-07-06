<?php
/**
 * This handler performs an HTTP redirect to a specific page
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
 * @version    SVN: $Id: Jump.php 294039 2010-01-26 12:29:46Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Interface for Controller action handlers */
// require_once 'HTML/QuickForm2/Controller/Action.php';

/**
 * This handler performs an HTTP redirect to a specific page
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Controller_Action_Jump
    implements HTML_QuickForm2_Controller_Action
{
   /**
    * Splits (part of) the URI into path and query components
    *
    * @param    string  String of the form 'foo?bar'
    * @return   array   Array of the form array('foo', '?bar)
    */
    protected static function splitUri($uri)
    {
        if (false === ($qm = strpos($uri, '?'))) {
            return array($uri, '');
        } else {
            return array(substr($uri, 0, $qm), substr($uri, $qm));
        }
    }

   /**
    * Removes the '..' and '.' segments from the path component
    *
    * @param    string  Path component of the URL, possibly with '.' and '..' segments
    * @return   string  Path component of the URL with '.' and '..' segments removed
    */
    protected static function normalizePath($path)
    {
        $pathAry = explode('/', $path);
        $i       = 1;

        do {
            if ('.' == $pathAry[$i]) {
                if ($i < count($pathAry) - 1) {
                    array_splice($pathAry, $i, 1);
                } else {
                    $pathAry[$i] = '';
                    $i++;
                }

            } elseif ('..' == $pathAry[$i]) {
                if (1 == $i) {
                    array_splice($pathAry, 1, 1);

                } elseif ('..' != $pathAry[$i - 1]) {
                    if ($i < count($pathAry) - 1) {
                        array_splice($pathAry, $i - 1, 2);
                        $i--;
                    } else {
                        array_splice($pathAry, $i - 1, 2, '');
                    }
                }

            } else {
                $i++;
            }
        } while ($i < count($pathAry));

        return implode('/', $pathAry);
    }

   /**
    * Resolves relative URL using current page's URL as base
    *
    * The method follows procedure described in section 4 of RFC 1808 and
    * passes the examples provided in section 5 of said RFC. Values from
    * $_SERVER array are used for calculation of "current URL"
    *
    * @param    string  Relative URL, probably from form's action attribute
    * @return   string  Absolute URL
    */
    protected static function resolveRelativeURL($url)
    {
        $https  = !empty($_SERVER['HTTPS']) && ('off' != strtolower($_SERVER['HTTPS']));
        $scheme = ($https? 'https:': 'http:');
        if ('//' == substr($url, 0, 2)) {
            return $scheme . $url;

        } else {
            $host   = $scheme . '//' . $_SERVER['SERVER_NAME'] .
                      (($https && 443 == $_SERVER['SERVER_PORT'] ||
                        !$https && 80 == $_SERVER['SERVER_PORT'])? '': ':' . $_SERVER['SERVER_PORT']);
            if ('' == $url) {
                return $host . $_SERVER['REQUEST_URI'];

            } elseif ('/' == $url[0]) {
                list($actPath, $actQuery) = self::splitUri($url);
                return $host . self::normalizePath($actPath) . $actQuery;

            } else {
                list($basePath, $baseQuery) = self::splitUri($_SERVER['REQUEST_URI']);
                list($actPath, $actQuery)   = self::splitUri($url);
                if ('' == $actPath) {
                    return $host . $basePath . $actQuery;
                } else {
                    $path = substr($basePath, 0, strrpos($basePath, '/') + 1) . $actPath;
                    return $host . self::normalizePath($path) . $actQuery;
                }
            }
        }
    }

    public function perform(HTML_QuickForm2_Controller_Page $page, $name)
    {
        // we check whether *all* pages up to current are valid
        // if there is an invalid page we go to it, instead of the
        // requested one
        if ($page->getController()->isWizard()
            && !$page->getController()->isValid($page)
        ) {
            $page = $page->getController()->getFirstInvalidPage();
        }

        // generate the URL for the page 'display' event and redirect to it
        $action = $page->getForm()->getAttribute('action');
        // Bug #13087: RFC 2616 requires an absolute URI in Location header
        if (!preg_match('@^([a-z][a-z0-9.+-]*):@i', $action)) {
            $action = self::resolveRelativeURL($action);
        }

        if (!$page->getController()->propagateId()) {
            $controllerId = '';
        } else {
            $controllerId = '&' . HTML_QuickForm2_Controller::KEY_ID . '=' .
                            $page->getController()->getId();
        }
        if (!defined('SID') || '' == SID || ini_get('session.use_only_cookies')) {
            $sessionId = '';
        } else {
            $sessionId = '&' . SID;
        }

        return $this->doRedirect(
            $action . (false === strpos($action, '?')? '?': '&') .
            $page->getButtonName('display') . '=true' . $controllerId . $sessionId
        );
    }


   /**
    * Redirects to a given URL via Location: header and exits the script
    *
    * A separate method is mostly needed for creating mocks of this class
    * during testing.
    *
    * @param    string  URL to redirect to
    */
    protected function doRedirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
}
?>
