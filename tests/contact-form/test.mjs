/**
 * Contact form submission regression tests
 * Reproduces name="action" collision bug, then verifies fix
 * Network fully intercepted - NO actual POST requests sent
 */

import { chromium } from 'playwright';
import { readFileSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const projectRoot = join(__dirname, '../..');

// Read source files - buggy baseline from uploads
const uploadsDir = '/home/ubuntu/.cursor/projects/workspace/uploads';
const liveJS = readFileSync(join(uploadsDir, 'contact-final-live_fdec.js'), 'utf-8');
const liveHTML = readFileSync(join(uploadsDir, 'contact-final-live_2ed8.html'), 'utf-8');

let browser, context, page;
let interceptedRequests = [];

async function teardown() {
  if (page) await page.close();
  if (context) await context.close();
  if (browser) await browser.close();
}

async function testScenario(name, action, expectedPath, jsSource) {
  console.log(`\n▸ ${name}`);
  
  // Create HTML with the specific action for this test
  browser = await chromium.launch({ headless: true });
  context = await browser.newContext({
    locale: 'en-US',
    viewport: { width: 1280, height: 720 }
  });
  
  interceptedRequests = [];
  
  // Intercept ALL network requests
  await context.route('**/*', route => {
    const url = route.request().url();
    
    if (url.startsWith('data:') || url.startsWith('file:')) {
      return route.continue();
    }
    
    interceptedRequests.push({
      url,
      method: route.request().method(),
      postData: route.request().postData()
    });
    
    route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: { message: 'Test response' }
      })
    });
  });
  
  page = await context.newPage();
  
  // Set HTML with specific action
  await page.setContent(`
    <!DOCTYPE html>
    <html lang="en-US" dir="ltr">
    <head>
      <meta charset="UTF-8">
      <base href="https://muhamedahmed.com/en/contact/">
      <title>Contact Form Test</title>
    </head>
    <body class="mx-body">
      <div class="mx-root">
        <main id="content" class="mx-main" tabindex="-1">
          <form data-mx-form action="${action}" method="post">
            <input type="text" name="name" value="Test User" required>
            <input type="email" name="email" value="test@example.com" required>
            <textarea name="message" required>Test message</textarea>
            <input type="hidden" name="action" value="mahx_contact">
            <button type="submit">Send</button>
            <div data-mx-status></div>
          </form>
        </main>
      </div>
    </body>
    </html>
  `);
  
  // Execute the JS after DOM is ready
  await page.evaluate(jsSource);
  
  // Clear any previous requests and submit
  interceptedRequests = [];
  await page.click('button[type="submit"]');
  
  // Wait for fetch to complete
  await page.waitForTimeout(800);
  
  // Check for console errors
  const consoleErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') consoleErrors.push(msg.text());
  });
  
  // Verify intercepted request
  const postRequests = interceptedRequests.filter(r => r.method === 'POST');
  
  if (postRequests.length === 0) {
    console.log('  ✗ FAIL: No POST request intercepted');
    return false;
  }
  
  const request = postRequests[0];
  const requestURL = new URL(request.url);
  const actualPath = requestURL.pathname;
  
  // Verify WordPress action field is preserved in FormData
  const hasActionField = request.postData && request.postData.includes('name="action"') && request.postData.includes('mahx_contact');
  
  if (actualPath === expectedPath && hasActionField) {
    console.log(`  ✓ PASS: ${actualPath} (action field preserved)`);
    return true;
  } else {
    console.log(`  ✗ FAIL: Expected ${expectedPath}, got ${actualPath}`);
    if (!hasActionField) {
      console.log('  ✗ FAIL: WordPress action field missing from FormData');
    }
    return false;
  }
}

async function runTests() {
  console.log('Contact Form Submission Tests');
  console.log('==============================\n');
  
  console.log('Testing BUGGY version (reproducing reported bug)...');
  const buggyResults = [];
  
  // Scenario 1: English contact page (reported bug)
  buggyResults.push(await testScenario(
    'English /en/contact/ (SHOULD FAIL - reproducing bug)',
    '/en/contact/',
    '/en/contact/',
    liveJS
  ));
  
  // Scenario 2: Arabic root contact
  buggyResults.push(await testScenario(
    'Arabic /contact/ (SHOULD FAIL)',
    '/contact/',
    '/contact/',
    liveJS
  ));
  
  // Scenario 3: Arbitrary locale
  buggyResults.push(await testScenario(
    'French /fr/contact/ (SHOULD FAIL)',
    '/fr/contact/',
    '/fr/contact/',
    liveJS
  ));
  
  // Scenario 4: Absolute URL
  buggyResults.push(await testScenario(
    'Absolute URL (SHOULD FAIL)',
    'https://muhamedahmed.com/en/contact/',
    '/en/contact/',
    liveJS
  ));
  
  await teardown();
  
  const buggyFailCount = buggyResults.filter(r => !r).length;
  console.log(`\n📊 Buggy version: ${buggyFailCount}/${buggyResults.length} scenarios FAIL (expected behavior)`);
  
  if (buggyFailCount !== buggyResults.length) {
    console.log('⚠️  WARNING: Buggy version passed some tests unexpectedly');
  }
  
  console.log('\n' + '='.repeat(50));
  console.log('\nTesting FIXED version...');
  
  // Read fixed version
  const fixedJS = readFileSync(join(projectRoot, 'src/assets/v18/home.js'), 'utf-8');
  
  const fixedResults = [];
  
  // Same scenarios with fixed version
  fixedResults.push(await testScenario(
    'English /en/contact/ (SHOULD PASS)',
    '/en/contact/',
    '/en/contact/',
    fixedJS
  ));
  
  fixedResults.push(await testScenario(
    'Arabic /contact/ (SHOULD PASS)',
    '/contact/',
    '/contact/',
    fixedJS
  ));
  
  fixedResults.push(await testScenario(
    'French /fr/contact/ (SHOULD PASS)',
    '/fr/contact/',
    '/fr/contact/',
    fixedJS
  ));
  
  fixedResults.push(await testScenario(
    'Absolute URL (SHOULD PASS)',
    'https://muhamedahmed.com/en/contact/',
    '/en/contact/',
    fixedJS
  ));
  
  // Scenario 5: Empty action (should use document base URL)
  fixedResults.push(await testScenario(
    'Empty action="" (SHOULD PASS - use base URL)',
    '',
    '/en/contact/',
    fixedJS
  ));
  
  await teardown();
  
  const fixedPassCount = fixedResults.filter(r => r).length;
  console.log(`\n📊 Fixed version: ${fixedPassCount}/${fixedResults.length} scenarios PASS`);
  
  if (fixedPassCount === fixedResults.length) {
    console.log('\n✅ ALL TESTS PASSED - Fix verified!');
    process.exit(0);
  } else {
    console.log('\n❌ SOME TESTS FAILED - Fix incomplete');
    process.exit(1);
  }
}

runTests().catch(err => {
  console.error('Test error:', err);
  process.exit(1);
});
