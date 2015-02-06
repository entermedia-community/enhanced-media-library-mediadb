<?php 

ini_set('file_uploads', 1);
ini_set('max_file_uploads', '9999999999M');
ini_set('post_max_size', '2000M');
ini_set('max_input_time', 3600);
ini_set('max_execution_time', 3600);

ini_set('html_errors', '1');
ini_set('file_uploads', '1');
ini_set('track_errors', '1');
ini_set('log_errors', '1');
ini_set('display_errors','1');
error_reporting(E_ALL);

echo 'start';

$rootpath = $_SERVER['DOCUMENT_ROOT'];
include_once($rootpath . '/wordpress/wp-config.php');

// Make sure these are populated

//echo '<br>File Package: ' . json_encode($_FILES) . '<br>POST Parameters: ' . json_encode($_POST);


$request = $_POST;
$id = $request["assetid"];
$sourcepath = $request["sourcepath"];
$exportname = $request["exportname"];
$libraries = $request["libraries"];
$keywords = $request["keywords"];

$target_file = $_FILES["file"];


$target_dir = wp_upload_dir();
//$target_file = $target_dir["path"] . '/' . $sourcepath . '/' . $exportname;
$target_file = $target_dir["path"] . '/' . $exportname;

$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// This checks if image file is a actual image or fake image
// But we want to allow arbitrary files...

/*
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
*/

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["file"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

/*
// Block certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
*/


// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    http_response_code(500);
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
   		http_response_code(500);
    }
}


// The ID of the post this attachment is for.
$parent_post_id = 0;


// Check the type of file. We'll use this as the 'post_mime_type'.
$filetype = wp_check_filetype( basename( $target_file ), null );


// Get the path to the upload directory.
$wp_upload_dir = $target_dir;

// Prepare an array of post data for the attachment.
$attachment = array(
        'guid'           => $wp_upload_dir['url'] . '/' . basename( $target_file ),
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $target_file ) ),
        'post_content'   => '',
        'post_type'     => 'attachment',
        'post_status'    => 'inherit'
);


// Insert the attachment.
$attach_id = wp_insert_attachment( $attachment, $target_file, $parent_post_id );

// Now that we have the attachment id, we can add metadata to the attachment
// use wp_set_object_terms() as in testmeta.php
$post_id = $attach_id;
$terms = $libraries;
$taxonomy = 'library';
$append = true;

$result = wp_set_object_terms($post_id, $terms, $taxonomy, $append);

$the_terms = the_terms($post_id, $taxonomy);

echo '<br> Taxonomy: ' . $taxonomy . '<br>' . 'Terms: ' . json_encode($the_terms) . '<br>' . 'Function result: ' . json_encode($result);


echo '  attach id = ' . (string)$attach_id;

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Generate the metadata for the attachment, and update the database record.
$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
wp_update_attachment_metadata( $attach_id, $attach_data );

echo 'Finished uploading ' . $target_file;




?>
