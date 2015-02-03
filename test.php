<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$rootpath = $_SERVER['DOCUMENT_ROOT'];
include_once($rootpath . '/wordpress/wp-config.php');

// $filename should be the path to a file in the upload directory.
$filename = '/var/www/html/wordpress/wp-content/uploads/RiverBasinsAsia.png';


echo print_r(array_values($_POST));

// The ID of the post this attachment is for.
$parent_post_id = 1;


// Check the type of file. We'll use this as the 'post_mime_type'.
$filetype = wp_check_filetype( basename( $filename ), null );


// Get the path to the upload directory.
$wp_upload_dir = wp_upload_dir();

// Prepare an array of post data for the attachment.
$attachment = array(
	'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
	'post_mime_type' => $filetype['type'],
	'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
	'post_content'   => '',
	'post_type' 	=> 'attachment',
	'post_status'    => 'inherit'
);


// Insert the attachment.
$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

echo '  attach id = ' . (string)$attach_id;

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Generate the metadata for the attachment, and update the database record.
$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
wp_update_attachment_metadata( $attach_id, $attach_data );

