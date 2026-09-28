/*!
 * Matomo - free/libre analytics platform
 *
 * The part of Puppeteer's API that Matomo's UI specs and PageRenderer use, implemented on Playwright,
 * so the existing specs run on Playwright unchanged.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const DEFAULT_TIMEOUT = 30_000; // Puppeteer's default for waits
const CLICK_TIMEOUT = 5_000;

const isTimeout = (error) => error && error.name === 'TimeoutError';
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Captures until two screenshots in a row are identical, the rule of Playwright's toHaveScreenshot.
async function stableCapture(capture) {
  let previous = await capture();
  for (let attempt = 0; attempt < 5; attempt += 1) {
    await sleep(100);
    const current = await capture();
    if (previous.equals(current)) {
      return current;
    }
    previous = current;
  }
  return previous;
}

function unwrap(value) {
  if (value && value.__pw) {
    return value.__pw;
  }
  return Array.isArray(value) ? value.map(unwrap) : value;
}

function mapWaitUntil(waitUntil) {
  const value = Array.isArray(waitUntil) ? waitUntil[0] : waitUntil;
  if (value === 'networkidle0' || value === 'networkidle2') {
    return 'networkidle';
  }
  return value;
}

function navigationOptions(options = {}) {
  return { waitUntil: mapWaitUntil(options.waitUntil), timeout: options.timeout ?? DEFAULT_TIMEOUT };
}

// Puppeteer passes any number of arguments to page functions, Playwright only one, and functions can only
// cross into the page as source. The runners rebuild the function in the page (the context bypasses CSP).
async function runInPage({ src, args }) {
  const result = await (0, eval)(`(${src})`)(...args);
  const isDomValue = result && typeof result === 'object'
    && (result instanceof Node || result === window || result.jquery);
  return isDomValue ? undefined : result;
}

async function runOnElement(element, { src, args }) {
  return (0, eval)(`(${src})`)(element, ...args);
}

async function runOnElements(elements, { src, args }) {
  return (0, eval)(`(${src})`)(elements, ...args);
}

async function runForHandle({ src, args }) {
  return (0, eval)(`(${src})`)(...args);
}

function payload(fn, args) {
  return { src: fn.toString(), args: unwrap(args) };
}

function evaluate(target, fn, args) {
  return typeof fn === 'string' ? target.evaluate(fn) : target.evaluate(runInPage, payload(fn, args));
}

function wrapHandle(handle) {
  if (!handle) {
    return null;
  }
  return handle.asElement() ? new ElementHandle(handle.asElement()) : new JSHandle(handle);
}

class JSHandle {
  constructor(handle) {
    this.__pw = handle;
  }

  async getProperty(name) {
    return wrapHandle(await this.__pw.getProperty(name));
  }

  jsonValue() {
    return this.__pw.jsonValue();
  }

  evaluate(fn, ...args) {
    return this.__pw.evaluate(runOnElement, payload(fn, args));
  }

  asElement() {
    return null;
  }

  dispose() {
    return this.__pw.dispose();
  }
}

class ElementHandle extends JSHandle {
  asElement() {
    return this;
  }

  // Like Puppeteer: click the element even if something covers it, but first give it the chance to
  // become actionable, which Puppeteer never waited for.
  async click(options = {}) {
    const clickOptions = { button: options.button, clickCount: options.clickCount ?? options.count, delay: options.delay };
    if (options.offset) {
      clickOptions.position = options.offset;
    }
    try {
      await this.__pw.click({ ...clickOptions, timeout: CLICK_TIMEOUT });
    } catch (error) {
      if (!isTimeout(error)) {
        throw error;
      }
      await this.__pw.click({ ...clickOptions, force: true, timeout: DEFAULT_TIMEOUT });
    }
  }

  async hover() {
    try {
      await this.__pw.hover({ timeout: CLICK_TIMEOUT });
    } catch (error) {
      if (!isTimeout(error)) {
        throw error;
      }
      await this.__pw.hover({ force: true, timeout: DEFAULT_TIMEOUT });
    }
  }

  focus() {
    return this.__pw.focus();
  }

  tap() {
    return this.click();
  }

  type(text, options = {}) {
    return this.__pw.type(text, { delay: options.delay });
  }

  press(key, options = {}) {
    return this.__pw.press(key, { delay: options.delay });
  }

  screenshot(options = {}) {
    return stableCapture(() => this.__pw.screenshot({ type: options.type, omitBackground: options.omitBackground, path: options.path }));
  }

  boundingBox() {
    return this.__pw.boundingBox();
  }

  async $(selector) {
    return wrapHandle(await this.__pw.$(selector));
  }

  async $$(selector) {
    return (await this.__pw.$$(selector)).map(wrapHandle);
  }

  async $eval(selector, fn, ...args) {
    const handle = await this.$(selector);
    if (!handle) {
      throw new Error(`Error: failed to find element matching selector "${selector}"`);
    }
    return handle.evaluate(fn, ...args);
  }

  $$eval(selector, fn, ...args) {
    return this.__pw.$$eval(selector, runOnElements, payload(fn, args));
  }

  uploadFile(...paths) {
    return this.__pw.setInputFiles(paths);
  }

  select(...values) {
    return this.__pw.selectOption(values);
  }

  async contentFrame() {
    const frame = await this.__pw.contentFrame();
    return frame ? new Frame(frame) : null;
  }

  isIntersectingViewport() {
    return this.__pw.evaluate((element) => new Promise((resolve) => {
      const observer = new IntersectionObserver((entries) => {
        resolve(entries[0].intersectionRatio > 0);
        observer.disconnect();
      });
      observer.observe(element);
    }));
  }

  scrollIntoView() {
    return this.__pw.scrollIntoViewIfNeeded();
  }
}

class Response {
  constructor(response) {
    this.__pw = response;
  }

  status() {
    return this.__pw.status();
  }

  ok() {
    return this.__pw.ok();
  }

  url() {
    return this.__pw.url();
  }

  headers() {
    return this.__pw.headers();
  }

  buffer() {
    return this.__pw.body();
  }

  text() {
    return this.__pw.text();
  }

  json() {
    return this.__pw.json();
  }
}

class Request {
  constructor(request, responses) {
    this.__pw = request;
    this.responses = responses;
  }

  url() {
    return this.__pw.url();
  }

  method() {
    return this.__pw.method();
  }

  headers() {
    return this.__pw.headers();
  }

  postData() {
    return this.__pw.postData();
  }

  resourceType() {
    return this.__pw.resourceType();
  }

  isNavigationRequest() {
    return this.__pw.isNavigationRequest();
  }

  failure() {
    return this.__pw.failure();
  }

  frame() {
    return new Frame(this.__pw.frame());
  }

  // Puppeteer returns the response synchronously once it arrived.
  response() {
    const response = this.responses.get(this.__pw);
    return response ? new Response(response) : null;
  }
}

class Keyboard {
  constructor(keyboard) {
    this.__pw = keyboard;
  }

  press(key, options = {}) {
    return this.__pw.press(key, { delay: options.delay });
  }

  type(text, options = {}) {
    return this.__pw.type(text, { delay: options.delay });
  }

  down(key) {
    return this.__pw.down(key);
  }

  up(key) {
    return this.__pw.up(key);
  }

  sendCharacter(char) {
    return this.__pw.insertText(char);
  }
}

class Mouse {
  constructor(mouse) {
    this.__pw = mouse;
  }

  move(x, y, options = {}) {
    return this.__pw.move(x, y, { steps: options.steps });
  }

  click(x, y, options = {}) {
    return this.__pw.click(x, y, { button: options.button, clickCount: options.clickCount ?? options.count, delay: options.delay });
  }

  down(options = {}) {
    return this.__pw.down({ button: options.button, clickCount: options.clickCount });
  }

  up(options = {}) {
    return this.__pw.up({ button: options.button, clickCount: options.clickCount });
  }

  wheel({ deltaX = 0, deltaY = 0 } = {}) {
    return this.__pw.wheel(deltaX, deltaY);
  }
}

/** Methods shared by pages and frames. */
class Target {
  constructor(target) {
    this.__pw = target;
  }

  evaluate(fn, ...args) {
    return evaluate(this.__pw, fn, args);
  }

  async evaluateHandle(fn, ...args) {
    return wrapHandle(await this.__pw.evaluateHandle(runForHandle, payload(fn, args)));
  }

  async $(selector) {
    return wrapHandle(await this.__pw.$(selector));
  }

  async $$(selector) {
    return (await this.__pw.$$(selector)).map(wrapHandle);
  }

  async $eval(selector, fn, ...args) {
    const handle = await this.$(selector);
    if (!handle) {
      throw new Error(`Error: failed to find element matching selector "${selector}"`);
    }
    return handle.evaluate(fn, ...args);
  }

  $$eval(selector, fn, ...args) {
    return this.__pw.$$eval(selector, runOnElements, payload(fn, args));
  }

  async waitForSelector(selector, options = {}) {
    let state = 'attached';
    if (options.visible) {
      state = 'visible';
    } else if (options.hidden) {
      state = 'hidden';
    }
    return wrapHandle(await this.__pw.waitForSelector(selector, { state, timeout: options.timeout ?? DEFAULT_TIMEOUT }));
  }

  // Polls in Node, so async predicates work and a navigation in between doesn't fail the wait.
  async waitForFunction(fn, options = {}, ...args) {
    const timeout = options.timeout ?? DEFAULT_TIMEOUT;
    const interval = typeof options.polling === 'number' ? options.polling : 50;
    const start = Date.now();
    let lastError;
    while (!timeout || Date.now() - start < timeout) {
      try {
        const value = await evaluate(this.__pw, typeof fn === 'string' ? `(${fn})` : fn, args);
        if (value) {
          return { jsonValue: async () => value, asElement: () => null, dispose: async () => {} };
        }
      } catch (error) {
        if (!/Execution context was destroyed|navigat/i.test(error.message)) {
          throw error;
        }
        lastError = error;
      }
      await sleep(interval);
    }
    const error = new Error(`Waiting failed: ${timeout}ms exceeded${lastError ? ` (${lastError.message})` : ''}`);
    error.name = 'TimeoutError';
    throw error;
  }

  async click(selector, options = {}) {
    const handle = await this.waitForSelector(selector);
    await handle.click(options);
  }

  async hover(selector) {
    const handle = await this.waitForSelector(selector);
    await handle.hover();
  }

  async focus(selector) {
    const handle = await this.waitForSelector(selector);
    await handle.focus();
  }

  async type(selector, text, options = {}) {
    const handle = await this.waitForSelector(selector);
    await handle.type(text, options);
  }

  async select(selector, ...values) {
    const handle = await this.waitForSelector(selector);
    return handle.select(...values);
  }

  content() {
    return this.__pw.content();
  }

  title() {
    return this.__pw.title();
  }

  url() {
    return this.__pw.url();
  }

  goto(url, options) {
    return this.__pw.goto(url, navigationOptions(options));
  }

  addStyleTag(options) {
    return this.__pw.addStyleTag(options);
  }

  addScriptTag(options) {
    return this.__pw.addScriptTag(options);
  }
}

class Frame extends Target {
  name() {
    return this.__pw.name();
  }

  childFrames() {
    return this.__pw.childFrames().map((frame) => new Frame(frame));
  }

  parentFrame() {
    const parent = this.__pw.parentFrame();
    return parent ? new Frame(parent) : null;
  }

  isDetached() {
    return this.__pw.isDetached();
  }
}

class CDPSession {
  constructor(session) {
    this.__pw = session;
  }

  send(method, params) {
    return this.__pw.send(method, params);
  }

  on(event, handler) {
    this.__pw.on(event, handler);
  }

  off(event, handler) {
    this.__pw.off(event, handler);
  }
}

class Page extends Target {
  constructor(page, context, cdp) {
    super(page);
    this.context = context;
    this.cdp = new CDPSession(cdp);
    this.responses = new WeakMap();
    this.listeners = new Map();
    this.keyboard = new Keyboard(page.keyboard);
    this.mouse = new Mouse(page.mouse);
    this.touchscreen = { tap: (x, y) => page.touchscreen.tap(x, y) };
    page.on('response', (response) => this.responses.set(response.request(), response));
  }

  _client() {
    return this.cdp;
  }

  wrapEvent(event, value) {
    if (value && ['request', 'requestfinished', 'requestfailed'].includes(event)) {
      return new Request(value, this.responses);
    }
    if (event === 'response') {
      return new Response(value);
    }
    if (event === 'popup') {
      return Page.create(value, this.context);
    }
    if (event === 'framenavigated' || event === 'frameattached' || event === 'framedetached') {
      return new Frame(value);
    }
    return value;
  }

  // popups get their own CDP session first
  dispatch(event, value, handler) {
    const wrapped = this.wrapEvent(event, value);
    if (wrapped instanceof Promise) {
      wrapped.then(handler);
      return undefined;
    }
    return handler(wrapped);
  }

  on(event, handler) {
    const pwEvent = event === 'error' ? 'crash' : event;
    const wrapped = (value) => this.dispatch(event, value, handler);
    this.listeners.set(handler, wrapped);
    this.__pw.on(pwEvent, wrapped);
    return this;
  }

  once(event, handler) {
    const pwEvent = event === 'error' ? 'crash' : event;
    const wrapped = (value) => {
      this.listeners.delete(handler);
      this.dispatch(event, value, handler);
    };
    this.listeners.set(handler, wrapped);
    this.__pw.once(pwEvent, wrapped);
    return this;
  }

  off(event, handler) {
    const pwEvent = event === 'error' ? 'crash' : event;
    const wrapped = this.listeners.get(handler);
    if (wrapped) {
      this.__pw.off(pwEvent, wrapped);
      this.listeners.delete(handler);
    }
    return this;
  }

  removeListener(event, handler) {
    return this.off(event, handler);
  }

  listenerCount(event) {
    return this.__pw.listenerCount(event === 'error' ? 'crash' : event);
  }

  mainFrame() {
    return new Frame(this.__pw.mainFrame());
  }

  frames() {
    return this.__pw.frames().map((frame) => new Frame(frame));
  }

  browserContext() {
    return this.context;
  }

  target() {
    return { type: () => 'page', url: () => this.url() };
  }

  reload(options) {
    return this.__pw.reload(navigationOptions(options));
  }

  goBack(options) {
    return this.__pw.goBack(navigationOptions(options));
  }

  goForward(options) {
    return this.__pw.goForward(navigationOptions(options));
  }

  waitForNavigation(options = {}) {
    return this.__pw.waitForNavigation(navigationOptions(options));
  }

  waitForRequest(urlOrPredicate, options = {}) {
    const predicate = typeof urlOrPredicate === 'function'
      ? (request) => urlOrPredicate(new Request(request, this.responses))
      : urlOrPredicate;
    return this.__pw.waitForRequest(predicate, { timeout: options.timeout ?? DEFAULT_TIMEOUT })
      .then((request) => new Request(request, this.responses));
  }

  waitForResponse(urlOrPredicate, options = {}) {
    const predicate = typeof urlOrPredicate === 'function'
      ? (response) => urlOrPredicate(new Response(response))
      : urlOrPredicate;
    return this.__pw.waitForResponse(predicate, { timeout: options.timeout ?? DEFAULT_TIMEOUT })
      .then((response) => new Response(response));
  }

  setViewport({ width, height }) {
    return this.__pw.setViewportSize({ width, height });
  }

  viewport() {
    const size = this.__pw.viewportSize();
    return size ? { ...size, deviceScaleFactor: 1 } : null;
  }

  async screenshot(options = {}) {
    const clip = options.clip
      ? { x: options.clip.x, y: options.clip.y, width: options.clip.width, height: options.clip.height }
      : undefined;
    // a page in the background (behind a popup) gets no new frames, which stalled Puppeteer for minutes
    await this.__pw.bringToFront();
    return stableCapture(() => this.__pw.screenshot({
      fullPage: !!(options.fullPage || clip),
      clip,
      type: options.type,
      omitBackground: options.omitBackground,
      path: options.path,
    }));
  }

  bringToFront() {
    return this.__pw.bringToFront();
  }

  setContent(html, options) {
    return this.__pw.setContent(html, navigationOptions(options));
  }

  setExtraHTTPHeaders(headers) {
    return this.__pw.setExtraHTTPHeaders(headers);
  }

  async setUserAgent(userAgent, userAgentMetadata) {
    await this.cdp.send('Emulation.setUserAgentOverride', { userAgent, userAgentMetadata });
  }

  async setCacheEnabled(enabled = true) {
    await this.cdp.send('Network.enable');
    await this.cdp.send('Network.setCacheDisabled', { cacheDisabled: !enabled });
  }

  setRequestInterception() {
    throw new Error('setRequestInterception is not supported on Playwright, use page.webpage.__pw.route()');
  }

  evaluateOnNewDocument(fn, ...args) {
    const content = typeof fn === 'string' ? fn : `(${fn})(...${JSON.stringify(args)})`;
    return this.__pw.addInitScript({ content });
  }

  emulateMediaFeatures(features = []) {
    const media = {};
    for (const { name, value } of features) {
      if (name === 'prefers-color-scheme') {
        media.colorScheme = value || null;
      } else if (name === 'prefers-reduced-motion') {
        media.reducedMotion = value || null;
      } else if (name === 'forced-colors') {
        media.forcedColors = value || null;
      }
    }
    return this.__pw.emulateMedia(media);
  }

  async cookies(...urls) {
    return this.__pw.context().cookies(urls.length ? urls : this.url());
  }

  setCookie(...cookies) {
    return this.__pw.context().addCookies(cookies.map((cookie) => {
      const result = { ...cookie };
      if (!result.url && !result.domain) {
        result.url = this.url();
      }
      if (result.domain && !result.path) {
        result.path = '/';
      }
      if (result.url) {
        delete result.domain;
        delete result.path;
      }
      return result;
    }));
  }

  async deleteCookie(...cookies) {
    for (const cookie of cookies) {
      await this.__pw.context().clearCookies({ name: cookie.name, domain: cookie.domain, path: cookie.path });
    }
  }

  static async create(page, context) {
    return new Page(page, context, await page.context().newCDPSession(page));
  }

  isClosed() {
    return this.__pw.isClosed();
  }

  close() {
    return this.__pw.close();
  }
}

class BrowserContext {
  constructor(context) {
    this.__pw = context;
  }

  async newPage() {
    return Page.create(await this.__pw.newPage(), this);
  }

  overridePermissions(origin, permissions) {
    return this.__pw.grantPermissions(permissions, { origin });
  }

  clearPermissionOverrides() {
    return this.__pw.clearPermissions();
  }

  close() {
    return this.__pw.close();
  }
}

class Browser {
  constructor(browser, contextOptions) {
    this.__pw = browser;
    this.contextOptions = contextOptions;
  }

  async createBrowserContext() {
    return new BrowserContext(await this.__pw.newContext(this.contextOptions()));
  }

  async userAgent() {
    return this.contextOptions().userAgent;
  }

  version() {
    return this.__pw.version();
  }

  close() {
    // the browser belongs to Playwright's worker
  }
}

module.exports = { Browser, Page, ElementHandle, JSHandle, Frame };
