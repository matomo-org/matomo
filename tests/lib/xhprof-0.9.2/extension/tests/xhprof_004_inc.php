<?php

// This file is part of xhprof_004.phpt test.

function foo() {
  echo "I am in foo()...\n";
}

// run some top-level code in this file.

// The profiler should mark functions called from here as
// children of the pseudo-function "run_init::<this_file_name>"
// which represents the initialization block of a file.
//

$result1 = explode(" ", "abc def ghi");

$result2 = implode(",", $result1);

echo $result2 . "\n";

foo();




