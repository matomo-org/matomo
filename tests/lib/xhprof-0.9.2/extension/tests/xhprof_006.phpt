--TEST--
XHPRrof: Basic Sampling Test
Author: mpal
--FILE--
<?php

include_once dirname(__FILE__).'/common.php';

function individual_strings($limit) {
  $sum  = 0;
  $t1   = microtime(true);
  $half = $limit/2;
  for ($idx = 0; $idx < $limit; $idx++) {
    $query = 'SELECT ';
    if ($idx < $half) {
      $query .= 'first_half';
    } else {
      $query .= 'second_half';
    }
    $query .= ' FROM database';
    $sum += strlen($query);
  }
  $t2   = microtime(true);
  $delta = $t2 - $t1;
  return $delta;
}

function folded_strings($limit) {
  $sum  = 0;
  $t1   = microtime(true);
  $half = $limit/2;
  for ($idx = 0; $idx < $limit; $idx++) {
    if ($idx < $half) {
      $query = 'SELECT first_half FROM database';
    } else {
      $query = 'SELECT second_half FROM database';
    }
    $sum += strlen($query);
  }
  $t2   = microtime(true);
  $delta = $t2 - $t1;
  return $delta;
}

function foo($x) {
  $time_individual = individual_strings($x);
  $time_folded     = folded_strings($x);
  $time_total      = $time_individual + $time_folded;
  $percent_individual = ($time_individual / $time_total) * 100;
  $percent_folded     = ($time_folded / $time_total) * 100;
  echo "Test Case   :  Percent of Total Time\n";
  echo "Individual  :" /* . "     $percent_individual" */ . "\n";
  echo "Folded      :" /* . "     $percent_folded"     */ . "\n";
  return strlen("hello: {$x}");
}

// 1: Sanity test a sampling profile run
echo "Part 1: Sampling Profile\n";
xhprof_sample_enable();
foo(5000);
$output = xhprof_sample_disable();

echo "Part 1: output\n";
echo "In general, sampling output depends upon execution speed.\n";
echo "Currently checking that this runs to completion.\n";
// print_r($output);
echo "\n";

?>
--EXPECT--
Part 1: Sampling Profile
Test Case   :  Percent of Total Time
Individual  :
Folded      :
Part 1: output
In general, sampling output depends upon execution speed.
Currently checking that this runs to completion.

