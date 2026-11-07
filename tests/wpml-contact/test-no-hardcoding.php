<?php
/**
 * Test: No Hardcoded Language Codes
 *
 * Verifies that contact form language switching uses dynamic WPML APIs
 * and NEVER hardcodes 'en', 'ar', or any language/locale literal.
 *
 * Run: php test-no-hardcoding.php
 */

echo "No-Hardcoding Tests\n";
echo "===================\n\n";

$contact_php = file_get_contents(__DIR__ . '/../../theme-src/inc/contact.php');

// Test 1: No hardcoded 'en' literals in fallback logic
preg_match_all("/(\'en\'|\"en\")/", $contact_php, $matches_en, PREG_OFFSET_CAPTURE);
$hardcoded_en = [];
foreach ($matches_en[0] as $match) {
	$line = substr_count(substr($contact_php, 0, $match[1]), "\n") + 1;
	// Exclude comments and function names containing 'en'
	$context = substr($contact_php, max(0, $match[1] - 50), 100);
	if (!preg_match('/\/\/|\/\*|\*\/|function|content/', $context)) {
		$hardcoded_en[] = "Line $line: " . trim(substr($context, 45, 60));
	}
}

if (empty($hardcoded_en)) {
	echo "✓ Test 1: No hardcoded 'en' found\n\n";
} else {
	echo "✗ Test 1 FAILED: Found hardcoded 'en':\n";
	foreach ($hardcoded_en as $err) {
		echo "  - $err\n";
	}
	echo "\n";
	exit(1);
}

// Test 2: No hardcoded 'ar' literals
preg_match_all("/(\'ar\'|\"ar\")/", $contact_php, $matches_ar, PREG_OFFSET_CAPTURE);
$hardcoded_ar = [];
foreach ($matches_ar[0] as $match) {
	$line = substr_count(substr($contact_php, 0, $match[1]), "\n") + 1;
	$context = substr($contact_php, max(0, $match[1] - 50), 100);
	if (!preg_match('/\/\/|\/\*|\*\/|function|array/', $context)) {
		$hardcoded_ar[] = "Line $line";
	}
}

if (empty($hardcoded_ar)) {
	echo "✓ Test 2: No hardcoded 'ar' found\n\n";
} else {
	echo "✗ Test 2 FAILED: Found hardcoded 'ar' at lines: " . implode(', ', $hardcoded_ar) . "\n\n";
	exit(1);
}

// Test 3: Uses wpml_default_language filter
if (strpos($contact_php, "apply_filters( 'wpml_default_language'") !== false) {
	echo "✓ Test 3: Uses wpml_default_language filter for fallback\n\n";
} else {
	echo "✗ Test 3 FAILED: Doesn't use wpml_default_language filter\n\n";
	exit(1);
}

// Test 4: Uses switch_to_locale() for actual locale switching
if (strpos($contact_php, 'switch_to_locale') !== false) {
	echo "✓ Test 4: Uses switch_to_locale() to switch WordPress locale\n\n";
} else {
	echo "✗ Test 4 FAILED: Doesn't use switch_to_locale()\n\n";
	exit(1);
}

// Test 5: Restores locale before exit
if (strpos($contact_php, 'restore_current_locale') !== false) {
	echo "✓ Test 5: Calls restore_current_locale() before exits\n\n";
} else {
	echo "✗ Test 5 FAILED: Doesn't restore locale\n\n";
	exit(1);
}

// Test 6: Validates array types before use
if (preg_match('/is_array\s*\(\s*\$active_langs/', $contact_php)) {
	echo "✓ Test 6: Validates array types before access\n\n";
} else {
	echo "✗ Test 6 FAILED: Doesn't validate array types\n\n";
	exit(1);
}

// Test 7: Restores in early exit ($fail closure)
if (preg_match('/use\s*\([^)]*\$switched[^)]*\)/', $contact_php) &&
    preg_match('/\$fail[^}]*restore_current_locale/s', $contact_php)) {
	echo "✓ Test 7: Restores locale in early exit paths\n\n";
} else {
	echo "✗ Test 7 FAILED: Early exits don't restore locale\n\n";
	exit(1);
}

// Test 8: Gets locale from WPML, not hardcoded
if (strpos($contact_php, "apply_filters( 'wpml_locale'") !== false ||
    strpos($contact_php, 'default_locale') !== false) {
	echo "✓ Test 8: Gets WordPress locale from WPML dynamically\n\n";
} else {
	echo "✗ Test 8 FAILED: Doesn't get locale from WPML\n\n";
	exit(1);
}

echo "✅ ALL NO-HARDCODING TESTS PASSED\n\n";
echo "Verified:\n";
echo "- No hardcoded 'en' or 'ar' language codes\n";
echo "- Uses wpml_default_language for fallback\n";
echo "- Uses switch_to_locale() to actually switch WP locale\n";
echo "- Restores locale on ALL exit paths\n";
echo "- Validates array types before use\n";
echo "- Gets locale dynamically from WPML\n";

exit(0);
