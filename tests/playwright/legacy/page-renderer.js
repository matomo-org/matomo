/*!
 * Matomo - free/libre analytics platform
 *
 * The Mocha harness's PageRenderer, on the Playwright facade. Only the parts that talked to Puppeteer
 * directly are replaced.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const path = require('path');
const { ROOT } = require('../support/fixtures');

const { PageRenderer } = require(path.join(ROOT, 'tests/lib/screenshot-testing/support/page-renderer'));

// Same as the harness's load handler, but applied before any page script runs, so it can't race them.
const PAGE_SETUP = `(() => {
  const setup = () => {
    document.documentElement.classList.add('uiTest');
    if (window.jQuery) {
      window.jQuery.fx.off = true;
    }
    const style = document.createElement('style');
    style.textContent = '* { caret-color: transparent !important; -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }'
      + ' .modal.open { transform: none !important; opacity: 1 !important; }';
    document.head.appendChild(style);
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setup);
  } else {
    setup();
  }
  window.addEventListener('load', () => {
    if (window.jQuery) {
      window.jQuery('html').addClass('uiTest');
      window.jQuery.fx.off = true;
    }
  });
})();`;

class PlaywrightPageRenderer extends PageRenderer {
  async screenshot(...args) {
    await this.resizeViewportToFullPage();
    return this.webpage.screenshot(...args);
  }

  screenshotNoResize(...args) {
    return this.webpage.screenshot(...args);
  }

  // The clip is measured once, so wait until the element's size stops changing (a late layout change
  // otherwise moves the capture boundary). Captures anyway after 5 s, like before.
  async screenshotSelector(selector, shouldResizeViewport = true) {
    if (shouldResizeViewport) {
      await this.resizeViewportToFullPage();
    }
    const measure = () => this.webpage.__pw.evaluate((sel) => (window.jQuery ? JSON.stringify(window.jQuery(sel)
      .filter(':visible').toArray().map((node) => [node.offsetWidth, node.offsetHeight])) : ''), selector)
      .catch(() => '');
    let previous = await measure();
    for (let attempt = 0; attempt < 50; attempt += 1) {
      await new Promise((resolve) => setTimeout(resolve, 100));
      const size = await measure();
      if (size === previous && size !== '' && size !== '[]') {
        break;
      }
      previous = size;
    }
    return super.screenshotSelector(selector, false);
  }

  async clearCookies() {
    await this.browserContext.__pw.clearCookies();
  }

  _setupWebpageEvents() {
    const page = this.webpage.__pw;

    page.addInitScript({ content: PAGE_SETUP }).catch(() => {});
    this.webpage.setCacheEnabled(false).catch(() => {});

    page.on('pageerror', (error) => {
      this._logMessage(`Webpage error: ${error.stack || error.message}`);
    });

    page.on('request', (request) => {
      this.pendingRequests.set(request, Date.now());
    });

    page.on('requestfailed', (request) => {
      this.pendingRequests.delete(request);
      const failure = request.failure();
      this._logMessage(`Unable to load resource (URL:${request.url()}): ${failure ? failure.errorText : 'Unknown error'}`);
    });

    page.on('requestfinished', async (request) => {
      this.pendingRequests.delete(request);
      const response = await request.response().catch(() => null);
      if (response && response.status() >= 400 && this._isUrlThatWeCareAbout(request.url())) {
        const body = await response.text().catch(() => '');
        this._logMessage(`Response (size "${body.length}", status "${response.status()}"): ${request.url()}\n${body.substring(0, 2000)}`);
      }
    });

    page.on('console', (message) => {
      this._logMessage(`Log: ${message.text()}`);
    });

    // Puppeteer left unhandled dialogs open, which blocked the page. Dismiss them unless a spec handles them.
    page.on('dialog', (dialog) => {
      this._logMessage(`Alert: ${dialog.message()}`);
      if (page.listenerCount('dialog') === 1) {
        dialog.dismiss().catch(() => {});
      }
    });
  }
}

module.exports = { PlaywrightPageRenderer };
