<?php

namespace nx\core;

/**
 * The Dispatcher handles incoming HTTP requests and sends back responses.
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright 2011-2012 Nick Sinopoli
 * @license   http://opensource.org/licenses/BSD-3-Clause The BSD License
 */
class Dispatcher {

   /**
    * The configuration settings.
    *
    * @var array
    */
    protected $_config = array();

   /**
    * Sets the configuration options for the dispatcher.
    *
    * @param array $config    The configuration options.
    * @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'response' => new \nx\core\Response(),
            'router'   => new \nx\core\Router()
        );
        $this->_config = $config + $defaults;
    }

   /**
    * Matches an incoming request with the supplied routes, calls the
    * callback associated with the matched route, and sends a response.
    *
    * @param object $request    The incoming request object.
    * @param array $routes      The routes.
    * @return void
    */
    public function handle($request, $routes) {
        $method = $request->request_method;

        $router = $this->_config['router'];
        $parsed = $router->parse($request->url, $method, $routes);

        if ( $parsed['callback'] ) {
            $request->params = $parsed['params'];
            $result = call_user_func($parsed['callback'], $request);
        } else {
            $result = false;
        }

        $response = $this->_config['response'];
        $response->render($result);
    }

}

?>
