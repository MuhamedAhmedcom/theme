# WPML Contact Form Fix - Deployment Checklist

## Pre-Deployment

- [ ] **Backup current theme files**
  ```bash
  cp inc/contact.php inc/contact.php.backup
  cp page-contact.php page-contact.php.backup
  ```

- [ ] **Verify baseline hashes match**
  ```bash
  sha256sum inc/contact.php    # Should be: 1185c1bd45...
  sha256sum page-contact.php   # Should be: 9027dd115b...
  ```

- [ ] **Verify home.js unchanged**
  ```bash
  sha256sum assets/v18/home.js # Should be: 8890cd59e2... ✅
  ```

## Deployment Steps

1. **Upload fixed files** (preserve directory structure)
   ```
   theme-src/inc/contact.php       → wp-content/themes/{theme}/inc/
   theme-src/page-contact.php      → wp-content/themes/{theme}/
   ```

2. **Verify file permissions**
   ```bash
   chmod 644 inc/contact.php page-contact.php
   ```

3. **Clear relevant caches**
   - **PHP OPcache**: Restart PHP-FPM or wait for TTL
   - **FlyingPress**: Purge `/en/contact/` and `/contact/` pages
   - **Browser**: Hard refresh or incognito for testing

4. **Verify uploaded hashes**
   ```bash
   sha256sum inc/contact.php    # Should be: 3ef987fb8c...
   sha256sum page-contact.php   # Should be: db904d2b35...
   ```

## Post-Deployment Testing

### Quick Smoke Test (5 min)

1. **English form, Arabic site**
   - Visit: `https://muhamedahmed.com/en/contact/`
   - Inspect HTML: verify `<input type="hidden" name="mx_lang" value="en">`
   - Submit with consent **unchecked**
   - **Expected**: "Please confirm the consent box." (English)
   - **Bug if**: Shows Arabic "يرجى تأكيد مربع الموافقة"

2. **Arabic form**
   - Visit: `https://muhamedahmed.com/contact/`
   - Inspect HTML: verify `<input type="hidden" name="mx_lang" value="ar">`
   - Submit with consent **unchecked**
   - **Expected**: "يرجى تأكيد مربع الموافقة" (Arabic)

### Full Test Matrix (15 min)

Run all 10 test scenarios from `WPML-LANGUAGE-FIX.md` § Manual Testing Required.

Key scenarios:
- [ ] EN form → EN errors (nonce, validation, consent)
- [ ] AR form → AR errors
- [ ] EN form with admin logged in as AR → EN errors
- [ ] Valid submission → success in correct language
- [ ] Old cached form (if any) → graceful fallback

### DevTools Inspection

1. **Network tab**: Verify POST includes `mx_lang` parameter
2. **Console**: No JavaScript errors
3. **Response**: Check error/success message language

## Rollback Procedure

If any issues:

1. **Restore backups immediately**
   ```bash
   cp inc/contact.php.backup inc/contact.php
   cp page-contact.php.backup page-contact.php
   ```

2. **Clear caches** (same as deployment step 3)

3. **Verify rollback hashes**
   ```bash
   sha256sum inc/contact.php    # Should be: 1185c1bd45...
   sha256sum page-contact.php   # Should be: 9027dd115b...
   ```

## Known Limitations

- **Cache**: Old HTML without `mx_lang` field will fall back to current/site language
- **WPML Required**: Fix assumes WPML active; gracefully no-ops if disabled
- **Manual Testing**: Unit tests validate logic only, not live WPML runtime
- **No Emails**: Testing should avoid actual email dispatch (use staging/dev)

## Verification Points

After deployment, confirm:
- ✅ English form shows English errors
- ✅ Arabic form shows Arabic errors
- ✅ Form language overrides site/admin/user locale
- ✅ No PHP warnings in error logs
- ✅ No JavaScript console errors
- ✅ FormData contains WordPress `action=mahx_contact`
- ✅ Nonce validation still works
- ✅ Success messages in correct language
- ✅ Original home.js (8890cd59...) unchanged

## Support Files

- **Documentation**: `WPML-LANGUAGE-FIX.md`
- **Deployment Archive**: `wpml-contact-fix-deployment.tar.gz`
- **Test Suite**: `tests/wpml-contact/test-language-context.php`
- **Diffs**: Available in git commit `ea3e8e3`
- **PR**: https://github.com/MuhamedAhmedcom/theme/pull/1

## Timing

- **Deployment window**: Off-peak hours recommended
- **Estimated downtime**: None (hot-swap compatible)
- **Cache propagation**: 5-15 minutes for FlyingPress
- **Testing duration**: 15-20 minutes full matrix

## Contact

For issues or questions during deployment, reference:
- Git commit: `ea3e8e3`
- PR: #1
- Files: `inc/contact.php`, `page-contact.php`
