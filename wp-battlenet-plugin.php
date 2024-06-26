<?php

/**
 * Plugin Name: WP Battle.net Plugin
 * Description: A plugin that provides shortcodes for Battle.net API
 * Version: 0.0.0
 */
add_action('init', function () {
    error_log('Hello World');
});

function say_something_func(){
    return '<h1>HELLO I AM SAYING SOMETHING</h1>';
}
add_shortcode('say_something', 'say_something_func');
/**google hire me bro */

add_action('admin_menu', 'fsdapikey_register_my_api_keys_page');

function fsdapikey_register_my_api_keys_page() {
  add_submenu_page(
    'tools.php', // Add our page under the "Tools" menu
    'API Keys', // Title in menu
    'API Keys', // Page title
    'manage_options', // permissions
    'api-keys', // slug for our page
    'fsdapikey_add_api_keys_callback' // Callback to render the page
  );
}
?>