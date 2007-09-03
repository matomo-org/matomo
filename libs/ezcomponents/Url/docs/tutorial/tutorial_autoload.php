<?php
$dir = dirname( __FILE__ );
$dirParts = explode( '/', $dir );
switch ( $dirParts[count( $dirParts ) - 3] )
{
    case 'doc': require_once 'ezc/Base/base.php'; break; // pear
    case 'trunk': require_once "$dir/../../../Base/src/base.php"; break; // svn
    default: require_once "$dir/../../../Base/src/base.php"; break; // bundle
}

/**
 * Autoload ezc classes 
 * 
 * @param string $className 
 */
function __autoload( $className )
{
    ezcBase::autoload( $className );
}
?>
