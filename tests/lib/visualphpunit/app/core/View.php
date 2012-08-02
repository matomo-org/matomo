<?php

namespace app\core;

class View {

    protected $_config;

   /**
    *  Loads the configuration settings for the view.
    *
    *  @param array $config         The configuration options.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'dependencies' => array(
                'compiler' => 'app\lib\Compiler',
            )
        );
        $this->_config = $config + $defaults;
    }

   /**
    *  Escapes a value for output in an HTML context.
    *
    *  @param mixed $value
    *  @access public
    *  @return mixed
    */
    public function escape($value) {
        return nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }

   /**
    *  Renders a given file with the supplied variables.
    *
    *  @param string $file    The file to be rendered.
    *  @param mixed $vars     The variables to be substituted in the view.
    *  @access public
    *  @return string
    */
    public function render($file, $vars = null) {
        $path = dirname(__DIR__) . '/resource/cache/';
        $file = dirname(__DIR__) . "/view/{$file}.html";

        $compiler = $this->_config['dependencies']['compiler'];
        $options = compact('path');
        $__template__ = $compiler::compile($file, $options);

        if ( is_array($vars) ) {
            extract($vars);
        }

        ob_start();
        require $__template__;
        return ob_get_clean();
    }

}

?>
