<?php
// Load Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test environment - UPDATE IT ACCORDINGLY
$_tests_dir = 'E:/xampp/htdocs/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname(__DIR__) . '/chatgpt-widget.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require $_tests_dir . '/includes/bootstrap.php';
?>
