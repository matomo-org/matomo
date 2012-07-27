<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     1.6.0
 * @package     Slim
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Router
 *
 * This class organizes Route objects and, upon request, will
 * return an iterator for routes that match the HTTP request URI.
 *
 * @package Slim
 * @author  Josh Lockhart
 * @since   1.0.0
 */
class Slim_Router implements IteratorAggregate {
    /**
     * @var Slim_Http_Request
     */
    protected $request;

    /**
     * @var Slim_Http_Response
     */
    protected $response;

    /**
     * @var array Lookup hash of all routes
     */
    protected $routes;

    /**
     * @var array Lookup hash of named routes, keyed by route name
     */
    protected $namedRoutes;

    /**
     * @var array Array of routes that match the Request URI (lazy-loaded)
     */
    protected $matchedRoutes;

    /**
     * @var mixed Callable to be invoked if no matching routes are found
     */
    protected $notFound;

    /**
     * @var mixed Callable to be invoked if application error
     */
    protected $error;

    /**
     * Constructor
     * @param   Slim_Http_Request   $request    The HTTP request object
     * @param   Slim_Http_Response  $response   The HTTP response object
     */
    public function __construct( Slim_Http_Request $request, Slim_Http_Response $response ) {
        $this->request = $request;
        $this->response = $response;
        $this->routes = array();
        $this->namedRoutes = array();
    }

    /**
     * Get Iterator
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->getMatchedRoutes());
    }

    /**
     * Get Request
     * @return Slim_Http_Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Get Response
     * @return Slim_Http_Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Return routes that match the current request
     * @return array[Slim_Route]
     */
    public function getMatchedRoutes( $reload = false ) {
        if ( $reload || is_null($this->matchedRoutes) ) {
            $this->matchedRoutes = array();
            foreach ( $this->routes as $route ) {
                if ( $route->matches($this->request->getResourceUri()) ) {
                    $this->matchedRoutes[] = $route;
                }
            }
        }
        return $this->matchedRoutes;
    }

    /**
     * Map a route to a callback function
     * @param   string      $pattern    The URL pattern (ie. "/books/:id")
     * @param   mixed       $callable   Anything that returns TRUE for is_callable()
     * @return  Slim_Route
     */
    public function map( $pattern, $callable ) {
        $route = new Slim_Route($pattern, $callable);
        $route->setRouter($this);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Get URL for named route
     * @param   string              $name   The name of the route
     * @param   array                       Associative array of URL parameter names and values
     * @throws  RuntimeException            If named route not found
     * @return  string                      The URL for the given route populated with the given parameters
     */
    public function urlFor( $name, $params = array() ) {
        if ( !$this->hasNamedRoute($name) ) {
            throw new RuntimeException('Named route not found for name: ' . $name);
        }
        $pattern = $this->getNamedRoute($name)->getPattern();
        $search = $replace = array();
        foreach ( $params as $key => $value ) {
            $search[] = ':' . $key;
            $replace[] = $value;
        }
        $pattern = str_replace($search, $replace, $pattern);
        //Remove remnants of unpopulated, trailing optional pattern segments
        return preg_replace(array(
            '@\(\/?:.+\/??\)\??@',
            '@\?|\(|\)@'
        ), '', $this->request->getRootUri() . $pattern);
    }

    /**
     * Add named route
     * @param   string              $name   The route name
     * @param   Slim_Route          $route  The route object
     * @throws  RuntimeException            If a named route already exists with the same name
     * @return  void
     */
    public function addNamedRoute( $name, Slim_Route $route ) {
        if ( $this->hasNamedRoute($name) ) {
            throw new RuntimeException('Named route already exists with name: ' . $name);
        }
        $this->namedRoutes[(string)$name] = $route;
    }

    /**
     * Has named route
     * @param   string  $name   The route name
     * @return  bool
     */
    public function hasNamedRoute( $name ) {
        return isset($this->namedRoutes[(string)$name]);
    }

    /**
     * Get named route
     * @param   string  $name
     * @return  Slim_Route|null
     */
    public function getNamedRoute( $name ) {
        if ( $this->hasNamedRoute($name) ) {
            return $this->namedRoutes[(string)$name];
        } else {
            return null;
        }
    }

    /**
     * Get named routes
     * @return ArrayIterator
     */
    public function getNamedRoutes() {
        return new ArrayIterator($this->namedRoutes);
    }

    /**
     * Register a 404 Not Found callback
     * @param   mixed $callable Anything that returns TRUE for is_callable()
     * @return  mixed
     */
    public function notFound( $callable = null ) {
        if ( is_callable($callable) ) {
            $this->notFound = $callable;
        }
        return $this->notFound;
    }

    /**
     * Register a 500 Error callback
     * @param   mixed $callable Anything that returns TRUE for is_callable()
     * @return  mixed
     */
    public function error( $callable = null ) {
        if ( is_callable($callable) ) {
            $this->error = $callable;
        }
        return $this->error;
    }
}