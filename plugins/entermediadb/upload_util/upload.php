<?php

$dir_array = explode('wp-content', dirname(__FILE__) );

$wp_dir = $dir_array[0];

// Make sure this has access to WP scope
require_once( $wp_dir . 'wp-load.php' );

// include option.php to grab EMDB settings
require_once( $wp_dir . 'wp-includes/option.php' );

$cdn_prefix = get_option('emdb_cdn_prefix');        # Set this to your catalogsetting/cdn_prefix
$mediadbappid = get_option('emdb_mediadbappid');         # Set this to your own mediadb location
$entermediakey = get_option('emdb_entermediakey');    # Set this to the Access Key in your Wordpress publisher

/* End EnterMedia variable definitions */

// Check if plugin has been configured

if (!$entermediakey) {
    http_response_code(403);
    throw new Exception('EnterMedia key is not configured. To use this plugin, please update the $entermediakey variable in upload.php and pass the same value as "accesskey" in your POST.');
	exit;
}

$post_max_size = 200000000;
ini_set('file_uploads', 1);
ini_set('post_max_size', $post_max_size);
ini_set('max_input_time', 3600);
ini_set('max_execution_time', 3600);

/*
ini_set('html_errors', '1');
ini_set('file_uploads', '1');
ini_set('track_errors', '1');
ini_set("log_errors", 1);
ini_set('display_errors','1');
error_reporting(E_ALL);
*/

// Make sure these are populated

$request = $_POST;
$id = $request["assetid"];
$sourcepath = $request["sourcepath"];
$sourcepath = str_replace('.', '', $sourcepath);
$exportname = $request["exportname"];
$collection = $request["collection"];
$library = $request["library"];
$keywords = $request["keywords"];
$post_key = $request["accesskey"];

$assettitle = $request["title"];
$assetcaption = $request["caption"];
$assetdescription = $request["description"];


// Check if POST is authenticated
if ($post_key != $entermediakey) {
    http_response_code(403);
    throw new Exception("Permission denied. POST key does not match EnterMedia access key.");
	exit;
}

$target_dir = wp_upload_dir();

// Build final file directory from asset sourcepath
$base_dir = $target_dir["path"] . '/' . $sourcepath;

if (!file_exists($base_dir)) {
    mkdir($base_dir, 0777, true);
}

// Build final file name from asset exportname
$target_file = $base_dir . '/' . $exportname;

$error = 0;

$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if file already exists
if (file_exists($target_file)) {
    //echo "Sorry, file already exists.";
    //$error = 1;
	$name = pathinfo($exportname, PATHINFO_FILENAME);
	$extension = pathinfo($exportname, PATHINFO_EXTENSION);
	$target_file = $base_dir . '/' . $name.'_copy.'.$extension;
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
if (move_uploaded_file($tmp_file, $target_file)) {
	echo "The file ". basename( $file_name). " has been uploaded.";
}
else
{
	echo "Could not move file";
	$error = 1;
}


// Tell the server that an error occurred.
if ($error) {
    header("HTTP/1.1 500 Internal Server Error");
    http_response_code(500);
    exit;
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
        'guid'           => $wp_upload_dir['url'] . '/' . basename( $target_file ),
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', $assettitle ),
        'post_content'   => $assetdescription,
		'post_excerpt'   => $assetcaption,
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

$post_id = $attach_id;

/*
// set parent terms
wp_set_object_terms( $post_id, $object_types, $taxonomy, true );
// make terms hierarchial by objektart
$term_parent = get_term_by( 'slug', $openimmo_data['objektart'], $taxonomy );
$term_child  = get_term_by( 'slug', $openimmo_data['objektart_detail'], $taxonomy );
delete_option( 'immomakler_object_type_children' ); // workaround for WordPress not updating this option properly
wp_update_term( $term_child->term_id, $taxonomy, array( 'parent' => $term_parent->term_id ) );
*/

/*
//Saving more than 1 collections
$taxonomy = 'media_category';
$terms = explode(",", $collections);
wp_set_object_terms($post_id, $terms, $taxonomy, true);
echo "saved " . $collections. "," . $post_id . "," . sizeof($terms)  . "," . $taxonomy  . "," . $append;
*/
//Saving one library & one collection only
$taxonomy = 'media_category';
$terms = array($library, $collection);
wp_set_object_terms($post_id, $terms, $taxonomy, true);

$term_parent = get_term_by( 'slug', $library, $taxonomy );
$term_child  = get_term_by( 'slug', $collection, $taxonomy );
delete_option( $taxonomy.'_children' ); // workaround for WordPress not updating this option properly
wp_update_term( $term_child->term_id, $taxonomy, array( 'parent' => $term_parent->term_id ) );

echo "Saved " . $library. "," .$collection .' Post id:'. $post_id . "," . sizeof($terms)  . "," . $taxonomy  . "," . $append."\n";
/*
//keywords
$taxonomy = 'media_keywords';
$terms = explode(",", $keywords);
wp_set_object_terms($post_id, $terms, $taxonomy, true);
echo "Keywords" . $keywords;
*/ 

$the_terms = the_terms($post_id, $taxonomy);

?>

