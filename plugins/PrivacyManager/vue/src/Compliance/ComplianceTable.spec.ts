/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ComplianceTable from './ComplianceTable.vue';

describe('PrivacyManager/ComplianceTable', () => {
  const results = [
    {
      name: 'IP Anonymisation Enabled',
      value: 'compliant',
      notes: 'Anonymisation of visitors\' IP addresses must be enabled.',
      impact: 'Visitor IP addresses are anonymised before storage.',
    },
    {
      name: 'Third-Party Cookies Disabled',
      value: 'non_compliant',
      notes: 'Third-party cookies must be disabled.',
      impact: 'Visitors cannot be tracked across different websites.',
    },
  ];

  function createWrapper() {
    return mount(ComplianceTable, {
      props: { results },
      global: {
        mocks: {
          translate: (key: string) => key,
          $sanitize: (value: string) => value,
        },
      },
    });
  }

  it('renders the four column headers including Description and Impact', () => {
    const wrapper = createWrapper();
    const headers = wrapper.findAll('thead th').map((th) => th.text());

    expect(headers).toEqual([
      'PrivacyManager_ComplianceTableSettingName',
      'PrivacyManager_ComplianceTableSettingStatus',
      'PrivacyManager_ComplianceTableSettingDescription',
      'PrivacyManager_ComplianceTableSettingImpact',
    ]);
  });

  it('renders the impact value in the fourth column of each row', () => {
    const wrapper = createWrapper();
    const firstRowCells = wrapper.findAll('tbody tr:first-child td');

    expect(firstRowCells).toHaveLength(4);
    expect(firstRowCells[3].html()).toContain('Visitor IP addresses are anonymised before storage.');
  });
});
