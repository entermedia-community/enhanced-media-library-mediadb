<?php 
// register actions
add_action('admin_init', 'emdb_admin_init');
add_action('admin_menu', 'add_emdb_settings_menu');

function emdb_admin_init() {

	// register your plugins settings
	register_setting('emdb-publish', 'emdb_cdn_prefix');
	register_setting('emdb-publish', 'emdb_entermediakey');
	register_setting('emdb-publish', 'emdb_mediadbappid');

}

function add_emdb_settings_menu() {

	// Add a page to manage this plugin's settings
	add_options_page(
		'EnterMedia DB Plugin Settings', // Page Title
		'EnterMedia DB', // Menu Title
		'manage_options', // required permission/capability
		'entermedia_db', // menu slug
		'emdb_plugin_settings_page' // callback
	);

}            

function emdb_plugin_settings_page() {

	if(!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	// Render the settings template
	include(sprintf("%s/templates/settings.php", dirname(__FILE__)));

}
?>
