<?php

namespace app\lib;

class Compiler {

   /**
    *  Retrieves the compiled filename, and caches the file
    *  if it is not already cached.
    *
    *  @param string $file      The file location.
    *  @param array $options    The compilation options, which take the
    *                           following keys:
    *                           'path' - The path where where the cached file
    *                           should be stored.
    *  @access public
    *  @return string
    */
    public static function compile($file, $options = array()) {
        $options += array(
            'path' => 'compiled/'
        );

        $stats = stat($file);
        $dir = dirname($file);
        $location = basename(dirname($dir)) . '_' . basename($dir)
            . '_' . basename($file, '.html');
        $template = 'template_' . $location . '_' . $stats['mtime']
            . '_' . $stats['ino'] . '_' . $stats['size'] . '.html';
        $template = $options['path'] . $template;

        if ( file_exists($template) ) {
            return $template;
        }

        $compiled = self::_replace(file_get_contents($file));
        $template_dir = dirname($template);
        if ( !is_dir($template_dir) && !mkdir($template_dir, 0755, true) ) {
           return false;
        }

        if (
            !is_writable($template_dir)
            || file_put_contents($template, $compiled) === false
        ) {
            return false;
        }

        $pattern = $template_dir . '/template_' . $location . '_*.html';
        foreach ( glob($pattern) as $old ) {
            if ( $old !== $template ) {
                unlink($old);
            }
        }
        return $template;
    }

   /**
    *  Replaces a template with custom syntax.
    *
    *  @param string $template      The template.
    *  @access public
    *  @return string
    */
    protected static function _replace($template) {
        $replace = array(
            '/\<\?=\s*\$this->(.+?)\s*;?\s*\?>/msx' =>
            '<?php echo $this->$1; ?>',

            '/\$e\((.+?)\)\s*;/msx'                 =>
            'echo $this->escape($1);',

            '/\<\?=\s*(.+?)\s*;?\s*\?>/msx'         =>
            '<?php echo $this->escape($1); ?>'
        );

        return preg_replace(
          array_keys($replace), array_values($replace), $template
        );
    }

}
