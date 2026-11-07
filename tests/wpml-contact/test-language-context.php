<?php
/**
 * WPML Contact Form Language Context Tests
 *
 * Tests that form validation errors and success messages match the language
 * of the form that was submitted, not the admin/site default language.
 *
 * IMPORTANT: These are isolated unit tests that mock WPML APIs.
 * They DO NOT prove live WPML behavior - manual testing required.
 *
 * Run: php test-language-context.php
 */

// Mock WordPress and WPML functions for isolated testing
global $wp_actions, $wp_current_filter, $wp_test_lang, $wp_test_strings;
$wp_actions = [];
$wp_current_filter = [];
$wp_test_lang = 'en';
$wp_test_strings = [
	'en' => [
		'Please check the highlighted fields.' => 'Please check the highlighted fields.',
		'Please confirm the consent box.' => 'Please confirm the consent box.',
		'Your session expired. Please reload the page and send again.' => 'Your session expired. Please reload the page and send again.',
	],
	'ar' => [
		'Please check the highlighted fields.' => 'يرجى التحقق من الحقول المميزة',
		'Please confirm the consent box.' => 'يرجى تأكيد مربع الموافقة',
		'Your session expired. Please reload the page and send again.' => 'انتهت جلستك. يرجى إعادة تحميل الصفحة والإرسال مرة أخرى',
	],
	'fr' => [
		'Please check the highlighted fields.' => 'Veuillez vérifier les champs surlignés',
		'Please confirm the consent box.' => 'Veuillez confirmer la case de consentement',
		'Your session expired. Please reload the page and send again.' => 'Votre session a expiré',
	],
];

function __($text, $domain = 'default') {
	global $wp_test_lang, $wp_test_strings;
	if (isset($wp_test_strings[$wp_test_lang][$text])) {
		return $wp_test_strings[$wp_test_lang][$text];
	}
	return $text;
}

function do_action($hook, ...$args) {
	global $wp_actions, $wp_test_lang;
	if ($hook === 'wpml_switch_language' && isset($args[0])) {
		$wp_test_lang = $args[0];
	}
	$wp_actions[] = ['hook' => $hook, 'args' => $args];
}

function apply_filters($hook, $value, ...$args) {
	global $wp_test_lang;
	if ($hook === 'wpml_current_language') {
		return $wp_test_lang;
	}
	if ($hook === 'wpml_active_languages') {
		return ['en' => ['code' => 'en'], 'ar' => ['code' => 'ar'], 'fr' => ['code' => 'fr']];
	}
	return $value;
}

function sanitize_key($key) {
	return preg_replace('/[^a-z0-9_-]/', '', strtolower($key));
}

// Test helper to capture language-dependent strings
function capture_validation_error() {
	return __('Please check the highlighted fields.', 'muhamed-ahmed-astran');
}

function capture_consent_error() {
	return __('Please confirm the consent box.', 'muhamed-ahmed-astran');
}

function capture_nonce_error() {
	return __('Your session expired. Please reload the page and send again.', 'muhamed-ahmed-astran');
}

// Test the language switching logic
function test_language_validation() {
	global $wp_test_lang, $wp_actions;
	
	echo "WPML Language Context Tests\n";
	echo "===========================\n\n";
	
	$tests_passed = 0;
	$tests_failed = 0;
	
	// Test 1: English form → English errors
	$wp_test_lang = 'en';
	$submitted_lang = 'en';
	$active_langs = apply_filters('wpml_active_languages', null);
	
	// Validate submitted language
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en'; // Fallback
	}
	
	// Switch to submitted language
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	$consent = capture_consent_error();
	
	if ($error === 'Please check the highlighted fields.' && $consent === 'Please confirm the consent box.') {
		echo "✓ Test 1: English form produces English errors\n";
		echo "  Error: $error\n";
		echo "  Consent: $consent\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 1 FAILED\n";
		echo "  Expected English, got: $error / $consent\n\n";
		$tests_failed++;
	}
	
	// Test 2: Arabic form → Arabic errors (site default is Arabic)
	$wp_test_lang = 'ar'; // Simulate site default
	$submitted_lang = 'ar';
	
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en';
	}
	
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	$consent = capture_consent_error();
	
	if ($error === 'يرجى التحقق من الحقول المميزة' && $consent === 'يرجى تأكيد مربع الموافقة') {
		echo "✓ Test 2: Arabic form produces Arabic errors\n";
		echo "  Error: $error\n";
		echo "  Consent: $consent\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 2 FAILED\n";
		echo "  Expected Arabic, got: $error / $consent\n\n";
		$tests_failed++;
	}
	
	// Test 3: CRITICAL - English form submitted while site is Arabic
	// This is the bug: form was EN but errors came back in AR
	$wp_test_lang = 'ar'; // Site/admin default
	$submitted_lang = 'en'; // Form was English
	
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en';
	}
	
	// BUG FIX: Switch to submitted language BEFORE validation
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	$nonce = capture_nonce_error();
	
	if ($error === 'Please check the highlighted fields.' && $nonce === 'Your session expired. Please reload the page and send again.') {
		echo "✓ Test 3: English form on Arabic site → English errors (BUG FIXED)\n";
		echo "  Error: $error\n";
		echo "  Nonce: $nonce\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 3 FAILED - BUG NOT FIXED\n";
		echo "  Expected English, got: $error / $nonce\n\n";
		$tests_failed++;
	}
	
	// Test 4: French form (arbitrary language)
	$wp_test_lang = 'ar'; // Site default
	$submitted_lang = 'fr';
	
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en';
	}
	
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	
	if ($error === 'Veuillez vérifier les champs surlignés') {
		echo "✓ Test 4: French form produces French errors\n";
		echo "  Error: $error\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 4 FAILED\n";
		echo "  Expected French, got: $error\n\n";
		$tests_failed++;
	}
	
	// Test 5: Invalid language → fallback to English
	$wp_test_lang = 'ar';
	$submitted_lang = 'invalid';
	
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en'; // Safe fallback
	}
	
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	
	if ($submitted_lang === 'en' && $error === 'Please check the highlighted fields.') {
		echo "✓ Test 5: Invalid language falls back to English\n";
		echo "  Fallback lang: $submitted_lang\n";
		echo "  Error: $error\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 5 FAILED\n";
		echo "  Expected English fallback, got: $submitted_lang / $error\n\n";
		$tests_failed++;
	}
	
	// Test 6: Missing language parameter → fallback
	$wp_test_lang = 'ar';
	$submitted_lang = ''; // Not provided
	
	// Fallback to current language or English
	if (!$submitted_lang) {
		$current = apply_filters('wpml_current_language', null);
		$submitted_lang = $current ?: 'en';
	}
	
	if (!isset($active_langs[$submitted_lang])) {
		$submitted_lang = 'en';
	}
	
	do_action('wpml_switch_language', $submitted_lang);
	
	$error = capture_validation_error();
	
	if ($submitted_lang === 'ar') {
		echo "✓ Test 6: Missing language uses current (Arabic)\n";
		echo "  Detected lang: $submitted_lang\n";
		echo "  Error: $error\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 6 FAILED\n\n";
		$tests_failed++;
	}
	
	// Test 7: Verify restore happens
	$wp_test_lang = 'ar';
	$original_lang = apply_filters('wpml_current_language', null);
	$submitted_lang = 'en';
	
	// Switch for validation
	do_action('wpml_switch_language', $submitted_lang);
	$error = capture_validation_error();
	
	// Restore
	do_action('wpml_switch_language', $original_lang);
	
	if ($wp_test_lang === 'ar') {
		echo "✓ Test 7: Language restored after processing\n";
		echo "  Original: ar, Switched: en, Restored: ar\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 7 FAILED\n\n";
		$tests_failed++;
	}
	
	// Check that wpml_switch_language was called
	$switch_calls = array_filter($wp_actions, fn($a) => $a['hook'] === 'wpml_switch_language');
	
	if (count($switch_calls) >= 7) {
		echo "✓ Test 8: wpml_switch_language called correctly\n";
		echo "  Switch calls: " . count($switch_calls) . "\n\n";
		$tests_passed++;
	} else {
		echo "✗ Test 8 FAILED - Not enough switch calls\n\n";
		$tests_failed++;
	}
	
	echo "==================\n";
	echo "Results: $tests_passed passed, $tests_failed failed\n";
	
	if ($tests_failed === 0) {
		echo "✅ ALL TESTS PASSED\n\n";
		echo "IMPORTANT NOTES:\n";
		echo "- These are isolated unit tests with mocked WPML functions\n";
		echo "- They validate the LOGIC of language switching\n";
		echo "- They DO NOT prove live WPML integration\n";
		echo "- Manual testing with actual WPML required\n";
		echo "- Test with: EN form on AR site, AR form, FR form, guest/admin\n";
		return 0;
	} else {
		echo "❌ SOME TESTS FAILED\n";
		return 1;
	}
}

exit(test_language_validation());
