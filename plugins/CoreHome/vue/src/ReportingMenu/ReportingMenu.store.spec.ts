/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import nock from 'nock';
import ReportingMenuStoreInstance from './ReportingMenu.store';
import ReportingPagesStoreInstance from '../ReportingPages/ReportingPages.store';

describe('CoreHome/ReportingMenu.store', () => {
  const PAGES = [
    {
      category: { id: 'General_Visitors', name: 'Visitors', groups: [''] },
      subcategory: { id: 'General_Overview', name: 'Overview' },
      widgets: [],
    },
    {
      category: {
        id: 'General_AIAssistants',
        name: 'AI Assistants',
        groups: ['', 'CoreHome_AIInsights'],
        groupsWithoutTrackingRequirement: ['CoreHome_AIInsights'],
      },
      subcategory: { id: 'CoreHome_AIInsightsOverview', name: 'Overview' },
      widgets: [],
    },
  ];

  beforeAll(() => {
    nock('http://localhost')
      .persist()
      .post('/')
      .query((query) => query.method === 'API.getReportPagesMetadata')
      .reply(200, JSON.stringify(PAGES));
  });

  afterAll(() => {
    nock.cleanAll();
  });

  describe('groupsWithoutTrackingRequirement', () => {
    it('collects the exempt groups across all categories from the report pages metadata', async () => {
      await ReportingPagesStoreInstance.reloadAllPages();

      const exemptGroups = ReportingMenuStoreInstance.groupsWithoutTrackingRequirement.value;

      expect(exemptGroups.has('CoreHome_AIInsights')).toBe(true);
    });

    it('does not mark the default Analytics group as exempt', async () => {
      await ReportingPagesStoreInstance.reloadAllPages();

      const exemptGroups = ReportingMenuStoreInstance.groupsWithoutTrackingRequirement.value;

      expect(exemptGroups.has('')).toBe(false);
      expect(exemptGroups.has('General_Visitors')).toBe(false);
    });
  });
});
