<?php
/**
 * a generic lexer
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2000-2006 Joshua Eichorn
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
 * @subpackage WordParsers
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @copyright  2000-2006 Joshua Eichorn
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: WordParser.inc,v 1.3 2006/04/30 22:18:14 cellog Exp $
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      0.1
 */
/**
 * Retrieves tokens from source code for use by the Parser
 * @see Parser
 * @author    Joshua Eichorn <jeichorn@phpdoc.org>
 * @version    $Id: WordParser.inc,v 1.3 2006/04/30 22:18:14 cellog Exp $
 * @package     phpDocumentor
 * @subpackage WordParsers
 */
class WordParser
{
    /*
    New lines around the world
    Macintosh: \r 
        Unix : \n 
    Windows : \r\n 
     */
    
    /**#@+
     * @access private
     */
    /**
     * List of text that separates tokens, used to retrieve tokens
     * @var array
     */
    var $wordseperators = array();
    
    /**
     * Position within input of the cursor pointing to the next text to be
     * retrieved as a token
     * @var integer
     */
    var $pos = 0;

    /**
     * Size of the input source code
     * @var integer
     */
    var $size;

    /**
     * Source code
     * @var string
     */
    var $data;

    var $cache;
    /**
     * Current line number
     * @var integer
     */
    var $linenum = 0;
    /**
     * Position the cursor was at the last time line numbers were counted, used
     * to guarantee that line numbers are incremented
     * @var integer
     */
    var $linenumpos = 0;
    
    /**
     * Used for {@}source} tag, contains currently parsed function source
     * @var string
     */
    var $source = '';
    /**
     * flag, determines whether tokens are added to {@link $source}
     * @var boolean
     */
    var $getsource = false;

    /**
     * If true, then white space is returned as a part of tokens, otherwise
     * tokens are trimmed
     * @var boolean
     */
    var $returnWhiteSpace = false;
    /**#@-*/

    /**
     * Initialize the WordParser
     * @param string source code
     */
    function setup(&$input)
    {
        $this->size = strlen($input);
        $this->data = & $input;
        $this->pos = 0;
        $this->linenum = 0;
        $this->linenumpos = 0;
        $this->cache = array();
        //$this->run = 0;
        //$this->word = WORD_PARSER_RET_WORD;
    }
    
    /**
     * Retrieve source code for the last function/method
     * @return string
     */
    function getSource()
    {
        $source = $this->source;
        $this->source = '';
        $this->getsource = false;
        return $source;
    }
    
    /**
     * Used to tell the WordParser to start retrieving source code
     * @access private
     */
    function retrievesource($word = '')
    {
        $this->source = $word;
        $this->getsource = true;
    }

    /**
     * Retrieve a token from the token list
     *
     * The {@link Parser} class relies upon this method to retrieve the next
     * token.  The {@link $wordseperators} array is a collection of strings
     * that delineate tokens for the current parser state.  $wordseperators
     * is set by the parser with a call to {@link Parser::configWordParser()}
     * every time a new parser state is reached.
     *
     * For example, while parsing the source code for a class, the word
     * <code>var</code> is a token, and <code>global</code> is not,
     * but inside a function, the reverse is true.  The parser state
     * {@link PARSER_STATE_CLASS} has a token list that includes whitespace,
     * code delimiters like ; and {}, and comment/DocBlock indicators
     *
     * If the whitespace option has been turned off using
     * {@link setWhitespace()}, then no whitespace is returned with tokens
     *
     * {@internal
     * In the first segment of the function, the code attempts to find the next
     * token.  A cache is used to speed repetitious tasks.  The $tpos variable
     * is used to hold the position of the next token.  $npos is used to
     * hold the end of the token, and so $npos - $tpos will give the length
     * of the token.  This is used to allow tokens that contain whitespace,
     * should that option be desired.
     *
     * {@link $data} is of course the string containing the PHP code to be
     * parsed, and {@link $pos} is the cursor, or current location within the
     * parsed data.
     * }}
     * @return string|false the next token, an empty string if there are no
     *                      token separators in the $wordseperators array,
     *                      or false if the end of input has been reached
     */
    function getWord()
    {
        //$st = $this->mtime();
        if ($this->size == $this->pos)
        {
            return false;
        }

        // assume, for starting, that the token is from $this->pos to the end
        $npos = $this->size;
        if (is_array($this->wordseperators))
        {
            //$this->wordseperators = array();
            foreach($this->wordseperators as $sep)
            {
                // cache is set if this separator has been tested
                if (isset($this->cache[$sep]))
                $tpos = $this->cache[$sep];
                else
                $tpos = false;
                if ($tpos < $this->pos || !is_int($tpos))
                {
                    // find the position of the next token separator
                    $tpos = strpos($this->data,$sep,$this->pos);
                }

                // was a token separator found that is closer to the current
                // location?
                if ( ($tpos < $npos) && !($tpos === false))
                {
                    //echo trim($sep) . "=$tpos\n";
                    // set the length of the token to be from $this->pos to
                    // the next token separator
                    $npos = $tpos;
                    $seplen = strlen($sep);
                } 
                  else if (!($tpos === false))
                {
                    $this->cache[$sep] = $tpos;
                }
            }
        } else {
            // no token separators, tell the parser to choose a new state
            return "";
        }

        $len = $npos - $this->pos;
        if ($len == 0)
        {
            $len = $seplen;
        }

        //$st3 = $this->mtime();
        $word = substr($this->data,$this->pos,$len);
        
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
     * Returns the current pointer position, or 1 character after the end of the word
     */
    function getPos()
    {
        return $this->pos;
    }

    /**
     * Unused
     *
     * {@source}
     * @param integer starting position
     * @param integer length of block to retrieve
     */
    function getBlock($start,$len)
    {
        return substr($this->data,$start,$len);
    }

    /**
     * @uses $wordseperators
     * @param array array of strings that separate tokens
     */
    function setSeperator(&$seps)
    {
        $this->wordseperators = &$seps;
    }

    /**
     * Set the internal cursor within the source code
     * @param integer
     */
    function setPos($pos)
    {
        $this->pos = $pos;
    }
    
    /**
     * Backup to the previous token so that it can be retrieved again in a new
     * context.
     *
     * Occasionally, a word will be passed to an event handler that should be
     * handled by another event handler.  This method allows that to happen.
     * @param string token to back up to
     */
    function backupPos($word)
    {
        if ($this->getsource) $this->source = substr($this->source,0,strlen($this->source) - 1);
        $this->pos = $this->pos - strlen($word);
    }

    /**
     * set parser to return or strip whitespace
     * @param boolean
     */
    function setWhitespace($val = false)
    {
        $this->returnWhiteSpace = $val;
    }
}
?>
