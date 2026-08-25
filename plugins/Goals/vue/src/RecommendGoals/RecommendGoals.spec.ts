/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';

const mockFetch = vi.hoisted(() => vi.fn());

vi.mock('CoreHome', () => ({
  Matomo: { idSite: 1 },
  AjaxHelper: { fetch: (...args: unknown[]) => mockFetch(...args) },
  translate: (key: string) => key,
  ContentBlock: { template: '<div><slot/></div>' },
  ActivityIndicator: { template: '<div/>' },
  Alert: { template: '<div><slot/></div>' },
  Progressbar: { template: '<div/>' },
}));

// eslint-disable-next-line import/first
import RecommendGoals from './RecommendGoals.vue';

async function flush() {
  await new Promise((resolve) => { setTimeout(resolve, 0); });
  await nextTick();
}

async function mountWith(aiAvailability: string) {
  mockFetch.mockResolvedValue({
    mode: 'deterministic',
    goals: [],
    manualGoals: [],
    useAi: false,
    generatedAt: 1700000000,
    aiAvailability,
    privacyNote: 'note from server',
  });

  const wrapper = mount(RecommendGoals, {
    props: { userCanEditGoals: true },
    global: { stubs: { RecommendGoalCard: true } },
  });

  await flush();
  return wrapper;
}

describe('RecommendGoals AI availability', () => {
  it('shows the toggle and the privacy link when AI is available', async () => {
    const w = await mountWith('available');

    expect(w.find('.recommendGoals-aiSwitch').exists()).toBe(true);
    expect(w.find('.recommendGoals-chip--aiUnavailable').exists()).toBe(false);
    expect(w.find('.recommendGoals-privacyLink').exists()).toBe(true);
  });

  it('replaces both with a badge when the plugin is active but unconfigured', async () => {
    const w = await mountWith('notConfigured');

    expect(w.find('.recommendGoals-aiSwitch').exists()).toBe(false);
    expect(w.find('.recommendGoals-privacyLink').exists()).toBe(false);
    expect(w.find('.recommendGoals-chip--aiUnavailable').text())
      .toBe('Goals_RecommendAiNotConfigured');
  });

  it('tells the user to activate the plugin when it is not active', async () => {
    const w = await mountWith('notActivated');

    expect(w.find('.recommendGoals-aiSwitch').exists()).toBe(false);
    expect(w.find('.recommendGoals-privacyLink').exists()).toBe(false);
    expect(w.find('.recommendGoals-chip--aiUnavailable').text())
      .toBe('Goals_RecommendAiNotActivated');
  });

  it('hides everything AI related when AI cannot be enabled on the instance', async () => {
    const w = await mountWith('disabled');

    expect(w.find('.recommendGoals-aiSwitch').exists()).toBe(false);
    expect(w.find('.recommendGoals-privacyLink').exists()).toBe(false);
    expect(w.find('.recommendGoals-chip--aiUnavailable').exists()).toBe(false);
  });

  it('uses the privacy note built by the server', async () => {
    const w = await mountWith('available');

    expect(w.find('.recommendGoals-privacyNote').text()).toBe('note from server');
  });
});
