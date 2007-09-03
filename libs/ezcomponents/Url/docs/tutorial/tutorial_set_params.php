<?php
require_once 'tutorial_autoload.php';

// create an ezcUrlConfiguration object
$urlCfg = new ezcUrlConfiguration();

// set the basedir and script values
$urlCfg->basedir = 'mydir';
$urlCfg->script = 'index.php';

// define delimiters for unordered parameter names
$urlCfg->unorderedDelimiters = array( '(', ')' );

// define ordered parameters
$urlCfg->addOrderedParameter( 'section' );
$urlCfg->addOrderedParameter( 'group' );
$urlCfg->addOrderedParameter( 'category' );
$urlCfg->addOrderedParameter( 'subcategory' );

// define unordered parameters
$urlCfg->addUnorderedParameter( 'game', ezcUrlConfiguration::MULTIPLE_ARGUMENTS );

// create a new ezcUrl object from a string url and use the above $urlCfg
$url = new ezcUrl( 'http://www.example.com/mydir/index.php/groups/Games/Adventure/Adult/(game)/Larry/7', $urlCfg );
var_dump( $url->buildUrl() );

// set the parameter values in the url
$url->setParam( 'subcategory', 'Kids' );
$url->setParam( 'game', array( 'Monkey_Island', '3' ) );
var_dump( $url->buildUrl() );

?>
