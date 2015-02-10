<?php

ini_set('track_errors', '1');
ini_set('log_errors', '1');
ini_set('display_errors','1');
error_reporting(E_ALL);

$rootpath = $_SERVER['DOCUMENT_ROOT'];
include_once($rootpath . '/wordpress/wp-config.php');

$post_id = 81;
$terms = Array('Dogs', 'Fun');
$taxonomy = 'library';
$append = true;

$result = wp_set_object_terms($post_id, $terms, $taxonomy, $append);

$the_terms = the_terms($post_id, $taxonomy);

echo '<br> Taxonomy: ' . $taxonomy . '<br>' . 'Terms: ' . json_encode($the_terms) . '<br>' . 'Function result: ' . json_encode($result);

?>
