<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Atom.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Feed_Entry_Abstract
 */
require_once 'Zend/Feed/Entry/Abstract.php';


/**
 * Concrete class for working with Atom entries.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Entry_Atom extends Zend_Feed_Entry_Abstract
{
    /**
     * Content-Type
     */
    const CONTENT_TYPE = 'application/atom+xml';

    /**
     * Root XML element for Atom entries.
     *
     * @var string
     */
    protected $_rootElement = 'entry';

    /**
     * Root namespace for Atom entries.
     *
     * @var string
     */
    protected $_rootNamespace = 'atom';


    /**
     * Delete an atom entry.
     *
     * Delete tries to delete this entry from its feed. If the entry
     * does not contain a link rel="edit", we throw an error (either
     * the entry does not yet exist or this is not an editable
     * feed). If we have a link rel="edit", we do the empty-body
     * HTTP DELETE to that URI and check for a response of 2xx.
     * Usually the response would be 204 No Content, but the Atom
     * Publishing Protocol permits it to be 200 OK.
     *
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function delete()
    {
        // Look for link rel="edit" in the entry object.
        $deleteUri = $this->link('edit');
        if (!$deleteUri) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Cannot delete entry; no link rel="edit" is present.');
        }

        // DELETE
        $client = Zend_Feed::getHttpClient();
        do {
            $client->setUri($deleteUri);
            if (Zend_Feed::getHttpMethodOverride()) {
                $client->setHeader('X-HTTP-Method-Override', 'DELETE');
                $response = $client->request('POST');
            } else {
                $response = $client->request('DELETE');
            }
            $httpStatus = $response->getStatus();
            switch ((int) $httpStatus / 100) {
                // Success
                case 2:
                    return true;
                // Redirect
                case 3:
                    $deleteUri = $response->getHeader('Location');
                    continue;
                // Error
                default:
                    /**
                     * @see Zend_Feed_Exception
                     */
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception("Expected response code 2xx, got $httpStatus");
            }
        } while (true);
    }


    /**
     * Save a new or updated Atom entry.
     *
     * Save is used to either create new entries or to save changes to
     * existing ones. If we have a link rel="edit", we are changing
     * an existing entry. In this case we re-serialize the entry and
     * PUT it to the edit URI, checking for a 200 OK result.
     *
     * For posting new entries, you must specify the $postUri
     * parameter to save() to tell the object where to post itself.
     * We use $postUri and POST the serialized entry there, checking
     * for a 201 Created response. If the insert is successful, we
     * then parse the response from the POST to get any values that
     * the server has generated: an id, an updated time, and its new
     * link rel="edit".
     *
     * @param  string $postUri Location to POST for creating new entries.
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function save($postUri = null)
    {
        if ($this->id()) {
            // If id is set, look for link rel="edit" in the
            // entry object and PUT.
            $editUri = $this->link('edit');
            if (!$editUri) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Cannot edit entry; no link rel="edit" is present.');
            }

            $client = Zend_Feed::getHttpClient();
            $client->setUri($editUri);
            if (Zend_Feed::getHttpMethodOverride()) {
                $client->setHeaders(array('X-HTTP-Method-Override: PUT',
                    'Content-Type: ' . self::CONTENT_TYPE));
                $client->setRawData($this->saveXML());
                $response = $client->request('POST');
            } else {
                $client->setHeaders('Content-Type', self::CONTENT_TYPE);
                $client->setRawData($this->saveXML());
                $response = $client->request('PUT');
            }
            if ($response->getStatus() !== 200) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Expected response code 200, got ' . $response->getStatus());
            }
        } else {
            if ($postUri === null) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('PostURI must be specified to save new entries.');
            }
            $client = Zend_Feed::getHttpClient();
            $client->setUri($postUri);
            $client->setHeaders('Content-Type', self::CONTENT_TYPE);
            $client->setRawData($this->saveXML());
            $response = $client->request('POST');

            if ($response->getStatus() !== 201) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Expected response code 201, got '
                                              . $response->getStatus());
            }
        }

        // Update internal properties using $client->responseBody;
        @ini_set('track_errors', 1);
        $newEntry = new DOMDocument;
        $status = @$newEntry->loadXML($response->getBody());
        @ini_restore('track_errors');

        if (!$status) {
            // prevent the class to generate an undefined variable notice (ZF-2590)
            if (!isset($php_errormsg)) {
                if (function_exists('xdebug_is_enabled')) {
                    $php_errormsg = '(error message not available, when XDebug is running)';
                } else {
                    $php_errormsg = '(error message not available)';
                }
            }

            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('XML cannot be parsed: ' . $php_errormsg);
        }

        $newEntry = $newEntry->getElementsByTagName($this->_rootElement)->item(0);
        if (!$newEntry) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('No root <feed> element found in server response:'
                                          . "\n\n" . $client->responseBody);
        }

        if ($this->_element->parentNode) {
            $oldElement = $this->_element;
            $this->_element = $oldElement->ownerDocument->importNode($newEntry, true);
            $oldElement->parentNode->replaceChild($this->_element, $oldElement);
        } else {
            $this->_element = $newEntry;
        }
    }


    /**
     * Easy access to <link> tags keyed by "rel" attributes.
     *
     * If $elt->link() is called with no arguments, we will attempt to
     * return the value of the <link> tag(s) like all other
     * method-syntax attribute access. If an argument is passed to
     * link(), however, then we will return the "href" value of the
     * first <link> tag that has a "rel" attribute matching $rel:
     *
     * $elt->link(): returns the value of the link tag.
     * $elt->link('self'): returns the href from the first <link rel="self"> in the entry.
     *
     * @param  string $rel The "rel" attribute to look for.
     * @return mixed
     */
    public function link($rel = null)
    {
        if ($rel === null) {
            return parent::__call('link', null);
        }

        // index link tags by their "rel" attribute.
        $links = parent::__get('link');
        if (!is_array($links)) {
            if ($links instanceof Zend_Feed_Element) {
                $links = array($links);
            } else {
                return $links;
            }
        }

        foreach ($links as $link) {
            if (empty($link['rel'])) {
                $link['rel'] = 'alternate'; // see Atom 1.0 spec
            }
            if ($rel == $link['rel']) {
                return $link['href'];
            }
        }

        return null;
    }

}
