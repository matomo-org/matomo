/*!
 * Matomo - free/libre analytics platform
 *
 * Runs a Mocha UI spec from tests/UI/specs or plugins/{Plugin}/tests/UI on Playwright Test, with the same
 * globals the Mocha harness provides (page, testEnvironment, expect with matchImage, describe/it...).
 * Only the runtime changes: the PageRenderer and test environment of the old harness are reused as they
 * are, on top of a Playwright browser.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const fs = require('fs');
const path = require('path');
const Module = require('module');
const { test, expect: playwrightExpect } = require('@playwright/test');
const { MATOMO_URL, ROOT, SERVER_GLOBAL, prepareFixture, teardownFixture, DEFAULT_FIXTURE } = require('../support/fixtures');
const { Browser } = require('./puppeteer-compat');
const { PlaywrightPageRenderer } = require('./page-renderer');

// let the reused harness modules resolve their dependencies (qs, chai) from this package
process.env.NODE_PATH = [path.join(__dirname, '../node_modules'), process.env.NODE_PATH].filter(Boolean).join(path.delimiter);
Module._initPaths();

const chai = require('chai');
const chaiFiles = require('chai-files');

const HARNESS = path.join(ROOT, 'tests/lib/screenshot-testing/support');
const SPEC_TIMEOUT = 240_000; // the Mocha harness default
const VIEWPORT = { width: 1350, height: 768 };

let chromeUserAgent;

function contextOptions() {
  return {
    viewport: VIEWPORT,
    userAgent: chromeUserAgent,
    locale: 'en-US',
    ignoreHTTPSErrors: true,
    bypassCSP: true, // page functions are rebuilt from source in the page
    extraHTTPHeaders: { 'Accept-Language': 'en-US' },
  };
}

function setUpGlobals(spec) {
  global.config = {
    piwikUrl: MATOMO_URL.href,
    phpServer: SERVER_GLOBAL,
    php: process.env.MATOMO_PHP || 'php',
    expectedScreenshotsDir: ['./expected-screenshots', './expected-ui-screenshots'],
    processedScreenshotsDir: './processed-ui-screenshots',
    screenshotDiffDir: './screenshot-diffs',
  };
  global.PIWIK_INCLUDE_PATH = ROOT;
  global.uiTestsDir = path.join(ROOT, 'tests/UI');
  global.testsLibDir = path.join(ROOT, 'tests/lib');
  global.options = {
    'persist-fixture-data': true,
    tests: [],
    ...(spec.plugin ? { plugin: spec.plugin } : {}),
  };
  global.superUserLogin = 'superUserLogin';
  global.superUserPassword = 'pas3!"§$%&/()=?\'ㄨ<|-_#*+~>word';
  global.expect = chai.expect;
  global.app = { runner: { suite: null } };
  global.testEnvironment = require(path.join(HARNESS, 'test-environment')).TestingEnvironment;
}

function baseDirectory(spec) {
  return spec.plugin ? path.join(ROOT, path.dirname(spec.file)) : global.uiTestsDir;
}

function expectedFile(spec, fileName) {
  for (const dir of global.config.expectedScreenshotsDir) {
    const candidate = path.join(baseDirectory(spec), dir);
    if (fs.existsSync(candidate)) {
      return path.join(candidate, fileName);
    }
  }
  return path.join(baseDirectory(spec), global.config.expectedScreenshotsDir[0], fileName);
}

function withExtension(name) {
  return /\.(png|txt)$/.test(name) ? name : `${name}.png`;
}

let chaiReady = false;

/** expect(buffer).to.matchImage(), compared by Playwright against baselines generated on CI. */
function installImageAssertions(spec, currentSuite) {
  const fileName = (name) => withExtension(`${currentSuite().title}_${name}`);
  global.expect.file = (name) => chai.expect(chaiFiles.file(expectedFile(spec, fileName(name))));
  global.expect.fileMatchesContent = (name, content) => chai.expect(chaiFiles.file(expectedFile(spec, fileName(name)))).to.equal(content);
  installImageAssertions.currentSuite = currentSuite;
  if (chaiReady) {
    return;
  }
  chaiReady = true;
  chai.use(chaiFiles);
  chai.use((chaiApi) => {
    chaiApi.Assertion.addMethod('matchImage', function matchImage(params) {
      const { imageName, compareAgainst, comparisonThreshold, prefix } = typeof params === 'string' ? { imageName: params } : params;
      const name = withExtension(`${prefix || installImageAssertions.currentSuite().title}_${compareAgainst || imageName}`);
      const buffer = this._obj;
      chai.assert.instanceOf(buffer, Buffer, `expected a screenshot buffer for ${name}`);
      playwrightExpect(buffer).toMatchSnapshot(name, comparisonThreshold
        ? { maxDiffPixelRatio: comparisonThreshold }
        : { maxDiffPixels: 10 });
    });
  });
}

class Suite {
  constructor(title, parent) {
    this.title = title;
    this.parent = parent;
    this.fixture = undefined;
    this.optionsOverride = undefined;
    this.titles = new Map();
  }

  timeout(ms) {
    test.describe.configure({ timeout: ms });
    return this;
  }

  retries() {
    return this;
  }

  slow() {
    return this;
  }

  // Playwright rejects duplicate titles in one file, Mocha doesn't
  uniqueTitle(title) {
    const count = (this.titles.get(title) || 0) + 1;
    this.titles.set(title, count);
    return count === 1 ? title : `${title} (${count})`;
  }
}

/** `this` inside Mocha tests and hooks. */
function mochaContext(testInfo, suite, title) {
  return {
    timeout(ms) {
      testInfo.setTimeout(ms);
    },
    retries() {},
    slow() {},
    skip() {
      test.skip();
    },
    test: { title, parent: suite },
    currentTest: { title, parent: suite },
  };
}

function callMochaFunction(fn, context) {
  if (!fn) {
    return undefined;
  }
  if (fn.length > 0) {
    return new Promise((resolve, reject) => {
      fn.call(context, (error) => (error ? reject(error) : resolve()));
    });
  }
  return fn.call(context);
}

/**
 * Registers the legacy spec with Playwright. Called by the generated wrapper files, one per spec.
 * spec = { file (relative to the Matomo root), plugin }
 */
function run(spec) {
  setUpGlobals(spec);

  let suite = null;
  let runningSuite = null;
  const currentSuite = () => runningSuite || { title: '' };
  installImageAssertions(spec, currentSuite);

  global.page = new PlaywrightPageRenderer(`${MATOMO_URL.href}tests/PHPUnit/proxy`, null, null);

  const root = new Suite('', null);
  suite = root;

  function registerRootHooks(rootSuite) {
    test.beforeAll(async ({ browser }, testInfo) => {
      testInfo.setTimeout(0); // fixture setup has no time limit, like in the Mocha harness
      setUpGlobals(spec);
      installImageAssertions(spec, currentSuite);

      if (!chromeUserAgent) {
        chromeUserAgent = `Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/${browser.version()} Safari/537.36`;
      }
      const compatBrowser = new Browser(browser, contextOptions);
      global.page.browser = compatBrowser;
      global.page.originalUserAgent = chromeUserAgent;
      await global.page.createPage();
      watchContext(global.page.browserContext.__pw, global.page.webpage);

      const persist = !(rootSuite.optionsOverride && rootSuite.optionsOverride['persist-fixture-data'] === false);
      prepareFixture({ fixtureClass: rootSuite.fixture || DEFAULT_FIXTURE, plugin: spec.plugin, persist });
      // like TestingEnvironment.setupFixture(): the harness defaults (mock auth, real translations) on top
      global.testEnvironment.reload();
      global.testEnvironment.save();
    });

    test.afterAll(async () => {
      const persist = !(rootSuite.optionsOverride && rootSuite.optionsOverride['persist-fixture-data'] === false);
      if (!persist) {
        teardownFixture(rootSuite.fixture || DEFAULT_FIXTURE);
      }
      if (global.page.browserContext) {
        await global.page.browserContext.close();
        global.page.browserContext = null;
      }
    });

    test.beforeEach(async () => {
      global.page._reset();
    });

    test.afterEach(async ({}, testInfo) => {
      if (testInfo.status !== testInfo.expectedStatus && global.page.webpage) {
        const logs = global.page.getPageLogsString('  ');
        if (logs) {
          await testInfo.attach('page-logs', { body: logs, contentType: 'text/plain' });
        }
        const screenshot = await global.page.webpage.__pw.screenshot({ fullPage: true }).catch(() => null);
        if (screenshot) {
          await testInfo.attach('failure', { body: screenshot, contentType: 'image/png' });
        }
      }
    });
  }

  global.describe = function describe(title, fn) {
    const parent = suite;
    const child = new Suite(parent.uniqueTitle(title), parent);
    test.describe(child.title, () => {
      suite = child;
      if (parent === root) {
        registerRootHooks(child);
      }
      fn.call(child);
      suite = parent;
    });
    return child;
  };
  global.describe.skip = function describeSkip(title, fn) {
    const parent = suite;
    const child = new Suite(parent.uniqueTitle(title), parent);
    test.describe.skip(child.title, () => {
      suite = child;
      fn.call(child);
      suite = parent;
    });
    return child;
  };
  global.describe.only = global.describe;
  global.xdescribe = global.describe.skip;
  global.context = global.describe;

  global.it = function it(title, fn) {
    const owner = suite;
    const uniqueTitle = owner.uniqueTitle(title);
    if (!fn) {
      test.skip(uniqueTitle, () => {});
      return;
    }
    test(uniqueTitle, async ({}, testInfo) => {
      runningSuite = owner;
      global.app.runner.suite = owner;
      await callMochaFunction(fn, mochaContext(testInfo, owner, title));
    });
  };
  global.it.skip = (title) => test.skip(suite.uniqueTitle(title), () => {});
  global.it.only = global.it;
  global.xit = global.it.skip;
  global.specify = global.it;

  const hook = (register) => (fn) => {
    const owner = suite;
    register(async ({}, testInfo) => {
      runningSuite = owner;
      await callMochaFunction(fn, mochaContext(testInfo, owner, ''));
    });
  };
  global.before = hook(test.beforeAll);
  global.after = hook(test.afterAll);
  global.beforeEach = hook(test.beforeEach);
  global.afterEach = hook(test.afterEach);

  // Tests continue from the previous test's page, so a failure skips the rest of the spec. For finding
  // many failures at once, PLAYWRIGHT_LEGACY_MODE=default runs them anyway, on a fresh page.
  test.describe.configure({ mode: process.env.PLAYWRIGHT_LEGACY_MODE || 'serial', timeout: SPEC_TIMEOUT });
  requireSloppy(path.join(ROOT, spec.file));
}

/**
 * Loads a spec and the helpers it requires like Mocha does. Playwright's loader compiles files in strict
 * mode, where the specs' undeclared assignments (`pageWrap = ...`) throw.
 */
function requireSloppy(file) {
  const isSpecCode = (filename) => /\/tests\/UI\//.test(filename) && !filename.includes('node_modules');
  const previous = Module._extensions['.js'];
  Module._extensions['.js'] = function load(module, filename) {
    if (isSpecCode(filename)) {
      module._compile(fs.readFileSync(filename, 'utf8'), filename);
      return;
    }
    previous.call(this, module, filename);
  };
  try {
    require(file);
  } finally {
    Module._extensions['.js'] = previous;
  }
}

/**
 * Requests to other hosts get an empty page: CI must not depend on them, a redirect there (Login's logme)
 * still lands on the right URL, and pages the app opens there (TagManager's debug site) load instantly.
 * The pattern is matched by Playwright itself, so local requests aren't intercepted at all.
 * Popups nobody listens for (the Overlay spec does) are closed, so the tested page stays in front.
 */
function watchContext(context, page) {
  const localHosts = [MATOMO_URL.hostname, 'localhost', '127.0.0.1'].map((host) => host.replace(/\./g, '\\.')).join('|');
  context.route(new RegExp(`^https?://(?!(?:${localHosts})(?::\\d+)?(?:/|$))`), (route) => route.fulfill({ status: 200, contentType: 'text/html', body: '' }));
  context.on('page', async (popup) => {
    if (popup !== page.__pw && page.__pw.listenerCount('popup') === 0) {
      await popup.close().catch(() => {});
      await page.__pw.bringToFront().catch(() => {});
    }
  });
}

module.exports = { run };
