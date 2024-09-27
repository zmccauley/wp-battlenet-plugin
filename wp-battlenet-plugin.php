<?php

/**
 * Plugin Name: WP Battle.net Plugin
 * Description: A plugin that provides shortcodes for Battle.net API
 * Version: 0.0.0
 */
global $wpdb;

$client_secret = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'our_client_secret'");
$client_id = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'our_client_id'");

function token_call(){
  global $client_id,$client_secret;
  $url = "https://us.battle.net/oauth/token";
  $params = ['grant_type'=>'client_credentials', 'scope' => 'wow.profile'];
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
  curl_setopt($curl, CURLOPT_USERPWD, $client_id.':'.$client_secret);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($curl);
  curl_close($curl);
  return json_decode($result)->access_token;
}

//token cost 
function blizzard_call_func(){
  $access_token=token_call();
  $region = 'us';
  $namespace = 'dynamic-us';
  $locale = 'en_US';
  $url="https://{$region}.api.blizzard.com/data/wow/token/?namespace={$namespace}&locale={$locale}";
  $headers = [
    "Authorization: Bearer " . $access_token
];
  $curl=curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($curl);
  curl_close($curl);
  $gold_value = number_format(intval(json_decode($result)-> price) / 100 / 100);
  return "<h1>The present value of a WoW token is {$gold_value} gold";
}

add_shortcode('blizzard_call','blizzard_call_func');

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
function fsdapikey_add_api_keys_callback() { ?>

    <div class="wrap"></div>
        <h2>API key settings</h2>
        <?php

          // Check if status is 1 which means a successful options save just happened
          if(isset($_GET['status']) && $_GET['status'] == 1): ?>
            
            <div class="notice notice-success inline">
              <p>Options Saved!</p>
            </div>

          <?php endif;

        ?>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            
            <h3>Client_ID</h3>

            <!-- The nonce field is a security feature to avoid submissions from outside WP admin -->
            <?php wp_nonce_field( 'fsdapikey_api_options_verify'); ?>

            <input type="password" name="our_client_id" placeholder="Enter Client ID" value="<?php echo $api_key ? esc_attr( $api_key ) : '' ; ?>">
            <input type="hidden" name="action" value="fsdapikey_external_api">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Client ID"  />
        </form> 

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">

            <h3>Client_Secret</h3>

            <!-- The nonce field is a security feature to avoid submissions from outside WP admin -->
            <?php wp_nonce_field( 'fsdapikey_api_options_verify'); ?>

            <input type="password" name="our_client_secret" placeholder="Enter Client Secret" value="<?php echo $api_key ? esc_attr( $api_key ) : '' ; ?>">
            <input type="hidden" name="action" value="fsdapikey_external_api">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Client Secret"  />
        </form> 
    <?php
}
add_action( 'admin_post_fsdapikey_external_api', 'fsdapikey_submit_api_key' );

function fsdapikey_submit_api_key() {

    // Make sure user actually has the capability to edit the options
    if(!current_user_can( 'edit_theme_options' )){
      wp_die("You do not have permission to view this page.");
    }
  
    // pass in the nonce ID from our form's nonce field - if the nonce fails this will kill script
    check_admin_referer( 'fsdapikey_api_options_verify');


    if (isset($_POST['our_client_id'])) {


      $api_key = sanitize_text_field( $_POST['our_client_id'] );

      $api_exists = get_option('our_client_id');

      if (!empty($api_key) && !empty($api_exists)) {
          // Update
          update_option('our_client_id', $api_key);
      } else {
          // Add option on first save
          add_option('our_client_id', $api_key);
      }
  }

  if (isset($_POST['our_client_secret'])) {


    $api_key = sanitize_text_field( $_POST['our_client_secret'] );

    $api_exists = get_option('our_client_secret');

    if (!empty($api_key) && !empty($api_exists)) {
        // Update
        update_option('our_client_secret', $api_key);
    } else {
        // Add option on first save
        add_option('our_client_secret', $api_key);
    }
}
    // Redirect to same page with status=1 to show our options updated banner
    wp_redirect($_SERVER['HTTP_REFERER'] . '&status=1');
}
?>