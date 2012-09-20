--TEST--
XHPRrof: Test excluding call_user_func and similar functions
Author: mpal
--FILE--
<?php

include_once dirname(__FILE__).'/common.php';

$xhprof_ignored_functions = array( 'ignored_functions' => 
                                      array('call_user_func',
                                            'call_user_func_array',
                                            'my_call_user_func_safe',
                                            'my_call_user_func_array_safe'));
function bar() {
  return 1;
}

function foo($x) {
  $sum = 0;
  for ($idx = 0; $idx < 2; $idx++) {
     $sum += bar();
  }
  echo "hello: {$x}\n" ;
  return strlen("hello: {$x}");
}

function foo_array($x1, $x2 = 'test') {
  $sum = 0;
  $x = array($x1, $x2);
  foreach ($x as $idx) {
     $sum += bar();
  }
  echo "hello: " . $x[0] . $x[1] . "\n";
  return strlen("hello: {$x[0]} {$x[1]}");
}

function my_call_user_func_safe($function, $args = 'my_safe') {
  if (!is_callable($function, true)) {
    throw new Exception('my_call_user_func_safe() invoked without ' .
                        'a valid callable.');
  }

  call_user_func($function, array($args));
}

function my_call_user_func_array_safe($function, $args = array()) {
  if (!is_callable($function, true)) {
    throw new Exception('my_call_user_func_array_safe() invoked without ' .
                        'a valid callable.');
  }

  call_user_func_array($function, $args);
}


class test_call_user_func {
  function test_call_user_func($test_func = 'foo',
                               $arg1      = 'user_func test') {
    call_user_func($test_func, $arg1);
  }
}

function test_call_user_func_array($test_func = 'foo_array',
                                   $arg1      = array(0 => 'user_func_array',
                                                      'test')) {
  call_user_func_array($test_func, $arg1);
}

function test_my_call_user_func_safe($test_func = 'foo',
                                     $arg1      = 'my_user_func_safe test') {
  my_call_user_func_safe($test_func, $arg1);
}

function test_my_call_user_func_array_safe(
                                   $test_func = 'foo_array',
                                   $arg1      = array('my_user_func_array_safe',
                                                      'test')) {
  my_call_user_func_array_safe($test_func, $arg1);
}


// 1: Sanity test a simple profile run
echo "Part 1: Default Flags\n";
xhprof_enable(0, $xhprof_ignored_functions);
foo("this is a test");
$array_arg = array();
$array_arg[] = 'calling ';
$array_arg[] = 'foo_array';
foo_array($array_arg);

$output = xhprof_disable();
echo "Part 1 output:\n";
print_canonical($output);
echo "\n";

// 2a: Sanity test ignoring call_user_func
echo "Part 2a: Ignore call_user_func\n";
xhprof_enable(0, $xhprof_ignored_functions);
$indirect_foo = new test_call_user_func('foo');
$output = xhprof_disable();
echo "Part 2a output:\n";
print_canonical($output);
echo "\n";

// 2b: Confirm that profiling without parameters still works
echo "Part 2b: Standard profile without parameters\n";
xhprof_enable();
$indirect_foo = new test_call_user_func('foo');
$output = xhprof_disable();
echo "Part 2b output:\n";
print_canonical($output);
echo "\n";

// 2c: Confirm that empty array of ignored functions works
echo "Part 2c: Standard profile with empty array of ignored functions\n";
xhprof_enable(0, array());
$indirect_foo = new test_call_user_func('foo');
$output = xhprof_disable();
echo "Part 2c output:\n";
print_canonical($output);
echo "\n";

// 3: Sanity test ignoring call_user_func_array
echo "Part 3: Ignore call_user_func_array\n";
xhprof_enable(XHPROF_FLAGS_CPU, $xhprof_ignored_functions);
test_call_user_func_array('foo_array', $array_arg);
$output = xhprof_disable();
echo "Part 3 output:\n";
print_canonical($output);
echo "\n";

// 4: Sanity test ignoring my_call_user_func_safe
echo "Part 4: Ignore my_call_user_func_safe\n";
xhprof_enable(0, $xhprof_ignored_functions);
test_my_call_user_func_safe('foo');
$output = xhprof_disable();
echo "Part 4 output:\n";
print_canonical($output);
echo "\n";

// 5a: Sanity test ignoring my_call_user_func_array_safe and strlen
echo "Part 5a: Ignore my_call_user_func_array_safe and strlen\n";
$tmp1 = $xhprof_ignored_functions['ignored_functions'];
$tmp1[] = 'strlen';
$ignore_strlen_also = array('ignored_functions' => $tmp1);
xhprof_enable(XHPROF_FLAGS_MEMORY, $ignore_strlen_also);
test_my_call_user_func_array_safe('foo_array');
$output = xhprof_disable();
echo "Part 5a output:\n";
print_canonical($output);
echo "\n";

// 5b: Sanity test to not ignore call_user_func variants
echo "Part 5b: Profile call_user_func_array and my_call_user_func_array_safe\n";
xhprof_enable(XHPROF_FLAGS_MEMORY, array());
test_my_call_user_func_array_safe('foo_array');
$output = xhprof_disable();
echo "Part 5b output:\n";
print_canonical($output);
echo "\n";

// 5c: Sanity test to only ignore my_call_user_func_array_safe
echo "Part 5c: Only ignore call_user_func_array\n";
$xhprof_ignored_functions = array('ignored_functions' => 
                                  'my_call_user_func_array_safe');
xhprof_enable(XHPROF_FLAGS_MEMORY, $xhprof_ignored_functions);
test_my_call_user_func_array_safe('foo_array');
$output = xhprof_disable();
echo "Part 5c output:\n";
print_canonical($output);
echo "\n";

?>
--EXPECT--
Part 1: Default Flags
hello: this is a test
hello: Arraytest
Part 1 output:
foo==>bar                               : ct=       2; wt=*;
foo==>strlen                            : ct=       1; wt=*;
foo_array==>bar                         : ct=       2; wt=*;
foo_array==>strlen                      : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>foo                            : ct=       1; wt=*;
main()==>foo_array                      : ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;

Part 2a: Ignore call_user_func
hello: user_func test
Part 2a output:
foo==>bar                               : ct=       2; wt=*;
foo==>strlen                            : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>test_call_user_func::test_call_user_func: ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
test_call_user_func::test_call_user_func==>foo: ct=       1; wt=*;

Part 2b: Standard profile without parameters
hello: user_func test
Part 2b output:
call_user_func==>foo                    : ct=       1; wt=*;
foo==>bar                               : ct=       2; wt=*;
foo==>strlen                            : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>test_call_user_func::test_call_user_func: ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
test_call_user_func::test_call_user_func==>call_user_func: ct=       1; wt=*;

Part 2c: Standard profile with empty array of ignored functions
hello: user_func test
Part 2c output:
call_user_func==>foo                    : ct=       1; wt=*;
foo==>bar                               : ct=       2; wt=*;
foo==>strlen                            : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>test_call_user_func::test_call_user_func: ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
test_call_user_func::test_call_user_func==>call_user_func: ct=       1; wt=*;

Part 3: Ignore call_user_func_array
hello: calling foo_array
Part 3 output:
foo_array==>bar                         : cpu=*; ct=       2; wt=*;
foo_array==>strlen                      : cpu=*; ct=       1; wt=*;
main()                                  : cpu=*; ct=       1; wt=*;
main()==>test_call_user_func_array      : cpu=*; ct=       1; wt=*;
main()==>xhprof_disable                 : cpu=*; ct=       1; wt=*;
test_call_user_func_array==>foo_array   : cpu=*; ct=       1; wt=*;

Part 4: Ignore my_call_user_func_safe
hello: Array
Part 4 output:
foo==>bar                               : ct=       2; wt=*;
foo==>strlen                            : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>test_my_call_user_func_safe    : ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
test_my_call_user_func_safe==>foo       : ct=       1; wt=*;
test_my_call_user_func_safe==>is_callable: ct=       1; wt=*;

Part 5a: Ignore my_call_user_func_array_safe and strlen
hello: my_user_func_array_safetest
Part 5a output:
foo_array==>bar                         : ct=       2; mu=*; pmu=*; wt=*;
main()                                  : ct=       1; mu=*; pmu=*; wt=*;
main()==>test_my_call_user_func_array_safe: ct=       1; mu=*; pmu=*; wt=*;
main()==>xhprof_disable                 : ct=       1; mu=*; pmu=*; wt=*;
test_my_call_user_func_array_safe==>foo_array: ct=       1; mu=*; pmu=*; wt=*;
test_my_call_user_func_array_safe==>is_callable: ct=       1; mu=*; pmu=*; wt=*;

Part 5b: Profile call_user_func_array and my_call_user_func_array_safe
hello: my_user_func_array_safetest
Part 5b output:
call_user_func_array==>foo_array        : ct=       1; mu=*; pmu=*; wt=*;
foo_array==>bar                         : ct=       2; mu=*; pmu=*; wt=*;
foo_array==>strlen                      : ct=       1; mu=*; pmu=*; wt=*;
main()                                  : ct=       1; mu=*; pmu=*; wt=*;
main()==>test_my_call_user_func_array_safe: ct=       1; mu=*; pmu=*; wt=*;
main()==>xhprof_disable                 : ct=       1; mu=*; pmu=*; wt=*;
my_call_user_func_array_safe==>call_user_func_array: ct=       1; mu=*; pmu=*; wt=*;
my_call_user_func_array_safe==>is_callable: ct=       1; mu=*; pmu=*; wt=*;
test_my_call_user_func_array_safe==>my_call_user_func_array_safe: ct=       1; mu=*; pmu=*; wt=*;

Part 5c: Only ignore call_user_func_array
hello: my_user_func_array_safetest
Part 5c output:
call_user_func_array==>foo_array        : ct=       1; mu=*; pmu=*; wt=*;
foo_array==>bar                         : ct=       2; mu=*; pmu=*; wt=*;
foo_array==>strlen                      : ct=       1; mu=*; pmu=*; wt=*;
main()                                  : ct=       1; mu=*; pmu=*; wt=*;
main()==>test_my_call_user_func_array_safe: ct=       1; mu=*; pmu=*; wt=*;
main()==>xhprof_disable                 : ct=       1; mu=*; pmu=*; wt=*;
test_my_call_user_func_array_safe==>call_user_func_array: ct=       1; mu=*; pmu=*; wt=*;
test_my_call_user_func_array_safe==>is_callable: ct=       1; mu=*; pmu=*; wt=*;
