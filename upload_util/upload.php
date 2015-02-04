<?php 


ini_set('html_errors', '1');
ini_set('file_uploads', '1');
ini_set('track_errors', '1');
ini_set('log_errors', '1');
ini_set('display_errors','1');
error_reporting(E_ALL);

$rootpath = $_SERVER['DOCUMENT_ROOT'];
include_once($rootpath . '/wordpress/wp-config.php');

$files = json_encode($_FILES);

$target_dir = wp_upload_dir();
$target_file = $target_dir["path"] . '/' . basename($_FILES["fileToUpload"]["name"]);

$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// This checks if image file is a actual image or fake image
// But we want to allow arbitrary files...

/*
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
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
if ($_FILES["fileToUpload"]["size"] > 500000) {
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
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


// $filename should be the path to a file in the upload directory.
// this is already established to be $target_file as per the upload above.

// The ID of the post this attachment is for.
$parent_post_id = 1;


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

echo '  attach id = ' . (string)$attach_id;

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Generate the metadata for the attachment, and update the database record.
$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
wp_update_attachment_metadata( $attach_id, $attach_data );

echo 'Finished uploading ' . $target_file;




?>
