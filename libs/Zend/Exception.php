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
 * @package    Zend
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    /**
     * @category   Zend
     * @package    Zend
     * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     * @license    http://framework.zend.com/license/new-bsd     New BSD License
     */
    class Zend_Exception extends Exception
    {
        /**
         * @var null|Exception
         */
        private $_previous = null;

        /**
         * Construct the exception
         *
         * @param  string $msg
         * @param  int $code
         * @param  Exception $previous
         * @return void
         */
        public function __construct($msg = '', $code = 0, Exception $previous = null)
        {
            parent::__construct($msg, (int) $code);
            $this->_previous = $previous;
        }

        /**
         * Returns previous Exception
         *
         * @return Exception|null
         */
        final public function getPrevious()
        {
            return $this->_previous;
        }

        /**
         * String representation of the exception
         *
         * @return string
         */
        public function __toString()
        {
            if (null !== ($e = $this->getPrevious())) {
                return $e->__toString() 
                    . "\n\nNext " 
                    . parent::__toString();
            }
            return parent::__toString();
        }
    }
} else {
    /**
     * @category   Zend
     * @package    Zend
     * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     * @license    http://framework.zend.com/license/new-bsd     New BSD License
     */
    class Zend_Exception extends Exception
    {
        public function __construct($msg = '', $code = 0, Exception $previous = null)
        {
            if (!is_int($code)) {
                $code = (int) $code;
            }
            parent::__construct($msg, $code, $previous);
        }
    }
}
