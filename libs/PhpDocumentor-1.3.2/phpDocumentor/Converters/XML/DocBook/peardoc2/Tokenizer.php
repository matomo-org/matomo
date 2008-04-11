<?php
/**
 * XML/Beautifier.php
 *
 * Format XML files containing unknown entities (like all of peardoc)
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2004-2006 Gregory Beaver
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
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2004-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.3.0
 */
/**
 * From the XML_Beautifier package
 */
require_once 'XML/Beautifier/Tokenizer.php';
/**
 * Highlights source code using {@link parse()}
 * @package phpDocumentor
 * @subpackage Parsers
 */
class phpDocumentor_XML_Beautifier_Tokenizer extends XML_Beautifier_Tokenizer
{
    /**#@+
     * @access private
     */
    var $_curthing;
    var $_tag;
    var $_attrs;
    var $_attr;

    /**#@-*/
    /**
     * @var array
     */
    var $eventHandlers = array(
                                PHPDOC_XMLTOKEN_EVENT_NOEVENTS => 'normalHandler',
                                PHPDOC_XMLTOKEN_EVENT_XML => 'parseXMLHandler',
                                PHPDOC_XMLTOKEN_EVENT_PI => 'parsePiHandler',
                                PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE => 'attrHandler',
                                PHPDOC_XMLTOKEN_EVENT_OPENTAG => 'tagHandler',
                                PHPDOC_XMLTOKEN_EVENT_IN_CDATA => 'realcdataHandler',
                                PHPDOC_XMLTOKEN_EVENT_DEF => 'defHandler',
                                PHPDOC_XMLTOKEN_EVENT_CLOSETAG => 'closetagHandler',
                                PHPDOC_XMLTOKEN_EVENT_ENTITY => 'entityHandler',
                                PHPDOC_XMLTOKEN_EVENT_COMMENT => 'commentHandler',
                                PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE => 'stringHandler',
                                PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE => 'stringHandler',
                                PHPDOC_XMLTOKEN_EVENT_CDATA => 'parseCdataHandler',
    );

    /**
     * Parse a new file
     *
     * The parse() method is a do...while() loop that retrieves tokens one by
     * one from the {@link $_event_stack}, and uses the token event array set up
     * by the class constructor to call event handlers.
     *
     * The event handlers each process the tokens passed to them, and use the
     * {@link _addoutput()} method to append the processed tokens to the
     * {@link $_line} variable.  The word parser calls {@link newLineNum()}
     * every time a line is reached.
     *
     * In addition, the event handlers use special linking functions
     * {@link _link()} and its cousins (_classlink(), etc.) to create in-code
     * hyperlinks to the documentation for source code elements that are in the
     * source code.
     *
     * @uses setupStates() initialize parser state variables
     * @uses configWordParser() pass $parse_data to prepare retrieval of tokens
     * @param    string
     * @param    Converter
     * @param    false|string full path to file with @filesource tag, if this
     *           is a @filesource parse
     * @param    false|integer starting line number from {@}source linenum}
     * @staticvar    integer    used for recursion limiting if a handler for
     *                          an event is not found
     * @return    bool
     */
    function parseString ($parse_data)
    {
        static $endrecur = 0;
        $parse_data = str_replace(array("\r\n", "\t"), array("\n", '    '), $parse_data);
        $this->setupStates($parse_data);

        $this->configWordParser(PHPDOC_XMLTOKEN_EVENT_NOEVENTS);
        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->_curthing = '';

        do
        {
            $lpevent = $pevent;
            $pevent = $this->_event_stack->getEvent();
            if ($lpevent != $pevent)
            {
                $this->_last_pevent = $lpevent;
                $this->configWordParser($pevent);
            }
            $this->_wp->setWhitespace(true);

            $dbg_linenum = $this->_wp->linenum;
            $dbg_pos = $this->_wp->getPos();
            $this->_pv_last_word = $word;
            $this->_pv_curline = $this->_wp->linenum;
            $word = $this->_wp->getWord();

            if (0)//PHPDOCUMENTOR_DEBUG == true)
            {
                echo "LAST: ";
                echo "|" . $this->_pv_last_word;
                echo "|\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo "LASTPEVENT: " . $this->getParserEventName($this->_last_pevent) . "\n";
//                echo "LINE: ".$this->_line."\n";
//                echo "OUTPUT: ".$this->_output."\n";
                echo $dbg_linenum.'-'.$dbg_pos . ": ";
                echo '|'.htmlspecialchars($word);
                echo "|\n";
                echo "-------------------\n\n\n";
                flush();
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                $this->$handle($word, $pevent);
            } else
            {
                echo ('WARNING: possible error, no handler for event number '.$pevent);
                if ($endrecur++ == 25)
                {
                    return $this->raiseError("FATAL ERROR, recursion limit reached");
                }
            }
        } while (!($word === false));
        return true;
    }
    
    /**#@+
     * Event Handlers
     *
     * All Event Handlers use {@link checkEventPush()} and
     * {@link checkEventPop()} to set up the event stack and parser state.
     * @access private
     * @param string|array token value
     * @param integer parser event from {@link Parser.inc}
     */
    /**
     * Most tokens only need highlighting, and this method handles them
     */
    function normalHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            $this->_addoutput($pevent);
            $this->_curthing = '';
            return;
        }
        $this->_curthing .= $word;
        
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
        }
    }

    /**
     * handle <!-- comments -->
     */
    function commentHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
        
        $this->_curthing .= $word;
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
        }
    }

    /**
     * handle <?Processor instructions?>
     */
    function parsePiHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
            $this->_attrs = null;
            return;
        }
        if (!strlen($this->_curthing)) {
            $this->_curthing .= str_replace('<?', '', $word);
        } else {
            if (!isset($this->_attrs) || !is_string($this->_attrs)) {
                $this->_attrs = '';
            }
            $this->_attrs .= $word;
        }
    }

    /**
     * handle <?xml Processor instructions?>
     */
    function parseXMLHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
        
        $this->_curthing .= $word;
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
        }
    }

    /**
     * handle <![CDATA[ unescaped text ]]>
     */
    function realcdataHandler($word, $pevent)
    {
        $this->_curthing .= $word;
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
        }
    }

    /**
     * handle <tags>
     */
    function tagHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            $this->_curthing = '';
            return;
        }
        
        if ($word{0} == '<') {
            $this->_tag = substr($word, 1);
        }
        
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_tag = null;
            $this->_attrs = null;
            if ($word == '>') {
                $this->_event_stack->pushEvent(PHPDOC_XMLTOKEN_EVENT_CDATA);
                return;
            }
        }
    }

    /**
     * handle </tags>
     */
    function closetagHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_tag = '';
            return;
        }
        $this->_tag = trim(str_replace('</', '', $word));
    }

    /**
     * handle <!def>
     */
    function defHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
        
        $this->_curthing .= $word;
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
        }
    }

    /**
     * Most tokens only need highlighting, and this method handles them
     */
    function attrHandler($word, $pevent)
    {
        if ($e = $this->checkEventPush($word, $pevent)) {
            return;
        }
        if (!isset($this->_attrs) || !is_array($this->_attrs)) {
            $this->_attrs = array();
        }
        if (strpos($word, '=')) {
            $this->_attrs[$this->_attr = trim(str_replace('=', '', $word))] = '';
        }
        if ($this->checkEventPop($word, $pevent)) {
            $this->_wp->backupPos($word);
            return;
        }
    }

    /**
     * handle attribute values
     */
    function stringHandler($word, $pevent)
    {
        if ($this->checkEventPop($word, $pevent)) {
            return;
        }
        $this->_attrs[$this->_attr] = $word;
    }

    /**
     * handle &entities;
     */
    function entityHandler($word, $pevent)
    {
        if ($this->checkEventPop($word, $pevent)) {
            $this->_addoutput($pevent);
            $this->_curthing = '';
            return;
        }
        if (strlen($word) && $word{0} == '&') {
            $word = substr($word, 1);
        }
        $this->_curthing .= $word;
    }

    /**
     * handle tag contents
     */
    function parseCdataHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) {
            $this->_wp->backupPos($word);
            if (strlen($this->_curthing)) {
                $this->_addoutput($pevent);
            }
            $this->_curthing = '';
            return;
        }
        if ($this->checkEventPop($word, $pevent)) {
            if (strlen($this->_curthing)) {
                $this->_addoutput($pevent);
            }
            $this->_curthing = '';
            $this->_event_stack->pushEvent(PHPDOC_XMLTOKEN_EVENT_CLOSETAG);
            return;
        }
        $this->_curthing .= $word;
    }

    /**#@-*/

    /**
     * Handler for real character data
     *
     * @access protected
     * @param  object XML parser object
     * @param  string CDATA
     * @return void
     */
    function incdataHandler($parser, $cdata)
    {
        if ((string)$cdata === '') {
            return true;
        }

        $struct = array(
                         "type"  => PHPDOC_BEAUTIFIER_CDATA,
                         "data"  => $cdata,
                         "depth" => $this->_depth
                       );

        $this->_appendToParent($struct);
    }
    /**#@+
     * Output Methods
     * @access private
     */
    /**
     * This method adds output to {@link $_line}
     *
     * If a string with variables like "$test this" is present, then special
     * handling is used to allow processing of the variable in context.
     * @see _flush_save()
     */
    function _addoutput($event)
    {
        $type =
        array(
            PHPDOC_XMLTOKEN_EVENT_NOEVENTS => '_handleXMLDefault',
            PHPDOC_XMLTOKEN_EVENT_CLOSETAG => 'endHandler',
            PHPDOC_XMLTOKEN_EVENT_ENTITY => 'entityrefHandler',
            PHPDOC_XMLTOKEN_EVENT_DEF => '_handleXMLDefault',
            PHPDOC_XMLTOKEN_EVENT_PI => 'parsePiHandler',
            PHPDOC_XMLTOKEN_EVENT_XML => '_handleXMLDefault',
            PHPDOC_XMLTOKEN_EVENT_OPENTAG => 'startHandler',
            PHPDOC_XMLTOKEN_EVENT_COMMENT => '_handleXMLDefault',
            PHPDOC_XMLTOKEN_EVENT_CDATA => 'cdataHandler',
            PHPDOC_XMLTOKEN_EVENT_IN_CDATA => 'incdataHandler',
        );
        $method = $type[$event];
        switch ($event) {
            case PHPDOC_XMLTOKEN_EVENT_COMMENT :
//                echo "comment: $this->_curthing\n";
                $this->$method(false, $this->_curthing);
            break;
            case PHPDOC_XMLTOKEN_EVENT_OPENTAG :
//                echo "open tag: $this->_tag\n";
//                var_dump($this->_attrs);
                $this->$method(false, $this->_tag, $this->_attrs);
            break;
            case PHPDOC_XMLTOKEN_EVENT_CLOSETAG :
//                echo "close tag: $this->_tag\n";
                $this->$method(false, $this->_curthing);
            break;
            case PHPDOC_XMLTOKEN_EVENT_NOEVENTS :
                if (!strlen($this->_curthing)) {
                    return;
                }
//                echo "default: $this->_curthing\n";
                $this->$method(false, $this->_curthing);
            break;
            case PHPDOC_XMLTOKEN_EVENT_DEF :
//                echo "<!definition: $this->_curthing\n";
                $this->$method(false, $this->_curthing);
            break;
            case PHPDOC_XMLTOKEN_EVENT_PI :
//                echo "<?pi: $this->_curthing\n";
//                echo "<?pi attrs: $this->_attrs\n";
                $this->$method(false, $this->_curthing, $this->_attrs);
            break;
            case PHPDOC_XMLTOKEN_EVENT_XML :
//                echo "<?xml: $this->_curthing\n";
                $this->$method(false, $this->_curthing, $this->_attrs);
            break;
            case PHPDOC_XMLTOKEN_EVENT_CDATA :
            case PHPDOC_XMLTOKEN_EVENT_IN_CDATA :
//                echo "cdata: $this->_curthing\n";
                $this->$method(false, $this->_curthing);
            break;
            case PHPDOC_XMLTOKEN_EVENT_ENTITY :
//                echo "entity: $this->_curthing\n";
                $this->$method(false, $this->_curthing, false, false, false);
            break;
        }
    }
    /**#@-*/

    /**
     * tell the parser's WordParser {@link $wp} to set up tokens to parse words by.
     * tokens are word separators.  In English, a space or punctuation are examples of tokens.
     * In PHP, a token can be a ;, a parenthesis, or even the word "function"
     * @param    $value integer an event number
     * @see WordParser
     */
    
    function configWordParser($e)
    {
        $this->_wp->setSeperator($this->tokens[($e + 100)]);
    }
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
        if (!isset($this->popEvent[$pevent])) return false;
        if (in_array(strtolower($word),$this->popEvent[$pevent]))
        {
            return $this->_event_stack->popEvent();
        } else {
            return false;
        }
    }

    /**
     * Initialize all parser state variables
     * @param boolean true if we are highlighting an inline {@}source} tag's
     *                output
     * @param false|string name of class we are going to start from
     * @uses $_wp sets to a new {@link phpDocumentor_HighlightWordParser}
     */
    function setupStates($parsedata)
    {
        $this->_output = '';
        $this->_line = '';
        unset($this->_wp);
        $this->_wp = new WordParser;
        $this->_wp->setup($parsedata);
        $this->_event_stack = @(new EventStack);
        $this->_event_stack->popEvent();
        $this->_event_stack->pushEvent(PHPDOC_XMLTOKEN_EVENT_NOEVENTS);
        $this->_pv_linenum = null;
        $this->_pv_next_word = false;
    }

    /**
     * Initialize the {@link $tokenpushEvent, $wordpushEvent} arrays
     */
    function phpDocumentor_XML_Beautifier_Tokenizer()
    {
        $this->tokens[STATE_XMLTOKEN_CDATA] =
        $this->tokens[STATE_XMLTOKEN_NOEVENTS]        = array('<?xml', '<!--', '<![CDATA[', '<!', '</', '<?', '<');//, '&');
        $this->tokens[STATE_XMLTOKEN_OPENTAG]        = array("\n","\t"," ", '>', '/>');
        $this->tokens[STATE_XMLTOKEN_XML] =
        $this->tokens[STATE_XMLTOKEN_PI]        = array("\n","\t"," ", '?>');
        $this->tokens[STATE_XMLTOKEN_IN_CDATA]        = array(']]>');
        $this->tokens[STATE_XMLTOKEN_CLOSETAG]        = array("\n",'>');
        $this->tokens[STATE_XMLTOKEN_COMMENT]        = array("\n",'-->');
        $this->tokens[STATE_XMLTOKEN_DEF]        = array("\n",']>','>');
        $this->tokens[STATE_XMLTOKEN_ENTITY]        = array("\n",';');
        $this->tokens[STATE_XMLTOKEN_ATTRIBUTE]        = array("\n",'"',"'",'>','/>');
        $this->tokens[STATE_XMLTOKEN_DOUBLEQUOTE]        = array("\n",'"');
        $this->tokens[STATE_XMLTOKEN_SINGLEQUOTE]        = array("\n","'");
/**************************************************************/

        $this->pushEvent[PHPDOC_XMLTOKEN_EVENT_NOEVENTS] = 
            array(
                '<' => PHPDOC_XMLTOKEN_EVENT_OPENTAG,
                '<?' => PHPDOC_XMLTOKEN_EVENT_PI,
                '<?xml' => PHPDOC_XMLTOKEN_EVENT_XML,
                '</' => PHPDOC_XMLTOKEN_EVENT_CLOSETAG,
//                '&' => PHPDOC_XMLTOKEN_EVENT_ENTITY,
                '<![cdata[' => PHPDOC_XMLTOKEN_EVENT_IN_CDATA,
                '<!--' => PHPDOC_XMLTOKEN_EVENT_COMMENT,
                '<!' => PHPDOC_XMLTOKEN_EVENT_DEF,
            );
/**************************************************************/

        $this->pushEvent[PHPDOC_XMLTOKEN_EVENT_OPENTAG] = 
            array(
                " " => PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE,
                "\n" => PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE,
            );
/**************************************************************/

        $this->pushEvent[PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE] = 
            array(
                "'" => PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE,
                '"' => PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE,
            );
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_IN_CDATA] = array(']]>');
/**************************************************************/

        $this->pushEvent[PHPDOC_XMLTOKEN_EVENT_CDATA] =
            array(
                '<' => PHPDOC_XMLTOKEN_EVENT_OPENTAG,
                '<?' => PHPDOC_XMLTOKEN_EVENT_PI,
//                '&' => PHPDOC_XMLTOKEN_EVENT_ENTITY,
                '<!--' => PHPDOC_XMLTOKEN_EVENT_COMMENT,
                '<!' => PHPDOC_XMLTOKEN_EVENT_DEF,
                '<![cdata[' => PHPDOC_XMLTOKEN_EVENT_IN_CDATA,
            );
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_XML] =
        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_PI] = array('?>');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_ENTITY] = array(';');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE] = array("'");
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE] = array('"');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_OPENTAG] = array('>', '/>');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_CLOSETAG] = array('>');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_COMMENT] = array('-->');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_DEF] = array('>',']>');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE] = array('>','/>');
/**************************************************************/

        $this->popEvent[PHPDOC_XMLTOKEN_EVENT_CDATA] = 
            array('</');
/**************************************************************/
    }

    function getParserEventName ($value)
    {    
        $lookup = array(
            PHPDOC_XMLTOKEN_EVENT_NOEVENTS         => "PHPDOC_XMLTOKEN_EVENT_NOEVENTS",
            PHPDOC_XMLTOKEN_EVENT_PI         => "PHPDOC_XMLTOKEN_EVENT_PI",
            PHPDOC_XMLTOKEN_EVENT_OPENTAG         => "PHPDOC_XMLTOKEN_EVENT_OPENTAG",
            PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE         => "PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE",
            PHPDOC_XMLTOKEN_EVENT_CLOSETAG         => "PHPDOC_XMLTOKEN_EVENT_CLOSETAG",
            PHPDOC_XMLTOKEN_EVENT_ENTITY         => "PHPDOC_XMLTOKEN_EVENT_ENTITY",
            PHPDOC_XMLTOKEN_EVENT_COMMENT         => "PHPDOC_XMLTOKEN_EVENT_COMMENT",
            PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE         => "PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE",
            PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE         => "PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE",
            PHPDOC_XMLTOKEN_EVENT_CDATA => 'PHPDOC_XMLTOKEN_EVENT_CDATA',
            PHPDOC_XMLTOKEN_EVENT_DEF => 'PHPDOC_XMLTOKEN_EVENT_DEF',
            PHPDOC_XMLTOKEN_EVENT_XML => 'PHPDOC_XMLTOKEN_EVENT_XML',
            PHPDOC_XMLTOKEN_EVENT_IN_CDATA => 'PHPDOC_XMLTOKEN_EVENT_IN_CDATA',
        );
        if (isset($lookup[$value]))
        return $lookup[$value];
        else return $value;
    }
}


/** starting state */
define("PHPDOC_XMLTOKEN_EVENT_NOEVENTS"    ,    1);
/** currently in starting state */
define("STATE_XMLTOKEN_NOEVENTS"    ,    101);

/** used when a processor instruction is found */
define("PHPDOC_XMLTOKEN_EVENT_PI"    ,    2);
/** currently in processor instruction */
define("STATE_XMLTOKEN_PI"    ,    102);

/** used when an open <tag> is found */
define("PHPDOC_XMLTOKEN_EVENT_OPENTAG"    ,    3);
/** currently parsing an open <tag> */
define("STATE_XMLTOKEN_OPENTAG"    ,    103);

/** used when a <tag attr="attribute"> is found */
define("PHPDOC_XMLTOKEN_EVENT_ATTRIBUTE"    ,    4);
/** currently parsing an open <tag> */
define("STATE_XMLTOKEN_ATTRIBUTE"    ,    104);

/** used when a close </tag> is found */
define("PHPDOC_XMLTOKEN_EVENT_CLOSETAG"    ,    5);
/** currently parsing a close </tag> */
define("STATE_XMLTOKEN_CLOSETAG"    ,    105);

/** used when an &entity; is found */
define("PHPDOC_XMLTOKEN_EVENT_ENTITY"    ,    6);
/** currently parsing an &entity; */
define("STATE_XMLTOKEN_ENTITY"    ,    106);

/** used when a <!-- comment --> is found */
define("PHPDOC_XMLTOKEN_EVENT_COMMENT"    ,    7);
/** currently parsing a <!-- comment --> */
define("STATE_XMLTOKEN_COMMENT"    ,    107);

/** used when a <!-- comment --> is found */
define("PHPDOC_XMLTOKEN_EVENT_SINGLEQUOTE"    ,    8);
/** currently parsing a <!-- comment --> */
define("STATE_XMLTOKEN_SINGLEQUOTE"    ,    108);

/** used when a <!-- comment --> is found */
define("PHPDOC_XMLTOKEN_EVENT_DOUBLEQUOTE"    ,    9);
/** currently parsing a <!-- comment --> */
define("STATE_XMLTOKEN_DOUBLEQUOTE"    ,    109);

/** used when a <! is found */
define("PHPDOC_XMLTOKEN_EVENT_DEF"    ,    10);
/** currently parsing a <! */
define("STATE_XMLTOKEN_DEF"    ,    110);

/** used when a <! is found */
define("PHPDOC_XMLTOKEN_EVENT_CDATA"    ,    11);
/** currently parsing a <! */
define("STATE_XMLTOKEN_CDATA"    ,    111);

/** used when a <?xml is found */
define("PHPDOC_XMLTOKEN_EVENT_XML"    ,    12);
/** currently parsing a <?xml */
define("STATE_XMLTOKEN_XML"    ,    112);

/** used when a <![CDATA[ section is found */
define('PHPDOC_XMLTOKEN_EVENT_IN_CDATA', 13);
/** currently parsing a <![CDATA[ ]]> */
define('STATE_XMLTOKEN_IN_CDATA', 113);

/** do not remove, needed in plain renderer */
define('PHPDOC_BEAUTIFIER_CDATA', 100000);
?>
