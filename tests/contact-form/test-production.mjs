/**
 * Contact form submission tests with ACTUAL production HTML
 * Tests real admin-post.php endpoint with named-control shadowing bug
 * Network fully intercepted - NO actual POST requests sent
 */

import { chromium } from 'playwright';
import { readFileSync } from 'fs';

const uploadsDir = '/home/ubuntu/.cursor/projects/workspace/uploads';
const liveHTML = readFileSync(`${uploadsDir}/contact-final-live_2ed8.html`, 'utf-8');
const buggyJS = readFileSync(`${uploadsDir}/contact-final-live_fdec.js`, 'utf-8');
const fixedJS = readFileSync('/workspace/src/assets/v18/home.js', 'utf-8');

// Extract form from production HTML
const formMatch = liveHTML.match(/<form[^>]*data-mx-form[^>]*>[\s\S]*?<\/form>/i);
if (!formMatch) {
  console.error('ERROR: Could not extract form from production HTML');
  process.exit(1);
}
const productionFormHTML = formMatch[0];

// Extract actual form action
const actionMatch = productionFormHTML.match(/action="([^"]*)"/i);
const ACTUAL_FORM_ACTION = actionMatch ? actionMatch[1] : '';

if (!ACTUAL_FORM_ACTION.includes('admin-post.php')) {
  console.error('ERROR: Expected admin-post.php in form action, got:', ACTUAL_FORM_ACTION);
  process.exit(1);
}

async function testScenario(name, jsSource, pageURL, formHTML, expectedURL) {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  
  const requests = [];
  await context.route('**/*', route => {
    const url = route.request().url();
    
    if (url === pageURL) {
      const html = `<!DOCTYPE html><html lang="en-US" dir="ltr"><head><meta charset="UTF-8"></head><body class="mx-body"><div class="mx-root"><main id="content" class="mx-main" tabindex="-1">${formHTML}</main></div><script>${jsSource}</script></body></html>`;
      return route.fulfill({ status: 200, contentType: 'text/html', body: html });
    }
    
    if (!url.startsWith('data:') && !url.startsWith('file:')) {
      requests.push({ url, method: route.request().method(), postData: route.request().postData() });
      return route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, data: { message: 'OK' } }) });
    }
    route.continue();
  });
  
  const page = await context.newPage();
  await page.goto(pageURL);
  await page.click('button[type="submit"]');
  await page.waitForTimeout(800);
  
  const postRequests = requests.filter(r => r.method === 'POST');
  let result = false;
  
  if (postRequests.length === 0) {
    console.log(`  ✗ FAIL: No POST request intercepted`);
  } else {
    const actualURL = postRequests[0].url;
    const hasActionField = postRequests[0].postData?.includes('mahx_contact');
    
    if (actualURL === expectedURL && hasActionField) {
      console.log(`  ✓ PASS: ${actualURL} (action preserved)`);
      result = true;
    } else {
      console.log(`  ✗ FAIL: Expected ${expectedURL}, got ${actualURL}`);
      if (!hasActionField) console.log(`  ✗ FAIL: WordPress action field missing`);
    }
  }
  
  await page.close();
  await context.close();
  await browser.close();
  return result;
}

async function runTests() {
  console.log('Contact Form Production HTML Integration Tests\n==============================================\n');
  console.log('ACTUAL PRODUCTION FORM ACTION:', ACTUAL_FORM_ACTION, '\n');
  
  const buggyResults = [];
  const fixedResults = [];
  
  console.log('Testing BUGGY version (reproducing bug)...\n');
  
  console.log('▸ English page with production form (SHOULD FAIL)');
  buggyResults.push(await testScenario('English', buggyJS, 'https://muhamedahmed.com/en/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  console.log('▸ Arabic root with production form (SHOULD FAIL)');
  buggyResults.push(await testScenario('Arabic', buggyJS, 'https://muhamedahmed.com/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  console.log('▸ Arbitrary locale with production form (SHOULD FAIL)');
  buggyResults.push(await testScenario('French', buggyJS, 'https://muhamedahmed.com/fr/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  const buggyFailCount = buggyResults.filter(r => !r).length;
  console.log(`\n📊 Buggy version: ${buggyFailCount}/${buggyResults.length} scenarios FAIL (expected)\n`);
  
  console.log('='.repeat(60));
  console.log('\nTesting FIXED version...\n');
  
  console.log('▸ English page with production form (SHOULD PASS)');
  fixedResults.push(await testScenario('English', fixedJS, 'https://muhamedahmed.com/en/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  console.log('▸ Arabic root with production form (SHOULD PASS)');
  fixedResults.push(await testScenario('Arabic', fixedJS, 'https://muhamedahmed.com/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  console.log('▸ Arbitrary locale with production form (SHOULD PASS)');
  fixedResults.push(await testScenario('French', fixedJS, 'https://muhamedahmed.com/fr/contact/', productionFormHTML, ACTUAL_FORM_ACTION));
  
  console.log('▸ Relative action URL (SHOULD PASS)');
  const relativeFormHTML = productionFormHTML.replace(/action="[^"]*"/, 'action="/wp-admin/admin-post.php"');
  fixedResults.push(await testScenario('Relative', fixedJS, 'https://muhamedahmed.com/en/contact/', relativeFormHTML, 'https://muhamedahmed.com/wp-admin/admin-post.php'));
  
  console.log('▸ Empty action with base href (SHOULD PASS)');
  const emptyFormHTML = productionFormHTML.replace(/action="[^"]*"/, 'action=""');
  const browser2 = await chromium.launch({ headless: true });
  const context2 = await browser2.newContext();
  const requests2 = [];
  
  await context2.route('**/*', route => {
    const url = route.request().url();
    const method = route.request().method();
    
    if (url === 'https://muhamedahmed.com/en/contact/') {
      if (method === 'GET') {
        const html = `<!DOCTYPE html><html lang="en-US" dir="ltr"><head><meta charset="UTF-8"><base href="https://muhamedahmed.com/en/contact/"></head><body class="mx-body"><div class="mx-root"><main id="content" class="mx-main" tabindex="-1">${emptyFormHTML}</main></div><script>${fixedJS}</script></body></html>`;
        return route.fulfill({ status: 200, contentType: 'text/html', body: html });
      } else {
        requests2.push({ url, method });
        return route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true }) });
      }
    }
    
    if (!url.startsWith('data:') && !url.startsWith('file:')) {
      requests2.push({ url, method });
      return route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true }) });
    }
    route.continue();
  });
  
  const page2 = await context2.newPage();
  await page2.goto('https://muhamedahmed.com/en/contact/');
  await page2.click('button[type="submit"]');
  await page2.waitForTimeout(800);
  
  const emptyResult = requests2.filter(r => r.method === 'POST')[0]?.url === 'https://muhamedahmed.com/en/contact/';
  if (emptyResult) {
    console.log('  ✓ PASS: https://muhamedahmed.com/en/contact/ (uses base URI)');
  } else {
    console.log('  ✗ FAIL: Empty action did not resolve to base URI');
  }
  fixedResults.push(emptyResult);
  
  await page2.close();
  await context2.close();
  await browser2.close();
  
  const fixedPassCount = fixedResults.filter(r => r).length;
  console.log(`\n📊 Fixed version: ${fixedPassCount}/${fixedResults.length} scenarios PASS\n`);
  
  if (buggyFailCount === buggyResults.length && fixedPassCount === fixedResults.length) {
    console.log('✅ ALL TESTS PASSED\n   - Bug reproduced with production HTML + admin-post.php\n   - Fix verified with actual endpoint\n   - WordPress action field preserved\n   - Multiple locales working\n   - Relative URLs working\n   - Empty action resolves per HTML spec');
    process.exit(0);
  } else {
    console.log('❌ TESTS FAILED');
    process.exit(1);
  }
}

runTests().catch(err => {
  console.error('Test error:', err);
  process.exit(1);
});
