<?php
/*
Plugin Name: RB2B WordPress Plugin
Plugin URI: https://support.rb2b.com
Description: RB2B's WordPress plugin allows you to add your RB2B tracking snippet for easy installation on your WordPress-built website.
Version: 1.2
Author: RB2B
Author URI: https://rb2b.com
Text Domain: rb2b
Developer: RB2B
Developer URI: https://rb2b.com
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Settings menu
function rb2b_code_injection_menu() {
	add_menu_page(
		__( 'RB2B Settings', 'rb2b' ),
		__( 'RB2B', 'rb2b' ),
		'manage_options',
		'rb2b-code-injection-settings',
		'rb2b_code_injection_settings_page',
		'',
		81
	);
}
add_action( 'admin_menu', 'rb2b_code_injection_menu' );

// Plugin action links — use plugin-specific hook to avoid running on every plugin row
function rb2b_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=rb2b-code-injection-settings' ) ) . '">' . __( 'Settings', 'rb2b' ) . '</a>';
	$docs_link     = '<a href="https://support.rb2b.com/en/articles/8795573-rb2b-install-guide-for-wordpress" target="_blank" rel="noopener noreferrer">' . __( 'Documentation', 'rb2b' ) . '</a>';
	$login_link    = '<a href="https://app.rb2b.com/login" target="_blank" rel="noopener noreferrer">' . __( 'Login', 'rb2b' ) . '</a>';
	$signup_link   = '<a href="https://app.rb2b.com/signup" target="_blank" rel="noopener noreferrer">' . __( 'Sign Up (Free)', 'rb2b' ) . '</a>';

	array_push( $links, $settings_link, $docs_link, $login_link, $signup_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rb2b_plugin_action_links' );

// Admin menu icon styles via wp_add_inline_style
function rb2b_code_injection_admin_styles() {
	$icon_url = esc_url( plugin_dir_url( __FILE__ ) . 'icon.png' );
	$css      = "
		#toplevel_page_rb2b-code-injection-settings .wp-menu-image {
			background: url({$icon_url}) no-repeat center center !important;
			background-size: contain !important;
			opacity: 0.5;
		}
		#toplevel_page_rb2b-code-injection-settings .wp-menu-image:before {
			content: '' !important;
		}
		#toplevel_page_rb2b-code-injection-settings:hover .wp-menu-image {
			opacity: 1;
		}
	";

	wp_register_style( 'rb2b-admin-icon', false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'rb2b-admin-icon' );
	wp_add_inline_style( 'rb2b-admin-icon', $css );
}
add_action( 'admin_enqueue_scripts', 'rb2b_code_injection_admin_styles' );

// Settings page
function rb2b_code_injection_settings_page() { ?>
	<div class="wrap">
		<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>" alt="RB2B" style="width: 200px; margin: 30px 0;" />
		<h1><?php esc_html_e( 'RB2B Plugin Settings', 'rb2b' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'rb2b_code_injection_group' );
			do_settings_sections( 'rb2b_code_injection_settings' );
			?>
			<p><?php esc_html_e( 'Copy your RB2B Account ID as it appears on your', 'rb2b' ); ?> <a href="https://app.rb2b.com/script/setup_script_html" title="script page" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'script page', 'rb2b' ); ?></a>. <?php esc_html_e( 'It can be found in the last line of the HTML snippet, before the closing script tag.', 'rb2b' ); ?></p>
			<p><?php esc_html_e( 'Example:', 'rb2b' ); ?> <code>reb2b.SNIPPET_VERSION = "1.0.1";reb2b.load("YOUR-ACCOUNT-ID");}();</code></p>

			<br />

			<p><strong><?php esc_html_e( 'Account ID:', 'rb2b' ); ?></strong></p>
			<input type="text" name="rb2b_code_injection_html" value="<?php echo esc_attr( get_option( 'rb2b_code_injection_html' ) ); ?>" size="50" placeholder="YOUR-ACCOUNT-ID" />
			<?php submit_button(); ?>

			<h3><?php esc_html_e( 'Need help finding your RB2B Account ID?', 'rb2b' ); ?></h3>
			<p><?php esc_html_e( 'This video will help.', 'rb2b' ); ?></p>
			<iframe width="700" height="500" src="https://www.youtube.com/embed/OkFlff4bz6c?si=BeLys-GIQNwtQC2R" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		</form>
	</div>
<?php }

// Register settings
function rb2b_code_injection_settings_init() {
	register_setting(
		'rb2b_code_injection_group',
		'rb2b_code_injection_html',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'rb2b_code_injection_settings_init' );

// Inject tracking script via wp_enqueue_scripts + wp_add_inline_script
function rb2b_code_injection() {
	$account_id = sanitize_text_field( get_option( 'rb2b_code_injection_html', '' ) );
	if ( empty( $account_id ) ) {
		return;
	}

	$account_id_js = esc_js( $account_id );

	$script = '!function(){var reb2b=window.reb2b=window.reb2b||[];if(reb2b.invoked)return;reb2b.invoked=true;reb2b.methods=["identify","collect"];reb2b.factory=function(method){return function(){var args=Array.prototype.slice.call(arguments);args.unshift(method);reb2b.push(args);return reb2b;};};for(var i=0;i<reb2b.methods.length;i++){var key=reb2b.methods[i];reb2b[key]=reb2b.factory(key);}reb2b.load=function(key){var script=document.createElement("script");script.type="text/javascript";script.async=true;script.src="https://b2bjsstore.s3.us-west-2.amazonaws.com/b/"+key+"/"+key+".js.gz";var first=document.getElementsByTagName("script")[0];first.parentNode.insertBefore(script,first);};reb2b.SNIPPET_VERSION="1.0.1";reb2b.load("' . $account_id_js . '");}();';

	wp_register_script( 'rb2b-tracker', '', array(), false, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
	wp_enqueue_script( 'rb2b-tracker' );
	wp_add_inline_script( 'rb2b-tracker', $script );
}
add_action( 'wp_enqueue_scripts', 'rb2b_code_injection' );
