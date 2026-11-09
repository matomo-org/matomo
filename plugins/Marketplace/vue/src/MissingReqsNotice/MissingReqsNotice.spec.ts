/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

import MissingReqsNotice from './MissingReqsNotice.vue';
import { translateStub } from '../testCoreHomeMock';

function mountNotice(missingRequirements: Record<string, unknown>[]) {
  return mount(MissingReqsNotice, {
    props: { plugin: { missingRequirements } },
    global: { mocks: { translate: translateStub } },
  });
}

describe('Marketplace/MissingReqsNotice', () => {
  it('asks for an update when the requirement is too old', () => {
    const wrapper = mountNotice([
      {
        requirement: 'php', actualVersion: '8.1.0', requiredVersion: '>=8.2.0', actualVersionTooNew: false,
      },
    ]);

    expect(wrapper.text()).toBe('CorePluginsAdmin_MissingRequirementsNotice:PHP,8.1.0,>=8.2.0');
  });

  it('says the plugin is not yet compatible when the requirement is too new', () => {
    const wrapper = mountNotice([
      {
        requirement: 'matomo',
        actualVersion: '6.1.0',
        requiredVersion: '>=4.0.0,<5.0.0-b1',
        actualVersionTooNew: true,
      },
    ]);

    expect(wrapper.text())
      .toBe('CorePluginsAdmin_MissingRequirementsNotYetCompatibleNotice:Matomo,6.1.0,>=4.0.0,<5.0.0-b1');
  });

  it('shows one notice per missing requirement', () => {
    const wrapper = mountNotice([
      {
        requirement: 'php', actualVersion: '8.1.0', requiredVersion: '>=8.2.0', actualVersionTooNew: false,
      },
      {
        requirement: 'matomo', actualVersion: '6.1.0', requiredVersion: '<5.0.0-b1', actualVersionTooNew: true,
      },
    ]);

    expect(wrapper.findAll('.alert')).toHaveLength(2);
  });
});
