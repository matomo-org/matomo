/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => (await import('../testCoreHomeMock')).coreHomeMock());

/* eslint-disable import/first */
import RequestTrial from './RequestTrial.vue';
import { makePlugin } from '../testMarketplaceFixtures';
import { translateStub } from '../testCoreHomeMock';

function confirmText(isTrialPending: boolean) {
  return mount(RequestTrial, {
    props: { modelValue: makePlugin({ isTrialPending }) },
    global: { mocks: { translate: translateStub } },
  }).find('p').text();
}

describe('Marketplace/RequestTrial', () => {
  it('warns that super users will be emailed when no request is pending', () => {
    expect(confirmText(false)).toBe('Marketplace_RequestTrialConfirmEmailWarning');
  });

  it('says no further email is sent when a request is already pending', () => {
    expect(confirmText(true)).toBe('Marketplace_RequestTrialConfirmAlreadyRequested');
  });
});
