<?php
/**
 * Sample File 2, phpDocumentor Quickstart
 * 
 * This file demonstrates the rich information that can be included in
 * in-code documentation through DocBlocks and tags.
 * @author Greg Beaver <cellog@php.net>
 * @version 1.0
 * @package sample
 */
// sample file #1
/**
 * Dummy include value, to demonstrate the parsing power of phpDocumentor
 */
include_once 'sample3.php';

/**
 * Special global variable declaration DocBlock
 * @global integer $GLOBALS['_myvar']
 * @name $_myvar
 */ 
$GLOBALS['_myvar'] = 6;

/**#@+
 * Constants
 */
/**
 * first constant
 */
define('testing', 6);
/**
 * second constant
 */
define('anotherconstant', strlen('hello'));

/**
 * A sample function docblock
 * @global string document the fact that this function uses $_myvar
 * @staticvar integer $staticvar this is actually what is returned
 * @param string $param1 name to declare
 * @param string $param2 value of the name
 * @return integer
 */
function firstFunc($param1, $param2 = 'optional')
{
    static $staticvar = 7;
    global $_myvar;
    return $staticvar;
}

/**
 * The first example class, this is in the same package as the
 * procedural stuff in the start of the file
 * @package sample
 * @subpackage classes
 */
class myclass {
    /**
     * A sample private variable, this can be hidden with the --parseprivate
     * option
     * @access private
     * @var integer|string
     */
    var $firstvar = 6;
    /**
     * @link http://www.example.com Example link
     * @see myclass()
     * @uses testing, anotherconstant
     * @var array
     */
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

    /**
     * Constructor sets up {@link $firstvar}
     */
    function myclass()
    {
        $this->firstvar = 7;
    }
    
    /**
     * Return a thingie based on $paramie
     * @param boolean $paramie
     * @return integer|babyclass
     */
    function parentfunc($paramie)
    {
        if ($paramie) {
            return 6;
        } else {
            return new babyclass;
        }
    }
}

/**
 * @package sample1
 */
class babyclass extends myclass {
    /**
     * The answer to Life, the Universe and Everything
     * @var integer
     */
    var $secondvar = 42;
    /**
     * Configuration values
     * @var array
     */
    var $thirdvar;
    
    /**
     * Calls parent constructor, then increments {@link $firstvar}
     */
    function babyclass()
    {
        parent::myclass();
        $this->firstvar++;
    }
    
    /**
     * This always returns a myclass
     * @param ignored $paramie
     * @return myclass
     */
    function parentfunc($paramie)
    {
        return new myclass;
    }
}
?>