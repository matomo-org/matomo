--TEST--
/** test \n*/
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex("/** test \n*/");
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n")
), $result, 'result 1');
$result = $lexer->lex("/** test \r\n*/");
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n")
), $result, 'result');
$result = $lexer->lex("/** test \r*/");
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n")
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done