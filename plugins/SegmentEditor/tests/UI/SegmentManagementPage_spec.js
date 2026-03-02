/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor ui tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentManagementPageTest", function () {
  this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';

  var generalParams = 'idSite=1&period=range&date=2010-03-06,2010-03-08';
  var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=CoreHome_Segments';
  const globalSegment = {
    id: null,
    name: 'UI Test Global Segment',
    definition: 'countryCode==fr',
  };
  const siteSegment = {
    id: null,
    name: 'UI Test Site Segment',
    definition: 'visitCount>=1',
  };
  const xssSegment = {
    id: null,
    name: '<script>alert("testsegment");</script>',
    definition: 'browserCode==FF',
  };
  const realtimeSegment = {
    id: null,
    name: 'UI Test Realtime Segment',
    definition: 'browserCode==FF',
  };
  const complexDashboardSegment = {
    id: null,
    name: 'UI Test Complex Dashboard Segment',
    definition: 'browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3,browserName==s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3;browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3',
  };

  before(async function () {
    testEnvironment.configOverride.General = {
      browser_archiving_disabled_enforce: '1',
      enable_browser_archiving_triggering: '0',
    };
    testEnvironment.optionsOverride = {
      enableBrowserTriggerArchiving: '0',
    };
    testEnvironment.save();

    const globalSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: globalSegment.name,
      definition: globalSegment.definition,
      idSite: 0,
      autoArchive: 1,
      enabledAllUsers: 1,
    });

    const siteSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: siteSegment.name,
      definition: siteSegment.definition,
      idSite: 1,
      autoArchive: 1,
      enabledAllUsers: 1,
    });

    const xssSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: xssSegment.name,
      definition: xssSegment.definition,
      idSite: 1,
      autoArchive: 1,
      enabledAllUsers: 1,
    });

    const realtimeSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: realtimeSegment.name,
      definition: realtimeSegment.definition,
      idSite: 1,
      autoArchive: 0,
      enabledAllUsers: 1,
    });

    const complexDashboardSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: complexDashboardSegment.name,
      definition: complexDashboardSegment.definition,
      idSite: 1,
      autoArchive: 1,
      enabledAllUsers: 1,
    });

    globalSegment.id = extractSegmentId(globalSegmentResult);
    siteSegment.id = extractSegmentId(siteSegmentResult);
    xssSegment.id = extractSegmentId(xssSegmentResult);
    realtimeSegment.id = extractSegmentId(realtimeSegmentResult);
    complexDashboardSegment.id = extractSegmentId(complexDashboardSegmentResult);

    testEnvironment.configOverride.General = {
      browser_archiving_disabled_enforce: '0',
      enable_browser_archiving_triggering: '1',
    };
    testEnvironment.optionsOverride = {
      enableBrowserTriggerArchiving: '1',
    };
    testEnvironment.save();

    await testEnvironment.callApi('VisitsSummary.get', {
      idSite: 1,
      period: 'range',
      date: '2010-03-06,2010-03-08',
      segment: siteSegment.definition,
    });
  });

  after(async function () {
    if (globalSegment.id) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: globalSegment.id });
    }
    if (siteSegment.id) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: siteSegment.id });
    }
    if (xssSegment.id) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: xssSegment.id });
    }
    if (realtimeSegment.id) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: realtimeSegment.id });
    }
    if (complexDashboardSegment.id) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: complexDashboardSegment.id });
    }
  });

  it("should load correctly", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
  });

  function extractSegmentId(result) {
    if (result && typeof result === 'object' && typeof result.value !== 'undefined') {
      return parseInt(result.value, 10) || 0;
    }
    return parseInt(result, 10) || 0;
  }
});
