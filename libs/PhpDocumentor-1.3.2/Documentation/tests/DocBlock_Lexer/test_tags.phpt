--TEST--
/** @tags test */
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
 * hi {@complex multi-line
 * @faketag tag}
 * @realtag
 * @anothertag with desc
 * @multiline tag
 *  with lots of stuff to process {@link and inline tag}
 * @singleline with {@inlinetag}
 */');
$phpt->assertEquals(array(
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' hi '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@complex'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' multi-line'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' @faketag tag'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_TAG, '@realtag'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_TAG, '@anothertag'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' with desc'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_TAG, '@multiline'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' tag'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, '  with lots of stuff to process '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@link'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' and inline tag'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
    array(PHPDOC_DOCBLOCK_TOKEN_TAG, '@singleline'),
    array(PHPDOC_DOCBLOCK_TOKEN_DESC, ' with '),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAG, '{@inlinetag'),
    array(PHPDOC_DOCBLOCK_TOKEN_INLINETAGCLOSE, '}'),
    array(PHPDOC_DOCBLOCK_TOKEN_NEWLINE, "\n"),
), $result, 'result');
echo 'test done';
?>
--EXPECT--
test done