<?php

class ChatGPTWidgetTest extends WP_UnitTestCase {
	public function setUp(): void {
		parent::setUp();
		update_option('chatgpt_api_key', 'Your Secret Key');
		update_option('chatgpt_custom_gpt_link', 'https://api.openai.com/v1/chat/completions');
	}

	public function test_chatgpt_settings() {
		$this->assertEquals('Your Secret Key', get_option('chatgpt_api_key'));
		$this->assertEquals('https://api.openai.com/v1/chat/completions', get_option('chatgpt_custom_gpt_link'));
	}

	public function test_chatgpt_enqueue_scripts() {
		chatgpt_enqueue_scripts();
		$this->assertTrue(wp_script_is('chatgpt-script', 'enqueued'));
		$this->assertTrue(wp_style_is('chatgpt-style', 'enqueued'));
	}

	public function test_chatgpt_handle_request() {
		$api_key = get_option('chatgpt_api_key');
		$api_url = get_option('chatgpt_custom_gpt_link');

		$response = wp_remote_post($api_url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json',
			],
			'body' => json_encode([
				'model' => 'gpt-3.5-turbo',
				'messages' => [['role' => 'user', 'content' => 'Hello, ChatGPT!']],
				'temperature' => 1,
				'max_tokens' => 256,
				'top_p' => 1,
				'frequency_penalty' => 0,
				'presence_penalty' => 0,
			]),
		]);

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		error_log(print_r($data, true));

		if (isset($data['error'])) {
			if ($data['error']['code'] === 'insufficient_quota') {
				$this->markTestIncomplete('Insufficient quota for OpenAI API.');
			} else {
				$this->fail('API Error: ' . $data['error']['message']);
			}
		}

		$this->assertArrayHasKey('choices', $data);
		$this->assertStringContainsString('Hello! How can I assist you today?', $data['choices'][0]['message']['content']);
	}

	// Helper method to handle Ajax requests
	protected function _handleAjax($action) {
		try {
			do_action('wp_ajax_' . $action);
		} catch (WPAjaxDieStopException $e) {
			// Handle the case where wp_die is called
		}
	}
}
?>
