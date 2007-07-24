<?php
/**
 * @package tests
 */
/**
 * The tokenizer splits up strings that have inline variables
 * and will fool the DEFINE_PARAMS_PARENTHESIS handler here
 */
define('bqnp_tester',"testing this $parser($me thingo\"");
/**
 * The tokenizer splits up strings that have inline variables
 * and will fool the GLOBAL_VALUE handler here
 * @global string $bqnp_tester
 */
$bqnp_tester = "testing this $parser;$me thingo\"";

/**
 * The tokenizer splits up strings that have inline variables
 * and will fool the STATICVAR handler here
 * @staticvar string
 */
function bqnp_testie()
{
    static $test = "testing this $parser;$me thingo\"";
}
?>