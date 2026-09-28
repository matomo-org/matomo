/*!
 * Matomo - free/libre analytics platform
 *
 * Bridge between Playwright Test and Matomo's PHP test environment. It reuses what the Mocha harness
 * uses (tests:setup-fixture and tmp/testingPathOverride.json), so no PHP changes are needed.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { expect } = require('@playwright/test');
const {
  DEFAULT_FIXTURE, MATOMO_URL, prepareFixture, readEnvironment, writeEnvironment,
} = require('./fixtures');

// same defaults as TestingEnvironment.reload() in tests/lib/screenshot-testing/support/test-environment.js
const ENV_DEFAULTS = {
  useOverrideCss: true,
  useOverrideJs: true,
  loadRealTranslations: true,
  testUseMockAuth: true,
  configOverride: {},
  optionsOverride: {},
  environmentVariables: {},
};

/**
 * Restores the fixture database for a spec (setting it up on first use). Tests change persisted state
 * (report preferences, options, users), so without this a spec depends on which specs ran before it.
 */
function restoreFixture(fixtureClass = DEFAULT_FIXTURE) {
  writeEnvironment({ ...ENV_DEFAULTS, ...prepareFixture({ fixtureClass }) });
}

/** Changes the environment for the following requests, like assigning testEnvironment.x and calling save(). */
function updateEnvironment(changes) {
  writeEnvironment({ ...ENV_DEFAULTS, ...readEnvironment(), ...changes });
}

// Known product bugs that would otherwise fail unrelated tests at random:
// - Dashboard.ts and dashboardObject.js never handle the rejection widgetMenu.js raises when a
//   navigation aborts the widget metadata request
const KNOWN_PAGE_ERRORS = [/^Loading widget metadata was aborted$/];

const PAGE_SETUP = `(() => {
  const setup = () => {
    document.documentElement.classList.add('uiTest');
    if (window.jQuery) {
      window.jQuery.fx.off = true;
    }
    const style = document.createElement('style');
    style.textContent = '* { caret-color: transparent !important; transition: none !important; animation: none !important; }'
      + ' .modal.open { transform: none !important; opacity: 1 !important; }';
    document.head.appendChild(style);
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setup);
  } else {
    setup();
  }
})();`;

/**
 * Opens a page that is shared by the tests of a serial describe block, since most Matomo UI tests
 * continue from the page the previous test left behind.
 *
 * The page fails fast instead of taking screenshots of broken states:
 * - requests to hosts other than the Matomo under test are aborted and reported
 * - HTTP 5xx responses and uncaught page errors are collected, and assertClean() fails the test,
 *   unless the test opted out with allowServerErrors()
 */
async function openSession(browser, contextOptions = {}) {
  const context = await browser.newContext(contextOptions);
  await context.addInitScript(PAGE_SETUP);

  const problems = [];
  const pending = new Set();
  let allowErrors = false;

  await context.route('**/*', (route) => {
    const url = new URL(route.request().url());
    const local = url.host === MATOMO_URL.host || ['localhost', '127.0.0.1'].includes(url.hostname);
    if (local || url.protocol === 'data:' || url.protocol === 'blob:') {
      return route.continue();
    }
    problems.push(`blocked external request: ${url.href}`);
    return route.abort('blockedbyclient');
  });

  const page = await context.newPage();
  page.on('request', (request) => pending.add(request));
  page.on('requestfinished', (request) => pending.delete(request));
  page.on('requestfailed', (request) => pending.delete(request));
  // requests of the previous document never finish once the main frame navigates away
  page.on('framenavigated', (frame) => {
    if (frame === page.mainFrame()) {
      for (const request of pending) {
        if (request.frame() === frame && !request.isNavigationRequest()) {
          pending.delete(request);
        }
      }
    }
  });
  page.on('response', (response) => {
    if (response.status() >= 500 && !allowErrors) {
      problems.push(`HTTP ${response.status()} ${response.url()}`);
    }
  });
  page.on('pageerror', (error) => {
    if (!allowErrors && !KNOWN_PAGE_ERRORS.some((known) => known.test(error.message))) {
      problems.push(`page error: ${(error.stack || error.message).split('\n').slice(0, 3).join(' | ')}`);
    }
  });

  return {
    page,
    context,
    /** Waits until no request has been in flight for 250 ms. */
    async waitForIdle(timeout = 30_000) {
      const start = Date.now();
      let quietSince = pending.size ? 0 : Date.now();
      while (Date.now() - start < timeout) {
        await page.waitForTimeout(50);
        if (pending.size) {
          quietSince = 0;
        } else if (!quietSince) {
          quietSince = Date.now();
        } else if (Date.now() - quietSince >= 250) {
          return;
        }
      }
      throw new Error(`requests still pending after ${timeout} ms: ${[...pending].map((r) => r.url()).join(', ')}`);
    },
    allowServerErrors(allow = true) {
      allowErrors = allow;
    },
    assertClean() {
      const found = problems.splice(0);
      expect(found, 'the page produced server errors, page errors or external requests').toEqual([]);
    },
    async close() {
      await context.close();
    },
  };
}

/**
 * Screenshot of the area covering all elements matched by the given selectors, like the Mocha
 * harness's screenshotSelector('a,b'), so absolutely positioned dropdowns are included. The area is
 * measured only once the layout has stopped moving, because a fixed clip can't follow a late shift.
 */
async function expectAreaScreenshot(session, selectors, name, options = {}) {
  const { page } = session;
  await session.waitForIdle();

  const measure = () => page.evaluate(async (list) => {
    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
    const rects = list.flatMap((selector) => [...document.querySelectorAll(selector)])
      .map((element) => element.getBoundingClientRect())
      .filter((rect) => rect.width && rect.height);
    if (!rects.length) {
      return null;
    }
    // rounded inwards: a partly covered edge pixel would show whatever the page renders behind it
    const left = Math.ceil(Math.min(...rects.map((r) => r.left)) + window.scrollX);
    const top = Math.ceil(Math.min(...rects.map((r) => r.top)) + window.scrollY);
    const right = Math.floor(Math.max(...rects.map((r) => r.right)) + window.scrollX);
    const bottom = Math.floor(Math.max(...rects.map((r) => r.bottom)) + window.scrollY);
    return { x: left, y: top, width: right - left, height: bottom - top };
  }, selectors);

  let clip = await measure();
  for (let attempt = 0; attempt < 20; attempt += 1) {
    const next = await measure();
    if (JSON.stringify(next) === JSON.stringify(clip)) {
      break;
    }
    clip = next;
  }

  expect(clip, `no visible element for ${selectors.join(', ')}`).not.toBeNull();
  await expect(page).toHaveScreenshot(name, { ...options, clip, fullPage: true });
}

/** Full-page screenshot once no request is in flight, like page.screenshot({ fullPage: true }) in the Mocha specs. */
async function expectPageScreenshot(session, name, options = {}) {
  await session.waitForIdle();
  await expect(session.page).toHaveScreenshot(name, { fullPage: true, ...options });
}

/** Screenshot of the first visible match of a selector, like element.screenshot() in the Mocha specs. */
async function expectElementScreenshot(session, selector, name, options = {}) {
  await session.waitForIdle();
  await expect(session.page.locator(selector).filter({ visible: true }).first()).toHaveScreenshot(name, options);
}

module.exports = {
  expectElementScreenshot,
  expectPageScreenshot,
  restoreFixture,
  DEFAULT_FIXTURE,
  updateEnvironment,
  openSession,
  expectAreaScreenshot,
};
