import { chromium } from 'playwright';
import { readFileSync } from 'fs';

const fixedJS = readFileSync('/workspace/src/assets/v18/home.js', 'utf-8');

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext();
const page = await context.newPage();

let requests = [];
await context.route('**/*', route => {
  const url = route.request().url();
  if (!url.startsWith('data:') && !url.startsWith('file:')) {
    requests.push({ url, method: route.request().method() });
    return route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { message: 'OK' } })
    });
  }
  route.continue();
});

await page.setContent(`
<!DOCTYPE html>
<html><head>
<base href="https://muhamedahmed.com/en/contact/">
</head><body class="mx-body">
<div class="mx-root">
<form data-mx-form action="" method="post">
  <input name="name" value="Test">
  <input name="email" value="test@test.com">
  <input type="hidden" name="action" value="mahx_contact">
  <button type="submit">Send</button>
  <div data-mx-status></div>
</form>
</div>
</body></html>
`);

page.on('console', msg => console.log('CONSOLE:', msg.text()));

await page.evaluate(fixedJS);

// Check form action resolution
const actionInfo = await page.evaluate(() => {
  const f = document.querySelector('form[data-mx-form]');
  return {
    getAttribute: f.getAttribute('action'),
    property: typeof f.action,
    locationHref: location.href,
    baseURI: document.baseURI
  };
});
console.log('Form action info:', actionInfo);

console.log('Clicking submit...');
await page.click('button[type="submit"]');
await page.waitForTimeout(2000);

console.log('Intercepted requests:', requests);
await browser.close();
