<?php
/*
Plugin Name: RB2B WordPress Plugin
Plugin URI: https://support.rb2b.com/whatever
Description: RB2B's WordPress plugin allows you to add your RB2B tracking snippet for easy installation on your WordPress-built website.
Version: 1.0
Author: RB2B
Author URI: https://rb2b.com
Text Domain: rb2b
Developer: RB2B
Developer URI: https://rb2b.com
Domain Path: /languages
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Hook to add the settings menu in the WordPress admin menu
function rb2b_code_injection_menu() {
  add_menu_page(
    __('RB2B Settings', 'rb2b'), // Page title
    __('RB2B', 'rb2b'), // Menu title
    'manage_options', // Capability required to access the page
    'rb2b-code-injection-settings', // Menu slug
    'rb2b_code_injection_settings_page', // Function to display the settings page
    '', // Empty for default icon, we'll set the custom icon later
    81 // Position in the menu
  );
}
add_action('admin_menu', 'rb2b_code_injection_menu');

// Add custom links to the Plugins page for this plugin

function rb2b_plugin_action_links($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $settings_link = '<a href="admin.php?page=rb2b-code-injection-settings">' . __('Settings', 'rb2b') . '</a>';
        $docs_link = '<a href="https://support.rb2b.com" target="_blank">' . __('Documentation', 'rb2b') . '</a>';
        $login_link = '<a href="https://app.rb2b.com/login" target="_blank">' . __('Login', 'rb2b') . '</a>';
        $signup_link = '<a href="https://app.rb2b.com/signup" target="_blank">' . __('Sign Up (Free)', 'rb2b') . '</a>';
        
        $links[] = $settings_link;
        $links[] = $docs_link;
        $links[] = $login_link;
        $links[] = $signup_link;
    }
    return $links;
}
add_filter('plugin_action_links', 'rb2b_plugin_action_links', 10, 2);

// Enqueue custom icon for the plugin menu
function rb2b_code_injection_admin_styles() {
  echo '<style>
    #toplevel_page_rb2b-code-injection-settings .wp-menu-image {
      background: url(' . plugin_dir_url(__FILE__) . 'icon.png) no-repeat center center !important;
      background-size: contain !important;
      opacity: 0.5;
    }
    
    #toplevel_page_rb2b-code-injection-settings .wp-menu-image:before {
      content: "" !important;
    }
    
    #toplevel_page_rb2b-code-injection-settings:hover .wp-menu-image {
      opacity: 1;
    }
  </style>';
}
add_action('admin_head', 'rb2b_code_injection_admin_styles');

// Settings page content
function rb2b_code_injection_settings_page() { ?>
  <div class="wrap">
    <img src="<?php echo plugin_dir_url(__FILE__) ?>logo.png" alt="RB2B" style="width: 200px; margin: 30px 0;" />
    <h1>RB2B Plugin Settings</h1>
    <form method="post" action="options.php">
      <?php
      // Settings API fields
      settings_fields('rb2b_code_injection_group');
      do_settings_sections('rb2b_code_injection_settings');
      ?>
      <p>Please copy your RB2B Account ID as it appears on your <a href="https://app.rb2b.com/script/setup_script_html" title="script page" target="_blank">script page</a>. It can be found in the very last line of the HTML snippet, before the closing script tag.</p>
      <p>Example: <code>reb2b.SNIPPET_VERSION = "1.0.1";reb2b.load("YOUR-ACCOUNT-ID");}();</code></p>
      
      <br />
      
      <p><strong>Account ID:</strong></td></p>
      <input type="text" name="rb2b_code_injection_html" value="<?php echo esc_attr(get_option('rb2b_code_injection_html')); ?>" size="50" placeholder="YOUR-ACCOUNT-ID" />
      <?php submit_button(); ?>
      
      <h3>Need help finding your RB2B Account ID?</h3>
      <p>This video will help.</p>
      <iframe width="700" height="500" src="https://www.youtube.com/embed/OkFlff4bz6c?si=BeLys-GIQNwtQC2R" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </form>
  </div>
<?php }

// Register plugin settings
function rb2b_code_injection_settings_init() {
  register_setting(
    'rb2b_code_injection_group', // Option group
    'rb2b_code_injection_html', // Option name
    'sanitize_text_field' // Sanitization function to avoid XSS
  );
}
add_action('admin_init', 'rb2b_code_injection_settings_init');

// Inject the custom HTML code into the header
function rb2b_code_injection() {
  $html_snippet = get_option('rb2b_code_injection_html', '');
  if (!empty($html_snippet)) {
    // Ensure we properly escape the ID to avoid issues with code injection
    $escaped_html_snippet = esc_js($html_snippet);
    echo '<script>!function () {var reb2b = window.reb2b = window.reb2b || [];if (reb2b.invoked) return;reb2b.invoked = true;reb2b.methods = ["identify", "collect"];reb2b.factory = function (method) {return function () {var args = Array.prototype.slice.call(arguments);args.unshift(method);reb2b.push(args);return reb2b;};};for (var i = 0; i < reb2b.methods.length; i++) {var key = reb2b.methods[i];reb2b[key] = reb2b.factory(key);}reb2b.load = function (key) {var script = document.createElement("script");script.type = "text/javascript";script.async = true;script.src = "https://s3-us-west-2.amazonaws.com/b2bjsstore/b/" + key + "/' . $escaped_html_snippet . '.js.gz";var first = document.getElementsByTagName("script")[0];first.parentNode.insertBefore(script, first);};reb2b.SNIPPET_VERSION = "1.0.1";reb2b.load("' . $escaped_html_snippet . '");}();</script>';
  }
}
add_action('wp_head', 'rb2b_code_injection');
