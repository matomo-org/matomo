--TEST--
/** test */
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** test */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test ')
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done