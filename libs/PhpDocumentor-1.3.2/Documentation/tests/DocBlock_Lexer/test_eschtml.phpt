--TEST--
/** test <<br>> escaped tags */
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** test <<br>> stuff
 * <<br />>
 * more <<<code>>> <</code>>*/');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test <br> stuff'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' <br />'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' more <<code>> </code>'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done