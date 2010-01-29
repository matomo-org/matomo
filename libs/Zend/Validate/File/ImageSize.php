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
 * @version   $Id: ImageSize.php 20358 2010-01-17 19:03:49Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the image size of a image file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_ImageSize extends Zend_Validate_Abstract
{
    /**
     * @const string Error constants
     */
    const WIDTH_TOO_BIG    = 'fileImageSizeWidthTooBig';
    const WIDTH_TOO_SMALL  = 'fileImageSizeWidthTooSmall';
    const HEIGHT_TOO_BIG   = 'fileImageSizeHeightTooBig';
    const HEIGHT_TOO_SMALL = 'fileImageSizeHeightTooSmall';
    const NOT_DETECTED     = 'fileImageSizeNotDetected';
    const NOT_READABLE     = 'fileImageSizeNotReadable';

    /**
     * @var array Error message template
     */
    protected $_messageTemplates = array(
        self::WIDTH_TOO_BIG    => "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected",
        self::WIDTH_TOO_SMALL  => "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected",
        self::HEIGHT_TOO_BIG   => "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected",
        self::HEIGHT_TOO_SMALL => "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected",
        self::NOT_DETECTED     => "The size of image '%value%' could not be detected",
        self::NOT_READABLE     => "File '%value%' can not be read",
    );

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = array(
        'minwidth'  => '_minwidth',
        'maxwidth'  => '_maxwidth',
        'minheight' => '_minheight',
        'maxheight' => '_maxheight',
        'width'     => '_width',
        'height'    => '_height'
    );

    /**
     * Minimum image width
     *
     * @var integer
     */
    protected $_minwidth;

    /**
     * Maximum image width
     *
     * @var integer
     */
    protected $_maxwidth;

    /**
     * Minimum image height
     *
     * @var integer
     */
    protected $_minheight;

    /**
     * Maximum image height
     *
     * @var integer
     */
    protected $_maxheight;

    /**
     * Detected width
     *
     * @var integer
     */
    protected $_width;

    /**
     * Detected height
     *
     * @var integer
     */
    protected $_height;

    /**
     * Sets validator options
     *
     * Accepts the following option keys:
     * - minheight
     * - minwidth
     * - maxheight
     * - maxwidth
     *
     * @param  Zend_Config|array $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (1 < func_num_args()) {
            if (!is_array($options)) {
                $options = array('minwidth' => $options);
            }
            $argv = func_get_args();
            array_shift($argv);
            $options['minheight'] = array_shift($argv);
            if (!empty($argv)) {
                $options['maxwidth'] = array_shift($argv);
                if (!empty($argv)) {
                    $options['maxheight'] = array_shift($argv);
                }
            }
        } else if (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        if (isset($options['minheight']) || isset($options['minwidth'])) {
            $this->setImageMin($options);
        }

        if (isset($options['maxheight']) || isset($options['maxwidth'])) {
            $this->setImageMax($options);
        }
    }

    /**
     * Returns the set minimum image sizes
     *
     * @return array
     */
    public function getImageMin()
    {
        return array('minwidth' => $this->_minwidth, 'minheight' => $this->_minheight);
    }

    /**
     * Returns the set maximum image sizes
     *
     * @return array
     */
    public function getImageMax()
    {
        return array('maxwidth' => $this->_maxwidth, 'maxheight' => $this->_maxheight);
    }

    /**
     * Returns the set image width sizes
     *
     * @return array
     */
    public function getImageWidth()
    {
        return array('minwidth' => $this->_minwidth, 'maxwidth' => $this->_maxwidth);
    }

    /**
     * Returns the set image height sizes
     *
     * @return array
     */
    public function getImageHeight()
    {
        return array('minheight' => $this->_minheight, 'maxheight' => $this->_maxheight);
    }

    /**
     * Sets the minimum image size
     *
     * @param  array $options               The minimum image dimensions
     * @throws Zend_Validate_Exception      When minwidth is greater than maxwidth
     * @throws Zend_Validate_Exception      When minheight is greater than maxheight
     * @return Zend_Validate_File_ImageSize Provides a fluent interface
     */
    public function setImageMin($options)
    {
        if (isset($options['minwidth'])) {
            if (($this->_maxwidth !== null) and ($options['minwidth'] > $this->_maxwidth)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The minimum image width must be less than or equal to the "
                    . " maximum image width, but {$options['minwidth']} > {$this->_maxwidth}");
            }
        }

        if (isset($options['maxheight'])) {
            if (($this->_maxheight !== null) and ($options['minheight'] > $this->_maxheight)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The minimum image height must be less than or equal to the "
                    . " maximum image height, but {$options['minheight']} > {$this->_maxheight}");
            }
        }

        if (isset($options['minwidth'])) {
            $this->_minwidth  = (int) $options['minwidth'];
        }

        if (isset($options['minheight'])) {
            $this->_minheight = (int) $options['minheight'];
        }

        return $this;
    }

    /**
     * Sets the maximum image size
     *
     * @param  array $options          The maximum image dimensions
     * @throws Zend_Validate_Exception When maxwidth is smaller than minwidth
     * @throws Zend_Validate_Exception When maxheight is smaller than minheight
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setImageMax($options)
    {
        if (isset($options['maxwidth'])) {
            if (($this->_minwidth !== null) and ($options['maxwidth'] < $this->_minwidth)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The maximum image width must be greater than or equal to the "
                    . "minimum image width, but {$options['maxwidth']} < {$this->_minwidth}");
            }
        }

        if (isset($options['maxheight'])) {
            if (($this->_minheight !== null) and ($options['maxheight'] < $this->_minheight)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The maximum image height must be greater than or equal to the "
                    . "minimum image height, but {$options['maxheight']} < {$this->_minwidth}");
            }
        }

        if (isset($options['maxwidth'])) {
            $this->_maxwidth  = (int) $options['maxwidth'];
        }

        if (isset($options['maxheight'])) {
            $this->_maxheight = (int) $options['maxheight'];
        }

        return $this;
    }

    /**
     * Sets the mimimum and maximum image width
     *
     * @param  array $options               The image width dimensions
     * @return Zend_Validate_File_ImageSize Provides a fluent interface
     */
    public function setImageWidth($options)
    {
        $this->setImageMin($options);
        $this->setImageMax($options);

        return $this;
    }

    /**
     * Sets the mimimum and maximum image height
     *
     * @param  array $options               The image height dimensions
     * @return Zend_Validate_File_ImageSize Provides a fluent interface
     */
    public function setImageHeight($options)
    {
        $this->setImageMin($options);
        $this->setImageMax($options);

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the imagesize of $value is at least min and
     * not bigger than max
     *
     * @param  string $value Real file to check for image size
     * @param  array  $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        $size = @getimagesize($value);
        $this->_setValue($file);

        if (empty($size) or ($size[0] === 0) or ($size[1] === 0)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $this->_width  = $size[0];
        $this->_height = $size[1];
        if ($this->_width < $this->_minwidth) {
            $this->_throw($file, self::WIDTH_TOO_SMALL);
        }

        if (($this->_maxwidth !== null) and ($this->_maxwidth < $this->_width)) {
            $this->_throw($file, self::WIDTH_TOO_BIG);
        }

        if ($this->_height < $this->_minheight) {
            $this->_throw($file, self::HEIGHT_TOO_SMALL);
        }

        if (($this->_maxheight !== null) and ($this->_maxheight < $this->_height)) {
            $this->_throw($file, self::HEIGHT_TOO_BIG);
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
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
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}
