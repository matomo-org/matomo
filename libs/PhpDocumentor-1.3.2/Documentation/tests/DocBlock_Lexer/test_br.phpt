--TEST--
/** test <br>*/
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** test <br> stuff
 * <br />
 * more <br>*/');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test '),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'br'),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'br'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' stuff'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'br'),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'br'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' more '),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAG, 'br'),
    array(PHPDOC_DOCBLOCK_TOKEN_HTMLTAGCLOSE, 'br'),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done