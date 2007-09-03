<?php
require_once 'tutorial_autoload.php';

// register an url under the alias 'map'
ezcUrlCreator::registerUrl( 'map', '/images/geo?xsize=450&ysize=450&zoom=4' );

// display the the url prepended to map_norway.gif
var_dump( ezcUrlCreator::prependUrl( 'map', 'map_norway.gif' ) );

// display the the url prepended to map_sweden.gif
var_dump( ezcUrlCreator::prependUrl( 'map', 'map_sweden.gif' ) );

// display the stored url under the alias 'map'
var_dump( ezcUrlCreator::getUrl( 'map' ) );

?>
