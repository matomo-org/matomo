<?php

namespace nx\core;

/**
 * The Request class is used to handle all data pertaining to an incoming HTTP
 * request.
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright 2011-2012 Nick Sinopoli
 * @license   http://opensource.org/licenses/BSD-3-Clause The BSD License
 */
class Request {

   /**
    * The POST/PUT/DELETE data.
    *
    * @var array
    */
    public $data = array();

   /**
    * The environment variables.
    *
    * @var array
    */
    protected $_env = array();

   /**
    * The GET data.
    *
    * @var array
    */
    public $query = array();

   /**
    * The parameters parsed from the request url.
    *
    * @var array
    */
    public $params;

   /**
    * The url of the request.
    *
    * @var string
    */
    public $url;

   /**
    * Sets the configuration options.
    *
    * @param array $config    The configuration options.  Possible keys
    *                         include:
    *                         'data' - the POST/PUT/DELETE data
    *                         'query' - the GET data
    * @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'data'  => array(),
            'query' => array()
        );

        $config += $defaults;

        $this->_env = $_SERVER + $_ENV + array(
            'CONTENT_TYPE'   => 'text/html',
            'REQUEST_METHOD' => 'GET'
        );

        if ( isset($this->_env['SCRIPT_URI']) ) {
            $this->_env['HTTPS'] =
                ( strpos($this->_env['SCRIPT_URI'], 'https://') === 0 );
        } elseif ( isset($this->_env['HTTPS']) ) {
            $this->_env['HTTPS'] = (
                !empty($this->_env['HTTPS']) && $this->_env['HTTPS'] !== 'off'
            );
        } else {
            $this->_env['HTTPS'] = false;
        }

        $this->_env['PHP_SELF'] = str_replace('\\', '/', str_replace(
            $this->_env['DOCUMENT_ROOT'], '', $this->_env['SCRIPT_FILENAME']
        ));

        $parsed = parse_url($this->_env['REQUEST_URI']);

        $base = str_replace('\\', '/', dirname($this->_env['PHP_SELF']));
        $base = rtrim(str_replace('/app/public', '', $base), '/');
        $pattern = '/^' . preg_quote($base, '/') . '/';
        $this->url = '/' . trim(
            preg_replace($pattern, '', $parsed['path']),
        '/');

        $query = array();
        if ( isset($parsed['query']) ) {
            $query_string = str_replace('%20', '+', $parsed['query']);
            parse_str(rawurldecode($query_string), $query);
        }
        $this->query = $config['query'] + $query;

        $this->data = $config['data'];
        if ( isset($_POST) ) {
            $this->data += $_POST;
        }

        $override ='HTTP_X_HTTP_METHOD_OVERRIDE';
        if ( isset($this->data['_method']) ) {
            $this->_env[$override] = strtoupper($this->data['_method']);
            unset($this->data['_method']);
        }
        if ( !empty($this->_env[$override]) ) {
            $this->_env['REQUEST_METHOD'] = $this->_env[$override];
        }

        $method = strtoupper($this->_env['REQUEST_METHOD']);

        if ( $method == 'PUT' || $method == 'DELETE' ) {
            $stream = fopen('php://input', 'r');
            parse_str(stream_get_contents($stream), $this->data);
            fclose($stream);
        }

    }

   /**
    * Returns an environment variable.
    *
    * @param string $key    The environment variable.
    * @return mixed
    */
    public function __get($key) {
        $key = strtoupper($key);
        return ( isset($this->_env[$key]) ) ? $this->_env[$key] : null;
    }

   /**
    * Checks for request characteristics.
    *
    * The full list of request characteristics is as follows:
    *
    * * 'ajax' - XHR
    * * 'delete' - DELETE REQUEST_METHOD
    * * 'flash' - "Shockwave Flash" HTTP_USER_AGENT
    * * 'get' - GET REQUEST_METHOD
    * * 'head' - HEAD REQUEST_METHOD
    * * 'mobile'  - any one of the following HTTP_USER_AGENTS:
    *
    * 1. 'Android'
    * 1. 'AvantGo'
    * 1. 'Blackberry'
    * 1. 'DoCoMo'
    * 1. 'iPod'
    * 1. 'iPhone'
    * 1. 'J2ME'
    * 1. 'NetFront'
    * 1. 'Nokia'
    * 1. 'MIDP'
    * 1. 'Opera Mini'
    * 1. 'PalmOS'
    * 1. 'PalmSource'
    * 1. 'Plucker'
    * 1. 'portalmmm'
    * 1. 'ReqwirelessWeb'
    * 1. 'SonyEricsson'
    * 1. 'Symbian'
    * 1. 'UP.Browser'
    * 1. 'Windows CE'
    * 1. 'Xiino'
    *
    * * 'options' - OPTIONS REQUEST_METHOD
    * * 'post'    - POST REQUEST_METHOD
    * * 'put'     - PUT REQUEST_METHOD
    * * 'ssl'     - HTTPS
    *
    * @param string $characteristic    The characteristic.
    * @return bool
    */
    public function is($characteristic) {
        switch ( strtolower($characteristic) ) {
            case 'ajax':
                return (
                    $this->http_x_requested_with == 'XMLHttpRequest'
                );
            case 'delete':
                return ( $this->request_method == 'DELETE' );
            case 'flash':
                return (
                    $this->http_user_agent == 'Shockwave Flash'
                );
            case 'get':
                return ( $this->request_method == 'GET' );
            case 'head':
                return ( $this->request_method == 'HEAD' );
            case 'mobile':
                $mobile_user_agents = array(
                    'Android', 'AvantGo', 'Blackberry', 'DoCoMo', 'iPod',
                    'iPhone', 'J2ME', 'NetFront', 'Nokia', 'MIDP', 'Opera Mini',
                    'PalmOS', 'PalmSource', 'Plucker', 'portalmmm',
                    'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\.Browser',
                    'Windows CE', 'Xiino'
                );
                $pattern = '/' . implode('|', $mobile_user_agents) . '/i';
                return (boolean) preg_match(
                    $pattern, $this->http_user_agent
                );
            case 'options':
                return ( $this->request_method == 'OPTIONS' );
            case 'post':
                return ( $this->request_method == 'POST' );
            case 'put':
                return ( $this->request_method == 'PUT' );
            case 'ssl':
                return $this->https;
            default:
                return false;
        }
    }

}

?>
