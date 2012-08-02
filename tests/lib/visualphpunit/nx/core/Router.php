<?php

namespace nx\core;

/**
 * The Router is used to handle url routing.
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright 2011-2012 Nick Sinopoli
 * @license   http://opensource.org/licenses/BSD-3-Clause The BSD License
 */
class Router {

   /**
    * Compiles the regex necessary to capture all match types within a route.
    *
    * @param string $route    The route.
    * @return string
    */
    protected function _compile_regex($route) {
        $pattern = '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`';

        if ( preg_match_all($pattern, $route, $matches, PREG_SET_ORDER) ) {
            $match_types = array(
                'i'  => '[0-9]++',
                'a'  => '[0-9A-Za-z]++',
                'h'  => '[0-9A-Fa-f]++',
                '*'  => '.+?',
                ''   => '[^/]++'
            );
            foreach ( $matches as $match ) {
                list($block, $pre, $type, $param, $optional) = $match;

                if ( isset($match_types[$type]) ) {
                    $type = $match_types[$type];
                }
                if ( $param ) {
                    $param = "?<{$param}>";
                }
                if ( $optional ) {
                    $optional = '?';
                }

                $replaced = "(?:{$pre}({$param}{$type})){$optional}";
                $route = str_replace($block, $replaced, $route);
            }
        }
        if ( substr($route, strlen($route) - 1) != '/' ) {
            $route .= '/?';
        }
        return "`^{$route}$`";
    }

   /**
    * Parses the supplied request uri based on the supplied routes and
    * the request method.
    *
    * Routes should be of the following format:
    *
    * <code>
    * $routes = array(
    *     array(
    *         mixed $request_method, string $request_uri, callable $callback
    *     ),
    *     ...
    * );
    * </code>
    *
    * where:
    *
    * <code>
    * $request_method can be a string ('GET', 'POST', 'PUT', 'DELETE'),
    * or an array (e.g., array('GET, 'POST')).  Note that $request_method
    * is case-insensitive.
    * </code>
    *
    * <code>
    * $request_uri is a string, with optional match types.  Valid match types
    * are as follows:
    *
    * [i] - integer
    * [a] - alphanumeric
    * [h] - hexadecimal
    * [*] - anything
    *
    * Match types can be combined with parameter names, which will be
    * captured:
    *
    * [i:id] - will match an integer, storing it within the returned 'params'
    * array under the 'id' key
    * [a:name] - will match an alphanumeric value, storing it within the
    * returned 'params' array under the 'name' key
    *
    * Here are some examples to help illustrate:
    *
    * /post/[i:id] - will match on /post/32 (with the returned 'params' array
    * containing an 'id' key with a value of 32), but will not match on
    * /post/today
    *
    * /find/[h:serial] - will match on /find/ae32 (with the returned 'params'
    * array containing a 'serial' key will a value of 'ae32'), but will not
    * match on /find/john
    * </code>
    *
    * <code>
    * $callback is a valid callback function.
    * </code>
    *
    * Returns an array containing the following keys:
    *
    * * 'params'   - The parameters collected from the matched uri
    * * 'callback' - The callback function pulled from the matched route
    *
    * @param string $request_uri       The request uri.
    * @param string $request_method    The request method.
    * @param array $routes             The routes.
    * @return array
    */
    public function parse($request_uri, $request_method, $routes) {
        foreach ( $routes as $route ) {
            list($method, $uri, $callback) = $route;

            if ( is_array($method) ) {
                $found = false;
                foreach ( $method as $value ) {
                    if ( strcasecmp($request_method, $value) == 0 ) {
                        $found = true;
                        break;
                    }
                }
                if ( !$found ) {
                    continue;
                }
            } elseif ( strcasecmp($request_method, $method) != 0 ) {
                continue;
            }

            if ( is_null($uri) || $uri == '*' ) {
                $params = array();
                return compact('params', 'callback');
            }

            $route_to_match = '';
            $len = strlen($uri);

            for ( $i = 0; $i < $len; $i++ ) {
                $char = $uri[$i];
                $is_regex = (
                    $char == '[' || $char == '(' || $char == '.'
                    || $char == '?' || $char == '+' || $char == '{'
                );
                if ( $is_regex ) {
                    $route_to_match = $uri;
                    break;
                } elseif (
                    !isset($request_uri[$i]) || $char != $request_uri[$i]
                ) {
                    continue 2;
                }
                $route_to_match .= $char;
            }

            $regex = $this->_compile_regex($route_to_match);
            if ( preg_match($regex, $request_uri, $params) ) {
                foreach ( $params as $key => $arg ) {
                    if ( is_numeric($key) ) {
                        unset($params[$key]);
                    }
                }
                return compact('params', 'callback');
            }
        }
        return array(
            'params'   => null,
            'callback' => null
        );
    }

}

?>
