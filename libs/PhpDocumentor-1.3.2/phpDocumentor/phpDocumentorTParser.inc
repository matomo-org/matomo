<?php
/**
 * tokenizer extension-based parser for PHP code
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2002-2006 Gregory Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @subpackage Parsers
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: phpDocumentorTParser.inc,v 1.20 2007/02/22 14:13:41 ashnazg Exp $
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
/**
 * Tokenizer-based parser for PHP source code
 * @package phpDocumentor
 * @subpackage Parsers
 * @author Gregory Beaver <cellog@users.sourceforge.net>
 * @version $Revision: 1.20 $
 */
class phpDocumentorTParser extends Parser
{
    /**#@+
     * @access private
     */
    /**
     * @var EventStack
     */
    var $_event_stack;
    /**
     * last event triggered before the current event
     * @var integer
     */
    var $_last_pevent;
    /**
     * last word parsed
     * @var integer
     */
    var $_last_word;
    /**
     * full path of the currently parsed file
     * @var string
     */
    var $_path;
    /**#@-*/
    /**#@+
     * Parser Variables
     * @access private
     */
    var $_pv_class;
    var $_pv_cur_class;
    var $_pv_define;
    var $_pv_define_name;
    var $_pv_define_value;
    var $_pv_define_params_data;
    var $_pv_dtype;
    var $_pv_docblock;
    var $_pv_dtemplate;
    var $_pv_func;
    var $_pv_func_param;
    var $_pv_findglobal;
    var $_pv_global_name;
    var $_pv_global_val;
    var $_pv_globals;
    var $_pv_global_count;
    var $_pv_include_params_data;
    var $_pv_include_name;
    var $_pv_include_value;
    var $_pv_linenum;
    var $_pv_periodline;
    var $_pv_paren_count = 0;
    var $_pv_statics;
    var $_pv_static_count;
    var $_pv_static_val;
    var $_pv_quote_data;
    var $_pv_function_data;
    var $_pv_var;
    var $_pv_varname;
    var $_pv_var_value;
    /**#@-*/
    /**#@+
     * Parser Flags
     * @access private
     */
    var $_pf_definename_isset = false;
    var $_pf_includename_isset = false;
    var $_pf_get_source = false;
    var $_pf_getting_source = false;
    var $_pf_internal = false;
    var $_pf_in_class = false;
    var $_pf_in_define = false;
    var $_pf_in_global = false;
    var $_pf_in_include = false;
    var $_pf_in_var = false;
    var $_pf_interface = false;
    var $_pf_funcparam_val = false;
    var $_pf_quote_active = false;
    var $_pf_reset_quote_data = true;
    var $_pf_useperiod = false;
    var $_pf_set_var_value = false;
    var $_pf_var_equals = false;
    /**#@-*/
    /**
     * relative path of the parsed file from the base parse directory
     * @var string
     */
    var $source_location;
    var $eventHandlers = array(
                                PARSER_EVENT_ARRAY => 'handleArray',
                                PARSER_EVENT_VAR_ARRAY => 'handleArray',
                                PARSER_EVENT_VAR_ARRAY_COMMENT => 'handleVarArrayComment',
                                PARSER_EVENT_CLASS => 'handleClass',
                                PARSER_EVENT_COMMENT => 'handleComment',
                                PARSER_EVENT_DOCBLOCK_TEMPLATE => 'handleDocBlockTemplate',
                                PARSER_EVENT_END_DOCBLOCK_TEMPLATE => 'handleEndDocBlockTemplate',
                                PARSER_EVENT_LOGICBLOCK => 'handleLogicBlock',
                                PARSER_EVENT_NOEVENTS => 'defaultHandler',
                                PARSER_EVENT_OUTPHP => 'defaultHandler',
                                PARSER_EVENT_DEFINE => 'handleDefine',
                                PARSER_EVENT_DEFINE_PARAMS => 'handleDefineParams',
                                PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS => 'handleDefineParamsParenthesis',
                                PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS => 'handleIncludeParamsParenthesis',
                                PARSER_EVENT_DOCBLOCK => 'handleDocBlock',
                                PARSER_EVENT_TAGS => 'handleTags',
                                PARSER_EVENT_DESC => 'handleDesc',
                                PARSER_EVENT_DOCKEYWORD => 'handleTag',
                                PARSER_EVENT_DOCKEYWORD_EMAIL => 'handleDockeywordEmail',
                                PARSER_EVENT_EOFQUOTE => 'handleHereDoc',
                                PARSER_EVENT_FUNCTION => 'handleFunction',
                                PARSER_EVENT_FUNCTION_PARAMS => 'handleFunctionParams',
                                PARSER_EVENT_FUNCTION_PARAM_VAR => 'handleFunctionParams',
                                PARSER_EVENT_FUNC_GLOBAL => 'handleFuncGlobal',
                                PARSER_EVENT_DEFINE_GLOBAL => 'handleGlobal',
                                PARSER_EVENT_GLOBAL_VALUE => 'handleGlobalValue',
                                PARSER_EVENT_INLINE_DOCKEYWORD => 'handleInlineDockeyword',
                                PARSER_EVENT_INCLUDE => 'handleInclude',
                                PARSER_EVENT_INCLUDE_PARAMS => 'handleIncludeParams',
                                PARSER_EVENT_QUOTE => 'handleQuote',
                                PARSER_EVENT_PHPCODE => 'handlePhpCode',
                                PARSER_EVENT_SINGLEQUOTE => 'handleSingleQuote',
                                PARSER_EVENT_STATIC_VAR => 'handleStaticVar',
                                PARSER_EVENT_STATIC_VAR_VALUE => 'handleStaticValue',
                                PARSER_EVENT_VAR => 'handleVar',
                                PARSER_EVENT_ACCESS_MODIFIER => 'handleAccessModifier',
                                PARSER_EVENT_IMPLEMENTS => 'handleImplements',
                                PARSER_EVENT_CLASS_CONSTANT => 'handleClassConstant',
    );
    
    var $inlineTagHandlers = array(
                                '*' => 'handleDefaultInlineTag',
                                'link' => 'handleLinkInlineTag',
                                );
    
    function phpDocumentorTParser()
    {
        $this->allowableTags = $GLOBALS['_phpDocumentor_tags_allowed'];
        $this->allowableInlineTags = $GLOBALS['_phpDocumentor_inline_doc_tags_allowed'];
        $this->subscribe(PHPDOCUMENTOR_EVENT_NEWLINENUM,$GLOBALS['phpDocumentor_errors']);
        $this->subscribe(PHPDOCUMENTOR_EVENT_NEWFILE,$GLOBALS['phpDocumentor_errors']);
        $this->tagHandlers['author'] = 'authorTagHandler';
        $this->tagHandlers['filesource'] = 'filesourceTagHandler';
        $this->setupEventStates();
    }
    
    /**
     * Parse a new file
     *
     * @param    string    $parse_data
     * @param    string    $path
     * @param    int    $base    number of directories to drop off the bottom when creating names using path
     * @staticvar    integer    used for recursion limiting if a handler for an event is not found
     * @return    bool
     */
    function parse (&$parse_data, $path, $base = 0, $packages = false)
    {
        global $_phpDocumentor_options;
        static $endrecur = 0;
        $this->setupStates();
        if (strlen($parse_data) == 0)
        {
            return false;
        }

        $this->configWordParser($parse_data);
        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;

        $page = new ParserPage;
        $page->setSource($this->_wp->getFileSource());
        $page->setPath($path);
        $this->_path = $path;
        $page->setPackageOutput($packages);
        $page->setFile(basename($path));
        $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWFILE,basename($path));
        //$name = str_replace("/","_",dirname($path)) . "_" . array_shift(explode(".",$page->getFile()));
        // fc@fc.clever-soft.com 11/29/2001
        $name = str_replace( ':', '', dirname($path) . PATH_DELIMITER . $page->getFile() );
        $tmp = explode( PATH_DELIMITER, $name );
        $name = implode( "---", array_slice( $tmp, $base ) );
        // if base is '', drive letter is present in windows

        $page->setName($name);
        $temploc = $_phpDocumentor_options['Program_Root'] . PATH_DELIMITER. implode(PATH_DELIMITER,
            array_slice(explode(PATH_DELIMITER,$path),$base));
        
        if ($temploc == $_phpDocumentor_options['Program_Root'] . PATH_DELIMITER) $temploc .= $path;
        
        $this->source_location = $source_location = $temploc;
        $page->setSourceLocation($source_location);

        $this->publishEvent(PHPDOCUMENTOR_EVENT_PAGE,$page);
        unset($page);
        do
        {
            $lpevent = $pevent;
            $pevent = $this->_event_stack->getEvent();
            if ($lpevent != $pevent)
            {
                $this->_last_pevent = $lpevent;
            }

            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,($pevent + 100));

            $this->_pv_last_word = $word;
            $word = $this->_wp->getWord();
            if (isset($this->_pv_findglobal) && $word == $this->_pv_findglobal)
            {
                $this->_last_pevent = $pevent;
                $this->_event_stack->pushEvent($pevent = PARSER_EVENT_DEFINE_GLOBAL);
            }
            // in wordparser, have to keep track of lines
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWLINENUM, $this->_wp->linenum);
            if ($this->_pf_get_source)
            {
                if ($word[0] == T_FUNCTION)
                {
                    $this->_wp->retrievesource($word);
                    $this->_pf_get_source = false;
                    $this->_pf_getting_source = true;
                }
            }

            if (0)//PHPDOCUMENTOR_DEBUG == true)
            {
                echo "LAST: ";
                if (is_array($this->_pv_last_word))
                {
                    echo token_name($this->_pv_last_word[0]). ' => |'.htmlspecialchars($this->_pv_last_word[1]);
                } else echo "|" . $this->_pv_last_word;
                echo "|\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo "LASTPEVENT: " . $this->getParserEventName($this->_last_pevent) . "\n";
                echo $this->_wp->getPos() . ": ";
                if (is_array($word))
                {
                    echo token_name($word[0]).' => |'.htmlspecialchars($word[1]);
                } else echo '|'.htmlspecialchars($word);
                echo "|\n-------------------\n\n\n";
            }
            if (0)//$this->_pf_getting_source && ($pevent == PARSER_EVENT_DOCBLOCK) || ($pevent == PARSER_EVENT_NOEVENTS))
            {
                addError(PDERROR_SOURCE_TAG_FUNCTION_NOT_FOUND);
                // throw away source
                $this->_wp->getSource();
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                $this->$handle($word, $pevent);
            } else
            {
                debug('WARNING: possible error, no handler for event number '.$pevent);
                if ($endrecur++ == 25)
                {
                    die("FATAL ERROR, recursion limit reached");
                }
            }
        } while (!($word === false));
        $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,PHPDOCUMENTOR_EVENT_END_PAGE);
    }

    /**#@+
     * @access private
     */
    /**
     * handler for COMMENT
     */
    function handleComment($word, $pevent)
    {
        $this->_wp->backupPos();
        $this->_event_stack->popEvent();
    }
    /**
     * handler for PHPCODE.
     * this handler recognizes the <code><?</code> php processor directive, and begins parsing php code
     */
    
    function handlePhpCode($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        if (isset($this->_pv_findglobal) && $e)
        {
            if ($e != PARSER_EVENT_DEFINE_GLOBAL && $e != PARSER_EVENT_ARRAY && $e != PARSER_EVENT_QUOTE && $e != PARSER_EVENT_SINGLEQUOTE && $e != PARSER_EVENT_COMMENT && $e != PARSER_EVENT_COMMENTBLOCK)
            {
                addError(PDERROR_GLOBAL_NOT_FOUND,$this->_pv_findglobal);
                $this->_wp->findGlobal(false);
                unset($this->_pv_findglobal);
            }
        }
    }
    
    /**
     * handler for FUNC_GLOBAL.
     * this handler recognizes "global $var1, $var2" declarations in a function, and parses them
     */
    
    function handleFuncGlobal($word, $pevent)
    {
        if ($this->checkEventPop($word, $pevent))
        {
            return;
        }
        if (!$this->checkEventPush($word, $pevent))
        {
            if ($word == ',')
            { // another variable
                $this->_pv_global_count++;
            } else
            {
                if (!isset($this->_pv_globals[$this->_pv_global_count]))
                $this->_pv_globals[$this->_pv_global_count] = '';
//                if (!empty($this->_pv_globals[$this->_pv_global_count])) $this->_pv_global_count++;
                if (is_array($word)) $word = $word[1];
                $this->_pv_globals[$this->_pv_global_count] .= $word;
            }
        }
    }
    
    /**
     * handler for STATIC_VAR.
     * this handler recognizes "static $var1, $var2 = 6" declarations in a function, and parses them
     */
    
    function handleStaticVar($word, $pevent)
    {
        if ($this->checkEventPop($word, $pevent))
        {
            $this->_pv_static_count++;
            return;
        }
        if (!$this->checkEventPush($word, $pevent))
        {
            if ($word == ',')
            {
                $this->_pv_static_count++;
                return;
            }
            if (!isset($this->_pv_statics[$this->_pv_static_count]))
            $this->_pv_statics[$this->_pv_static_count] = '';
            if (!empty($this->_pv_statics[$this->_pv_static_count])) $this->_pv_static_count++;
            if (is_array($word)) $word = $word[1];
            $this->_pv_statics[$this->_pv_static_count] = $word;
        }
    }
    
    /**
     * handler for STATIC_VAR_VALUE.
     * this handler parses the 6 in "static $var1, $var2 = 6"
     */
    
    function handleStaticValue($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent))
        {
            return;
        }
        if (!isset($this->_pv_static_val[$this->_pv_static_count])) $this->_pv_static_val[$this->_pv_static_count] = '';
        if ($this->_last_pevent == PARSER_EVENT_QUOTE)
        {
            $this->_pv_static_val[$this->_pv_static_count] .= $this->_pv_quote_data;
            unset($this->_pv_quote_data);
        }
        if ($this->_last_pevent == PARSER_EVENT_ARRAY)
        {
            $this->_pv_static_val[$this->_pv_static_count] .= $this->_pv_function_data;
            $this->_pv_function_data = '';
        }
        if ($this->checkEventPop($word, $pevent))
        {
            $this->_pv_static_val[$this->_pv_static_count] = trim($this->_pv_static_val[$this->_pv_static_count]);
            $this->_wp->backupPos($word);
            return;
        } else
        {
            if (is_array($word)) $word = $word[1];
            $this->_pv_static_val[$this->_pv_static_count] .= $word;
        }
    }
    
    /**
     * handler for LOGICBLOCK
     *
     * Logic Blocks are the stuff between { and } in a function/method.  A
     * logic block can clearly contain other logic blocks, as in:
     *
     * <code>
     * function test($a)
     * {
     *    if (testcondition)
     *    { // nested logic block
     *    }
     * }
     * </code>
     *
     * So, the exit portion of the logic block handler must check to see if the
     * logic block being exited is the top-level, and it does this by retrieving
     * the last event from the stack.  If it is a function (and not a logic block)
     * then it backs up the word parser so that the function will exit properly.
     *
     * {@source 11}
     */
    
    function handleLogicBlock($word, $pevent)
    {
        $a = $this->checkEventPush( $word, $pevent);
        if ($this->checkEventPop($word,$pevent))
        {
            $e = $this->_event_stack->popEvent();
            $this->_event_stack->pushEvent($e);
            if ($e == PARSER_EVENT_FUNCTION)
            {
                $this->_wp->backupPos(); 
            }
        }
    }
    
    /**
     * handler for FUNCTION.
     * this handler recognizes function declarations, and parses them.  The body
     * of the function is parsed by handleLogicBlock()
     * @see handleLogicBlock()
     */
    
    function handleFunction($word, $pevent)
    {
        if ($e = $this->checkEventPush( $word, $pevent))
        {
            $this->_pv_function_data = '';
            if ($e == PARSER_EVENT_FUNCTION_PARAMS && !is_object($this->_pv_func))
            {
                addErrorDie(PDERROR_FUNCTION_HAS_NONAME);
            }
            if ($e == PARSER_EVENT_COMMENT || $e == PARSER_EVENT_COMMENTBLOCK || $e == PARSER_EVENT_FUNCTION_PARAMS || $e == PARSER_EVENT_LOGICBLOCK) return;
        }
    
        if (!isset($this->_pv_func)) $this->_pv_func = false;
        if (! is_object($this->_pv_func)) 
        {
            $this->_pv_globals = array();
            $this->_pv_global_count = $this->_pv_static_count = 0;
            if ($this->_pf_in_class)
            {
                $this->_pv_func = new parserMethod($this->_pv_cur_class); 
            } else
            {
                $this->_pv_func = new parserFunction;
                unset($this->_accessModifiers);
            }
            if (isset($this->_accessModifiers))
            {
                $this->_pv_func->setModifiers($this->_accessModifiers);
                unset($this->_accessModifiers);
            }
            $this->_pv_func->setLineNumber($this->_wp->linenum + 1);
            if (is_string($word) && $word == '&')
            $this->_pv_func->setReturnsReference();
            if (is_array($word) && $word[0] == T_STRING)
            $this->_pv_func->setName($word[1]);
        } else
        {
            if ($this->_pv_func->getReturnsReference())
            {
                if (is_array($word) && $word[0] == T_STRING)
                {
                    $this->_pv_func->setName($word[1]);
                }
            }
        }
        if ($this->checkEventPop($word,$pevent)) 
        {
            $this->_pv_func->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pv_func->addGlobals($this->_pv_globals);
            $this->_pv_func->addStatics($this->_pv_statics,$this->_pv_static_val);
            $this->_pv_globals = array();
            $this->_pv_global_count = 0;
            if ($this->_pf_getting_source)
            {
                $x = $this->_wp->getSource();
                $this->_pv_func->addSource($x);
                $this->_pf_get_source = false;
                $this->_pf_getting_source = false;
            }
            $this->publishEvent(PHPDOCUMENTOR_EVENT_FUNCTION,$this->_pv_func); 
            $this->_pv_func = false; 
            unset($this->_pv_quote_data); // subtle bug fixed by this, sometimes string
                                          // from function body was picked up
                                          // by the next function as a default value
                                          // for a parameter!
        } 
    }

    /**
     * Helper function for {@link handleFunctionParams()}
     *
     * This function adds a new parameter to the parameter list
     */
    function endFunctionParam($word)
    {
        if (isset($this->_pv_quote_data))
        {
            $this->_pv_function_data .= $this->_pv_quote_data;
            unset($this->_pv_quote_data);
        }
        if (isset($this->_pv_function_param))
        {
            $this->_pv_func->addParam($this->_pv_function_param,$this->_pv_function_data,
                $this->_pf_funcparam_val, $this->_pv_function_param_type);
            unset($this->_pv_function_param);
            $this->_pv_function_data = '';
            $this->_pf_funcparam_val = false;
            $this->_pv_function_param_type = null;
        }
    }
    /**
     * handler for FUNCTION_PARAMS.
     * this handler recognizes the parameters of a function within parentheses like function(param, param = default_value)
     * and parses them
     * @see endFunctionParam()
     */
    
    function handleFunctionParams($word, $pevent)
    {
        //echo $this->_wp->getPos() . ": word=|$word|\t\t\tlastword=|".$this->_pv_last_word."|\n";
        //echo "function_param = '".$this->_pv_function_param."'\n";
        //echo "function_data = '".$this->_pv_function_data."'\n";
        $e1 = $this->checkEventPush($word, $pevent); 

        if (!$e1)
        {
            if (($pop = $this->checkEventPop($word,$pevent)) && $pevent == PARSER_EVENT_FUNCTION_PARAM_VAR)
            { // end of [typehint ]$param[= defaultval]
                if (is_string($word) && $word == ')') {
                    $this->_wp->backupPos();
                }
                $this->endFunctionParam($word);
            } elseif ($word == '=')
            { // about to parse the default value
                $this->_pf_funcparam_val = true;
            } else
            {
                if ($this->_pf_funcparam_val)
                { // parsing default value
                    if (isset($this->_pv_quote_data))
                    {
                        $this->_pv_function_data .= $this->_pv_quote_data;
                        unset($this->_pv_quote_data);
                    }
                    if (is_array($word)) $word = $word[1];
                    $this->_pv_function_data .= $word;
                } else
                { // pre-param
                    if ($pop) {
                        return;
                    }
                    if (!isset($this->_pv_function_param)) $this->_pv_function_param = '';
                    if (is_array($word) && $pevent == PARSER_EVENT_FUNCTION_PARAMS)
                    {
                        if ($word[0] == T_STRING || $word[0] == T_ARRAY)
                        { // object or array type hint
                            $this->_pv_function_param_type = $word[1];
                            return;
                        }
                        $word = $word[1];
                    }
                    $this->_pv_function_param .= $word;
                }
            }
        } elseif ($e1 == PARSER_EVENT_ARRAY)
        {
            $this->_wp->setWhiteSpace(true);
        } elseif ($e1 == PARSER_EVENT_FUNCTION_PARAM_VAR) {
            if (!isset($this->_pv_function_param)) $this->_pv_function_param = '';
            // we just got the $var part of the param
            $this->_pv_function_param .= $word[1];
        }
    }

    /**
     * handler for ARRAY.
     * this event handler parses arrays in default values of function and var definitions
     */
    
    function handleArray($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        if ($e) return;

        if (!isset($this->_pv_function_data) || (isset($this->_pv_function_data) && empty($this->_pv_function_data)))
        {
            $this->_pv_function_data = "array";
        }

        if ($word == '(' && $this->_pv_paren_count++)
        { // need extra parentheses help
            $this->_event_stack->pushEvent($pevent);
        }
        if (is_array($word))
        {
            $this->_pv_function_data .= $word[1];
        } else
        $this->_pv_function_data .= $word;
        //echo "function_data = |$this->_pv_function_data|\n";

        if ($this->checkEventPop($word,$pevent))
        {
            $this->_pv_paren_count--;
            $this->_wp->setWhiteSpace(false);
        }
    }
    
    /**
     * handler for HEREDOC in a function logic block.
     * this handler recognizes function declarations, and parses them.  The body
     * of the function is parsed by handleLogicBlock()
     * @see handleLogicBlock()
     */
    
    function handleHereDoc($word, $pevent)
    {
        if (is_array($this->_pv_last_word) && $this->_pv_last_word[0] == T_START_HEREDOC)
        {
            $save = $word;
            if (is_array($word)) $word = $word[1];
            $this->_pv_quote_data = $this->_pv_last_word[1] . $word;
            $this->_pf_quote_active = true;
        } elseif (!$this->_pf_quote_active)
        {
            $this->_pv_quote_data = $this->_pv_last_word[1];
            $this->_event_stack->popEvent();
            $this->_wp->backupPos();
            return;
        }
        $save = $word;
        if (is_array($word)) $word = $word[1];
        $this->_pv_quote_data .= $word;
        if ($this->checkEventPop($save, $pevent))
        {
            $this->_pf_quote_active = false;
        }
    }

    /**
     * handler for QUOTE.
     * this handler recognizes strings defined with double quotation marks (")
     * and single quotation marks and handles them correctly
     * in any place that they legally appear in php code
     */
    
    function handleQuote($word, $pevent)
    {
        if ($this->_pv_last_word == '"' || $this->_pv_last_word == "'" && $this->_last_pevent != PARSER_EVENT_QUOTE)
        {
            $save = $word;
            if (is_array($word)) $word = $word[1];
            $this->_pv_quote_data = $this->_pv_last_word . $word;
            $this->_pf_quote_active = true;
            $this->checkEventPop($save, $pevent);
        } elseif (!$this->_pf_quote_active)
        {
            $this->_pv_quote_data = $this->_pv_last_word[1];
            $this->_event_stack->popEvent();
            $this->_wp->backupPos();
            return;
        }
        $save = $word;
        if (is_array($word)) $word = $word[1];
        $this->_pv_quote_data .= $word;
        if ($this->checkEventPop($save, $pevent))
        {
            $this->_pf_quote_active = false;
        }
    }

    /**
     * handler for INCLUDE.
     * this handler recognizes include/require/include_once/include_once statements, and publishes the
     * data to Render
     */
    
    function handleInclude($word, $pevent)
    {
        if (!$this->_pf_in_include)
        {
            $this->_pv_linenum = $this->_wp->linenum;
        }
        $this->_pf_in_include = true;
        $a = $this->checkEventPush( $word, $pevent);
        if (!$this->_pf_includename_isset)
        {
            $this->_pf_includename_isset = true;
            $w = $this->_pv_last_word;
            if (is_array($w)) $w = $w[1];
            $this->_pv_include_name = $w;
            if ($a)
            $this->_pv_include_value = '';
            else
            {
                if (is_array($word)) $word = $word[1];
                $this->_pv_include_value = $word;
            }
            unset($this->_pv_quote_data);
        } else
        {
            if (!$a)
            {
                if (empty($this->_pv_include_params_data))
                {
                    if ($word != ';')
                    {
                        if (is_array($word)) $word = $word[1];
                        $this->_pv_include_value .= $word;
                    }
                }
            } else
            {
                $this->_pv_include_params_data = '';
            }
        }

        if ($this->checkEventPop($word,$pevent))
        {
            $this->_pv_include = new parserInclude;
            $this->_pv_include->setLineNumber($this->_pv_linenum + 1);
            $this->_pf_in_include = false;
            $this->_pv_include->setName($this->_pv_include_name);
            $this->_pv_include->setValue($this->_pv_include_value);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_INCLUDE,$this->_pv_include);
            $this->_pf_includename_isset = false;
            unset($this->_pv_include);
            unset($this->_pv_include_name);
            unset($this->_pv_include_value);
            unset($this->_pv_include_params_data);
        } elseif ($this->_last_pevent == PARSER_EVENT_INCLUDE_PARAMS) {
            // include is part of a larger statement
            // force ending of include
            $this->_event_stack->popEvent();
            $this->_pv_include = new parserInclude;
            $this->_pv_include->setLineNumber($this->_pv_linenum + 1);
            $this->_pf_in_include = false;
            $this->_pv_include->setName($this->_pv_include_name);
            $this->_pv_include->setValue($this->_pv_include_value);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_INCLUDE,$this->_pv_include);
            $this->_pf_includename_isset = false;
            unset($this->_pv_include);
            unset($this->_pv_include_name);
            unset($this->_pv_include_value);
            unset($this->_pv_include_params_data);
        }
    }
    
    /**
     * handler for INCLUDE_PARAMS.
     * this handler parses the contents of ( ) in include/require/include_once/include_once statements
     */
    
    function handleIncludeParams($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        if ($e == PARSER_EVENT_COMMENT) return;
        
        if(!isset($this->_pv_include_params_data)) $this->_pv_include_params_data = '';
        
        if ($this->checkEventPop($word,$pevent))
        {
            if (!empty($this->_pv_include_params_data))
            $this->_pv_include_value = $this->_pv_include_params_data;
            else
            {
                $w = $this->_pv_last_word;
                if (is_array($w)) $w = $w[1];
                $this->_pv_include_value = $w;
            }
        }
        if (is_array($word)) $word = $word[1];
        $this->_pv_include_params_data .= $word;
    }
    
    /**
     * handler for INCLUDE_PARAMS_PARENTHESIS.
     * this handler takes all parenthetical statements within file in:
     * include statement include(file), and handles them properly
     */
    
    function handleIncludeParamsParenthesis($word, $pevent)
    {
        $this->checkEventPush( $word, $pevent);
        $this->checkEventPop( $word, $pevent);
        if (is_array($word)) $word = $word[1];
        $this->_pv_include_params_data .= $word;
    }

    /**
     * handler for DEFINE.
     * handles define(constant, value); statements
     */
    
    function handleDefine($word, $pevent)
    {
        if (!$this->_pf_in_define)
        {
            $this->_pv_linenum = $this->_wp->linenum + 1;
        }
        $this->_pf_in_define = true;
        $this->checkEventPush( $word, $pevent);

        $this->_pf_definename_isset = false;
        $this->_pv_define_params_data = '';
        unset($this->_pv_quote_data);
        if ($this->checkEventPop($word,$pevent))
        {
            $this->_pf_in_define = false;
            $this->_pv_define = new parserDefine;
            $this->_pv_define->setLineNumber($this->_pv_linenum);
            $this->_pv_define->setName($this->_pv_define_name);
            $this->_pv_define->setValue($this->_pv_define_value);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_DEFINE,$this->_pv_define);
            $this->_pf_definename_isset = false;
            unset($this->_pv_define);
            unset($this->_pv_define_name);
            unset($this->_pv_define_value);
            $this->_pf_in_define = false;
            $this->_pv_define_params_data = '';
        }
    }
    
    /**
     * handler for DEFINE_PARAMS.
     * handles the parsing of constant and value in define(constant, value);
     */
    
    function handleDefineParams($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        if ($e && $e != PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS) return;
        
        if(!isset($this->_pv_define_params_data)) $this->_pv_define_params_data = '';
        
        if ($this->checkEventPop($word,$pevent))
        {
            if ($this->_last_pevent == PARSER_EVENT_QUOTE ||
                $this->_last_pevent == PARSER_EVENT_EOFQUOTE)
            {
                $this->_pv_define_params_data .= $this->_pv_quote_data;
                unset($this->_pv_quote_data);
            }
            if (is_array($word)) $word = $word[1];
            if (!empty($this->_pv_define_params_data))
            {
                //echo $this->_pv_define_params_data."\n";
                $this->_pv_define_value = $this->_pv_define_params_data;
            }
            else
            {
                $w = $this->_pv_last_word;
                if (is_array($this->_pv_last_word)) $w = $this->_pv_last_word[1];
                if (!empty($w))
                {
                    $this->_pv_define_value = $w;
                }
                else
                {
                    $this->_pv_define_value = "";
                    switch ($w) {
                        case 0:
                        $this->_pv_define_value = "0";
                        break;
                        case null:
                        $this->_pv_define_value = "null";
                        break;
                        case "":
                        $this->_pv_define_value = "";
                        break;
                    }
                }
            }
        }
        if ($this->_pf_definename_isset)
        {
            if (is_array($word)) $word = $word[1];
            $this->_pv_define_params_data .= $word;
        } else
        {
            if ($word != ",")
            {
                if (is_array($word)) $word = $word[1];
                $this->_pv_define_params_data .= $word;
            } else
            {
                if (substr($this->_pv_define_params_data,0,1) ==
                    substr($this->_pv_define_params_data,strlen($this->_pv_define_params_data) - 1) &&
                    in_array(substr($this->_pv_define_params_data,0,1),array('"',"'")))
                { // remove leading and ending quotation marks if there are only two
                    $a = substr($this->_pv_define_params_data,0,1);
                    $b = substr($this->_pv_define_params_data,1,strlen($this->_pv_define_params_data) - 2);
                    if (strpos($b,$a) === false)
                    {
                        $this->_pv_define_params_data = $b;
                    }
                }
                $this->_pf_definename_isset = true;
                $this->_pv_define_name = $this->_pv_define_params_data;
                $this->_pv_define_params_data = '';
            }
        }
    }
    
    /**
     * handler for DEFINE_PARAMS_PARENTHESIS.
     * this handler takes all parenthetical statements within constant or value in:
     * define(constant, value) of a define statement, and handles them properly
     */
    
    function handleDefineParamsParenthesis($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        $this->checkEventPop( $word, $pevent);
        if ($this->_last_pevent == PARSER_EVENT_QUOTE)
        {
            $this->_pv_define_params_data .= $this->_pv_quote_data;
            unset($this->_pv_quote_data);
        }
        if (is_array($word)) $word = $word[1];
        $this->_pv_define_params_data .= $word;
    }

    /**
     * handler for IMPLEMENTS.
     * this handler parses a class statement's implements clause (PHP 5)
     */
    
    function handleImplements($word, $pevent)
    {
        if ($this->checkEventPop($word, $pevent))
        {
            $this->_wp->backupPos();
            return;
        }
        if (is_array($word) && $word[0] == T_STRING)
        {
            $this->_pv_class->addImplements($word[1]);
        }
    }

    /**
     * handler for ACCESS_MODIFIER.
     * this handler parses public/private/protected/static/abstract PHP 5 modifiers
     */
    
    function handleAccessModifier($word, $pevent)
    {
        if (!isset($this->_accessModifiers))
        {
            $this->_accessModifiers = array();
        }
        $this->_wp->backupPos();
        $this->_event_stack->popEvent();
        if ($word[0] == T_VARIABLE) {
            // this is a PHP5-style variable with no "var"
            $this->_event_stack->pushEvent(PARSER_EVENT_VAR);
        }
        $this->_accessModifiers[] = strtolower($this->_pv_last_word[1]);
    }

    /**
     * handler for CLASS.
     * this handler parses a class/interface statement
     */
    
    function handleClass($word, $pevent)
    {
        if (!$this->_pf_in_class)
        {
            $this->_pf_in_class = true;
            if ($this->_pv_last_word[0] == T_INTERFACE)
            {
                $this->_pf_interface = true;
            } else
            {
                $this->_pf_interface = false;
            }
        }
        $a = $this->checkEventPush( $word, $pevent);

        if (!isset($this->_pv_class)) $this->_pv_class = false;
        if (!is_subclass_of($this->_pv_class,"parserBase"))
        {
            $this->_pv_class = new parserClass;
            if (isset($this->_accessModifiers))
            {
                $this->_pv_class->setModifiers($this->_accessModifiers);
                unset($this->_accessModifiers);
            }
            if ($this->_pf_interface)
            {
                $this->_pv_class->setInterface();
            }
            $this->_pv_class->setLineNumber($this->_wp->linenum + 1);
            $this->_pv_class->setname($word[1]);
            $this->_pv_cur_class = $word[1];
            $this->_pv_class->setSourceLocation($this->source_location);
        }

        if (is_array($this->_pv_last_word) && $this->_pv_last_word[0] == T_EXTENDS)
        {
            // I don't know why I am so nice, this fixes 1150809
            if ($word[1] == $this->_pv_class->getName()) {
                addErrorDie(PDERROR_CANNOT_EXTEND_SELF, $word[1]);
            }
            $this->_pv_class->setExtends($word[1]);
        }

        if ($word == "{")
        {
            $this->publishEvent(PHPDOCUMENTOR_EVENT_CLASS,$this->_pv_class);
        }
        //echo $this->wp->getPos() . ": |$word|\n";
        if ($this->checkEventPop($word,$pevent))
        {
            $this->_pv_class->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pf_in_class = $this->_pf_interface = false;
            // throw an event when class is done
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,STATE_END_CLASS);
            $this->_pv_class = false;
        }
    }
    
    /**
     * handler for VAR_ARRAY_COMMENT
     * if parsing a default value, add the comment to the text
     */
    function handleVarArrayComment($word, $pevent)
    {
        $this->_pv_function_data .= $this->_pv_last_word[1];
        return $this->handleComment($word, $pevent);
    }

    /**
     * handler for VAR.
     * handle a var $varname = default_value; or var $varname; statement in a class definition
     */
    
    function handleVar($word, $pevent)
    {
        if (!$this->_pf_in_var)
        {
            $this->_pf_set_var_value = false;
            $this->_pv_var_value = '';
            $this->_pv_linenum = $this->_wp->linenum + 1;
        }
        $this->_pf_in_var = true;
        //echo $word."\n";
        $e = $this->checkEventPush( $word, $pevent);
        
        if (!isset($this->_pv_var)) $this->_pv_var = false;
        if ($word == '=' || $word == ';' || $word == ',')
        {
            $this->_wp->setWhitespace(true);
            $this->_pf_var_equals = true;
            $this->_pv_var = new parserVar($this->_pv_cur_class);
            $this->_pv_var->setName($this->_pv_varname);
        }
        if ($this->_last_pevent == PARSER_EVENT_VAR_ARRAY)
        {
            if (isset($this->_pv_function_data))
            $this->_pv_var->setValue($this->_pv_function_data);
            $this->_pf_set_var_value = true;
            unset($this->_pv_function_data);
        } elseif ($this->_pf_var_equals && $word != ';' && $word != '=' && $word != ',' && !$e)
        {
            if (is_array($word)) $word = $word[1];
            $this->_pv_var_value .= $word;
        }
        if ($word == ',')
        {
            if (!$this->_pf_set_var_value)
            $this->_pv_var->setValue($this->_pv_var_value);
            $this->_pf_set_var_value = false;
            unset($this->_pv_var_value);
            $this->_pv_var->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pv_var->setLineNumber($this->_pv_linenum);
            if (isset($this->_accessModifiers))
            {
                $this->_pv_var->setModifiers($this->_accessModifiers);
            }
            $this->publishEvent(PHPDOCUMENTOR_EVENT_VAR,$this->_pv_var);
            unset($this->_pv_var);
            $this->_pf_in_var = false;
            $this->_pf_var_equals = false;
            $this->_pv_varname = '';
            return;
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $this->_wp->setWhitespace(false);
            if (!$this->_pf_set_var_value)
            $this->_pv_var->setValue($this->_pv_var_value);
            $this->_pf_set_var_value = false;
            unset($this->_pv_var_value);
            $this->_pv_var->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pv_var->setLineNumber($this->_pv_linenum);
            if (isset($this->_accessModifiers))
            {
                $this->_pv_var->setModifiers($this->_accessModifiers);
                unset($this->_accessModifiers);
            }
            $this->publishEvent(PHPDOCUMENTOR_EVENT_VAR,$this->_pv_var);
            unset($this->_pv_var);
            $this->_pf_in_var = false;
            $this->_pf_var_equals = false;
            $this->_pv_varname = '';
            return;
        }
        if ($word[0] == T_VARIABLE)
        {
            $this->_pv_varname = $word[1];
        }

    }

    /**
     * handler for CLASS_CONSTANT.
     * handle a const constname = default_value; statement in a class definition
     */
    
    function handleClassConstant($word, $pevent)
    {
        if (!$this->_pf_in_const)
        {
            $this->_pf_set_const_value = false;
            $this->_pv_const_value = '';
            $this->_pv_linenum = $this->_wp->linenum + 1;
        }
        $this->_pf_in_const = true;
        //echo $word."\n";
        $e = $this->checkEventPush( $word, $pevent);
        
        if (!isset($this->_pv_const)) $this->_pv_const = false;
        if ($word == '=' || $word == ';' || $word == ',')
        {
            $this->_wp->setWhitespace(true);
            $this->_pf_const_equals = true;
            $this->_pv_const = new parserConst($this->_pv_cur_class);
            $this->_pv_const->setName($this->_pv_constname);
        }
        if ($this->_last_pevent == PARSER_EVENT_VAR_ARRAY)
        {
            if (isset($this->_pv_function_data))
            $this->_pv_const->setValue($this->_pv_function_data);
            $this->_pf_set_const_value = true;
            unset($this->_pv_function_data);
        } elseif ($this->_pf_const_equals && $word != ';' && $word != '=' && $word != ',' && !$e)
        {
            if (is_array($word)) $word = $word[1];
            $this->_pv_const_value .= $word;
        }
        if ($word == ',')
        {
            if (!$this->_pf_set_const_value)
            {
                $this->_pv_const->setValue($this->_pv_const_value);
            }
            $this->_pf_set_const_value = false;
            unset($this->_pv_const_value);
            $this->_pv_const->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pv_const->setLineNumber($this->_pv_linenum);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_CONST,$this->_pv_const);
            unset($this->_pv_const);
            $this->_pf_in_const = false;
            $this->_pf_const_equals = false;
            $this->_pv_constname = '';
            return;
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $this->_wp->setWhitespace(false);
            if (!$this->_pf_set_const_value)
            $this->_pv_const->setValue($this->_pv_const_value);
            $this->_pf_set_const_value = false;
            unset($this->_pv_const_value);
            $this->_pv_const->setEndLineNumber($this->_wp->linenum + 1);
            $this->_pv_const->setLineNumber($this->_pv_linenum);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_VAR,$this->_pv_const);
            unset($this->_pv_const);
            $this->_pf_in_const = false;
            $this->_pf_const_equals = false;
            $this->_pv_constname = '';
            return;
        }
        if ($word[0] == T_STRING && !$this->_pf_const_equals)
        {
            $this->_pv_constname = $word[1];
        }

    }
    
    /**
     * Handler for the {@tutorial phpDocumentor.howto.pkg#using.command-line.javadocdesc}
     * command-line switch DocBlocks.
     */
    function JavaDochandleDocblock($word, $pevent)
    {
        $this->commonDocBlock($word, $pevent, 'handleJavaDocDesc');
    }
    
    /**
     * Handler for normal DocBlocks
     */
    function handleDocBlock($word, $pevent)
    {
        $this->commonDocBlock($word, $pevent, 'handleDesc');
    }
    
    /**
     * Common DocBlock Handler for both JavaDoc-format and normal DocBlocks
     */
    function commonDocBlock($word, $pevent, $deschandler)
    {
        $this->_wp->backupPos();
        $this->_event_stack->popEvent();
        $word = $this->_pv_last_word[1];
        $dtype = '_pv_docblock';
        if (strpos($word,'/**') !== 0)
        { // not a docblock
//            $this->_wp->backupPos();
            $this->_event_stack->pushEvent(PARSER_EVENT_COMMENT);
            return;
        }
        if ($word == '/**#@-*/')
        { // stop using docblock template
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,PHPDOCUMENTOR_EVENT_END_DOCBLOCK_TEMPLATE);
            unset($this->_pv_dtemplate);
            return;
        }
        if (strpos($word,'/**#@+') === 0)
        { // docblock template definition
            $dtype = '_pv_dtemplate';
            // strip /**#@+ and */
            $word = substr($word,6).'*';
            $word = trim(substr($word,0,strlen($word) - 3));
            if (strlen($word) && $word{0} != '*') {
                $word = "* $word";
            }
        } else
        {
            // strip /** and */
            $word = substr($word,2);
            $word = substr($word,0,strlen($word) - 2);
        }
        $lines = explode("\n",trim($word));
        $go = count($lines);
        for($i=0;$i<$go;$i++)
        {
            if (substr(trim($lines[$i]),0,1) != '*') unset($lines[$i]);
            else
            $lines[$i] = substr(trim($lines[$i]),1); // remove leading "* "
        }
        // remove empty lines
        if( $lines == array(false) )
        { 
            // prevent PHP 5.2 segfault (see http://bugs.php.net/bug.php?id=39350)
            $lines = array('');
        }
        $lines = explode("\n",trim(join("\n",$lines)));
        for($i = 0;$i<count($lines);$i++)
        {
            if (substr(trim($lines[$i]),0,1) == '@' && substr(trim($lines[$i]),0,2) != '@ ')
            {
                $tagindex = $i;
                $i = count($lines);
            }
        }
        if (isset($tagindex))
        {
            $tags = array_slice($lines,$tagindex);
            $desc = array_slice($lines,0,$tagindex);
        } else
        {
            $tags = array();
            $desc = $lines;
        }
//        var_dump($desc,$tags);
        $this->$dtype = new parserDocBlock;
        $this->$dtype->setLineNumber($this->_wp->_docblock_linenum + 1);
        $this->$dtype->setEndLineNumber($this->_wp->linenum);
        $this->_pv_dtype = $dtype;
        $this->$deschandler($desc);
        $this->handleTags($tags);
        if ($dtype == '_pv_docblock')
        {
            $this->publishEvent(PHPDOCUMENTOR_EVENT_DOCBLOCK,$this->$dtype);
            $this->$dtype = new parserDocBlock();
        } else
        {
            $this->publishEvent(PHPDOCUMENTOR_EVENT_DOCBLOCK_TEMPLATE,$this->$dtype);
        }
    }
    
    /**
     * Handles JavaDoc descriptions
     */
    function handleJavaDocDesc($desc)
    {
        unset($this->_pv_periodline);
        $this->_pf_useperiod = false;
        if (empty($desc)) {
            $desc = array('');
        }
        foreach($desc as $i => $line)
        {
            $line = trim($line);
            if (!isset($this->_pv_periodline) && substr($line,strlen($line) - 1) == '.')
            {
                $this->_pv_periodline = $i;
                $this->_pf_useperiod = true;
            }
        }
        if (!isset($this->_pv_periodline)) $this->_pv_periodline = 0;

        $dtype = $this->_pv_dtype;
        if ($dtype == '_pv_docblock')
        {
            $save = $desc;
            // strip leading <p>
            if (strpos($desc[0],'<p>') === 0) $desc[0] = substr($desc[0],3);
            $sdesc = new parserDesc;
            $desci = '';
            for($i = 0; ($i <= $this->_pv_periodline) && ($i < count($desc)); $i++)
            {
                if (strpos($desc[$i],'.') !== false)
                {
                    $desci .= substr($desc[$i],0,strpos($desc[$i],'.') + 1);
                } else
                {
                    $desci .= $desc[$i];
                }
                $desci .= "\n";
            }
            $sdesc->add($this->getInlineTags($desci));
            $desc = $save;
        
            $my_desc = new parserDesc;
            if (isset($this->_pv_dtemplate))
            {
                // copy template values if not overridden
                if (!$this->_pv_docblock->getExplicitPackage())
                {
                    if ($p = $this->_pv_dtemplate->getKeyword('package'))
                    {
                        $this->_pv_docblock->addKeyword('package',$p);
                        $this->_pv_docblock->setExplicitPackage();
                    }
                    if ($p = $this->_pv_dtemplate->getKeyword('category'))
                    {
                        $this->_pv_docblock->addKeyword('category',$p);
                        $this->_pv_docblock->setExplicitCategory();
                    }
                    if ($p = $this->_pv_dtemplate->getKeyword('subpackage'))
                    {
                        $this->_pv_docblock->addKeyword('subpackage',$p);
                    }
                }
                $tags = $this->_pv_dtemplate->listTags();
                foreach($tags as $tag)
                {
                    $this->_pv_docblock->addTag($tag);
                }
                if (!count($this->_pv_docblock->params)) $this->_pv_docblock->params = $this->_pv_dtemplate->params;
                $my_desc->add($this->_pv_dtemplate->desc);
            }
//            echo "i = ".$this->_pv_periodline."; i < " . count($desc) . "\n";
            $desci = '';
            for($i = 0; $i < count($desc); $i++)
            {
                // the line will not be set if it doesn't start with a *
                if (isset($desc[$i]))
                {
                    $desci .= $desc[$i]."\n";
                }
            }
            $my_desc->add($this->getInlineTags($desci));
        } else
        {
            $sdesc = new parserDesc;
            $save = $desc;
            // strip leading <p>
            if (strpos($desc[0],'<p>') === 0) $desc[0] = substr($desc[0],3);
            $desci = '';
            for($i = 0; ($i <= $this->_pv_periodline) && ($i < count($desc)); $i++)
            {
                if (strpos($desc[$i],'.') !== false)
                {
                    $desci .= substr($desc[$i],0,strpos($desc[$i],'.') + 1);
                } else
                {
                    $desci .= $desc[$i];
                }
                $desci .= "\n";
            }
            $sdesc->add($this->getInlineTags($desci));
            $desc = $save;
        
            $my_desc = new parserDesc;
            $desci = '';
            for($i=0; $i < count($desc); $i++)
            {
                if (isset($desc[$i]))
                    $desci .= $desci[$i]."\n";
            }
            $my_desc->add($this->getInlineTags($desci));
        }
        
        if ($this->_pf_internal)
        {
            addError(PDERROR_INTERNAL_NOT_CLOSED);
            $this->_pf_internal = false;
        }
        $this->$dtype->setShortDesc($sdesc);
        $this->$dtype->setDesc($my_desc);
        unset($my_desc);
//        var_dump($this->$dtype);
//        exit;
    }
    
    /**
     * Process the Long Description of a DocBlock
     * @param array array of lines containing the description with leading
     *              asterisk "*" stripped off.
     */
    function handleDesc($desc)
    {
        unset($this->_pv_periodline);
        $this->_pf_useperiod = false;
        foreach($desc as $i => $line)
        {
            $line = trim($line);
            if (!isset($this->_pv_periodline) && substr($line,strlen($line) - 1) == '.')
            {
                $this->_pv_periodline = $i;
                $this->_pf_useperiod = true;
            }
        }
        if (!isset($this->_pv_periodline)) $this->_pv_periodline = 0;
        if ($this->_pv_periodline > 3)
        {
            $this->_pf_useperiod = false;
        } else
        {
            for($i = 0; $i < $this->_pv_periodline; $i++)
            {
                if (strlen($desc[$i]) == 0 && isset($desc[$i - 1]) && strlen($desc[$i - 1]))
                {
                    $this->_pv_periodline = $i;
                }
            }
        }
        for($i=0;$i <= $this->_pv_periodline && $i < count($desc);$i++)
        {
            if (!strlen(trim($desc[$i]))) $this->_pf_useperiod = false;
        }
        // figure out the shortdesc
        if ($this->_pf_useperiod === false)
        {
            // use the first non blank line for short desc
            for($i = 0; $i < count($desc); $i++)
            {
                if (strlen($desc[$i]) > 0)
                {
                    $this->_pv_periodline = $i;
                    $i = count($desc);
                }
            }
        
            // check to see if we are going to use a blank line to end the shortdesc
            // this can only be in the first 4 lines
            if (count($desc) > 4)
            {
                $max = 4;
            } else {
                $max = count($desc);
            }
        
            for($i = $this->_pv_periodline; $i < $max; $i++)
            {
                if (strlen(trim($desc[$i])) == 0)
                {
                    $this->_pv_periodline = $i;
                    $i = $max;
                }
            }
        }

        $dtype = $this->_pv_dtype;
        if ($dtype == '_pv_docblock')
        {
            $sdesc = new parserDesc;
            $desci = '';
            for($i = 0; ($i <= $this->_pv_periodline) && ($i < count($desc)); $i++)
            {
                $desci .= $desc[$i]."\n";
            }
            $sdesc->add($this->getInlineTags($desci));
            $this->_pv_periodline++;
        
            $my_desc = new parserDesc;
            if (isset($this->_pv_dtemplate))
            {
                // copy template values if not overridden
                if (!$this->_pv_docblock->getExplicitPackage())
                {
                    if ($p = $this->_pv_dtemplate->getKeyword('package'))
                    {
                        $this->_pv_docblock->addKeyword('package',$p);
                        $this->_pv_docblock->setExplicitPackage();
                    }
                    if ($p = $this->_pv_dtemplate->getKeyword('category'))
                    {
                        $this->_pv_docblock->addKeyword('category',$p);
                        $this->_pv_docblock->setExplicitCategory();
                    }
                    if ($p = $this->_pv_dtemplate->getKeyword('subpackage'))
                    {
                        $this->_pv_docblock->addKeyword('subpackage',$p);
                    }
                }
                $tags = $this->_pv_dtemplate->listTags();
                foreach($tags as $tag)
                {
                    $this->_pv_docblock->addTag($tag);
                }
                if (!count($this->_pv_docblock->params)) {
                    $this->_pv_docblock->params = $this->_pv_dtemplate->params;
                }
                if (!$this->_pv_docblock->return) {
                    $this->_pv_docblock->return = $this->_pv_dtemplate->return;
                }
                $my_desc->add($this->_pv_dtemplate->sdesc);
                $my_desc->add($this->_pv_dtemplate->desc);
            }
//            echo "i = ".$this->_pv_periodline."; i < " . count($desc) . "\n";
            $desci = '';
            for($i = $this->_pv_periodline; $i < count($desc); $i++)
            {
                // the line will not be set if it doesn't start with a *
                if (isset($desc[$i]))
                $desci .= $desc[$i]."\n";
            }
            $my_desc->add($this->getInlineTags($desci));
        } else
        { // this is a docblock template
            $sdesc = new parserDesc;
            $desci = '';
            for($i = 0; ($i <= $this->_pv_periodline) && ($i < count($desc)); $i++)
            {
                if (isset($desc[$i]))
                    $desci .= $desc[$i]."\n";
            }
            $sdesc->add($this->getInlineTags($desci));
            $this->_pv_periodline++;
        
            $my_desc = new parserDesc;
            $desci = '';
            for($i=$this->_pv_periodline; $i < count($desc); $i++)
            {
                if (isset($desc[$i]))
                    $desci .= $desc[$i]."\n";
            }
            $my_desc->add($this->getInlineTags($desci));
        }
        if ($this->_pf_internal)
        {
            addError(PDERROR_INTERNAL_NOT_CLOSED);
            $this->_pf_internal = false;
        }
        $this->$dtype->setShortDesc($sdesc);
        $this->$dtype->setDesc($my_desc);
        unset($my_desc);
//            var_dump($this->$dtype);
//            exit;
    }
    
    /**
     * Process the tags of a DocBlock
     * @param array array of lines that contain all @tags
     */
    function handleTags($tags)
    {
        $newtags = array();
        $curtag = '';
        for($i=0;$i < count($tags);$i++)
        {
            if (strpos(trim($tags[$i]),'@') === 0) $tags[$i] = ltrim($tags[$i]);
            if (substr($tags[$i],0,1) == '@' && substr($tags[$i],0,2) != '@ ')
            { // start a new tag
                if (!empty($curtag))
                {
                    $newtags[] = $curtag;
                }
                $curtag = $tags[$i];
            } else $curtag .= "\n".$tags[$i];
        }
        if (!empty($curtag)) $newtags[] = $curtag;
        foreach($newtags as $tag)
        {
            $x = explode(' ',str_replace("\t",'    ',$tag));
            $tagname = trim(substr(array_shift($x),1));
            $restoftag = $x;
            if (isset($this->tagHandlers[$tagname])) {
                $handle = $this->tagHandlers[$tagname];
            } else {
                $handle = $this->tagHandlers['*'];
            }
            $this->$handle($tagname,$restoftag);
        }
    }
    
    /**
     * Process all inline tags in text, and convert them to their abstract
     * object representations.
     * @param string|array complete code to search for inline tags, if an
     *                     array, it's an array of strings
     * @return parserStringWithInlineTags
     */
    function getInlineTags($value)
    {
        if (is_array($value)) $value = join("\n",$value);
        global $_phpDocumentor_setting;
        $priv = (isset($_phpDocumentor_setting['parseprivate']) && $_phpDocumentor_setting['parseprivate'] == 'on');
        $a = new parserStringWithInlineTags();
        if (!$priv && $this->_pf_internal)
        {
            if ($x = strpos($value,'}}'))
            {
                $x = strrpos($value,'}}');
                $value = substr($value,$x + 1);
                $this->_pf_internal = false;
            } else $value = '';
        } elseif ($this->_pf_internal)
        {
            if ($x = strpos($value,'}}'))
            {
                $x = strrpos($value,'}}');
                $value = substr($value,0,$x) . substr($value,$x+2);
            }
        }
        $save = $value;
        $value = explode('{@',$value);
        $newval = array();
        if ($priv || (!$priv && !$this->_pf_internal)) {
            // ignore anything between {@internal and }}
            $a->add($value[0]);
        }
        for($i=1;$i<count($value);$i++)
        {
            if (substr($value[$i],0,1) == '}')
            {
                if ($priv || (!$priv && !$this->_pf_internal)) {
                    // ignore anything between {@internal and }}
                    $a->add('{@'.substr($value[$i],1));
                }
            } elseif (substr($value[$i],0,2) == '*}')
            { // used for inserting */ in code examples
                if ($priv || (!$priv && !$this->_pf_internal)) {
                    // ignore anything between {@internal and }}
                    $a->add('*/'.substr($value[$i],2));
                }
            } else
            {
                $save = $value[$i];
                $value[$i] = split("[\t ]",str_replace("\t",'    ',$value[$i]));
                $word = trim(array_shift($value[$i]));
                $val = join(' ',$value[$i]);
                if (trim($word) == 'internal')
                {
                    if ($this->_pf_internal) {
                        addErrorDie(PDERROR_NESTED_INTERNAL);
                    }
                    $this->_pf_internal = true;
                    $value[$i] = substr($save,strlen('internal') + 1);
                    if (!$value[$i]) {
                        $value[$i] = ''; // substr can set this to false
                    }
                    if (strpos($value[$i],'}}') !== false)
                    {
                        $x = strrpos($value[$i],'}}');
                        // strip internal and cycle as if it were normal text.
                        $startval = substr($value[$i],0,$x - 1);
                        if ($priv) {
                            $a->add($startval);
                        }
                        $value[$i] = substr($value[$i],$x + 1);
                        if (!$value[$i]) $value[$i] = '';
                        $this->_pf_internal = false;
                        $a->add($value[$i]);
                        continue;
                    } elseif ($priv) {
                        $a->add($value[$i]);
                    }
                    continue;
                }
                if (in_array(str_replace('}','',trim($word)),$this->allowableInlineTags))
                {
                    if (strpos($word,'}'))
                    {
                        $res = substr($word,strpos($word, '}'));
                        $word = str_replace('}','',trim($word));
                        $val = $res.$val;
                    }
                    if ($priv || (!$priv && !$this->_pf_internal)) {
                        // ignore anything between {@internal and }}
                        if ($word == 'source')
                        {
                            $this->_pf_get_source = true;
                        }
                    }
                    $val = explode('}',$val);
                    if (count($val) == 1)
                    {
                           addError(PDERROR_UNTERMINATED_INLINE_TAG,$word,'',$save);
                    }
                    $rest = $val;
                    $val = array_shift($rest);
                    $rest = join('}',$rest);
                    if (isset($this->inlineTagHandlers[$word])) {
                        $handle = $this->inlineTagHandlers[$word];
                    } else {
                        $handle = $this->inlineTagHandlers['*'];
                    }
                    $val = $this->$handle($word,$val);
                    if ($priv || (!$priv && !$this->_pf_internal)) {
                        // ignore anything between {@internal and }}
                        $a->add($val);
                    }
                    if ($this->_pf_internal)
                    {
                        if (($x = strpos($rest,'}}')) !== false)
                        {
                            $value[$i] = $rest;
                            $startval = substr($value[$i],0,$x);
                            if ((false !== $startval) && $priv) {
                                $a->add($startval);
                            }
                            $value[$i] = substr($value[$i],$x + 2);
                            if (!$value[$i]) {
                                $value[$i] = '';
                            }
                            $this->_pf_internal = false;
                            $a->add($value[$i]);
                        } else {
                            $rest = explode('}}', $rest);
                            if ($priv) {
                                $a->add(array_shift($rest));
                            }
                            $this->_pf_internal = false;
                            // try this line again without internal
                            $value[$i--] = join('}}', $rest);
                            continue;
                        }
                    } else {
                        $a->add($rest);
                    }
                } else
                {
                    $val = $word.' '.$val;
                    if ($priv || (!$priv && !$this->_pf_internal)) {
                        // ignore anything between {@internal and }}
                        $a->add('{@'.$val);
                    }
                }
            }
        }
        return $a;
    }
    /**#@-*/
    /**#@+
     * @param string name of the tag
     * @param string any parameters passed to the inline tag
     * @access private
     */
    /**
     * Most inline tags require no special processing
     * 
     */
    function handleDefaultInlineTag($name, $value)
    {
        $tag = 'parser'.ucfirst($name).'InlineTag';
        return new $tag($value,$value);
    }
    
    /**
     * Handle the inline {@}link} tag
     * @tutorial tags.inlinelink.pkg
     */
    function handleLinkInlineTag($name, $value)
    {
        // support hyperlinks of any protocol
        if (is_numeric(strpos($value,'://')) || (strpos(trim($value),'mailto:') === 0))
        {
            $value = str_replace('\\,', '###commanana####', $value);
            if (strpos($value,','))
            {
                $val = new parserLinkInlineTag($value,$value);
            } elseif (strpos(trim($value),' '))
            { // if there is more than 1 parameter, the stuff after the space is the hyperlink text
                $i1 = strpos(trim($value),' ') + 1;
                $link = substr(trim($value),0,$i1 - 1);
                $text = substr(trim($value),$i1);
                $val = new parserLinkInlineTag($link,$text);
            } else
            {
                $val = new parserLinkInlineTag($value,$value);
            }
        } else
        {
            $value = str_replace('\\,', '###commanana####', $value);
            if (!strpos($value,','))
            {
                $testp = explode('#',$value);
                if (count($testp) - 1)
                    $val = new parserLinkInlineTag($value,$testp[1]);
                else
                    $val = new parserLinkInlineTag($value,$value);
            } else
                $val = new parserLinkInlineTag($value,$value);
        }
        return $val;
    }

    /**#@-*/
    /**#@+
     * @access private
     * @param string name of tag
     * @param array all words in the tag that were separated by a space ' '
     */
    /**
     * Most tags only need the value as a string
     * @uses getInlineTags() all tag handlers check their values for inline tags
     */
    function defaultTagHandler($name, $value)
    {
        $dtype = $this->_pv_dtype;
        $this->$dtype->addKeyword($name,$this->getInlineTags(join(' ',$value)));
    }
    
    /**
     * @tutorial tags.example.pkg
     * @uses parserDocBlock::addExample()
     */
    function exampleTagHandler($name, $value)
    {
        $dtype = $this->_pv_dtype;
        $this->$dtype->addExample($this->getInlineTags(join(' ',$value)), $this->_path);
    }
    
    /**
     * @tutorial tags.filesource.pkg
     * @uses phpDocumentorTWordParser::getFileSource() retrieves the source for
     *       use in the @filesource tag
     */
    function filesourceTagHandler($name, $value)
    {
        $dtype = $this->_pv_dtype;
        $this->$dtype->addFileSource($this->_path, $this->_wp->getFileSource());
    }
    
    /**
     * @tutorial tags.uses.pkg
     */
    function usesTagHandler($name, $value)
    {
        $dtype = $this->_pv_dtype;
        $seel = '';
        while ($seel == '' && count($value))
        {
            $seel = array_shift($value);
        }
        $this->$dtype->addUses($this->getInlineTags($seel), $this->getInlineTags(join(' ',$value)));
    }
    
    /**
     * @tutorial tags.author.pkg
     */
    function authorTagHandler($name, $value)
    {
        $dtype = $this->_pv_dtype;
        $value = join(' ',$value);
        if ((strpos($value,'<') !== false) && (strpos($value,'>') !== false))
        {
            $email = substr($value,strpos($value,'<') + 1,strpos($value,'>') - strpos($value,'<') - 1);
            $value = str_replace('<'.$email.'>','<{@link mailto:'.$email.' ' .
                        $email . '}>',$value);
        }
        $this->$dtype->addKeyword('author',$this->getInlineTags($value));
    }
    
    /**
     * @tutorial tags.package.pkg
     */
    function packageTagHandler($name, $value)
    {
        if (count($value) && empty($value[0]))
        {
            $found = false;
            for($i=0;$i<count($value) && !strlen($value[$i]);$i++);
            array_splice($value,0,$i);
        }
        $this->defaultTagHandler($name, $value);
        $dtype = $this->_pv_dtype;
        $this->$dtype->setExplicitPackage();
    }
    
    /**
     * @tutorial tags.category.pkg
     */
    function categoryTagHandler($name, $value)
    {
        if (count($value) && empty($value[0]))
        {
            $found = false;
            for($i=0;$i<count($value) && !strlen($value[$i]);$i++);
            array_splice($value,0,$i);
        }
        $this->defaultTagHandler($name, $value);
        $dtype = $this->_pv_dtype;
        $this->$dtype->setExplicitCategory();
    }
    
    /**
     * @tutorial tags.global.pkg
     */
    function globalTagHandler($name, $value)
    {
        $info = $this->retrieveType($value, true);
        if (!$info) addErrorDie(PDERROR_MALFORMED_TAG, '@global');
        $type = $info['type'];
        $var = $info['var'];
        $desc = $info['desc'];
        $dtype = $this->_pv_dtype;
        if (!$var && empty($desc))
        {
            if ($type{0} == '$') addError(PDERROR_MALFORMED_GLOBAL_TAG);
            return $this->$dtype->addFuncGlobal($type,new parserStringWithInlineTags);
        }
        if ($var)
        { // global define
            $this->_pv_global_type = $type;
            if (!empty($desc)) $var .= ' '.$desc;
            $this->findGlobal(trim($var));
        } elseif (!empty($desc))
        { // function global
            if ($type{0} == '$') addError(PDERROR_MALFORMED_GLOBAL_TAG);
            $this->$dtype->addFuncGlobal($type,$this->getInlineTags($desc));
        } else
        {
            addError(PDERROR_MALFORMED_GLOBAL_TAG);
        }
    }
    
    /**
     * @tutorial tags.staticvar.pkg
     */
    function staticvarTagHandler($name, $value)
    {
        $info = $this->retrieveType($value, true);
        if (!$info) addErrorDie(PDERROR_MALFORMED_TAG, '@staticvar');
        $type = $info['type'];
        $var = $info['var'];
        $desc = $info['desc'];
        $dtype = $this->_pv_dtype;
        if (!$var && empty($desc))
        {
            $this->$dtype->addStaticVar(null,$type,new parserStringWithInlineTags);
        } else
        {
            if ($var)
            {
                $this->$dtype->addStaticVar($var,$type,$this->getInlineTags($desc));
            } else
            {
                $this->$dtype->addStaticVar(null,$type,$this->getInlineTags($desc));
            }
        }
    }
    
    /**
     * @tutorial tags.param.pkg
     */
    function paramTagHandler($name, $value)
    {
        $info = $this->retrieveType($value, true);
        //if (!$info) addErrorDie(PDERROR_MALFORMED_TAG, '@param');
	    if (!$info) { addError(PDERROR_MALFORMED_TAG, '@param'); return; }
        $type = $info['type'];
        $var = $info['var'];
        $desc = $info['desc'];
        $dtype = $this->_pv_dtype;
        if (!$var && empty($desc))
        {
            $this->$dtype->addParam(null,$type,new parserStringWithInlineTags);
        } else
        {
            if ($var)
            {
                $this->$dtype->addParam($var,$type,$this->getInlineTags($desc));
            } else
            {
                $this->$dtype->addParam(null,$type,$this->getInlineTags($desc));
            }
        }
    }
    
    /**
     * @tutorial tags.return.pkg
     */
    function returnTagHandler($name, $value)
    {
        $info = $this->retrieveType($value, true);
        //if (!$info) addErrorDie(PDERROR_MALFORMED_TAG, '@return');
        if (!$info) { addError(PDERROR_MALFORMED_TAG, '@return'); return; }
        $type = $info['type'];
        $desc = $info['desc'];
        $dtype = $this->_pv_dtype;
        $this->$dtype->addReturn($type,$this->getInlineTags($desc));
    }
    
    /**
     * @tutorial tags.var.pkg
     */
    function varTagHandler($name, $value)
    {
        $info = $this->retrieveType($value, true);
        if (!$info) addErrorDie(PDERROR_MALFORMED_TAG, '@var');
        $type = $info['type'];
        $desc = $info['desc'];
        $dtype = $this->_pv_dtype;
        $this->$dtype->addVar($type,$this->getInlineTags($desc));
    }
    /**#@-*/
    /**#@+
     * @access private
     */
    
    /**
     * Retrieve the type portion of a @tag type description
     *
     * Tags like @param, @return and @var all have a PHP type portion in their
     * description.  Since the type may contain the expression "object blah"
     * where blah is a classname, it makes parsing out the type field complex.
     *
     * Even more complicated is the case where a tag variable can contain
     * multiple types, such as object blah|object blah2|false, and so this
     * method handles these cases.
     * @param array array of words that were separated by spaces
     * @param boolean flag to determine whether to check for the end of a
     *        type is defined by a $varname
     * @return array Format: array('type' => string, 'var' =>
     *                             false|string variable name, 'desc' => rest
     *                             of the tag)
     */
    function retrieveType($value, $checkforvar = false)
    {
        if (!count($value)) return false;
        $result = array();
        $types = '';
        // remove empty entries resulting from extra spaces between @tag and type
        $this->_removeWhiteSpace($value, 0);
        $index = 0;
        if (trim($value[0]) == 'object')
        {
            $types .= array_shift($value).' ';
            $this->_removeWhiteSpace($value, 0);
            if (!count($value))
            { // was just passed "object"
                $result = array('type' => rtrim($types),'desc' => '');
                if ($checkforvar) $result['var'] = false;
                return $result;
            }
            if ($value[0]{0} == '$' || substr($value[0],0,2) == '&$')
            { // was just passed "object" and the next thing is a variable name
                $result['var'] = trim($value[0]);
                $result['type'] = 'object';
                array_shift($value);
                $result['desc'] = join(' ', $value);
                return $result;
            }
        }
        $done = false;
        do
        { // this loop checks for type|type|type and for
          // type|object classname|type|object classname2
            if (strpos($value[0], '|'))
            {
                $temptypes = explode('|', $value[0]);
                while(count($temptypes))
                {
                    $type = array_shift($temptypes);
                    $types .= $type;
                    if (count($temptypes)) $types .= '|';
                }
                if (trim($type) == 'object')
                {
                    $types .= ' ';
                    $this->_removeWhiteSpace($value,0);
                } else $done = true;
                array_shift($value);
                if (isset ($value[0]) && strlen($value[0]) && ($value[0]{0} == '$' || substr($value[0],0,2) == '&$'))
                { // was just passed "object" and the next thing is a variable name
                    $result['var'] = trim($value[0]);
                    $result['type'] = $types;
                    array_shift($value);
                    $result['desc'] = join(' ', $value);
                    return $result;
                }
            } else
            {
                $types .= $value[0];
                array_shift($value);
                $done = true;
            }
        } while (!$done && count($value));
        $result['type'] = rtrim($types);
        $this->_removeWhiteSpace($value,0);
        if ($checkforvar)
        {
            if (!count($value))
            {
                $result['var'] = false;
            } else
            {
                if (substr($value[0],0,1) == '$' || substr($value[0],0,2) == '&$')
                {
                    $result['var'] = trim($value[0]);
                    array_shift($value);
                } else $result['var'] = false;
            }
        }
        $result['desc'] = join(' ',$value);
        return $result;
    }
    
    /**
     * @param array array of string
     * @param integer index to seek non-whitespace to
     */
    function _removeWhiteSpace(&$value, $index)
    {
        if (count($value) > $index && empty($value[$index]))
        {
            $found = false;
            for($i=$index; $i<count($value) && !strlen($value[$i]); $i++);
            array_splice($value, $index, $i - $index);
        }
    }
    
    /**
     * Retrieve all the tokens that represent the definition of the global
     * variable.
     *
     * {@source}
     */
    function findGlobal($name)
    {
        $tokens = token_get_all('<?php '.$name);
        $tokens = array_slice($tokens,1);
        $this->_wp->findGlobal($tokens);
        $this->_pv_findglobal = $name;
    }

    /**
     * handler for DEFINE_GLOBAL
     */
    function handleGlobal($word, $pevent)
    {
        if (isset($this->_pv_findglobal))
        {
            $this->_pv_global_name = $this->_pv_findglobal;
            unset($this->_pv_findglobal);
        }
        if (!$this->_pf_in_global)
        {
            $this->_pv_linenum = $this->_wp->linenum + 1;
        }
        $this->_pf_in_global = true;
        if($this->checkEventPush($word, $pevent))
        {
            $this->_wp->setWhitespace(true);
        }
        if ($this->checkEventPop($word, $pevent))
        {
            $this->_pf_in_global = false;
            $a = new parserGlobal;
            $a->setDataType($this->_pv_global_type);
            $this->_pv_global_type = '';
            $a->setLineNumber($this->_pv_linenum);
            $a->setName($this->_pv_global_name);
            if (isset($this->_pv_global_val))
            $a->setValue(trim($this->_pv_global_val));
            $this->publishEvent(PHPDOCUMENTOR_EVENT_GLOBAL,$a);
            unset($this->_pv_global_val);
            unset($this->_pv_global_type);
        }
    }
    
    /**
     * handler for GLOBAL_VALUE
     */
    function handleGlobalValue($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) return;
        $this->_wp->setWhitespace(true);
        if (!isset($this->_pv_global_val)) $this->_pv_global_val = '';
        if ($this->_last_pevent == PARSER_EVENT_ARRAY)
        {
            $this->_pv_global_val .= $this->_pv_function_data;
            $this->_pv_function_data = '';
        }
        if ($this->_last_pevent == PARSER_EVENT_QUOTE ||
            $this->_last_pevent == PARSER_EVENT_EOFQUOTE)
        {
            $this->_pv_global_val .= $this->_pv_quote_data;
            unset($this->_pv_quote_data);
        }
        if ($this->checkEventPop($word, $pevent))
        {
            $this->_wp->setWhitespace(false);
            $this->_wp->backupPos();
            return;
        }
        if (is_array($word)) $word = $word[1];
        $this->_pv_global_val .= $word;
    }
    
    /**#@-*/
    /**
     * this function checks whether parameter $word is a token for pushing a new event onto the Event Stack.
     * @return mixed    returns false, or the event number
     */
    
    function checkEventPush($word,$pevent)
    {
        if (is_array($word) && $word[0] == T_STRING) $word = $word[1];
        if (is_array($word))
        {
            $pushEvent = &$this->tokenpushEvent;
            $word = $word[0];
        } else
        {
            $pushEvent = &$this->wordpushEvent;
            $word = strtolower($word);
        }
        $e = false;
        if (isset($pushEvent[$pevent]))
        {
            if (isset($pushEvent[$pevent][$word]))
            $e = $pushEvent[$pevent][$word];
        }
        if ($e)
        {
            $this->_event_stack->pushEvent($e);
            return $e;
        } else {
            return false;
        }
    }

    /**
     * this function checks whether parameter $word is a token for popping the current event off of the Event Stack.
     * @return mixed    returns false, or the event number popped off of the stack
     */
    
    function checkEventPop($word,$pevent)
    {
        if (is_array($word) && $word[0] == T_STRING) $word = $word[1];
        if (is_array($word))
        {
            $popEvent = &$this->tokenpopEvent;
            $word = $word[0];
        } else
        {
            $popEvent = &$this->wordpopEvent;
            $word = strtolower($word);
        }
        if (!isset($popEvent[$pevent])) return false;
        if (in_array($word,$popEvent[$pevent]))
        {
            return $this->_event_stack->popEvent();
        } else {
            return false;
        }
    }

    function getToken($word)
    {
        if (is_array($word)) return $word[0];
        return false;
    }
    
    /**
     * setup the parser tokens, and the pushEvent/popEvent arrays
     * @see $tokens, $pushEvent, $popEvent
     */
    
    function setupStates()
    {
        unset($this->_wp);
        $this->_wp = new phpDocumentorTWordParser;
        $this->_pv_class = null;
        $this->_pv_cur_class = null;
        $this->_pv_define = null;
        $this->_pv_define_name = null;
        $this->_pv_define_value = null;
        $this->_pv_define_params_data = null;
        $this->_pv_dtype = null;
        $this->_pv_docblock = null;
        $this->_pv_dtemplate = null;
        $this->_pv_func = null;
        $this->_pv_findglobal = null;
        $this->_pv_global_name = null;
        $this->_pv_global_val = null;
        $this->_pv_globals = null;
        $this->_pv_global_count = null;
        $this->_pv_include_params_data = null;
        $this->_pv_include_name = null;
        $this->_pv_include_value = null;
        $this->_pv_linenum = null;
        $this->_pv_periodline = null;
        $this->_pv_paren_count = 0;
        $this->_pv_statics = null;
        $this->_pv_static_count = null;
        $this->_pv_static_val = null;
        $this->_pv_quote_data = null;
        $this->_pv_function_data = null;
        $this->_pv_var = null;
        $this->_pv_varname = null;
        $this->_pv_const = null;
        $this->_pv_constname = null;
        $this->_pv_function_param_type = null;
        $this->_pf_definename_isset = false;
        $this->_pf_includename_isset = false;
        $this->_pf_get_source = false;
        $this->_pf_getting_source = false;
        $this->_pf_in_class = false;
        $this->_pf_in_define = false;
        $this->_pf_in_global = false;
        $this->_pf_in_include = false;
        $this->_pf_in_var = false;
        $this->_pf_in_const = false;
        $this->_pf_funcparam_val = false;
        $this->_pf_quote_active = false;
        $this->_pf_reset_quote_data = true;
        $this->_pf_useperiod = false;
        $this->_pf_var_equals = false;
        $this->_pf_const_equals = false;
        $this->_event_stack = new EventStack;
    }
    
    /**
     * Creates the state arrays
     */
    function setupEventStates()
    {
        if (!defined('T_DOC_COMMENT'))
        {
            define('T_DOC_COMMENT', T_DOC_COMMENT);
        }
/**************************************************************/

        $this->wordpushEvent[PARSER_EVENT_LOGICBLOCK] = 
            array(
                "{"    => PARSER_EVENT_LOGICBLOCK,
                '"'    => PARSER_EVENT_QUOTE,
            );
        $this->tokenpushEvent[PARSER_EVENT_LOGICBLOCK] =
            array(
                T_GLOBAL    => PARSER_EVENT_FUNC_GLOBAL,
                T_STATIC    => PARSER_EVENT_STATIC_VAR,
                T_START_HEREDOC   => PARSER_EVENT_EOFQUOTE,
                T_CURLY_OPEN    => PARSER_EVENT_LOGICBLOCK,
                T_DOLLAR_OPEN_CURLY_BRACES => PARSER_EVENT_LOGICBLOCK,
            );

        $this->wordpopEvent[PARSER_EVENT_LOGICBLOCK] = array("}");
        $this->tokenpopEvent[PARSER_EVENT_LOGICBLOCK] = array(T_CURLY_OPEN);
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                T_OPEN_TAG => PARSER_EVENT_PHPCODE,
            );

/**************************************************************/

        $this->tokenpopEvent[PARSER_EVENT_EOFQUOTE] = array(T_END_HEREDOC);
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_PHPCODE] = 
            array(
                T_FUNCTION     => PARSER_EVENT_FUNCTION,
                T_ABSTRACT => PARSER_EVENT_ACCESS_MODIFIER,
                T_CLASS     => PARSER_EVENT_CLASS,
                T_INTERFACE => PARSER_EVENT_CLASS,
                T_INCLUDE_ONCE => PARSER_EVENT_INCLUDE,
                T_INCLUDE => PARSER_EVENT_INCLUDE,
                T_REQUIRE    => PARSER_EVENT_INCLUDE,
                T_REQUIRE_ONCE    => PARSER_EVENT_INCLUDE,
                T_COMMENT   => PARSER_EVENT_DOCBLOCK,
                T_DOC_COMMENT         => PARSER_EVENT_DOCBLOCK,
//                "/**#@+"    => PARSER_EVENT_DOCBLOCK_TEMPLATE,
//                "/**#@-*/"    => PARSER_EVENT_END_DOCBLOCK_TEMPLATE,
                T_CLOSE_TAG         => PARSER_EVENT_OUTPHP,
            );
        $this->wordpushEvent[PARSER_EVENT_PHPCODE] =
            array(
                "define"     => PARSER_EVENT_DEFINE,
            );
/**************************************************************/

        $this->tokenpopEvent[PARSER_EVENT_OUTPHP] = array(T_OPEN_TAG);
/**************************************************************/

        $this->wordpushEvent[PARSER_EVENT_FUNCTION] =
            array(
                '{'     => PARSER_EVENT_LOGICBLOCK,
                '('     => PARSER_EVENT_FUNCTION_PARAMS,
            );
        $this->tokenpushEvent[PARSER_EVENT_FUNCTION] =
            array(
                T_COMMENT   => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT => PARSER_EVENT_COMMENT,
            );

        $this->wordpopEvent[PARSER_EVENT_FUNCTION] = array("}",';');
/**************************************************************/

        $this->wordpopEvent[PARSER_EVENT_QUOTE] = array('"');
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_FUNCTION_PARAMS] =
            array(
                T_VARIABLE => PARSER_EVENT_FUNCTION_PARAM_VAR,
                T_COMMENT => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT => PARSER_EVENT_COMMENT,
            );
        $this->wordpopEvent[PARSER_EVENT_FUNCTION_PARAMS] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_FUNCTION_PARAM_VAR] =
            array(
                T_CONSTANT_ENCAPSED_STRING => PARSER_EVENT_QUOTE,
                T_COMMENT => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT => PARSER_EVENT_COMMENT,
                T_ARRAY => PARSER_EVENT_ARRAY,
            );
        $this->wordpushEvent[PARSER_EVENT_FUNCTION_PARAM_VAR] =
            array(
                '"' => PARSER_EVENT_QUOTE,
                "'" => PARSER_EVENT_QUOTE,
            );
        $this->wordpopEvent[PARSER_EVENT_FUNCTION_PARAM_VAR] = array(",", ")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_ARRAY] = 
            array(
                T_COMMENT  => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
            );
        $this->wordpopEvent[PARSER_EVENT_ARRAY] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_VAR_ARRAY] = 
            array(
                T_COMMENT  => PARSER_EVENT_VAR_ARRAY_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_VAR_ARRAY_COMMENT,
            );
        $this->wordpopEvent[PARSER_EVENT_VAR_ARRAY] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_FUNC_GLOBAL] =
            array(
                T_COMMENT   => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT    => PARSER_EVENT_COMMENT,
            );

        $this->wordpopEvent[PARSER_EVENT_FUNC_GLOBAL] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_STATIC_VAR] =
            array(
                T_CONSTANT_ENCAPSED_STRING  => PARSER_EVENT_QUOTE,
                T_COMMENT   => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT    => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_STATIC_VAR] =
            array(
                "="        => PARSER_EVENT_STATIC_VAR_VALUE,
            );
        $this->wordpopEvent[PARSER_EVENT_STATIC_VAR] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_STATIC_VAR_VALUE] = 
            array(
                T_CONSTANT_ENCAPSED_STRING  => PARSER_EVENT_QUOTE,
                T_COMMENT   => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT    => PARSER_EVENT_COMMENT,
                T_ARRAY     => PARSER_EVENT_ARRAY,
            );
        $this->wordpushEvent[PARSER_EVENT_STATIC_VAR_VALUE] =
            array(
                '"' => PARSER_EVENT_QUOTE,
                "'" => PARSER_EVENT_QUOTE,
            );
        $this->wordpopEvent[PARSER_EVENT_STATIC_VAR_VALUE] = array(";",",");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_DEFINE] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
                T_CONSTANT_ENCAPSED_STRING        => PARSER_EVENT_QUOTE,
            );
        $this->wordpushEvent[PARSER_EVENT_DEFINE] = 
            array(
                "("     => PARSER_EVENT_DEFINE_PARAMS,
            );
        $this->wordpopEvent[PARSER_EVENT_DEFINE] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_INCLUDE] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_INCLUDE] = 
            array(
                "("     => PARSER_EVENT_INCLUDE_PARAMS,
            );
        $this->wordpopEvent[PARSER_EVENT_INCLUDE] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_DEFINE_PARAMS] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
                T_START_HEREDOC => PARSER_EVENT_EOFQUOTE,
            );
        $this->wordpushEvent[PARSER_EVENT_DEFINE_PARAMS] = 
            array(
                "("    =>    PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS,
                '"' => PARSER_EVENT_QUOTE,
                "'" => PARSER_EVENT_QUOTE,
            );
        $this->wordpopEvent[PARSER_EVENT_DEFINE_PARAMS] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_INCLUDE_PARAMS] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_INCLUDE_PARAMS] = 
            array(
                "("    =>    PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS,
            );
        $this->wordpopEvent[PARSER_EVENT_INCLUDE_PARAMS] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS] =
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS] =
            array(
                "("    =>    PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS,
                '"' => PARSER_EVENT_QUOTE,
                "'" => PARSER_EVENT_QUOTE,
            );
        $this->wordpopEvent[PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS] =
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS] =
            array(
                "("    =>    PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS,
            );
        $this->wordpopEvent[PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS] = array(")");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_VAR] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
                T_ARRAY     => PARSER_EVENT_VAR_ARRAY,
            );
        $this->wordpopEvent[PARSER_EVENT_VAR] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_CLASS_CONSTANT] = 
            array(
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
                T_ARRAY     => PARSER_EVENT_VAR_ARRAY,
            );
        $this->wordpopEvent[PARSER_EVENT_CLASS_CONSTANT] = array(";");
/**************************************************************/

        $this->wordpopEvent[PARSER_EVENT_IMPLEMENTS] = array('{');
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_CLASS] = 
            array(
                T_ABSTRACT      => PARSER_EVENT_ACCESS_MODIFIER,
                T_PUBLIC      => PARSER_EVENT_ACCESS_MODIFIER,
                T_PRIVATE      => PARSER_EVENT_ACCESS_MODIFIER,
                T_PROTECTED      => PARSER_EVENT_ACCESS_MODIFIER,
                T_STATIC      => PARSER_EVENT_ACCESS_MODIFIER,
                T_IMPLEMENTS      => PARSER_EVENT_IMPLEMENTS,
                T_CONST        => PARSER_EVENT_CLASS_CONSTANT,
                T_FUNCTION     => PARSER_EVENT_FUNCTION,
                T_VAR         => PARSER_EVENT_VAR,
                T_COMMENT         => PARSER_EVENT_DOCBLOCK,
                T_DOC_COMMENT         => PARSER_EVENT_DOCBLOCK,
                T_CLOSE_TAG        => PARSER_EVENT_OUTPHP,
            );
        $this->wordpopEvent[PARSER_EVENT_CLASS] = array("}");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_DEFINE_GLOBAL] = 
            array(
                T_COMMENT => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT => PARSER_EVENT_COMMENT,
            );
        $this->wordpushEvent[PARSER_EVENT_DEFINE_GLOBAL] = 
            array(
                "="    => PARSER_EVENT_GLOBAL_VALUE,
            );
        $this->wordpopEvent[PARSER_EVENT_DEFINE_GLOBAL] = array(";");
/**************************************************************/

        $this->tokenpushEvent[PARSER_EVENT_GLOBAL_VALUE] = 
            array(
                T_ARRAY => PARSER_EVENT_ARRAY,
                T_COMMENT     => PARSER_EVENT_COMMENT,
                T_DOC_COMMENT     => PARSER_EVENT_COMMENT,
                T_START_HEREDOC => PARSER_EVENT_EOFQUOTE,
            );
        $this->wordpushEvent[PARSER_EVENT_GLOBAL_VALUE] =
            array(
                '"' => PARSER_EVENT_QUOTE,
                "'" => PARSER_EVENT_QUOTE,
            );
        $this->wordpopEvent[PARSER_EVENT_GLOBAL_VALUE] = array(";");
    }
    
    function configWordParser(&$data)
    {
        $this->_wp->setup($data);
        $this->_wp->setWhitespace(false);
    }
}
?>