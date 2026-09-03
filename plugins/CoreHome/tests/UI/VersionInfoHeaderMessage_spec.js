/*!
 * Matomo - free/libre analytics platform
 *
 * VersionInfoHeaderMessage screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('VersionInfoHeaderMessage', function() {
  const parentSuite = this;
  this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";

  const selectorComponent = 'div[vue-entry="CoreHome.VersionInfoHeaderMessage"]';
  const selectorMessage = '#header_message';
  const selectorMessageTitle = '#header_message .title';
  // the dropdown is two nodes: the outer owns placement, the inner owns appearance
  const selectorMessageDropdown = '#header_message .piwikSelector__dropdown';
  const selectorMessagePanel = '#header_message .mtm-dropdownPanel';
  const selectorUpdateLink = '#updateCheckLinkContainer';
  const selectorUpdateButtonIcon = '#header_message .title .icon-reload';
  const selectorDropdownUpdaterLink = `${selectorMessagePanel} a[href*="CoreUpdater"]`;

  const urlAdminHome = '?idSite=1&period=year&date=2012-08-09&module=CoreAdminHome&action=home';
  const urlHome = '?idSite=1&period=year&date=2012-08-09&module=CoreHome&action=index';

  const getMessageTitleText = async function() {
    const messageTitle = await page.$(selectorMessageTitle);
    const titleText = await messageTitle.getProperty('textContent');

    return await titleText.jsonValue();
  };

  const afterAsRegularUser = function() {
    delete testEnvironment.idSitesAdminAccess;
    testEnvironment.save();
  };

  const beforeAsRegularUser = function() {
    testEnvironment.idSitesAdminAccess = [1];
    testEnvironment.save();
  };

  const getUpdateLinkText = async function() {
    const updateLink = await page.$(selectorUpdateLink);
    const linkText = await updateLink.getProperty('textContent');

    return await linkText.jsonValue();
  };

  const makeUpdateAvailable = function() {
    testEnvironment.optionsOverride['UpdateCheck_LatestVersion'] = '99.99.99';
    testEnvironment.optionsOverride['UpdateCheck_LastCheckFailed'] = false;
    testEnvironment.save();
  };

  const isDropdownOpen = async function() {
    return await page.evaluate(function(rootSel, dropdownSel) {
      const root = document.querySelector(rootSel);
      const dropdown = document.querySelector(dropdownSel);

      // offsetParent covers the `display: none` the dropdown falls back to when not expanded
      return !!root && root.classList.contains('expanded')
        && !!dropdown && dropdown.offsetParent !== null;
    }, selectorMessage, selectorMessageDropdown);
  };

  const openDropdownByHover = async function() {
    await page.mouse.move(-10, -10); // start off the control so the hover is a real enter
    await page.hover(selectorMessageTitle);
    await page.waitForSelector(selectorMessageDropdown, {visible: true, timeout: 250});
  };

  /**
   * Walks the cursor down from the button into the panel, stopping in the gap between the two on
   * the way. Stopping there is the point of this helper: it is the position that used to close the
   * dropdown, and a single jump straight into the panel would skip it. The gap is measured against
   * the panel rather than its positioning wrapper, because the wrapper's padding is what now
   * covers the gap.
   */
  const moveCursorThroughGapIntoDropdown = async function() {
    const boxes = await page.evaluate(function(rootSel, panelSel) {
      const rect = (sel) => {
        const r = document.querySelector(sel).getBoundingClientRect();
        return {top: r.top, bottom: r.bottom, left: r.left, right: r.right};
      };

      return {root: rect(rootSel), panel: rect(panelSel)};
    }, selectorMessage, selectorMessagePanel);

    const gapHeight = boxes.panel.top - boxes.root.bottom;
    expect(gapHeight, 'expected a gap between the button and the panel').to.be.above(0);

    const centreX = Math.round((boxes.root.left + boxes.root.right) / 2);

    // land inside the gap itself, then continue into the panel
    await page.mouse.move(centreX, boxes.root.bottom + (gapHeight / 2), {steps: 5});
    await page.mouse.move(centreX, boxes.panel.top + 10, {steps: 5});
  };

  const makeUpdateFail = function() {
    testEnvironment.optionsOverride['UpdateCheck_LatestVersion'] = '';
    testEnvironment.optionsOverride['UpdateCheck_LastCheckFailed'] = true;
    testEnvironment.save();
  };

  beforeEach(() => {
    // otherwise #header_message.title will be hidden
    testEnvironment.useOverrideCss = false;

    testEnvironment.optionsOverride = {
      UpdateCheck_LastCheckFailed: false,
      UpdateCheck_LastTimeChecked: 2147468400,
      UpdateCheck_LatestVersion: '',
    };
    testEnvironment.save();
  });

  describe('CoreHome', function() {
    it('should not render without update', async function() {
      await page.goto(urlHome);
      await page.waitForNetworkIdle();

      expect(await page.$(selectorComponent)).to.be.null;
      expect(await page.$(selectorMessage)).to.be.null;
    });

    it('should display an available update', async function() {
      makeUpdateAvailable();

      await page.goto(urlHome);
      await page.waitForNetworkIdle();

      expect(await getMessageTitleText()).to.match(/New Update: Matomo 99.99.99/);
    });

    // The panel is positioned a few px below the button, so a pointer has a gap to cross to reach
    // it. That gap used to belong to no element, which fired mouseleave on #header_message and
    // closed the dropdown before it could be reached, making its links unusable (since 5.10.0).
    it('should stay open while the cursor travels from the button into the dropdown', async function() {
      makeUpdateAvailable();

      await page.goto(urlHome);
      await page.waitForNetworkIdle();

      await openDropdownByHover();
      await moveCursorThroughGapIntoDropdown();

      expect(await isDropdownOpen()).to.be.true;
    });

    it('should let a link inside the dropdown be clicked', async function() {
      makeUpdateAvailable();

      await page.goto(urlHome);
      await page.waitForNetworkIdle();

      await openDropdownByHover();
      await moveCursorThroughGapIntoDropdown();

      // the cursor is already inside the panel, so this clicks the way a user would
      await page.click(selectorDropdownUpdaterLink);
      await page.waitForNetworkIdle();

      expect(await page.url()).to.match(/module=CoreUpdater/);
    });
  });

  describe('CoreAdminHome', function() {
    describe('without superuser permissions', function() {
      before(beforeAsRegularUser);
      after(afterAsRegularUser);

      it('should not render', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        expect(await page.$(selectorComponent)).to.be.null;
        expect(await page.$(selectorMessage)).to.be.null;
      });
    });

    describe('with superuser permissions', function() {
      it('should always render', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        expect(await getUpdateLinkText()).to.match(/Check for updates/);
      });

      it('should tell if no new version is available', async function() {
        await page.click(selectorUpdateLink);
        await page.waitForNetworkIdle();

        expect(await getMessageTitleText()).to.match(/latest version of Matomo/);
      });

      it('should check for a new version when clicking the full title anchor', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        expect(await getUpdateLinkText()).to.match(/Check for updates/);

        await page.click(selectorMessageTitle);
        await page.waitForNetworkIdle();

        expect(await getMessageTitleText()).to.match(/latest version of Matomo/);
      });

      it('should check for a new version when clicking the icon inside the title anchor', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        expect(await getUpdateLinkText()).to.match(/Check for updates/);

        await page.click(selectorUpdateButtonIcon);
        await page.waitForNetworkIdle();

        expect(await getMessageTitleText()).to.match(/latest version of Matomo/);
      });

      it('should check for a new version on request', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        expect(await getUpdateLinkText()).to.match(/Check for updates/);

        makeUpdateFail();

        await page.click(selectorUpdateLink);
        await page.waitForNetworkIdle();

        expect(await getUpdateLinkText()).to.match(/Please try again/);

        makeUpdateAvailable();

        await page.click(selectorUpdateLink);
        await page.waitForNetworkIdle();

        expect(await getMessageTitleText()).to.match(/New Update: Matomo 99.99.99/);
      });

      describe('rendering', function() {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        it('looks great', async function() {
          await page.evaluate(function(selectorComponent, selectorMessageDropdown) {
            const component = document.querySelector(selectorComponent);
            const messageDropdown = document.querySelector(selectorMessageDropdown);
            const matomoVersion = JSON.parse(component.getAttribute('piwik-version'));

            messageDropdown.innerHTML = messageDropdown.innerHTML.replace(matomoVersion, '5.x-uitest');
          }, selectorComponent, selectorMessageDropdown);

          await page.mouse.move(-10, -10);
          await page.hover(selectorMessage);
          await page.waitForSelector(selectorMessageDropdown, {visible: true, timeout: 250});

          expect(
            await page.screenshotSelector(`${selectorMessage}, ${selectorMessageDropdown}`)
          ).to.matchImage('update_available');
        });
      });
    });
  });
});
