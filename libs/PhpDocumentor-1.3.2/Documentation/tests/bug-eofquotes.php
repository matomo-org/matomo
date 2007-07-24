<?php
/**
*
* @package tests
*/
/** tests */
function test_eofquotes ()
{
}
echo <<< EOF
This shouldn't be parsed
function bob ()
{
}
EOF;

$string = <<< EOF
This shouldn't be parsed
function bob ()
{
}
EOF;
?>
