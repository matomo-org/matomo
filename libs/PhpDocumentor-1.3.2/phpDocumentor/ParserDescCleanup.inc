<?php
/**
 * All of the functions to clean up and handle the long description
 * of a DocBlock are in this file.
 *
 * The primary functionality is based on Parser and WordParser, and modified to recognize
 * only the tokens defined in the PHPDOCUMENTOR_PDP_* constants
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
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        Parser, WordParser
 * @since      1.2
 */

/**#@+
 * {@link parserDescParser} token constants
 */
/** when <<code>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_CODE', 600);
/** when <<code>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_CODE', 700);
/** when <<p>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_P', 601);
/** when <<p>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_P', 701);
/** when \n\n is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_DOUBLECR', 602);
/** when \n\n is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_DOUBLECR', 702);
/** when <<pre>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_PRE', 603);
/** when <<pre>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_PRE', 703);
/** when <<ul>>/<<ol>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_LIST', 604);
/** when <<ul>>/<<ol>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_LIST', 704);
/** when <<b>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_B', 605);
/** when <<b>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_B', 705);
/** when <<i>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_I', 606);
/** when <<i>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_I', 706);
/** when <<br>> is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_BR', 607);
/** when <<br>> is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_BR', 707);
/** when the << potential escape for tags is found in a desc */
define('PHPDOCUMENTOR_PDP_EVENT_ESCAPE',608);
/** when the << potential escape for tags is found in a desc */
define('PHPDOCUMENTOR_PDP_STATE_ESCAPE',708);
/** when << /pre>> is found in a <<pre>><</pre>> section */
define('PHPDOCUMENTOR_PDP_EVENT_ESCAPE_PRE',609);
/** when << /pre>> is found in a <<pre>><</pre>> section */
define('PHPDOCUMENTOR_PDP_STATE_ESCAPE_PRE',709);
/** when << /code>> is found in a <<code>><</code>> section  */
define('PHPDOCUMENTOR_PDP_EVENT_ESCAPE_CODE',610);
/** when << /code>> is found in a <<code>><</code>> section  */
define('PHPDOCUMENTOR_PDP_STATE_ESCAPE_CODE',710);
/** when <<var>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_EVENT_VAR',611);
/** when <<var>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_STATE_VAR',711);
/** when <<samp>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_EVENT_SAMP',612);
/** when <<samp>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_STATE_SAMP',712);
/** when <<kbd>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_EVENT_KBD',613);
/** when <<kbd>> is found in a desc  */
define('PHPDOCUMENTOR_PDP_STATE_KBD',713);
/** when a simple list is found in a desc
 *
 * like
 * <pre>
 *  o item 1
 *  o item 2
 * </pre>
 */
define('PHPDOCUMENTOR_PDP_EVENT_SIMLIST',614);
/** when a simple list is found in a desc
 *
 * like
 * <pre>
 *  o item 1
 *  o item 2
 * </pre>
 */
define('PHPDOCUMENTOR_PDP_STATE_SIMLIST',714);
/**#@-*/
/**
* Like WordParser but designed to handle an array with strings and
* {@link parserInlineTag}s
* @package phpDocumentor
* @subpackage WordParsers
* @author Greg Beaver <cellog@users.sourceforge.net>
* @since 1.2
*/
class ObjectWordParser extends WordParser
{
    /**
     * Determines whether text searching is case-sensitive or not
     * @access private
     */
    var $_casesensitive = false;
    
    function ObjectWordParser($casesensitive = false)
    {
        $this->_casesensitive = $casesensitive;
    }
    
    /**
     * Set the word parser to go.
     *
     * @param array {@link parserStringWithInlineTags::$value} style-array, with
     *              alternating text and inline tags
     */
    function setup(&$input)
    {
//        if (is_string($input[0])) $input[0] = ltrim($input[0]);
        $this->data = & $input;
        $this->pos = 0;
        $this->linenum = 0;
        $this->linenumpos = 0;
        $this->cache = array();
        reset($this->data);
        list($this->index,) = each($this->data);
        if (!is_object($this->data[$this->index]))
        $this->size = strlen($this->data[$this->index]);
        else $this->size = 0;
        //$this->run = 0;
        //$this->word = WORD_PARSER_RET_WORD;
    }
    
    function getWord()
    {
        if (!isset($this->data[$this->index])) return false;
        // return any inline tags unchanged
        if (is_object($this->data[$this->index]))
        {
            $index = $this->index;
            list($this->index,) = each($this->data);
            $this->pos = 0;
            if ($this->index)
            {
                if (!is_object($this->data[$this->index]))
                $this->size = strlen($this->data[$this->index]);
                else $this->size = 0;
                $this->cache = array();
                return $this->data[$index];
            } else
            {
                return false;
            }
        }
        //$st = $this->mtime();
        if ($this->size == $this->pos)
        {
            // cycle to next line in the array
            list($this->index,) = each($this->data);
            if (!$this->index) return false;
            $this->pos = 0;
            if (!is_object($this->data[$this->index]))
            $this->size = strlen($this->data[$this->index]);
            else $this->size = 0;
            $this->cache = array();
            return $this->getWord();
        }

        $npos = $this->size;
        if (is_array($this->wordseperators))
        {
            //$this->wordseperators = array();
            foreach($this->wordseperators as $sep)
            {
                if (isset($this->cache[$sep]))
                $tpos = $this->cache[$sep];
                else
                $tpos = false;
                if ($tpos < $this->pos || !is_int($tpos))
                {
                    if ($this->_casesensitive)
                        $tpos = strpos($this->data[$this->index],$sep,$this->pos);
                    else
                        $tpos = strpos(strtolower($this->data[$this->index]),$sep,$this->pos);
                }
            
                if ( ($tpos < $npos) && !($tpos === false))
                {
                    //echo trim($sep) . "=$tpos\n";
                    $npos = $tpos;
                    $seplen = strlen($sep);
                } 
                  else if (!($tpos === false))
                {
                    $this->cache[$sep] = $tpos;
                }
            }
        } else {
            // its time to cycle
            return "";
        }

        $len = $npos - $this->pos;
        if ($len == 0)
        {
            $len = $seplen;
        }

        //$st3 = $this->mtime();
        $word = substr($this->data[$this->index],$this->pos,$len);
        
        // Change random other os newlines to the unix one
        if ($word == "\r" || $word == "\r\n")
        {
            $word = "\n";
        }
        
        if ($this->linenumpos <= $this->pos)
        {
            $this->linenumpos = $this->pos + $len;
            $this->linenum += count(explode("\n",$word)) - 1;
        }

        if ($this->getsource)
        {
            $this->source .= $word;
        }
        $this->pos = $this->pos + $len;
        //$this->word = WORD_PARSER_RET_SEP;

        // Things like // commenats rely on the newline to find their end so im going to have to return them
        // never return worthless white space /t ' '
        if ($this->returnWhiteSpace == false)
        {
            if (strlen(trim($word)) == 0 && $word != "\n") 
            {
                $word = $this->getWord();
            }
        }
        //$this->time3 = $this->time3 + ($this->mtime() - $st3);
        //$this->time = $this->time + ($this->mtime() - $st);
        return $word;
    }
    
    /**
     * Determine if the next word is an inline tag
     * @return boolean
     */
    function nextIsObjectOrNonNL()
    {
        return (($this->size == $this->pos) && isset($this->data[$this->index + 1])
            && is_object($this->data[$this->index + 1])) ||
               (($this->size > $this->pos) && !in_array($this->data[$this->index]{$this->pos}, array("\n", "\r")));
    }
}

/**
 * Parses a DocBlock description to retrieve abstract representations of
 * <<pre>>,<<code>>,<<p>>,<<ul>>,<<ol>>,<<li>>,<<b>>,<<i>>
 * @tutorial phpDocumentor.howto.pkg#basics.desc
 * @package phpDocumentor
 * @subpackage Parsers
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.2
 */
class parserDescParser extends Parser
{
    /**#@+
     * @access private
     */
    /**
     * @var array
     */
    var $eventHandlers = array(PHPDOCUMENTOR_PDP_EVENT_CODE => 'handleCode',
                               PHPDOCUMENTOR_PDP_EVENT_PRE => 'handlePre',
                               PHPDOCUMENTOR_PDP_EVENT_P => 'handleP',
                               PHPDOCUMENTOR_PDP_EVENT_DOUBLECR => 'handleDoubleCR',
                               PHPDOCUMENTOR_PDP_EVENT_LIST => 'handleList',
                               PHPDOCUMENTOR_PDP_EVENT_B => 'handleB',
                               PHPDOCUMENTOR_PDP_EVENT_I => 'handleI',
                               PHPDOCUMENTOR_PDP_EVENT_VAR => 'handleVar',
                               PHPDOCUMENTOR_PDP_EVENT_KBD => 'handleKbd',
                               PHPDOCUMENTOR_PDP_EVENT_SAMP => 'handleSamp',
                               PHPDOCUMENTOR_PDP_EVENT_BR => 'handleBr',
                               PHPDOCUMENTOR_PDP_EVENT_ESCAPE => 'handleEscape',
                               PHPDOCUMENTOR_PDP_EVENT_ESCAPE_CODE => 'handleEscapeCode',
                               PHPDOCUMENTOR_PDP_EVENT_ESCAPE_PRE => 'handleEscapePre',
                               PHPDOCUMENTOR_PDP_EVENT_SIMLIST => 'handleSimpleList',
                               PARSER_EVENT_NOEVENTS => 'defaultHandler',
                               );
    
    /**
     * @var array
     */
    var $pars = array();
    /**
     * Determines whether parsing of &lt;p&gt; tags will occur, or double CR will
     * be used
     * @var boolean
     */
    var $parse_Ps;
    /**
     * Context stack.
     *
     * Values can be 'normal', or any tag container like 'my_i', 'my_b'.  This
     * is used to determine which tag text or nested tags should be added to
     * @var array
     */
    var $_context = array('normal');
    /**#@-*/
    
    /**
     * sets $wp to be a {@link ObjectWordParser}
     * 
     * $wp is the word parser that retrieves tokens
     */
    function parserDescParser()
    {
        $this->wp = new ObjectWordParser;
    }
    
    /**
     * Parse a long or short description for tags
     *
     * @param array array of strings or {@link parserInlineTag}s
     * @param boolean true if the description is a short description. (only 1 paragraph allowed in short desc)
     * @param string name of the class to instantiate for each paragraph.  parserDesc for desc/sdesc,
     *               parserStringWithInlineTags for tag data
     * @staticvar integer used for recursion limiting if a handler for an event is not found
     */
    function parse (&$parse_data,$sdesc = false,$ind_type = 'parserDesc')
    {
        static $endrecur = 0;
        global $_phpDocumentor_setting;
        if (!is_array($parse_data) || count($parse_data) == 0)
        {
            return false;
        }
        $this->p_vars['indtype'] = $ind_type;
        $this->setupStates($sdesc);
        if (isset($_phpDocumentor_setting['javadocdesc']) && $_phpDocumentor_setting['javadocdesc'] == 'on')
            $this->parse_Ps = true;

        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->p_vars['curpar'] = 0;
        $this->pars = array();
        $this->p_vars['start'] = true;
        $this->p_vars['event_stack'] = new EventStack;

        $this->wp->setup($parse_data,$sdesc);
        $this->wp->setWhitespace(true);
        $this->p_vars['list_count'] = 0;
        if ($sdesc) $this->p_vars['start'] = false;
        do
        {
            if (!isset($this->pars[$this->p_vars['curpar']])) $this->pars[$this->p_vars['curpar']] = new $ind_type;
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
                echo "LAST: |" . htmlentities($this->p_vars['last_word']) . "|\n";
//                echo "INDEX: ".$this->p_vars['curpar']."\n";
                echo "PEVENT: " . $this->getParserEventName($pevent) . "\n";
                echo "LASTPEVENT: " . $this->getParserEventName($this->p_vars['last_pevent']) . "\n";
                echo $this->wp->getPos() . " WORD: |".htmlentities($word)."|\n\n";
                var_dump($this->_context);
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                if ($word !== false) $this->$handle($word, $pevent);
                else
                {
                    if (!count($this->pars[$this->p_vars['curpar']]->value)) unset($this->pars[$this->p_vars['curpar']]);
                }
            } else
            {
                debug('WARNING: possible error, no ParserDescParser handler for event number '.$pevent);
                if ($endrecur++ == 25)
                {
                    die("FATAL ERROR, recursion limit reached");
                }
            }
            if (is_object($word) || trim($word) != '')
            {
                $this->p_vars['start'] = false;
            }
        } while (is_object($word) || !($word === false) && $word != '');
        $context = $this->getContext();
        if ($context != 'normal')
        {
            if ($context == 'list' && $this->p_flags['simplelist'])
            {
                $this->p_vars['lists'][0]->addItem($this->p_vars['list_item'][0]);
                unset($this->p_vars['list_item'][0]);
                $this->setContext('normal');
                $this->addText($this->p_vars['lists'][0]);
            } else addError(PDERROR_UNCLOSED_TAG,str_replace('my_','',$context));
        }
        if ($this->p_vars['list_count'] > 0) addError(PDERROR_UNMATCHED_LIST_TAG);
        if ($sdesc)
        $this->publishEvent(2,$this->pars);
        else
        $this->publishEvent(1,$this->pars);
    }
    /**#@+ @access private */
    /**
     * basic handling
     *
     * This function checks to see if the first thing in
     * a description is the <p> tag.  If so, it will switch
     * into a mode of parsing out paragraphs by <p> instead
     * of a double line-break
     *
     * It also removes extra whitespace
     * @uses doSimpleList()
     */
    function defaultHandler($word, $pevent)
    {
        $context = $this->getContext();
        if ($context != 'normal') $this->setContext('normal');
        if ($this->p_vars['start'] && is_string($word) && strtolower($word) == '<p>')
        {
            $this->parse_Ps = true;
        }
        if (is_string($word) && $this->checkEventPush($word, $pevent)) return;
//        if (!isset($this->parse_Ps) || !$this->parse_Ps)
        {
            if (is_string($word) && is_string($this->p_vars['last_word']) &&
                  ($word == ' ' && $this->p_vars['last_word'] == ' ')) return;
            if ($pevent == PARSER_EVENT_NOEVENTS)
            {
                if ($this->doSimpleList($word)) return;
            }
            $this->addText($word);
        }
    }
    
    /**
     * Retrieve the current top-level tag to add text into
     * @uses $_context
     */
    function getContext()
    {
        array_push($this->_context,$a = array_pop($this->_context));
        return $a;
    }
    
    /**
     * Pop a context off of the context stack
     * @uses $_context
     */
    function dropContext()
    {
        array_pop($this->_context);
        if (count($this->_context) == 0)
        $this->_context = array('normal');
    }
    
    /**
     * @uses $_context
     * @param string context name
     */
    function setContext($context)
    {
        array_push($this->_context,$context);
    }
    
    /**
     * add input as text to the current paragraph or list
     * @param string|parserInlineTag
     */
    function addText($text)
    {
        $context = $this->getContext();
        if ($context == 'list')
        {
//            debug('aded to '.$context);
            if (!is_object($this->p_vars['list_item'][$this->p_vars['list_count']])) {
                addErrorDie(PDERROR_UL_IN_UL);
            }
            $this->p_vars['list_item'][$this->p_vars['list_count']]->add($text);
        } elseif ($context != 'normal')
        {
//            debug('added to '.$context);
            $this->p_vars[$context]->add($text);
        } else
        {
//            debug('added to normal ');
            $indtype = $this->p_vars['indtype'];
            if (!isset($this->pars[$this->p_vars['curpar']]))
                $this->pars[$this->p_vars['curpar']] = new $indtype;
            $this->pars[$this->p_vars['curpar']]->add($text);
        }
    }
    
    /**#@-*/
    /**#@+
     * @access private
     * @param string|parserInlineTag token from the ObjectWordParser
     * @param integer parser event from {@link ParserDescCleanup.inc}
     */
    /**
     * Handles special case where a description needs the text "<tag>" and tag
     * is one of code, b, i, pre, var, or any other valid in-DocBlock html tag.
     *
     * the text <<<code>>> in a DocBlock will parse out as <<code>>, instead
     * of being parsed as markup.
     */
    function handleEscape($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
        if (!in_array($word, $this->tokens[PHPDOCUMENTOR_PDP_STATE_ESCAPE]))
        {
            if ($word == '<')
            {
                $this->addText($word);
                $this->wp->backupPos($word.$word);
            } else
            $this->wp->backupPos($word);
            return;
        }
        $this->addText('<'.str_replace('>>','>',$word));
    }
    
    /**
     * Just like {@link handleEscape}, except the only valid escape is
     * <<</pre>>>
     */
    function handleEscapePre($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
        $this->addText('</pre>');
    }
    
    /**
     * Just like {@link handleEscape}, except the only valid escape is
     * <<</code>>>
     */
    function handleEscapeCode($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
        $this->addText('</code>');
    }
    
    /**
     * Handle "<<br>>"
     * Add a new {@link parserBr}
     * @uses addText()
     */
    function handleBr($word, $pevent)
    {
        if (is_string($word) && $this->checkEventPop($word, $pevent))
        {
            $this->addText(new parserBr);
        }
    }
    
    /**
     * Handles simple lists
     *
     * phpEdit has an ingenious facility to handle simple lists used in a
     * DocBlock like this:
     *
     * - item 1
     * - item 2
     * - item 3
     *
     * The DocBlock is:
     * <pre>
     * * - item 1
     * * - item 2
     * * - item 3
     * </pre>
     * This function converts these simple lists into the parserList class
     * @param boolean true if this is the first list item in the list
     */
    function handleSimpleList($word, $pevent, $start = false)
    {
        if (is_object($word) && $this->p_flags['in_item'])
        {
            $this->p_vars['list_item'][0]->add($word);
            return;
        }
        if (is_string($word) && $this->checkEventPush($word, $pevent))
        {
            $this->p_flags['in_event'] = true;
            return;
        }
        $ltrimword = @substr($word, @strpos($word, ltrim($word)));
        $is_valid = false;
        if (strlen(trim($word)) == 0)
        {
            if ($this->wp->nextIsObjectOrNonNL())
            {
                $is_valid = true;
            }
        }
        if ($word == "\n" && is_string($this->p_vars['last_word'])
            && $this->p_vars['last_word']{strlen($this->p_vars['last_word']) - 1}
                == "\n")
        {
            if ($this->p_flags['in_item'])
            {
                $this->p_vars['lists'][0]->addItem($this->p_vars['list_item'][0]);
                unset($this->p_vars['list_item'][0]);
                $this->setContext('normal');
                $this->p_flags['simplelist'] = false;
                $this->addText($this->p_vars['lists'][0]);
                unset($this->p_vars['lists']);
                unset($this->p_vars['last_list']);
                $this->wp->backuppos($word);
                $this->p_vars['event_stack']->popEvent();
                $this->p_flags['in_item'] = false;
//                debug('end of list 3');
                return;
            } else
            {
                $this->wp->backuppos($word);
                $this->p_vars['event_stack']->popEvent();
                $this->p_flags['in_item'] = false;
//                debug('not a list 2');
                return;
            }
        }
        $start_list = $this->getStartList($word);
        if (substr($ltrimword,0,strlen($start_list)) != $start_list 
             || $this->p_flags['in_event'] || is_object($this->p_vars['last_word']))
        {
            if (((strlen($this->p_vars['whitespace']) + 1) < strlen(substr($word,0,strpos($word, $ltrimword))))
                   || $word == "\n"
                   || $is_valid
                   || $this->p_flags['in_event']
                   || (is_object($this->p_vars['last_word']) && $this->p_flags['in_item']))
            {
                $this->p_vars['list_item'][0]->add($word);
                $this->resetStartList($start_list);
                $this->p_flags['in_event'] = false;
//                debug('middle of list');
            } else
            {
                if ($this->p_flags['in_item'])
                {
                    $this->p_vars['lists'][0]->addItem($this->p_vars['list_item'][0]);
                    unset($this->p_vars['list_item'][0]);
                    $this->setContext('normal');
                    $this->p_flags['simplelist'] = false;
                    $this->addText($this->p_vars['lists'][0]);
                    unset($this->p_vars['lists']);
                    unset($this->p_vars['last_list']);
                    $this->wp->backuppos($word);
                    $this->p_vars['event_stack']->popEvent();
                    $this->p_flags['in_item'] = false;
//                    debug('end of list 1');
                    return;
                } else
                {
                    $this->wp->backuppos($word);
                    $this->p_vars['event_stack']->popEvent();
                    $this->p_flags['in_item'] = false;
//                    debug('not a list');
                    return;
                }
            }
        } else
        {
            if ($this->p_vars['whitespace'] != substr($word,0,strpos($word, $start_list)))
            { // if the whitespace is greater than that preceding the list
              // delimiter, it's a multi-line list item
                $this->setContext('normal');
                $this->p_flags['simplelist'] = false;
                $this->addText($this->p_vars['lists'][0]);
                unset($this->p_vars['lists']);
                $this->wp->backuppos($word);
                $this->p_vars['event_stack']->popEvent();
                unset($this->p_vars['last_list']);
                $this->p_flags['in_item'] = false;
//                debug('end of list 2');
                return;
            } else
            {
                if ($this->p_flags['in_item'])
                {
                    // end of a list item, add it to the list
                    $this->p_vars['lists'][0]->addItem($this->p_vars['list_item'][0]);
                    unset($this->p_vars['list_item'][0]);
                }
//                debug('next list item');
                $this->p_vars['list_item'][0] = new parserStringWithInlineTags;
                $this->p_vars['list_item'][0]->add(ltrim(substr($ltrimword,strlen($start_list))));
                $this->p_flags['in_item'] = true;
            }
        }
    }
    /**#@-*/
    /**
     * Get the next list marker
     *
     * In unordered lists, this will be something like "o", "-"
     *
     * In ordered lists, this will be either the number "3", "5" or "3.", "5."
     * @return string text of the next list marker to look for
     * @param string current word from the parser
     * @access private
     */
    function getStartList($word)
    {
        // unordered, return the first marker found
        if (!$this->p_flags['orderedlist']) return $this->p_vars['start_list'];
        if (isset($this->p_vars['last_list']))
        {
            $this->p_vars['save_list'] = $this->p_vars['last_list'];
            $next = $this->p_vars['last_list'];
            // increment to next list number, convert to string
            if (substr($this->p_vars['start_list'], strlen($this->p_vars['start_list']) - 1) == '.')
                $next = (substr($next, 0, strpos($next,'.')) + 1) . '.';
            else
                $next = ($next + 1) . '';
//                debug("next is '$next'");
            if ($this->p_vars['whitespace'] == substr($word,0,strpos($word, $next)))
                return $this->p_vars['last_list'] = $next;
            // the next number is not in this word, so return but don't save
            return $next;
        } else
        {
            $this->p_vars['last_list'] = $this->p_vars['start_list'];
            return $this->p_vars['start_list'];
        }
    }
    
    /**
     * Set the next list marker to the current list marker
     *
     * In ordered lists, this will ensure that the next number returned is the
     * right number
     * @param string token for next list marker
     * @access private
     */
    function resetStartList($start)
    {
        if (!isset($this->p_vars['save_list'])) return false;
        $this->p_vars['last_list'] = $this->p_vars['save_list'];
    }
    
    /**#@+
     * @access private
     * @param string|parserInlineTag token from the ObjectWordParser
     * @param integer parser event from {@link ParserDescCleanup.inc}
     */
    /**
     * Handles <<ol>>,<<li>>,<<ul>>
     *
     * This allows parsing of lists nested to any level.  Using
     * the lists and list_item temporary variables and using
     * list_count to control nesting, the method creates a {@link parserList}
     * for each <<ol>> or <<ul>> tag, and a
     * standard {@link parserStringWithInlineTags} for all the text, adding
     * in nested lists as if they were inline tags (the conversion interface
     * is the same for both object types)
     */
    function handleList($word, $pevent)
    {
        if (is_string($word) && $this->checkEventPush($word, $pevent))
        {
            return;
        }
        $ordered = false;
        if (!is_object($this->p_vars['last_word']) && strtolower($this->p_vars['last_word']) == '<ol>')
        {
            // ordered list
            $ordered = true;
        }
        // start a new list
        if (!is_object($this->p_vars['last_word']) && (strtolower($this->p_vars['last_word']) == '<ol>' || strtolower($this->p_vars['last_word']) == '<ul>'))
        {
            $this->p_flags['in_item'] = false;
            $this->setContext('list');
            $this->p_vars['lists'][++$this->p_vars['list_count']] = new parserList($ordered);
        }
        if (!is_object($word) && strtolower($word) == '<li>')
        {
            if ($this->p_flags['in_item'])
            {
                // end of a list item (no end tag), add it to the list
                $this->p_vars['lists'][$this->p_vars['list_count']]->addItem($this->p_vars['list_item'][$this->p_vars['list_count']]);
                unset($this->p_vars['list_item'][$this->p_vars['list_count']]);
            }
            // start a new list item
            $this->p_vars['list_item'][$this->p_vars['list_count']] = new parserStringWithInlineTags;
            $this->p_flags['in_item'] = true;
        } else
        {
            if (is_object($word) || (strtolower($word) != '</li>'))
            {
                if (is_object($word) || (strtolower($word) != '</ul>' && strtolower($word) != '</ol>'))
                {
                    // item text
                    if (isset($this->p_vars['list_item'][$this->p_vars['list_count']]))
                    {
                        if (is_string($word) && $word == ' ' &&
                              $this->p_vars['last_word'] == ' ') return;
                        $this->p_vars['list_item'][$this->p_vars['list_count']]->add($word);
                    }
                } else
                {
                    if ($this->p_flags['in_item'])
                    {
                        // end the current list item before ending a list
                        $this->p_vars['lists'][$this->p_vars['list_count']]->addItem($this->p_vars['list_item'][$this->p_vars['list_count']]);
                        unset($this->p_vars['list_item'][$this->p_vars['list_count']]);
                        $this->p_flags['in_item'] = false;
                    }
                    if (is_string($word) && $this->checkEventPop($word, $pevent))
                    {
                        if ($this->p_vars['list_count'] > 1)
                        {
                            // this is a sublist, add it to the list item of the parent list
                            if (!isset($this->p_vars['list_item'][$this->p_vars['list_count'] - 1])) {
                                addErrorDie(PDERROR_UL_IN_UL);
                            }
                            $this->p_vars['list_item'][$this->p_vars['list_count'] - 1]->add($this->p_vars['lists'][$this->p_vars['list_count']]);
                            // remove the sublist item and sublist, drop to parent list
                            unset($this->p_vars['lists'][$this->p_vars['list_count']]);
                            unset($this->p_vars['lists'][$this->p_vars['list_count']]);
                            $this->p_vars['list_count']--;
                            $this->p_flags['in_item'] = true;
                        } else
                        {
                            // this is a primary list and it has concluded
                            $this->pars[$this->p_vars['curpar']]->add($this->p_vars['lists'][$this->p_vars['list_count']]);
                            unset($this->p_vars['lists']);
                            unset($this->p_vars['list_item']);
                            $this->p_vars['list_count'] = 0;
                            $this->dropContext();
                        }
                    }
                }
            } else
            {
                // check to make sure our list item is not unclosed
                if (!$this->p_flags['in_item'])
                {
                    addError(PDERROR_TEXT_OUTSIDE_LI);
                } else
                {
                    // end of a list item, add it to the list
                    $this->p_vars['lists'][$this->p_vars['list_count']]->addItem($this->p_vars['list_item'][$this->p_vars['list_count']]);
                    unset($this->p_vars['list_item'][$this->p_vars['list_count']]);
                    $this->p_flags['in_item'] = false;
                }
            }
        }
    }

    /**
     * Handles <<code>><</code>> blocks
     */
    function handleCode($word, $pevent)
    {
        if (!isset($this->p_vars['my_code']))
        {
            $this->setContext('my_code');
            $this->p_vars['my_code'] = new parserCode;
        }
        if (is_string($word) && $this->checkEventPush($word, $pevent)) return;
        if (is_object($word) || strtolower($word) != '</code>') $this->p_vars['my_code']->add($word);
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_code']);
                unset($this->p_vars['my_code']);
            }
        }
    }
    
    /**
     * Handles <<pre>><</pre>> blocks
     */
    function handlePre($word, $pevent)
    {
        if (!isset($this->p_vars['my_pre']))
        {
            $this->setContext('my_pre');
            $this->p_vars['my_pre'] = new parserPre;
        }
        if (is_string($word) && $this->checkEventPush($word, $pevent)) return;
        if (is_object($word) || strtolower($word) != '</pre>') $this->p_vars['my_pre']->add($word);
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_pre']);
                unset($this->p_vars['my_pre']);
            }
        }
    }
    
    /**
     * Handles <<b>><</b>> blocks
     */
    function handleB($word, $pevent)
    {
        if (!isset($this->p_vars['my_b']))
        {
            $this->setContext('my_b');
            $this->p_vars['my_b'] = new parserB;
        }
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_b']);
                unset($this->p_vars['my_b']);
            } else
            {
                $this->p_vars['my_b']->add($word);
            }
        } else $this->p_vars['my_b']->add($word);
    }
    
    /**
     * Handles <<i>><</i>> blocks
     */
    function handleI($word, $pevent)
    {
        if (!isset($this->p_vars['my_i']))
        {
            $this->p_vars['my_i'] = new parserI;
            $this->setContext('my_i');
        }
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_i']);
                unset($this->p_vars['my_i']);
            } else
            {
                $this->p_vars['my_i']->add($word);
            }
        } else $this->p_vars['my_i']->add($word);
    }
    
    /**
     * Handles <<var>><</var>> blocks
     */
    function handleVar($word, $pevent)
    {
        if (!isset($this->p_vars['my_var']))
        {
            $this->setContext('my_var');
            $this->p_vars['my_var'] = new parserDescVar;
        }
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_var']);
                unset($this->p_vars['my_var']);
            } else
            {
                $this->p_vars['my_var']->add($word);
            }
        } else $this->p_vars['my_var']->add($word);
    }
    
    /**
     * Handles <<samp>><</samp>> blocks
     */
    function handleSamp($word, $pevent)
    {
        if (!isset($this->p_vars['my_samp']))
        {
            $this->setContext('my_samp');
            $this->p_vars['my_samp'] = new parserSamp;
        }
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_samp']);
                unset($this->p_vars['my_samp']);
            } else
            {
                $this->p_vars['my_samp']->add($word);
            }
        } else $this->p_vars['my_samp']->add($word);
    }
    
    /**
     * Handles <<kbd>><</kbd>> blocks
     */
    function handleKbd($word, $pevent)
    {
        if (!isset($this->p_vars['my_kbd']))
        {
            $this->setContext('my_kbd');
            $this->p_vars['my_kbd'] = new parserKbd;
        }
        if (is_string($word))
        {
            if ($this->checkEventPop($word,$pevent))
            {
                $this->dropContext();
                $this->addText($this->p_vars['my_kbd']);
                unset($this->p_vars['my_kbd']);
            } else
            {
                $this->p_vars['my_kbd']->add($word);
            }
        } else $this->p_vars['my_kbd']->add($word);
    }
    
    /**
     * Handles <<p>><</p>> blocks
     *
     * Note that the only time <<p>> will be interpreted as delimiting a
     * paragraph is if it is the first thing in the description.
     */
    function handleP($word, $pevent)
    {
        if (!isset($this->parse_Ps)) $this->parse_Ps = false;
        if (is_string($word))
        {
            if (is_string($word) && $this->checkEventPush($word, $pevent)) return;
        }
        if (!$this->parse_Ps)
        {
            $this->p_vars['event_stack']->popEvent();
            if (!is_object($word) && strtolower($this->p_vars['last_word']) == '<p>') $this->addText('<p>');
            $this->addText($word);
            return;
        }
        if (is_string($word) && $word == "\n") $word = " ";
        if (is_string($word))
        {
            if ($this->checkEventPop($word, $pevent))
            {
                $this->p_vars['curpar']++;
                return;
            }
            // if no closing tag, pretend there was one
            if (!is_object($word) && strtolower($word) == '<p>' && $this->parse_Ps)
            {
                $this->p_vars['curpar']++;
                return;
            }
        }
        if ($this->p_vars['start'])
        {
            $this->addText($word);
        } else
        {// if the <p> is not at the beginning of the desc, then it is not
         // possible to parse into paragraphs using this tag
            if ($word === ' ' && $this->p_vars['last_word'] === ' ') return;
            $this->addText($word);
        }
    }
    
    /**
     * Handles \n\n as a paragraph marker
     * @uses doSimpleList()
     */
    function handleDoubleCR($word, $pevent)
    {
        $this->p_vars['event_stack']->popEvent();
        if ($word == "\n")
        {
            // only use this if <p> isn't being used
            if ((!isset($this->parse_Ps) || !$this->parse_Ps))
            {
                if ($this->p_vars['last_word'] == "\n")
                {
                    $this->p_vars['curpar']++;
                    $this->parse_Ps = false;
                } else
                {
                    if (is_string($word) && !$this->checkEventPush($word, $pevent))
                    {
                        if ($word == ' ' && $this->p_vars['last_word'] == ' ') return;
                        $this->addText($word);
                    }
                }
            } else
            {
                if (is_string($word) && !$this->checkEventPush($word, $pevent))
                {
                    if ($word == ' ' && $this->p_vars['last_word'] == ' ') return;
                    $this->addText($word);
                }
            }
        } else
        {
            if ($this->p_vars['last_word'] == "\n")
            {
                if ((!isset($this->parse_Ps) || !$this->parse_Ps))
                {
                    $this->addText(' ');
                }
            }
            if (is_string($word) && !($e = $this->checkEventPush($word, $pevent)))
            {
                if ($word == ' ' && $this->p_vars['last_word'] == ' ') return;
                if ($this->doSimpleList($word)) return;
                $this->addText($word);
            }
        }
    }
    
    /**#@-*/
    /**
     * Return a simple list, if found
     *
     * This helper function extracts a simple list beginning with any of
     * 'o','-'.'#','+','0','1','0.','1.' and starts parsing it.
     * @param string line that may contain a simple list
     * @return boolean true if a list is found, false otherwise
     */
    function doSimpleList($word)
    {
        if ($this->p_flags['in_event']) return true;
        if (is_object($word)) return false;
        $ltrimword = ltrim($word);
        if ((strlen($ltrimword) != strlen($word))
             && strlen($ltrimword) > 1
             && ((in_array($ltrimword{0},array('o','-','1','0','#','+')) && $ltrimword{1} == ' '))
                 || ((strlen($ltrimword) >= 2) && (substr($ltrimword,0,2) === '1.' || substr($ltrimword,0,2) === '0.') && $ltrimword{2} == ' '))
        {
            // save the whitespace for comparison
            $this->p_vars['whitespace'] = substr($word,0,strlen($word) - strlen($ltrimword));
            $this->p_vars['start_list'] = $ltrimword{0};
            if ($this->p_vars['start_list'] != '1' && $this->p_vars['start_list'] != '1.' &&
                $this->p_vars['start_list'] != '0' && $this->p_vars['start_list'] != '0.')
            {
                $this->p_flags['orderedlist'] = false;
            } else
            {
                if (substr($ltrimword,0,2) == '1.')
                {
                    $this->p_vars['start_list'] = '1.';
                }
                $this->p_flags['orderedlist'] = true;
            }
            $this->p_vars['event_stack']->pushEvent(PHPDOCUMENTOR_PDP_EVENT_SIMLIST);
            $this->setContext('list');
            $this->p_flags['simplelist'] = true;
            $this->p_vars['lists'][0] = new parserList($this->p_flags['orderedlist']);
            $this->p_vars['list_count'] = 0;
            $this->handleSimpleList($word, PHPDOCUMENTOR_PDP_EVENT_SIMLIST, true);
            return true;
        }
        return false;
    }
    /**
    * setup the parser tokens, and the pushEvent/popEvent arrays
    * @see $tokens, $pushEvent, $popEvent
    * @param boolean determines whether to allow paragraph parsing
    * @global boolean used to determine whether to slow things down or not by
    * eliminating whitespace from comments
    */
    
    function setupStates($sdesc)
    {
        $this->p_flags['in_item'] = false;
        $this->p_flags['in_event'] = false;
        $this->p_flags['simplelist'] = false;
        $this->_context = array('normal');
        $this->tokens[STATE_NOEVENTS]            = array("\n", "<code>", "<pre>", "<ol>", "<ul>", 
                                                         "<b>", "<i>", '<var>', '<kbd>', '<samp>', "<br", '<<');
        if (!$sdesc)
        {
            $this->tokens[STATE_NOEVENTS][] = "<p>";
            $this->tokens[STATE_NOEVENTS][] = "</p>";
        }
        if (PHPDOCUMENTOR_KILL_WHITESPACE) $this->tokens[STATE_NOEVENTS][] = ' ';
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_P]        = array("</p>","<code>","<pre>","\n","<ol>","<ul>","<b>","<i>","<br","<p>", '<<',
                                                                '<var>', '<kbd>', '<samp>');
        if (PHPDOCUMENTOR_KILL_WHITESPACE) $this->tokens[PHPDOCUMENTOR_PDP_STATE_P][] = ' ';
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_CODE]        = array("</code>", '<</code>>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_PRE]        = array("</pre>", '<</pre>>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_LIST]        = array("<ul>","<ol>","</ul>","</ol>","<li>","</li>","<b>","<i>","<br", '<<',"<code>","<pre>","<br",
                                                                   '<var>', '<kbd>', '<samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_DOUBLECR]        = array("\n","<ol>","<ul>","<code>","<pre>","<b>","<i>","<br","<p>","</p>",
                                                                       '<var>', '<kbd>', '<samp>', '<<');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_SIMLIST]      = array("\n",'<var>', '<kbd>', '<samp>','<b>','<i>', '<pre>', '<code>',
                                                                    '<br', '<<');

        $this->tokens[PHPDOCUMENTOR_PDP_STATE_B]    = array("<code>","\n","<pre>","<ol>","<ul>","</b>","<i>","<br", '<<',
                                                            '<var>', '<kbd>', '<samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_KBD]    = array("<code>","\n","<pre>","<ol>","<ul>","<b>","<i>","<br", '<<',
                                                            '<var>', '</kbd>', '<samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_VAR]    = array("<code>","\n","<pre>","<ol>","<ul>","<b>","<i>","<br", '<<',
                                                            '</var>', '<kbd>', '<samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_SAMP]    = array("<code>","\n","<pre>","<ol>","<ul>","<b>","<i>","<br", '<<',
                                                            '<var>', '<kbd>', '</samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_I]    = array("<code>","\n","<pre>","<ol>","<ul>","<b>","</i>","<br", '<<',
                                                            '<var>', '<kbd>', '<samp>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_BR]    = array(">","/>");
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_ESCAPE]    = array('code>>', '/code>>', 'pre>>', '/pre>>', 'b>>', '/b>>', 
                                                                 'i>>', '/i>>', 'ol>>', '/ol>>', 'ul>>', '/ul>>', 'li>>', '/li>>', 
                                                                 'br>>', 'br />>', 'p>>', '/p>>', 'samp>>', '/samp>>', 
                                                                 'kbd>>', '/kbd>>', 'var>>', '/var>>'); 
        if (PHPDOCUMENTOR_KILL_WHITESPACE) $this->tokens[PHPDOCUMENTOR_PDP_STATE_DOUBLECR][] = ' ';

        // For each event word to event mapings
        $this->pushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                "<code>"    => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<pre>"    => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<p>" => PHPDOCUMENTOR_PDP_EVENT_P,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<b>" => PHPDOCUMENTOR_PDP_EVENT_B,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                "\n" => PHPDOCUMENTOR_PDP_EVENT_DOUBLECR,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_CODE] =
            array(
                '<</code>>' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE_CODE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_CODE] = array("</code>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_PRE] =
            array(
                '<</pre>>' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE_PRE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_PRE] = array("</pre>");
##########################
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_BR] = array(">","/>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_P] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<b>" => PHPDOCUMENTOR_PDP_EVENT_B,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_P] = array("</p>");
##########################
        
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_LIST] =
            array(
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<b>" => PHPDOCUMENTOR_PDP_EVENT_B,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
        
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_LIST] = array("</ul>","</ol>");
##########################

        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_SIMLIST] = 
            array(
                "<code>"    => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<pre>"    => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<p>" => PHPDOCUMENTOR_PDP_EVENT_P,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<b>" => PHPDOCUMENTOR_PDP_EVENT_B,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
##########################
        
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_DOUBLECR] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<b>" => PHPDOCUMENTOR_PDP_EVENT_B,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                "<p>" => PHPDOCUMENTOR_PDP_EVENT_P,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
        
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_B] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<i>' => PHPDOCUMENTOR_PDP_EVENT_I,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_B] = array("</b>");

##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_I] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<b>' => PHPDOCUMENTOR_PDP_EVENT_B,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_I] = array("</i>");

##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_VAR] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<b>' => PHPDOCUMENTOR_PDP_EVENT_B,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_VAR] = array("</var>");

##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_SAMP] =
            array(
                "<code>" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre>" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<kbd>" => PHPDOCUMENTOR_PDP_EVENT_KBD,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<b>' => PHPDOCUMENTOR_PDP_EVENT_B,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_SAMP] = array("</samp>");

##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_KBD] =
            array(
                "<code" => PHPDOCUMENTOR_PDP_EVENT_CODE,
                "<ol>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<ul>" => PHPDOCUMENTOR_PDP_EVENT_LIST,
                "<pre" => PHPDOCUMENTOR_PDP_EVENT_PRE,
                "<var>" => PHPDOCUMENTOR_PDP_EVENT_VAR,
                "<samp>" => PHPDOCUMENTOR_PDP_EVENT_SAMP,
                "<i>" => PHPDOCUMENTOR_PDP_EVENT_I,
                "<br" => PHPDOCUMENTOR_PDP_EVENT_BR,
                '<b>' => PHPDOCUMENTOR_PDP_EVENT_B,
                '<<' => PHPDOCUMENTOR_PDP_EVENT_ESCAPE,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_KBD] = array("</kbd>");
    }
    
    function getParserEventName ($value)
    {    
        $lookup = array(
            PARSER_EVENT_NOEVENTS         => "PARSER_EVENT_NOEVENTS",
            PHPDOCUMENTOR_PDP_EVENT_CODE        => "PHPDOCUMENTOR_PDP_EVENT_CODE",
            PHPDOCUMENTOR_PDP_EVENT_P        => "PHPDOCUMENTOR_PDP_EVENT_P",
            PHPDOCUMENTOR_PDP_EVENT_B        => "PHPDOCUMENTOR_PDP_EVENT_B",
            PHPDOCUMENTOR_PDP_EVENT_I        => "PHPDOCUMENTOR_PDP_EVENT_I",
            PHPDOCUMENTOR_PDP_EVENT_BR        => "PHPDOCUMENTOR_PDP_EVENT_BR",
            PHPDOCUMENTOR_PDP_EVENT_VAR        => "PHPDOCUMENTOR_PDP_EVENT_VAR",
            PHPDOCUMENTOR_PDP_EVENT_SAMP        => "PHPDOCUMENTOR_PDP_EVENT_SAMP",
            PHPDOCUMENTOR_PDP_EVENT_KBD        => "PHPDOCUMENTOR_PDP_EVENT_KBD",
            PHPDOCUMENTOR_PDP_EVENT_ESCAPE        => "PHPDOCUMENTOR_PDP_EVENT_ESCAPE",
            PHPDOCUMENTOR_PDP_EVENT_ESCAPE_CODE        => "PHPDOCUMENTOR_PDP_EVENT_ESCAPE_CODE",
            PHPDOCUMENTOR_PDP_EVENT_ESCAPE_PRE        => "PHPDOCUMENTOR_PDP_EVENT_ESCAPE_PRE",
            PHPDOCUMENTOR_PDP_EVENT_DOUBLECR        => "PHPDOCUMENTOR_PDP_EVENT_DOUBLECR",
            PHPDOCUMENTOR_PDP_EVENT_LIST    => "PHPDOCUMENTOR_PDP_EVENT_LIST",
            PHPDOCUMENTOR_PDP_EVENT_PRE => "PHPDOCUMENTOR_PDP_EVENT_PRE",
            PHPDOCUMENTOR_PDP_EVENT_SIMLIST => "PHPDOCUMENTOR_PDP_EVENT_SIMLIST",
        );
        if (isset($lookup[$value]))
        return $lookup[$value];
        else return $value;
    }
}

?>
