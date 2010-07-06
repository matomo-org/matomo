<?php
/**
 * Object wrapping around session variable used to store controller data
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
 * @version    SVN: $Id: SessionContainer.php 293868 2010-01-23 18:37:16Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Object wrapping around session variable used to store controller data
 *
 * Unlike old HTML_QuickForm_Controller, this does not extend HTML_QuickForm2
 * but accepts an instance of that in the constructor. You need to create a
 * subclass of this class and implement its populateForm() method.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Controller_SessionContainer
{
   /**
    * A reference to a key in $_SESSION superglobal array
    * @var array
    */
    protected $data;

   /**
    * Class constructor
    *
    * Initializes a variable in $_SESSION array, its name is based upon the
    * name of the Controller passed here
    *
    * @param    HTML_QuickForm2_Controller
    */
    public function __construct(HTML_QuickForm2_Controller $controller)
    {
        $name = sprintf(HTML_QuickForm2_Controller::KEY_CONTAINER,
                        $controller->getId());
        if (empty($_SESSION[$name])) {
            $_SESSION[$name] = array(
                'datasources' => array(),
                'values'      => array(),
                'valid'       => array()
            );
        }
        $this->data =& $_SESSION[$name];
    }

   /**
    * Stores the page submit values
    *
    * @param    string  Page ID
    * @param    array   Page submit values
    */
    public function storeValues($pageId, array $values)
    {
        $this->data['values'][$pageId] = $values;
    }

   /**
    * Returns the page values kept in session
    *
    * @param    string  Page ID
    * @return   array
    */
    public function getValues($pageId)
    {
        return array_key_exists($pageId, $this->data['values'])
               ? $this->data['values'][$pageId]: array();
    }

   /**
    * Stores the page validation status
    *
    * @param    string  Page ID
    * @param    bool    Whether the page is valid
    */
    public function storeValidationStatus($pageId, $status)
    {
        $this->data['valid'][$pageId] = (bool)$status;
    }

   /**
    * Returns the page validation status kept in session
    *
    * @param    string  Page ID
    * @return   bool
    */
    public function getValidationStatus($pageId)
    {
        return array_key_exists($pageId, $this->data['valid'])
               ? $this->data['valid'][$pageId]: null;

    }

   /**
    * Stores the controller data sources
    *
    * @param    array   A new data source list
    * @throws   HTML_QuickForm2_InvalidArgumentException    if given array
    *               contains something that is not a valid data source
    */
    public function storeDatasources(array $datasources)
    {
        foreach ($datasources as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    'Array should contain only DataSource instances'
                );
            }
        }
        $this->data['datasources'] = $datasources;
    }

   /**
    * Returns the controller data sources
    *
    * @return array
    */
    public function getDatasources()
    {
        return $this->data['datasources'];
    }

   /**
    * Stores some user-supplied parameter alongside controller data
    *
    * It is sometimes useful to pass some additional user data between pages
    * of the form, thus this method. It will be removed with all the other
    * data by {@link HTML_QuickForm2_Controller::destroySessionContainer()}
    *
    * @param    string  Parameter name
    * @param    string  Parameter value
    */
    public function storeOpaque($name, $value)
    {
        if (!array_key_exists('opaque', $this->data)) {
            $this->data['opaque'] = array();
        }
        $this->data['opaque'][$name] = $value;
    }

   /**
    * Returns a user-supplied parameter
    *
    * @param    string  Parameter name
    * @return   mixed
    */
    public function getOpaque($name)
    {
        return (array_key_exists('opaque', $this->data)
                && array_key_exists($name, $this->data['opaque']))
               ? $this->data['opaque'][$name]: null;
    }
}