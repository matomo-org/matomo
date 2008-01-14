<?php
/**
 * Parser for XML DocBook-based phpDocumentor tutorials
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
 * @tutorial   tutorials.pkg
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */
/** when <programlisting> is found */
define('PHPDOCUMENTOR_PDP_EVENT_PROGRAMLISTING', 600);
/** when <programlisting> is found */
define('PHPDOCUMENTOR_PDP_STATE_PROGRAMLISTING', 700);
/** when a DocBook <tag> is found */
define('PHPDOCUMENTOR_PDP_EVENT_TAG', 601);
/** when a DocBook <tag> is found */
define('PHPDOCUMENTOR_PDP_STATE_TAG', 701);
/** when <![CDATA[ ]]> is found */
define('PHPDOCUMENTOR_PDP_EVENT_CDATA', 602);
/** when <![CDATA[ ]]> is found */
define('PHPDOCUMENTOR_PDP_STATE_CDATA', 702);
/** when tag attributes name="value" are found */
define('PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES', 603);
/** when tag attributes name="value" are found */
define('PHPDOCUMENTOR_PDP_STATE_ATTRIBUTES', 703);
/** when tag attributes name="value" are found */
define('PHPDOCUMENTOR_PDP_EVENT_ENTITY', 604);
/** when tag attributes name="value" are found */
define('PHPDOCUMENTOR_PDP_STATE_ENTITY', 704);

/**
 * Used to parse XML DocBook-based tutorials
 * @package phpDocumentor
 * @subpackage Parsers
 * @author Greg Beaver <cellog@users.sourceforge.net>
 * @since 1.2
 */
class XMLPackagePageParser extends Parser
{
    /** @var array */
    var $eventHandlers = array(
                               PHPDOCUMENTOR_PDP_EVENT_TAG => 'handleTag',
                               PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES => 'handleAttributes',
                               PHPDOCUMENTOR_PDP_EVENT_CDATA => 'handleCData',
                               PARSER_EVENT_NOEVENTS => 'defaultHandler',
                               PARSER_EVENT_COMMENTBLOCK => 'ignoreHandler',
                               PARSER_EVENT_OUTPHP => 'ignoreHandler',
                               PARSER_EVENT_QUOTE => 'handleQuote',
                               PHPDOCUMENTOR_PDP_EVENT_ENTITY => 'handleEntity',
                               );
    
    /**
     * @var array
     */
    var $pars = array();
    
    var $refsect1id = false;
    var $refsect2id = false;
    var $refsect3id = false;
    /**
     * @var array the tag stack
     */
    var $context;
    /**#@+ @access private */
    var $_gettoc = false;
    var $_toc = array();
    var $_cursection = 0;
    /**#@-*/
    /**
     * Set up the wordparser
     *
     * {@source}
     * @uses ObjectWordParser
     */
    function XMLPackagePageParser()
    {
        $this->wp = new ObjectWordParser(true);
    }
    /**
     * Parse a new file
     *
     * @param    string    $parse_data
     * @param    array    $tutorial for format, see {@link Io::getTutorials()}
     * @staticvar    integer    used for recursion limiting if a handler for an event is not found
     * @return    bool
     * @uses parserTutorial using {@link Publisher::PublishEvent()}, a new tutorial
     *                      is created from the file parsed, and passed to the
     *                      Intermediate Parser
     */
    function parse ($parse_data, $tutorial)
    {
        $tempparse = new ppageParser;
        $parse_data = $tempparse->parse($parse_data,true,$tutorial['package'],$tutorial['subpackage'],
                                        basename($tutorial['path']),$tutorial['category'], $tutorial['path']);
        unset($tempparse);
        static $endrecur = 0;
        if (!is_array($parse_data) || count($parse_data) == 0)
        {
            return false;
        }
        $this->setupStates();

        // initialize variables so E_ALL error_reporting doesn't complain
        $pevent = 0;
        $word = 0;
        $this->p_vars['start'] = true;
        $this->p_vars['event_stack'] = new EventStack;

        $this->wp->setup($parse_data,false);
        $this->wp->setWhitespace(true);
        $this->context = array();
        if (isset($this->curtag)) unset($this->curtag);

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
                echo $this->wp->getPos() . " WORD: |$word|\n\n";
//                echo '"'.$this->p_vars['quote_data']."\"\n";
            }
            if (isset($this->eventHandlers[$pevent]))
            {
                $handle = $this->eventHandlers[$pevent];
                if ($word !== false) $this->$handle($word, $pevent);
            } else
            {
                debug('WARNING: possible error, no XMLPackagePageParser handler for event number '.$pevent);
                if ($endrecur++ == 25)
                {
                    die("FATAL ERROR, recursion limit reached");
                }
            }
            $this->p_vars['start'] = false;
        } while (!($word === false));
        if (count($this->_toc) && isset($this->p_vars['toc']))
        {
            $a = $this->curtag->getTOC($this->p_vars['toc']);
            $a->setTOC($this->_toc);
            $a->setPath($tutorial['path']);
            $this->curtag->setTOC($this->p_vars['toc'],$a);
        }
        $this->PublishEvent(PHPDOCUMENTOR_EVENT_TUTORIAL,new parserTutorial($this->curtag, $tutorial));
        return $this->curtag;
    }
    
    /**#@+
     * @access private
     * @param string|parserInlineTag token
     * @param integer parser event
     */
    function defaultHandler($word, $pevent)
    {
        if (is_string($word) && $this->checkEventPush($word, $pevent))
        {
            return;
        }
    }
    
    function ignoreHandler($word, $pevent)
    {
        $this->checkEventPop($word, $pevent);
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
        if (!is_object($word)) $this->checkEventPush( $word, $pevent);
        if (is_object($word))
        {
            $this->p_vars['quote_data'] = $word;
        } else
        {
            if ($word != "\"")
            {
                if (!is_object($this->p_vars['quote_data']))
                {
                    $this->p_vars['quote_data'] .= $word;
                }
            }
            if ($word == '>')
            {
                if (is_object($this->p_vars['quote_data']))
                {
                    $this->p_vars['quote_data'] = '{@id '.$this->p_vars['quote_data']->id.'}';
                }
                addErrorDie(PDERROR_UNTERMINATED_ATTRIB,
                    $this->curtag->name,$this->p_vars['attrname'],$this->p_vars['quote_data']);
            }
            if ($this->checkEventPop($word,$pevent))
            {
                $this->p_flags['reset_quote_data'] = true;
            }
        }
    }
    
    /**
     * Handles all XML DocBook tags
     */
    function handleTag($word, $pevent)
    {
        if (isset($this->curtag) && $this->curtag->hasTitle() && $this->_gettoc && $this->_gettoc->name == $this->curtag->name)
        {
            if (isset($this->_toc[$this->_cursection]))
            {
                $this->_toc[$this->_cursection]['title'] = $this->curtag->_title;
                $this->_cursection++;
            }
            $this->_gettoc = false;
        }
        if ($this->p_vars['last_word'] == '<')
        { // get tag name
            $this->p_flags['begin_tag'] = true;
            array_push($this->context,$word);
//            if (isset($this->curtag)) debug("pushed ".$this->curtag->name);
            if (isset($this->curtag))
            {
                array_push($this->pars,$this->curtag);
            }
            $this->curtag = new parserXMLDocBookTag($word);
        } elseif ($this->p_vars['last_word'] == '</' || $word == '/>')
        {
            $tag = array_pop($this->context);
            if ($word == '/>') {
                // all is OK
                $this->checkEventPop($word, $pevent);
                $word = $tag;
            }
            if ($tag != $word)
            {
                addErrorDie(PDERROR_UNMATCHED_TUTORIAL_TAG,$tag,$word,$this->curtag->getString());
            }
            if (in_array($this->curtag->name, array('refentry', 'refsect1', 'refsect2', 'refsect3')))
            {
                if (!isset($this->curtag->_id))
                {
                    $title = '';
                    if (isset($this->curtag->_title))
                    {
                        $title = $this->curtag->_title->getString();
                    }
                    addWarning(PDERROR_NO_DOCBOOK_ID, $this->curtag->name, $title);
                }
            }
            $this->p_flags['begin_tag'] = false;
            $curtag = @array_pop($this->pars);
//            debug("popped $tag ".$curtag->name.' I am '.$this->curtag->name);
            if ($curtag)
            {
                if ($this->curtag->name == 'refsect1') $this->refsect1id = false;
                if ($this->curtag->name == 'refsect2') $this->refsect2id = false;
                if ($this->curtag->name == 'refsect3') $this->refsect3id = false;
                $curtag->add($this->curtag);
//                debug("added ".$this->curtag->name." to ".$curtag->name.' '.$curtag->id);
                $this->curtag = $curtag;
            } else
            {
//                debug("here");
            }
        } elseif (is_string($word))
        {
            if (!($e = $this->checkEventPush($word, $pevent)))
            {
                if ($this->checkEventPop($word, $pevent))
                {
                    if ($this->p_flags['begin_tag'])
                    {
                        $this->p_vars['event_stack']->pushEvent(PHPDOCUMENTOR_PDP_EVENT_TAG);
                        $this->p_vars['event_stack']->pushEvent(PHPDOCUMENTOR_PDP_EVENT_CDATA);
                        $this->p_vars['last_tag'] = array_pop($this->context);
                        array_push($this->context,$this->p_vars['last_tag']);
                        $this->p_flags['in_cdata'] = false;
                    }
                    return;
                }
            } else
            {
                $this->p_flags['start_attr'] = true;
                $this->p_flags['end_attr'] = false;
            }
        } else addErrorDie(PDERROR_CANT_HAVE_INLINE_IN_TAGNAME);
    }
    
    /**
     * Handle CData sections
     */
    function handleCData($word, $pevent)
    {
        if ($this->curtag->name == 'refentry' && phpDocumentor_get_class($word) == 'parsertocinlinetag')
        {
            $this->p_vars['toc'] = $this->curtag->getTOC();
        }
        if (is_string($word) && !$this->p_flags['in_cdata'])
        {
            if ($this->checkEventPop($word, $pevent))
            {
                return;
            }
            if ($this->checkEventPush($word, $pevent))
            {
                return;
            }
        }
        if (is_string($word) && $word == '<![CDATA[')
        {
            $this->curtag->startCData();
            $this->p_flags['in_cdata'] = true;
        } elseif ($this->p_flags['in_cdata'] && is_string($word) && $word == ']]>')
        {
            $this->curtag->endCData();
            $this->p_flags['in_cdata'] = false;
        } else
        {
            if ($this->p_flags['in_cdata'])
            $this->curtag->addCData($word);
            else
            $this->curtag->add($word);
        }
    }
    
    /**
     * Handle Entities like &rdquo;
     */
    function handleEntity($word, $pevent)
    {
        if (!$word)
        {
            if (!isset($this->p_vars['entity_name']))
                $this->p_vars['entity_name'] = '';
            addErrorDie(PDERROR_UNTERMINATED_ENTITY,$this->p_vars['entity_name']);
        }
        $e = $this->checkEventPop($word, $pevent);
        if ($word && !$e) $this->p_vars['entity_name'] = $word;
        if ($e)
        {
            $entity = new parserEntity($this->p_vars['entity_name']);
            unset($this->p_vars['entity_name']);
            $this->curtag->add($entity);
        }
    }
    
    /**
     * Handle Tag attributes name="value"
     */
    function handleAttributes($word, $pevent)
    {
        if ($this->checkEventPush($word, $pevent)) return;
        if ($word == '=')
        {
            $this->p_flags['start_attr'] = false;
            $this->p_vars['end_attr'] = true;
        } else
        {
            if ($this->p_flags['start_attr'])
            {
                $this->p_vars['attrname'] = $word;
            } else
            {
                if (isset($this->p_vars['attrname']))
                {
                    $value = $this->p_vars['quote_data'];
                    if (phpDocumentor_get_class($value) == 'parseridinlinetag')
                    { // "inherit" the parent section's id, so
                      // <refsect1 id="{@id test"}> <!-- id is 'test' -->
                      // ...
                      //  <refsect2 id="{@id me}"> <!-- id is 'test.me' -->
                      //  ...
                      //   <refsect3 id="{@id out}"> <!-- id is 'test.me.out' -->
                      //    <example id="{@id withexample}"> <!-- id is 'test.me.out.withexample' -->
                        $a = ($this->refsect1id ? $this->refsect1id . '.' : '');
                        $a .= ($this->refsect2id ? $this->refsect2id . '.' : '');
                        $a .= ($this->refsect3id ? $this->refsect3id . '.' : '');
                        if ($this->curtag->name == 'refsect1')
                        {
                            $this->refsect1id = $value->id;
                        }
                        if ($this->curtag->name == 'refsect2')
                        {
                            $this->refsect2id = $value->id;
                        }
                        if ($this->curtag->name == 'refsect3')
                        {
                            $this->refsect3id = $value->id;
                        }
//                            debug($value->id.' is now '.$a.$value->id);
                        $value->id = $a . $value->id;
                        if ($value->id != '')
                        {
                            if (isset($this->_toc[$this->_cursection]))
                            {
                                $this->_cursection++;
                            }
                            $this->_toc[$this->_cursection]['id'] = $value;
                            $this->_toc[$this->_cursection]['tag'] = new parserXMLDocBookTag($this->curtag->name);
//                            debug("set gettoc to ".$this->curtag->name .' '. $value->id);
                            $this->_gettoc = $this->curtag;
                        }
                    }
                    $this->curtag->addAttribute($this->p_vars['attrname'],$value);
                    unset($this->p_vars['attrname']);
                    if (is_string($word) && $this->checkEventPop($word, $pevent))
                    {
                        $this->p_flags['start_attr'] = true;
                        $this->p_flags['end_attr'] = false;
                        $this->wp->setPos($this->wp->getPos() - strlen($word));
                    } else {
                        $this->wp->setPos($this->wp->getPos() - strlen($word));
                    }
                    return;
                }
            }
        }
        if (is_string($word) && $this->checkEventPop($word, $pevent))
        {
            $this->p_flags['start_attr'] = true;
            $this->p_flags['end_attr'] = false;
            $this->wp->setPos($this->wp->getPos() - strlen($word));
        }
    }
    /**#@-*/
    
    /**
     * setup the parser tokens, and the pushEvent/popEvent arrays
     * @see $tokens, $pushEvent, $popEvent
     */
    
    function setupStates()
    {
        $this->_gettoc = false;
        $this->_toc = array();
        $this->_cursection = 0;
        if (isset($this->p_vars['toc'])) unset($this->p_vars['toc']);
        
        $this->tokens[STATE_NOEVENTS]                     = array('</','<!--','<!','<?','<');
        $this->tokens[STATE_COMMENTBLOCK]                 = array('-->');
        $this->tokens[STATE_OUTPHP]                       = array('?>','>');
        $this->tokens[STATE_QUOTE]                        = array("\\\"","\\\\","\"",'>');
        $this->tokens[STATE_ESCAPE]                       = false;// this tells the word parser to just cycle
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_TAG]        = array('>',' ','/>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_CDATA]      = array('&','<!--','</','<![CDATA[','<',']]>');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_ATTRIBUTES] = array('=','>','/>','"');
        $this->tokens[PHPDOCUMENTOR_PDP_STATE_ENTITY]     = array(';');

        // For each event word to event mapings
        $this->pushEvent[PARSER_EVENT_NOEVENTS] = 
            array(
                '<!--' => PARSER_EVENT_COMMENTBLOCK,
                '<!' => PARSER_EVENT_OUTPHP,
                "</" => PHPDOCUMENTOR_PDP_EVENT_TAG,
                '<?' => PARSER_EVENT_OUTPHP,
                "<" => PHPDOCUMENTOR_PDP_EVENT_TAG,
                '&' => PHPDOCUMENTOR_PDP_EVENT_ENTITY,
            );
##########################

        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_TAG] =
            array(
                ' ' => PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES,
            );
         
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_TAG] = array(">","/>");
##########################
        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES] =
            array(
                '"' => PARSER_EVENT_QUOTE,
            );

        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES] = array(">","/>");
##########################

        $this->popEvent[PARSER_EVENT_COMMENTBLOCK] = array("-->");
##########################
        $this->pushEvent[PARSER_EVENT_QUOTE] = 
            array(
                "\\"    => PARSER_EVENT_ESCAPE
            );
        $this->popEvent[PARSER_EVENT_QUOTE] = array("\"");
##########################

        $this->popEvent[PARSER_EVENT_OUTPHP] = array("?>",">");
##########################

        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_ENTITY] = array(";");
##########################

        $this->pushEvent[PHPDOCUMENTOR_PDP_EVENT_CDATA] =
            array(
                "<" => PHPDOCUMENTOR_PDP_EVENT_TAG,
                '<!--' => PARSER_EVENT_COMMENTBLOCK,
                '<?' => PARSER_EVENT_OUTPHP,
                '&' => PHPDOCUMENTOR_PDP_EVENT_ENTITY,
            );
        $this->popEvent[PHPDOCUMENTOR_PDP_EVENT_CDATA] = array("</");
    }
    
    /**
     * debugging function
     *
     * {@source}
     * @static
     */
    function getParserEventName ($value)
    {    
        $lookup = array(
            PARSER_EVENT_NOEVENTS         => "PARSER_EVENT_NOEVENTS",
            PHPDOCUMENTOR_PDP_EVENT_TAG        => "PHPDOCUMENTOR_PDP_EVENT_TAG",
            PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES        => "PHPDOCUMENTOR_PDP_EVENT_ATTRIBUTES",
            PHPDOCUMENTOR_PDP_EVENT_CDATA        => "PHPDOCUMENTOR_PDP_EVENT_CDATA",
            PHPDOCUMENTOR_PDP_EVENT_LIST    => "PHPDOCUMENTOR_PDP_EVENT_LIST",
            PARSER_EVENT_QUOTE  => "PARSER_EVENT_QUOTE",
            PHPDOCUMENTOR_PDP_EVENT_ENTITY => "PHPDOCUMENTOR_PDP_EVENT_ENTITY",
            PHPDOCUMENTOR_PDP_EVENT_COMMENT => "PHPDOCUMENTOR_PDP_EVENT_COMMENT",
            PHPDOCUMENTOR_PDP_EVENT_PI => "PHPDOCUMENTOR_PDP_EVENT_PI",
        );
        if (isset($lookup[$value]))
        return $lookup[$value];
        else return $value;
    }
}
?>