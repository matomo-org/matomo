<?php
/**
 * File containing the ezcUrlException class
 *
 * @package Mail
 * @version 1.1
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * ezcUrlExceptions are thrown when an exceptional state
 * occures in the Url package.
 *
 * @package Url
 * @version 1.1
 */
class ezcUrlException extends ezcBaseException
{
    /**
     * Constructs a new ezcUrlException with error message $message.
     *
     * @param string $message
     */
    public function __construct( $message )
    {
        parent::__construct( $message );
    }
}
?>
