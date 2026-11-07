# WPML Contact Form Fix - Corrected Deployment Summary

## Review Gate Status: CORRECTED (Awaiting Revalidation)

### Previous Failure (Run 4)
- ❌ Hardcoded `'en'` fallback (2 occurrences)
- ❌ `wpml_switch_language` doesn't switch WordPress locale
- ❌ Early exits not restored
- ❌ No gettext textdomain reload

### Corrections Applied
- ✅ Removed ALL hardcoded language literals
- ✅ Use `wpml_default_language` filter for fallback
- ✅ Use `switch_to_locale()` to actually switch WP locale and reload textdomains
- ✅ Restore locale on ALL exit paths (early errors + success)
- ✅ Validate array types before access
- ✅ Reject malformed input before sanitize

---

## Deployment Artifacts (Corrected)

### Files to Deploy (2)

**1. inc/contact.php**
```
SHA-256: 633b75f1be882a9740a582e64f4ba9e75c69e8d320bdc26a6d456221e77ae42f
Size: 15.3 KB (428 lines, +60/-3 from baseline)
URL: https://raw.githubusercontent.com/MuhamedAhmedcom/theme/1a1d6e3/theme-src/inc/contact.php
```

**2. page-contact.php**
```
SHA-256: db904d2b357550bb5da07d4658111083a103cdfe247157ab1431e9c2143d274e
Size: 13 KB (224 lines, +1 from baseline)
URL: https://raw.githubusercontent.com/MuhamedAhmedcom/theme/1a1d6e3/theme-src/page-contact.php
```

### Preserved (Unchanged)

**assets/v18/home.js** (Previous form action fix)
```
SHA-256: 8890cd59e251e3b818ee5f0bfc3cf23dcac4d5e06fbc3964ecc2a21697ada0e9
Status: ✅ PRESERVED (do not replace)
```

---

## What Changed from Previous Version

| Aspect | Previous (Failed) | Corrected |
|--------|-------------------|-----------|
| **Fallback** | `'en'` hardcoded | `apply_filters('wpml_default_language', null)` |
| **Locale Switch** | `wpml_switch_language` (WPML context only) | `switch_to_locale()` (loads textdomains) |
| **Gettext** | Didn't work (locale not switched) | Works (`__()` returns correct language) |
| **Early Exits** | Not restored | Restored before ALL exits |
| **Type Safety** | Assumed array | `is_array()` validation |

---

## Technical Implementation

### Language Fallback Chain (No Hardcoding)

```
1. $_POST['mx_lang'] (submitted from form)
   ↓ if empty/invalid
2. wpml_current_language (current page)
   ↓ if not in active_languages
3. wpml_default_language (site default)
   ↓ if WPML absent
4. Keep current locale (no-op)
```

**Never**: `'en'`, `'ar'`, `'en_US'`, or any literal language/locale

### Locale Switching

```php
// 1. Get WPML language code → WordPress locale
$form_locale = apply_filters('wpml_locale', '', $form_lang);
if (!$form_locale) {
    $form_locale = $active_langs[$form_lang]['default_locale'];
}

// 2. Switch locale (loads .mo files, __() now works)
if ($form_locale && function_exists('switch_to_locale')) {
    $switched = switch_to_locale($form_locale);
}

// 3. ALL translated strings (__()) now return correct language

// 4. Restore before exit
if ($switched && function_exists('restore_current_locale')) {
    restore_current_locale();
}
```

---

## Verification Commands

### Check No Hardcoded Languages

```bash
# Should return NO matches in mahx_contact_submit():
grep -n "'en'\|'ar'" theme-src/inc/contact.php | \
  grep -v "phpcs\|function\|content\|array\|//"
```

### Check Dynamic Fallback

```bash
# Should show 2+ matches:
grep -n "wpml_default_language" theme-src/inc/contact.php
```

### Check Locale Switching

```bash
# Should show switch_to_locale + 2 restore_current_locale:
grep -n "switch_to_locale\|restore_current_locale" theme-src/inc/contact.php
```

### Check Array Validation

```bash
# Should validate before accessing:
grep -n "is_array.*active_langs" theme-src/inc/contact.php
```

---

## Testing

### Critical Test (Reproduces Original Bug)

**Setup**: Site locale is Arabic (`ar`)  
**Action**:
1. Visit `/en/contact/` (English form)
2. Leave consent checkbox **unchecked**
3. Submit

**Expected Result**: **English** error message  
```
"Please confirm the consent box."
```

**Bug if**: Arabic message appears  
```
"يرجى تأكيد مربع الموافقة"
```

### Additional Tests

| Scenario | Expected Language |
|----------|-------------------|
| AR form → consent error | Arabic |
| EN form → name validation | English |
| FR form (if enabled) → error | French (or fallback) |
| EN form → nonce expired | English |
| EN form → success | English |

---

## WordPress/WPML APIs Used

| API | Purpose | Fallback |
|-----|---------|----------|
| `get_locale()` | Get current WordPress locale | Return empty string |
| `switch_to_locale($locale)` | Switch locale, load .mo files | Check `function_exists()` |
| `restore_current_locale()` | Restore previous locale | Check `function_exists()` |
| `apply_filters('wpml_default_language', null)` | Get site default language | Returns null if WPML absent |
| `apply_filters('wpml_active_languages', [])` | Get enabled languages | Returns empty array |
| `apply_filters('wpml_locale', '', $lang)` | Map lang code to locale | Returns empty string |

---

## Edge Cases

| Input | Behavior |
|-------|----------|
| `mx_lang` empty | Falls back to `wpml_current_language` |
| `mx_lang` is array | Rejected, falls back to default |
| Invalid language code | Falls back to `wpml_default_language` |
| Language disabled | Falls back to `wpml_default_language` |
| WPML not installed | No switch, keeps current locale |
| `switch_to_locale()` unavailable | No switch (WP <4.7) |

---

## Diff Summary

```bash
git diff 03a2392..1a1d6e3 theme-src/inc/contact.php
```

**Changes**:
- Lines 249-290: Locale capture, validation, fallback chain, switch
- Line 297: Restore in `$fail` closure (early exits)
- Lines 427-429: Restore before success exit
- **Removed**: Hardcoded `'en'` (2 occurrences)
- **Added**: `wpml_default_language` filter (2 occurrences)
- **Added**: `switch_to_locale()` + `restore_current_locale()`

---

## Download & Deploy

```bash
# Backup current files
cp inc/contact.php inc/contact.php.backup
cp page-contact.php page-contact.php.backup

# Download corrected files
wget -O inc/contact.php \
  https://raw.githubusercontent.com/MuhamedAhmedcom/theme/1a1d6e3/theme-src/inc/contact.php

wget -O page-contact.php \
  https://raw.githubusercontent.com/MuhamedAhmedcom/theme/1a1d6e3/theme-src/page-contact.php

# Verify hashes
sha256sum inc/contact.php
# Expected: 633b75f1be882a9740a582e64f4ba9e75c69e8d320bdc26a6d456221e77ae42f

sha256sum page-contact.php
# Expected: db904d2b357550bb5da07d4658111083a103cdfe247157ab1431e9c2143d274e

# Verify home.js unchanged
sha256sum assets/v18/home.js
# Expected: 8890cd59e251e3b818ee5f0bfc3cf23dcac4d5e06fbc3964ecc2a21697ada0e9
```

---

## Rollback

If issues:
```bash
cp inc/contact.php.backup inc/contact.php
cp page-contact.php.backup page-contact.php
# Clear caches, verify baseline hashes
```

---

## Summary

**Problem**: English form showed Arabic errors due to lost language context  

**Previous Attempt**: Used `wpml_switch_language` action (doesn't switch WordPress locale)  

**Corrected Solution**:
1. No hardcoded languages - use `wpml_default_language` filter
2. Use `switch_to_locale()` to actually switch locale and reload textdomains
3. Get locale from WPML via `wpml_locale` filter + `default_locale`
4. Restore on ALL exit paths
5. Validate types before access

**Result**: `__()` gettext calls now return correct language matching form

**Files**: 2 PHP files, 60 net lines added  
**Preserved**: home.js (previous fix)  
**Status**: Ready for production testing  

---

**PR**: https://github.com/MuhamedAhmedcom/theme/pull/1  
**Final Commit**: `1a1d6e3`  
**Review Gate**: Corrections applied, awaiting revalidation
