--TEST--
XHPRrof: Test Class Methods, Constructors, Destructors.
Author: Kannan
--FILE--
<?php

include_once dirname(__FILE__).'/common.php';

class C {
  private static $_static_attr = "i am a class static";
  private $_attr;
  function __construct($attr) {
    echo "In constructor...\n";
    $this->_attr = $attr;
  }

  private static function inner_static() {
    return C::$_static_attr;
  }

  public static function outer_static() {
    return C::inner_static();
  }

  public function get_attr() {
    return $this->_attr;
  }

  function __destruct() {
    echo "Destroying class {$this->_attr}\n";
  }
}


xhprof_enable();

// static methods
echo C::outer_static() . "\n";

// constructor
$obj = new C("Hello World");

// instance methods
$obj->get_attr();

// destructor
$obj = null;


$output = xhprof_disable();

echo "Profiler data for 'Class' tests:\n";
print_canonical($output);
echo "\n";

?>
--EXPECT--
i am a class static
In constructor...
Destroying class Hello World
Profiler data for 'Class' tests:
C::outer_static==>C::inner_static       : ct=       1; wt=*;
main()                                  : ct=       1; wt=*;
main()==>C::__construct                 : ct=       1; wt=*;
main()==>C::__destruct                  : ct=       1; wt=*;
main()==>C::get_attr                    : ct=       1; wt=*;
main()==>C::outer_static                : ct=       1; wt=*;
main()==>xhprof_disable                 : ct=       1; wt=*;
