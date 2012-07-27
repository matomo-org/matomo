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
 * Slim View
 *
 * The View is responsible for rendering and/or displaying a template.
 * It is recommended that you subclass View and re-implement the
 * `View::render` method to use a custom templating engine such as
 * Smarty, Twig, Mustache, etc. It is important that `View::render`
 * `return` the final template output. Do not `echo` the output.
 *
 * @package Slim
 * @author  Josh Lockhart
 * @since   1.0.0
 */
class Slim_View {
    /**
     * @var array Key-value array of data available to the template
     */
    protected $data = array();

    /**
     * @var string Absolute or relative path to the templates directory
     */
    protected $templatesDirectory;

    /**
     * Constructor
     *
     * This is empty but may be overridden in a subclass
     */
    public function __construct() {}

    /**
     * Get data
     * @param   string              $key
     * @return  array|mixed|null    All View data if no $key, value of datum
     *                              if $key, or NULL if $key but datum
     *                              does not exist.
     */
    public function getData( $key = null ) {
        if ( !is_null($key) ) {
            return isset($this->data[$key]) ? $this->data[$key] : null;
        } else {
            return $this->data;
        }
    }

    /**
     * Set data
     *
     * This method is overloaded to accept two different method signatures.
     * You may use this to set a specific key with a specfic value,
     * or you may use this to set all data to a specific array.
     *
     * USAGE:
     *
     * View::setData('color', 'red');
     * View::setData(array('color' => 'red', 'number' => 1));
     *
     * @param   string|array
     * @param   mixed                       Optional. Only use if first argument is a string.
     * @return  void
     * @throws  InvalidArgumentException    If incorrect method signature
     */
    public function setData() {
        $args = func_get_args();
        if ( count($args) === 1 && is_array($args[0]) ) {
            $this->data = $args[0];
        } else if ( count($args) === 2 ) {
            $this->data[(string)$args[0]] = $args[1];
        } else {
            throw new InvalidArgumentException('Cannot set View data with provided arguments. Usage: `View::setData( $key, $value );` or `View::setData([ key => value, ... ]);`');
        }
    }

    /**
     * Append data to existing View data
     * @param   array $data
     * @return  void
     */
    public function appendData( array $data ) {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Get templates directory
     * @return string|null Path to templates directory without trailing slash
     */
    public function getTemplatesDirectory() {
        return $this->templatesDirectory;
    }

    /**
     * Set templates directory
     * @param   string $dir
     * @return  void
     * @throws  RuntimeException If directory is not a directory or does not exist
     */
    public function setTemplatesDirectory( $dir ) {
        $this->templatesDirectory = rtrim($dir, '/');
    }

    /**
     * Display template
     *
     * This method echoes the rendered template to the current output buffer
     *
     * @param   string $template Path to template file relative to templates directoy
     * @return  void
     */
    public function display( $template ) {
        echo $this->render($template);
    }

    /**
     * Render template
     * @param   string $template    Path to template file relative to templates directory
     * @return  string              Rendered template
     * @throws  RuntimeException    If template does not exist
     */
    public function render( $template ) {
        extract($this->data);
        $templatePath = $this->getTemplatesDirectory() . '/' . ltrim($template, '/');
        if ( !file_exists($templatePath) ) {
            throw new RuntimeException('View cannot render template `' . $templatePath . '`. Template does not exist.');
        }
        ob_start();
        require $templatePath;
        return ob_get_clean();
    }

}