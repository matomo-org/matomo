<?php
/**
 * This class handles the XML-based CezPDF markup language created to allow
 * templates for the PDFdefaultConverter
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
 * @package    Converters
 * @subpackage PDFdefault
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: ParserPDF.inc,v 1.3 2006/04/30 22:18:14 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
/** when <text> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_EVENT_TEXT', 600);
/** when <text> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_STATE_TEXT', 700);
/** used for parsing stuff between <text> */
define('PHPDOCUMENTOR_PDF_EVENT_CONTENT', 601);
/** used for parsing stuff between <text> */
define('PHPDOCUMENTOR_PDF_STATE_CONTENT', 701);
/** when <font> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_EVENT_FONT', 602);
/** when <font> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_STATE_FONT', 702);
/** when <newpage/> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_EVENT_NEWPAGE', 603);
/** when <newpage/> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_STATE_NEWPAGE', 703);
/** when <pdffunction> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION', 604);
/** when <pdffunction> is found in an ezText input */
define('PHPDOCUMENTOR_PDF_STATE_PDFFUNCTION', 704);


/**
 * @package Converters
 * @subpackage PDFdefault
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.2
 */
class PDFParser extends Parser
{
    /**
     * Mapping of event constants to events handler function names
     * @var array
     * @access private
     */
    var $eventHandlers
        = array(
            PHPDOCUMENTOR_PDF_EVENT_TEXT => 'handleText',
            PHPDOCUMENTOR_PDF_EVENT_FONT => 'handleFont',
            PHPDOCUMENTOR_PDF_EVENT_NEWPAGE => 'handleNewPage',
            PARSER_EVENT_QUOTE => 'handleQuote',
            PARSER_EVENT_NOEVENTS => 'defaultHandler',
            PHPDOCUMENTOR_PDF_EVENT_CONTENT => 'handleContent',
            PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION => 'handlePDFfunction',
            );
    
    /**
     * Sets up the wordparser for this class
     */
    function PDFParser()
    {
        $this->wp = new WordParser;
        $this->setupStates();
    }
    /**
     * Parse text for PDFParser XML tags, and add the text to the PDF file
     *
     * @param    string text to parse for PDFParser XML tags
     * @param    string full path to the font directory
     * @param    phpdocpdf
     * @param    boolean determines whether output is saved in a variable or
     *                   added directly to the output
     * @staticvar    integer    used for recursion limiting if a handler for an event is not found
     * @return    bool
     */
    function parse ($parse_data,$fontdir,&$pdf,$debug=false)
    {
        static $endrecur = 0;
        $this->_debug = $debug;

        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->p_vars['event_stack'] = new EventStack;
        $this->p_flags['reset_quote_data'] = true;
        $this->p_vars['options'] = false;
        $this->p_vars['font_dir'] = $fontdir;
        $this->p_vars['text_size'] = false;
        $this->p_vars['pdf'] = &$pdf;

        $this->wp->setup($parse_data);
        $this->wp->setWhitespace(true);

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


            $this->p_vars['last_word'] = $word;
            $word = $this->wp->getWord();

            if (0)//PHPDOCUMENTOR_DEBUG == true)
            {
                echo "----------------\n";
                echo "LAST: |" . $this->p_vars['last_word'] . "|\n";
//                echo "INDEX: ".$this->p_vars['curpar']."\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo "LASTPEVENT: " . $this->getParserEventName($this->p_vars['last_pevent']) . "\n";
                echo $this->wp->getPos() . " WORD: |".$word."|\n\n";
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                $this->$handle($word, $pevent);
            } else
            {
                debug('WARNING: possible error, no ParserPDFParser handler for event number '.$pevent);
                if ($endrecur++ == 25)
                {
                    die("FATAL ERROR, recursion limit reached");
                }
            }
        } while (!($word === false));
        if (false) {
            $fp = fopen("C:/Documents and Settings/Owner/Desktop/pdfsource.txt", "a");
            fwrite($fp, $this->wp->data);
            fclose($fp);
        }
    }
    
    /**#@+
     * Event Handlers
     * @param string token
     * @param integer event constant
     * @access private
     */
    function defaultHandler($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) return;
    }
    
    /**
     * Handles <newpage />
     * @tutorial ParserPDF.cls#tags.newpage
     */
    function handleNewPage($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
        $this->p_vars['pdf']->ezNewPage($this->_debug);
    }
    
    /**
     * Handles <text></text>
     * @tutorial ParserPDF.cls#tags.text
     */
    function handleText($word, $pevent)
    {
        $e = $this->checkEventPush($word, $pevent);
        $e1 = $this->checkEventPop($word, $pevent);
        if ($e == PARSER_EVENT_QUOTE) return;
        if ($e1)
        {
            $this->p_flags['textcolor'] = false;
            if (($a = $this->p_vars['savecolor']) != $this->p_vars['pdf']->getColor())
            {
                $this->p_vars['pdf']->setColor($a['r'],$a['g'],$a['b']);
            }
        }
        if ($this->p_vars['last_word'] == '<text')
        {
            // set up flags
            $this->p_flags['paramval'] = false;
            $this->p_flags['textcolor'] = false;
            $this->p_vars['curparam'] = false;
            $this->p_vars['savecolor'] = $this->p_vars['pdf']->getColor();
            $this->p_vars['options'] = array();
            unset($this->p_vars['quote_data']);
        }
        if (!$this->p_flags['paramval'])
        {
            if ($e || $e1) return;
            if ($word == '=')
            {
//                debug('set paramval '.$this->p_vars['curparam']);
                $this->p_flags['paramval'] = true;
                return;
            }
            $this->p_vars['curparam'] = trim($word);
        } else
        {
            if ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE)
            {
                if ($this->p_vars['curparam'] == 'size')
                {
                    $this->p_vars['text_size'] = (int)$this->p_vars['quote_data'];
                    $this->p_flags['paramval'] = false;
                    $this->p_vars['curparam'] = false;
                    if (!$e && !$e1)
                    {
                        $this->p_vars['curparam'] = trim($word);
                    }
                    unset($this->p_vars['quote_data']);
                } elseif ($this->p_vars['curparam'] == 'color')
                {
                    if ($a = $this->p_vars['pdf']->validHTMLColor($this->p_vars['quote_data']))
                    {
                        $this->p_flags['textcolor'] = true;
                        $this->p_vars['pdf']->setHTMLColor($a);
                    }
                } else
                {
                    if ($this->p_vars['quote_data'] === (string)(int)$this->p_vars['quote_data']) $this->p_vars['quote_data'] = (int)$this->p_vars['quote_data'];
//                    debug('added '.$this->p_vars['curparam'].' = '.$this->p_vars['quote_data']);
                    $this->p_vars['options'][$this->p_vars['curparam']] = $this->p_vars['quote_data'];
                    $this->p_flags['paramval'] = false;
                    $this->p_vars['curparam'] = false;
                    if (!$e && !$e1)
                    {
                        $this->p_vars['curparam'] = trim($word);
                    }
                    unset($this->p_vars['quote_data']);
                }
            }
        }
    }
    
    /**
     * handles <font></font>
     * @tutorial ParserPDF.cls#tags.font
     */
    function handleFont($word, $pevent)
    {
        $e = $this->checkEventPush($word, $pevent);
        $e1 = $this->checkEventPop($word, $pevent);
        if ($e == PARSER_EVENT_QUOTE) return;
        if ($this->p_vars['last_word'] == '<font')
        {
            // set up flags
            $this->p_flags['paramval'] = false;
            $this->p_vars['curparam'] = false;
            unset($this->p_vars['quote_data']);
        }
        if (!$this->p_flags['paramval'])
        {
            if ($e || $e1) return;
            if ($word == '=')
            {
                //debug('set paramval '.$this->p_vars['curparam']);
                $this->p_flags['paramval'] = true;
                return;
            }
            $this->p_vars['curparam'] = trim($word);
        } else
        {
            if ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE)
            {
                if ($this->p_vars['curparam'] == 'face')
                {
                    //debug('set face to '.$this->p_vars['font_dir'] . $this->p_vars['quote_data'] . '.afm');
                    $this->p_vars['pdf']->selectFont($this->p_vars['font_dir'] . $this->p_vars['quote_data'] . '.afm');
                    $this->p_flags['paramval'] = false;
                    $this->p_vars['curparam'] = false;
                    unset($this->p_vars['quote_data']);
                }
            }
        }
    }
    
    /**
     * handles <pdffunction>
     * @tutorial ParserPDF.cls#tags.pdffunction
     */
    function handlePDFFunction($word, $pevent)
    {
        $e = $this->checkEventPush($word, $pevent);
        $e1 = $this->checkEventPop($word, $pevent);
        if ($e == PARSER_EVENT_QUOTE) return;
        if ($this->p_vars['last_word'] == '<pdffunction:')
        {
            // set up flags
            $this->p_flags['paramval'] = $this->p_flags['curparam'] = false;
            $this->p_flags['returnval'] = false;
            $this->p_vars['funcname'] = trim($word);
//            debug("funcname is $word");
            $this->p_vars['options'] = array();
            unset($this->p_vars['quote_data']);
            if ($e1) addErrorDie(PDERROR_PDFFUNCTION_NO_FUNC);
        }
        if (!$this->p_flags['paramval'])
        {
            if ($e1)
            { // call function, no parameters
                $func = $this->p_vars['funcname'];
                if (!$func) addErrorDie(PDERROR_PDFFUNCTION_NO_FUNC);
                if (method_exists($this->p_vars['pdf'],$func))
                {
                    if (count($this->p_vars['options']))
                    {
//                        fancy_debug("calling function $func",$this->p_vars['options']);
                        $a = call_user_func_array(array(&$this->p_vars['pdf'],$func), $this->p_vars['options']);
                    } else
                    {
//                        debug("calling function $func");
                        $a = $this->p_vars['pdf']->$func();
                    }
                    if ($this->p_flags['returnval'])
                    {
//                        debug("setting returnvar ".$this->p_vars['return_varname']);
                        $this->tempvars[$this->p_vars['return_varname']] = $a;
                    }
                } else
                {
                    addWarning(PDERROR_PDF_METHOD_DOESNT_EXIST,$func);
                }
                return;
            }
            if ($e) return;
            if ($word == '=')
            {
//                debug('set paramval '.$this->p_vars['curparam']);
                $this->p_flags['paramval'] = true;
                return;
            }
            $this->p_vars['curparam'] = trim($word);
        } else
        {
            if ($this->p_vars['last_word'] == '=')
            { // check to see if we should use a tempvar from a previous return
                if (substr(trim($word),0,1) == '$')
                {
                    if (substr(trim($word),0,7) == '$this->')
                    { // this is a pdf var
                        $a = substr(trim($word),7);
                        $a = $this->p_vars['pdf']->$a;
    //                    debug("set option to $word");
                        $this->p_vars['options'][] = $a;
                        $this->p_flags['paramval'] = false;
                        unset($this->p_vars['quote_data']);
                    } else
                    { // this is a tempvar
                        if (!isset($this->tempvars[substr(trim($word),1)]))
                        {
                            addErrorDie(PDERROR_PDF_TEMPVAR_DOESNT_EXIST,$this->p_vars['funcname'],trim($word),trim($word));
                        }
                        $a = $this->tempvars[substr(trim($word),1)];
    //                    debug("set option to $word");
                        $this->p_vars['options'][] = $a;
                        $this->p_flags['paramval'] = false;
                        unset($this->p_vars['quote_data']);
                    }
                }
            } else
            {
                if ($this->p_vars['last_pevent'] == PARSER_EVENT_QUOTE)
                {
                    if ($this->p_vars['quote_data'] === (string)(int)$this->p_vars['quote_data'])
                    {
                        $this->p_vars['quote_data'] = (int)$this->p_vars['quote_data'];
                    }
                    if ($this->p_vars['curparam'] == 'return')
                    {
//                        debug("param is return");
                        $this->p_vars['return_varname'] = $this->p_vars['quote_data'];
                        $this->p_flags['returnval'] = true;
                    } else
                    {
//                        fancy_debug("set option to arg",$this->p_vars['quote_data']);
                        $this->p_vars['options'][] = $this->p_vars['quote_data'];
                    }
                    $this->p_flags['paramval'] = false;
                    unset($this->p_vars['quote_data']);
                }
            }
            if ($e1)
            { // call function, with parameters
                $func = $this->p_vars['funcname'];
                if (method_exists($this->p_vars['pdf'],$func))
                {
                    if (count($this->p_vars['options']))
                    {
//                        fancy_debug("calling function $func",$this->p_vars['options']);
                        if ($func == 'ezImage') {
                            // set padding to 5, width to 0, resize to none
                            $this->p_vars['options'][] = 5;
                            $this->p_vars['options'][] = 0;
                            $this->p_vars['options'][] = 'none';
                        }
                        $a = call_user_func_array(array(&$this->p_vars['pdf'],$func), $this->p_vars['options']);
                    } else
                    {
//                        debug("calling function $func");
                        $a = $this->p_vars['pdf']->$func();
                    }
                    if ($this->p_flags['returnval'])
                    {
//                        debug("setting returnvar ".$this->p_vars['return_varname']);
                        $this->tempvars[$this->p_vars['return_varname']] = $a;
                    }
                } else
                {
                    addWarning(PDERROR_PDF_METHOD_DOESNT_EXIST,$func);
                }
            }
        }
    }
    
    /**
     * Adds content to the <text> tag
     */
    function handleContent($word, $pevent)
    {
        if ($e = $this->checkEventPush($word, $pevent))
        {
            if ($e == PHPDOCUMENTOR_PDF_EVENT_FONT)
            { // flush content
                if (!isset($this->p_vars['content'])) return;
                $this->p_vars['pdf']->_ezText($this->p_vars['content'],$this->p_vars['text_size'],$this->p_vars['options']);
                unset($this->p_vars['content']);
            }
            return;
        }
        if ($this->checkEventPop($word, $pevent))
        {
            $this->wp->backupPos($word);
            if (!isset($this->p_vars['content'])) return;
            $this->p_vars['pdf']->_ezText($this->p_vars['content'],$this->p_vars['text_size'],$this->p_vars['options']);
            unset($this->p_vars['content']);
        } else
        {
            if (!isset($this->p_vars['content'])) $this->p_vars['content'] = '';
            if (isset($this->p_vars['quote_data']))
            {
                $this->p_vars['content'] .= $this->p_vars['quote_data'];
                unset($this->p_vars['quote_data']);
            }
            $this->p_vars['content'] .= $word;
        }
    }
    /**#@-*/
    /**
     * setup the parser tokens, and the pushEvent/popEvent arrays
     * @see $tokens, $pushEvent, $popEvent
     */
    
    function setupStates()
    {
        $this->tokens[STATE_NOEVENTS]            = array("<text","<font","<newpage />","<newpage/>",'<pdffunction:','"');
        $this->tokens[PHPDOCUMENTOR_PDF_STATE_TEXT]    = array(">","=",'"',"</text>");
        $this->tokens[PHPDOCUMENTOR_PDF_STATE_FONT] = array("/>","=",'"');
        $this->tokens[PHPDOCUMENTOR_PDF_STATE_CONTENT]    = array("<font",'<pdffunction:',"</text>");
        $this->tokens[PHPDOCUMENTOR_PDF_STATE_PDFFUNCTION]    = array('"',"/>","="," ");
        $this->tokens[STATE_QUOTE]            = array("\\\"","\\\\","\"");
        $this->tokens[STATE_ESCAPE]            = false;// this tells the word parser to just cycle

        // For each event word to event mapings
        $this->pushEvent[PARSER_EVENT_QUOTE] = 
            array(
                "\\"    => PARSER_EVENT_ESCAPE
            );
        $this->popEvent[PARSER_EVENT_QUOTE] = array("\"");
##########################
        $this->pushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                "<text"    => PHPDOCUMENTOR_PDF_EVENT_TEXT,
                "<font"    => PHPDOCUMENTOR_PDF_EVENT_FONT,
                "<newpage />" => PHPDOCUMENTOR_PDF_EVENT_NEWPAGE,
                "<newpage/>" => PHPDOCUMENTOR_PDF_EVENT_NEWPAGE,
                "<pdffunction:" => PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION,
                '"' => PARSER_EVENT_QUOTE,
            );
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDF_EVENT_TEXT] =
            array(
                '"' => PARSER_EVENT_QUOTE,
                '>' => PHPDOCUMENTOR_PDF_EVENT_CONTENT,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDF_EVENT_TEXT] = array("</text>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDF_EVENT_FONT] =
            array(
                '"' => PARSER_EVENT_QUOTE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDF_EVENT_FONT] = array("/>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION] =
            array(
                '"' => PARSER_EVENT_QUOTE,
            );
        
        $this->popEvent[PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION] = array("/>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDF_EVENT_CONTENT] =
            array(
                "<font"    => PHPDOCUMENTOR_PDF_EVENT_FONT,
                "<newpage />" => PHPDOCUMENTOR_PDF_EVENT_NEWPAGE,
                "<newpage/>" => PHPDOCUMENTOR_PDF_EVENT_NEWPAGE,
                "<pdffunction:" => PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION,
            );
        
        $this->popEvent[PHPDOCUMENTOR_PDF_EVENT_CONTENT] = array("</text>");
    }
    
    /**
     * Return the name of the parser event
     * @param integer
     */
    function getParserEventName ($value)
    {
        $lookup = array(
            PARSER_EVENT_NOEVENTS         => "PARSER_EVENT_NOEVENTS",
            PARSER_EVENT_QUOTE        => "PARSER_EVENT_QUOTE",
            PHPDOCUMENTOR_PDF_EVENT_TEXT        => "PHPDOCUMENTOR_PDF_EVENT_TEXT",
            PHPDOCUMENTOR_PDF_EVENT_CONTENT        => "PHPDOCUMENTOR_PDF_EVENT_CONTENT",
            PHPDOCUMENTOR_PDF_EVENT_FONT    => "PHPDOCUMENTOR_PDF_EVENT_FONT",
            PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION    => "PHPDOCUMENTOR_PDF_EVENT_PDFFUNCTION",
        );
        if (isset($lookup[$value]))
        return $lookup[$value];
        else return $value;
    }
}
?>