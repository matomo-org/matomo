--TEST--
XHPRrof: Test (direct and indirect) recursive function calls.
Author: Kannan
--FILE--
<?php

include_once dirname(__FILE__).'/common.php';

// dummy wrapper to test indirect recursion
function bar($depth, $use_direct_recursion) {
  foo($depth, $use_direct_recursion);
}

function foo($depth, $use_direct_recursion = false) {
  if ($depth > 0) {
    if ($use_direct_recursion)
      foo($depth - 1, $use_direct_recursion);
    else
      bar($depth - 1, $use_direct_recursion);
  }
}


xhprof_enable();
foo(4, true);
$output = xhprof_disable();

echo "Direct Recursion\n";
print_canonical($output);
echo "\n";


xhprof_enable();
foo(4, false);
$output = xhprof_disable();

echo "Indirect Recursion\n";
print_canonical($output);
echo "\n";

?>
--EXPECT--
Direct Recursion
foo==>foo@1                             : ct=       1; wt=*;
foo@1==>foo@2                           : ct=       1; wt=*;
foo@2==>foo@3                           : ct=       1; wt=*;
foo@3==>foo@4                           : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>foo                            : ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;

Indirect Recursion
bar==>foo@1                             : ct=       1; wt=*;
bar@1==>foo@2                           : ct=       1; wt=*;
bar@2==>foo@3                           : ct=       1; wt=*;
bar@3==>foo@4                           : ct=       1; wt=*;
foo==>bar                               : ct=       1; wt=*;
foo@1==>bar@1                           : ct=       1; wt=*;
foo@2==>bar@2                           : ct=       1; wt=*;
foo@3==>bar@3                           : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>foo                            : ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
