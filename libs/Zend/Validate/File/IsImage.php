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
 * @version   $Id: IsImage.php 18148 2009-09-16 19:27:43Z thomas $
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
        self::FALSE_TYPE   => "The file '%value%' is no image, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' has not been detected",
        self::NOT_READABLE => "The file '%value%' can not be read"
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
        } else if (empty($mimetype)) {
            $mimetype = array(
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
        }

        $this->setMimeType($mimetype);
    }
}
