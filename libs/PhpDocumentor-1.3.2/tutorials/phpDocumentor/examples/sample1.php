<?php
// sample file #1

include_once 'sample2.php';

$GLOBALS['_myvar'] = 6;

define('testing', 6);
define('anotherconstant', strlen('hello'));

function firstFunc($param1, $param2 = 'optional')
{
    static $staticvar = 7;
    global $_myvar;
    return $staticvar;
}

class myclass {
    var $firstvar = 6;
    var $secondvar =
        array(
            'stuff' =>
                array(
                    6,
                    17,
                    'armadillo'
                ),
            testing => anotherconstant
        );

    function myclass()
    {
        $this->firstvar = 7;
    }
    
    function parentfunc($paramie)
    {
        if ($paramie) {
            return 6;
        } else {
            return new babyclass;
        }
    }
}

class babyclass extends myclass {
    var $secondvar = 42;
    var $thirdvar;
    
    function babyclass()
    {
        parent::myclass();
        $this->firstvar++;
    }
    
    function parentfunc($paramie)
    {
        return new myclass;
    }
}
?>