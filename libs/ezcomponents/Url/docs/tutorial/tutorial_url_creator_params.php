<?php
require_once 'tutorial_autoload.php';

// register an url under the alias 'map'
ezcUrlCreator::registerUrl( 'map', '/images/geo/%s?xsize=%d&ysize=%d&zoom=%d' );

// display the stored url under the alias 'map' formatted with parameters
var_dump( ezcUrlCreator::getUrl( 'map', 'map_norway.gif', 450, 450, 4 ) );

// display the stored url under the alias 'map' formatted with other parameters
var_dump( ezcUrlCreator::getUrl( 'map', 'map_sweden.gif', 450, 450, 4 ) );
?>
