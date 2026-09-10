/*!
 * Matomo - free/libre analytics platform
 *
 * Opens the tooltips of the reports the TooltipPayloads fixture feeds and confirms every tracked
 * value reaches them complete and as text, whatever it contains.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('TooltipContent', function () {
  this.fixture = 'Piwik\\Plugins\\CoreHome\\tests\\Fixtures\\TooltipPayloads';

  const params = '&idSite=20&period=month&date=2019-08-09';

  // every value the fixture tracks starts with this, so a sweep can tell them from Matomo's own
  // tooltips, which are written for a tooltip and may render
  const marker = 'PL';

  // one per payload class the fixture tracks; the trailing space keeps PL1 apart from PL10
  const payloadClasses = Array.from({ length: 12 }, (value, index) => 'PL' + (index + 1) + ' ');

  // the tags a tooltip may render; everything else has to be shown as text
  const allowedTags = ['B', 'BR', 'EM', 'I', 'SMALL', 'SPAN', 'STRONG', 'U'];

  const widget = (module, action, extra) => '?module=Widgetize&action=iframe&moduleToWidgetize='
    + module + '&actionToWidgetize=' + action + params + '&filter_limit=50' + (extra || '');

  /**
   * Opens the tooltip of every element below `hostSelector` whose title carries a payload and reads
   * back what it renders. jQuery UI builds the tooltip and fills in its content while handling the
   * mouseover, so a triggered event gets at the same content a hover produces - even where the
   * tooltip is only shown after a delay.
   */
  function sweepPayloadTooltips(hostSelector) {
    return page.evaluate((selector, payloadMarker) => {
      const $ = window.jQuery;
      const seen = new Set();
      const results = [];

      $(selector).find('[title]').each(function () {
        const title = this.getAttribute('title');

        if (!title || title.indexOf(payloadMarker) === -1) {
          return;
        }

        $(this).trigger('mouseover');

        const opened = $('.ui-tooltip').filter(function () {
          return !seen.has(this);
        });

        opened.each(function () {
          seen.add(this);
        });

        const content = opened.last().find('.ui-tooltip-content')[0];

        if (!content) {
          results.push({ title, opened: false });
          return;
        }

        const clone = content.cloneNode(true);
        // read a line break as one, so the text can be compared to the title character for character
        $(clone).find('br').replaceWith('\n');

        results.push({
          title,
          opened: true,
          text: clone.textContent,
          breaks: content.querySelectorAll('br').length,
          elements: Array.from(content.querySelectorAll('*')).map((node) => ({
            tag: node.tagName,
            attributes: node.getAttributeNames(),
          })),
        });

        $(this).trigger('mouseleave');
      });

      return results;
    }, hostSelector, marker);
  }

  const countOf = (value, pattern) => (value.match(pattern) || []).length;

  // htmlspecialchars() is the only encoder in the chain, and the attribute parse already resolved
  // one of its layers before a title was read back
  const decodeOnce = (value) => value
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#0?39;/g, "'")
    .replace(/&amp;/g, '&');

  const lineBreaks = /<br ?\/?>/gi;
  const otherAllowedTags = new RegExp('</?(' + allowedTags.filter((tag) => tag !== 'BR').join('|') + ') ?/?>', 'gi');

  /**
   * A title is admissible either rendered - its allow-listed tags became elements - or shown as
   * text, and Matomo picks between the two by what the title holds. Both have to display every
   * character of the value that was tracked.
   */
  function admissibleContent(title) {
    return [
      {
        text: decodeOnce(title.replace(otherAllowedTags, '').replace(lineBreaks, '\n')),
        breaks: countOf(title, /\n/g) + countOf(title, lineBreaks),
      },
      {
        text: decodeOnce(title),
        breaks: countOf(title, /\n/g),
      },
    ];
  }

  function expectTooltipToShowItsTitle(tooltip) {
    const admissible = admissibleContent(tooltip.title);

    if (!admissible.some((candidate) => candidate.text === tooltip.text
      && candidate.breaks === tooltip.breaks)) {
      throw new Error('A tooltip does not show its title.'
        + '\n  title:      ' + JSON.stringify(tooltip.title)
        + '\n  tooltip:    ' + JSON.stringify(tooltip.text) + ' with ' + tooltip.breaks + ' line breaks'
        + '\n  rendered:   ' + JSON.stringify(admissible[0].text) + ' with ' + admissible[0].breaks
        + '\n  or as text: ' + JSON.stringify(admissible[1].text) + ' with ' + admissible[1].breaks);
    }
  }

  /**
   * A tag left escaped in the visible text is the signature of a value that was escaped one layer
   * too many. Single brackets are not looked for: the tracker escapes an unpaired one inside a url
   * on its own, so one can be part of the value a report legitimately shows.
   */
  const escapedMarkup = [
    /&lt;\/?(b|div|em|i|img|small|span|strong|style|svg|u)[ &]/,
    /&amp;(amp;)*lt;/,
  ];

  function expectTooltipsToBeSafe(tooltips, minimumCount) {
    tooltips.forEach((tooltip) => {
      expect(tooltip.opened, 'no tooltip opened for title ' + tooltip.title).to.be.true;

      tooltip.elements.forEach((element) => {
        expect(allowedTags, 'element in tooltip for title ' + tooltip.title).to.include(element.tag);
        expect(element.attributes, element.tag + ' in tooltip for title ' + tooltip.title).to.be.empty;
      });

      expectTooltipToShowItsTitle(tooltip);

      escapedMarkup.forEach((pattern) => {
        expect(tooltip.text, 'a value escaped one layer too deep in tooltip for title ' + tooltip.title)
          .to.not.match(pattern);
      });
    });

    // a selector that stops matching would otherwise leave nothing to assert on
    expect(tooltips.length, 'tooltips carrying a tracked value').to.be.at.least(minimumCount);
  }

  async function expectNoPayloadRan() {
    expect(await page.evaluate(() => window.__tooltipProbe)).to.be.undefined;
  }

  async function loadPage(url, hostSelector) {
    // the reports below differ only in the query string, which a page.goto() alone would not reload
    await page.goto('about:blank');
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector(hostSelector);
  }

  async function sweep(hostSelector, minimumCount) {
    const tooltips = await sweepPayloadTooltips(hostSelector);

    expectTooltipsToBeSafe(tooltips, minimumCount);
    await expectNoPayloadRan();

    return tooltips;
  }

  async function loadAndSweep(url, hostSelector, minimumCount) {
    await loadPage(url, hostSelector);

    return sweep(hostSelector, minimumCount);
  }

  it('should show tracked values as text in the visits log action tooltips', async function () {
    const tooltips = await loadAndSweep(
      widget('Live', 'getVisitorLog'),
      '.dataTableVizVisitorLog',
      90
    );

    // every action type and every payload class ends up in these tooltips
    expect(tooltips.filter((tooltip) => tooltip.text.indexOf('onerror') !== -1)).to.not.be.empty;

    payloadClasses.forEach((payload) => {
      expect(tooltips.filter((tooltip) => tooltip.text.indexOf(payload) !== -1),
        payload + 'is missing from the action tooltips').to.not.be.empty;
    });
  });

  it('should list the browser plugins of a visit with their icons', async function () {
    // the icon tooltips build their content from a hidden list in the page rather than from a title
    const plugins = await page.evaluate(() => {
      const $ = window.jQuery;
      const icon = $('.dataTableVizVisitorLog .visitorLogIconWithDetails').filter(function () {
        return $(this).find('ul li img[src*="plugins/"]').length > 0;
      }).first();

      icon.trigger('mouseover');

      const content = $('.ui-tooltip:last .ui-tooltip-content');

      return { items: content.find('li').length, icons: content.find('img').length };
    });

    expect(plugins.items).to.be.at.least(3);
    expect(plugins.icons).to.be.at.least(3);
  });

  it('should show tracked values as text in the visitor profile', async function () {
    await page.evaluate(() => {
      const $ = window.jQuery;

      $('.dataTableVizVisitorLog .card.row').filter(function () {
        return $(this).text().indexOf('PL12') !== -1;
      }).first().find('.visitor-log-visitor-profile-link').click();
    });

    await page.waitForSelector('.visitor-profile');
    await page.waitForNetworkIdle();

    await sweep('.visitor-profile', 8);
  });

  it('should show tracked values as text in the page url tooltips', async function () {
    await loadAndSweep(widget('Actions', 'getPageUrls', '&flat=1'), '.dataTable', 20);
  });

  it('should show tracked values as text in the site search keyword tooltips', async function () {
    await loadAndSweep(widget('Actions', 'getSiteSearchKeywords'), '.dataTable', 15);
  });

  it('should show tracked values as text in the event tooltips', async function () {
    await loadAndSweep(widget('Events', 'getCategory', '&flat=1'), '.dataTable', 1);
  });

  it('should show tracked values as text in the ecommerce item tooltips', async function () {
    await loadAndSweep(widget('Goals', 'getItemsName'), '.dataTable', 10);
  });

  it('should show tracked values as text in the referrer tooltips', async function () {
    await loadAndSweep(widget('Referrers', 'getWebsites', '&flat=1'), '.dataTable', 10);
  });

  it('should show tracked values as text in the user id tooltips', async function () {
    await loadAndSweep(widget('UserId', 'getUsers'), '.dataTable', 30);
  });

  it('should render its own markup in a comparison tooltip', async function () {
    const tooltips = await loadAndSweep(
      widget('Actions', 'getPageUrls', '&flat=1&comparePeriods%5B%5D=month&compareDates%5B%5D=2019-07-09'),
      '.dataTable',
      20
    );

    // a comparison adds a line break of Matomo's own to the tooltip title, which a title that
    // carries nothing else is expected to render
    const withOwnBreaks = tooltips.filter((tooltip) => /<br ?\/?>/i.test(tooltip.title)
      && tooltip.title.indexOf('&lt;') === -1);

    expect(withOwnBreaks).to.not.be.empty;
    withOwnBreaks.forEach((tooltip) => {
      expect(tooltip.breaks, 'line breaks in tooltip for title ' + tooltip.title).to.be.at.least(1);
    });
  });
});
