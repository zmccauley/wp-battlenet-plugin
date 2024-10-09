<?php

/**
 * Plugin Name: WP Battle.net Plugin
 * Description: A plugin that provides shortcodes for Battle.net API
 * Version: 0.0.0
 */


class Credentials{
  
  private $client_id;
  private $client_secret;
  private $access_token_data;
  
  public function __construct(){
    global $wpdb;

    $this -> client_id = get_option('our_client_id');
    $this -> client_secret = get_option('our_client_secret');
    $this -> access_token_data = $this -> set_access_token_data();

    if (is_resource($this -> client_id)) {
      $this -> client_id = stream_get_contents($this -> client_id);
    }
    if (is_resource($this -> client_secret)) {
      $this -> client_secret = stream_get_contents($this -> client_secret);
    }
  }
 
  public function get_client_id(){
    return $this->client_id;
  }
  public function get_client_secret(){
    return $this->client_secret;
}
  public function get_access_token_data(){
    return $this->access_token_data;
}
private function set_access_token_data() {
    $url = "https://us.battle.net/oauth/token";
    $params = ['grant_type'=>'client_credentials', 'scope' => 'wow.profile'];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_USERPWD, $this -> client_id.':'.$this -> client_secret);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result)->access_token;
  }
}
// Deprecated class: Affix
class Affix{
  
  private $affix_name;
  private $affix_id;
  private $affix_description;
  private $affix_media;
  
  public function __construct($affix_name,$affix_id,$affix_description,$affix_media){
    $this-> affix_name = $affix_name;
    $this-> affix_id = $affix_id;
    $this-> affix_description = $affix_description;
    $this-> affix_media = $affix_media;
  }
 
  public function get_affix_name(){
    return $this->affix_name;
  }

  public function get_affix_id(){
    return $this->affix_id;
  }

  public function get_affix_description(){
    return $this->affix_description;
  }

  public function get_affix_media(){
    return $this->affix_media;
  }

}

function blizzard_api_token_cost() {
  $my_creds = new Credentials();
  $client_id = $my_creds -> get_client_id();
  $client_secret = $my_creds -> get_client_secret();
  $access_token = $my_creds -> get_access_token_data();
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
  return "<h1>The present value of a WoW token is {$gold_value} gold</h1><br><p>- via the <a href='https://develop.battle.net/documentation/world-of-warcraft/game-data-apis' 
  target='_blank'>Blizzard API</a></p><br><div id='dateTime'></div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const dateTimeString = now.toLocaleString(); // Formats date and time based on the user's locale
            document.getElementById('dateTime').textContent = dateTimeString;
        }

        updateDateTime(); // Initial call
        setInterval(updateDateTime, 1000); // Update every second
    </script>";
}
    
add_shortcode('token_cost','blizzard_api_token_cost');

function my_affix_description_handler() {
  $my_creds = new Credentials();  
  $access_token = $my_creds->get_access_token_data();
  $affix_id = intval($_GET['affix_id']); // Get affix ID from AJAX request
  $region = 'us';
  $namespace = 'static-us';
  $locale = 'en_US';
  $url = "https://{$region}.api.blizzard.com/data/wow/keystone-affix/{$affix_id}?namespace={$namespace}&locale={$locale}";
  
  $headers = [
      "Authorization: Bearer " . $access_token
  ];
  
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
  $response = curl_exec($curl);
  curl_close($curl);
  
  // Return the response
  if ($response) {
      return wp_send_json_success(json_decode($response, true));
  } else {
     return wp_send_json_error('Error fetching data');
  }
}

add_action('wp_ajax_my_affix_description', 'my_affix_description_handler');
add_action('wp_ajax_nopriv_my_affix_description', 'my_affix_description_handler');


function display_affixes($result){
  $affixes_formatted = '';
  foreach ($result['affixes'] as $index => $affix) {
    $affixes_formatted .= "<div style='display:block;' class='hover-target' data-id='" . $affix['id'] . "' data-text=''>" . $affix['name'] . "</div>";
};
  return $affixes_formatted;
}

function blizzard_api_affixes() {
  $my_creds = new Credentials();
  $client_id = $my_creds -> get_client_id();
  $client_secret = $my_creds -> get_client_secret();
  $access_token = $my_creds -> get_access_token_data();
  $region = 'us';
  $namespace = 'static-us';
  $locale = 'en_US';
  $url="https://{$region}.api.blizzard.com/data/wow/keystone-affix/index?namespace={$namespace}&locale={$locale}";
  $headers = [
      "Authorization: Bearer " . $access_token
  ];
  $curl=curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $result = json_decode(curl_exec($curl),true);
  curl_close($curl);
return "<div class='hover-text' id='hoverText'></div>" . display_affixes($result) .  "
<style>
        .hover-target {
            display: inline-block;
            cursor: pointer;
            margin: 5px; /* Add some space between elements */
        }

        .hover-text {
            display: none; /* Initially hide the hover text */
            position: absolute; /* Position it relative to the document */
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 5px;
            margin-top: 5px;
            z-index: 10;
        }
    </style>

<script>
    const hoverTargets = document.querySelectorAll('.hover-target');
        const hoverText = document.getElementById('hoverText');

        hoverTargets.forEach(target => {
            target.addEventListener('mouseenter', () => {
                const affixId = target.getAttribute('data-id'); // Get the affix ID

                // Make AJAX call to get affix description
                fetch(`{${my_ajax_object.ajax_url}}?action=my_affix_description&affix_id={${affixId}}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            hoverText.textContent = data.data.description; // Use the correct key based on the API response
                            hoverText.style.display = 'block';
                            
                            const rect = target.getBoundingClientRect();
                            hoverText.style.top = (rect.bottom + window.scrollY) + 'px';
                            hoverText.style.left = rect.left + 'px';
                        } else {
                            console.error('Error:', data.data);
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });

            target.addEventListener('mouseleave', () => {
                hoverText.style.display = 'none'; // Hide text when not hovering
            });
        });
</script>";
};
    
add_shortcode('affix_index','blizzard_api_affixes');
//


// Admin menu for storing client id/secret to wp db
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