--TEST--
/** test <code>*/
--SKIPIF--
<?php
if (!@include_once('PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc')) {
    echo 'skip needs PhpDocumentor_DocBlock_Lexer class';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$result = $lexer->lex('/** test <code> stuff</code>
 *
 * <code>
 * // more <pre> {@link test}
 * @include_once "not a tag";
 * </code>
 */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test '),
    array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN, '<code>'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' stuff'),
    array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE, '</code>'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGOPEN, '<code>'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' // more <pre> '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@link'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' test'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' @include_once "not a tag";'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' '),
    array(PHPDOC_DOCBLOCK_TOKEN_ESCTAGCLOSE, '</code>'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done