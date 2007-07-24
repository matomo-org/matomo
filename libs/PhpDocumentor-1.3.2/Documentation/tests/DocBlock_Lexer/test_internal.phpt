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
 * Hello {@there}
 *
 * {@multiword tag here} {@second}
 * {@multiline
 * tag version 1} {@internal @extra fake tag }}
 * hi {@there}{@internal {@complex multi-line
 * @faketag tag}}}
 */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' Hello '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@there'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@multiword'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' tag here'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@second'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@multiline'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' tag version 1'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_INTERNAL, '{@internal'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' @extra fake tag '),
    array(PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE, '}}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' hi '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@there'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_INTERNAL, '{@internal'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@complex'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' multi-line'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' @faketag tag'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_INTERNALCLOSE, '}}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done