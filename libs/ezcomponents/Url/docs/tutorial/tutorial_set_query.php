<?php
require_once 'tutorial_autoload.php';

// create a new Url object from a string url
$url = new ezcUrl( 'http://www.example.com/mydir/index.php/content/view/article/42/mode/print?user[name]=Bob+Smith&user[age]=47&user[sex]=M' );

// create an array which will be used to set the query part
$query = array( 'user' => array( 'name' => 'Bob Smith',
                                 'age'  => '47',
                                 'sex'  => 'M',
                                 'dob'  => '5/12/1956'),
              );

// set the query part of the Url object
$url->setQuery( $query );
var_dump( rawurldecode( $url ) );

// add a query parameter to the query part
$url->setQuery( array_merge( $url->getQuery(), array( 'sort' => 'desc' ) ) );
var_dump( rawurldecode( $url ) );

// remove a query parameter from the query part
$url->setQuery( array_diff_key( $url->getQuery(), array( 'sort' => null ) ) );
var_dump( rawurldecode( $url ) );

?>
