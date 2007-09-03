<?php
require_once 'tutorial_autoload.php';

// create a new Url object from a string url
$url = new ezcUrl( 'http://www.example.com/mydir/index.php/content/view/article/42/mode/print?user[name]=Bob+Smith&user[age]=47&user[sex]=M' );

// get the query parts
var_dump( $url->getQuery() );

?>
