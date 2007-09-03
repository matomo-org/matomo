<?php
/**
 * File containing the ezcUrl class.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1
 * @filesource
 * @package Url
 */

/**
 * ezcUrl stores an URL both absolute and relative and contains methods to
 * retrieve the various parts of the URL and to manipulate them.
 *
 * Example of use:
 * <code>
 * // create an ezcUrlConfiguration object
 * $urlCfg = new ezcUrlConfiguration();
 * // set the basedir and script values
 * $urlCfg->basedir = 'mydir';
 * $urlCfg->script = 'index.php';
 *
 * // define delimiters for unordered parameter names
 * $urlCfg->unorderedDelimiters = array( '(', ')' );
 *
 * // define ordered parameters
 * $urlCfg->addOrderedParameter( 'section' );
 * $urlCfg->addOrderedParameter( 'group' );
 * $urlCfg->addOrderedParameter( 'category' );
 * $urlCfg->addOrderedParameter( 'subcategory' );
 *
 * // define unordered parameters
 * $urlCfg->addUnorderedParameter( 'game', ezcUrlConfiguration::MULTIPLE_ARGUMENTS );
 *
 * // create a new ezcUrl object from a string URL and use the above $urlCfg
 * $url = new ezcUrl( 'http://www.example.com/mydir/index.php/groups/Games/Adventure/Adult/(game)/Larry/7', $urlCfg );
 *
 * // to get the parameter values from the URL use $url->getParam():
 * $section =  $url->getParam( 'section' ); // will be "groups"
 * $group = $url->getParam( 'group' ); // will be "Games"
 * $category = $url->getParam( 'category' ); // will be "Adventure"
 * $subcategory = $url->getParam( 'subcategory' ); // will be "Adult"
 * $game = $url->getParam( 'game' ); // will be array( "Larry", "7" )
 * </code>
 *
 * @property string $host
 *           Hostname or null
 * @property string $path
 *           Complete path as an array.
 * @property string $user
 *           User or null.
 * @property string $pass
 *           Password or null.
 * @property string $port
 *           Port or null.
 * @property string $scheme
 *           Protocol or null.
 * @property string $query
 *           Complete query string as an associative array.
 * @property string $fragment
 *           Anchor or null.
 * @property string $basedir
 *           Base directory or null.
 * @property string $script
 *           Script name or null.
 * @property string $params
 *           Complete ordered parameters as array.
 * @property string $uparams
 *           Complete unordered parameters as associative array.
 * @property ezcUrlConfiguration $configuration
 *           The URL configuration defined for this URL, or null.
 *
 * @package Url
 * @version 1.1
 * @mainclass
 */
class ezcUrl
{
    /**
     * Holds the properties of this class.
     *
     * @var array(string=>mixed)
     */
    private $properties = array();

    /**
     * Constructs a new ezcUrl object from the string $url.
     *
     * If the $configuration parameter is provided, then it will apply the
     * configuration to the URL by calling {@link applyConfiguration()}.
     *
     * @param string $url A string URL from which to construct the URL object
     * @param ezcUrlConfiguration $configuration An optional URL configuration used when parsing and building the URL
     */
    public function __construct( $url = null, ezcUrlConfiguration $configuration = null )
    {
        $this->parseUrl( $url );
        $this->configuration = $configuration;
        if ( $configuration != null )
        {
            $this->applyConfiguration( $configuration );
        }
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name does not exist
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name The name of the property to set
     * @param mixed $value The new value of the property
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'host':
            case 'path':
            case 'user':
            case 'pass':
            case 'port':
            case 'scheme':
            case 'fragment':
            case 'query':
            case 'basedir':
            case 'script':
            case 'params':
            case 'uparams':
                $this->properties[$name] = $value;
                break;

            case 'configuration':
                if ( $value === null || $value instanceof ezcUrlConfiguration )
                {
                    $this->properties[$name] = $value;
                }
                else
                {
                    throw new ezcBaseValueException( $name, $value, 'instance of ezcUrlConfiguration' );
                }
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $name );
                break;
        }
    }

    /**
     * Returns the property $name.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name does not exist
     * @param string $name The name of the property for which to return the value
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'host':
            case 'path':
            case 'user':
            case 'pass':
            case 'port':
            case 'scheme':
            case 'fragment':
            case 'query':
            case 'basedir':
            case 'script':
            case 'params':
            case 'uparams':
            case 'configuration':
                return $this->properties[$name];

            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name The name of the property to test if it is set
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        switch ( $name )
        {
            case 'host':
            case 'path':
            case 'user':
            case 'pass':
            case 'port':
            case 'scheme':
            case 'fragment':
            case 'query':
            case 'basedir':
            case 'script':
            case 'params':
            case 'uparams':
            case 'configuration':
                return isset( $this->properties[$name] );

            default:
                return false;
        }
    }

    /**
     * Returns this URL as a string by calling {@link buildUrl()}.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->buildUrl();
    }

    /**
     * Parses the string $url and sets the class properties.
     *
     * @param string $url A string URL to parse
     */
    private function parseUrl( $url = null )
    {
        $urlArray = parse_url( $url );

        $this->properties['host'] = isset( $urlArray['host'] ) ? $urlArray['host'] : null;
        $this->properties['user'] = isset( $urlArray['user'] ) ? $urlArray['user'] : null;
        $this->properties['pass'] = isset( $urlArray['pass'] ) ? $urlArray['pass'] : null;
        $this->properties['port'] = isset( $urlArray['port'] ) ? $urlArray['port'] : null;
        $this->properties['scheme'] = isset( $urlArray['scheme'] ) ? $urlArray['scheme'] : null;
        $this->properties['fragment'] = isset( $urlArray['fragment'] ) ? $urlArray['fragment'] : null;
        $this->properties['path'] = isset( $urlArray['path'] ) ? explode( '/', trim( $urlArray['path'], '/' ) ) : array();

        $this->properties['basedir'] = array();
        $this->properties['script'] = array();
        $this->properties['params'] = array();
        $this->properties['uparams'] = array();

        if ( isset( $urlArray['query'] ) )
        {
            parse_str( $urlArray['query'] , $this->properties['query'] );
        }
        else
        {
            $this->properties['query'] = array();
        }
    }

    /**
     * Applies the URL configuration $configuration to the current url.
     *
     * It fills the arrays $basedir, $script, $params and $uparams with values
     * from $path.
     *
     * It also sets the property configuration to the value of $configuration.
     *
     * @param ezcUrlConfiguration $configuration An URL configuration used in parsing
     */
    public function applyConfiguration( ezcUrlConfiguration $configuration )
    {
        $this->configuration = $configuration;
        $this->basedir = $this->parsePathElement( $configuration->basedir, 0 );
        $this->script = $this->parsePathElement( $configuration->script, count( $this->basedir ) );
        $this->params = $this->parseOrderedParameters( $configuration->orderedParameters, count( $this->basedir ) + count( $this->script ) );
        $this->uparams = $this->parseUnorderedParameters( $configuration->unorderedParameters, count( $this->basedir ) + count( $this->script ) + count( $this->params ) );
    }

    /**
     * Parses $path based on the configuration $config, starting from $index.
     *
     * Returns the first few elements of $this->path matching $config,
     * starting from $index.
     *
     * @param string $config A string which will be matched against the path part of the URL
     * @param int $index The index in the URL path part from where to start the matching of $config
     * @return array(string=>mixed)
     */
    private function parsePathElement( $config, $index )
    {
        $config = trim( $config, '/' );
        $paramParts = explode( '/', $config );
        $pathElement = array();
        foreach ( $paramParts as $part )
        {
            if ( isset( $this->path[$index] ) && $part == $this->path[$index] )
            {
                $pathElement[] = $part;
            }
            $index++;
        }
        return $pathElement;
    }

    /**
     * Returns ordered parameters from the $path array.
     *
     * @param array(string) $config An array of ordered parameters names, from the URL configuration used in parsing
     * @param int $index The index in the URL path part from where to start the matching of $config
     * @return array(string=>mixed)
     */
    public function parseOrderedParameters( $config, $index )
    {
        $result = array();
        $pathCount = count( $this->path );
        for ( $i = 0; $i < count( $config ); $i++ )
        {
            if ( isset( $this->path[$index + $i] ) )
            {
                $result[] = $this->path[$index + $i];
            }
            else
            {
                $result[] = null;
            }
        }
        return $result;
    }

    /**
     * Returns unordered parameters from the $path array.
     *
     * @param array(string) $config An array of unordered parameters names, from the URL configuration used in parsing
     * @param int $index The index in the URL path part from where to start the matching of $config
     * @return array(string=>mixed)
     */
    public function parseUnorderedParameters( $config, $index )
    {
        $result = array();
        $pathCount = count( $this->path );
        if ( $pathCount == 0 || ( $pathCount == 1 && trim( $this->path[0] ) === "" ) )
        {
            // special case: a bug? in parse_url() which makes $this->path
            // be array( "" ) if the provided URL is null or empty
            return $result;
        }
        for ( $i = $index; $i < $pathCount; $i++ )
        {
            $param = $this->path[$i];
            if ( $param{0} == $this->configuration->unorderedDelimiters[0] )
            {
                $param = trim( trim( $param, $this->configuration->unorderedDelimiters[0] ), $this->configuration->unorderedDelimiters[1] );
                $result[$param] = array();
                $j = 1;
                while ( ( $i + $j ) < $pathCount && $this->path[$i + $j]{0} != $this->configuration->unorderedDelimiters[0] )
                {
                    $result[$param][] = trim( trim( $this->path[$i + $j], $this->configuration->unorderedDelimiters[0] ), $this->configuration->unorderedDelimiters[1] );
                    $j++;
                }
            }
        }
        return $result;
    }

    /**
     * Returns this URL as a string.
     *
     * The query part of the URL is build with http_build_query() which
     * encodes the query in a similar way to urlencode().
     *
     * @return string
     */
    public function buildUrl()
    {
        $url = '';

        if ( $this->scheme )
        {
            $url .= $this->scheme . '://';
        }

        if ( $this->host )
        {
            if ( $this->user )
            {
                $url .= $this->user;
                if ( $this->pass )
                {
                    $url .= ':' . $this->pass;
                }
                $url .= '@';
            }

            $url .= $this->host;
            if ( $this->port )
            {
                $url .= ':' . $this->port;
            }
        }

        if ( $this->configuration != null )
        {
            if ( $this->basedir )
            {
                if ( !( count( $this->basedir ) == 0 || trim( $this->basedir[0] ) === "" ) )
                {
                    $url .= '/' . implode( '/', $this->basedir );
                }
            }

            if ( $this->params && count( $this->params ) != 0 )
            {
                $url .= '/' . implode( '/', $this->params );
            }

            if ( $this->uparams && count( $this->uparams ) != 0 )
            {
                foreach ( $this->properties['uparams'] as $key => $values )
                {
                    $url .= '/(' . $key . ')/' . implode( '/', $values );
                }
            }
        }
        else
        {
            if ( $this->path )
            {
                $url .= '/' . implode( '/', $this->path );
            }
        }

        if ( $this->query )
        {
            $url .= '?' . http_build_query( $this->query );
        }

        if ( $this->fragment )
        {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Returns true if this URL is relative and false if the URL is absolute.
     *
     * @return bool
     */
    public function isRelative()
    {
        if ( $this->host === null || $this->host == '' )
        {
            return true;
        }
        return false;
    }

    /**
     * Returns the specified parameter from the URL based on the URL configuration.
     *
     * @throws ezcUrlNoConfigurationException
     *         if an URL configuration is not defined
     * @throws ezcUrlInvalidParameterException
     *         if the specified parameter is not defined in the URL configuration
     * @param string $name The name of the parameter for which to return the value
     * @return mixed
     */
    public function getParam( $name )
    {
        if ( $this->configuration != null )
        {
            if ( !( isset( $this->configuration->orderedParameters[$name] ) ||
                    isset( $this->configuration->unorderedParameters[$name] ) ) )
            {
                throw new ezcUrlInvalidParameterException( $name );
            }

            $params = $this->params;
            $uparams = $this->uparams;
            if ( isset( $this->configuration->orderedParameters[$name] ) &&
                 isset( $params[$this->configuration->orderedParameters[$name]] ) )
            {
                return $params[$this->configuration->orderedParameters[$name]];
            }

            if ( isset( $this->configuration->unorderedParameters[$name] ) &&
                 isset( $uparams[$name] ) )
            {
                if ( $this->configuration->unorderedParameters[$name] == ezcUrlConfiguration::SINGLE_ARGUMENT )
                {
                    if ( count( $uparams[$name] ) > 0 )
                    {
                        return $uparams[$name][0];
                    }
                }
                else
                {
                    return $uparams[$name];
                }
            }
            return null;
        }
        throw new ezcUrlNoConfigurationException( $name );
    }

    /**
     * Sets the specified parameter in the URL based on the URL configuration.
     *
     * @throws ezcUrlNoConfigurationException
     *         if an URL configuration is not defined
     * @throws ezcUrlInvalidParameterException
     *         if the specified parameter is not defined in the URL configuration
     * @param string $name The name of the parameter to set
     * @param string $value The new value of the parameter
     */
    public function setParam( $name, $value )
    {
        if ( $this->configuration != null )
        {
            if ( !( isset( $this->configuration->orderedParameters[$name] ) ||
                    isset( $this->configuration->unorderedParameters[$name] ) ) )
            {
                throw new ezcUrlInvalidParameterException( $name );
            }

            if ( isset( $this->configuration->orderedParameters[$name] ) )
            {
                $this->properties['params'][$this->configuration->orderedParameters[$name]] = $value;
                return;
            }
            if ( isset( $this->configuration->unorderedParameters[$name] ) )
            {
                if ( is_array( $value ) )
                {
                    $this->properties['uparams'][$name] = $value;
                }
                else
                {
                    $this->properties['uparams'][$name] = array( $value );
                }
            }
            return;
        }
        throw new ezcUrlNoConfigurationException( $name );
    }

    /**
     * Returns the query elements as an associative array.
     *
     * Example:
     * for 'http://www.example.com/mydir/shop?content=view&products=10'
     * returns array( 'content' => 'view', 'products' => '10' )
     *
     * @return array(string=>mixed)
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the query elements using the associative array provided.
     *
     * Example:
     * for 'http://www.example.com/mydir/shop'
     * and $query = array( 'content' => 'view', 'products' => '10' )
     * then 'http://www.example.com/mydir/shop?content=view&products=10'
     *
     * @param array(string=>mixed) $query The new value of the query part
     */
    public function setQuery( $query )
    {
        $this->query = $query;
    }
}
?>
