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
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: IsCompressed.php 16971 2009-07-22 18:05:45Z mikaelkael $
 */

/**
 * @see Zend_Validate_File_MimeType
 */
require_once 'Zend/Validate/File/MimeType.php';

/**
 * Validator which checks if the file already exists in the directory
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_IsCompressed extends Zend_Validate_File_MimeType
{
    /**
     * @const string Error constants
     */
    const FALSE_TYPE   = 'fileIsCompressedFalseType';
    const NOT_DETECTED = 'fileIsCompressedNotDetected';
    const NOT_READABLE = 'fileIsCompressedNotReadable';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "The file '%value%' is not compressed, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' has not been detected",
        self::NOT_READABLE => "The file '%value%' can not be read"
    );

    /**
     * Sets validator options
     *
     * @param  string|array $compression
     * @return void
     */
    public function __construct($mimetype = array())
    {
        if (empty($mimetype)) {
            $mimetype = array(
                'application/x-tar',
                'application/x-cpio',
                'application/x-debian-package',
                'application/x-archive',
                'application/x-arc',
                'application/x-arj',
                'application/x-lharc',
                'application/x-lha',
                'application/x-rar',
                'application/zip',
                'application/zoo',
                'application/x-eet',
                'application/x-java-pack200',
                'application/x-compress',
                'application/x-gzip',
                'application/x-bzip2'
            );
        }

        $this->setMimeType($mimetype);
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the file is compression with the set compression types
     *
     * @param  string  $value Real file to check for compression
     * @param  array   $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        if ($file !== null) {
            if (class_exists('finfo', false) && defined('MAGIC')) {
                $mime = new finfo(FILEINFO_MIME);
                $this->_type = $mime->file($value);
                unset($mime);
            } elseif (function_exists('mime_content_type') && ini_get('mime_magic.magicfile')) {
                $this->_type = mime_content_type($value);
            } else {
                $this->_type = $file['type'];
            }
        }

        if (empty($this->_type)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $compressions = $this->getMimeType(true);
        if (in_array($this->_type, $compressions)) {
            return true;
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        foreach ($compressions as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_TYPE);
    }
}
