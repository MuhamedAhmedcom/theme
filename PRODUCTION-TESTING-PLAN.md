# WPML Contact Form - Production Testing Plan

## Overview

This fix addresses validation errors showing in the wrong language when form language differs from site default.

**Symptom**: English form at `/en/contact/` displays Arabic server errors  
**Root Cause**: admin-post.php handler loses WPML language context  
**Fix**: Propagate language from form render through handler using WPML official APIs

## Testing Environment

- **Site**: muhamedahmed.com
- **WPML Active Languages**: Arabic (default), English
- **Site Locale**: Arabic (`ar`)
- **Test URLs**:
  - English: `https://muhamedahmed.com/en/contact/`
  - Arabic: `https://muhamedahmed.com/contact/`

## Pre-Testing Verification

### 1. Inspect Form HTML Source

Visit `/en/contact/` and view page source, find:

```html
<input type="hidden" name="mx_lang" value="en">
```

**Expected**: `value="en"` for English form  
**If Missing**: Cache not cleared or files not deployed

Visit `/contact/` (Arabic), find:

```html
<input type="hidden" name="mx_lang" value="ar">
```

**Expected**: `value="ar"` for Arabic form

### 2. Browser DevTools Network Tab

1. Open DevTools → Network
2. Submit form (trigger validation error)
3. Find POST to `admin-post.php`
4. Check **Form Data** includes:
   ```
   action: mahx_contact
   mx_lang: en (or ar)
   mx_nonce: [...]
   ```

**Expected**: `mx_lang` parameter present in POST

## Test Matrix

Run all scenarios. Mark ✅ PASS or ❌ FAIL with notes.

### Test 1: English Form - Consent Error (Critical)

**Setup**: Site locale AR, Admin logged in as AR or guest  
**URL**: `/en/contact/`  
**Action**:
1. Fill name: "Test User"
2. Fill email: "test@example.com"
3. Leave consent checkbox **unchecked**
4. Submit

**Expected Result**:
- Error message: **"Please confirm the consent box."** (English)
- NOT: "يرجى تأكيد مربع الموافقة" (Arabic)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL  
**Notes**: ___________________________________________

---

### Test 2: English Form - Name Validation

**URL**: `/en/contact/`  
**Action**:
1. Leave name **empty**
2. Fill email: "test@example.com"
3. Check consent
4. Submit

**Expected Result**:
- Error message: **"Please enter your name."** (English)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL  
**Notes**: ___________________________________________

---

### Test 3: English Form - General Error

**URL**: `/en/contact/`  
**Action**:
1. Empty name, invalid email
2. Submit

**Expected Result**:
- General error: **"Please check the highlighted fields."** (English)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL  
**Notes**: ___________________________________________

---

### Test 4: Arabic Form - Consent Error

**URL**: `/contact/` (Arabic root)  
**Action**:
1. Fill name, email
2. Leave consent **unchecked**
3. Submit

**Expected Result**:
- Error message: **"يرجى تأكيد مربع الموافقة"** (Arabic)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL  
**Notes**: ___________________________________________

---

### Test 5: English Form - Success Message

**URL**: `/en/contact/`  
**Action**:
1. Fill all required fields correctly
2. Check consent
3. Submit (if test mode allows)

**Expected Result**:
- Success message in **English**
- NO Arabic text

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ SKIPPED (live mail)  
**Notes**: ___________________________________________

---

### Test 6: Arabic Form - Success Message

**URL**: `/contact/`  
**Action**: Valid submission

**Expected Result**:
- Success message in **Arabic**

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ SKIPPED (live mail)  
**Notes**: ___________________________________________

---

### Test 7: Nonce Expiry (Any Language)

**URL**: `/en/contact/`  
**Action**:
1. Load form
2. Wait 12+ hours OR manually corrupt nonce in DevTools
3. Submit

**Expected Result**:
- Error: **"Your session expired. Please reload the page and send again."**
- In **English** (form language)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ SKIPPED  
**Notes**: ___________________________________________

---

### Test 8: Admin with Different Locale

**Setup**: Logged in as admin with AR locale  
**URL**: `/en/contact/`  
**Action**: Trigger validation error (unchecked consent)

**Expected Result**:
- Error in **English** (form language overrides admin locale)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL  
**Notes**: ___________________________________________

---

### Test 9: French or Other Language (If Enabled)

**URL**: `/fr/contact/` (if French enabled)  
**Action**: Trigger validation error

**Expected Result**:
- Error in **French** (or fallback to English if translations missing)

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ NOT APPLICABLE  
**Notes**: ___________________________________________

---

### Test 10: Old Cached Form (Graceful Degradation)

**Setup**: If cache serves old HTML without `mx_lang` field  
**Action**: Submit from cached form

**Expected Result**:
- Falls back to site default language OR current language
- Does NOT crash
- NO PHP warnings

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ CACHE CLEARED  
**Notes**: ___________________________________________

---

### Test 11: WPML Disabled (Edge Case)

**Setup**: Temporarily disable WPML (staging/dev only)  
**Action**: Submit form

**Expected Result**:
- Form works normally with WordPress default locale
- NO PHP errors

**Result**: [ ] ✅ PASS / [ ] ❌ FAIL / [ ] ⚠️ SKIPPED  
**Notes**: ___________________________________________

---

## Error Checking

During all tests, monitor:

### PHP Error Logs

```bash
tail -f /path/to/php-error.log
```

**Expected**: No warnings, no fatal errors  
**Check for**: `Undefined variable`, `WPML not found`, etc.

### Browser Console

**Expected**: No JavaScript errors  
**Check for**: Network failures, console warnings

### Server Response

**Expected**:
- 200 OK for valid submissions
- Redirects to `?mx_sent=1` or `?mx_cf=...`
- NO 500 Internal Server Error

## Success Criteria

Fix is considered **SUCCESSFUL** if:

- ✅ Tests 1-3 (English errors) show English text
- ✅ Test 4 (Arabic error) shows Arabic text
- ✅ Test 8 (admin locale override) works correctly
- ✅ No PHP errors in logs
- ✅ No JavaScript console errors
- ✅ Success messages match form language
- ✅ FormData still contains `action=mahx_contact`

## Failure Scenarios

If ANY test fails:

1. **Immediate Action**: Document exact error message and screenshot
2. **Check**:
   - Are files deployed correctly? (verify hashes)
   - Is cache cleared?
   - Is `mx_lang` field present in HTML?
   - Is `mx_lang` in POST data?
   - Any PHP warnings?

3. **Rollback Decision**:
   - Single test fails: Investigate, may be edge case
   - Critical test (1, 3, 4, 8) fails: Consider rollback
   - PHP errors: Immediate rollback

4. **Rollback**: Follow `DEPLOYMENT-CHECKLIST.md` § Rollback Procedure

## Rollback Indicators

Rollback **REQUIRED** if:
- ❌ Test 1 fails (English form still shows Arabic errors)
- ❌ PHP fatal error or warnings
- ❌ Form submissions fail completely
- ❌ Success messages in wrong language

Rollback **OPTIONAL** (investigate) if:
- ⚠️ Only edge case (Test 10, 11) fails
- ⚠️ French/other language fallback behavior differs
- ⚠️ Cached forms show old behavior (clear cache, retest)

## Post-Testing Report

After completing all tests, generate report:

```
=== WPML Contact Form Fix - Test Results ===

Date: [YYYY-MM-DD]
Tester: [Name]
Environment: Production / Staging

Tests Passed: __/11
Tests Failed: __/11
Tests Skipped: __/11

Critical Tests (1, 3, 4, 8):
- [ ] All passed
- [ ] Some failed (ROLLBACK REQUIRED)

PHP Errors: [ ] None / [ ] Warnings (details: ___)

Recommendation:
- [ ] Fix APPROVED for production
- [ ] Rollback REQUIRED
- [ ] Further investigation needed

Notes:
___________________________________________
___________________________________________
```

## Support

- **Documentation**: `WPML-LANGUAGE-FIX.md`
- **Deployment**: `DEPLOYMENT-CHECKLIST.md`
- **PR**: https://github.com/MuhamedAhmedcom/theme/pull/1
- **Commit**: `9b43039`
