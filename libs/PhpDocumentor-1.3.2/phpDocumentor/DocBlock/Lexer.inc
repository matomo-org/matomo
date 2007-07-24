<?php
define('PHPDOC_LEXER_DESC', 1);
define('PHPDOC_LEXER_TAGS', 2);
define('PHPDOC_LEXER_ESCTAG', 3);
define('PHPDOC_LEXER_INLINETAG', 4);
define('PHPDOC_LEXER_INTERNAL', 5);
define('PHPDOC_LEXER_INTERNALTAG', 6);
define('PHPDOC_LEXER_SIMPLELIST', 7);
define('PHPDOC_DOCBLOCK_TOKEN_NEWLINE', 1);
define('PHPDOC_DOCBLOCK_TOKEN_DESC', 2);
define('PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN', 3);
define('PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE', 4);
define('PHPDOC_DOCBLOCK_TOKEN_TAG', 5);
define('PHPDOC_DOCBLOCK_TOKEN_INLINETAG', 6);
define('PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE', 7);
define('PHPDOC_DOCBLOCK_TOKEN_INTERNAL', 8);
define('PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE', 9);
define('PHPDOC_DOCBLOCK_TOKEN_HTMLTAG', 10);
define('PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE', 11);
define('PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART', 12);
define('PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND', 13);
define('PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET', 14);
define('PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET', 15);
class PhpDocumentor_DocBlock_Lexer
{
    var $processedline;
    var $tagStack = array();
    var $tokens = array();
    var $simplelist = array();
    var $whitespace = array();
    var $states = array(PHPDOC_LEXER_DESC);
    function tokenName($token)
    {
        $this->tokens = array(
            PHPDOC_DOCBLOCK_TOKEN_NEWLINE => 'PHPDOC_DOCBLOCK_TOKEN_NEWLINE',
            PHPDOC_DOCBLOCK_TOKEN_DESC => 'PHPDOC_DOCBLOCK_TOKEN_DESC',
            PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN => 'PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN',
            PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE => 'PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE',
            PHPDOC_DOCBLOCK_TOKEN_TAG => 'PHPDOC_DOCBLOCK_TOKEN_TAG',
            PHPDOC_DOCBLOCK_TOKEN_INLINETAG => 'PHPDOC_DOCBLOCK_TOKEN_INLINETAG',
            PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE => 'PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE',
            PHPDOC_DOCBLOCK_TOKEN_INTERNAL => 'PHPDOC_DOCBLOCK_TOKEN_INTERNAL',
            PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE => 'PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE',
        );
        if (in_array($token, array_keys($this->tokens))) {
            return $this->tokens[$token];
        }
        return 'UNKNOWN';
    }

    function lex($comment)
    {
        $comment = str_replace(array("\r\n", "\r"), array("\n", "\n"), $comment);
        $comment = explode("\n", $comment);
        $this->tokens = array();
        $state = PHPDOC_LEXER_DESC;
        $this->states = array(PHPDOC_LEXER_DESC);
        $tid = 0;
        $token = '';
        $esctag = false;
        $this->exception = false;
        list($lastline,) = each(array_reverse($comment, true));
        foreach ($comment as $this->linenum => $line) {
            if ($this->exception) {
                $this->tokens = array();
                return false;
            }
            $linestart = true;
            $this->processedline = trim($line);
            if ($this->processedline == '*/') {
                break;
            }
            if (!$this->processedline) {
                continue;
            }
            if (substr($this->processedline, 0, 3) == '/**') {
                $this->processedline = substr($this->processedline, 3);
                if (!$this->processedline) {
                    continue;
                }
                if (trim($this->processedline) == '*/') {
                    $this->endSimpleList();
                    break;
                }
            } else {
                $this->processedline = substr($this->processedline, 1);
            }
            while (true) {
                switch ($state) {
                    case PHPDOC_LEXER_INTERNALTAG :
                        $internalendpos = strpos($this->processedline, '}}');
                    case PHPDOC_LEXER_TAGS :
                        $trimline = trim($this->processedline);
                        if (strlen($trimline) && $trimline{0} == '@') {
                            if (preg_match('/^(@[^\s]+)\s/', $trimline, $matches) ||
                                  preg_match('/^(@[^\s]+)$/', $trimline, $matches)) {
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_TAG,
                                    $matches[1]);
                                $this->processedline = substr($trimline, strpos($trimline, $matches[1]) +
                                    strlen($matches[1]));
                                if (!$this->processedline) {
                                    break 2;
                                }
                                continue 2; // to while(true)
                            }
                        } elseif (preg_match('/^@/', $trimline, $matches)) {
                            // throw exception for invalid tag
                            return $this->throwException('Invalid tag encountered in line number ' .
                                $this->linenum . ': "' . $line . '"', 'tag');
                        } else {
                            $tagpos = strpos($this->processedline, '<');
                            $inlinetagpos = strpos($this->processedline, '{@');
                            if (isset($internalendpos) && $internalendpos !== false) {
                                do {
                                    if ($tagpos !== false && $internalendpos > $tagpos) {
                                        break;
                                    }
                                    if ($inlinetagpos !== false && $internalendpos > $inlinetagpos) {
                                        break;
                                    }
                                    // }} is the next important token
                                    $this->appendDesc(substr($this->processedline, 0,
                                        $internalendpos));
                                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE, '}}');
                                    $this->processedline = substr($this->processedline, $internalendpos
                                        + 2);
                                    array_shift($this->states);
                                    $state = PHPDOC_LEXER_TAGS;
                                    continue 3; // to while(true);
                                } while (false);
                            }
                            if ($tagpos !== false && $inlinetagpos !== false) {
                                if ($tagpos > $inlinetagpos) {
                                    $tagpos = false;
                                } else {
                                    $inlinetagpos = false;
                                }
                            }
                            if ($tagpos !== false) {
                                continue $this->searchForHTMLTag($tagpos,
                                    $state, $esctag, $linestart, $trimline);
                            }
                            if ($inlinetagpos !== false) {
                                $state = $this->searchForInlineTag($inlinetagpos, $comment);
                                $linestart = false;
                                continue 2; // to while (true)
                            }
                            if (strpos($this->processedline, '*/')) {
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_DESC,
                                    substr($this->processedline,
                                    0, strpos($this->processedline, '*/')));
                                return $this->tokens;
                            }
                            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_DESC, $this->processedline);
                        }
                    break;
                    case PHPDOC_LEXER_INTERNAL :
                        $internalendpos = strpos($this->processedline, '}}');
                    case PHPDOC_LEXER_SIMPLELIST :
                        if ($linestart && $state == PHPDOC_LEXER_SIMPLELIST) {
                            if (!$this->processSimpleList()) {
                                while (count($this->simplelist) && !$this->processSimpleList());
                            }
                            if (!count($this->simplelist)) {
                                array_shift($this->states);
                                $state = array_shift($this->states);
                                array_unshift($this->states, $state);
                            }
                        }
                    case PHPDOC_LEXER_DESC :
                        if (strpos($this->processedline, '*/')) {
                            $this->processedline = substr($this->processedline,
                                0, strpos($this->processedline, '*/'));
                        }
                        if (!$this->processedline) {
                            if ($this->linenum != $lastline) {
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n");
                            }
                            continue 3; // to foreach
                        }
                        $trimline = trim($this->processedline);
                        if ($linestart && $state != PHPDOC_LEXER_TAGS && $trimline{0} == '@') {
                            if ($state == PHPDOC_LEXER_INTERNAL) {
                                // throw exception
                                return $this->throwException('Cannot start tags in {@internal}} ' .
                                    ' in line number ' .
                                    $this->linenum . ': "' . $line . '"', 'tag');
                            }
                            $this->states = array(PHPDOC_LEXER_TAGS);
                            $state = PHPDOC_LEXER_TAGS;
                            continue 2; // to while(true)

                        }
                        if ($state != PHPDOC_LEXER_SIMPLELIST && $linestart && strlen($trimline) > 3 &&
                             ($trimline{0} == '-' || $trimline{0} == '*' ||
                              $trimline{0} == '#' || $trimline{0} == 'o' ||
                              $trimline{0} == '1')) {
                            if ($this->searchForSimplelist($trimline)) {
                                $state = PHPDOC_LEXER_SIMPLELIST;
                                $linestart = false;
                                continue 2; // to while(true)
                            }
                        }
                        $tagpos = strpos($this->processedline, '<');
                        $inlinetagpos = strpos($this->processedline, '{@');
                        if (isset($internalendpos) && $internalendpos !== false) {
                            do {
                                if ($tagpos !== false && $internalendpos > $tagpos) {
                                    break;
                                }
                                if ($inlinetagpos !== false && $internalendpos > $inlinetagpos) {
                                    break;
                                }
                                // }} is the next important token
                                $this->appendDesc(substr($this->processedline, 0, $internalendpos));
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE, '}}');
                                $this->processedline = substr($this->processedline, $internalendpos
                                    + 2);
                                array_shift($this->states);
                                $state = PHPDOC_LEXER_DESC;
                                continue 3; // to while(true);
                            } while (false);
                        }
                        if ($tagpos !== false && $inlinetagpos !== false) {
                            if ($tagpos > $inlinetagpos) {
                                $tagpos = false;
                            } else {
                                $inlinetagpos = false;
                            }
                        }
                        if ($tagpos !== false) {
                            continue $this->searchForHTMLTag($tagpos, $state,
                                $esctag, $linestart, $trimline);
                        }
                        if ($inlinetagpos !== false) {
                            $state = $this->searchForInlineTag($inlinetagpos, $comment);
                            $linestart = false;
                            continue 2; // to while (true)
                        }
                        $this->appendDesc($this->processedline);
                    break;
                    case PHPDOC_LEXER_ESCTAG :
                        if (!$this->processedline) {
                            if ($this->linenum != $lastline) {
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n");
                            }
                            continue 3; // to foreach
                        }
                        $endpos = strpos($this->processedline, $esctag);
                        $inlinetagpos = strpos($this->processedline, '{@');
                        if ($endpos !== false && $inlinetagpos !== false) {
                            if ($endpos > $inlinetagpos) {
                                $endpos = false;
                            } else {
                                $inlinetagpos = false;
                            }
                        }
                        if ($endpos !== false) {
                            if (strlen(substr($this->processedline, 0, $endpos))) {
                                $this->appendDesc(substr($this->processedline, 0, $endpos));
                            }
                            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE, $esctag);
                            $this->processedline = substr($this->processedline, $endpos +
                                strlen($esctag));
                            $state = $this->states[0];
                            if (count($this->states) > 1) {
                                array_shift($this->states);
                            }
                            if (!$this->processedline) {
                                break 2;
                            }
                        }
                        if ($inlinetagpos !== false) {
                            array_unshift($this->states, PHPDOC_LEXER_ESCTAG);
                            $state = $this->searchForInlineTag($inlinetagpos, $comment);
                            $linestart = false;
                            continue 2; // to while (true)
                        }
                        $this->appendDesc($this->processedline);
                    break;
                    case PHPDOC_LEXER_INLINETAG :
                        if (!$this->processedline) {
                            if ($this->linenum != $lastline) {
                                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n");
                            }
                            continue 3; // to foreach
                        }
                        $endpos = strpos($this->processedline, '}');
                        if ($endpos !== false) {
                            if (strlen(substr($this->processedline, 0, $endpos))) {
                                $this->appendDesc(substr($this->processedline, 0, $endpos));
                            }
                            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}');
                            $this->processedline = substr($this->processedline, $endpos + 1); // strlen('}')
                            $state = $this->states[0];
                            if (count($this->states) > 1) {
                                array_shift($this->states);
                            }
                            if (!$this->processedline) {
                                break 2;
                            }
                            if ($state != PHPDOC_LEXER_INLINETAG && strlen($this->processedline)) {
                                $linestart = false;
                                continue 2; // to while (true)
                            }
                        }
                        $this->appendDesc($this->processedline);
                    break;
                }
                break;
            }
            if ($this->linenum != $lastline) {
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n");
            }
        }
        return $this->tokens;
    }

    function searchForSimplelist($trimline)
    {
        $whitespace = strpos($this->processedline, $trimline);
        switch ($trimline{0}) {
            case '-' :
            case '*' :
            case '+' :
            case 'o' :
                if (strlen($trimline) < 3 || $trimline{1} != ' ') {
                    return false;
                }
                // unordered lists
                $this->simplelist[] = 'u' . $trimline{0};
                $this->whitespace[] = $whitespace;
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, '');
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET, $trimline{0});
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    $trimline{0}) + 2);
                array_unshift($this->states, PHPDOC_LEXER_SIMPLELIST);
                return true;
            break;
            case '#' :
                if (strlen($trimline) < 3) {
                    return false;
                }
                // ordered lists
                $this->simplelist[] = '#1';
                $this->whitespace[] = $whitespace;
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, '');
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '#');
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    '#') + 2);
                array_unshift($this->states, PHPDOC_LEXER_SIMPLELIST);
                return true;
            case '1' :
                if (strlen($trimline) < 4) {
                    return false;
                }
                if ($trimline{1} == ' ') {
                    $this->simplelist[] = 'O1';
                } elseif ($trimline{1} == '.' && $trimline{2} == ' ') {
                    $this->simplelist[] = 'o1';
                } else {
                    return false;
                }
                $this->whitespace[] = $whitespace;
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, '');
                if ($trimline{1} == '.') {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '1.');
                    $this->processedline = substr($this->processedline, strpos($this->processedline,
                        '1.') + 3);
                } else {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '1');
                    $this->processedline = substr($this->processedline, strpos($this->processedline,
                        '1') + 2);
                }
                array_unshift($this->states, PHPDOC_LEXER_SIMPLELIST);
                return true;
        }
        return false;
    }

    /**
     * @todo process multi-line elements via whitespace
     * @todo process nested simple lists
     */
    function processSimpleList()
    {
        $trimline = trim($this->processedline);
        $index = count($this->simplelist) - 1;
        $whitespace = strpos($this->processedline, $trimline);
        if ($whitespace < $this->whitespace[$index]) {
            while (count($this->whitespace[$index]) && $whitespace < $this->whitespace[$index]) {
                // the end of the current simplelist
                $this->tokens = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
                array_pop($this->simplelist);
                $index--;
            }
            if ($index == -1) {
                return false; // all simple lists concluded
            }
        }
        if ($whitespace > $this->whitespace[$index]) {
            // could be a multi-line element or a new list
            if (!$this->searchForSimplelist($trimline)) {
                // trim off the simple list whitespace if this is multi-line
                $this->processedline = substr($this->processedline, $this->whitespace[$index] + 2);
            }
            return true;
        }
        // implied: whitespace matches exactly so this is either another
        // bullet point or the end of the simple list
        switch ($this->simplelist[$index]{0}) {
            case 'u' :
                if (strlen($trimline) < 3 || $trimline{0} != $this->simplelist[$index]{1} ||
                      $trimline{1} != ' ') {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
                    array_pop($this->simplelist);
                    return false;
                }
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET, $trimline{0});
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    $trimline{0}) + 2);
            break;
            case 'o' :
                if (strlen($trimline) < 4 || ($trimline{0} !=
                      ($this->simplelist[$index]{1} + 1) . '') ||
                      $trimline{1} != '.' || $trimline{2} != ' ') {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
                    array_pop($this->simplelist);
                    return false;
                }
                $this->simplelist[$index] = $this->simplelist[$index]{0} .
                    ($this->simplelist[$index]{1} + 1);
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET,
                    $this->simplelist[$index]{1} . '.');
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    $trimline{0} . '.') + 3);
            break;
            case 'O' :
                if (strlen($trimline) < 3 || $trimline{0} != ($this->simplelist[$index]{1} + 1) . '' ||
                      $trimline{1} != ' ') {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
                    array_pop($this->simplelist);
                    return false;
                }
                $this->simplelist[$index] = $this->simplelist[$index]{0} .
                    ($this->simplelist[$index]{1} + 1);
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET,
                    $this->simplelist[$index]{1});
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    $this->simplelist[$index]{1}) + 2);
            break;
            case '#' :
                if (strlen($trimline) < 3 || $trimline{0} != '#' ||
                      $trimline{1} != ' ') {
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
                    array_pop($this->simplelist);
                    return false;
                }
                $this->simplelist[$index] = $this->simplelist[$index]{0} .
                    ($this->simplelist[$index]{1} + 1);
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET,
                    $this->simplelist[$index]{1});
                $this->processedline = substr($this->processedline, strpos($this->processedline,
                    '#') + 2);
            break;
        }
        return true;
    }

    function searchForHTMLTag($tagpos, &$state, &$esctag, &$linestart,
                              &$trimline)
    {
        if (preg_match('/^<(<\/?[a-zA-Z]+ ?\/?>)>/', substr($this->processedline, $tagpos),
              $matches)) {
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            $this->appendDesc($matches[1]);
            $this->processedline = substr($this->processedline, $tagpos
                + strlen($matches[1])
                + 2);
            return 2;
        } elseif (!count($matches) &&
              preg_match('/^<(<\/?[a-zA-Z]+ ?\/?>)>/', substr($this->processedline, $tagpos + 1),
              $matches)) {
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            $this->appendDesc('<' . $matches[1]);
            $this->processedline = substr($this->processedline, $tagpos
                + strlen($matches[1])
                + 3);
            return 2;
        } elseif (preg_match('#^(br>|br/>|br />)#',
              substr($this->processedline, $tagpos + 1), $matches)) {
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'br');
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'br');
            $this->processedline = substr($this->processedline, $tagpos
                + strlen($matches[1])
                + 1);
            return 2;
        } elseif (preg_match('/^(b>|i>|li>|ol>|ul>|p>|pre>|var>)/',
              substr($this->processedline, $tagpos + 1), $matches)) {
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            $matches = substr($matches[1], 0, strlen($matches[1]) - 1);
            array_push($this->tagStack, $matches);
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, $matches);
            $this->processedline = substr($this->processedline, $tagpos
                + strlen($matches)
                + 2);
            return 2;
        } elseif (preg_match('#^(/b>|/i>|/li>|/ol>|/ul>|/p>|/pre>|/var>)#',
              substr($this->processedline, $tagpos + 1), $matches)) {
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            $matches = substr($matches[1], 1, strlen($matches[1]) - 2);
            $test = array_pop($this->tagStack);
            if ($matches != $test) {
                // throw exception
                $this->throwException('Invalid closing html tag encountered in line number ' .
                    $this->linenum . ': "</' . $matches . '>", expecting "</' . $test . '>"', 'htmltag');
                return 3;
            }
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, $matches);
            $this->processedline = substr($this->processedline, $tagpos
                + strlen($matches)
                + 3);
            return 2;
        } elseif (preg_match('/^(code>|pre>|kbd>|samp>)/',
              substr($this->processedline, $tagpos + 1), $matches)) {
            $esctag = '</' . $matches[0];
            if ($tagpos) {
                $this->appendDesc(substr($this->processedline, 0, $tagpos));
            }
            switch ($matches[0]) {
                case 'code>' :
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN,
                        '<code>');
                break;
                case 'pre>' :
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN,
                        '<pre>');
                break;
                case 'kbd>' :
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN,
                        '<kbd>');
                break;
                case 'samp>' :
                    $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN,
                        '<samp>');
                break;
            }
            $this->processedline = $line = substr($this->processedline, $tagpos
                + strlen($matches[0])
                + 1);
            $state = PHPDOC_LEXER_ESCTAG;
            if (!$this->processedline) {
                return 3; // to foreach
            }
            if (strpos($this->processedline, $esctag) !== false) {
                if (!strpos($this->processedline, $esctag)) {
                    $linestart = false;
                    return 2; // to while (true)
                }
                if (strpos($this->processedline, $esctag)) {
                    $this->appendDesc(substr($this->processedline, 0,
                        strpos($this->processedline, $esctag)));
                }
                $this->processedline = $trimline =
                    substr($this->processedline, strpos($this->processedline, $esctag));
                $linestart = false;
                return 2; // to while(true)
            }
        }
    }

    function searchForInlineTag($inlinetagpos, $comment)
    {
        if ($inlinetagpos) {
            $this->appendDesc(substr($this->processedline, 0, $inlinetagpos));
        }
        if ($inlinetagpos === strpos($this->processedline, '{@internal')) {
            $state = array_shift($this->states);
            array_unshift($this->states, $state);
            if ($state == PHPDOC_LEXER_INTERNAL || $state == PHPDOC_LEXER_INTERNALTAG) {
                // throw exception
                return $this->throwException('cannot nest {@internal}} in line number ' .
                    $this->linenum . ': "' . $line . '"', 'internaltag');
            }
            $basestate = array_pop($this->states);
            array_push($this->states, $basestate);
            if ($basestate == PHPDOC_LEXER_DESC) {
                $newstate = PHPDOC_LEXER_INTERNAL;
            } else {
                $newstate = PHPDOC_LEXER_INTERNALTAG;
            }
            array_unshift($this->states, $newstate);
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_INTERNAL, '{@internal');
            $this->processedline = substr($this->processedline, $inlinetagpos + strlen('{@internal'));
            return $newstate;
        }
        if (!preg_match('/^({@[^}\s]+)\s/', substr($this->processedline, $inlinetagpos), $matches)) {
            if (!preg_match('/^({@[^}\s]+)}/', substr($this->processedline, $inlinetagpos), $matches)) {
                if ($this->hasEndchar($comment, '}')) {
                    $matches = array(substr($this->processedline, $inlinetagpos),
                        substr($this->processedline, $inlinetagpos));
                } else {
                    // throw exception if this does not match
                    $this->throwException('Unclosed inline tag encountered on line number ' .
                        $this->linenum . ': "' . $line . '"', 'tag');
                    return PHPDOC_LEXER_DESC;
                }
            } else {
                // throw exception if this does not match
                $this->throwException('Invalid inline tag encountered in line number ' .
                    $this->linenum . ': "' . $line . '"', 'tag');
                return PHPDOC_LEXER_DESC;
            }
        }
        $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, $matches[1]);
        $this->processedline = substr($this->processedline, $inlinetagpos + strlen($matches[1]));
        return PHPDOC_LEXER_INLINETAG;
    }

    function appendDesc($desc)
    {
        if (!strlen($desc)) {
            return;
        }
        if (count($this->tokens)) {
            $last = array_pop($this->tokens);
            if ($last[0] == PHPDOC_DOCBLOCK_TOKEN_DESC) {
                $last[1] .= $desc;
                $this->tokens[] = $last;
            } else {
                $this->tokens[] = $last;
                $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_DESC, $desc);
            }
        } else {
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_DESC, $desc);
        }
    }

    function hasEndChar($comment, $char)
    {
        $linenum = $this->linenum;
        foreach ($comment as $num => $line) {
            if ($linenum !== false && $num <= $linenum) {
                continue;
            }
            $linenum = false;
            $processedline = trim($line);
            if ($processedline == '*/') {
                break;
            }
            if (!$processedline) {
                continue;
            }
            if (substr($processedline, 0, 3) == '/**') {
                $processedline = substr($processedline, 3);
                if (!$processedline) {
                    continue;
                }
                if (trim($processedline) == '*/') {
                    break;
                }
            } else {
                $processedline = substr($processedline, 1);
            }
            if (strpos($processedline, $char) !== false) {
                return true;
            }
        }
        return false;
    }

    function endSimpleList()
    {
        if (count($this->tagStack)) {
            $this->tokens = array();
            return $this->throwException('Error: unclosed html tags: "' .
                implode(', ', $this->tagStack) . '"', 'simplelist');
        }
        while (count($this->simplelist)) {
            $this->tokens[] = array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, '');
        }
    }

    function throwException($message, $type)
    {
        $this->error = array($message, $this->linenum, $type);
        $this->exception = true;
    }
}
?>