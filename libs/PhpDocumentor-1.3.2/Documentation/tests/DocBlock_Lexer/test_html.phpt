--TEST--
/** test normal html <b> <i> etc. */
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** test <b> stuff</b>
 * <i>test
 * multi-line</i> after stuff
 * more
 */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test '),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'b'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' stuff'),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'b'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'i'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, 'test'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' multi-line'),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'i'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' after stuff'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' more'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done