<?php
/**
 * Base parser for all parsers
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2000-2006 Joshua Eichorn, Gregory Beaver
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
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2000-2006 Joshua Eichorn, Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      0.1
 */
/** used when a backslash is encountered in parsing a string or other escapable entity */ 
define("PARSER_EVENT_ESCAPE"        ,    900);
/** used when a backslash is encountered in parsing a string or other escapable entity */ 
define("STATE_ESCAPE"            ,    1000);

/** Class published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_CLASS"        ,    800);
/** DocBlock published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_DOCBLOCK"        ,    801);
/** Function published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_FUNCTION"        ,    802);
/** Class Variable published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_VAR"        ,    803);
/** New File (page) published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_PAGE"        ,    804);
/** Constant (define) published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_DEFINE"        ,    805);
/** Class Constant published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_CONST"        ,    806);
/** @deprecated */
define("PHPDOCUMENTOR_EVENT_MESSAGE"        ,    807);
/** use to inform IntermediateParser of a new element being parsed */
define("PHPDOCUMENTOR_EVENT_NEWSTATE"        ,    808);
/**
 * used to inform phpDocumentor_IntermediateParser that the current file has been completely parsed.
 * Render then flushes all buffers for functions/classes/defines/includes on the current page
 * @see phpDocumentor_IntermediateParser::HandleEvent()
 */
define("PHPDOCUMENTOR_EVENT_END_PAGE"        ,    808);
/** Package-level page published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_PACKAGEPAGE"        ,    809);
/** Include (include/require/include_once/include_once) published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_INCLUDE"        ,    810);
/** Tutorial published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_TUTORIAL"        ,    811);
/** Contents of README/INSTALL/CHANGELOG files published to IntermediateParser with this event */
define("PHPDOCUMENTOR_EVENT_README_INSTALL_CHANGELOG"        ,    812);

/** use to inform ErrorTracker of a new file being parsed */
define("PHPDOCUMENTOR_EVENT_NEWFILE"    ,    811);
/** use to inform ErrorTracker of the next line number being parsed */
define("PHPDOCUMENTOR_EVENT_NEWLINENUM"    ,    812);
/** used when a global variable definition is encountered in the source */
define("PHPDOCUMENTOR_EVENT_GLOBAL"    ,    813);
/** used when a docblock template is encountered in the source */
define("PHPDOCUMENTOR_EVENT_DOCBLOCK_TEMPLATE"    ,    814);
/** used when a docblock template is encountered in the source */
define("PHPDOCUMENTOR_EVENT_END_DOCBLOCK_TEMPLATE"    ,    815);
/** used when double quotation mark (") encountered in parsing */
define("PARSER_EVENT_QUOTE"        ,    101);
/** currently parsing a quote */
define("STATE_QUOTE"            ,    201);

/** { encountered in parsing a function or php code */
define("PARSER_EVENT_LOGICBLOCK"    ,    102);
/** currently parsing a { } block */
define("STATE_LOGICBLOCK"        ,    202);

/** used for the beginning of parsing, before first < ? php encountered */
define("PARSER_EVENT_NOEVENTS"        ,    103);
/** out of < ? php tag */
define("STATE_NOEVENTS"            ,    203);

/** used when long comment /x x/ where x is an asterisk is encountered in parsing */
define("PARSER_EVENT_COMMENTBLOCK"    ,    104);
/** currently parsing a long comment /x x/ where x is an asterisk */
define("STATE_COMMENTBLOCK"        ,    204);

/** used when short comment // is encountered in parsing */
define("PARSER_EVENT_COMMENT"        ,    105);
/** currently parsing a short comment // */
define("STATE_COMMENT"            ,    205);

/** used when php code processor instruction (< ? php) is encountered in parsing */
define("PARSER_EVENT_PHPCODE"        ,    106);
/** currently parsing php code */
define("STATE_PHPCODE"            ,    206);

/** used when a define statement is encountered in parsing */
define("PARSER_EVENT_DEFINE"        ,    107);
/** currently parsing a define statement */
define("STATE_DEFINE"            ,    207);

/** used when a define statement opening parenthesis is encountered in parsing */
define("PARSER_EVENT_DEFINE_PARAMS"    ,    108);
/** currently parsing the stuff in ( ) of a define statement */
define("STATE_DEFINE_PARAMS"        ,    208);

/** used when a function statement opening parenthesis is encountered in parsing */
define("PARSER_EVENT_FUNCTION_PARAMS"    ,    109);
/** currently parsing the stuff in ( ) of a function definition */
define("STATE_FUNCTION_PARAMS"        ,    209);

/** used when a single quote (') is encountered in parsing */
define("PARSER_EVENT_SINGLEQUOTE"    ,    110);
/** currently parsing a string enclosed in single quotes (') */
define("STATE_SINGLEQUOTE"        ,    210);

/** used when a class definition is encountered in parsing */
define("PARSER_EVENT_CLASS"        ,    111);
/** currently parsing a class definition */
define("STATE_CLASS"            ,    211);
/** used to tell Render that a class has been completely parsed, and to flush buffers */
define("STATE_END_CLASS"        ,    311);

/** used when a DocBlock is encountered in parsing */
define("PARSER_EVENT_DOCBLOCK"        ,    112);
/** currently parsing a DocBlock */
define("STATE_DOCBLOCK"            ,    212);

/** used when a @tag is encountered in DocBlock parsing */
define("PARSER_EVENT_DOCKEYWORD"    ,    113);
/** currently parsing a @tag in a DocBlock */
define("STATE_DOCKEYWORD"        ,    213);

/** used when a <email@address> is encountered in parsing an @author tag*/
define("PARSER_EVENT_DOCKEYWORD_EMAIL"    ,    114);
/** currently parsing an email in brackets in an @author tag of a DocBlock */
define("STATE_DOCKEYWORD_EMAIL"        ,    214);

/** used when an array definition is encountered in parsing */
define("PARSER_EVENT_ARRAY"        ,    115);
/** currently parsing an array */
define("STATE_ARRAY"            ,    215);

/** used when a var statement is encountered in parsing a class definition */
define("PARSER_EVENT_VAR"        ,    116);
/** currently parsing a Class variable */
define("STATE_VAR"            ,    216);

/** used when a function definition is encountered in parsing */
define("PARSER_EVENT_FUNCTION"        ,    117);
/** currently parsing a Function or Method */
define("STATE_FUNCTION"            ,    217);

/** used when a ? > (with no space) is encountered in parsing */
define("PARSER_EVENT_OUTPHP"        ,    118);
/** currently out of php code */
define("STATE_OUTPHP"            ,    218);

/** used when an inline {@tag} is encountered in parsing a DocBlock */
define("PARSER_EVENT_INLINE_DOCKEYWORD"    ,    119);
/** currently parsing an inline tag like { @link} in a DocBlock */
define("STATE_INLINE_DOCKEYWORD"        ,    219);

/** used when a define statement's opening parenthesis is encountered in parsing */
define("PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS"    ,    120);
/** currently parsing an inner parenthetical statement of a define( ) */
define("STATE_DEFINE_PARAMS_PARENTHESIS"        ,    220);

define("PARSER_EVENT_END_STATEMENT",    121);

/** used when a <<< is encountered in parsing */
define("PARSER_EVENT_EOFQUOTE"    ,    122);
/** currently parsing a string defined using Perl <<< */
define("STATE_EOFQUOTE"        ,    222);

/** used when an include/require/include_once/include_once statement is encountered in parsing */
define("PARSER_EVENT_INCLUDE"    ,    123);
/** currently parsing an include/require/include_once/include_once */
define("STATE_INCLUDE"    ,    223);

/** used when an opening parenthesis of an include/require/include_once/include_once statement is encountered in parsing */
define("PARSER_EVENT_INCLUDE_PARAMS"    ,    124);
/** currently parsing the stuff in ( ) of a define statement */
define("STATE_INCLUDE_PARAMS"    ,    224);

/** used when an inner ( ) is encountered while parsing an include/require/include_once/include_once statement */
define("PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS"    ,    125);
/** currently parsing an inner parenthetical statement of an include/includeonce/require/requireonce( ) */
define("STATE_INCLUDE_PARAMS_PARENTHESIS"    ,    225);

/** used when parsing the desc part of a docblock */
define("PARSER_EVENT_DESC"    ,    126);
/** currently parsing the desc part of a docblock */
define("STATE_DESC"    ,    226);

/** used when parsing the @tag block of a docblock */
define("PARSER_EVENT_TAGS"    ,    127);
/** currently parsing the @tag block of a docblock */
define("STATE_TAGS"    ,    227);

/** used when parsing a global variable declaration */
define("PARSER_EVENT_DEFINE_GLOBAL"    ,    128);
/** currently parsing a global variable declaration */
define("STATE_GLOBAL"    ,    228);

/** used when parsing the default value in a global variable declaration */
define("PARSER_EVENT_GLOBAL_VALUE"    ,    129);
/** currently parsing the default value in a global variable declaration */
define("STATE_GLOBAL_VALUE"    ,    229);

/** used when parsing a "global $var1, $var2;" declaration in a function */
define("PARSER_EVENT_FUNC_GLOBAL"    ,    130);
/** currently parsing a "global $var1, $var2;" declaration in a function */
define("STATE_FUNC_GLOBAL"    ,    230);

/** used when parsing a "static $var1, $var2;" declaration in a function */
define("PARSER_EVENT_STATIC_VAR"    ,    131);
/** currently parsing a "static $var1, $var2;" declaration in a function */
define("STATE_STATIC_VAR"    ,    231);

/** used when parsing the value in a "static $var1 = x" declaration in a function */
define("PARSER_EVENT_STATIC_VAR_VALUE"    ,    132);
/** currently parsing the value in a "static $var1 = x" declaration in a function */
define("STATE_STATIC_VAR_VALUE"    ,    232);

/** used when encountering a /**#@+ comment marking a new docblock template */
define("PARSER_EVENT_DOCBLOCK_TEMPLATE"    ,    133);
/** currently parsing the value in a "static $var1 = x" declaration in a function */
define("STATE_DOCBLOCK_TEMPLATE"    ,    233);

/** used when encountering a /**#@-* / comment (no space) marking the end of using a docblock template */
define("PARSER_EVENT_END_DOCBLOCK_TEMPLATE"    ,    134);
/** currently parsing the value in a "static $var1 = x" declaration in a function */
define("STATE_END_DOCBLOCK_TEMPLATE"    ,    234);

/** used by the {@link HighlightParser} only, when a method starts */
define("PARSER_EVENT_METHOD"    ,    135);
/** currently parsing a method using the {@link HighlightParser} */
define("STATE_METHOD"    ,    235);

/** used by the {@link HighlightParser} only, when a method body is parsed */
define("PARSER_EVENT_METHOD_LOGICBLOCK"    ,    136);
/** currently parsing the method body using the {@link HighlightParser} */
define("STATE_METHOD_LOGICBLOCK"    ,    236);

/** used by the {@link HighlightParser} only, when ->var or ->function() is encountered in a method */
define("PARSER_EVENT_CLASS_MEMBER"    ,    137);
/** currently parsing a class member using the {@link HighlightParser} */
define("STATE_CLASS_MEMBER"    ,    237);

/** used by the {@link HighlightParser} only, when {$var} is encountered in a string */
define("PARSER_EVENT_QUOTE_VAR"    ,    138);
/** currently parsing a {$encapsed_var} using the {@link HighlightParser} */
define("STATE_QUOTE_VAR"    ,    238);

/** used when parsing an access modifier */
define("PARSER_EVENT_ACCESS_MODIFIER"    ,    139);
/** currently parsing an access modifier */
define("STATE_ACCESS_MODIFIER"    ,    239);

/** used when a class implements interfaces */
define("PARSER_EVENT_IMPLEMENTS"    ,    140);
/** currently parsing an implements clause */
define("STATE_IMPLEMENTS"    ,    240);

/** used when a class implements interfaces */
define("PARSER_EVENT_CLASS_CONSTANT"    ,    141);
/** currently parsing a class constant */
define("STATE_CLASS_CONSTANT"    ,    241);

/** used when a variable value is an array */
define("PARSER_EVENT_VAR_ARRAY"    ,    142);
/** currently parsing a variable value is an array */
define("STATE_VAR_ARRAY"    ,    242);

/** used when a comment is found in a variable array value */
define("PARSER_EVENT_VAR_ARRAY_COMMENT"    ,    143);
/** currently parsing a comment in a variable array value */
define("STATE_VAR_ARRAY_COMMENT"    ,    243);

/** used when a $param is encountered in a function definition */
define("PARSER_EVENT_FUNCTION_PARAM_VAR", 144);
/** currently parsing a $param in a function definition */
define("STATE_FUNCTION_PARAM_VAR", 244);

if (!defined('T_INTERFACE'))
{
    define('T_INTERFACE', 'foo');
    if (!defined('T_CONST')) {
        define('T_CONST', 'foo');
    }
    define('T_ABSTRACT', 'foo');
    define('T_PRIVATE', 'foo');
    define('T_PUBLIC', 'foo');
    define('T_PROTECTED', 'foo');
    define('T_FINAL', 'foo');
    define('T_IMPLEMENTS', 'foo');
}
if (!defined('T_ML_COMMENT'))
{
    define('T_ML_COMMENT', T_COMMENT);
}
if (!defined('T_DOC_COMMENT'))
{
    define('T_DOC_COMMENT', T_ML_COMMENT);
}
/**
 * PHP Parser for PHP 4.2.3-
 *
 * This parser is slower than the tokenizer-based parser, and is deprecated.
 * @author    Joshua Eichorn <jeichorn@phpdoc.org>
 * @author    Gregory Beaver <cellog@users.sourceforge.net>
 * @version    $Id$
 * @package     phpDocumentor
 * @subpackage Parsers
 * @deprecated in favor of {@link phpDocumentorTParser}
 */
class Parser extends Publisher
{
    /**#@+
     * @access private
     */
    /**
     * Word parser
     * @see WordParser
     */
    var $wp;
    
    /**
     * temporary parser variables
     */
    var $p_vars = array('func' => false, 'function_data' => '', 'quote_data' => '', 'event_stack' => false, 'last_pevent' => 0,
                        'two_words_ago' => '', 'temp_word' => '', 'docblock' => false, 'line' => array(), 'linecount' => 0, 'startword' => '',
                        'periodline' => 0, 'shortdesc' => '', 'docblock_desc' => '', 'class' => false, 'source_location' => '',
                        'define_params_data' => '', 'define' => false, 'define_name' => '', 'define_value' => '', 'var' => false,
                        'oldtoken' => false, 'comment_data' => '', 'function_param' => NULL, 'inline_dockeyword_type' => false,
                        'inline_dockeyword_data' => false, 'dockeyword_type' => false, 'dockeyword_data' =>false, 'param_var' => false,
                        'include_name' => '', 'include_value' => '','include' => false, 'return_type' => '', 'cur_class' => '',
                        'function_data' => false, 'varname' => '', 'returntype' => false, 'vartype' => false, 'paramtype' => false,
                        'tagname' => '', 'find_global' => '', 'global_type' => '', 'paramname' => false, 'statics' => array(),
                        'static_count' => 0, 'static_val' => array(), 'docblock_type' => 'docblock', 'seelement' => false);
    
    /**
     * parser flags, for states that don't warrant a new event (like new line in a docblock)
     */
    var $p_flags = array('docblocknewline' => false, 'docblockintags' => false, 'useperiod' => false,
                        'definename_isset' => false, 'define_parens' => false, 'reset_quote_data' => false,
                        'in_desc' => true, 'in_tag' => false, 'newline' => true, 'tempnewline' => false,
                        'start_docblock' => false, 'includename_isset' => false, 'return_isset' => false,
                        'is_return' => false, 'in_class' => false, 'asterisk' => false, 'var_equals' => false,
                        'arrayinvarname' => false, 'valid_newline' => true, 'startline' => false,
                        'function_global' => false, 'define_global' => false, 'static_value' => false,'funcparam_val' => false,
                        'get_source' => false, 'getting_source' => false);

    /**
     * lookup table for event handler methods
     * @see Parser::parse()
     */
    var $eventHandlers = array(
                                'handleArray' => PARSER_EVENT_ARRAY,
                                'handleClass' => PARSER_EVENT_CLASS,
                                'handleComment' => PARSER_EVENT_COMMENT,
                                'handleDocBlockTemplate' => PARSER_EVENT_DOCBLOCK_TEMPLATE,
                                'handleEndDocBlockTemplate' => PARSER_EVENT_END_DOCBLOCK_TEMPLATE,
                                'handleEscape' => PARSER_EVENT_ESCAPE,
                                'handleLogicBlock' => PARSER_EVENT_LOGICBLOCK,
                                'defaultHandler' => PARSER_EVENT_NOEVENTS,
//                                'defaultHandler' => PARSER_EVENT_COMMENTBLOCK, (set in constructor below)
//                                'defaultHandler' => PARSER_EVENT_OUTPHP,
                                'handleDefine' => PARSER_EVENT_DEFINE,
                                'handleDefineParams' => PARSER_EVENT_DEFINE_PARAMS,
                                'handleDefineParamsParenthesis' => PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS,
                                'handleIncludeParamsParenthesis' => PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS,
//                                'handleDocBlock' => PARSER_EVENT_DOCBLOCK,
                                'BetterhandleDocBlock' => PARSER_EVENT_DOCBLOCK,
                                'handleTags' => PARSER_EVENT_TAGS,
                                'handleDesc' => PARSER_EVENT_DESC,
//                                'handleDockeyword' => PARSER_EVENT_DOCKEYWORD,
                                'handleTag' => PARSER_EVENT_DOCKEYWORD,
                                'handleDockeywordEmail' => PARSER_EVENT_DOCKEYWORD_EMAIL,
                                'handleEOFQuote' => PARSER_EVENT_EOFQUOTE,
                                'handleFunction' => PARSER_EVENT_FUNCTION,
                                'handleFunctionParams' => PARSER_EVENT_FUNCTION_PARAMS,
                                'handleFuncGlobal' => PARSER_EVENT_FUNC_GLOBAL,
                                'handleGlobal' => PARSER_EVENT_DEFINE_GLOBAL,
                                'handleGlobalValue' => PARSER_EVENT_GLOBAL_VALUE,
                                'handleInlineDockeyword' => PARSER_EVENT_INLINE_DOCKEYWORD,
                                'handleInclude' => PARSER_EVENT_INCLUDE,
                                'handleIncludeParams' => PARSER_EVENT_INCLUDE_PARAMS,
                                'handleQuote' => PARSER_EVENT_QUOTE,
                                'handlePhpCode' => PARSER_EVENT_PHPCODE,
                                'handleSingleQuote' => PARSER_EVENT_SINGLEQUOTE,
                                'handleStaticVar' => PARSER_EVENT_STATIC_VAR,
                                'handleStaticValue' => PARSER_EVENT_STATIC_VAR_VALUE,
                                'handleVar' => PARSER_EVENT_VAR,
    );
    
    /**
     * event handlers for @tags
     * @tutorial tags.pkg
     */
    var $tagHandlers = array(
                                '*' => 'defaultTagHandler',
                                'category' => 'categoryTagHandler',
                                'example' => 'exampleTagHandler',
                                'filesource' => 'invalidTagHandler',
                                'return' => 'returnTagHandler',
                                'returns' => 'returnTagHandler',
                                'var' => 'varTagHandler',
                                'package' => 'packageTagHandler',
                                'param' => 'paramTagHandler',
                                'parameter' => 'paramTagHandler',
                                'global' => 'globalTagHandler',
                                'staticvar' => 'staticvarTagHandler',
                                'uses' => 'usesTagHandler'
                            );

    var $laststart = false;

    /**
     * An array of allowable @tags
     */
    var $allowableTags;


    /**
     * An array of allowed inline @tags
     */
    var $allowableInlineTags;
    
    /**
     * Sets the states up, and creates a new WordParser
     */
    
    /**
     * an array of parsing tokens organized by event number.
     * A token is defined as the smallest group of characters that separates or
     * defines a new parser element.  In English, a space or punctuation are
     * tokens that separate words.  in PHP, tokens may be //, or even "
     * Format: array(eventnum =>array(token1, token2, token3, ...),...)
     * @var array
     */
    var $tokens;
    
    /**
     * array of events that are raised, organized by the tokens that raise them.
     * Format: array(eventnum => array(token => neweventnum, token2 => neweventnum2,...),...)
     * @var array
     */
    var $pushEvent;
    
    /**
     * array of tokens that end an event, organized by event
     * Format: array(eventnum => array(token => neweventnum, token2 => neweventnum2,...),...)
     * @var array
     */
    var $popEvent;
    /**#@-*/
    
    /**
     * Set up invariant parsing variables
     */
    function Parser()
    {
        $this->allowableTags = $GLOBALS['_phpDocumentor_tags_allowed'];
        $this->allowableInlineTags = $GLOBALS['_phpDocumentor_inline_doc_tags_allowed'];
        $this->wp = new WordParser;
        // strange PHP 4.0.6 behavior: it converts constants to strings without warning if it's an array index
        $this->eventHandlers = array_flip($this->eventHandlers);
        $this->eventHandlers[PARSER_EVENT_COMMENTBLOCK] = 'defaultHandler';
        $this->eventHandlers[PARSER_EVENT_OUTPHP] = 'defaultHandler';
        $this->subscribe(PHPDOCUMENTOR_EVENT_NEWLINENUM,$GLOBALS['phpDocumentor_errors']);
        $this->subscribe(PHPDOCUMENTOR_EVENT_NEWFILE,$GLOBALS['phpDocumentor_errors']);
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
        $this->p_vars = array('func' => false, 'function_data' => '', 'quote_data' => '', 'event_stack' => false, 'last_pevent' => 0,
                        'two_words_ago' => '', 'temp_word' => '', 'docblock' => false, 'line' => array(), 'linecount' => 0, 'startword' => '',
                        'periodline' => 0, 'shortdesc' => '', 'docblock_desc' => '', 'class' => false, 'source_location' => '',
                        'define_params_data' => '', 'define' => false, 'define_name' => '', 'define_value' => '', 'var' => false,
                        'oldtoken' => false, 'comment_data' => '', 'function_param' => NULL, 'inline_dockeyword_type' => false,
                        'inline_dockeyword_data' => false, 'dockeyword_type' => false, 'dockeyword_data' =>false, 'param_var' => false,
                        'include_name' => '', 'include_value' => '','include' => false, 'return_type' => '', 'cur_class' => '',
                        'function_data' => false, 'varname' => '', 'returntype' => false, 'vartype' => false, 'paramtype' => false,
                        'tagname' => '', 'find_global' => '', 'global_type' => '', 'paramname' => false, 'statics' => array(),
                        'static_count' => 0, 'static_val' => array(), 'docblock_type' => 'docblock', 'linenum' => false,
                        'seelement' => false);
    
        $this->p_flags = array('docblocknewline' => false, 'docblockintags' => false, 'useperiod' => false,
                        'definename_isset' => false, 'define_parens' => false, 'reset_quote_data' => false,
                        'in_desc' => true, 'in_tag' => false, 'newline' => true, 'tempnewline' => false,
                        'start_docblock' => false, 'includename_isset' => false, 'return_isset' => false,
                        'is_return' => false, 'in_class' => false, 'asterisk' => false, 'var_equals' => false,
                        'arrayinvarname' => false, 'valid_newline' => true, 'startline' => false,
                        'function_global' => false, 'define_global' => false, 'static_value' => false,'funcparam_val' => false,
                        'get_source' => false, 'getting_source' => false, 'in_define' => false, 'in_include' => false,
                        'in_var' => false, 'in_global' => false);
        $this->p_vars['parsepath'] = $path;
        $this->setupStates();
        if (strlen($parse_data) == 0)
        {
            return false;
        }

        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->p_vars['event_stack'] = new EventStack;

        $this->wp->setup($parse_data);
        

        $page = new ParserPage;
        $page->setPath($path);
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
        
        $this->p_vars['source_location'] = $source_location = $temploc;
        $page->setSourceLocation($source_location);

        $this->publishEvent(PHPDOCUMENTOR_EVENT_PAGE,$page);
        unset($page);
        $this->p_flags['reset_quote_data'] = true;

        do
        {
            $lpevent = $pevent;
            $pevent = $this->p_vars['event_stack']->getEvent();
            if ($lpevent != $pevent)
            {
                $this->p_vars['last_pevent'] = $lpevent;
            }

            if ($this->p_vars['last_pevent'] != $pevent)
            {
                // its a new event so the word parser needs to be reconfigured 
                $this->configWordParser($pevent);
            }
        
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,($pevent + 100));

            if ($pevent == PARSER_EVENT_GLOBAL_VALUE || $pevent == PARSER_EVENT_DOCBLOCK || $pevent == PARSER_EVENT_DOCBLOCK_TEMPLATE)
            {
                $this->wp->setWhitespace(true);
            }

            $this->p_vars['last_word'] = $word;
            $word = $this->wp->getWord();
            // in wordparser, have to keep track of lines
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWLINENUM, $this->wp->linenum);

            if (0)//PHPDOCUMENTOR_DEBUG == true)
            {
                echo "\nLAST: |" . $this->p_vars['last_word'] . "|\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo "LASTPEVENT: " . $this->getParserEventName($this->p_vars['last_pevent']) . "\n";
                echo $this->wp->getPos() . ": |$word|\n--------------------------\n\n";
            }
            if ($this->p_flags['get_source'])
            {
                if ($pevent == PARSER_EVENT_FUNCTION)
                {
                    $this->wp->retrievesource("function $word");
                    $this->p_flags['get_source'] = false;
                    $this->p_flags['getting_source'] = true;
                }
            }
            if (false)//$this->p_flags['getting_source'] && ($pevent == PARSER_EVENT_DOCBLOCK) || ($pevent == PARSER_EVENT_NOEVENTS))
            {
                addError(PDERROR_SOURCE_TAG_FUNCTION_NOT_FOUND);
                // throw away source
                $this->wp->getSource();
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
     * @param string token parsed from source
     * @param integer parser constant from {@link Parser.inc}
     */
    /**
     * handler for NOEVENTS, OUTPHP, COMMENTBLOCK
     */
    
    function defaultHandler($word, $pevent)
    {
        $this->checkEventPush( $word, $pevent);
        $this->checkEventPop($word,$pevent);
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
        if ($a == PARSER_EVENT_FUNC_GLOBAL || $a == PARSER_EVENT_STATIC_VAR)
        {
            if (substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != ' ' && substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != "\t" && substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != "\n" && substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != ";" && substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != "}" && substr($this->p_vars['last_word'],strlen($this->p_vars['last_word']) - 1,1) != "{")
            {
                $this->p_vars['event_stack']->popEvent();
            }
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $e = $this->p_vars['event_stack']->popEvent();
            $this->p_vars['event_stack']->pushEvent($e);
            if ($e == PARSER_EVENT_FUNCTION)
            {
                $this->wp->backupPos($word); 
            }
        }
    }
    
    /**
     * handler for ESCAPE.
     * this event handler parses <code>"this string \"with its escape backslashes\""</code> and returns:
     * <code>this string "with its escape backslashes"</code>
     * to make it human-readable
     */
    
    function handleEscape($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
    }
    
    /**
     * handler for COMMENT.
     * this event handler parses single-line comments like:
     * // this one
     */
    
    function handleComment($word, $pevent)
    {
        $this->checkEventPush( $word, $pevent);
    
        if (!isset($this->p_vars['comment_data'])) $this->p_vars['comment_data'] = '';
        $this->p_vars['comment_data'] .= $word;
    
        $this->checkEventPop($word,$pevent);
    }

    /**
     * handler for ARRAY.
     * this event handler parses arrays in default values of function and var definitions
     */
    
    function handleArray($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent); 
        if (($e == PARSER_EVENT_COMMENTBLOCK) ||
            ($e == PARSER_EVENT_COMMENT)) return;

        if (!isset($this->p_vars['function_data']) || (isset($this->p_vars['function_data']) && empty($this->p_vars['function_data'])))
        {
            $this->p_vars['function_data'] = "array";
        }

        if ( ($this->p_vars['last_word'] == "'"))
        {
            $this->p_vars['function_data'] .= $this->p_vars['quote_data']."'";
        }
        if ( ($this->p_vars['last_word'] == "\""))
        {
            $this->p_vars['function_data'] .= $this->p_vars['quote_data']."\"";
        }

        $this->p_vars['function_data'] .= $word;
        //echo "function_data = |$this->p_vars['function_data']|\n";

        if ($this->checkEventPop($word,$pevent))
        {
        }
    }

    /**
     * handler for DEFINE.
     * handles define(constant, value); statements
     */
    
    function handleDefine($word, $pevent)
    {
        if (!$this->p_flags['in_define'])
        {
            $this->p_vars['linenum'] = $this->wp->linenum;
        }
        $this->p_flags['in_define'] = true;
        $this->checkEventPush( $word, $pevent);

        $this->p_flags['definename_isset'] = false;
        $this->p_vars['define_params_data'] = '';
        unset($this->p_vars['quote_data']);
        if ($this->checkEventPop($word,$pevent))
        {
            $this->p_flags['in_define'] = false;
            $this->p_vars['define'] = new parserDefine;
            $this->p_vars['define']->setLineNumber($this->p_vars['linenum']);
            $this->p_vars['define']->setName($this->p_vars['define_name']);
            $this->p_vars['define']->setValue($this->p_vars['define_value']);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_DEFINE,$this->p_vars['define']);
            $this->p_flags['definename_isset'] = false;
            unset($this->p_vars['define']);
            unset($this->p_vars['define_name']);
            unset($this->p_vars['define_value']);
            $this->p_flags['in_define'] = false;
            $this->p_vars['define_params_data'] = '';
        }
    }
    
    /**
     * handler for DEFINE_PARAMS.
     * handles the parsing of constant and value in define(constant, value);
     */
    
    function handleDefineParams($word, $pevent)
    {
        if ($this->checkEventPush( $word, $pevent))
        {
            if ($word == '(')
            {
                $this->p_vars['define_params_data'] .= $word;
            }
            return;
        }
        
        $this->p_flags['define_parens'] = true;
        if(!isset($this->p_vars['define_params_data'])) $this->p_vars['define_params_data'] = '';
        
        if ($this->checkEventPop($word,$pevent))
        {
            if (!empty($this->p_vars['quote_data']))
            {
                $this->p_vars['define_params_data'] .= $this->p_vars['quote_data'];
            }
            if (!empty($this->p_vars['define_params_data']))
            {
                //echo $this->p_vars['define_params_data']."\n";
                $this->p_vars['define_value'] = $this->p_vars['define_params_data'];
            }
            else
            {
                if (    $this->p_vars['last_word'] != "/*" && 
                    $this->p_vars['last_word'] != "//" && $this->p_vars['last_word'] != "#")
                {
                    $this->p_vars['define_value'] = trim($this->p_vars['last_word']);
                }
                else
                {
                    $this->p_vars['define_value'] = "";
                }
            }
        }
        if ($this->p_flags['definename_isset'])
        {
            if (isset($this->p_vars['quote_data']))
            {
                $this->p_vars['define_params_data'] .= '"'.$this->p_vars['quote_data'].'"';
                unset($this->p_vars['quote_data']);
            }
            $this->p_vars['define_params_data'] .= $word;
        } else
        {
            if ($word != ",")
            {
                if (isset($this->p_vars['quote_data']))
                {
                    $this->p_vars['define_params_data'] .= $this->p_vars['quote_data'];
                    unset($this->p_vars['quote_data']);
                }
                $this->p_vars['define_params_data'] .= $word;
            } else
            {
                if (isset($this->p_vars['quote_data']) && !$this->p_flags['definename_isset'])
                {
                    $this->p_vars['define_params_data'] .= $this->p_vars['quote_data'];
                    unset($this->p_vars['quote_data']);
                }
                $this->p_flags['definename_isset'] = true;
                $this->p_vars['define_name'] = $this->p_vars['define_params_data'];
                unset($this->p_vars['quote_data']);
                $this->p_vars['define_params_data'] = '';
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
        if (isset($this->p_vars['quote_data']))
        {
            $this->p_vars['define_params_data'] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
        }
        $this->p_vars['define_params_data'] .= $word;
        $this->checkEventPush( $word, $pevent);
        $this->checkEventPop( $word, $pevent);
    }

    /**
     * handler for CLASS.
     * this handler parses a class statement
     */
    
    function handleClass($word, $pevent)
    {
        $this->p_flags['in_class'] = true;
        $a = $this->checkEventPush( $word, $pevent);
        if ($a == PARSER_EVENT_DOCBLOCK || $a == PARSER_EVENT_DOCBLOCK_TEMPLATE)
        {
            $this->wp->setWhitespace(true);
        }

        if (!isset($this->p_vars['class'])) $this->p_vars['class'] = false;
        if (!is_subclass_of($this->p_vars['class'],"parserBase"))
        {
            $this->p_vars['class'] = new parserClass;
            $this->p_vars['class']->setLineNumber($this->wp->linenum);
            $this->p_vars['class']->setname($word);
            $this->p_vars['cur_class'] = $word;
            $this->p_vars['class']->setSourceLocation($this->p_vars['source_location']);
        }

        if (strtolower($this->p_vars['last_word']) == "extends")
        {
            $this->p_vars['class']->setExtends($word);
        }

        if ($word == "{")
        {
            $this->publishEvent(PHPDOCUMENTOR_EVENT_CLASS,$this->p_vars['class']);
        }
        //echo $this->wp->getPos() . ": |$word|\n";
        if ($this->checkEventPop($word,$pevent))
        {
            $this->p_flags['in_class'] = false;
            // throw an event when class is done
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,STATE_END_CLASS);
            $this->p_vars['class'] = false;
        }
    }

    /**
     * handler for VAR.
     * handle a var $varname = default_value; or var $varname; statement in a class definition
     */
    
    function handleVar($word, $pevent)
    {
        if (!$this->p_flags['in_var'])
        {
            $this->p_vars['linenum'] = $this->wp->linenum;
        }
        $this->p_flags['in_var'] = true;
        //echo $word."\n";
        $e = $this->checkEventPush( $word, $pevent);
        
        if (!isset($this->p_vars['var'])) $this->p_vars['var'] = false;
        if ($word == '=' || $word == ';') $this->p_flags['var_equals'] = true;
        if (!$this->p_flags['var_equals'])
        {
            // if we haven't parsed the = yet, no arrays are possible!
            if ($e == PARSER_EVENT_ARRAY)
            {
                $this->p_flags['arrayinvarname'] = true;
                $this->p_vars['event_stack']->popEvent();
            }
            if (!$e || ($e == PARSER_EVENT_ARRAY))
            $this->p_vars['varname'] .= $word;
        }

        if (!$this->p_flags['var_equals'])
        {
            if ($word != "/*" && $word != "//" && $word != "#")
            {
                $this->p_vars['var'] = new parserVar($this->p_vars['cur_class']);
                $this->p_vars['var']->setName($this->p_vars['varname']);
            }
        }
        if ($this->p_vars['last_word'] == "=")
        {
            if ($word != "/*" && $word != "//" && $word != "#")
            {
                $this->p_vars['var']->setValue($word);
            }
        }
        // fix 1202772
        if (isset($this->p_vars['quote_data']) && ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE || $this->p_vars['last_pevent'] == PARSER_EVENT_SINGLEQUOTE))
        {
            $this->p_vars['var']->setValue($this->p_vars['quote_data']);
            unset($this->p_vars['quote_data']);
        }
        if ($this->p_vars['last_pevent'] == PARSER_EVENT_ARRAY)
        {
            $this->p_vars['var']->setValue($this->p_vars['function_data']);
            $this->p_vars['function_data'] = false;
        }
            
        if ($this->checkEventPop($word,$pevent))
        {
            $this->p_vars['var']->setLineNumber($this->p_vars['linenum']);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_VAR,$this->p_vars['var']);
            unset($this->p_vars['var']);
            $this->p_flags['in_var'] = false;
            $this->p_flags['var_equals'] = false;
            $this->p_flags['arrayinvarname'] = false;
            $this->p_vars['varname'] = '';
        }
    }

    /**
     * handler for QUOTE.
     * this handler recognizes strings defined with double quotation marks (") and handles them correctly
     * in any place that they legally appear in php code
     */
    
    function handleQuote($word, $pevent)
    {
        if ($this->p_flags['reset_quote_data'] === true)
        {
            $this->p_flags['reset_quote_data'] = false;
            $this->p_vars['quote_data'] = "";
        }
        $this->checkEventPush( $word, $pevent);
        if ($word != "\"")
        {
            $this->p_vars['quote_data'] .= $word;
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $this->p_flags['reset_quote_data'] = true;
        }
    }
    
    /**
     * handler for SINGLEQUOTE.
     * this handler recognizes strings defined with single quotation marks (') and handles them correctly
     * in any place that they legally appear in php code
     */
    
    function handleSingleQuote($word, $pevent)
    {
        $this->checkEventPush( $word, $pevent);
        if ($this->checkEventPop($word,$pevent))
        {
            if ($this->p_vars['last_word'] != "'")
            {
                $this->p_vars['quote_data'] = $this->p_vars['last_word'];
            } else {
                $this->p_vars['quote_data'] = "";
            }
        }
    }

    /**
     * handler for EOFQUOTE.
     * this handler recognizes strings defined with perl-style <<< EOF quotes, and handles them correctly
     * in any place that they legally appear in php code
     *
     * an example:
     * <code>$var <<< EOF
     * blah blah blah
     * EOF;</code>
     */
    
    function handleEOFQuote($word, $pevent)
    {
        //    echo $this->wp->getPos() . ": word=|$word|\t\t\tlastword=|$this->p_vars['last_word']|\n";
        if (trim($this->p_vars['last_word']) == "<<<")
        {
            // ok we found the keyword
            //echo "Keyword == $word\n";
            $this->p_vars['oldtoken'] = $this->tokens[STATE_EOFQUOTE];
            $this->tokens[STATE_EOFQUOTE] = array($word);
        } 
        else if ($this->p_vars['last_pevent'] || PARSER_EVENT_EOFQUOTE) 
        {
            // i don't think anything will ever use this so were not going to set it
            //$this->p_vars['quote_data'] = $this->p_vars['last_word']; 
            $this->p_vars['event_stack']->popEvent();
            $this->tokens[STATE_EOFQUOTE] = $this->p_vars['oldtoken'];
        }
    }
    /**#@-*/

    /**
     * Tells the parser to search for a global variable definition as
     * defined by a @global type $name tag.
     *
     * The parser is fooled into looking for the entire global variable as a
     * single token by amending the {@link $tokens} array.
     *
     * {@source}
     * @access private
     * @param string name of global variable as it appears in the source code
     */
    function findGlobal($name)
    {
        if (!isset($this->p_vars['globaltofind']))
        {
            $this->p_vars['globaltofind'] = $name;
            $this->pushEvent[PARSER_EVENT_PHPCODE][strtolower($name)] = PARSER_EVENT_DEFINE_GLOBAL;
            $this->tokens[STATE_PHPCODE][] = $name;
        } else
        {
            addError(PDERROR_MULTIPLE_GLOBAL_TAGS,$this->p_vars['globaltofind'],$name);
        }
    }
    
    /**#@+
     * @access private
     * @param string token parsed from source
     * @param integer parser constant from {@link Parser.inc}
     */
    /**
     * handler for PHPCODE.
     * this handler recognizes the <code><?</code> php processor directive, and begins parsing php code
     */
    
    function handlePhpCode($word, $pevent)
    {
        $e = $this->checkEventPush( $word, $pevent);
        if ($e == PARSER_EVENT_DOCBLOCK || $e == PARSER_EVENT_DOCBLOCK_TEMPLATE)
        {
            $this->wp->setWhitespace(true);
        }
        if (isset($this->p_vars['globaltofind']) && $e)
        {
            if ($e != PARSER_EVENT_DEFINE_GLOBAL && $e != PARSER_EVENT_ARRAY && $e != PARSER_EVENT_QUOTE && $e != PARSER_EVENT_SINGLEQUOTE && $e != PARSER_EVENT_COMMENT && $e != PARSER_EVENT_COMMENTBLOCK)
            {
                addError(PDERROR_GLOBAL_NOT_FOUND,$this->p_vars['globaltofind']);
                unset($this->pushEvent[PARSER_EVENT_PHPCODE][strtolower($this->p_vars['globaltofind'])]);
                foreach($this->tokens[STATE_PHPCODE] as $i => $notme)
                if ($this->tokens[STATE_PHPCODE][$i] == $this->p_vars['globaltofind'])
                unset($this->tokens[STATE_PHPCODE][$i]);
                unset($this->p_vars['globaltofind']);
            }
        }
    }
    
    /**
     * handler for global variables
     */
    function handleGlobal($word, $pevent)
    {
        if (!$this->p_flags['in_global'])
        {
            $this->p_vars['linenum'] = $this->wp->linenum;
        }
        $this->p_flags['in_global'] = true;
        $e = $this->checkEventPush($word, $pevent);
        if ($this->checkEventPop($word, $pevent))
        {
            $this->p_flags['in_global'] = false;
            $a = new parserGlobal;
            $a->setDataType($this->p_vars['global_type']);
            $this->p_vars['global_type'] = '';
            $a->setLineNumber($this->p_vars['linenum']);
            $a->setName($this->p_vars['globaltofind']);
            if (isset($this->p_vars['global_val']))
            $a->setValue(trim($this->p_vars['global_val']));
            unset($this->p_vars['global_val']);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_GLOBAL,$a);
            unset($this->pushEvent[PARSER_EVENT_PHPCODE][strtolower($this->p_vars['globaltofind'])]);
            foreach($this->tokens[STATE_PHPCODE] as $i => $notme)
            if ($this->tokens[STATE_PHPCODE][$i] == $this->p_vars['globaltofind'])
            unset($this->tokens[STATE_PHPCODE][$i]);
            unset($this->p_vars['globaltofind']);
        }
    }
    
    /**
     * Handles the stuff after the = in <code>$globalvar = value</code>
     */
    function handleGlobalValue($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent))
        {
            $this->wp->setWhitespace(false);
            return;
        }
        if (!isset($this->p_vars['global_val'])) $this->p_vars['global_val'] = '';
        if ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE || $this->p_vars['last_pevent'] == PARSER_EVENT_SINGLEQUOTE)
        {
            if (!isset($this->p_vars['quote_data'])) $this->p_vars['quote_data'] = '';
            $this->p_vars['global_val'] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
            $this->p_vars['last_pevent'] = PARSER_EVENT_GLOBAL_VALUE;
        }
        if ($this->p_vars['last_pevent'] == PARSER_EVENT_ARRAY)
        {
            $this->p_vars['global_val'] .= $this->p_vars['function_data'];
            $this->p_vars['function_data'] = false;
        }
        if ($word != ';')
        $this->p_vars['global_val'] .= $word;
        if ($this->checkEventPop($word, $pevent))
        {
            $this->wp->setWhitespace(false);
            $this->wp->backupPos($word);
        }
    }
    
    /**
     * handler for FUNC_GLOBAL.
     * this handler recognizes "global $var1, $var2" declarations in a function, and parses them
     */
    
    function handleFuncGlobal($word, $pevent)
    {
        if ((substr(trim($word),0,1) != '$') && ($word != ',') && ($word != ';'))
        { // not a global declaration, using a variable named "$global"
            $this->p_vars['event_stack']->popEvent();
            return;
        }
        if ($this->checkEventPop($word, $pevent))
        {
            return;
        }
        if (!$this->checkEventPush($word, $pevent))
        {
            if ($word == ',')
            { // another variable
                $this->p_vars['global_count']++;
            } else
            {
                if (!isset($this->p_vars['globals'][$this->p_vars['global_count']]))
                $this->p_vars['globals'][$this->p_vars['global_count']] = '';
                if (!empty($this->p_vars['globals'][$this->p_vars['global_count']])) $this->p_vars['global_count']++;
                $this->p_vars['globals'][$this->p_vars['global_count']] = trim($word);
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
            $this->p_vars['static_count']++;
            return;
        }
        if (!$this->checkEventPush($word, $pevent))
        {
            if ($word == ',')
            {
                $this->p_vars['static_count']++;
                return;
            }
            if (!isset($this->p_vars['statics'][$this->p_vars['static_count']]))
            $this->p_vars['statics'][$this->p_vars['static_count']] = '';
            if (!empty($this->p_vars['statics'][$this->p_vars['static_count']])) $this->p_vars['static_count']++;
            $this->p_vars['statics'][$this->p_vars['static_count']] = trim($word);
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
        if (!isset($this->p_vars['static_val'][$this->p_vars['static_count']])) $this->p_vars['static_val'][$this->p_vars['static_count']] = '';
        if ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE || $this->p_vars['last_pevent'] == PARSER_EVENT_SINGLEQUOTE)
        {
            $this->p_vars['static_val'][$this->p_vars['static_count']] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
        }
        if ($this->p_vars['last_pevent'] == PARSER_EVENT_ARRAY)
        {
            $this->p_vars['static_val'][$this->p_vars['static_count']] .= $this->p_vars['function_data'];
            $this->p_vars['function_data'] = false;
        }
        if ($this->checkEventPop($word, $pevent))
        {
            $this->p_vars['static_val'][$this->p_vars['static_count']] = trim($this->p_vars['static_val'][$this->p_vars['static_count']]);
            $this->wp->backupPos($word);
            return;
        } else $this->p_vars['static_val'][$this->p_vars['static_count']] .= $word;
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
            if ($e == PARSER_EVENT_COMMENT || $e == PARSER_EVENT_COMMENTBLOCK) return;
        }
    
        if (!isset($this->p_vars['func'])) $this->p_vars['func'] = false;
        if (! is_subclass_of($this->p_vars['func'],"parserBase")) 
        {
            $this->p_vars['globals'] = array();
            $this->p_vars['global_count'] = 0;
            if ($this->p_flags['in_class'])
            $this->p_vars['func'] = new parserMethod($this->p_vars['cur_class']); 
            else
            $this->p_vars['func'] = new parserFunction;
            $this->p_vars['func']->setLineNumber($this->wp->linenum);
            if (trim($word) != '&')
            $this->p_vars['func']->setName(trim($word));
            else
            $this->p_vars['func']->setReturnsReference();
        } else
        {
            if ($this->p_vars['func']->getReturnsReference())
            {
                if ($this->p_vars['last_word'] == '&')
                {
                    $this->p_vars['func']->setName(trim($word));
                }
            }
        }
        if ($this->checkEventPop($word,$pevent)) 
        { 
            $this->p_vars['func']->addGlobals($this->p_vars['globals']);
            $this->p_vars['func']->addStatics($this->p_vars['statics'],$this->p_vars['static_val']);
            $this->p_vars['globals'] = array();
            $this->p_vars['global_count'] = 0;
            if ($this->p_flags['getting_source'])
            {
                $x = $this->wp->getSource();
                $this->p_vars['func']->addSource($x);
                $this->p_flags['get_source'] = false;
                $this->p_flags['getting_source'] = false;
            }
            $this->publishEvent(PHPDOCUMENTOR_EVENT_FUNCTION,$this->p_vars['func']); 
            $this->p_vars['func'] = false; 
        } 
    }

    /**#@-*/
    /**
     * Helper function for {@link handleFunctionParams()}
     *
     * This function adds a new parameter to the parameter list
     * @access private
     * @param string
     */
    function endFunctionParam($word)
    {
        if (isset($this->p_vars['quote_data']) && ($this->p_vars['last_pevent'] == PARSER_EVENT_SINGLEQUOTE))
        {
            $this->p_vars['function_data'] .= "'".$this->p_vars['quote_data']."'";
            unset($this->p_vars['quote_data']);
        }
        if (isset($this->p_vars['quote_data']) && ($this->p_vars['quote_data'] != '') && ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE))
        {
            $this->p_vars['function_data'] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
        }
        if (isset($this->p_vars['function_param']))
        {
            $this->p_vars['func']->addParam($this->p_vars['function_param'],$this->p_vars['function_data'], $this->p_flags['funcparam_val']);
            unset($this->p_vars['function_param']);
            $this->p_vars['function_data'] = '';
            $this->p_flags['funcparam_val'] = false;
        }
    }
    /**#@+
     * @access private
     * @param string token parsed from source
     * @param integer parser constant from {@link Parser.inc}
     */
    /**
     * handler for FUNCTION_PARAMS.
     * this handler recognizes the parameters of a function within parentheses like function(param, param = default_value)
     * and parses them
     * @see endFunctionParam()
     */
    
    function handleFunctionParams($word, $pevent)
    {
        //echo $this->wp->getPos() . ": word=|$word|\t\t\tlastword=|".$this->p_vars['last_word']."|\n";
        //echo "function_param = '".$this->p_vars['function_param']."'\n";
        //echo "function_data = '".$this->p_vars['function_data']."'\n";
        $e1 = $this->checkEventPush( $word, $pevent); 

        if (!$e1)
        {
            if ($word == ',' || $this->checkEventPop($word,$pevent))
            {
                $this->endFunctionParam($word);
            } elseif ($word == '=')
            {
                $this->p_flags['funcparam_val'] = true;
            } else
            {
                if ($this->p_flags['funcparam_val'])
                {
                    if (isset($this->p_vars['quote_data']) && ($this->p_vars['last_pevent'] == PARSER_EVENT_SINGLEQUOTE))
                    {
                        $this->p_vars['function_data'] .= "'".$this->p_vars['quote_data']."'";
                        unset($this->p_vars['quote_data']);
                    }
                    if (isset($this->p_vars['quote_data']) && ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE))
                    {
                        $this->p_vars['function_data'] .= '"'.$this->p_vars['quote_data'].'"';
                        unset($this->p_vars['quote_data']);
                    }
                    $this->p_vars['function_data'] .= $word;
                } else
                {
                    $this->p_vars['function_param'] = $word;
                }
            }
        }
    }

    
    /**
     * javadoc-desc-compliant handler for DOCBLOCK.
     * this handler recognizes @tags in DocBlocks and parses them for display.
     * It also parses out unknown tags into their own array for use by the docblock
     */
    
    function JavaDochandleDocblock($word, $pevent)
    {
        $e1 = $this->checkEventPush( $word, $pevent);
        if (!isset($this->p_vars[$this->p_vars['docblock_type']]) || !$this->p_vars[$this->p_vars['docblock_type']])
        {
            $this->p_vars[$this->p_vars['docblock_type']] = new parserDocBlock();
            $this->p_vars['returntype'] = false;
            $this->p_vars['vartype'] = false;
            $this->p_flags['startdocblock'] = true;
            $this->p_flags['valid_newline'] = true;
            $this->p_flags['startline'] = true;
            $this->p_flags['newline'] = true;
            $this->p_flags['in_desc'] = true;
            $this->p_flags['in_tag'] = false;
            $this->p_flags['useperiod'] = false;
            $this->p_vars['line'] = array();
            $this->p_vars['linecount'] = 0;
        }
        $e = $this->checkEventPop( $word, $pevent);
        if (!$e1 && !$e)
        {
            if ($this->p_flags['in_desc']) $this->JavaDochandleDesc($word, $pevent);
            else $this->handleTags($word, $pevent);
        }
        if ($e)
        {
            if (!isset($this->p_vars['periodline'])) $this->p_vars['periodline'] = 0;
            if ($this->p_vars['periodline'] > 3)
            {
                $this->p_flags['useperiod'] = false;
            }

            $this->p_vars['docblock_desc'] = new parserDesc;
//            echo "i = ".$this->p_vars['periodline']."; i < " . count($this->p_vars['line']) . "\n";
            if ($this->p_vars['docblock_type'] == 'docblock')
            {
                if (isset($this->p_vars['docblock_template']))
                {
                    // copy template values if not overridden
                    if (!$this->p_vars['docblock']->getExplicitPackage())
                    {
                        if ($p = $this->p_vars['docblock_template']->getKeyword('package'))
                        {
                            $this->p_vars['docblock']->addKeyword('package',$p);
                            $this->p_vars['docblock']->setExplicitPackage();
                        }
                        if ($p = $this->p_vars['docblock_template']->getKeyword('category'))
                        {
                            $this->p_vars['docblock']->addKeyword('category',$p);
                            $this->p_vars['docblock']->setExplicitCategory();
                        }
                        if ($p = $this->p_vars['docblock_template']->getKeyword('subpackage'))
                        {
                            $this->p_vars['docblock']->addKeyword('subpackage',$p);
                        }
                    }
                    $tags = $this->p_vars['docblock_template']->listTags();
                    foreach($tags as $tag)
                    {
                        $this->p_vars['docblock']->addKeyword($tag->keyword,$tag->value);
                    }
                    $this->p_vars['docblock_desc']->add($this->p_vars['docblock_template']->desc);
                    if (!count($this->p_vars['docblock']->params)) $this->p_vars['docblock']->params = $this->p_vars['docblock_template']->params;
                }
                if ($a = strpos(trim($this->p_vars['shortdesc']),'<p>') === 0)
                $this->p_vars['shortdesc'] = substr($this->p_vars['shortdesc'],strpos($this->p_vars['shortdesc'],'<p>') + 4);
                $this->p_vars[$this->p_vars['docblock_type']]->setShortDesc($this->p_vars['shortdesc']);
            }
            for($i = 0; $i < count($this->p_vars['line']); $i++)
            {
                // the line will not be set if it doesn't start with a *
                if (isset($this->p_vars['line'][$i]))
                $this->p_vars['docblock_desc']->add($this->p_vars['line'][$i]);
            }


            $this->p_vars[$this->p_vars['docblock_type']]->setDesc($this->p_vars['docblock_desc']);
            unset($this->p_vars['docblock_desc']);
//            var_dump($this->p_vars[$this->p_vars['docblock_type']]);
//            exit;
            if ($this->p_vars['docblock_type'] == 'docblock')
            {
                $this->publishEvent(PHPDOCUMENTOR_EVENT_DOCBLOCK,$this->p_vars[$this->p_vars['docblock_type']]);
                unset($this->p_vars[$this->p_vars['docblock_type']]);
                $this->p_vars[$this->p_vars['docblock_type']] = new parserDocBlock();
            }
            $this->p_flags['in_desc'] = true;
            $this->p_flags['in_tag'] = false;
            $this->p_flags['useperiod'] = false;
            $this->p_vars['line'] = array();
            $this->p_vars['linecount'] = 0;
            $this->p_flags['start_docblock'] = true;
            $this->p_flags['valid_newline'] = true;
            $this->wp->setWhitespace(false);
        }
    }

    /**
     * handler for DOCKEYWORD_DESC.
     * this handler parses the short and long description of a dockeyword
     */
    
    function JavaDochandleDesc($word, $pevent)
    {
        if ($this->p_flags['valid_newline'])
        {
            if ($word == '@' && $this->p_flags['startline'])
            {
                return $this->handleTag($word, $pevent);
            }
            if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
            {
                $this->p_vars['line'][$this->p_vars['linecount']] = new parserStringWithInlineTags;
            }
            if ($this->p_vars['last_word'] == "." && $this->p_flags['useperiod'] == false)
            {
                $this->p_vars['periodline'] = $this->p_vars['linecount'];
                $this->p_vars['shortdesc'] = new parserDesc;
                for($i = 0; ($i <= $this->p_vars['periodline']) && ($i < count($this->p_vars['line'])); $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    $this->p_vars['shortdesc']->add($this->p_vars['line'][$i]);
                }
                $this->p_flags['useperiod'] = true;
            }
            $this->p_vars['line'][$this->p_vars['linecount']]->add($word);
//            debug("DESC $word");
        }
        $this->handleCR($word);
    }
    
    /**
     * handler for DOCBLOCK.
     * this handler recognizes @tags in DocBlocks and parses them for display.
     * It also parses out unknown tags into their own array for use by the docblock
     */
    
    function BetterhandleDocblock($word, $pevent)
    {
        $e1 = $this->checkEventPush( $word, $pevent);
        if (!$this->wp->returnWhiteSpace)
        {
            addErrorDie(PDERROR_NEED_WHITESPACE);
        }
        if (!isset($this->p_vars[$this->p_vars['docblock_type']]) || !$this->p_vars[$this->p_vars['docblock_type']])
        {
            $this->p_vars[$this->p_vars['docblock_type']] = new parserDocBlock();
            $this->p_vars['returntype'] = false;
            $this->p_vars['vartype'] = false;
            $this->p_flags['startdocblock'] = true;
            $this->p_flags['valid_newline'] = true;
            $this->p_flags['startline'] = true;
            $this->p_flags['newline'] = true;
            $this->p_flags['in_desc'] = true;
            $this->p_flags['in_tag'] = false;
            $this->p_flags['useperiod'] = false;
            $this->p_vars['line'] = array();
            $this->p_vars['linecount'] = 0;
        }
        $e = $this->checkEventPop( $word, $pevent);
        if (!$e1 && !$e)
        {
            if ($this->p_flags['in_desc']) $this->handleDesc($word, $pevent);
            else $this->handleTags($word, $pevent);
        }
        if ($e)
        {
            if (!isset($this->p_vars['periodline'])) $this->p_vars['periodline'] = 0;
            if ($this->p_vars['periodline'] > 3)
            {
                $this->p_flags['useperiod'] = false;
            } else
            {
                for($i = 0; $i < $this->p_vars['periodline']; $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    {
                        if ($this->p_vars['line'][$i]->trimmedStrlen() == 0 && isset($this->p_vars['line'][$i - 1]) && $this->p_vars['line'][$i - 1]->trimmedStrlen())
                        {
                            $this->p_vars['periodline'] = $i;
                        }
                    }
                }
            }
            // figure out the shortdesc
            if ($this->p_flags['useperiod'] === false)
            {
                // use the first non blank line for short desc
                for($i = 0; $i < count($this->p_vars['line']); $i++)
                {
                    if (!isset($this->p_vars['line'][$i]))
                    $this->p_vars['line'][$i] = new parserStringWithInlineTags;
                    if ($this->p_vars['line'][$i]->trimmedStrlen() > 0)
                    {
                        $this->p_vars['periodline'] = $i;
                        $i = count($this->p_vars['line']);
                    }
                }
                        
                // check to see if we are going to use a blank line to end the shortdesc
                // this can only be in the first 4 lines
                if (count($this->p_vars['line']) > 4)
                {
                    $max = 4;
                } else {
                    $max = count($this->p_vars['line']);
                }

                for($i = $this->p_vars['periodline']; $i < $max; $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    if ($this->p_vars['line'][$i]->trimmedStrlen() == 0)
                    {
                        $this->p_vars['periodline'] = $i;
                        $i = $max;
                    }
                }
            }

            if ($this->p_vars['docblock_type'] == 'docblock')
            {
                $this->p_vars['shortdesc'] = new parserDesc;
                for($i = 0; ($i <= $this->p_vars['periodline']) && ($i < count($this->p_vars['line'])); $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    $this->p_vars['shortdesc']->add($this->p_vars['line'][$i]);
                }
                $this->p_vars['periodline']++;
    
                $this->p_vars['docblock_desc'] = new parserDesc;
                if (isset($this->p_vars['docblock_template']))
                {
                    // copy template values if not overridden
                    if (!$this->p_vars['docblock']->getExplicitPackage())
                    {
                        if ($p = $this->p_vars['docblock_template']->getKeyword('package'))
                        {
                            $this->p_vars['docblock']->addKeyword('package',$p);
                            $this->p_vars['docblock']->setExplicitPackage();
                        }
                        if ($p = $this->p_vars['docblock_template']->getKeyword('category'))
                        {
                            $this->p_vars['docblock']->addKeyword('category',$p);
                            $this->p_vars['docblock']->setExplicitCategory();
                        }
                        if ($p = $this->p_vars['docblock_template']->getKeyword('subpackage'))
                        {
                            $this->p_vars['docblock']->addKeyword('subpackage',$p);
                        }
                    }
                    $tags = $this->p_vars['docblock_template']->listTags();
                    foreach($tags as $tag)
                    {
                        $this->p_vars['docblock']->addKeyword($tag->keyword,$tag->value);
                    }
                    if (!count($this->p_vars['docblock']->params)) $this->p_vars['docblock']->params = $this->p_vars['docblock_template']->params;
                    $this->p_vars['docblock_desc']->add($this->p_vars['docblock_template']->desc);
                }
    //            echo "i = ".$this->p_vars['periodline']."; i < " . count($this->p_vars['line']) . "\n";
                for($i = $this->p_vars['periodline']; $i < count($this->p_vars['line']); $i++)
                {
                    // the line will not be set if it doesn't start with a *
                    if (isset($this->p_vars['line'][$i]))
                    $this->p_vars['docblock_desc']->add($this->p_vars['line'][$i]);
                }
            } else
            {
                $this->p_vars['shortdesc'] = new parserDesc;
                for($i = 0; ($i <= $this->p_vars['periodline']) && ($i < count($this->p_vars['line'])); $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    $this->p_vars['shortdesc']->add($this->p_vars['line'][$i]);
                }
                $this->p_vars['periodline']++;
    
                $this->p_vars['docblock_desc'] = new parserDesc;
                for($i=$this->p_vars['periodline']; $i < count($this->p_vars['line']); $i++)
                {
                    if (isset($this->p_vars['line'][$i]))
                    $this->p_vars['docblock_desc']->add($this->p_vars['line'][$i]);
                }
            }


            $this->p_vars[$this->p_vars['docblock_type']]->setShortDesc($this->p_vars['shortdesc']);
            $this->p_vars[$this->p_vars['docblock_type']]->setDesc($this->p_vars['docblock_desc']);
            unset($this->p_vars['docblock_desc']);
//            var_dump($this->p_vars[$this->p_vars['docblock_type']]);
//            exit;
            if ($this->p_vars['docblock_type'] == 'docblock')
            {
                $this->publishEvent(PHPDOCUMENTOR_EVENT_DOCBLOCK,$this->p_vars[$this->p_vars['docblock_type']]);
                unset($this->p_vars[$this->p_vars['docblock_type']]);
                $this->p_vars[$this->p_vars['docblock_type']] = new parserDocBlock();
            } else
            {
                $this->publishEvent(PHPDOCUMENTOR_EVENT_DOCBLOCK_TEMPLATE,$this->p_vars[$this->p_vars['docblock_type']]);
            }
            $this->p_flags['in_desc'] = true;
            $this->p_flags['in_tag'] = false;
            $this->p_flags['useperiod'] = false;
            $this->p_vars['line'] = array();
            $this->p_vars['linecount'] = 0;
            $this->p_flags['start_docblock'] = true;
            $this->p_flags['valid_newline'] = true;
            $this->wp->setWhitespace(false);
            $this->p_vars['docblock_type'] = 'docblock';
        }
    }
    
    /**
     * Handles docblock templates
     * @tutorial phpDocumentor.howto.pkg#basics.docblocktemplate
     */
    function handleDocBlockTemplate($word, $pevent)
    {
        $this->p_vars['docblock_type'] = 'docblock_template';
        $this->p_vars['event_stack']->popEvent();
        $this->p_vars['event_stack']->pushEvent(PARSER_EVENT_DOCBLOCK);
        // fool the docblock handler into thinking everything is totally normal
        $this->p_vars['last_word'] = '/**';
        $pevent = PARSER_EVENT_DOCBLOCK;
        $this->BetterhandleDocBlock($word, $pevent);
    }
    
    /**
     * Handles closing docblock templates /**#@-* /
     * @tutorial phpDocumentor.howto.pkg#basics.docblocktemplate
     */
    function handleEndDocBlockTemplate($word, $pevent)
    {
        unset($this->p_vars['docblock_template']);
        $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,PHPDOCUMENTOR_EVENT_END_DOCBLOCK_TEMPLATE);
        $this->p_vars['event_stack']->popEvent();
    }
    /**#@-*/
    /**
     * Handles a new line in a DocBlock
     * @param string token containing a newline \n
     * @access private
     */
    function handleCR($word)
    {
        $this->laststart = $this->p_flags['startline'];
        if ($word == "\n" || $word == ".\n")
        {
            $this->p_flags['start_docblock'] = false;
            $this->p_flags['newline'] = true;
            $this->p_flags['valid_newline'] = false;
            if ($this->p_flags['in_desc'] && !$this->p_flags['useperiod'])
            {
                if ($word == ".\n")
                {
                    $this->p_flags['useperiod'] = true;
                    $this->p_vars['periodline'] = $this->p_vars['linecount'];
                }
            }
        } else
        {
            if ($this->p_flags['valid_newline'] && strlen(trim($word)))
            {
                $this->p_flags['startline'] = false;
            }
            if ($this->p_flags['newline'] && ($word == '*' || $this->p_flags['start_docblock']))
            {
                $this->p_flags['newline'] = false;
                $this->p_flags['valid_newline'] = true;
                if (!$this->p_flags['start_docblock'])
                $this->p_vars['linecount']++;
                $this->p_flags['startline'] = true;
                $justset = true;
//                debug('valid newline');
            }
        }
    }
    
    /**
     * handler for DOCKEYWORD_DESC.
     * this handler parses the short and long description of a dockeyword
     * @access private
     */
    
    function handleDesc($word, $pevent)
    {
//        echo "|$word|\n";
        if ($this->p_flags['valid_newline'])
        {
            if ($word == '@' && $this->p_flags['startline'])
            {
                return $this->handleTag($word, $pevent);
            }
            if ($this->p_vars['last_word'] == ". " || $this->p_vars['last_word'] == ".\t")
            {
                $this->p_flags['useperiod'] = true;
                $this->p_vars['periodline'] = $this->p_vars['linecount'];
                $this->p_vars['linecount']++;
            }
            if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
            {
                $this->p_vars['line'][$this->p_vars['linecount']] = new parserStringWithInlineTags;
            }
            if ($this->p_flags['startline'])
            {
                if ($word[0] == ' ') $word = substr($word,1);
//                $word = ltrim($word," \t");
            }
            if ($word != '') $this->p_vars['line'][$this->p_vars['linecount']]->add($word);
//            debug("DESC $word");
        }
        $this->handleCR($word);
    }
    
    /**#@+
     * @access private
     * @param string token parsed from source
     * @param integer parser constant from {@link Parser.inc}
     */
    /**
     * handler for DOCKEYWORD_TAGS.
     * this handler recognizes @tags in DocBlocks and parses them for display
     * I think this may be unused.  We'll delete from 1.1 if so
     */
    function handleTags($word, $pevent)
    {
        if ($this->p_flags['valid_newline'])
        {
//            debug("TAGS $word");
        }
        $this->handleCR($word);
    }
    
    /**
     * handler for DOCKEYWORD.
     * this handler recognizes @tags in DocBlocks and parses them for display
     */
    function handleTag($word, $pevent)
    {
        if ($this->p_flags['in_desc'] && !$this->p_flags['valid_newline'])
        {
            $this->p_vars['event_stack']->popEvent();
            return $this->handleDesc($word, $pevent);
        }
//        if ($this->p_vars['last_word'] == '@') fancy_debug('here'.$word,$this->p_flags['startline'],$this->p_flags['in_tag']);
        if ($this->p_vars['tagname'] == 'author')
        {
            if ($word == '<')
            {
                $this->p_vars['event_stack']->pushEvent(PARSER_EVENT_DOCKEYWORD_EMAIL);
                return $this->handleDockeywordEmail($word, $pevent);
            }
        }
        if ($this->checkEventPush( $word, $pevent)) return;
        if ($this->p_vars['last_word'] == '@' && !$this->p_flags['startline'] && $this->p_flags['in_desc'])
        {
            // fix 1203445
            if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
            {
                $this->p_vars['line'][$this->p_vars['linecount']] = new parserStringWithInlineTags;
            }
            $this->p_vars['event_stack']->popEvent();
            $this->p_vars['line'][$this->p_vars['linecount']]->add('@');
            return $this->handleDesc($word, $pevent);
        } elseif($this->p_vars['last_word'] == '@' && !strlen(trim($word)) && empty($this->p_vars['tagname']) && $this->p_flags['in_desc'])
        {
            // fix 1203445
            if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
            {
                $this->p_vars['line'][$this->p_vars['linecount']] = new parserStringWithInlineTags;
            }
            $pevent = $this->p_vars['event_stack']->popEvent();
            $this->p_vars['line'][$this->p_vars['linecount']]->add('@');
            return $this->handleDesc($word, $pevent);
        }
        if ($word == '@' && $this->p_flags['startline'] && $this->p_flags['in_tag'])
        {
            $this->wp->backupPos($word);
            $white = $this->wp->returnWhiteSpace;
            $this->wp->setWhitespace(true);
            $word1 = $this->wp->getWord();
            $this->wp->backupPos($word1);
            if (strlen(trim($word1)))
            {
                $this->endTag();
            }
            $this->wp->getWord();
            $this->wp->setWhitespace($white);
        }
        $this->p_flags['in_tag'] = true;
        $e = $this->checkEventPop($word, $pevent);
        if (!$e)
        {
            if ($this->p_flags['valid_newline'])
            {
                if (($this->p_flags['startline'] || $this->laststart) && $word != '@')
                {
                    if ($this->p_vars['last_word'] == '@')
                    {
//                        debug("TAGSTART $word");
                        $this->p_flags['in_tag'] = true;
                        $this->p_vars['tagname'] = $word;
                        $this->p_flags['startline'] = false;
                        $this->p_vars['tag_value'] = new parserStringWithInlineTags;
                    } else
                    {
//                        debug("TAG1 $word");
                        if (isset($this->tagHandlers[$this->p_vars['tagname']]))
                        $handler = $this->tagHandlers[$this->p_vars['tagname']];
                        else $handler = $this->tagHandlers['*'];
                        $this->$handler($word);
                    }
                } else
                {
                    if (empty($this->p_vars['tagname']))
                    {
                        if ($this->p_flags['in_desc'])
                        {
                            $this->p_flags['in_tag'] = false;
                            // fix 1203445
                            if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
                            {
                                $this->p_vars['line'][$this->p_vars['linecount']] =
                                    new parserStringWithInlineTags;
                            }
                            $this->p_vars['line'][$this->p_vars['linecount']]->add('@');
                            $this->p_vars['event_stack']->popEvent();
                            $this->handleCR($word);
                            return $this->handleDesc($word, $pevent);
                        }
                    }
//                    debug("TAG2 $word");
                    if (isset($this->tagHandlers[$this->p_vars['tagname']]))
                    $handler = $this->tagHandlers[$this->p_vars['tagname']];
                    else $handler = $this->tagHandlers['*'];
                    $this->$handler($word);
                }
            }
            $this->handleCR($word);
        }
        $this->p_flags['in_desc'] = false;
        if ($e)
        {
            $this->endTag();
            $this->wp->setWhitespace(false);
            // walk back a word
            $this->wp->backupPos($word);
            $this->wp->setWhitespace(true);
        }
    }
    /**#@-*/
    /**
     * Called to clean up at the end of parsing a @tag in a docblock
     */
    function endTag()
    {
        if (isset($this->tagHandlers[$this->p_vars['tagname']]))
        $handler = $this->tagHandlers[$this->p_vars['tagname']];
        else $handler = $this->tagHandlers['*'];
        $this->$handler(false);
        $this->p_vars['tagname'] = '';
        $this->p_flags['startline'] = true;
//        debug("ENDTAG");
    }
    
    /**#@+
     * Tag Handlers
     * @param string
     */
    /**
     * Handles all standard tags that only have a description
     */
    function defaultTagHandler($word)
    {
        if ($word !== false)
        {
            $this->p_vars['tag_value']->add($word);
        } else
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addKeyword($this->p_vars['tagname'],$this->p_vars['tag_value']);
        }
    }
    
    /**
     * Handles tags like '@filesource' that only work in PHP 4.3.0+
     */
    function invalidTagHandler($word)
    {
        if ($word === false)
        {
            addError(PDERROR_TAG_NOT_HANDLED,$this->p_vars['tagname']);
        }
    }
    
    /**
     * handles @package
     * @tutorial tags.package.pkg
     */
    function packageTagHandler($word)
    {
        if ($word !== false)
        {
            $this->p_vars['tag_value']->add($word);
        } else
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addKeyword($this->p_vars['tagname'],$this->p_vars['tag_value']);
            $this->p_vars[$this->p_vars['docblock_type']]->setExplicitPackage();
        }
    }
    
    /**
     * handles @example
     * @tutorial tags.example.pkg
     */
    function exampleTagHandler($word)
    {
        if ($word !== false)
        {
            $this->p_vars['tag_value']->add($word);
        } else
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addExample($this->p_vars['tag_value'], $this->p_vars['parsepath']);
        }
    }
    
    /**
     * handles @category
     * @tutorial tags.category.pkg
     */
    function categoryTagHandler($word)
    {
        if ($word !== false)
        {
            $this->p_vars['tag_value']->add($word);
        } else
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addKeyword($this->p_vars['tagname'],$this->p_vars['tag_value']);
            $this->p_vars[$this->p_vars['docblock_type']]->setExplicitCategory();
        }
    }
    
    /**
     * handles @global
     * @tutorial tags.global.pkg
     */
    function globalTagHandler($word)
    {
        if ($word !== false)
        {
            // no data yet
            $a = trim($this->p_vars['tag_value']->getString());
            if (empty($a))
            {
                // not an empty word
                if (trim($word) != '')
                {
                    if (!empty($this->p_vars['global_type']))
                    {
                        if (!$this->p_flags['define_global'] && !$this->p_flags['function_global'])
                        {
                            // @global type $GLOBALVARNAME ?
                            if (substr($word,0,1) == '$')
                            {
                                $this->p_flags['define_global'] = true;
                                $this->p_flags['function_global'] = false;
                                $this->p_vars['find_global'] = $word;
                            } else
                            { // function @global type description
                                $this->p_flags['function_global'] = true;
                                $this->p_flags['define_global'] = false;
                                $this->p_vars['tag_value']->add($word);
                            }
                        } else
                        {
                            if ($this->p_flags['define_global'])
                            {
                                $this->p_vars['find_global'] .= $word;
                            } elseif($this->p_flags['function_global'])
                            {
                                // description, to be added to the tag
                                $this->p_vars['tag_value']->add($word);
                            }
                        }
                    } else
                    {
                        $this->p_vars['global_type'] = $word;
                    } 
                } else $this->p_vars['tag_value']->add($word); // add whitespace to the tag description
            } else
            { // tag_value has data, must be a function @global
                $this->p_vars['tag_value']->add($word);
            }
        } else
        { // endtag
            if ($this->p_flags['define_global'])
            {
                $this->findGlobal($this->p_vars['find_global']);
            }
            elseif ($this->p_flags['function_global'])
            {
                $this->p_vars[$this->p_vars['docblock_type']]->addFuncGlobal($this->p_vars['global_type'],$this->p_vars['tag_value']);
                $this->p_vars['global_type'] = '';
            }
            else
            {
                addError(PDERROR_MALFORMED_GLOBAL_TAG);
            }
            $this->p_vars['find_global'] = '';
            $this->p_flags['define_global'] = false;
            $this->p_flags['function_global'] = false;
        }
    }
    
    /**
     * handles @staticvar
     * @tutorial tags.staticvar.pkg
     */
    function staticvarTagHandler($word)
    {
        if ($word !== false)
        {
            if (!$this->p_vars['returntype']) $this->p_vars['returntype'] = trim($word);
            else
            {
                if (!$this->p_vars['paramname'])
                {
                    if (substr(trim($word),0,1) == "$")
                    $this->p_vars['paramname'] = trim($word);
                    else $this->p_vars['tag_value']->add($word);
                } else
                {
                    if (0)//strtolower($this->p_vars['paramtype']) == 'object')
                    {
                        if (strlen(trim($word)))
                        $this->p_vars['paramname'] = trim($word);
                    } else $this->p_vars['tag_value']->add($word);
                }
            }
        } else
        {
            if (!$this->p_vars['paramname'])
            $this->p_vars[$this->p_vars['docblock_type']]->addStaticVar(null,$this->p_vars['returntype'],$this->p_vars['tag_value']);
            else
            $this->p_vars[$this->p_vars['docblock_type']]->addStaticVar($this->p_vars['paramname'],$this->p_vars['returntype'],$this->p_vars['tag_value']);
            $this->p_vars['paramname'] = false;
            $this->p_vars['returntype'] = false;
        }
    }
    
    /**
     * handles @uses
     * @tutorial tags.uses.pkg
     */
    function usesTagHandler($word)
    {
        if ($word !== false)
        {
            if (!$this->p_vars['seelement']) $this->p_vars['seelement'] = trim($word);
            else
            {
                $this->p_vars['tag_value']->add($word);
            }
        } else
        {
            $see = new parserStringWithInlineTags;
            $see->add($this->p_vars['seelement']);
            $this->p_vars[$this->p_vars['docblock_type']]->addUses($see,$this->p_vars['tag_value']);
            $this->p_vars['seelement'] = false;
        }
    }
    
    /**
     * handles @param
     * @tutorial tags.param.pkg
     */
    function paramTagHandler($word)
    {
        if ($word !== false)
        {
            if (!$this->p_vars['returntype']) $this->p_vars['returntype'] = trim($word);
            else
            {
                if (!$this->p_vars['paramname'])
                {
                    if (substr(trim($word),0,1) == "$" || substr(trim($word),0,2) == "&$")
                    $this->p_vars['paramname'] = trim($word);
                    else $this->p_vars['tag_value']->add($word);
                } else
                {
                    if (0)//strtolower($this->p_vars['paramtype']) == 'object')
                    {
                        if (strlen(trim($word)))
                        $this->p_vars['paramname'] = trim($word);
                    } else $this->p_vars['tag_value']->add($word);
                }
            }
        } else
        {
            if (!$this->p_vars['paramname'])
            $this->p_vars[$this->p_vars['docblock_type']]->addParam(null,$this->p_vars['returntype'],$this->p_vars['tag_value']);
            else
            $this->p_vars[$this->p_vars['docblock_type']]->addParam($this->p_vars['paramname'],$this->p_vars['returntype'],$this->p_vars['tag_value']);
            $this->p_vars['paramname'] = false;
            $this->p_vars['returntype'] = false;
        }
    }
    
    /**
     * handles @return
     * @tutorial tags.return.pkg
     */
    function returnTagHandler($word)
    {
        if ($word !== false)
        {
            if (!$this->p_vars['returntype']) $this->p_vars['returntype'] = trim($word);
            else
            {
                if (strtolower($this->p_vars['returntype']) == 'object')
                {
                    if (strlen(trim($word)))
                    $this->p_vars['returntype'] = trim($word);
                } else $this->p_vars['tag_value']->add($word);
            }
        } else
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addReturn($this->p_vars['returntype'],$this->p_vars['tag_value']);
            $this->p_vars['returntype'] = false;
        }
    }
    
    /**
     * handles @var
     * @tutorial tags.var.pkg
     */
    function varTagHandler($word)
    {
        if ($word)
        {
            if (!$this->p_vars['vartype']) $this->p_vars['vartype'] = trim($word);
            else
            {
                if (strtolower($this->p_vars['vartype']) == 'object')
                {
                    if (strlen(trim($word)))
                    $this->p_vars['vartype'] = trim($word);
                }
                else $this->p_vars['tag_value']->add($word);
            }
        } elseif ($word === false)
        {
            $this->p_vars[$this->p_vars['docblock_type']]->addVar($this->p_vars['vartype'],$this->p_vars['tag_value']);
            $this->p_vars['vartype'] = false;
        }
    }
    /**#@-*/
    /** @access private */
    function getSource()
    {
        $this->p_flags['get_source'] = true;
    }
    /**#@+
     * @access private
     * @param string token parsed from source
     * @param integer parser constant from {@link Parser.inc}
     */
    /**
     * handler for DOCKEYWORD_EMAIL.
     * this handler recognizes angle brackets < and > surrounding an email address in an @author tag,
     * and returns a mailto: hyperlink
     */
    
    function handleDockeywordEmail($word, $pevent)
    {
        //echo $this->wp->getPos() . ": |$word|\n";
        if (!$this->checkEventPop($word,$pevent) && $word != "<")
        {
            if (strstr($word,"@"))
            {
                $this->p_vars['tag_value']->add('<');
                $this->p_vars['tag_value']->add(new parserLinkInlineTag("mailto:$word",$word));
                $this->p_vars['tag_value']->add('>');
            } else {
                $this->p_vars['tag_value']->add("<$word>");
            }
        }
    }

    /**
     * handler for INLINE_DOCKEYWORD.
     * this handler recognizes {@inline tags} like link, and parses them, replacing them directly
     * in the text flow with their output.
     */
    
    function handleInlineDockeyword($word, $pevent)
    {
        //        echo $this->wp->getPos() . ": |$word|\n";

        //        echo "docktype: $this->p_vars['inline_dockeyword_type']\n";
        if (!isset($this->p_vars['inline_dockeyword_type'])) $this->p_vars['inline_dockeyword_type'] = false;
        if (!isset($this->p_vars['inline_dockeyword_data'])) $this->p_vars['inline_dockeyword_data'] = '';
        if (!$this->p_vars['inline_dockeyword_type'])
        {
            if (in_array($word,$this->allowableInlineTags))
            {
                if ($word == '}')
                $this->p_vars['inline_dockeyword_type'] = '';
                else
                $this->p_vars['inline_dockeyword_type'] = strtolower($word);
                $this->p_vars['whitesp'] = $this->wp->returnWhiteSpace;
                $this->wp->setWhiteSpace(true);
            } else {
                if ($this->p_flags['in_desc'])
                {
                    // fix 1203445
                    if (!isset($this->p_vars['line'][$this->p_vars['linecount']]))
                    {
                        $this->p_vars['line'][$this->p_vars['linecount']] =
                            new parserStringWithInlineTags;
                    }
                    if ($word == '}')
                    {
                        $this->p_vars['line'][$this->p_vars['linecount']]->add('{@');
                    } else
                    {
                        $this->p_vars['line'][$this->p_vars['linecount']]->add('{@'.$word);
                    }
                } elseif($this->p_flags['in_tag'])
                {
                    if ($word == '}')
                    $this->p_vars['tag_value']->add('{@'.$word);
                    else
                    $this->p_vars['tag_value']->add('{@'.$word);
                }
                $this->p_vars['event_stack']->popEvent();
                $this->p_vars['inline_dockeyword_type'] = false;
                $this->p_vars['inline_dockeyword_data'] = '';
                return;
            }
        } else
        {
            if ($word != "}")
            {
                $this->p_vars['inline_dockeyword_data'] .= $word;
            }
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $this->wp->setWhiteSpace($this->p_vars['whitesp']);
            if ($this->p_vars['inline_dockeyword_type']=='link')
            {
                // support hyperlinks of any protocol
                if (is_numeric(strpos($this->p_vars['inline_dockeyword_data'],'://')) || (strpos(trim($this->p_vars['inline_dockeyword_data']),'mailto:') === 0))
                {
                    // if there is more than 1 parameter, the stuff after the space is the hyperlink text
                    if (strpos(trim($this->p_vars['inline_dockeyword_data']),' '))
                    {
                        $i1 = strpos(trim($this->p_vars['inline_dockeyword_data']),' ') + 1;
                        $link = substr(trim($this->p_vars['inline_dockeyword_data']),0,$i1 - 1);
                        $text = substr(trim($this->p_vars['inline_dockeyword_data']),$i1);
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($link,$text);
//                        '<a href="'.$link.'">'.$text.'</a>';
                    }
                    else
                    {
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
                    }
//                    '<a href="'.$this->p_vars['inline_dockeyword_data'].'">'.$this->p_vars['inline_dockeyword_data'].'</a>';
                } else
                {
                    if (!strpos($this->p_vars['inline_dockeyword_data'],','))
                    {
                        $testp = explode('#',$this->p_vars['inline_dockeyword_data']);
                        if (count($testp) - 1)
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$testp[1]);
                        else
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
                    } else
                    $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
                }
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'tutorial')
            {
                $this->p_vars['inline_dockeyword_data'] = new parserTutorialInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'source')
            {
                $this->getSource();
                $this->p_vars['inline_dockeyword_data'] = new parserSourceInlineTag($this->p_vars['inline_dockeyword_data']);
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'inheritdoc')
            {
                $this->p_vars['inline_dockeyword_data'] = new parserInheritdocInlineTag();
            }
            if ($word == '*/')
            {
                if (!isset($this->p_vars['inline_dockeyword_type'])) $this->p_vars['inline_dockeyword_type'] = '';
                if (!isset($this->p_vars['tagname'])) $this->p_vars['tagname'] = '';
                if (!isset($this->p_vars['tag_value']) || !is_object($this->p_vars['tag_value'])) $this->p_vars['tag_value'] = new parserStringWithInlineTags;
                addError(PDERROR_UNTERMINATED_INLINE_TAG,$this->p_vars['inline_dockeyword_type'],$this->p_vars['tagname'],'@'.$this->p_vars['tagname'].' '.$this->p_vars['tag_value']->getString());
        // when we add the error class, raise error here: we reached the end of the docblock
                $this->wp->backupPos($word); 
            }
            if ($this->p_flags['in_desc'])
            {
                $this->p_vars['line'][$this->p_vars['linecount']]->add($this->p_vars['inline_dockeyword_data']);
                $this->p_vars['inline_dockeyword_type'] = false;
                $this->p_vars['inline_dockeyword_data'] = '';
            }
            elseif ($this->p_flags['in_tag'])
            {
                $this->p_vars['tag_value']->add($this->p_vars['inline_dockeyword_data']);
                $this->p_vars['inline_dockeyword_type'] = false;
                $this->p_vars['inline_dockeyword_data'] = '';
            }
        }
    }

    /**
     * handler for INCLUDE.
     * this handler recognizes include/require/include_once/include_once statements, and publishes the
     * data to Render
     */
    
    function handleInclude($word, $pevent)
    {
        if (!$this->p_flags['in_include'])
        {
            $this->p_vars['linenum'] = $this->wp->linenum;
        }
        $this->p_flags['in_include'] = true;
        $a = $this->checkEventPush( $word, $pevent);
        if (!$this->p_flags['includename_isset'])
        {
            $this->p_flags['includename_isset'] = true;
            $this->p_vars['include_name'] = $this->p_vars['last_word'];
            if ($a)
            $this->p_vars['include_value'] = '';
            else
            $this->p_vars['include_value'] = $word;
            unset($this->p_vars['quote_data']);
        } else
        {
            if (!$a)
            {
                if (empty($this->p_vars['include_params_data']))
                {
                    if (isset($this->p_vars['quote_data']))
                    {
                        $this->p_vars['include_value'] .= '"'.$this->p_vars['quote_data'].'"';
                        unset($this->p_vars['quote_data']);
                    }
                    if ($word != ';')
                    $this->p_vars['include_value'] .= $word;
                }
            } else
            {
                $this->p_vars['include_params_data'] = '';
            }
        }

        if ($this->checkEventPop($word,$pevent))
        {
            $this->p_vars['include'] = new parserInclude;
            $this->p_vars['include']->setLineNumber($this->p_vars['linenum']);
            $this->p_flags['in_include'] = false;
            $this->p_vars['include']->setName($this->p_vars['include_name']);
            $this->p_vars['include']->setValue($this->p_vars['include_value']);
            $this->publishEvent(PHPDOCUMENTOR_EVENT_INCLUDE,$this->p_vars['include']);
            $this->p_flags['includename_isset'] = false;
            unset($this->p_vars['include']);
            unset($this->p_vars['include_name']);
            unset($this->p_vars['include_value']);
            unset($this->p_vars['include_params_data']);
        }
    }
    
    /**
     * handler for INCLUDE_PARAMS.
     * this handler parses the contents of ( ) in include/require/include_once/include_once statements
     */
    
    function handleIncludeParams($word, $pevent)
    {
        $this->checkEventPush( $word, $pevent);
        
        $this->p_flags['include_parens'] = true;
        if(!isset($this->p_vars['include_params_data'])) $this->p_vars['include_params_data'] = '';
        
        if ($this->checkEventPop($word,$pevent))
        {
            if (isset($this->p_vars['quote_data']))
            {
                $this->p_vars['include_value'] = $this->p_vars['include_params_data'].'"'.$this->p_vars['quote_data'].'"';
                unset($this->p_vars['quote_data']);
            } else {
                if (!empty($this->p_vars['include_params_data']))
                $this->p_vars['include_value'] = $this->p_vars['include_params_data'];
                else
                $this->p_vars['include_value'] = trim($this->p_vars['last_word']);
            }
        }
        if (isset($this->p_vars['quote_data']))
        {
            $this->p_vars['include_params_data'] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
        }
        if (($word != "'") && ($word != '"'))
        $this->p_vars['include_params_data'] .= $word;
    }
    
    /**
     * handler for INCLUDE_PARAMS_PARENTHESIS.
     * this handler takes all parenthetical statements within file in:
     * include statement include(file), and handles them properly
     */
    
    function handleIncludeParamsParenthesis($word, $pevent)
    {
        if (isset($this->p_vars['quote_data']))
        {
            $this->p_vars['include_params_data'] .= '"'.$this->p_vars['quote_data'].'"';
            unset($this->p_vars['quote_data']);
        }
        $this->p_vars['include_params_data'] .= $word;
        $this->checkEventPush( $word, $pevent);
        $this->checkEventPop( $word, $pevent);
    }
    /**#@-*/
    /**
     * this function checks whether parameter $word is a token for pushing a new event onto the Event Stack.
     * @return mixed    returns false, or the event number
     */
    
    function checkEventPush($word,$pevent)
    {
        $e = false;
        if (isset($this->pushEvent[$pevent]))
        {
            if (isset($this->pushEvent[$pevent][strtolower($word)]))
            $e = $this->pushEvent[$pevent][strtolower($word)];
        }
        if ($e)
        {
            $this->p_vars['event_stack']->pushEvent($e);
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
        if (!isset($this->popEvent[$pevent])) return false;
        if (in_array(strtolower($word),$this->popEvent[$pevent]))
        {
            return $this->p_vars['event_stack']->popEvent();
        } else {
            return false;
        }
    }

    /**
     * setup the parser tokens, and the pushEvent/popEvent arrays
     * @see $tokens, $pushEvent, $popEvent
     */
    
    function setupStates()
    {
        $this->tokens[STATE_PHPCODE]            = array(" ", "\t",";","?>","</script>","/**#@+","/**#@-*/","/**", "//","/*","#","\r\n","\n","\r","(",'<<<','"',"'");
        $this->tokens[STATE_QUOTE]            = array("\\\"","\\\\","\"");
        $this->tokens[STATE_LOGICBLOCK]            = array("{","}","\"","'","/*","//","#","?>","</script>",'<<<','global','static');
        $this->tokens[STATE_FUNC_GLOBAL]        = array("\"","'","/*","//","#",";",",");
        $this->tokens[STATE_STATIC_VAR]        = array("\"","'","/*","//","#",";",",",'=','array');
        $this->tokens[STATE_STATIC_VAR_VALUE]        = array("/*","//","#"," ","\t",";","=","\"","'","array",",");
        $this->tokens[STATE_NOEVENTS]            = array("<?php","<?",'<script language="php">');
        $this->tokens[STATE_COMMENTBLOCK]        = array("*/","\n");
        $this->tokens[STATE_COMMENT]            = array("\r\n","\r","\n");
        $this->tokens[STATE_DEFINE]            = array(" ","(",";");
        $this->tokens[STATE_DEFINE_PARAMS]        = array("/*","//","#",",",")"," ","'","\"","(");
        $this->tokens[STATE_DEFINE_PARAMS_PARENTHESIS]    = array("(","'","\"",")");
        $this->tokens[STATE_FUNCTION_PARAMS]        = array("/*","//","#","\"",",",")","="," ","'","(");
        $this->tokens[STATE_SINGLEQUOTE]        = array("'","\\'","\\\\");
        $this->tokens[STATE_CLASS]            = array(" ", "\t", "?>", "</script>", ";", "}", "{",
                                                      "/**#@+", "/**#@-*/", "/**", "//", "/*", "#",
                                                      "\r\n", "\n", "\r","(");
        $this->tokens[STATE_DOCBLOCK]            = array("*/","*","@","\r\n","\n","\r",". ",".\n",".\t",'{@');
        $this->tokens[STATE_DOCBLOCK_TEMPLATE]            = array("*/","*","@","\r\n","\n","\r",". ",".\n",".\t",'{@');
        $this->tokens[STATE_DOCKEYWORD]            = array("@","*/","*","\n","\r\n","\r","\t"," ","<",">",'{@');
        $this->tokens[STATE_INLINE_DOCKEYWORD]        = array("{@","}","\t"," ","*/");
        $this->tokens[STATE_DOCKEYWORD_EMAIL]        = array(">","\n","\r\n","\r");
        $this->tokens[STATE_VAR]            = array("/*","//","#"," ","\t",";","=",",","\"","'","array");
        $this->tokens[STATE_GLOBAL]            = array("/*","//","#"," ","\t",";","=","\"","'");
        $this->tokens[STATE_GLOBAL_VALUE]            = array("/*","//","#"," ","\t",";","=","\"","'","array");
        $this->tokens[STATE_ARRAY]            = array("/*","//","#","(",")","\"","'","array");
        $this->tokens[STATE_FUNCTION]            = array("(","{","}"," ","\t","&","/*","//","#");
        $this->tokens[STATE_OUTPHP]            = array("<?php","<?",'<script language="php">');
        $this->tokens[STATE_EOFQUOTE]            = array(" ","\t","\n");
        $this->tokens[STATE_ESCAPE]            = false;// this tells the word parser to just cycle
        $this->tokens[STATE_INCLUDE]            = array(" ","(",";","'",'"');
        $this->tokens[STATE_INCLUDE_PARAMS]        = array("/*",")"," ","'","\"","(");
        $this->tokens[STATE_INCLUDE_PARAMS_PARENTHESIS]    = array("(","'","\"",")");

        // For each event word to event mapings
        $this->pushEvent[PARSER_EVENT_QUOTE] = 
            array(
                "\\"    => PARSER_EVENT_ESCAPE
            );
        $this->popEvent[PARSER_EVENT_QUOTE] = array("\"");
##########################
         
        $this->pushEvent[PARSER_EVENT_LOGICBLOCK] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT,
                "global"    => PARSER_EVENT_FUNC_GLOBAL,
                "static"    => PARSER_EVENT_STATIC_VAR,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "{"    => PARSER_EVENT_LOGICBLOCK,
                "?>"    => PARSER_EVENT_OUTPHP,
                "</script>"    => PARSER_EVENT_OUTPHP,
                "<<<"    => PARSER_EVENT_EOFQUOTE
            );
        $this->popEvent[PARSER_EVENT_LOGICBLOCK] = array("}");
##########################

        $this->pushEvent[PARSER_EVENT_FUNC_GLOBAL] =
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
            );
        $this->popEvent[PARSER_EVENT_FUNC_GLOBAL] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_STATIC_VAR] =
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "="        => PARSER_EVENT_STATIC_VAR_VALUE,
            );
        $this->popEvent[PARSER_EVENT_STATIC_VAR] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_STATIC_VAR_VALUE] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "array" => PARSER_EVENT_ARRAY,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_STATIC_VAR_VALUE] = array(";",",");
##########################

        $this->pushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                "<?php" => PARSER_EVENT_PHPCODE,
                "<?" => PARSER_EVENT_PHPCODE,
                '<script language="php">' => PARSER_EVENT_PHPCODE,
            );
##########################

        $this->pushEvent[PARSER_EVENT_PHPCODE] = 
            array(
                "function"     => PARSER_EVENT_FUNCTION,
                "class"     => PARSER_EVENT_CLASS,
                "define"     => PARSER_EVENT_DEFINE,
                "include_once" => PARSER_EVENT_INCLUDE,
                "require_once" => PARSER_EVENT_INCLUDE,
                "include"    => PARSER_EVENT_INCLUDE,
                "require"    => PARSER_EVENT_INCLUDE,
                "//"         => PARSER_EVENT_COMMENT,
                "#"         => PARSER_EVENT_COMMENT,
                "/*"         => PARSER_EVENT_COMMENTBLOCK,
                "/**"         => PARSER_EVENT_DOCBLOCK,
                "/**#@+"    => PARSER_EVENT_DOCBLOCK_TEMPLATE,
                "/**#@-*/"    => PARSER_EVENT_END_DOCBLOCK_TEMPLATE,
                "\""        => PARSER_EVENT_QUOTE,
                "'"        => PARSER_EVENT_SINGLEQUOTE,
                "<<<"        => PARSER_EVENT_EOFQUOTE,
                "?>"         => PARSER_EVENT_OUTPHP,
                "</script>"         => PARSER_EVENT_OUTPHP,
            );
##########################

        $this->pushEvent[PARSER_EVENT_FUNCTION] = 
            array(
                "("     => PARSER_EVENT_FUNCTION_PARAMS,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "{"     => PARSER_EVENT_LOGICBLOCK
            );
        $this->popEvent[PARSER_EVENT_FUNCTION] = array("}");
##########################

        $this->pushEvent[PARSER_EVENT_DOCBLOCK] = 
            array(
                "@"     => PARSER_EVENT_DOCKEYWORD,
                "{@"    => PARSER_EVENT_INLINE_DOCKEYWORD
            );
        $this->popEvent[PARSER_EVENT_DOCBLOCK] = array("*/");
##########################

        $this->pushEvent[PARSER_EVENT_DOCBLOCK_TEMPLATE] = 
            array(
                "@"     => PARSER_EVENT_DOCKEYWORD,
                "{@"    => PARSER_EVENT_INLINE_DOCKEYWORD
            );
        $this->popEvent[PARSER_EVENT_DOCBLOCK_TEMPLATE] = array("*/");
##########################

        $this->pushEvent[PARSER_EVENT_CLASS] = 
            array(
                "function"     => PARSER_EVENT_FUNCTION,
                "var"         => PARSER_EVENT_VAR,
                "/**"         => PARSER_EVENT_DOCBLOCK,
                "/**#@+"    => PARSER_EVENT_DOCBLOCK_TEMPLATE,
                "/**#@-*/"    => PARSER_EVENT_END_DOCBLOCK_TEMPLATE,
                "//"         => PARSER_EVENT_COMMENT,
                "/*"         => PARSER_EVENT_COMMENTBLOCK,
                "#"         => PARSER_EVENT_COMMENT,
                "?>"        => PARSER_EVENT_OUTPHP,
                "</script>"    => PARSER_EVENT_OUTPHP,
            );
        $this->popEvent[PARSER_EVENT_CLASS] = array("}");
##########################

        $this->pushEvent[PARSER_EVENT_DEFINE] = 
            array(
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "("     => PARSER_EVENT_DEFINE_PARAMS
            );
        $this->popEvent[PARSER_EVENT_DEFINE] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_INCLUDE] = 
            array(
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "("     => PARSER_EVENT_INCLUDE_PARAMS,
                "'"        => PARSER_EVENT_SINGLEQUOTE,
                '"'        => PARSER_EVENT_QUOTE,
            );
        $this->popEvent[PARSER_EVENT_INCLUDE] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_DEFINE_PARAMS] = 
            array(
                "("    =>    PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS,
                "'"        => PARSER_EVENT_SINGLEQUOTE,
                '"' =>    PARSER_EVENT_QUOTE,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_DEFINE_PARAMS] = array(")");
##########################

        $this->pushEvent[PARSER_EVENT_INCLUDE_PARAMS] = 
            array(
                "("    =>    PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS,
                "'" =>    PARSER_EVENT_SINGLEQUOTE,
                '"' =>    PARSER_EVENT_QUOTE,
            );
        $this->popEvent[PARSER_EVENT_INCLUDE_PARAMS] = array(")");
##########################

        $this->pushEvent[PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS] =
            array(
                "("    =>    PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS,
                "'" =>    PARSER_EVENT_SINGLEQUOTE,
                '"' =>    PARSER_EVENT_QUOTE,
            );
        $this->popEvent[PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS] = array(")");
##########################

        $this->pushEvent[PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS] =
            array(
                "("    =>    PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS,
                "'" =>    PARSER_EVENT_SINGLEQUOTE,
                '"' =>    PARSER_EVENT_QUOTE,
            );
        $this->popEvent[PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS] = array(")");
##########################

        $this->pushEvent[PARSER_EVENT_VAR] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "array" => PARSER_EVENT_ARRAY,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_VAR] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_DEFINE_GLOBAL] = 
            array(
                "="    => PARSER_EVENT_GLOBAL_VALUE,
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_DEFINE_GLOBAL] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_GLOBAL_VALUE] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "array" => PARSER_EVENT_ARRAY,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_GLOBAL_VALUE] = array(";");
##########################

        $this->pushEvent[PARSER_EVENT_COMMENT] = 
            array(
                "\\"    => PARSER_EVENT_ESCAPE
            );
        $this->popEvent[PARSER_EVENT_COMMENT] = array("\n");
##########################

        $this->popEvent[PARSER_EVENT_COMMENTBLOCK] = array("*/");
##########################
        $this->pushEvent[PARSER_EVENT_SINGLEQUOTE] = 
            array(
                "\\"    => PARSER_EVENT_ESCAPE
            );

        $this->popEvent[PARSER_EVENT_SINGLEQUOTE] = array("'");
##########################
        $this->pushEvent[PARSER_EVENT_FUNCTION_PARAMS] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "array" => PARSER_EVENT_ARRAY,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_FUNCTION_PARAMS] = array(")");
##########################
        $this->pushEvent[PARSER_EVENT_DOCKEYWORD] = 
            array(
//                "<"    => PARSER_EVENT_DOCKEYWORD_EMAIL,
                "{@" => PARSER_EVENT_INLINE_DOCKEYWORD,
            );

        $this->popEvent[PARSER_EVENT_DOCKEYWORD] = array("*/");
##########################

        $this->popEvent[PARSER_EVENT_INLINE_DOCKEYWORD] = array("}","*/");
##########################

        $this->popEvent[PARSER_EVENT_OUTPHP] = array("<?php","<?",'<script language="php">');
##########################

        $this->popEvent[PARSER_EVENT_DOCKEYWORD_EMAIL] = array(">","\n");

##########################
        $this->pushEvent[PARSER_EVENT_ARRAY] = 
            array(
                "\""    => PARSER_EVENT_QUOTE,
                "'"    => PARSER_EVENT_SINGLEQUOTE,
                "array" => PARSER_EVENT_ARRAY,
                "/*"     => PARSER_EVENT_COMMENTBLOCK,
                "//"     => PARSER_EVENT_COMMENT,
                "#"     => PARSER_EVENT_COMMENT
            );
        $this->popEvent[PARSER_EVENT_ARRAY] = array(")");
##########################
    }

    /**
     * tell the parser's WordParser {@link $wp} to set up tokens to parse words by.
     * tokens are word separators.  In English, a space or punctuation are examples of tokens.
     * In PHP, a token can be a ;, a parenthesis, or even the word "function"
     * @param    $value integer an event number
     * @see WordParser
     */
    
    function configWordParser($e)
    {
        $this->wp->setSeperator($this->tokens[($e + 100)]);
    }

    /**
     * Debugging function, takes an event number and attempts to return its name
     * @param    $value integer an event number
     */
    

    function getParserEventName ($value)
    {    
        $lookup = array(
            PARSER_EVENT_NOEVENTS         => "PARSER_EVENT_NOEVENTS",
            PARSER_EVENT_PHPCODE        => "PARSER_EVENT_PHPCODE",
            PARSER_EVENT_DOCBLOCK        => "PARSER_EVENT_DOCBLOCK",
            PARSER_EVENT_FUNCTION        => "PARSER_EVENT_FUNCTION",
            PARSER_EVENT_CLASS        => "PARSER_EVENT_CLASS",
            PARSER_EVENT_DEFINE        => "PARSER_EVENT_DEFINE",
            PARSER_EVENT_DEFINE_PARAMS    => "PARSER_EVENT_DEFINE_PARAMS",
            PARSER_EVENT_COMMENT        => "PARSER_EVENT_COMMENT",
            PARSER_EVENT_COMMENTBLOCK    => "PARSER_EVENT_COMMENTBLOCK",
            PARSER_EVENT_ESCAPE        => "PARSER_EVENT_ESCAPE",
            PARSER_EVENT_QUOTE        => "PARSER_EVENT_QUOTE",
            PARSER_EVENT_FUNCTION_PARAMS    => "PARSER_EVENT_FUNCTION_PARAMS",
            PARSER_EVENT_SINGLEQUOTE    => "PARSER_EVENT_SINGLEQUOTE",
            PARSER_EVENT_VAR        => "PARSER_EVENT_VAR",
            PARSER_EVENT_LOGICBLOCK        => "PARSER_EVENT_LOGICBLOCK",
            PARSER_EVENT_OUTPHP        => "PARSER_EVENT_OUTPHP",
            PARSER_EVENT_DOCKEYWORD        => "PARSER_EVENT_DOCKEYWORD",
            PARSER_EVENT_DOCKEYWORD_EMAIL    => "PARSER_EVENT_DOCKEYWORD_EMAIL",
            PARSER_EVENT_ARRAY        => "PARSER_EVENT_ARRAY",
            PARSER_EVENT_INLINE_DOCKEYWORD    =>    "PARSER_EVENT_INLINE_DOCKEYWORD",
            PARSER_EVENT_EOFQUOTE    =>    "PARSER_EVENT_EOFQUOTE",
            PARSER_EVENT_INCLUDE    =>    "PARSER_EVENT_INCLUDE",
            PARSER_EVENT_INCLUDE_PARAMS    =>    "PARSER_EVENT_INCLUDE_PARAMS",
            PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS    => "PARSER_EVENT_INCLUDE_PARAMS_PARENTHESIS",
            PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS => "PARSER_EVENT_DEFINE_PARAMS_PARENTHESIS",
            PARSER_EVENT_DEFINE_GLOBAL => "PARSER_EVENT_DEFINE_GLOBAL",
            PARSER_EVENT_GLOBAL_VALUE => "PARSER_EVENT_GLOBAL_VALUE",
            PARSER_EVENT_FUNC_GLOBAL => "PARSER_EVENT_FUNC_GLOBAL",
            PARSER_EVENT_STATIC_VAR => "PARSER_EVENT_STATIC_VAR",
            PARSER_EVENT_DOCBLOCK_TEMPLATE => "PARSER_EVENT_DOCBLOCK_TEMPLATE",
            PARSER_EVENT_END_DOCBLOCK_TEMPLATE => "PARSER_EVENT_END_DOCBLOCK_TEMPLATE",
            PARSER_EVENT_METHOD_LOGICBLOCK => 'PARSER_EVENT_METHOD_LOGICBLOCK',
            PARSER_EVENT_CLASS_MEMBER => 'PARSER_EVENT_CLASS_MEMBER',
            PARSER_EVENT_METHOD => 'PARSER_EVENT_METHOD',
            PARSER_EVENT_QUOTE_VAR => 'PARSER_EVENT_QUOTE_VAR',
            PARSER_EVENT_ACCESS_MODIFIER => 'PARSER_EVENT_ACCESS_MODIFIER',
            PARSER_EVENT_IMPLEMENTS => 'PARSER_EVENT_IMPLEMENTS',
            PARSER_EVENT_CLASS_CONSTANT => 'PARSER_EVENT_CLASS_CONSTANT',
            PARSER_EVENT_VAR_ARRAY => 'PARSER_EVENT_VAR_ARRAY',
            PARSER_EVENT_VAR_ARRAY_COMMENT =>'PARSER_EVENT_VAR_ARRAY_COMMENT',
        );
        if (isset($lookup[$value]))
        return $lookup[$value];
        else return $value;
    }
}

/**
 * Global package page parser
 *
 * @deprecated in favor of tutorials
 * @tutorial tutorials.pkg
 * @package phpDocumentor
 * @subpackage Parsers
 */
class ppageParser extends Parser
{
    /** @var string */
    var $package = false;
    /** @var string */
    var $subpackage = '';
    /**
     * set up invariant Parser variables
     */
    function ppageParser()
    {
        Parser::Parser();
        $this->allowableInlineTags = $GLOBALS['_phpDocumentor_inline_tutorial_tags_allowed'];
        $this->eventHandlers = array();
        $this->eventHandlers[PARSER_EVENT_NOEVENTS] = 'defaultHandler';
        $this->eventHandlers[PARSER_EVENT_INLINE_DOCKEYWORD] = 'handleInlineDocKeyword';
    }
    
    /**
     * set up invariant Parser variables
     */
    function setupStates()
    {
        $this->tokens[STATE_NOEVENTS]        = array("{@","}");
        $this->tokens[STATE_INLINE_DOCKEYWORD]        = array("{@","}","\t"," ");

##########################

        $this->pushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                "{@" => PARSER_EVENT_INLINE_DOCKEYWORD
            );
##########################

        $this->popEvent[PARSER_EVENT_INLINE_DOCKEYWORD] = array("}");
    }
    
    /**
     * Parse a new file
     *
     * @param    string    $parse_data
     * @param    string    $package
     * @param    int    $subpackage
     * @return    mixed    false or parsed data
     */
    function parse (&$parse_data,$xml,$package = 'default',$subpackage = '',$tutorial = '',
                    $category='default', $path='')
    {
        $this->setupStates();
        $this->p_vars['total'] = new parserPackagePage($package,$xml);
        $this->p_vars['tutorial'] = $tutorial;
        $this->_path = $path;
        $this->category = $category;
        $this->package = $package;
        if (!isset($subpackage) || !$subpackage) $subpackage = '';
        $this->subpackage = $subpackage;
        if (strlen($parse_data) == 0)
        {
            return false;
        }

        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->p_vars['event_stack'] = new EventStack;
        // change this to a new ParserStringWithInlineTags, and change all $total .= blah to $total->add(blah)
        // then modify phpDocumentor_IntermediateParser->Convert to convert all package pages (the package page handler in phpDocumentor_IntermediateParser should
        // save them all in a variable) to perform the linking.  then, remove the legacy code from handleDocBlock
        // and handleClass in Render.inc, and do a loop that converts each package page, and passes it to handleEvent
        // just like Converter::walk does with the other elements.  The only other addition that might be good is a
        // new descendant of parserElement parserPackagePage that contains the data and stuff.  Hope this helps :)
        $total = '';

        $this->wp->setup($parse_data);

        $this->p_flags['reset_quote_data'] = true;

        do
        {
            $lpevent = $pevent;
            $pevent = $this->p_vars['event_stack']->getEvent();
            if ($lpevent != $pevent)
            {
                $this->p_vars['last_pevent'] = $lpevent;
            }

            if ($this->p_vars['last_pevent'] != $pevent)
            {
                // its a new event so the word parser needs to be reconfigured 
                $this->configWordParser($pevent);
            }

            if (!$xml)
            $this->publishEvent(PHPDOCUMENTOR_EVENT_NEWSTATE,($pevent + 100));


            $this->p_vars['last_word'] = $word;
            $word = $this->wp->getWord();

            if (0)//PHPDOCUMENTOR_DEBUG == true)
            {
                echo "LAST: |" . $this->p_vars['last_word'] . "|\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo $this->wp->getPos() . ": |$word|\n";
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                $this->$handle($word, $pevent);
            }
        } while (!($word === false));
        if (!$xml)
        $this->PublishEvent(PHPDOCUMENTOR_EVENT_PACKAGEPAGE,$this->p_vars['total']);
        else
        return $this->p_vars['total']->value;
    }
    
    /**
     * Handles all non-inline tags
     * 
     * @param string token
     * @param integer parser event
     */
    function defaultHandler($word, $pevent)
    {
        if (!$this->checkEventPush( $word, $pevent))
        {
            if ($word) $this->p_vars['total']->add($word);
        }
    }

    /**
     * handler for INLINE_DOCKEYWORD.
     * this handler recognizes {@inline tags} like link, and parses them, replacing them directly
     * in the text flow with their output.
     * @param string token
     * @param integer parser event
     */
    
    function handleInlineDockeyword($word, $pevent)
    {
        //        echo $this->wp->getPos() . ": |$word|\n";

        //        echo "docktype: $this->p_vars['inline_dockeyword_type']\n";
        if (!isset($this->p_vars['inline_dockeyword_type'])) $this->p_vars['inline_dockeyword_type'] = false;
        if (!isset($this->p_vars['inline_dockeyword_data'])) $this->p_vars['inline_dockeyword_data'] = '';
        if (!$this->p_vars['inline_dockeyword_type'])
        {
            if (in_array($word,$this->allowableInlineTags))
            {
                $this->p_vars['inline_dockeyword_type'] = strtolower($word);
                $this->p_vars['whitesp'] = $this->wp->returnWhiteSpace;
                $this->wp->setWhiteSpace(true);
            } else {
                if ($word == '}')
                $this->p_vars['total']->add('{@');
                else
                {
                    $this->p_vars['total']->add('{@'.$word);
                    $this->p_vars['event_stack']->popEvent();
                }
                $this->p_vars['inline_dockeyword_type'] = false;
                $this->p_vars['inline_dockeyword_data'] = '';
            }
        } else
        {
            if ($word != "}")
            {
                $this->p_vars['inline_dockeyword_data'] .= $word;
            }
        }
        if ($this->checkEventPop($word,$pevent))
        {
            $this->wp->setWhiteSpace($this->p_vars['whitesp']);
            if ($this->p_vars['inline_dockeyword_type']=='link')
            {
                // support hyperlinks of any protocol
                if (is_numeric(strpos($this->p_vars['inline_dockeyword_data'],'://')) || (strpos(trim($this->p_vars['inline_dockeyword_data']),'mailto:') === 0))
                {
                    // if there is more than 1 parameter, the stuff after the space is the hyperlink text
                    if (strpos(trim($this->p_vars['inline_dockeyword_data']),' '))
                    {
                        $i1 = strpos(trim($this->p_vars['inline_dockeyword_data']),' ') + 1;
                        $link = substr(trim($this->p_vars['inline_dockeyword_data']),0,$i1 - 1);
                        $text = substr(trim($this->p_vars['inline_dockeyword_data']),$i1);
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($link,$text);
//                        '<a href="'.$link.'">'.$text.'</a>';
                    }
                    else
                    {
                        $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
                    }
//                    '<a href="'.$this->p_vars['inline_dockeyword_data'].'">'.$this->p_vars['inline_dockeyword_data'].'</a>';
                } else
                {
                    $testp = explode('#',$this->p_vars['inline_dockeyword_data']);
                    if (count($testp) - 1) $this->p_vars['inline_dockeyword_data'] = $testp[1];
                    $this->p_vars['inline_dockeyword_data'] = new parserLinkInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
                }
            }
            if ($this->p_vars['inline_dockeyword_type']=='id')
            {
                $this->p_vars['inline_dockeyword_data'] = new parserIdInlineTag($this->category,$this->package,$this->subpackage,$this->p_vars['tutorial'],trim($this->p_vars['inline_dockeyword_data']));
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'tutorial')
            {
                $this->p_vars['inline_dockeyword_data'] = new parserTutorialInlineTag($this->p_vars['inline_dockeyword_data'],$this->p_vars['inline_dockeyword_data']);
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'toc')
            {
                $this->p_vars['inline_dockeyword_data'] = new parserTocInlineTag();
            }
            if ($this->p_vars['inline_dockeyword_type'] == 'example')
            {
                $example =
                    new parserExampleInlineTag($this->p_vars['inline_dockeyword_data'], $this->_path, true);
                $this->p_vars['total']->add($example->getProgramListing());
            } else
            {
                $this->p_vars['total']->add($this->p_vars['inline_dockeyword_data']);
            }
            $this->p_vars['inline_dockeyword_type'] = false;
            $this->p_vars['inline_dockeyword_data'] = '';
        }
    }
}
?>
