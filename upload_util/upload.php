<?php 

$entermediakey = null;

// Check if plugin has been configured
if ($entermediakey == null) {
	http_response_code(403);
	throw new Exception('EnterMedia key is not configured. To use this plugin, please update the $entermediakey variable in upload.php and pass the same value as "accesskey" in your POST.');
}

$post_max_size = 200000000;
ini_set('file_uploads', 1);
ini_set('post_max_size', $post_max_size);
ini_set('max_input_time', 3600);
ini_set('max_execution_time', 3600);

ini_set('html_errors', '1');
ini_set('file_uploads', '1');
ini_set('track_errors', '1');
ini_set("log_errors", 1);
ini_set("error_log","~/php.err");
ini_set('display_errors','1');
error_reporting(E_ALL);

$rootpath = $_SERVER['DOCUMENT_ROOT'];

// Make sure these are populated

$request = $_POST;
$id = $request["assetid"];
$sourcepath = $request["sourcepath"];
$exportname = $request["exportname"];
$libraries = $request["libraries"];
$keywords = $request["keywords"];
$post_key = $request["accesskey"];

// Check if POST is authenticated
if ($post_key != $entermediakey) {
	http_response_code(403);
	throw new Exception("Permission denied. POST key does not match EnterMedia access key.");
}

$target_dir = wp_upload_dir();

// Build final file directory from asset sourcepath
$base_dir = $target_dir["path"] . '/' . $sourcepath;

if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}

// Build final file name from asset exportname
$target_file = $base_dir . '/' . $exportname;

$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Get file data from $_FILES object
$tmp_file = $_FILES["file"]["tmp_name"];
$file_name = $_FILES["file"]["name"];
$file_size = $_FILES["file"]["size"];
if (is_array($tmp_file)) {
	$tmp_file = $tmp_file[0];
}
if (is_array($file_name)) {
    $file_name = $file_name[0];
}
if (is_array($file_size)) {
    $file_size = $file_size[0];
}

$error = 1;
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    http_response_code(500);
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($tmp_file, $target_file)) {
        echo "The file ". basename( $file_name). " has been uploaded.";
		$error = 1;
    } else {
        echo "Sorry, there was an error uploading your file.";
   		http_response_code(500);
    }
}

// Tell the server that an error occurred.
if ($error) {
	header("HTTP/1.1 500 Internal Server Error");
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

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Generate the metadata for the attachment, and update the database record.
$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
wp_update_attachment_metadata( $attach_id, $attach_data );


// Now that we have the attachment id, we can add metadata to the attachment
$post_id = $attach_id;
$terms = explode(",", $libraries);
$taxonomy = 'library';
$append = true;

$result = wp_set_object_terms($post_id, $terms, $taxonomy, $append);

$the_terms = the_terms($post_id, $taxonomy);

?>
