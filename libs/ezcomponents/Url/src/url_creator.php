<?php
/**
 * File containing the ezcUrlCreator class.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1
 * @filesource
 * @package Url
 */

/**
 * ezcUrlCreator makes it easy to create urls from scratch.
 *
 * Holds a list of urls mapped to aliases. The aliases are used to refer to the
 * urls stored, so the urls will not be hardcoded all over the application code.
 *
 * Example of use:
 * <code>
 * // register an URL under the alias 'map'
 * ezcUrlCreator::registerUrl( 'map', '/images/geo/%s?xsize=%d&ysize=%d&zoom=%d' );
 *
 * // retrieve the stored URL under the alias 'map' formatted with parameters
 * $url = ezcUrlCreator::getUrl( 'map', 'map_norway.gif', 450, 450, 4 );
 *      // will be: "/images/geo/map_norway.gif?xsize=450&ysize=450&zoom=4"
 *
 * // retrieve the stored URL under the alias 'map' formatted with other parameters
 * $url = ezcUrlCreator::getUrl( 'map', 'map_sweden.gif', 450, 450, 4 );
 *      // will be: "/images/geo/map_sweden.gif?xsize=450&ysize=450&zoom=4"
 * </code>
 *
 * @package Url
 * @version 1.1
 */
class ezcUrlCreator
{
    /**
     * Holds the registered urls.
     *
     * @var array(string=>string)
     */
    private static $urls = array();

    /**
     * Registers $url as $name in the URLs list.
     *
     * If $name is already registered, it will be overwritten.
     *
     * @param string $name The name associated with the URL
     * @param string $url The URL to register
     */
    public static function registerUrl( $name, $url )
    {
        self::$urls[$name] = $url;
    }

    /**
     * Returns the URL registerd as $name prepended to $suffix.
     *
     * Example:
     * <code>
     * ezcUrlCreator::registerUrl( 'map', '/images/geo?xsize=450&ysize=450&zoom=4' );
     * echo ezcUrlCreator::prependUrl( 'map', 'map_sweden.gif' );
     * </code>
     * will output:
     * /images/geo/map_sweden.gif?xsize=450&ysize=450&zoom=4
     *
     * @throws ezcUrlNotRegisteredException
     *         if $name is not registered
     * @param string $name The name associated with the URL that will be appended with $suffix
     * @param string $suffix The string which will be appended to the URL
     * @return string
     */
    public static function prependUrl( $name, $suffix )
    {
        if ( !isset( self::$urls[$name] ) )
        {
            throw new ezcUrlNotRegisteredException( $name );
        }

        $url = new ezcUrl( self::$urls[$name] );
        $url->path = array_merge( $url->path, explode( '/', $suffix ) );
        return $url->buildUrl();
    }

    /**
     * Returns the URL registered as $name.
     *
     * This function accepts a variable number of arguments like the sprintf()
     * function. If you specify more than 1 arguments when calling this
     * function, the registered URL will be formatted using those arguments
     * similar with the sprintf() function.
     * Example:
     * <code>
     * ezcUrlCreator::registerUrl( 'map', '/images/geo/%s?xsize=%d&ysize=%d&zoom=%d' );
     * echo ezcUrlCreator::getUrl( 'map', 'map_sweden.gif', 450, 450, 4 );
     * </code>
     * will output:
     * /images/geo/map_sweden.gif?xsize=450&ysize=450&zoom=4
     *
     * @throws ezcUrlNotRegisteredException
     *         if $name is not registered
     * @param string $name The name associated with the URL
     * @param mixed $args,... Optional values which will be vsprintf-ed in the URL
     * @return string
     */
    public static function getUrl( $name )
    {
        if ( !isset( self::$urls[$name] ) )
        {
            throw new ezcUrlNotRegisteredException( $name );
        }

        if ( func_num_args() > 1 )
        {
            $args = func_get_args();
            // get rid of the first argument ($name)
            unset( $args[0] );
            return vsprintf( self::$urls[$name], $args );
        }
        return self::$urls[$name];
    }
}
?>
