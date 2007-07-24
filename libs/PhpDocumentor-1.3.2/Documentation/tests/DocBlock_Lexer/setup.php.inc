<?php
require_once 'PhpDocumentor/phpDocumentor/DocBlock/Lexer.inc';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
$phpt = new PEAR_PHPTest(true);
$lexer = new PhpDocumentor_DocBlock_Lexer;
?>