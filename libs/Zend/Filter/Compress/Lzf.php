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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Lzf.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_Compress_CompressInterface
 */
// require_once 'Zend/Filter/Compress/CompressInterface.php';

/**
 * Compression adapter for Lzf
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Compress_Lzf implements Zend_Filter_Compress_CompressInterface
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('lzf')) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This filter needs the lzf extension');
        }
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     */
    public function compress($content)
    {
        $compressed = lzf_compress($content);
        if (!$compressed) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Error during compression');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     */
    public function decompress($content)
    {
        $compressed = lzf_decompress($content);
        if (!$compressed) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Error during compression');
        }

        return $compressed;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Lzf';
    }
}
