--TEST--
XHPRrof: Timer Tests
Author: Kannan
--FILE--
<?php

//
// Some coarse grained sanity tests for the time just
// to make sure profiler's timer implementation isn't
// way off.
// The test allows for a 25% margin of error.
//

include_once dirname(__FILE__).'/common.php';

// sleep 10000 microsecs (10 millisecs)
function sleep_10000_micro() {
   usleep(10000);
}


// sleep 20000 microsecs (20 millisecs)
function sleep_20000_micro() {
   usleep(20000);
}

// sleep 50000 microsecs (50 millisecs)
function sleep_50000_micro() {
   usleep(50000);
}

function invoke_all() {
  sleep_10000_micro();
  sleep_20000_micro();
  sleep_50000_micro();
}

xhprof_enable();
invoke_all();
$output = xhprof_disable();

// verify output

function verify($expected, $actual, $description) {

  echo "Verifying ${description}...\n";

  // 25% tolerance
  $range_low = ($expected * 0.75);
  $range_high = ($expected * 1.25);

  if (($actual < $range_low) ||
      ($actual > $range_high)) {
     echo "Failed ${description}. Expected: ${expected} microsecs. ".
          "Actual: ${actual} microsecs.\n";
  } else {
     echo "OK: ${description}\n";
  }
  echo "-------------\n";
}

verify(10000,
       $output["sleep_10000_micro==>usleep"]["wt"],
       "sleep_10000_micro");
verify(20000,
       $output["sleep_20000_micro==>usleep"]["wt"],
       "sleep_20000_micro");
verify(50000,
       $output["sleep_50000_micro==>usleep"]["wt"],
       "sleep_50000_micro");

?>
--EXPECT--
Verifying sleep_10000_micro...
OK: sleep_10000_micro
-------------
Verifying sleep_20000_micro...
OK: sleep_20000_micro
-------------
Verifying sleep_50000_micro...
OK: sleep_50000_micro
-------------
