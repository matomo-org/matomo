<?php
/**
 * Class for <input type="file" /> elements
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
 * @version    SVN: $Id: InputFile.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for <input> elements
 */
// require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="file" /> elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_InputFile extends HTML_QuickForm2_Element_Input
{
   /**
    * Default language for error messages
    */
    const DEFAULT_LANGUAGE = 'en';

   /**
    * Localized error messages for PHP's file upload errors
    * @var  array
    */
    protected $errorMessages = array(
        'en' => array(
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds size permitted by PHP configuration (%d bytes)',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in HTML form (%d bytes)',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: temporary directory is missing',
            UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write the file to disk',
            UPLOAD_ERR_EXTENSION  => 'File upload was stopped by extension'
        ),
        'fr' => array(
            UPLOAD_ERR_INI_SIZE   => 'Le fichier envoy&eacute; exc&egrave;de la taille autoris&eacute;e par la configuration de PHP (%d octets)',
            UPLOAD_ERR_FORM_SIZE  => 'Le fichier envoy&eacute; exc&egrave;de la taille de MAX_FILE_SIZE sp&eacute;cifi&eacute;e dans le formulaire HTML (%d octets)',
            UPLOAD_ERR_PARTIAL    => 'Le fichier n\'a &eacute;t&eacute; que partiellement t&eacute;l&eacute;charg&eacute;',
            UPLOAD_ERR_NO_TMP_DIR => 'Erreur serveur: le r&eacute;pertoire temporaire est manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur serveur: &eacute;chec de l\'&eacute;criture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION  => 'L\'envoi de fichier est arr&ecirc;t&eacute; par l\'extension'
        ),
        'ru' => array(
            UPLOAD_ERR_INI_SIZE   => '&#x420;&#x430;&#x437;&#x43c;&#x435;&#x440; &#x437;&#x430;&#x433;&#x440;&#x443;&#x436;&#x435;&#x43d;&#x43d;&#x43e;&#x433;&#x43e; &#x444;&#x430;&#x439;&#x43b;&#x430; &#x43f;&#x440;&#x435;&#x432;&#x43e;&#x441;&#x445;&#x43e;&#x434;&#x438;&#x442; &#x43c;&#x430;&#x43a;&#x441;&#x438;&#x43c;&#x430;&#x43b;&#x44c;&#x43d;&#x43e; &#x440;&#x430;&#x437;&#x440;&#x435;&#x448;&#x451;&#x43d;&#x43d;&#x44b;&#x439; &#x43d;&#x430;&#x441;&#x442;&#x440;&#x43e;&#x439;&#x43a;&#x430;&#x43c;&#x438; PHP (%d &#x431;&#x430;&#x439;&#x442;)',
            UPLOAD_ERR_FORM_SIZE  => '&#x420;&#x430;&#x437;&#x43c;&#x435;&#x440; &#x437;&#x430;&#x433;&#x440;&#x443;&#x436;&#x435;&#x43d;&#x43d;&#x43e;&#x433;&#x43e; &#x444;&#x430;&#x439;&#x43b;&#x430; &#x43f;&#x440;&#x435;&#x432;&#x43e;&#x441;&#x445;&#x43e;&#x434;&#x438;&#x442; &#x434;&#x438;&#x440;&#x435;&#x43a;&#x442;&#x438;&#x432;&#x443; MAX_FILE_SIZE, &#x443;&#x43a;&#x430;&#x437;&#x430;&#x43d;&#x43d;&#x443;&#x44e; &#x432; &#x444;&#x43e;&#x440;&#x43c;&#x435; (%d &#x431;&#x430;&#x439;&#x442;)',
            UPLOAD_ERR_PARTIAL    => '&#x424;&#x430;&#x439;&#x43b; &#x431;&#x44b;&#x43b; &#x437;&#x430;&#x433;&#x440;&#x443;&#x436;&#x435;&#x43d; &#x43d;&#x435; &#x43f;&#x43e;&#x43b;&#x43d;&#x43e;&#x441;&#x442;&#x44c;&#x44e;',
            UPLOAD_ERR_NO_TMP_DIR => '&#x41e;&#x448;&#x438;&#x431;&#x43a;&#x430; &#x43d;&#x430; &#x441;&#x435;&#x440;&#x432;&#x435;&#x440;&#x435;: &#x43e;&#x442;&#x441;&#x443;&#x442;&#x441;&#x442;&#x432;&#x443;&#x435;&#x442; &#x43a;&#x430;&#x442;&#x430;&#x43b;&#x43e;&#x433; &#x434;&#x43b;&#x44f; &#x432;&#x440;&#x435;&#x43c;&#x435;&#x43d;&#x43d;&#x44b;&#x445; &#x444;&#x430;&#x439;&#x43b;&#x43e;&#x432;',
            UPLOAD_ERR_CANT_WRITE => '&#x41e;&#x448;&#x438;&#x431;&#x43a;&#x430; &#x43d;&#x430; &#x441;&#x435;&#x440;&#x432;&#x435;&#x440;&#x435;: &#x43d;&#x435; &#x443;&#x434;&#x430;&#x43b;&#x43e;&#x441;&#x44c; &#x437;&#x430;&#x43f;&#x438;&#x441;&#x430;&#x442;&#x44c; &#x444;&#x430;&#x439;&#x43b; &#x43d;&#x430; &#x434;&#x438;&#x441;&#x43a;',
            UPLOAD_ERR_EXTENSION  => '&#x417;&#x430;&#x433;&#x440;&#x443;&#x437;&#x43a;&#x430; &#x444;&#x430;&#x439;&#x43b;&#x430; &#x431;&#x44b;&#x43b;&#x430; &#x43e;&#x441;&#x442;&#x430;&#x43d;&#x43e;&#x432;&#x43b;&#x435;&#x43d;&#x430; &#x440;&#x430;&#x441;&#x448;&#x438;&#x440;&#x435;&#x43d;&#x438;&#x435;&#x43c;'
        )
    );

   /**
    * Language to display error messages in
    * @var  string
    */
    protected $language;

   /**
    * Information on uploaded file, from submit data source
    * @var array
    */
    protected $value = null;

    protected $attributes = array('type' => 'file');


   /**
    * Class constructor
    *
    * Possible keys in $data array are:
    *  - 'language': language to display error messages in, it should either be
    *    already available in the class or provided in 'errorMessages'
    *  - 'errorMessages': an array of error messages with the following format
    *    <pre>
    *      'language code 1' => array(
    *         UPLOAD_ERR_... => 'message',
    *         ...
    *         UPLOAD_ERR_... => 'message'
    *      ),
    *      ...
    *      'language code N' => array(
    *         ...
    *      )
    *    </pre>
    *    Note that error messages for UPLOAD_ERR_INI_SIZE and UPLOAD_ERR_FORM_SIZE
    *    may contain '%d' placeholders that will be automatically replaced by the
    *    appropriate size limits. Note also that you don't need to provide messages
    *    for every possible error code in the arrays, you may e.g. override just
    *    one error message for a particular language.
    *
    * @param    string  Element name
    * @param    mixed   Attributes (either a string or an array)
    * @param    array   Data used to set up error messages for PHP's file
    *                   upload errors.
    */
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        if (isset($data['errorMessages'])) {
            // neither array_merge() nor array_merge_recursive will do
            foreach ($data['errorMessages'] as $lang => $ary) {
                foreach ($ary as $code => $message) {
                    $this->errorMessages[$lang][$code] = $message;
                }
            }
            unset($data['errorMessages']);
        }
        if (!isset($data['language'])) {
            $this->language = self::DEFAULT_LANGUAGE;
        } else {
            $this->language = isset($this->errorMessages[$data['language']])?
                              $data['language']: self::DEFAULT_LANGUAGE;
            unset($data['language']);
        }
        parent::__construct($name, $attributes, $data);
    }


   /**
    * File upload elements cannot be frozen
    *
    * To properly "freeze" a file upload element one has to store the uploaded
    * file somewhere and store the file info in session. This is way outside
    * the scope of this class.
    *
    * @param    bool    Whether element should be frozen or editable. This
    *                   parameter is ignored in case of file uploads
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Returns the information on uploaded file
    *
    * @return   array|null
    */
    public function getValue()
    {
        return $this->value;
    }

   /**
    * File upload's value cannot be set here
    *
    * @param     mixed    Value for file element, this parameter is ignored
    * @return    HTML_QuickForm2_Element_InputFile
    */
    public function setValue($value)
    {
        return $this;
    }

    public function updateValue()
    {
        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $value = $ds->getUpload($this->getName());
                if (null !== $value) {
                    $this->value = $value;
                    return;
                }
            }
        }
        $this->value = null;
    }

   /**
    * Performs the server-side validation
    *
    * Before the Rules added to the element kick in, the element checks the
    * error code added to the $_FILES array by PHP. If the code isn't
    * UPLOAD_ERR_OK or UPLOAD_ERR_NO_FILE then a built-in error message will be
    * displayed and no further validation will take place.
    *
    * @return   boolean     Whether the element is valid
    */
    protected function validate()
    {
        if (strlen($this->error)) {
            return false;
        }
        if (isset($this->value['error']) &&
            !in_array($this->value['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE)))
        {
            if (isset($this->errorMessages[$this->language][$this->value['error']])) {
                $errorMessage = $this->errorMessages[$this->language][$this->value['error']];
            } else {
                $errorMessage = $this->errorMessages[self::DEFAULT_LANGUAGE][$this->value['error']];
            }
            if (UPLOAD_ERR_INI_SIZE == $this->value['error']) {
                $iniSize = ini_get('upload_max_filesize');
                $size    = intval($iniSize);
                switch (strtoupper(substr($iniSize, -1))) {
                    case 'G': $size *= 1024;
                    case 'M': $size *= 1024;
                    case 'K': $size *= 1024;
                }

            } elseif (UPLOAD_ERR_FORM_SIZE == $this->value['error']) {
                foreach ($this->getDataSources() as $ds) {
                    if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                        $size = intval($ds->getValue('MAX_FILE_SIZE'));
                        break;
                    }
                }
            }
            $this->error = isset($size)? sprintf($errorMessage, $size): $errorMessage;
            return false;
        }
        return parent::validate();
    }

    public function addFilter($callback, array $options = null, $recursive = true)
    {
        throw new HTML_QuickForm2_Exception(
            'InputFile elements do not support filters'
        );
    }
}
?>
