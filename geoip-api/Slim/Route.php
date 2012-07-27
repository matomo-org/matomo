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
 * Route
 * @package Slim
 * @author  Josh Lockhart
 * @since   1.0.0
 */
class Slim_Route {
    /**
     * @var string The route pattern (ie. "/books/:id")
     */
    protected $pattern;

    /**
     * @var mixed The callable associated with this route
     */
    protected $callable;

    /**
     * @var array Conditions for this route's URL parameters
     */
    protected $conditions = array();

    /**
     * @var array Default conditions applied to all Route instances
     */
    protected static $defaultConditions = array();

    /**
     * @var string The name of this route (optional)
     */
    protected $name;

    /**
     * @var array Key-value array of URL parameters
     */
    protected $params = array();

    /**
     * @var array HTTP methods supported by this Route
     */
    protected $methods = array();

    /**
     * @var Slim_Router The Router to which this Route belongs
     */
    protected $router;

    /**
     * @var array[Callable] Middleware
     */
    protected $middleware = array();

    /**
     * Constructor
     * @param   string  $pattern    The URL pattern (ie. "/books/:id")
     * @param   mixed   $callable   Anything that returns TRUE for is_callable()
     */
    public function __construct( $pattern, $callable ) {
        $this->setPattern($pattern);
        $this->setCallable($callable);
        $this->setConditions(self::getDefaultConditions());
    }

    /**
     * Set default route conditions for all instances
     * @param   array $defaultConditions
     * @return  void
     */
    public static function setDefaultConditions( array $defaultConditions ) {
        self::$defaultConditions = $defaultConditions;
    }

    /**
     * Get default route conditions for all instances
     * @return array
     */
    public static function getDefaultConditions() {
        return self::$defaultConditions;
    }

    /**
     * Get route pattern
     * @return string
     */
    public function getPattern() {
        return $this->pattern;
    }

    /**
     * Set route pattern
     * @param   string $pattern
     * @return  void
     */
    public function setPattern( $pattern ) {
        $this->pattern = str_replace(')', ')?', (string)$pattern);
    }

    /**
     * Get route callable
     * @return mixed
     */
    public function getCallable() {
        return $this->callable;
    }

    /**
     * Set route callable
     * @param   mixed $callable
     * @return  void
     */
    public function setCallable($callable) {
        $this->callable = $callable;
    }

    /**
     * Get route conditions
     * @return array
     */
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * Set route conditions
     * @param   array $conditions
     * @return  void
     */
    public function setConditions( array $conditions ) {
        $this->conditions = $conditions;
    }

    /**
     * Get route name
     * @return string|null
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set route name
     * @param   string $name
     * @return  void
     */
    public function setName( $name ) {
        $this->name = (string)$name;
        $this->router->addNamedRoute($this->name, $this);
    }

    /**
     * Get route parameters
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Add supported HTTP method(s)
     * @return void
     */
    public function setHttpMethods() {
        $args = func_get_args();
        $this->methods = $args;
    }

    /**
     * Get supported HTTP methods
     * @return array
     */
    public function getHttpMethods() {
        return $this->methods;
    }

    /**
     * Append supported HTTP methods
     * @return void
     */
    public function appendHttpMethods() {
        $args = func_get_args();
        $this->methods = array_merge($this->methods, $args);
    }

    /**
     * Append supported HTTP methods (alias for Route::appendHttpMethods)
     * @return Slim_Route
     */
    public function via() {
        $args = func_get_args();
        $this->methods = array_merge($this->methods, $args);
        return $this;
    }

    /**
     * Detect support for an HTTP method
     * @return bool
     */
    public function supportsHttpMethod( $method ) {
        return in_array($method, $this->methods);
    }

    /**
     * Get router
     * @return Slim_Router
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * Set router
     * @param   Slim_Router $router
     * @return  void
     */
    public function setRouter( Slim_Router $router ) {
        $this->router = $router;
    }

    /**
     * Get middleware
     * @return array[Callable]
     */
    public function getMiddleware() {
        return $this->middleware;
    }

    /**
     * Set middleware
     *
     * This method allows middleware to be assigned to a specific Route.
     * If the method argument `is_callable` (including callable arrays!),
     * we directly append the argument to `$this->middleware`. Else, we
     * assume the argument is an array of callables and merge the array
     * with `$this->middleware`. Even if non-callables are included in the
     * argument array, we still merge them; we lazily check each item
     * against `is_callable` during Route::dispatch().
     *
     * @param   Callable|array[Callable]
     * @return  Slim_Route
     * @throws  InvalidArgumentException If argument is not callable or not an array
     */
    public function setMiddleware( $middleware ) {
        if ( is_callable($middleware) ) {
            $this->middleware[] = $middleware;
        } else if ( is_array($middleware) ) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            throw new InvalidArgumentException('Route middleware must be callable or an array of callables');
        }
        return $this;
    }

    /**
     * Matches URI?
     *
     * Parse this route's pattern, and then compare it to an HTTP resource URI
     * This method was modeled after the techniques demonstrated by Dan Sosedoff at:
     *
     * http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
     *
     * @param   string  $resourceUri A Request URI
     * @return  bool
     */
    public function matches( $resourceUri ) {
        //Extract URL params
        preg_match_all('@:([\w]+)@', $this->pattern, $paramNames, PREG_PATTERN_ORDER);
        $paramNames = $paramNames[0];

        //Convert URL params into regex patterns, construct a regex for this route
        $patternAsRegex = preg_replace_callback('@:[\w]+@', array($this, 'convertPatternToRegex'), $this->pattern);
        if ( substr($this->pattern, -1) === '/' ) {
            $patternAsRegex = $patternAsRegex . '?';
        }
        $patternAsRegex = '@^' . $patternAsRegex . '$@';

        //Cache URL params' names and values if this route matches the current HTTP request
        if ( preg_match($patternAsRegex, $resourceUri, $paramValues) ) {
            array_shift($paramValues);
            foreach ( $paramNames as $index => $value ) {
                $val = substr($value, 1);
                if ( isset($paramValues[$val]) ) {
                    $this->params[$val] = urldecode($paramValues[$val]);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convert a URL parameter (ie. ":id") into a regular expression
     * @param   array   URL parameters
     * @return  string  Regular expression for URL parameter
     */
    protected function convertPatternToRegex( $matches ) {
        $key = str_replace(':', '', $matches[0]);
        if ( array_key_exists($key, $this->conditions) ) {
            return '(?P<' . $key . '>' . $this->conditions[$key] . ')';
        } else {
            return '(?P<' . $key . '>[a-zA-Z0-9_\-\.\!\~\*\\\'\(\)\:\@\&\=\$\+,%]+)';
        }
    }

    /**
     * Set route name
     * @param   string $name The name of the route
     * @return  Slim_Route
     */
    public function name( $name ) {
        $this->setName($name);
        return $this;
    }

    /**
     * Merge route conditions
     * @param   array $conditions Key-value array of URL parameter conditions
     * @return  Slim_Route
     */
    public function conditions( array $conditions ) {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }

    /**
     * Dispatch route
     *
     * This method invokes this route's callable. If middleware is
     * registered for this route, each callable middleware is invoked in
     * the order specified.
     *
     * This method is smart about trailing slashes on the route pattern.
     * If this route's pattern is defined with a trailing slash, and if the
     * current request URI does not have a trailing slash but otherwise
     * matches this route's pattern, a Slim_Exception_RequestSlash
     * will be thrown triggering an HTTP 301 Permanent Redirect to the same
     * URI _with_ a trailing slash. This Exception is caught in the
     * `Slim::run` loop. If this route's pattern is defined without a
     * trailing slash, and if the current request URI does have a trailing
     * slash, this route will not be matched and a 404 Not Found
     * response will be sent if no subsequent matching routes are found.
     *
     * @return  bool Was route callable invoked successfully?
     * @throws  Slim_Exception_RequestSlash
     */
    public function dispatch() {
        if ( substr($this->pattern, -1) === '/' && substr($this->router->getRequest()->getResourceUri(), -1) !== '/' ) {
            throw new Slim_Exception_RequestSlash();
        }

        //Invoke middleware
        $req = $this->router->getRequest();
        $res = $this->router->getResponse();
        foreach ( $this->middleware as $mw ) {
            if ( is_callable($mw) ) {
                call_user_func_array($mw, array($req, $res, $this));
            }
        }

        //Invoke callable
        if ( is_callable($this->getCallable()) ) {
            call_user_func_array($this->callable, array_values($this->params));
            return true;
        }
        return false;
    }
}