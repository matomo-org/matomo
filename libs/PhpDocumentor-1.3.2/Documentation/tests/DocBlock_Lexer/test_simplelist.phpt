--TEST--
/** test {@inline tags}*/
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/**
 * Simple DocBlock with simple lists
 *
 * Here\'s a simple list:
 * - item 1
 * - item 2, this one
 *   is multi-line
 * - item 3
 * end of list.  Next list is ordered
 * 1 ordered item 1
 * 2 ordered item 2
 * end of list.  This is also ordered:
 * 1. ordered item 1
 * 2. ordered item 2
 */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' Simple DocBlock with simple lists'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' Here\'s a simple list:'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, ''),
    array(PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET, '-'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'item 1'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET, '-'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'item 2, this one'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'is multi-line'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_UNORDEREDBULLET, '-'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'item 3'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, ''),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' end of list.  Next list is ordered'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, ''),
    array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '1'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'ordered item 1'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '2'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'ordered item 2'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, ''),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' end of list.  This is also ordered:'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTSTART, ''),
    array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '1.'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'ordered item 1'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_ORDEREDBULLET, '2.'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'ordered item 2'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_SIMPLELISTEND, ''),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done