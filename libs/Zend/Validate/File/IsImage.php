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
 * @version   $Id: IsImage.php 20358 2010-01-17 19:03:49Z thomas $
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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_IsImage extends Zend_Validate_File_MimeType
{
    /**
     * @const string Error constants
     */
    const FALSE_TYPE   = 'fileIsImageFalseType';
    const NOT_DETECTED = 'fileIsImageNotDetected';
    const NOT_READABLE = 'fileIsImageNotReadable';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "File '%value%' is no image, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not been detected",
        self::NOT_READABLE => "File '%value%' can not be read",
    );

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $mimetype
     * @return void
     */
    public function __construct($mimetype = array())
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        }

        $temp    = array();
        $default = array(
            'image/x-quicktime',
            'image/jp2',
            'image/x-xpmi',
            'image/x-portable-bitmap',
            'image/x-portable-greymap',
            'image/x-portable-pixmap',
            'image/x-niff',
            'image/tiff',
            'image/png',
            'image/x-unknown',
            'image/gif',
            'image/x-ms-bmp',
            'application/dicom',
            'image/vnd.adobe.photoshop',
            'image/vnd.djvu',
            'image/x-cpi',
            'image/jpeg',
            'image/x-ico',
            'image/x-coreldraw',
            'image/svg+xml'
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
