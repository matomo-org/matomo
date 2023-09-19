/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('VersionInfoHeaderMessage', function() {
  const parentSuite = this;

  const selectorComponent = 'div[vue-entry="CoreHome.VersionInfoHeaderMessage"]';
  const selectorMessage = '#header_message';
  const selectorMessageTitle = '#header_message .title';
  const selectorMessageDropdown = '#header_message .dropdown';
  const selectorUpdateLink = '#updateCheckLinkContainer';

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
    testEnvironment.save();
  };

  beforeEach(() => {
    // otherwise #header_message.title will be hidden
    testEnvironment.useOverrideCss = false;

    testEnvironment.optionsOverride = {
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

      it('should check for a new version on request', async function() {
        await page.goto(urlAdminHome);
        await page.waitForNetworkIdle();

        makeUpdateAvailable();

        await page.click(selectorUpdateLink);
        await page.waitForNetworkIdle();

        expect(await getMessageTitleText()).to.match(/New Update: Matomo 99.99.99/);
      });

      describe('rendering', function() {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        it('looks great', async function() {
          makeUpdateAvailable();

          await page.goto(urlAdminHome);
          await page.waitForNetworkIdle();

          expect(await page.screenshotSelector(selectorMessage)).to.matchImage('update_available');

          // hover is broken
          return;

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
