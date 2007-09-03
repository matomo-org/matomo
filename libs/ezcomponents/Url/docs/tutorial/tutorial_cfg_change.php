<?php
require_once 'tutorial_autoload.php';

// create a default url configuration
$urlCfgDefault = new ezcUrlConfiguration();
$urlCfgDefault->addOrderedParameter( 'section' );

// create a configuration for artists
$urlCfgArtist = new ezcUrlConfiguration();
$urlCfgArtist->addOrderedParameter( 'section' );
$urlCfgArtist->addOrderedParameter( 'artist_name' );

// create a configuration for albums
$urlCfgAlbum = new ezcUrlConfiguration();
$urlCfgAlbum->addOrderedParameter( 'section' );
$urlCfgAlbum->addOrderedParameter( 'artist_name' );
$urlCfgAlbum->addOrderedParameter( 'album_name' );

// create a configuration for music genres
$urlCfgGenre = new ezcUrlConfiguration();
$urlCfgGenre->addOrderedParameter( 'section' );
$urlCfgGenre->addOrderedParameter( 'genre_name' );

$url = new ezcUrl( 'http://mymusicsite.com/showartist/Beatles', $urlCfgDefault );

switch ( $url->getParam( 'section' ) )
{
	case 'showartist':
		$url->applyConfiguration( $urlCfgArtist );
		$artist = $url->getParam( 'artist_name' );
        // do stuff with $artist
        var_dump( $artist );
		break;
	case 'showalbum':
		$url->applyConfiguration( $urlCfgAlbum );
		$artist = $url->getParam( 'artist_name' );
		$album = $url->getParam( 'album_name' );
        // do stuff with $artist and $album
        var_dump( $artist );
        var_dump( $album );
        break;
    case 'showgenre':
		$url->applyConfiguration( $urlCfgGenre );
		$genre = $url->getParam( 'genre_name' );
        // do stuff with $genre
        var_dump( $genre );
        break;
}

?>
