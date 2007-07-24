--TEST--
/** */
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** */');
$phpt->assertEquals(array(), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done