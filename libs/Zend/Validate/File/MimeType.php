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
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: MimeType.php 20505 2010-01-21 21:40:23Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the mime type of a file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_MimeType extends Zend_Validate_Abstract
{
    /**#@+
     * @const Error type constants
     */
    const FALSE_TYPE   = 'fileMimeTypeFalse';
    const NOT_DETECTED = 'fileMimeTypeNotDetected';
    const NOT_READABLE = 'fileMimeTypeNotReadable';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "File '%value%' has a false mimetype of '%type%'",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not be detected",
        self::NOT_READABLE => "File '%value%' can not be read",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'type' => '_type'
    );

    /**
     * @var string
     */
    protected $_type;

    /**
     * Mimetypes
     *
     * If null, there is no mimetype
     *
     * @var string|null
     */
    protected $_mimetype;

    /**
     * Magicfile to use
     *
     * @var string|null
     */
    protected $_magicfile;

    /**
     * If no $_ENV['MAGIC'] is set, try and autodiscover it based on common locations
     * @var array
     */
    protected $_magicFiles = array(
        '/usr/share/misc/magic',
        '/usr/share/misc/magic.mime',
        '/usr/share/misc/magic.mgc',
        '/usr/share/mime/magic',
        '/usr/share/mime/magic.mime',
        '/usr/share/mime/magic.mgc',
        '/usr/share/file/magic',
        '/usr/share/file/magic.mime',
        '/usr/share/file/magic.mgc',
    );

    /**
     * Option to allow header check
     *
     * @var boolean
     */
    protected $_headerCheck = false;

    /**
     * Sets validator options
     *
     * Mimetype to accept
     *
     * @param  string|array $mimetype MimeType
     * @return void
     */
    public function __construct($mimetype)
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        } elseif (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            $this->setMagicFile($mimetype['magicfile']);
            unset($mimetype['magicfile']);
        }

        if (isset($mimetype['headerCheck'])) {
            $this->enableHeaderCheck($mimetype['headerCheck']);
            unset($mimetype['headerCheck']);
        }

        $this->setMimeType($mimetype);
    }

    /**
     * Returns the actual set magicfile
     *
     * @return string
     */
    public function getMagicFile()
    {
        if (null === $this->_magicfile) {
            if (!empty($_ENV['MAGIC'])) {
                $this->setMagicFile($_ENV['MAGIC']);
            } elseif (!(@ini_get("safe_mode") == 'On' || @ini_get("safe_mode") === 1)) {
                foreach ($this->_magicFiles as $file) {
                    // supressing errors which are thrown due to openbase_dir restrictions
                    if (@file_exists($file)) {
                        $this->setMagicFile($file);
                        break;
                    }
                }
            }
        }

        return $this->_magicfile;
    }

    /**
     * Sets the magicfile to use
     * if null, the MAGIC constant from php is used
     *
     * @param  string $file
     * @return Zend_Validate_File_MimeType Provides fluid interface
     */
    public function setMagicFile($file)
    {
        if (empty($file)) {
            $this->_magicfile = null;
        } else if (!is_readable($file)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The given magicfile can not be read');
        } else {
            $this->_magicfile = (string) $file;
        }

        return $this;
    }

    /**
     * Returns the Header Check option
     *
     * @return boolean
     */
    public function getHeaderCheck()
    {
        return $this->_headerCheck;
    }

    /**
     * Defines if the http header should be used
     * Note that this is unsave and therefor the default value is false
     *
     * @param  boolean $checkHeader
     * @return Zend_Validate_File_MimeType Provides fluid interface
     */
    public function enableHeaderCheck($headerCheck = true)
    {
        $this->_headerCheck = (boolean) $headerCheck;
        return $this;
    }

    /**
     * Returns the set mimetypes
     *
     * @param  boolean $asArray Returns the values as array, when false an concated string is returned
     * @return string|array
     */
    public function getMimeType($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $mimetype = (string) $this->_mimetype;
        if ($asArray) {
            $mimetype = explode(',', $mimetype);
        }

        return $mimetype;
    }

    /**
     * Sets the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to validate
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setMimeType($mimetype)
    {
        $this->_mimetype = null;
        $this->addMimeType($mimetype);
        return $this;
    }

    /**
     * Adds the mimetypes
     *
     * @param  string|array $mimetype The mimetypes to add for validation
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function addMimeType($mimetype)
    {
        $mimetypes = $this->getMimeType(true);

        if (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            unset($mimetype['magicfile']);
        }

        foreach ($mimetype as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }
            $mimetypes[] = trim($content);
        }
        $mimetypes = array_unique($mimetypes);

        // Sanity check to ensure no empty values
        foreach ($mimetypes as $key => $mt) {
            if (empty($mt)) {
                unset($mimetypes[$key]);
            }
        }

        $this->_mimetype = implode(',', $mimetypes);

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if the mimetype of the file matches the given ones. Also parts
     * of mimetypes can be checked. If you give for example "image" all image
     * mime types will be accepted like "image/gif", "image/jpeg" and so on.
     *
     * @param  string $value Real file to check for mimetype
     * @param  array  $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if ($file === null) {
            $file = array(
                'type' => null,
                'name' => $value
            );
        }

        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        $mimefile = $this->getMagicFile();
        if (class_exists('finfo', false)) {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            if (!empty($mimefile)) {
                $mime = new finfo($const, $mimefile);
            } else {
                $mime = new finfo($const);
            }

            if ($mime !== false) {
                $this->_type = $mime->file($value);
            }
            unset($mime);
        }

        if (empty($this->_type) &&
            (function_exists('mime_content_type') && ini_get('mime_magic.magicfile'))) {
                $this->_type = mime_content_type($value);
        }

        if (empty($this->_type) && $this->_headerCheck) {
            $this->_type = $file['type'];
        }

        if (empty($this->_type)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->_type, $mimetype)) {
            return true;
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        foreach($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_TYPE);
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        $this->_value = $file['name'];
        $this->_error($errorType);
        return false;
    }
}
