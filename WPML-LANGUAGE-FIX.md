# WPML Contact Form Language Context Fix

## Problem

English contact form (`/en/contact/`) displays Arabic server validation errors because:
- Form submits to `admin-post.php` (separate HTTP request)
- WPML language context is lost between page render and POST handler
- Handler uses site default locale (Arabic) instead of form's language
- All `__()` translated strings show in wrong language

## Solution

Standards-based WPML language propagation using official APIs:

1. **Form render**: Capture current WPML language and send with form
2. **Handler start**: Validate and switch to form's language BEFORE any `__()` calls
3. **Handler end**: Restore original language context

## Implementation

### Files Changed

1. **page-contact.php** (+1 line)
   - Add hidden field: `<input type="hidden" name="mx_lang" value="<?php echo esc_attr( (string) apply_filters( 'wpml_current_language', null ) ); ?>">`

2. **inc/contact.php** (+26 lines)
   - Capture and validate submitted language at function start
   - Switch to form language before nonce/validation/error messages
   - Restore original language before exit

### WPML APIs Used

- `apply_filters('wpml_current_language', null)` - Get current language code
- `apply_filters('wpml_active_languages', array())` - Get enabled languages for validation
- `do_action('wpml_switch_language', $lang)` - Switch context (no-op when WPML absent)

### Logic Flow

```php
// 1. Capture original and form languages
$original_lang = apply_filters('wpml_current_language', null);
$form_lang = sanitize_key($_POST['mx_lang'] ?? '');

// 2. Fallback chain
if (empty($form_lang)) {
    $form_lang = $original_lang ?: 'en';
}

// 3. Validate against active languages
$active_langs = apply_filters('wpml_active_languages', array());
if (!isset($active_langs[$form_lang])) {
    $form_lang = 'en'; // Safe fallback
}

// 4. Switch to form language
do_action('wpml_switch_language', $form_lang);

// ... all validation, errors, success messages ...

// 5. Restore original before exit
if ($original_lang && $original_lang !== $form_lang) {
    do_action('wpml_switch_language', $original_lang);
}
```

### Edge Cases Handled

| Scenario | Behavior |
|----------|----------|
| EN form on AR site | Switches to EN → English errors |
| AR form on AR site | No switch needed → Arabic errors |
| Missing `mx_lang` field | Falls back to current or 'en' |
| Invalid language code | Falls back to 'en' |
| Disabled language | Falls back to 'en' |
| WPML not active | `do_action` is no-op, locale unchanged |
| Guest vs admin locale | Form language always wins |
| Logged-in with different locale | Form language always wins |

### No Hardcoding

- ✅ No `if ($lang === 'ar')` or `if ($lang === 'en')`
- ✅ No URL prefix parsing
- ✅ No domain/path/locale maps
- ✅ Works with ALL active WPML languages (FR, DE, ES, etc.)
- ✅ No custom language constants

### Backward Compatibility

- When WPML not installed: filters return null, actions do nothing
- When `mx_lang` missing (old cached forms): falls back gracefully
- Existing home.js (hash 8890cd59...) preserved - no JS changes needed

## Testing Strategy

### Automated Tests (Isolated)

`tests/wpml-contact/test-language-context.php` validates:
- ✅ English form → English errors
- ✅ Arabic form → Arabic errors
- ✅ **Critical: EN form on AR site → EN errors (bug fix)**
- ✅ French/arbitrary language → correct errors
- ✅ Invalid language → fallback to EN
- ✅ Missing language → fallback chain
- ✅ Language restoration after processing
- ✅ `wpml_switch_language` called correctly

**Important**: These are unit tests with mocked WPML functions. They validate the LOGIC but DO NOT prove live WPML integration.

### Manual Testing Required

Test matrix (all scenarios with actual WPML):

| Test | Setup | Action | Expected Result |
|------|-------|--------|-----------------|
| 1 | Site locale: AR, Visit `/en/contact/` | Leave consent unchecked, submit | English error: "Please confirm the consent box." |
| 2 | Site locale: AR, Visit `/en/contact/` | Empty name, submit | English error: "Please enter your name." |
| 3 | Site locale: AR, Visit `/contact/` (AR) | Leave consent unchecked, submit | Arabic error: "يرجى تأكيد مربع الموافقة" |
| 4 | Site locale: AR, Visit `/en/contact/` | Valid submission | English success message |
| 5 | Site locale: AR, Visit `/contact/` (AR) | Valid submission | Arabic success message |
| 6 | Logged in as AR admin, Visit `/en/contact/` | Validation error | English error (not AR) |
| 7 | Guest user, Site default AR | Visit FR page (if enabled), error | French error |
| 8 | Visit `/en/contact/` | Expired nonce | English nonce error |
| 9 | WPML disabled | Any form | Works with default WordPress locale |
| 10 | Old cached form (no `mx_lang`) | Submit | Falls back gracefully |

### Verification Points

Each test should verify:
1. **Response language** matches FORM language (not site/admin/user locale)
2. **All strings** translated: errors, nonce, honeypot, success
3. **FormData** still contains WordPress `action=mahx_contact` field
4. **No console errors** or PHP warnings
5. **Original language restored** (check subsequent requests)

### Cache Clearing

Per BRIEF.txt, FlyingPress cache may serve old HTML without `mx_lang` field:
- Use **browser DevTools** to inspect actual HTML source
- Or manually purge `/en/contact/` and `/contact/` from FlyingPress
- No WP-CLI command available for selective URL purge

## Hashes

### Before (Baseline from upload)

```
1185c1bd45e0eb7f08ef207cf656039bd3e7980ed31298ce59df95956ca29575  inc/contact.php
9027dd115bbe61e85b64cdf1a849b8b70da3b1891a48317136ace06084ff93f3  page-contact.php
```

### After (Fixed)

```
3ef987fb8c184493dd4b45aa69bc192f46ebbe126f36dd5c457b5b2ccfd8c618  inc/contact.php
db904d2b357550bb5da07d4658111083a103cdfe247157ab1431e9c2143d274e  page-contact.php
```

### Unchanged (Preserved)

```
8890cd59e251e3b818ee5f0bfc3cf23dcac4d5e06fbc3964ecc2a21697ada0e9  assets/v18/home.js ✅
```

## Files for Deployment

Located in `/workspace/theme-src/`:
- `inc/contact.php` (393 lines)
- `page-contact.php` (224 lines)

Upload to production theme directory preserving paths:
- `wp-content/themes/{theme-name}/inc/contact.php`
- `wp-content/themes/{theme-name}/page-contact.php`

## Rollback

If issues occur, restore original files from backup or attached source:
```bash
# Restore from attached source
cp contact-wpml-source_5b7c/theme/inc/contact.php {theme}/inc/
cp contact-wpml-source_5b7c/theme/page-contact.php {theme}/
```

## Related Documentation

- [WPML Language API](https://wpml.org/wpml-hook/wpml_current_language/)
- [WPML Switch Language](https://wpml.org/wpml-hook/wpml_switch_language/)
- [WPML Active Languages](https://wpml.org/wpml-hook/wpml_active_languages/)

## Summary

- ✅ Minimal source changes (1 line + 26 lines)
- ✅ Uses WPML official APIs only
- ✅ No hardcoded languages/domains/paths
- ✅ All active languages supported
- ✅ Safe fallbacks for edge cases
- ✅ WPML-absent compatibility
- ✅ Preserves existing home.js fix
- ⚠️ Manual production testing required
- ⚠️ Cache may need selective purge
