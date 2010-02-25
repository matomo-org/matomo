<?php

/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty_Compiler.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://smarty.php.net/
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Andrei Zmievski <andrei@php.net>
 * @version 2.6.26
 * @copyright 2001-2005 New Digital Group, Inc.
 * @package Smarty
 */

/* $Id$ */

/**
 * Template compiling class
 * @package Smarty
 */
class Smarty_Compiler extends Smarty {

    // internal vars
    /**#@+
     * @access private
     */
    var $_folded_blocks         =   array();    // keeps folded template blocks
    var $_current_file          =   null;       // the current template being compiled
    var $_current_line_no       =   1;          // line number for error messages
    var $_capture_stack         =   array();    // keeps track of nested capture buffers
    var $_plugin_info           =   array();    // keeps track of plugins to load
    var $_init_smarty_vars      =   false;
    var $_permitted_tokens      =   array('true','false','yes','no','on','off','null');
    var $_db_qstr_regexp        =   null;        // regexps are setup in the constructor
    var $_si_qstr_regexp        =   null;
    var $_qstr_regexp           =   null;
    var $_func_regexp           =   null;
    var $_reg_obj_regexp        =   null;
    var $_var_bracket_regexp    =   null;
    var $_num_const_regexp      =   null;
    var $_dvar_guts_regexp      =   null;
    var $_dvar_regexp           =   null;
    var $_cvar_regexp           =   null;
    var $_svar_regexp           =   null;
    var $_avar_regexp           =   null;
    var $_mod_regexp            =   null;
    var $_var_regexp            =   null;
    var $_parenth_param_regexp  =   null;
    var $_func_call_regexp      =   null;
    var $_obj_ext_regexp        =   null;
    var $_obj_start_regexp      =   null;
    var $_obj_params_regexp     =   null;
    var $_obj_call_regexp       =   null;
    var $_cacheable_state       =   0;
    var $_cache_attrs_count     =   0;
    var $_nocache_count         =   0;
    var $_cache_serial          =   null;
    var $_cache_include         =   null;

    var $_strip_depth           =   0;
    var $_additional_newline    =   "\n";

    /**#@-*/
    /**
     * The class constructor.
     */
    function Smarty_Compiler()
    {
        // matches double quoted strings:
        // "foobar"
        // "foo\"bar"
        $this->_db_qstr_regexp = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';

        // matches single quoted strings:
        // 'foobar'
        // 'foo\'bar'
        $this->_si_qstr_regexp = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';

        // matches single or double quoted strings
        $this->_qstr_regexp = '(?:' . $this->_db_qstr_regexp . '|' . $this->_si_qstr_regexp . ')';

        // matches bracket portion of vars
        // [0]
        // [foo]
        // [$bar]
        $this->_var_bracket_regexp = '\[\$?[\w\.]+\]';

        // matches numerical constants
        // 30
        // -12
        // 13.22
        $this->_num_const_regexp = '(?:\-?\d+(?:\.\d+)?)';

        // matches $ vars (not objects):
        // $foo
        // $foo.bar
        // $foo.bar.foobar
        // $foo[0]
        // $foo[$bar]
        // $foo[5][blah]
        // $foo[5].bar[$foobar][4]
        $this->_dvar_math_regexp = '(?:[\+\*\/\%]|(?:-(?!>)))';
        $this->_dvar_math_var_regexp = '[\$\w\.\+\-\*\/\%\d\>\[\]]';
        $this->_dvar_guts_regexp = '\w+(?:' . $this->_var_bracket_regexp
                . ')*(?:\.\$?\w+(?:' . $this->_var_bracket_regexp . ')*)*(?:' . $this->_dvar_math_regexp . '(?:' . $this->_num_const_regexp . '|' . $this->_dvar_math_var_regexp . ')*)?';
        $this->_dvar_regexp = '\$' . $this->_dvar_guts_regexp;

        // matches config vars:
        // #foo#
        // #foobar123_foo#
        $this->_cvar_regexp = '\#\w+\#';

        // matches section vars:
        // %foo.bar%
        $this->_svar_regexp = '\%\w+\.\w+\%';

        // matches all valid variables (no quotes, no modifiers)
        $this->_avar_regexp = '(?:' . $this->_dvar_regexp . '|'
           . $this->_cvar_regexp . '|' . $this->_svar_regexp . ')';

        // matches valid variable syntax:
        // $foo
        // $foo
        // #foo#
        // #foo#
        // "text"
        // "text"
        $this->_var_regexp = '(?:' . $this->_avar_regexp . '|' . $this->_qstr_regexp . ')';

        // matches valid object call (one level of object nesting allowed in parameters):
        // $foo->bar
        // $foo->bar()
        // $foo->bar("text")
        // $foo->bar($foo, $bar, "text")
        // $foo->bar($foo, "foo")
        // $foo->bar->foo()
        // $foo->bar->foo->bar()
        // $foo->bar($foo->bar)
        // $foo->bar($foo->bar())
        // $foo->bar($foo->bar($blah,$foo,44,"foo",$foo[0].bar))
        $this->_obj_ext_regexp = '\->(?:\$?' . $this->_dvar_guts_regexp . ')';
        $this->_obj_restricted_param_regexp = '(?:'
                . '(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . ')(?:' . $this->_obj_ext_regexp . '(?:\((?:(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . ')'
                . '(?:\s*,\s*(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . '))*)?\))?)*)';
        $this->_obj_single_param_regexp = '(?:\w+|' . $this->_obj_restricted_param_regexp . '(?:\s*,\s*(?:(?:\w+|'
                . $this->_var_regexp . $this->_obj_restricted_param_regexp . ')))*)';
        $this->_obj_params_regexp = '\((?:' . $this->_obj_single_param_regexp
                . '(?:\s*,\s*' . $this->_obj_single_param_regexp . ')*)?\)';
        $this->_obj_start_regexp = '(?:' . $this->_dvar_regexp . '(?:' . $this->_obj_ext_regexp . ')+)';
        $this->_obj_call_regexp = '(?:' . $this->_obj_start_regexp . '(?:' . $this->_obj_params_regexp . ')?(?:' . $this->_dvar_math_regexp . '(?:' . $this->_num_const_regexp . '|' . $this->_dvar_math_var_regexp . ')*)?)';
        
        // matches valid modifier syntax:
        // |foo
        // |@foo
        // |foo:"bar"
        // |foo:$bar
        // |foo:"bar":$foobar
        // |foo|bar
        // |foo:$foo->bar
        $this->_mod_regexp = '(?:\|@?\w+(?::(?:\w+|' . $this->_num_const_regexp . '|'
           . $this->_obj_call_regexp . '|' . $this->_avar_regexp . '|' . $this->_qstr_regexp .'))*)';

        // matches valid function name:
        // foo123
        // _foo_bar
        $this->_func_regexp = '[a-zA-Z_]\w*';

        // matches valid registered object:
        // foo->bar
        $this->_reg_obj_regexp = '[a-zA-Z_]\w*->[a-zA-Z_]\w*';

        // matches valid parameter values:
        // true
        // $foo
        // $foo|bar
        // #foo#
        // #foo#|bar
        // "text"
        // "text"|bar
        // $foo->bar
        $this->_param_regexp = '(?:\s*(?:' . $this->_obj_call_regexp . '|'
           . $this->_var_regexp . '|' . $this->_num_const_regexp  . '|\w+)(?>' . $this->_mod_regexp . '*)\s*)';

        // matches valid parenthesised function parameters:
        //
        // "text"
        //    $foo, $bar, "text"
        // $foo|bar, "foo"|bar, $foo->bar($foo)|bar
        $this->_parenth_param_regexp = '(?:\((?:\w+|'
                . $this->_param_regexp . '(?:\s*,\s*(?:(?:\w+|'
                . $this->_param_regexp . ')))*)?\))';

        // matches valid function call:
        // foo()
        // foo_bar($foo)
        // _foo_bar($foo,"bar")
        // foo123($foo,$foo->bar(),"foo")
        $this->_func_call_regexp = '(?:' . $this->_func_regexp . '\s*(?:'
           . $this->_parenth_param_regexp . '))';
    }

    /**
     * compile a resource
     *
     * sets $compiled_content to the compiled source
     * @param string $resource_name
     * @param string $source_content
     * @param string $compiled_content
     * @return true
     */
    function _compile_file($resource_name, $source_content, &$compiled_content)
    {

        if ($this->security) {
            // do not allow php syntax to be executed unless specified
            if ($this->php_handling == SMARTY_PHP_ALLOW &&
                !$this->security_settings['PHP_HANDLING']) {
                $this->php_handling = SMARTY_PHP_PASSTHRU;
            }
        }

        $this->_load_filters();

        $this->_current_file = $resource_name;
        $this->_current_line_no = 1;
        $ldq = preg_quote($this->left_delimiter, '~');
        $rdq = preg_quote($this->right_delimiter, '~');

        // run template source through prefilter functions
        if (count($this->_plugins['prefilter']) > 0) {
            foreach ($this->_plugins['prefilter'] as $filter_name => $prefilter) {
                if ($prefilter === false) continue;
                if ($prefilter[3] || is_callable($prefilter[0])) {
                    $source_content = call_user_func_array($prefilter[0],
                                                            array($source_content, &$this));
                    $this->_plugins['prefilter'][$filter_name][3] = true;
                } else {
                    $this->_trigger_fatal_error("[plugin] prefilter '$filter_name' is not implemented");
                }
            }
        }

        /* fetch all special blocks */
        $search = "~{$ldq}\*(.*?)\*{$rdq}|{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}|{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}~s";

        preg_match_all($search, $source_content, $match,  PREG_SET_ORDER);
        $this->_folded_blocks = $match;
        reset($this->_folded_blocks);

        /* replace special blocks by "{php}" */
        $source_content = preg_replace($search.'e', "'"
                                       . $this->_quote_replace($this->left_delimiter) . 'php'
                                       . "' . str_repeat(\"\n\", substr_count('\\0', \"\n\")) .'"
                                       . $this->_quote_replace($this->right_delimiter)
                                       . "'"
                                       , $source_content);

        /* Gather all template tags. */
        preg_match_all("~{$ldq}\s*(.*?)\s*{$rdq}~s", $source_content, $_match);
        $template_tags = $_match[1];
        /* Split content by template tags to obtain non-template content. */
        $text_blocks = preg_split("~{$ldq}.*?{$rdq}~s", $source_content);

        /* loop through text blocks */
        for ($curr_tb = 0, $for_max = count($text_blocks); $curr_tb < $for_max; $curr_tb++) {
            /* match anything resembling php tags */
            if (preg_match_all('~(<\?(?:\w+|=)?|\?>|language\s*=\s*[\"\']?\s*php\s*[\"\']?)~is', $text_blocks[$curr_tb], $sp_match)) {
                /* replace tags with placeholders to prevent recursive replacements */
                $sp_match[1] = array_unique($sp_match[1]);
                usort($sp_match[1], '_smarty_sort_length');
                for ($curr_sp = 0, $for_max2 = count($sp_match[1]); $curr_sp < $for_max2; $curr_sp++) {
                    $text_blocks[$curr_tb] = str_replace($sp_match[1][$curr_sp],'%%%SMARTYSP'.$curr_sp.'%%%',$text_blocks[$curr_tb]);
                }
                /* process each one */
                for ($curr_sp = 0, $for_max2 = count($sp_match[1]); $curr_sp < $for_max2; $curr_sp++) {
                    if ($this->php_handling == SMARTY_PHP_PASSTHRU) {
                        /* echo php contents */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', '<?php echo \''.str_replace("'", "\'", $sp_match[1][$curr_sp]).'\'; ?>'."\n", $text_blocks[$curr_tb]);
                    } else if ($this->php_handling == SMARTY_PHP_QUOTE) {
                        /* quote php tags */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', htmlspecialchars($sp_match[1][$curr_sp]), $text_blocks[$curr_tb]);
                    } else if ($this->php_handling == SMARTY_PHP_REMOVE) {
                        /* remove php tags */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', '', $text_blocks[$curr_tb]);
                    } else {
                        /* SMARTY_PHP_ALLOW, but echo non php starting tags */
                        $sp_match[1][$curr_sp] = preg_replace('~(<\?(?!php|=|$))~i', '<?php echo \'\\1\'?>'."\n", $sp_match[1][$curr_sp]);
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', $sp_match[1][$curr_sp], $text_blocks[$curr_tb]);
                    }
                }
            }
        }
        
        /* Compile the template tags into PHP code. */
        $compiled_tags = array();
        for ($i = 0, $for_max = count($template_tags); $i < $for_max; $i++) {
            $this->_current_line_no += substr_count($text_blocks[$i], "\n");
            $compiled_tags[] = $this->_compile_tag($template_tags[$i]);
            $this->_current_line_no += substr_count($template_tags[$i], "\n");
        }
        if (count($this->_tag_stack)>0) {
            list($_open_tag, $_line_no) = end($this->_tag_stack);
            $this->_syntax_error("unclosed tag \{$_open_tag} (opened line $_line_no).", E_USER_ERROR, __FILE__, __LINE__);
            return;
        }

        /* Reformat $text_blocks between 'strip' and '/strip' tags,
           removing spaces, tabs and newlines. */
        $strip = false;
        for ($i = 0, $for_max = count($compiled_tags); $i < $for_max; $i++) {
            if ($compiled_tags[$i] == '{strip}') {
                $compiled_tags[$i] = '';
                $strip = true;
                /* remove leading whitespaces */
                $text_blocks[$i + 1] = ltrim($text_blocks[$i + 1]);
            }
            if ($strip) {
                /* strip all $text_blocks before the next '/strip' */
                for ($j = $i + 1; $j < $for_max; $j++) {
                    /* remove leading and trailing whitespaces of each line */
                    $text_blocks[$j] = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $text_blocks[$j]);
                    if ($compiled_tags[$j] == '{/strip}') {                       
                        /* remove trailing whitespaces from the last text_block */
                        $text_blocks[$j] = rtrim($text_blocks[$j]);
                    }
                    $text_blocks[$j] = "<?php echo '" . strtr($text_blocks[$j], array("'"=>"\'", "\\"=>"\\\\")) . "'; ?>";
                    if ($compiled_tags[$j] == '{/strip}') {
                        $compiled_tags[$j] = "\n"; /* slurped by php, but necessary
                                    if a newline is following the closing strip-tag */
                        $strip = false;
                        $i = $j;
                        break;
                    }
                }
            }
        }
        $compiled_content = '';
        
        $tag_guard = '%%%SMARTYOTG' . md5(uniqid(rand(), true)) . '%%%';
        
        /* Interleave the compiled contents and text blocks to get the final result. */
        for ($i = 0, $for_max = count($compiled_tags); $i < $for_max; $i++) {
            if ($compiled_tags[$i] == '') {
                // tag result empty, remove first newline from following text block
                $text_blocks[$i+1] = preg_replace('~^(\r\n|\r|\n)~', '', $text_blocks[$i+1]);
            }
            // replace legit PHP tags with placeholder
            $text_blocks[$i] = str_replace('<?', $tag_guard, $text_blocks[$i]);
            $compiled_tags[$i] = str_replace('<?', $tag_guard, $compiled_tags[$i]);
            
            $compiled_content .= $text_blocks[$i] . $compiled_tags[$i];
        }
        $compiled_content .= str_replace('<?', $tag_guard, $text_blocks[$i]);

        // escape php tags created by interleaving
        $compiled_content = str_replace('<?', "<?php echo '<?' ?>\n", $compiled_content);
        $compiled_content = preg_replace("~(?<!')language\s*=\s*[\"\']?\s*php\s*[\"\']?~", "<?php echo 'language=php' ?>\n", $compiled_content);

        // recover legit tags
        $compiled_content = str_replace($tag_guard, '<?', $compiled_content); 
        
        // remove \n from the end of the file, if any
        if (strlen($compiled_content) && (substr($compiled_content, -1) == "\n") ) {
            $compiled_content = substr($compiled_content, 0, -1);
        }

        if (!empty($this->_cache_serial)) {
            $compiled_content = "<?php \$this->_cache_serials['".$this->_cache_include."'] = '".$this->_cache_serial."'; ?>" . $compiled_content;
        }

        // run compiled template through postfilter functions
        if (count($this->_plugins['postfilter']) > 0) {
            foreach ($this->_plugins['postfilter'] as $filter_name => $postfilter) {
                if ($postfilter === false) continue;
                if ($postfilter[3] || is_callable($postfilter[0])) {
                    $compiled_content = call_user_func_array($postfilter[0],
                                                              array($compiled_content, &$this));
                    $this->_plugins['postfilter'][$filter_name][3] = true;
                } else {
                    $this->_trigger_fatal_error("Smarty plugin error: postfilter '$filter_name' is not implemented");
                }
            }
        }

        // put header at the top of the compiled template
        $template_header = "<?php /* Smarty version ".$this->_version.", created on ".strftime("%Y-%m-%d %H:%M:%S")."\n";
        $template_header .= "         compiled from ".strtr(urlencode($resource_name), array('%2F'=>'/', '%3A'=>':'))." */ ?>\n";

        /* Emit code to load needed plugins. */
        $this->_plugins_code = '';
        if (count($this->_plugin_info)) {
            $_plugins_params = "array('plugins' => array(";
            foreach ($this->_plugin_info as $plugin_type => $plugins) {
                foreach ($plugins as $plugin_name => $plugin_info) {
                    $_plugins_params .= "array('$plugin_type', '$plugin_name', '" . strtr($plugin_info[0], array("'" => "\\'", "\\" => "\\\\")) . "', $plugin_info[1], ";
                    $_plugins_params .= $plugin_info[2] ? 'true),' : 'false),';
                }
            }
            $_plugins_params .= '))';
            $plugins_code = "<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');\nsmarty_core_load_plugins($_plugins_params, \$this); ?>\n";
            $template_header .= $plugins_code;
            $this->_plugin_info = array();
            $this->_plugins_code = $plugins_code;
        }

        if ($this->_init_smarty_vars) {
            $template_header .= "<?php require_once(SMARTY_CORE_DIR . 'core.assign_smarty_interface.php');\nsmarty_core_assign_smarty_interface(null, \$this); ?>\n";
            $this->_init_smarty_vars = false;
        }

        $compiled_content = $template_header . $compiled_content;
        return true;
    }

    /**
     * Compile a template tag
     *
     * @param string $template_tag
     * @return string
     */
    function _compile_tag($template_tag)
    {
        /* Matched comment. */
        if (substr($template_tag, 0, 1) == '*' && substr($template_tag, -1) == '*')
            return '';
        
        /* Split tag into two three parts: command, command modifiers and the arguments. */
        if(! preg_match('~^(?:(' . $this->_num_const_regexp . '|' . $this->_obj_call_regexp . '|' . $this->_var_regexp
                . '|\/?' . $this->_reg_obj_regexp . '|\/?' . $this->_func_regexp . ')(' . $this->_mod_regexp . '*))
                      (?:\s+(.*))?$
                    ~xs', $template_tag, $match)) {
            $this->_syntax_error("unrecognized tag: $template_tag", E_USER_ERROR, __FILE__, __LINE__);
        }
        
        $tag_command = $match[1];
        $tag_modifier = isset($match[2]) ? $match[2] : null;
        $tag_args = isset($match[3]) ? $match[3] : null;

        if (preg_match('~^' . $this->_num_const_regexp . '|' . $this->_obj_call_regexp . '|' . $this->_var_regexp . '$~', $tag_command)) {
            /* tag name is a variable or object */
            $_return = $this->_parse_var_props($tag_command . $tag_modifier);
            return "<?php echo $_return; ?>" . $this->_additional_newline;
        }

        /* If the tag name is a registered object, we process it. */
        if (preg_match('~^\/?' . $this->_reg_obj_regexp . '$~', $tag_command)) {
            return $this->_compile_registered_object_tag($tag_command, $this->_parse_attrs($tag_args), $tag_modifier);
        }

        switch ($tag_command) {
            case 'include':
                return $this->_compile_include_tag($tag_args);

            case 'include_php':
                return $this->_compile_include_php_tag($tag_args);

            case 'if':
                $this->_push_tag('if');
                return $this->_compile_if_tag($tag_args);

            case 'else':
                list($_open_tag) = end($this->_tag_stack);
                if ($_open_tag != 'if' && $_open_tag != 'elseif')
                    $this->_syntax_error('unexpected {else}', E_USER_ERROR, __FILE__, __LINE__);
                else
                    $this->_push_tag('else');
                return '<?php else: ?>';

            case 'elseif':
                list($_open_tag) = end($this->_tag_stack);
                if ($_open_tag != 'if' && $_open_tag != 'elseif')
                    $this->_syntax_error('unexpected {elseif}', E_USER_ERROR, __FILE__, __LINE__);
                if ($_open_tag == 'if')
                    $this->_push_tag('elseif');
                return $this->_compile_if_tag($tag_args, true);

            case '/if':
                $this->_pop_tag('if');
                return '<?php endif; ?>';

            case 'capture':
                return $this->_compile_capture_tag(true, $tag_args);

            case '/capture':
                return $this->_compile_capture_tag(false);

            case 'ldelim':
                return $this->left_delimiter;

            case 'rdelim':
                return $this->right_delimiter;

            case 'section':
                $this->_push_tag('section');
                return $this->_compile_section_start($tag_args);

            case 'sectionelse':
                $this->_push_tag('sectionelse');
                return "<?php endfor; else: ?>";
                break;

            case '/section':
                $_open_tag = $this->_pop_tag('section');
                if ($_open_tag == 'sectionelse')
                    return "<?php endif; ?>";
                else
                    return "<?php endfor; endif; ?>";

            case 'foreach':
                $this->_push_tag('foreach');
                return $this->_compile_foreach_start($tag_args);
                break;

            case 'foreachelse':
                $this->_push_tag('foreachelse');
                return "<?php endforeach; else: ?>";

            case '/foreach':
                $_open_tag = $this->_pop_tag('foreach');
                if ($_open_tag == 'foreachelse')
                    return "<?php endif; unset(\$_from); ?>";
                else
                    return "<?php endforeach; endif; unset(\$_from); ?>";
                break;

            case 'strip':
            case '/strip':
                if (substr($tag_command, 0, 1)=='/') {
                    $this->_pop_tag('strip');
                    if (--$this->_strip_depth==0) { /* outermost closing {/strip} */
                        $this->_additional_newline = "\n";
                        return '{' . $tag_command . '}';
                    }
                } else {
                    $this->_push_tag('strip');
                    if ($this->_strip_depth++==0) { /* outermost opening {strip} */
                        $this->_additional_newline = "";
                        return '{' . $tag_command . '}';
                    }
                }
                return '';

            case 'php':
                /* handle folded tags replaced by {php} */
                list(, $block) = each($this->_folded_blocks);
                $this->_current_line_no += substr_count($block[0], "\n");
                /* the number of matched elements in the regexp in _compile_file()
                   determins the type of folded tag that was found */
                switch (count($block)) {
                    case 2: /* comment */
                        return '';

                    case 3: /* literal */
                        return "<?php echo '" . strtr($block[2], array("'"=>"\'", "\\"=>"\\\\")) . "'; ?>" . $this->_additional_newline;

                    case 4: /* php */
                        if ($this->security && !$this->security_settings['PHP_TAGS']) {
                            $this->_syntax_error("(secure mode) php tags not permitted", E_USER_WARNING, __FILE__, __LINE__);
                            return;
                        }
                        return '<?php ' . $block[3] .' ?>';
                }
                break;

            case 'insert':
                return $this->_compile_insert_tag($tag_args);

            default:
                if ($this->_compile_compiler_tag($tag_command, $tag_args, $output)) {
                    return $output;
                } else if ($this->_compile_block_tag($tag_command, $tag_args, $tag_modifier, $output)) {
                    return $output;
                } else if ($this->_compile_custom_tag($tag_command, $tag_args, $tag_modifier, $output)) {
                    return $output;                    
                } else {
                    $this->_syntax_error("unrecognized tag '$tag_command'", E_USER_ERROR, __FILE__, __LINE__);
                }

        }
    }


    /**
     * compile the custom compiler tag
     *
     * sets $output to the compiled custom compiler tag
     * @param string $tag_command
     * @param string $tag_args
     * @param string $output
     * @return boolean
     */
    function _compile_compiler_tag($tag_command, $tag_args, &$output)
    {
        $found = false;
        $have_function = true;

        /*
         * First we check if the compiler function has already been registered
         * or loaded from a plugin file.
         */
        if (isset($this->_plugins['compiler'][$tag_command])) {
            $found = true;
            $plugin_func = $this->_plugins['compiler'][$tag_command][0];
            if (!is_callable($plugin_func)) {
                $message = "compiler function '$tag_command' is not implemented";
                $have_function = false;
            }
        }
        /*
         * Otherwise we need to load plugin file and look for the function
         * inside it.
         */
        else if ($plugin_file = $this->_get_plugin_filepath('compiler', $tag_command)) {
            $found = true;

            include_once $plugin_file;

            $plugin_func = 'smarty_compiler_' . $tag_command;
            if (!is_callable($plugin_func)) {
                $message = "plugin function $plugin_func() not found in $plugin_file\n";
                $have_function = false;
            } else {
                $this->_plugins['compiler'][$tag_command] = array($plugin_func, null, null, null, true);
            }
        }

        /*
         * True return value means that we either found a plugin or a
         * dynamically registered function. False means that we didn't and the
         * compiler should now emit code to load custom function plugin for this
         * tag.
         */
        if ($found) {
            if ($have_function) {
                $output = call_user_func_array($plugin_func, array($tag_args, &$this));
                if($output != '') {
                $output = '<?php ' . $this->_push_cacheable_state('compiler', $tag_command)
                                   . $output
                                   . $this->_pop_cacheable_state('compiler', $tag_command) . ' ?>';
                }
            } else {
                $this->_syntax_error($message, E_USER_WARNING, __FILE__, __LINE__);
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * compile block function tag
     *
     * sets $output to compiled block function tag
     * @param string $tag_command
     * @param string $tag_args
     * @param string $tag_modifier
     * @param string $output
     * @return boolean
     */
    function _compile_block_tag($tag_command, $tag_args, $tag_modifier, &$output)
    {
        if (substr($tag_command, 0, 1) == '/') {
            $start_tag = false;
            $tag_command = substr($tag_command, 1);
        } else
            $start_tag = true;

        $found = false;
        $have_function = true;

        /*
         * First we check if the block function has already been registered
         * or loaded from a plugin file.
         */
        if (isset($this->_plugins['block'][$tag_command])) {
            $found = true;
            $plugin_func = $this->_plugins['block'][$tag_command][0];
            if (!is_callable($plugin_func)) {
                $message = "block function '$tag_command' is not implemented";
                $have_function = false;
            }
        }
        /*
         * Otherwise we need to load plugin file and look for the function
         * inside it.
         */
        else if ($plugin_file = $this->_get_plugin_filepath('block', $tag_command)) {
            $found = true;

            include_once $plugin_file;

            $plugin_func = 'smarty_block_' . $tag_command;
            if (!function_exists($plugin_func)) {
                $message = "plugin function $plugin_func() not found in $plugin_file\n";
                $have_function = false;
            } else {
                $this->_plugins['block'][$tag_command] = array($plugin_func, null, null, null, true);

            }
        }

        if (!$found) {
            return false;
        } else if (!$have_function) {
            $this->_syntax_error($message, E_USER_WARNING, __FILE__, __LINE__);
            return true;
        }

        /*
         * Even though we've located the plugin function, compilation
         * happens only once, so the plugin will still need to be loaded
         * at runtime for future requests.
         */
        $this->_add_plugin('block', $tag_command);

        if ($start_tag)
            $this->_push_tag($tag_command);
        else
            $this->_pop_tag($tag_command);

        if ($start_tag) {
            $output = '<?php ' . $this->_push_cacheable_state('block', $tag_command);
            $attrs = $this->_parse_attrs($tag_args);
            $_cache_attrs='';
            $arg_list = $this->_compile_arg_list('block', $tag_command, $attrs, $_cache_attrs);
            $output .= "$_cache_attrs\$this->_tag_stack[] = array('$tag_command', array(".implode(',', $arg_list).')); ';
            $output .= '$_block_repeat=true;' . $this->_compile_plugin_call('block', $tag_command).'($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);';
            $output .= 'while ($_block_repeat) { ob_start(); ?>';
        } else {
            $output = '<?php $_block_content = ob_get_contents(); ob_end_clean(); ';
            $_out_tag_text = $this->_compile_plugin_call('block', $tag_command).'($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat)';
            if ($tag_modifier != '') {
                $this->_parse_modifiers($_out_tag_text, $tag_modifier);
            }
            $output .= '$_block_repeat=false;echo ' . $_out_tag_text . '; } ';
            $output .= " array_pop(\$this->_tag_stack); " . $this->_pop_cacheable_state('block', $tag_command) . '?>';
        }

        return true;
    }


    /**
     * compile custom function tag
     *
     * @param string $tag_command
     * @param string $tag_args
     * @param string $tag_modifier
     * @return string
     */
    function _compile_custom_tag($tag_command, $tag_args, $tag_modifier, &$output)
    {
        $found = false;
        $have_function = true;

        /*
         * First we check if the custom function has already been registered
         * or loaded from a plugin file.
         */
        if (isset($this->_plugins['function'][$tag_command])) {
            $found = true;
            $plugin_func = $this->_plugins['function'][$tag_command][0];
            if (!is_callable($plugin_func)) {
                $message = "custom function '$tag_command' is not implemented";
                $have_function = false;
            }
        }
        /*
         * Otherwise we need to load plugin file and look for the function
         * inside it.
         */
        else if ($plugin_file = $this->_get_plugin_filepath('function', $tag_command)) {
            $found = true;

            include_once $plugin_file;

            $plugin_func = 'smarty_function_' . $tag_command;
            if (!function_exists($plugin_func)) {
                $message = "plugin function $plugin_func() not found in $plugin_file\n";
                $have_function = false;
            } else {
                $this->_plugins['function'][$tag_command] = array($plugin_func, null, null, null, true);

            }
        }

        if (!$found) {
            return false;
        } else if (!$have_function) {
            $this->_syntax_error($message, E_USER_WARNING, __FILE__, __LINE__);
            return true;
        }

        /* declare plugin to be loaded on display of the template that
           we compile right now */
        $this->_add_plugin('function', $tag_command);

        $_cacheable_state = $this->_push_cacheable_state('function', $tag_command);
        $attrs = $this->_parse_attrs($tag_args);
        $_cache_attrs = '';
        $arg_list = $this->_compile_arg_list('function', $tag_command, $attrs, $_cache_attrs);

        $output = $this->_compile_plugin_call('function', $tag_command).'(array('.implode(',', $arg_list)."), \$this)";
        if($tag_modifier != '') {
            $this->_parse_modifiers($output, $tag_modifier);
        }

        if($output != '') {
            $output =  '<?php ' . $_cacheable_state . $_cache_attrs . 'echo ' . $output . ';'
                . $this->_pop_cacheable_state('function', $tag_command) . "?>" . $this->_additional_newline;
        }

        return true;
    }

    /**
     * compile a registered object tag
     *
     * @param string $tag_command
     * @param array $attrs
     * @param string $tag_modifier
     * @return string
     */
    function _compile_registered_object_tag($tag_command, $attrs, $tag_modifier)
    {
        if (substr($tag_command, 0, 1) == '/') {
            $start_tag = false;
            $tag_command = substr($tag_command, 1);
        } else {
            $start_tag = true;
        }

        list($object, $obj_comp) = explode('->', $tag_command);

        $arg_list = array();
        if(count($attrs)) {
            $_assign_var = false;
            foreach ($attrs as $arg_name => $arg_value) {
                if($arg_name == 'assign') {
                    $_assign_var = $arg_value;
                    unset($attrs['assign']);
                    continue;
                }
                if (is_bool($arg_value))
                    $arg_value = $arg_value ? 'true' : 'false';
                $arg_list[] = "'$arg_name' => $arg_value";
            }
        }

        if($this->_reg_objects[$object][2]) {
            // smarty object argument format
            $args = "array(".implode(',', (array)$arg_list)."), \$this";
        } else {
            // traditional argument format
            $args = implode(',', array_values($attrs));
            if (empty($args)) {
                $args = '';
            }
        }

        $prefix = '';
        $postfix = '';
        $newline = '';
        if(!is_object($this->_reg_objects[$object][0])) {
            $this->_trigger_fatal_error("registered '$object' is not an object" , $this->_current_file, $this->_current_line_no, __FILE__, __LINE__);
        } elseif(!empty($this->_reg_objects[$object][1]) && !in_array($obj_comp, $this->_reg_objects[$object][1])) {
            $this->_trigger_fatal_error("'$obj_comp' is not a registered component of object '$object'", $this->_current_file, $this->_current_line_no, __FILE__, __LINE__);
        } elseif(method_exists($this->_reg_objects[$object][0], $obj_comp)) {
            // method
            if(in_array($obj_comp, $this->_reg_objects[$object][3])) {
                // block method
                if ($start_tag) {
                    $prefix = "\$this->_tag_stack[] = array('$obj_comp', $args); ";
                    $prefix .= "\$_block_repeat=true; \$this->_reg_objects['$object'][0]->$obj_comp(\$this->_tag_stack[count(\$this->_tag_stack)-1][1], null, \$this, \$_block_repeat); ";
                    $prefix .= "while (\$_block_repeat) { ob_start();";
                    $return = null;
                    $postfix = '';
                } else {
                    $prefix = "\$_obj_block_content = ob_get_contents(); ob_end_clean(); \$_block_repeat=false;";
                    $return = "\$this->_reg_objects['$object'][0]->$obj_comp(\$this->_tag_stack[count(\$this->_tag_stack)-1][1], \$_obj_block_content, \$this, \$_block_repeat)";
                    $postfix = "} array_pop(\$this->_tag_stack);";
                }
            } else {
                // non-block method
                $return = "\$this->_reg_objects['$object'][0]->$obj_comp($args)";
            }
        } else {
            // property
            $return = "\$this->_reg_objects['$object'][0]->$obj_comp";
        }

        if($return != null) {
            if($tag_modifier != '') {
                $this->_parse_modifiers($return, $tag_modifier);
            }

            if(!empty($_assign_var)) {
                $output = "\$this->assign('" . $this->_dequote($_assign_var) ."',  $return);";
            } else {
                $output = 'echo ' . $return . ';';
                $newline = $this->_additional_newline;
            }
        } else {
            $output = '';
        }

        return '<?php ' . $prefix . $output . $postfix . "?>" . $newline;
    }

    /**
     * Compile {insert ...} tag
     *
     * @param string $tag_args
     * @return string
     */
    function _compile_insert_tag($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);
        $name = $this->_dequote($attrs['name']);

        if (empty($name)) {
            return $this->_syntax_error("missing insert name", E_USER_ERROR, __FILE__, __LINE__);
        }
        
        if (!preg_match('~^\w+$~', $name)) {
            return $this->_syntax_error("'insert: 'name' must be an insert function name", E_USER_ERROR, __FILE__, __LINE__);
        }

        if (!empty($attrs['script'])) {
            $delayed_loading = true;
        } else {
            $delayed_loading = false;
        }

        foreach ($attrs as $arg_name => $arg_value) {
            if (is_bool($arg_value))
                $arg_value = $arg_value ? 'true' : 'false';
            $arg_list[] = "'$arg_name' => $arg_value";
        }

        $this->_add_plugin('insert', $name, $delayed_loading);

        $_params = "array('args' => array(".implode(', ', (array)$arg_list)."))";

        return "<?php require_once(SMARTY_CORE_DIR . 'core.run_insert_handler.php');\necho smarty_core_run_insert_handler($_params, \$this); ?>" . $this->_additional_newline;
    }

    /**
     * Compile {include ...} tag
     *
     * @param string $tag_args
     * @return string
     */
    function _compile_include_tag($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);
        $arg_list = array();

        if (empty($attrs['file'])) {
            $this->_syntax_error("missing 'file' attribute in include tag", E_USER_ERROR, __FILE__, __LINE__);
        }

        foreach ($attrs as $arg_name => $arg_value) {
            if ($arg_name == 'file') {
                $include_file = $arg_value;
                continue;
            } else if ($arg_name == 'assign') {
                $assign_var = $arg_value;
                continue;
            }
            if (is_bool($arg_value))
                $arg_value = $arg_value ? 'true' : 'false';
            $arg_list[] = "'$arg_name' => $arg_value";
        }

        $output = '<?php ';

        if (isset($assign_var)) {
            $output .= "ob_start();\n";
        }

        $output .=
            "\$_smarty_tpl_vars = \$this->_tpl_vars;\n";


        $_params = "array('smarty_include_tpl_file' => " . $include_file . ", 'smarty_include_vars' => array(".implode(',', (array)$arg_list)."))";
        $output .= "\$this->_smarty_include($_params);\n" .
        "\$this->_tpl_vars = \$_smarty_tpl_vars;\n" .
        "unset(\$_smarty_tpl_vars);\n";

        if (isset($assign_var)) {
            $output .= "\$this->assign(" . $assign_var . ", ob_get_contents()); ob_end_clean();\n";
        }

        $output .= ' ?>';

        return $output;

    }

    /**
     * Compile {include ...} tag
     *
     * @param string $tag_args
     * @return string
     */
    function _compile_include_php_tag($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);

        if (empty($attrs['file'])) {
            $this->_syntax_error("missing 'file' attribute in include_php tag", E_USER_ERROR, __FILE__, __LINE__);
        }

        $assign_var = (empty($attrs['assign'])) ? '' : $this->_dequote($attrs['assign']);
        $once_var = (empty($attrs['once']) || $attrs['once']=='false') ? 'false' : 'true';

        $arg_list = array();
        foreach($attrs as $arg_name => $arg_value) {
            if($arg_name != 'file' AND $arg_name != 'once' AND $arg_name != 'assign') {
                if(is_bool($arg_value))
                    $arg_value = $arg_value ? 'true' : 'false';
                $arg_list[] = "'$arg_name' => $arg_value";
            }
        }

        $_params = "array('smarty_file' => " . $attrs['file'] . ", 'smarty_assign' => '$assign_var', 'smarty_once' => $once_var, 'smarty_include_vars' => array(".implode(',', $arg_list)."))";

        return "<?php require_once(SMARTY_CORE_DIR . 'core.smarty_include_php.php');\nsmarty_core_smarty_include_php($_params, \$this); ?>" . $this->_additional_newline;
    }


    /**
     * Compile {section ...} tag
     *
     * @param string $tag_args
     * @return string
     */
    function _compile_section_start($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);
        $arg_list = array();

        $output = '<?php ';
        $section_name = $attrs['name'];
        if (empty($section_name)) {
            $this->_syntax_error("missing section name", E_USER_ERROR, __FILE__, __LINE__);
        }

        $output .= "unset(\$this->_sections[$section_name]);\n";
        $section_props = "\$this->_sections[$section_name]";

        foreach ($attrs as $attr_name => $attr_value) {
            switch ($attr_name) {
                case 'loop':
                    $output .= "{$section_props}['loop'] = is_array(\$_loop=$attr_value) ? count(\$_loop) : max(0, (int)\$_loop); unset(\$_loop);\n";
                    break;

                case 'show':
                    if (is_bool($attr_value))
                        $show_attr_value = $attr_value ? 'true' : 'false';
                    else
                        $show_attr_value = "(bool)$attr_value";
                    $output .= "{$section_props}['show'] = $show_attr_value;\n";
                    break;

                case 'name':
                    $output .= "{$section_props}['$attr_name'] = $attr_value;\n";
                    break;

                case 'max':
                case 'start':
                    $output .= "{$section_props}['$attr_name'] = (int)$attr_value;\n";
                    break;

                case 'step':
                    $output .= "{$section_props}['$attr_name'] = ((int)$attr_value) == 0 ? 1 : (int)$attr_value;\n";
                    break;

                default:
                    $this->_syntax_error("unknown section attribute - '$attr_name'", E_USER_ERROR, __FILE__, __LINE__);
                    break;
            }
        }

        if (!isset($attrs['show']))
            $output .= "{$section_props}['show'] = true;\n";

        if (!isset($attrs['loop']))
            $output .= "{$section_props}['loop'] = 1;\n";

        if (!isset($attrs['max']))
            $output .= "{$section_props}['max'] = {$section_props}['loop'];\n";
        else
            $output .= "if ({$section_props}['max'] < 0)\n" .
                       "    {$section_props}['max'] = {$section_props}['loop'];\n";

        if (!isset($attrs['step']))
            $output .= "{$section_props}['step'] = 1;\n";

        if (!isset($attrs['start']))
            $output .= "{$section_props}['start'] = {$section_props}['step'] > 0 ? 0 : {$section_props}['loop']-1;\n";
        else {
            $output .= "if ({$section_props}['start'] < 0)\n" .
                       "    {$section_props}['start'] = max({$section_props}['step'] > 0 ? 0 : -1, {$section_props}['loop'] + {$section_props}['start']);\n" .
                       "else\n" .
                       "    {$section_props}['start'] = min({$section_props}['start'], {$section_props}['step'] > 0 ? {$section_props}['loop'] : {$section_props}['loop']-1);\n";
        }

        $output .= "if ({$section_props}['show']) {\n";
        if (!isset($attrs['start']) && !isset($attrs['step']) && !isset($attrs['max'])) {
            $output .= "    {$section_props}['total'] = {$section_props}['loop'];\n";
        } else {
            $output .= "    {$section_props}['total'] = min(ceil(({$section_props}['step'] > 0 ? {$section_props}['loop'] - {$section_props}['start'] : {$section_props}['start']+1)/abs({$section_props}['step'])), {$section_props}['max']);\n";
        }
        $output .= "    if ({$section_props}['total'] == 0)\n" .
                   "        {$section_props}['show'] = false;\n" .
                   "} else\n" .
                   "    {$section_props}['total'] = 0;\n";

        $output .= "if ({$section_props}['show']):\n";
        $output .= "
            for ({$section_props}['index'] = {$section_props}['start'], {$section_props}['iteration'] = 1;
                 {$section_props}['iteration'] <= {$section_props}['total'];
                 {$section_props}['index'] += {$section_props}['step'], {$section_props}['iteration']++):\n";
        $output .= "{$section_props}['rownum'] = {$section_props}['iteration'];\n";
        $output .= "{$section_props}['index_prev'] = {$section_props}['index'] - {$section_props}['step'];\n";
        $output .= "{$section_props}['index_next'] = {$section_props}['index'] + {$section_props}['step'];\n";
        $output .= "{$section_props}['first']      = ({$section_props}['iteration'] == 1);\n";
        $output .= "{$section_props}['last']       = ({$section_props}['iteration'] == {$section_props}['total']);\n";

        $output .= "?>";

        return $output;
    }


    /**
     * Compile {foreach ...} tag.
     *
     * @param string $tag_args
     * @return string
     */
    function _compile_foreach_start($tag_args)
    {
        $attrs = $this->_parse_attrs($tag_args);
        $arg_list = array();

        if (empty($attrs['from'])) {
            return $this->_syntax_error("foreach: missing 'from' attribute", E_USER_ERROR, __FILE__, __LINE__);
        }
        $from = $attrs['from'];

        if (empty($attrs['item'])) {
            return $this->_syntax_error("foreach: missing 'item' attribute", E_USER_ERROR, __FILE__, __LINE__);
        }
        $item = $this->_dequote($attrs['item']);
        if (!preg_match('~^\w+$~', $item)) {
            return $this->_syntax_error("foreach: 'item' must be a variable name (literal string)", E_USER_ERROR, __FILE__, __LINE__);
        }

        if (isset($attrs['key'])) {
            $key  = $this->_dequote($attrs['key']);
            if (!preg_match('~^\w+$~', $key)) {
                return $this->_syntax_error("foreach: 'key' must to be a variable name (literal string)", E_USER_ERROR, __FILE__, __LINE__);
            }
            $key_part = "\$this->_tpl_vars['$key'] => ";
        } else {
            $key = null;
            $key_part = '';
        }

        if (isset($attrs['name'])) {
            $name = $attrs['name'];
        } else {
            $name = null;
        }

        $output = '<?php ';
        $output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }";
        if (isset($name)) {
            $foreach_props = "\$this->_foreach[$name]";
            $output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
            $output .= "if ({$foreach_props}['total'] > 0):\n";
            $output .= "    foreach (\$_from as $key_part\$this->_tpl_vars['$item']):\n";
            $output .= "        {$foreach_props}['iteration']++;\n";
        } else {
            $output .= "if (count(\$_from)):\n";
            $output .= "    foreach (\$_from as $key_part\$this->_tpl_vars['$item']):\n";
        }
        $output .= '?>';

        return $output;
    }


    /**
     * Compile {capture} .. {/capture} tags
     *
     * @param boolean $start true if this is the {capture} tag
     * @param string $tag_args
     * @return string
     */

    function _compile_capture_tag($start, $tag_args = '')
    {
        $attrs = $this->_parse_attrs($tag_args);

        if ($start) {
            $buffer = isset($attrs['name']) ? $attrs['name'] : "'default'";
            $assign = isset($attrs['assign']) ? $attrs['assign'] : null;
            $append = isset($attrs['append']) ? $attrs['append'] : null;
            
            $output = "<?php ob_start(); ?>";
            $this->_capture_stack[] = array($buffer, $assign, $append);
        } else {
            list($buffer, $assign, $append) = array_pop($this->_capture_stack);
            $output = "<?php \$this->_smarty_vars['capture'][$buffer] = ob_get_contents(); ";
            if (isset($assign)) {
                $output .= " \$this->assign($assign, ob_get_contents());";
            }
            if (isset($append)) {
                $output .= " \$this->append($append, ob_get_contents());";
            }
            $output .= "ob_end_clean(); ?>";
        }

        return $output;
    }

    /**
     * Compile {if ...} tag
     *
     * @param string $tag_args
     * @param boolean $elseif if true, uses elseif instead of if
     * @return string
     */
    function _compile_if_tag($tag_args, $elseif = false)
    {

        /* Tokenize args for 'if' tag. */
        preg_match_all('~(?>
                ' . $this->_obj_call_regexp . '(?:' . $this->_mod_regexp . '*)? | # valid object call
                ' . $this->_var_regexp . '(?:' . $this->_mod_regexp . '*)?    | # var or quoted string
                \-?0[xX][0-9a-fA-F]+|\-?\d+(?:\.\d+)?|\.\d+|!==|===|==|!=|<>|<<|>>|<=|>=|\&\&|\|\||\(|\)|,|\!|\^|=|\&|\~|<|>|\||\%|\+|\-|\/|\*|\@    | # valid non-word token
                \b\w+\b                                                        | # valid word token
                \S+                                                           # anything else
                )~x', $tag_args, $match);

        $tokens = $match[0];

        if(empty($tokens)) {
            $_error_msg = $elseif ? "'elseif'" : "'if'";
            $_error_msg .= ' statement requires arguments'; 
            $this->_syntax_error($_error_msg, E_USER_ERROR, __FILE__, __LINE__);
        }
            
                
        // make sure we have balanced parenthesis
        $token_count = array_count_values($tokens);
        if(isset($token_count['(']) && $token_count['('] != $token_count[')']) {
            $this->_syntax_error("unbalanced parenthesis in if statement", E_USER_ERROR, __FILE__, __LINE__);
        }

        $is_arg_stack = array();

        for ($i = 0; $i < count($tokens); $i++) {

            $token = &$tokens[$i];

            switch (strtolower($token)) {
                case '!':
                case '%':
                case '!==':
                case '==':
                case '===':
                case '>':
                case '<':
                case '!=':
                case '<>':
                case '<<':
                case '>>':
                case '<=':
                case '>=':
                case '&&':
                case '||':
                case '|':
                case '^':
                case '&':
                case '~':
                case ')':
                case ',':
                case '+':
                case '-':
                case '*':
                case '/':
                case '@':
                    break;

                case 'eq':
                    $token = '==';
                    break;

                case 'ne':
                case 'neq':
                    $token = '!=';
                    break;

                case 'lt':
                    $token = '<';
                    break;

                case 'le':
                case 'lte':
                    $token = '<=';
                    break;

                case 'gt':
                    $token = '>';
                    break;

                case 'ge':
                case 'gte':
                    $token = '>=';
                    break;

                case 'and':
                    $token = '&&';
                    break;

                case 'or':
                    $token = '||';
                    break;

                case 'not':
                    $token = '!';
                    break;

                case 'mod':
                    $token = '%';
                    break;

                case '(':
                    array_push($is_arg_stack, $i);
                    break;

                case 'is':
                    /* If last token was a ')', we operate on the parenthesized
                       expression. The start of the expression is on the stack.
                       Otherwise, we operate on the last encountered token. */
                    if ($tokens[$i-1] == ')') {
                        $is_arg_start = array_pop($is_arg_stack);
                        if ($is_arg_start != 0) {
                            if (preg_match('~^' . $this->_func_regexp . '$~', $tokens[$is_arg_start-1])) {
                                $is_arg_start--;
                            } 
                        } 
                    } else
                        $is_arg_start = $i-1;
                    /* Construct the argument for 'is' expression, so it knows
                       what to operate on. */
                    $is_arg = implode(' ', array_slice($tokens, $is_arg_start, $i - $is_arg_start));

                    /* Pass all tokens from next one until the end to the
                       'is' expression parsing function. The function will
                       return modified tokens, where the first one is the result
                       of the 'is' expression and the rest are the tokens it
                       didn't touch. */
                    $new_tokens = $this->_parse_is_expr($is_arg, array_slice($tokens, $i+1));

                    /* Replace the old tokens with the new ones. */
                    array_splice($tokens, $is_arg_start, count($tokens), $new_tokens);

                    /* Adjust argument start so that it won't change from the
                       current position for the next iteration. */
                    $i = $is_arg_start;
                    break;

                default:
                    if(preg_match('~^' . $this->_func_regexp . '$~', $token) ) {
                            // function call
                            if($this->security &&
                               !in_array($token, $this->security_settings['IF_FUNCS'])) {
                                $this->_syntax_error("(secure mode) '$token' not allowed in if statement", E_USER_ERROR, __FILE__, __LINE__);
                            }
                    } elseif(preg_match('~^' . $this->_var_regexp . '$~', $token) && (strpos('+-*/^%&|', substr($token, -1)) === false) && isset($tokens[$i+1]) && $tokens[$i+1] == '(') {
                        // variable function call
                        $this->_syntax_error("variable function call '$token' not allowed in if statement", E_USER_ERROR, __FILE__, __LINE__);                      
                    } elseif(preg_match('~^' . $this->_obj_call_regexp . '|' . $this->_var_regexp . '(?:' . $this->_mod_regexp . '*)$~', $token)) {
                        // object or variable
                        $token = $this->_parse_var_props($token);
                    } elseif(is_numeric($token)) {
                        // number, skip it
                    } else {
                        $this->_syntax_error("unidentified token '$token'", E_USER_ERROR, __FILE__, __LINE__);
                    }
                    break;
            }
        }

        if ($elseif)
            return '<?php elseif ('.implode(' ', $tokens).'): ?>';
        else
            return '<?php if ('.implode(' ', $tokens).'): ?>';
    }


    function _compile_arg_list($type, $name, $attrs, &$cache_code) {
        $arg_list = array();

        if (isset($type) && isset($name)
            && isset($this->_plugins[$type])
            && isset($this->_plugins[$type][$name])
            && empty($this->_plugins[$type][$name][4])
            && is_array($this->_plugins[$type][$name][5])
            ) {
            /* we have a list of parameters that should be cached */
            $_cache_attrs = $this->_plugins[$type][$name][5];
            $_count = $this->_cache_attrs_count++;
            $cache_code = "\$_cache_attrs =& \$this->_smarty_cache_attrs('$this->_cache_serial','$_count');";

        } else {
            /* no parameters are cached */
            $_cache_attrs = null;
        }

        foreach ($attrs as $arg_name => $arg_value) {
            if (is_bool($arg_value))
                $arg_value = $arg_value ? 'true' : 'false';
            if (is_null($arg_value))
                $arg_value = 'null';
            if ($_cache_attrs && in_array($arg_name, $_cache_attrs)) {
                $arg_list[] = "'$arg_name' => (\$this->_cache_including) ? \$_cache_attrs['$arg_name'] : (\$_cache_attrs['$arg_name']=$arg_value)";
            } else {
                $arg_list[] = "'$arg_name' => $arg_value";
            }
        }
        return $arg_list;
    }

    /**
     * Parse is expression
     *
     * @param string $is_arg
     * @param array $tokens
     * @return array
     */
    function _parse_is_expr($is_arg, $tokens)
    {
        $expr_end = 0;
        $negate_expr = false;

        if (($first_token = array_shift($tokens)) == 'not') {
            $negate_expr = true;
            $expr_type = array_shift($tokens);
        } else
            $expr_type = $first_token;

        switch ($expr_type) {
            case 'even':
                if (isset($tokens[$expr_end]) && $tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "!(1 & ($is_arg / " . $this->_parse_var_props($expr_arg) . "))";
                } else
                    $expr = "!(1 & $is_arg)";
                break;

            case 'odd':
                if (isset($tokens[$expr_end]) && $tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "(1 & ($is_arg / " . $this->_parse_var_props($expr_arg) . "))";
                } else
                    $expr = "(1 & $is_arg)";
                break;

            case 'div':
                if (@$tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "!($is_arg % " . $this->_parse_var_props($expr_arg) . ")";
                } else {
                    $this->_syntax_error("expecting 'by' after 'div'", E_USER_ERROR, __FILE__, __LINE__);
                }
                break;

            default:
                $this->_syntax_error("unknown 'is' expression - '$expr_type'", E_USER_ERROR, __FILE__, __LINE__);
                break;
        }

        if ($negate_expr) {
            $expr = "!($expr)";
        }

        array_splice($tokens, 0, $expr_end, $expr);

        return $tokens;
    }


    /**
     * Parse attribute string
     *
     * @param string $tag_args
     * @return array
     */
    function _parse_attrs($tag_args)
    {

        /* Tokenize tag attributes. */
        preg_match_all('~(?:' . $this->_obj_call_regexp . '|' . $this->_qstr_regexp . ' | (?>[^"\'=\s]+)
                         )+ |
                         [=]
                        ~x', $tag_args, $match);
        $tokens       = $match[0];

        $attrs = array();
        /* Parse state:
            0 - expecting attribute name
            1 - expecting '='
            2 - expecting attribute value (not '=') */
        $state = 0;

        foreach ($tokens as $token) {
            switch ($state) {
                case 0:
                    /* If the token is a valid identifier, we set attribute name
                       and go to state 1. */
                    if (preg_match('~^\w+$~', $token)) {
                        $attr_name = $token;
                        $state = 1;
                    } else
                        $this->_syntax_error("invalid attribute name: '$token'", E_USER_ERROR, __FILE__, __LINE__);
                    break;

                case 1:
                    /* If the token is '=', then we go to state 2. */
                    if ($token == '=') {
                        $state = 2;
                    } else
                        $this->_syntax_error("expecting '=' after attribute name '$last_token'", E_USER_ERROR, __FILE__, __LINE__);
                    break;

                case 2:
                    /* If token is not '=', we set the attribute value and go to
                       state 0. */
                    if ($token != '=') {
                        /* We booleanize the token if it's a non-quoted possible
                           boolean value. */
                        if (preg_match('~^(on|yes|true)$~', $token)) {
                            $token = 'true';
                        } else if (preg_match('~^(off|no|false)$~', $token)) {
                            $token = 'false';
                        } else if ($token == 'null') {
                            $token = 'null';
                        } else if (preg_match('~^' . $this->_num_const_regexp . '|0[xX][0-9a-fA-F]+$~', $token)) {
                            /* treat integer literally */
                        } else if (!preg_match('~^' . $this->_obj_call_regexp . '|' . $this->_var_regexp . '(?:' . $this->_mod_regexp . ')*$~', $token)) {
                            /* treat as a string, double-quote it escaping quotes */
                            $token = '"'.addslashes($token).'"';
                        }

                        $attrs[$attr_name] = $token;
                        $state = 0;
                    } else
                        $this->_syntax_error("'=' cannot be an attribute value", E_USER_ERROR, __FILE__, __LINE__);
                    break;
            }
            $last_token = $token;
        }

        if($state != 0) {
            if($state == 1) {
                $this->_syntax_error("expecting '=' after attribute name '$last_token'", E_USER_ERROR, __FILE__, __LINE__);
            } else {
                $this->_syntax_error("missing attribute value", E_USER_ERROR, __FILE__, __LINE__);
            }
        }

        $this->_parse_vars_props($attrs);

        return $attrs;
    }

    /**
     * compile multiple variables and section properties tokens into
     * PHP code
     *
     * @param array $tokens
     */
    function _parse_vars_props(&$tokens)
    {
        foreach($tokens as $key => $val) {
            $tokens[$key] = $this->_parse_var_props($val);
        }
    }

    /**
     * compile single variable and section properties token into
     * PHP code
     *
     * @param string $val
     * @param string $tag_attrs
     * @return string
     */
    function _parse_var_props($val)
    {
        $val = trim($val);

        if(preg_match('~^(' . $this->_obj_call_regexp . '|' . $this->_dvar_regexp . ')(' . $this->_mod_regexp . '*)$~', $val, $match)) {
            // $ variable or object
            $return = $this->_parse_var($match[1]);
            $modifiers = $match[2];
            if (!empty($this->default_modifiers) && !preg_match('~(^|\|)smarty:nodefaults($|\|)~',$modifiers)) {
                $_default_mod_string = implode('|',(array)$this->default_modifiers);
                $modifiers = empty($modifiers) ? $_default_mod_string : $_default_mod_string . '|' . $modifiers;
            }
            $this->_parse_modifiers($return, $modifiers);
            return $return;
        } elseif (preg_match('~^' . $this->_db_qstr_regexp . '(?:' . $this->_mod_regexp . '*)$~', $val)) {
                // double quoted text
                preg_match('~^(' . $this->_db_qstr_regexp . ')('. $this->_mod_regexp . '*)$~', $val, $match);
                $return = $this->_expand_quoted_text($match[1]);
                if($match[2] != '') {
                    $this->_parse_modifiers($return, $match[2]);
                }
                return $return;
            }
        elseif(preg_match('~^' . $this->_num_const_regexp . '(?:' . $this->_mod_regexp . '*)$~', $val)) {
                // numerical constant
                preg_match('~^(' . $this->_num_const_regexp . ')('. $this->_mod_regexp . '*)$~', $val, $match);
                if($match[2] != '') {
                    $this->_parse_modifiers($match[1], $match[2]);
                    return $match[1];
                }
            }
        elseif(preg_match('~^' . $this->_si_qstr_regexp . '(?:' . $this->_mod_regexp . '*)$~', $val)) {
                // single quoted text
                preg_match('~^(' . $this->_si_qstr_regexp . ')('. $this->_mod_regexp . '*)$~', $val, $match);
                if($match[2] != '') {
                    $this->_parse_modifiers($match[1], $match[2]);
                    return $match[1];
                }
            }
        elseif(preg_match('~^' . $this->_cvar_regexp . '(?:' . $this->_mod_regexp . '*)$~', $val)) {
                // config var
                return $this->_parse_conf_var($val);
            }
        elseif(preg_match('~^' . $this->_svar_regexp . '(?:' . $this->_mod_regexp . '*)$~', $val)) {
                // section var
                return $this->_parse_section_prop($val);
            }
        elseif(!in_array($val, $this->_permitted_tokens) && !is_numeric($val)) {
            // literal string
            return $this->_expand_quoted_text('"' . strtr($val, array('\\' => '\\\\', '"' => '\\"')) .'"');
        }
        return $val;
    }

    /**
     * expand quoted text with embedded variables
     *
     * @param string $var_expr
     * @return string
     */
    function _expand_quoted_text($var_expr)
    {
        // if contains unescaped $, expand it
        if(preg_match_all('~(?:\`(?<!\\\\)\$' . $this->_dvar_guts_regexp . '(?:' . $this->_obj_ext_regexp . ')*\`)|(?:(?<!\\\\)\$\w+(\[[a-zA-Z0-9]+\])*)~', $var_expr, $_match)) {
            $_match = $_match[0];
            $_replace = array();
            foreach($_match as $_var) {
                $_replace[$_var] = '".(' . $this->_parse_var(str_replace('`','',$_var)) . ')."';
            }
            $var_expr = strtr($var_expr, $_replace);
            $_return = preg_replace('~\.""|(?<!\\\\)""\.~', '', $var_expr);
        } else {
            $_return = $var_expr;
        }
        // replace double quoted literal string with single quotes
        $_return = preg_replace('~^"([\s\w]+)"$~',"'\\1'",$_return);
        return $_return;
    }

    /**
     * parse variable expression into PHP code
     *
     * @param string $var_expr
     * @param string $output
     * @return string
     */
    function _parse_var($var_expr)
    {
        $_has_math = false;
        $_math_vars = preg_split('~('.$this->_dvar_math_regexp.'|'.$this->_qstr_regexp.')~', $var_expr, -1, PREG_SPLIT_DELIM_CAPTURE);

        if(count($_math_vars) > 1) {
            $_first_var = "";
            $_complete_var = "";
            $_output = "";
            // simple check if there is any math, to stop recursion (due to modifiers with "xx % yy" as parameter)
            foreach($_math_vars as $_k => $_math_var) {
                $_math_var = $_math_vars[$_k];

                if(!empty($_math_var) || is_numeric($_math_var)) {
                    // hit a math operator, so process the stuff which came before it
                    if(preg_match('~^' . $this->_dvar_math_regexp . '$~', $_math_var)) {
                        $_has_math = true;
                        if(!empty($_complete_var) || is_numeric($_complete_var)) {
                            $_output .= $this->_parse_var($_complete_var);
                        }

                        // just output the math operator to php
                        $_output .= $_math_var;

                        if(empty($_first_var))
                            $_first_var = $_complete_var;

                        $_complete_var = "";
                    } else {
                        $_complete_var .= $_math_var;
                    }
                }
            }
            if($_has_math) {
                if(!empty($_complete_var) || is_numeric($_complete_var))
                    $_output .= $this->_parse_var($_complete_var);

                // get the modifiers working (only the last var from math + modifier is left)
                $var_expr = $_complete_var;
            }
        }

        // prevent cutting of first digit in the number (we _definitly_ got a number if the first char is a digit)
        if(is_numeric(substr($var_expr, 0, 1)))
            $_var_ref = $var_expr;
        else
            $_var_ref = substr($var_expr, 1);
        
        if(!$_has_math) {
            
            // get [foo] and .foo and ->foo and (...) pieces
            preg_match_all('~(?:^\w+)|' . $this->_obj_params_regexp . '|(?:' . $this->_var_bracket_regexp . ')|->\$?\w+|\.\$?\w+|\S+~', $_var_ref, $match);
                        
            $_indexes = $match[0];
            $_var_name = array_shift($_indexes);

            /* Handle $smarty.* variable references as a special case. */
            if ($_var_name == 'smarty') {
                /*
                 * If the reference could be compiled, use the compiled output;
                 * otherwise, fall back on the $smarty variable generated at
                 * run-time.
                 */
                if (($smarty_ref = $this->_compile_smarty_ref($_indexes)) !== null) {
                    $_output = $smarty_ref;
                } else {
                    $_var_name = substr(array_shift($_indexes), 1);
                    $_output = "\$this->_smarty_vars['$_var_name']";
                }
            } elseif(is_numeric($_var_name) && is_numeric(substr($var_expr, 0, 1))) {
                // because . is the operator for accessing arrays thru inidizes we need to put it together again for floating point numbers
                if(count($_indexes) > 0)
                {
                    $_var_name .= implode("", $_indexes);
                    $_indexes = array();
                }
                $_output = $_var_name;
            } else {
                $_output = "\$this->_tpl_vars['$_var_name']";
            }

            foreach ($_indexes as $_index) {
                if (substr($_index, 0, 1) == '[') {
                    $_index = substr($_index, 1, -1);
                    if (is_numeric($_index)) {
                        $_output .= "[$_index]";
                    } elseif (substr($_index, 0, 1) == '$') {
                        if (strpos($_index, '.') !== false) {
                            $_output .= '[' . $this->_parse_var($_index) . ']';
                        } else {
                            $_output .= "[\$this->_tpl_vars['" . substr($_index, 1) . "']]";
                        }
                    } else {
                        $_var_parts = explode('.', $_index);
                        $_var_section = $_var_parts[0];
                        $_var_section_prop = isset($_var_parts[1]) ? $_var_parts[1] : 'index';
                        $_output .= "[\$this->_sections['$_var_section']['$_var_section_prop']]";
                    }
                } else if (substr($_index, 0, 1) == '.') {
                    if (substr($_index, 1, 1) == '$')
                        $_output .= "[\$this->_tpl_vars['" . substr($_index, 2) . "']]";
                    else
                        $_output .= "['" . substr($_index, 1) . "']";
                } else if (substr($_index,0,2) == '->') {
                    if(substr($_index,2,2) == '__') {
                        $this->_syntax_error('call to internal object members is not allowed', E_USER_ERROR, __FILE__, __LINE__);
                    } elseif($this->security && substr($_index, 2, 1) == '_') {
                        $this->_syntax_error('(secure) call to private object member is not allowed', E_USER_ERROR, __FILE__, __LINE__);
                    } elseif (substr($_index, 2, 1) == '$') {
                        if ($this->security) {
                            $this->_syntax_error('(secure) call to dynamic object member is not allowed', E_USER_ERROR, __FILE__, __LINE__);
                        } else {
                            $_output .= '->{(($_var=$this->_tpl_vars[\''.substr($_index,3).'\']) && substr($_var,0,2)!=\'__\') ? $_var : $this->trigger_error("cannot access property \\"$_var\\"")}';
                        }
                    } else {
                        $_output .= $_index;
                    }
                } elseif (substr($_index, 0, 1) == '(') {
                    $_index = $this->_parse_parenth_args($_index);
                    $_output .= $_index;
                } else {
                    $_output .= $_index;
                }
            }
        }

        return $_output;
    }

    /**
     * parse arguments in function call parenthesis
     *
     * @param string $parenth_args
     * @return string
     */
    function _parse_parenth_args($parenth_args)
    {
        preg_match_all('~' . $this->_param_regexp . '~',$parenth_args, $match);
        $orig_vals = $match = $match[0];
        $this->_parse_vars_props($match);
        $replace = array();
        for ($i = 0, $count = count($match); $i < $count; $i++) {
            $replace[$orig_vals[$i]] = $match[$i];
        }
        return strtr($parenth_args, $replace);
    }

    /**
     * parse configuration variable expression into PHP code
     *
     * @param string $conf_var_expr
     */
    function _parse_conf_var($conf_var_expr)
    {
        $parts = explode('|', $conf_var_expr, 2);
        $var_ref = $parts[0];
        $modifiers = isset($parts[1]) ? $parts[1] : '';

        $var_name = substr($var_ref, 1, -1);

        $output = "\$this->_config[0]['vars']['$var_name']";

        $this->_parse_modifiers($output, $modifiers);

        return $output;
    }

    /**
     * parse section property expression into PHP code
     *
     * @param string $section_prop_expr
     * @return string
     */
    function _parse_section_prop($section_prop_expr)
    {
        $parts = explode('|', $section_prop_expr, 2);
        $var_ref = $parts[0];
        $modifiers = isset($parts[1]) ? $parts[1] : '';

        preg_match('!%(\w+)\.(\w+)%!', $var_ref, $match);
        $section_name = $match[1];
        $prop_name = $match[2];

        $output = "\$this->_sections['$section_name']['$prop_name']";

        $this->_parse_modifiers($output, $modifiers);

        return $output;
    }


    /**
     * parse modifier chain into PHP code
     *
     * sets $output to parsed modified chain
     * @param string $output
     * @param string $modifier_string
     */
    function _parse_modifiers(&$output, $modifier_string)
    {
        preg_match_all('~\|(@?\w+)((?>:(?:'. $this->_qstr_regexp . '|[^|]+))*)~', '|' . $modifier_string, $_match);
        list(, $_modifiers, $modifier_arg_strings) = $_match;

        for ($_i = 0, $_for_max = count($_modifiers); $_i < $_for_max; $_i++) {
            $_modifier_name = $_modifiers[$_i];

            if($_modifier_name == 'smarty') {
                // skip smarty modifier
                continue;
            }

            preg_match_all('~:(' . $this->_qstr_regexp . '|[^:]+)~', $modifier_arg_strings[$_i], $_match);
            $_modifier_args = $_match[1];

            if (substr($_modifier_name, 0, 1) == '@') {
                $_map_array = false;
                $_modifier_name = substr($_modifier_name, 1);
            } else {
                $_map_array = true;
            }

            if (empty($this->_plugins['modifier'][$_modifier_name])
                && !$this->_get_plugin_filepath('modifier', $_modifier_name)
                && function_exists($_modifier_name)) {
                if ($this->security && !in_array($_modifier_name, $this->security_settings['MODIFIER_FUNCS'])) {
                    $this->_trigger_fatal_error("[plugin] (secure mode) modifier '$_modifier_name' is not allowed" , $this->_current_file, $this->_current_line_no, __FILE__, __LINE__);
                } else {
                    $this->_plugins['modifier'][$_modifier_name] = array($_modifier_name,  null, null, false);
                }
            }
            $this->_add_plugin('modifier', $_modifier_name);

            $this->_parse_vars_props($_modifier_args);

            if($_modifier_name == 'default') {
                // supress notifications of default modifier vars and args
                if(substr($output, 0, 1) == '$') {
                    $output = '@' . $output;
                }
                if(isset($_modifier_args[0]) && substr($_modifier_args[0], 0, 1) == '$') {
                    $_modifier_args[0] = '@' . $_modifier_args[0];
                }
            }
            if (count($_modifier_args) > 0)
                $_modifier_args = ', '.implode(', ', $_modifier_args);
            else
                $_modifier_args = '';

            if ($_map_array) {
                $output = "((is_array(\$_tmp=$output)) ? \$this->_run_mod_handler('$_modifier_name', true, \$_tmp$_modifier_args) : " . $this->_compile_plugin_call('modifier', $_modifier_name) . "(\$_tmp$_modifier_args))";

            } else {

                $output = $this->_compile_plugin_call('modifier', $_modifier_name)."($output$_modifier_args)";

            }
        }
    }


    /**
     * add plugin
     *
     * @param string $type
     * @param string $name
     * @param boolean? $delayed_loading
     */
    function _add_plugin($type, $name, $delayed_loading = null)
    {
        if (!isset($this->_plugin_info[$type])) {
            $this->_plugin_info[$type] = array();
        }
        if (!isset($this->_plugin_info[$type][$name])) {
            $this->_plugin_info[$type][$name] = array($this->_current_file,
                                                      $this->_current_line_no,
                                                      $delayed_loading);
        }
    }


    /**
     * Compiles references of type $smarty.foo
     *
     * @param string $indexes
     * @return string
     */
    function _compile_smarty_ref(&$indexes)
    {
        /* Extract the reference name. */
        $_ref = substr($indexes[0], 1);
        foreach($indexes as $_index_no=>$_index) {
            if (substr($_index, 0, 1) != '.' && $_index_no<2 || !preg_match('~^(\.|\[|->)~', $_index)) {
                $this->_syntax_error('$smarty' . implode('', array_slice($indexes, 0, 2)) . ' is an invalid reference', E_USER_ERROR, __FILE__, __LINE__);
            }
        }

        switch ($_ref) {
            case 'now':
                $compiled_ref = 'time()';
                $_max_index = 1;
                break;

            case 'foreach':
                array_shift($indexes);
                $_var = $this->_parse_var_props(substr($indexes[0], 1));
                $_propname = substr($indexes[1], 1);
                $_max_index = 1;
                switch ($_propname) {
                    case 'index':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach[$_var]['iteration']-1)";
                        break;
                        
                    case 'first':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach[$_var]['iteration'] <= 1)";
                        break;

                    case 'last':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach[$_var]['iteration'] == \$this->_foreach[$_var]['total'])";
                        break;
                        
                    case 'show':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach[$_var]['total'] > 0)";
                        break;
                        
                    default:
                        unset($_max_index);
                        $compiled_ref = "\$this->_foreach[$_var]";
                }
                break;

            case 'section':
                array_shift($indexes);
                $_var = $this->_parse_var_props(substr($indexes[0], 1));
                $compiled_ref = "\$this->_sections[$_var]";
                break;

            case 'get':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_GET";
                break;

            case 'post':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_POST";
                break;

            case 'cookies':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_COOKIE";
                break;

            case 'env':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_ENV";
                break;

            case 'server':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_SERVER";
                break;

            case 'session':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                $compiled_ref = "\$_SESSION";
                break;

            /*
             * These cases are handled either at run-time or elsewhere in the
             * compiler.
             */
            case 'request':
                if ($this->security && !$this->security_settings['ALLOW_SUPER_GLOBALS']) {
                    $this->_syntax_error("(secure mode) super global access not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                if ($this->request_use_auto_globals) {
                    $compiled_ref = "\$_REQUEST";
                    break;
                } else {
                    $this->_init_smarty_vars = true;
                }
                return null;

            case 'capture':
                return null;

            case 'template':
                $compiled_ref = "'$this->_current_file'";
                $_max_index = 1;
                break;

            case 'version':
                $compiled_ref = "'$this->_version'";
                $_max_index = 1;
                break;

            case 'const':
                if ($this->security && !$this->security_settings['ALLOW_CONSTANTS']) {
                    $this->_syntax_error("(secure mode) constants not permitted",
                                         E_USER_WARNING, __FILE__, __LINE__);
                    return;
                }
                array_shift($indexes);
                if (preg_match('!^\.\w+$!', $indexes[0])) {
                    $compiled_ref = '@' . substr($indexes[0], 1);
                } else {
                    $_val = $this->_parse_var_props(substr($indexes[0], 1));
                    $compiled_ref = '@constant(' . $_val . ')';
                }
                $_max_index = 1;
                break;

            case 'config':
                $compiled_ref = "\$this->_config[0]['vars']";
                $_max_index = 3;
                break;

            case 'ldelim':
                $compiled_ref = "'$this->left_delimiter'";
                break;

            case 'rdelim':
                $compiled_ref = "'$this->right_delimiter'";
                break;
                
            default:
                $this->_syntax_error('$smarty.' . $_ref . ' is an unknown reference', E_USER_ERROR, __FILE__, __LINE__);
                break;
        }

        if (isset($_max_index) && count($indexes) > $_max_index) {
            $this->_syntax_error('$smarty' . implode('', $indexes) .' is an invalid reference', E_USER_ERROR, __FILE__, __LINE__);
        }

        array_shift($indexes);
        return $compiled_ref;
    }

    /**
     * compiles call to plugin of type $type with name $name
     * returns a string containing the function-name or method call
     * without the paramter-list that would have follow to make the
     * call valid php-syntax
     *
     * @param string $type
     * @param string $name
     * @return string
     */
    function _compile_plugin_call($type, $name) {
        if (isset($this->_plugins[$type][$name])) {
            /* plugin loaded */
            if (is_array($this->_plugins[$type][$name][0])) {
                return ((is_object($this->_plugins[$type][$name][0][0])) ?
                        "\$this->_plugins['$type']['$name'][0][0]->"    /* method callback */
                        : (string)($this->_plugins[$type][$name][0][0]).'::'    /* class callback */
                       ). $this->_plugins[$type][$name][0][1];

            } else {
                /* function callback */
                return $this->_plugins[$type][$name][0];

            }
        } else {
            /* plugin not loaded -> auto-loadable-plugin */
            return 'smarty_'.$type.'_'.$name;

        }
    }

    /**
     * load pre- and post-filters
     */
    function _load_filters()
    {
        if (count($this->_plugins['prefilter']) > 0) {
            foreach ($this->_plugins['prefilter'] as $filter_name => $prefilter) {
                if ($prefilter === false) {
                    unset($this->_plugins['prefilter'][$filter_name]);
                    $_params = array('plugins' => array(array('prefilter', $filter_name, null, null, false)));
                    require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
                    smarty_core_load_plugins($_params, $this);
                }
            }
        }
        if (count($this->_plugins['postfilter']) > 0) {
            foreach ($this->_plugins['postfilter'] as $filter_name => $postfilter) {
                if ($postfilter === false) {
                    unset($this->_plugins['postfilter'][$filter_name]);
                    $_params = array('plugins' => array(array('postfilter', $filter_name, null, null, false)));
                    require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
                    smarty_core_load_plugins($_params, $this);
                }
            }
        }
    }


    /**
     * Quote subpattern references
     *
     * @param string $string
     * @return string
     */
    function _quote_replace($string)
    {
        return strtr($string, array('\\' => '\\\\', '$' => '\\$'));
    }

    /**
     * display Smarty syntax error
     *
     * @param string $error_msg
     * @param integer $error_type
     * @param string $file
     * @param integer $line
     */
    function _syntax_error($error_msg, $error_type = E_USER_ERROR, $file=null, $line=null)
    {
        $this->_trigger_fatal_error("syntax error: $error_msg", $this->_current_file, $this->_current_line_no, $file, $line, $error_type);
    }


    /**
     * check if the compilation changes from cacheable to
     * non-cacheable state with the beginning of the current
     * plugin. return php-code to reflect the transition.
     * @return string
     */
    function _push_cacheable_state($type, $name) {
        $_cacheable = !isset($this->_plugins[$type][$name]) || $this->_plugins[$type][$name][4];
        if ($_cacheable
            || 0<$this->_cacheable_state++) return '';
        if (!isset($this->_cache_serial)) $this->_cache_serial = md5(uniqid('Smarty'));
        $_ret = 'if ($this->caching && !$this->_cache_including): echo \'{nocache:'
            . $this->_cache_serial . '#' . $this->_nocache_count
            . '}\'; endif;';
        return $_ret;
    }


    /**
     * check if the compilation changes from non-cacheable to
     * cacheable state with the end of the current plugin return
     * php-code to reflect the transition.
     * @return string
     */
    function _pop_cacheable_state($type, $name) {
        $_cacheable = !isset($this->_plugins[$type][$name]) || $this->_plugins[$type][$name][4];
        if ($_cacheable
            || --$this->_cacheable_state>0) return '';
        return 'if ($this->caching && !$this->_cache_including): echo \'{/nocache:'
            . $this->_cache_serial . '#' . ($this->_nocache_count++)
            . '}\'; endif;';
    }


    /**
     * push opening tag-name, file-name and line-number on the tag-stack
     * @param string the opening tag's name
     */
    function _push_tag($open_tag)
    {
        array_push($this->_tag_stack, array($open_tag, $this->_current_line_no));
    }

    /**
     * pop closing tag-name
     * raise an error if this stack-top doesn't match with the closing tag
     * @param string the closing tag's name
     * @return string the opening tag's name
     */
    function _pop_tag($close_tag)
    {
        $message = '';
        if (count($this->_tag_stack)>0) {
            list($_open_tag, $_line_no) = array_pop($this->_tag_stack);
            if ($close_tag == $_open_tag) {
                return $_open_tag;
            }
            if ($close_tag == 'if' && ($_open_tag == 'else' || $_open_tag == 'elseif' )) {
                return $this->_pop_tag($close_tag);
            }
            if ($close_tag == 'section' && $_open_tag == 'sectionelse') {
                $this->_pop_tag($close_tag);
                return $_open_tag;
            }
            if ($close_tag == 'foreach' && $_open_tag == 'foreachelse') {
                $this->_pop_tag($close_tag);
                return $_open_tag;
            }
            if ($_open_tag == 'else' || $_open_tag == 'elseif') {
                $_open_tag = 'if';
            } elseif ($_open_tag == 'sectionelse') {
                $_open_tag = 'section';
            } elseif ($_open_tag == 'foreachelse') {
                $_open_tag = 'foreach';
            }
            $message = " expected {/$_open_tag} (opened line $_line_no).";
        }
        $this->_syntax_error("mismatched tag {/$close_tag}.$message",
                             E_USER_ERROR, __FILE__, __LINE__);
    }

}

/**
 * compare to values by their string length
 *
 * @access private
 * @param string $a
 * @param string $b
 * @return 0|-1|1
 */
function _smarty_sort_length($a, $b)
{
    if($a == $b)
        return 0;

    if(strlen($a) == strlen($b))
        return ($a > $b) ? -1 : 1;

    return (strlen($a) > strlen($b)) ? -1 : 1;
}


/* vim: set et: */

?>
