--TEST--
XHPRrof: Sampling Mode Test
Author: kannan
--FILE--
<?php

include_once dirname(__FILE__).'/common.php';

function foo() {
   // sleep 0.8 seconds
   usleep(800000);
}

function bar() {
   foo();
}

function goo() {
    bar();
}

// call goo() once
xhprof_sample_enable();
goo();
$output1 = xhprof_sample_disable();


// call goo() twice
xhprof_sample_enable();
goo();
goo();
$output2 = xhprof_sample_disable();

// how many usleep samples did we get in single call to goo()?
$count1 = 0;
foreach  ($output1 as $sample) {
  if ($sample == "main()==>goo==>bar==>foo==>usleep") {
    $count1++;
  }
}

// how many usleep samples did we get in two calls to goo()?
$count2 = 0;
foreach  ($output2 as $sample) {
  if ($sample == "main()==>goo==>bar==>foo==>usleep") {
    $count2++;
  }
}

//
// our default sampling frequency is 0.1 seconds. So
// we would expect about 8 samples (given that foo()
// sleeps for 0.8 seconds). However, we might in future 
// allow the sampling frequency to be modified. So rather
// than depend on the absolute number of samples, we'll
// check to see if $count2 is roughly double of $count1.
//

if (($count1 == 0)
    || (($count2 / $count1) > 2.5)
    || (($count2 / $count1) < 1.5)) {
  echo "Test failed\n";
  echo "Count of usleep samples in one call to goo(): $count1\n";
  echo "Count of usleep samples in two calls to goo(): $count2\n";
  echo "Samples in one call to goo(): \n";
  var_dump($output1);
  echo "Samples in two calls to goo(): \n";
  var_dump($output2);
} else {
  echo "Test passed\n";
}

?>
--EXPECT--
Test passed
