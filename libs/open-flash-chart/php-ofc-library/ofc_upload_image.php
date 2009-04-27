<?php

//
// In Open Flash Chart -> save_image debug mode, you
// will see the 'echo' text in a new window.
//

/*
 
print_r( $_GET );
print_r( $_POST );
print_r( $_FILES );

print_r( $GLOBALS );
print_r( $GLOBALS["HTTP_RAW_POST_DATA"] );

*/


// default path for the image to be stored //
$default_path = '../tmp-upload-images/';

if (!file_exists($default_path)) mkdir($default_path, 0777, true);

// full path to the saved image including filename //
$destination = $default_path . basename( $_GET[ 'name' ] ); 

echo 'Saving your image to: '. $destination;
// print_r( $_POST );
// print_r( $_SERVER );
// echo $HTTP_RAW_POST_DATA;

//
// POST data is usually string data, but we are passing a RAW .png
// so PHP is a bit confused and $_POST is empty. But it has saved
// the raw bits into $HTTP_RAW_POST_DATA
//

$jfh = fopen($destination, 'w') or die("can't open file");
fwrite($jfh, $HTTP_RAW_POST_DATA);
fclose($jfh);

//
// LOOK:
//
exit();


//
// PHP5:
//


// default path for the image to be stored //
$default_path = 'tmp-upload-images/';

if (!file_exists($default_path)) mkdir($default_path, 0777, true);

// full path to the saved image including filename //
$destination = $default_path . basename( $_FILES[ 'Filedata' ][ 'name' ] ); 

// move the image into the specified directory //
if (move_uploaded_file($_FILES[ 'Filedata' ][ 'tmp_name' ], $destination)) {
    echo "The file " . basename( $_FILES[ 'Filedata' ][ 'name' ] ) . " has been uploaded;";
} else {
    echo "FILE UPLOAD FAILED";
}


?>
