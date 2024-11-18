<?php
/**
 * Plugin Name: ChatGPT ChatBot Widget
 * Description: A ChatBot widget using ChatGPT APIs.
 * Version: 1.0
 * Author: Rana Faraz
 */

// Enqueue frontend scripts and styles
function chatgpt_enqueue_scripts() {
	wp_enqueue_style('chatgpt-style', plugin_dir_url(__FILE__) . 'css/chatgpt-style.css');
	wp_enqueue_script('chatgpt-script', plugin_dir_url(__FILE__) . 'js/chatgpt-widget.js', array('jquery'), null, true);
	wp_localize_script('chatgpt-script', 'chatgpt_ajax', array(
		'url' => admin_url('admin-ajax.php'),
		'security' => wp_create_nonce('chatgpt-widget-nonce')
	));
}
add_action('wp_enqueue_scripts', 'chatgpt_enqueue_scripts');

// Create admin settings page
function chatgpt_create_menu() {
	add_menu_page('ChatGPT Settings', 'ChatGPT Settings', 'manage_options', 'chatgpt-settings', 'chatgpt_settings_page');
	add_action('admin_init', 'chatgpt_register_settings');
}
add_action('admin_menu', 'chatgpt_create_menu');

function chatgpt_register_settings() {
	register_setting('chatgpt-settings-group', 'chatgpt_api_key');
	register_setting('chatgpt-settings-group', 'chatgpt_custom_gpt_link');
}

function chatgpt_settings_page() {
	?>
    <div class="wrap">
        <h1>ChatGPT Settings</h1>
        <form method="post" action="options.php">
			<?php settings_fields('chatgpt-settings-group'); ?>
			<?php do_settings_sections('chatgpt-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="chatgpt_api_key" value="<?php echo esc_attr(get_option('chatgpt_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom GPT Link</th>
                    <td><input type="text" name="chatgpt_custom_gpt_link" value="<?php echo esc_attr(get_option('chatgpt_custom_gpt_link')); ?>" /></td>
                </tr>
            </table>
			<?php submit_button(); ?>
        </form>
    </div>
	<?php
}

// Add chat widget to the frontend
function chatgpt_add_widget() {
	?>
    <div id="chatgpt-widget">
<!--        <button id="chatgpt-fullscreen">Full Screen</button>-->
        <div id="chatgpt-header">Chat with us</div>
        <div id="chatgpt-body">
            <div id="chatgpt-messages"></div>
            <textarea id="chatgpt-input" placeholder="Type your message..."></textarea>
            <button id="chatgpt-send">Send</button>
        </div>
    </div>
	<?php
}
add_action('wp_footer', 'chatgpt_add_widget');

// AJAX handler for API requests
function chatgpt_handle_request() {
	check_ajax_referer('chatgpt-widget-nonce', 'security');

	$message = sanitize_text_field($_POST['message']);
	$history = json_decode(stripslashes($_POST['history']), true);  // Retrieve history from request
	$api_key = get_option('chatgpt_api_key');  // Your fine-tuned model API key
	$custom_gpt_link = get_option('chatgpt_custom_gpt_link');  // Endpoint for your fine-tuned model

	$history[] = array('role' => 'user', 'content' => $message);

	$response = wp_remote_post($custom_gpt_link, array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $api_key,
			'Content-Type' => 'application/json',
		),
		'body' => json_encode(array(
			'model' => 'gpt-3.5-turbo',  // Update with your custom model name if needed
			'messages' => $history,  // Send entire conversation history
			'temperature' => 0.7,
			'max_tokens' => 256,
		)),
	));

	if (is_wp_error($response)) {
		wp_send_json_error($response->get_error_message());
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		if (isset($data['error'])) {
			wp_send_json_error($data['error']['message']);
		} else {
			wp_send_json_success($data['choices'][0]['message']['content']);
		}
	}
}
add_action('wp_ajax_chatgpt_request', 'chatgpt_handle_request');
add_action('wp_ajax_nopriv_chatgpt_request', 'chatgpt_handle_request');

// Activation hook to set default options
function chatgpt_widget_activate() {
	add_option('chatgpt_api_key', '');
	add_option('chatgpt_custom_gpt_link', 'https://api.openai.com/v1/chat/completions');
}
register_activation_hook(__FILE__, 'chatgpt_widget_activate');

// Deactivation hook to clean up options
function chatgpt_widget_deactivate() {
	delete_option('chatgpt_api_key');
	delete_option('chatgpt_custom_gpt_link');
}
register_deactivation_hook(__FILE__, 'chatgpt_widget_deactivate');
?>

<!-- This is a test case. -->