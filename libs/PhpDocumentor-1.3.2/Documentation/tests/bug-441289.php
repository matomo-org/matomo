<?php
/** @package tests */
/**
* This is a long comment which
* needs more than one line.
* This is the detailed documentation.
* This is another line of detailed docs
*/ 
function test_441289()
{
}

/** I started my short desc
* on the first line.
* This is the detailed documentation.
*/
function test_4412892()
{
}

/**
* I am using just the first line as my short desc
* since i didn't use a period anywhere in this desc
*/
function test_4412893()
{
}

/**
* I am using a blank line to end my short desc
*
* I think this looks the nicest when writing your
* code and makes the code more readable
*/
function test_4412894()
{
}

/**
* This is a test to see if the desc is fooled by 2.4
* the short desc ends here.
* This is the detailed documentation.
*/
function test_4412895()
{
}

/**
* This desc 
* tries to fool 
* the non period 
* short desc systems
*
* junk
* should only show the first line since we dont' look that far
* down for the short desc line break
*/
function test6()
{
}

/**
* This desc tries to fool 
* the non period short desc systems
*
* junk
* junk
*/
function test7()
{
}

/**
* This is a test case where i think things break
* extended desc
* many lines of desc
* many lines of desc
* many lines of desc
* many lines of desc
* many lines of desc
* many lines of desc
* many lines of desc
* many lines of desc
* more extended desc that ends with a period.
* without a line limiter everything til period. would be part of the short desc
* allowing this many lines to be part of the short desc seemed like a problem to me
* so now using both the period and line break as a seperator you can only have a 4
* line extended desc.  the first line is usually blank since its the /** line
*/
function test8()
{
}

/**
* This example is really tricky
*
* The short desc should end on the blank line even though this line ends with a period.
* Here is the long desc
*/
function test_bug_567455()
{
}

/**
* This example is also really tricky
*

* The tries to break the parser test.
* Here is the long desc
*/
function test_bug_567455_2()
{
}
?>
