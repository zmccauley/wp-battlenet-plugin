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

?>