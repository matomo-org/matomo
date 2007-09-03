<?php
/**
 * File containing the ezcUrlNoConfigurationException class
 *
 * @package Mail
 * @version 1.1
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcUrlNoConfigurationException is thrown whenever you try to use a url
 * configuration that is not defined.
 *
 * @package Url
 * @version 1.1
 */
class ezcUrlNoConfigurationException extends ezcUrlException
{
    /**
     * Constructs a new ezcUrlNoConfigurationException.
     *
     * @param string $param
     */
    public function __construct( $param )
    {
        $message = "The parameter '{$param}' could not be set/get because the url doesn't have a configuration defined.";
        parent::__construct( $message );
    }
}
?>
