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
 * @version   $Id: IsCompressed.php 22697 2010-07-26 21:14:47Z alexander $
 */

/**
 * @see Zend_Validate_File_MimeType
 */
// require_once 'Zend/Validate/File/MimeType.php';

/**
 * Validator which checks if the file already exists in the directory
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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
        self::FALSE_TYPE   => "File '%value%' is not compressed, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not been detected",
        self::NOT_READABLE => "File '%value%' can not be read",
    );

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $compression
     * @return void
     */
    public function __construct($mimetype = array())
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        }

        $temp    = array();
        // http://de.wikipedia.org/wiki/Liste_von_Dateiendungen
            $default = array(
            'application/arj',
            'application/gnutar',
            'application/lha',
            'application/lzx',
            'application/vnd.ms-cab-compressed',
            'application/x-ace-compressed',
            'application/x-arc',
            'application/x-archive',
            'application/x-arj',
            'application/x-bzip',
            'application/x-bzip2',
            'application/x-cab-compressed',
            'application/x-compress',
            'application/x-compressed',
            'application/x-cpio',
            'application/x-debian-package',
            'application/x-eet',
            'application/x-gzip',
            'application/x-java-pack200',
            'application/x-lha',
            'application/x-lharc',
            'application/x-lzh',
            'application/x-lzma',
            'application/x-lzx',
            'application/x-rar',
            'application/x-sit',
            'application/x-stuffit',
            'application/x-tar',
            'application/zip',
            'application/zoo',
            'multipart/x-gzip',
        );

        if (is_array($mimetype)) {
            $temp = $mimetype;
            if (array_key_exists('magicfile', $temp)) {
                unset($temp['magicfile']);
            }

            if (array_key_exists('headerCheck', $temp)) {
                unset($temp['headerCheck']);
            }

            if (empty($temp)) {
                $mimetype += $default;
            }
        }

        if (empty($mimetype)) {
            $mimetype = $default;
        }

        parent::__construct($mimetype);
    }

    /**
     * Throws an error of the given type
     * Duplicates parent method due to OOP Problem with late static binding in PHP 5.2
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        $this->_value = $file['name'];
        switch($errorType) {
            case Zend_Validate_File_MimeType::FALSE_TYPE :
                $errorType = self::FALSE_TYPE;
                break;
            case Zend_Validate_File_MimeType::NOT_DETECTED :
                $errorType = self::NOT_DETECTED;
                break;
            case Zend_Validate_File_MimeType::NOT_READABLE :
                $errorType = self::NOT_READABLE;
                break;
        }

        $this->_error($errorType);
        return false;
    }
}
