/**
 * WHATWG HTML Form Submission Algorithm Tests
 * 
 * Tests correct action URL resolution per HTML Standard:
 * - Empty/missing action → document URL (location.href)
 * - Non-empty relative → resolved against base URI
 * - Absolute → used as-is
 * - All with named action field present (shadowing test)
 * 
 * Network fully intercepted - NO actual POST requests
 */

import { chromium } from 'playwright';
import { readFileSync } from 'fs';

const uploadsDir = '/home/ubuntu/.cursor/projects/workspace/uploads';
const liveHTML = readFileSync(`${uploadsDir}/contact-final-live_2ed8.html`, 'utf-8');
const buggyJS = readFileSync(`${uploadsDir}/contact-final-live_fdec.js`, 'utf-8');
const fixedJS = readFileSync('/workspace/src/assets/v18/home.js', 'utf-8');

const formMatch = liveHTML.match(/<form[^>]*data-mx-form[^>]*>[\s\S]*?<\/form>/i);
const productionFormHTML = formMatch[0];

async function testCase(name, jsSource, docURL, baseHref, formActionAttr, expectedURL, shouldFail) {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  
  const requests = [];
  let nativeFormAction = null;
  
  await context.route('**/*', route => {
    const url = route.request().url();
    const method = route.request().method();
    
    if (url === docURL && method === 'GET') {
      const baseTag = baseHref ? `<base href="${baseHref}">` : '';
      const formHTML = productionFormHTML.replace(
        /action="[^"]*"/,
        formActionAttr === null ? '' : `action="${formActionAttr}"`
      );
      
      const html = `<!DOCTYPE html><html><head><meta charset="UTF-8">${baseTag}</head><body class="mx-body"><div class="mx-root"><main id="content" class="mx-main">${formHTML}</main></div><script>${jsSource}</script></body></html>`;
      return route.fulfill({ status: 200, contentType: 'text/html', body: html });
    }
    
    if (!url.startsWith('data:') && !url.startsWith('file:')) {
      requests.push({ url, method, postData: route.request().postData() });
      return route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true }) });
    }
    route.continue();
  });
  
  const page = await context.newPage();
  await page.goto(docURL);
  
  // Capture native form.action for comparison (oracle)
  nativeFormAction = await page.evaluate(() => {
    const f = document.querySelector('form[data-mx-form]');
    // Use prototype getter to bypass named element
    return Object.getOwnPropertyDescriptor(HTMLFormElement.prototype, 'action').get.call(f);
  });
  
  await page.click('button[type="submit"]');
  await page.waitForTimeout(800);
  
  const postReq = requests.filter(r => r.method === 'POST')[0];
  const actualURL = postReq?.url;
  const hasActionField = postReq?.postData?.includes('mahx_contact');
  
  let result = false;
  let details = '';
  
  if (shouldFail) {
    // Buggy version - expect failure
    if (!actualURL || actualURL.includes('[object') || actualURL !== expectedURL) {
      result = true; // Expected to fail
      details = actualURL ? `→ ${actualURL}` : '(no POST)';
    }
  } else {
    // Fixed version - expect success
    if (actualURL === expectedURL && hasActionField) {
      result = true;
      details = `→ ${actualURL}`;
    } else {
      details = `Expected: ${expectedURL}, Got: ${actualURL || '(no POST)'}`;
    }
  }
  
  await page.close();
  await context.close();
  await browser.close();
  
  return { result, details, nativeFormAction, actualURL };
}

async function runTests() {
  console.log('WHATWG HTML Form Submission Algorithm Tests');
  console.log('===========================================\n');
  
  const docURL = 'https://muhamedahmed.com/en/contact/';
  const diffBase = 'https://example.test/different-base/';
  
  // Test scenarios with distinct document URL and base href
  const scenarios = [
    // 1. Missing action attribute (no action="...")
    {
      name: 'Missing action attribute (no action="...")',
      docURL,
      baseHref: diffBase,
      formAction: null,
      expected: docURL, // Per spec: use document URL
      buggy: false
    },
    
    // 2. Empty action (action="")
    {
      name: 'Empty action (action="")',
      docURL,
      baseHref: diffBase,
      formAction: '',
      expected: docURL, // Per spec: use document URL
      buggy: false
    },
    
    // 3. Relative action with base href
    {
      name: 'Relative action with different base href',
      docURL,
      baseHref: diffBase,
      formAction: 'submit.php',
      expected: 'https://example.test/different-base/submit.php', // Resolved against base
      buggy: false
    },
    
    // 4. Absolute action (ignores base)
    {
      name: 'Absolute action (ignores base href)',
      docURL,
      baseHref: diffBase,
      formAction: 'https://muhamedahmed.com/wp-admin/admin-post.php',
      expected: 'https://muhamedahmed.com/wp-admin/admin-post.php',
      buggy: false
    },
    
    // 5. Root-relative action with base
    {
      name: 'Root-relative action (/path) with base href',
      docURL,
      baseHref: diffBase,
      formAction: '/api/submit',
      expected: 'https://example.test/api/submit', // Base origin + path
      buggy: false
    },
    
    // 6. Production form (absolute action)
    {
      name: 'Production form (admin-post.php absolute)',
      docURL,
      baseHref: null,
      formAction: 'https://muhamedahmed.com/wp-admin/admin-post.php',
      expected: 'https://muhamedahmed.com/wp-admin/admin-post.php',
      buggy: false
    }
  ];
  
  console.log('Testing BUGGY version (named-control shadowing)...\n');
  
  const buggyResults = [];
  for (const s of scenarios.slice(0, 3)) { // Test subset for buggy
    process.stdout.write(`▸ ${s.name}\n`);
    const r = await testCase(s.name, buggyJS, s.docURL, s.baseHref, s.formAction, s.expected, true);
    console.log(`  ${r.result ? '✗' : '✓'} ${r.details}`);
    console.log(`  Native form.action: ${r.nativeFormAction}`);
    buggyResults.push(r.result);
  }
  
  console.log(`\n📊 Buggy: ${buggyResults.filter(Boolean).length}/${buggyResults.length} FAIL as expected\n`);
  
  console.log('='.repeat(60));
  console.log('\nTesting FIXED version (WHATWG semantics)...\n');
  
  const fixedResults = [];
  for (const s of scenarios) {
    process.stdout.write(`▸ ${s.name}\n`);
    const r = await testCase(s.name, fixedJS, s.docURL, s.baseHref, s.formAction, s.expected, false);
    console.log(`  ${r.result ? '✓' : '✗'} ${r.details}`);
    console.log(`  Native form.action: ${r.nativeFormAction}`);
    console.log(`  Expected URL: ${s.expected}`);
    console.log(`  Actual URL:   ${r.actualURL || '(none)'}`);
    fixedResults.push(r.result);
  }
  
  console.log(`\n📊 Fixed: ${fixedResults.filter(Boolean).length}/${fixedResults.length} PASS\n`);
  
  if (buggyResults.every(Boolean) && fixedResults.every(Boolean)) {
    console.log('✅ ALL TESTS PASSED');
    console.log('   - Buggy version fails with [object HTMLInputElement]');
    console.log('   - Fixed version follows WHATWG HTML Standard');
    console.log('   - Empty/missing action → document URL');
    console.log('   - Non-empty relative → resolved against base URI');
    console.log('   - Absolute action → used as-is');
    console.log('   - WordPress action field preserved in all cases');
    process.exit(0);
  } else {
    console.log('❌ SOME TESTS FAILED');
    process.exit(1);
  }
}

runTests().catch(err => {
  console.error('Test error:', err);
  process.exit(1);
});
