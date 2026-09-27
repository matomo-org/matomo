/*!
 * Matomo - free/libre analytics platform
 *
 * Bridge between Playwright Test and Matomo's PHP test environment. It reuses what the Mocha harness
 * uses (tests:setup-fixture and tmp/testingPathOverride.json), so no PHP changes are needed.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { expect } = require('@playwright/test');

const ROOT = path.resolve(__dirname, '../../..');
const ENV_FILE = path.join(ROOT, 'tmp/testingPathOverride.json');
const MATOMO_URL = new URL(process.env.MATOMO_URL || 'http://localhost/');
const DEFAULT_FIXTURE = 'Piwik\\Tests\\Fixtures\\UITestFixture';

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

function readEnvironment() {
  try {
    return JSON.parse(fs.readFileSync(ENV_FILE, 'utf8') || '{}');
  } catch (e) {
    return {};
  }
}

function writeEnvironment(data) {
  fs.writeFileSync(ENV_FILE, JSON.stringify(data));
}

/** Reads [database_tests] from config/config.ini.php, for the mysql client calls below. */
function testDatabaseConfig() {
  const ini = fs.readFileSync(path.join(ROOT, 'config/config.ini.php'), 'utf8');
  const section = (ini.split(/^\[database_tests\]\s*$/m)[1] || '').split(/^\[/m)[0];
  const value = (key, fallback) => {
    const match = section.match(new RegExp(`^${key}[ \\t]*=[ \\t]*"?([^"\\n]*)"?`, 'm'));
    return match ? match[1].trim() : fallback;
  };
  return { host: value('host', '127.0.0.1'), user: value('username', 'root'), password: value('password', '') };
}

function mysqlArgs() {
  const { host, user, password } = testDatabaseConfig();
  return [`-h${host}`, `-u${user}`, ...(password ? [`-p${password}`] : [])];
}

function snapshotFile(dbName) {
  return path.join(ROOT, `tmp/playwright-fixture-${dbName}.sql`);
}

/**
 * Sets up (or reuses the persisted database of) a PHP fixture, like TestingEnvironment.setupFixture(),
 * then dumps the fixture database so every spec file can start from the same state. The PHP side
 * writes the fixture database and plugin list into the environment file, so this merges into it
 * instead of overwriting it. PLAYWRIGHT_FIXTURE_DROP=1 rebuilds a persisted database from scratch.
 */
function setupFixture(fixtureClass = DEFAULT_FIXTURE) {
  writeEnvironment({});
  execFileSync(process.env.MATOMO_PHP || 'php', [
    path.join(ROOT, 'console'),
    'tests:setup-fixture',
    fixtureClass,
    '--set-symlinks',
    `--server-global=${JSON.stringify({ HTTP_HOST: MATOMO_URL.host, REQUEST_URI: '/', REMOTE_ADDR: '127.0.0.1' })}`,
    '--persist-fixture-data',
    ...(process.env.PLAYWRIGHT_FIXTURE_DROP ? ['--drop'] : []),
  ], { cwd: ROOT, stdio: 'inherit' });

  const environment = { ...ENV_DEFAULTS, ...readEnvironment(), fixtureClass };
  writeEnvironment(environment);

  const dump = execFileSync('mysqldump', [...mysqlArgs(), '--single-transaction', '--skip-lock-tables', '--no-tablespaces', environment.dbName], { maxBuffer: 1024 * 1024 * 1024 });
  fs.writeFileSync(snapshotFile(environment.dbName), dump);
}

/**
 * Restores the fixture database dumped by setupFixture(). Tests change persisted state (report
 * preferences, options, users), so without this a spec depends on which specs ran before it.
 */
function restoreFixture() {
  const { dbName, fixtureClass } = readEnvironment();
  execFileSync('mysql', [...mysqlArgs(), dbName], { input: fs.readFileSync(snapshotFile(dbName)), maxBuffer: 1024 * 1024 * 1024 });
  writeEnvironment({ ...ENV_DEFAULTS, ...readEnvironment(), fixtureClass });
}

/** Changes the environment for the following requests, like assigning testEnvironment.x and calling save(). */
function updateEnvironment(changes) {
  writeEnvironment({ ...ENV_DEFAULTS, ...readEnvironment(), ...changes });
}

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
    if (!allowErrors) {
      problems.push(`page error: ${error.message}`);
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
    const left = Math.floor(Math.min(...rects.map((r) => r.left)) + window.scrollX);
    const top = Math.floor(Math.min(...rects.map((r) => r.top)) + window.scrollY);
    const right = Math.ceil(Math.max(...rects.map((r) => r.right)) + window.scrollX);
    const bottom = Math.ceil(Math.max(...rects.map((r) => r.bottom)) + window.scrollY);
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

module.exports = {
  expectPageScreenshot,
  restoreFixture,
  DEFAULT_FIXTURE,
  setupFixture,
  updateEnvironment,
  openSession,
  expectAreaScreenshot,
};
