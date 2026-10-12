/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';

const mockFetch = vi.hoisted(() => vi.fn());

const mockMatomo = vi.hoisted(() => ({ idSite: 1, hasSuperUserAccess: false }));
const mockShowNotification = vi.hoisted(() => vi.fn());

vi.mock('CoreHome', () => ({
  Matomo: mockMatomo,
  MatomoUrl: {
    urlParsed: { value: { idSite: '1' } },
    stringify: (params: Record<string, string>) => new URLSearchParams(params).toString(),
  },
  AjaxHelper: { fetch: (...args: unknown[]) => mockFetch(...args) },
  translate: (key: string) => key,
  ContentBlock: { template: '<div><slot/></div>' },
  ActivityIndicator: { template: '<div/>' },
  Alert: { template: '<div><slot/></div>' },
  Progressbar: { template: '<div/>' },
  NotificationsStore: { show: mockShowNotification, scrollToNotification: vi.fn() },
}));

// eslint-disable-next-line import/first
import RecommendGoals from './RecommendGoals.vue';
// eslint-disable-next-line import/first
import RecommendGoalCard from './RecommendGoalCard.vue';

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

  it('links a superuser to the AI processing settings when AI processing is not allowed', async () => {
    mockMatomo.hasSuperUserAccess = true;
    window.history.replaceState(null, '', '/index.php?module=Goals&action=manage&idSite=1#?period=day');
    const w = await mountWith('notPermitted');
    mockMatomo.hasSuperUserAccess = false;
    window.history.replaceState(null, '', '/');

    expect(w.find('.recommendGoals-aiSwitch').exists()).toBe(false);
    expect(w.find('.recommendGoals-chip--aiUnavailable').exists()).toBe(false);
    expect(w.find('.recommendGoals-aiProcessingLink').attributes('href'))
      .toBe(`?idSite=1&module=AIProviders&action=aiProcessing&returnTo=${
        encodeURIComponent('index.php?module=Goals&action=manage&idSite=1#?period=day')}`);
  });

  it('tells other users to ask a superuser when they click the AI processing link', async () => {
    const w = await mountWith('notPermitted');

    expect(w.find('.recommendGoals-chip--aiUnavailable').exists()).toBe(false);
    const link = w.find('.recommendGoals-aiProcessingLink');
    expect(link.attributes('href')).toBe('#');

    await link.trigger('click');
    expect(mockShowNotification).toHaveBeenCalledWith(expect.objectContaining({
      message: 'Goals_RecommendAiAllowProcessingNoPermission',
      context: 'error',
    }));
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

describe('RecommendGoals scan warnings', () => {
  async function mountWithWarnings(warnings: unknown[], goals: unknown[] = []) {
    mockFetch.mockResolvedValue({
      mode: 'deterministic',
      goals,
      manualGoals: [],
      warnings,
      useAi: false,
      generatedAt: 1700000000,
      aiAvailability: 'available',
    });
    const wrapper = mount(RecommendGoals, {
      props: { userCanEditGoals: true },
      global: { stubs: { RecommendGoalCard: true } },
    });
    await flush();
    return wrapper;
  }

  it('shows the single server warning with its severity', async () => {
    const w = await mountWithWarnings([
      { type: 'blocked', severity: 'warning', message: 'The site blocked the scanner.' },
    ]);

    const notice = w.find('.recommendGoals-notice');
    expect(notice.exists()).toBe(true);
    expect(notice.classes()).toContain('recommendGoals-notice--warning');
    expect(notice.text()).toContain('The site blocked the scanner.');
    expect(notice.find('.icon-warning').exists()).toBe(true);
  });

  it('renders an info notice without the warning styling', async () => {
    const w = await mountWithWarnings([
      { type: 'fewConversions', severity: 'info', message: 'Few actions worth tracking.' },
    ]);

    const notice = w.find('.recommendGoals-notice');
    expect(notice.classes()).not.toContain('recommendGoals-notice--warning');
    expect(notice.find('.icon-info').exists()).toBe(true);
  });

  it('shows nothing when the scan had nothing to explain', async () => {
    const w = await mountWithWarnings([]);

    expect(w.find('.recommendGoals-notice').exists()).toBe(false);
  });
});

describe('RecommendGoals empty and completed states', () => {
  const contactGoal = {
    id: 'url:contact', name: 'Visited contact page', matchAttribute: 'url', pattern: '/contact', patternType: 'contains', reason: '', source: 'rule',
  };

  async function mountWithGoal(goals: Record<string, unknown> = {}) {
    mockFetch.mockResolvedValue({
      mode: 'deterministic',
      goals: [contactGoal],
      manualGoals: [{ name: 'Submitted a form', howTo: 'Send an event', category: 'form' }],
      useAi: false,
      generatedAt: 1700000000,
      aiAvailability: 'disabled',
    });
    const wrapper = mount(RecommendGoals, {
      props: { userCanEditGoals: true, goals },
      global: { stubs: { RecommendGoalCard: true } },
    });
    await flush();
    return wrapper;
  }

  it('replaces the cards with a confirmation once every suggestion is a goal', async () => {
    const w = await mountWithGoal({ 1: { match_attribute: 'url', pattern: '/contact' } });

    expect(w.find('.recommendGoals-notice--success').text()).toContain('Goals_RecommendAllCreated');
    expect(w.find('.recommendGoals-list').exists()).toBe(false);
    expect(w.find('.recommendGoals-manual').exists()).toBe(true);
  });

  it('collapses the whole section when the last suggestion is dismissed', async () => {
    const w = await mountWithGoal();
    mockFetch.mockClear();
    mockFetch.mockResolvedValue({});

    w.findComponent(RecommendGoalCard).vm.$emit('dismiss');
    await flush();

    expect(mockFetch).toHaveBeenCalledWith({ method: 'Goals.dismissRecommendedGoals', idSite: 1 });
    expect(w.find('.recommendGoals-list').exists()).toBe(false);
    expect(w.find('.recommendGoals-manual').exists()).toBe(false);
    expect(w.find('.recommendGoals-run').text()).toBe('Goals_RecommendGoals');
  });
});

describe('RecommendGoalCard', () => {
  it('shows the reason with the crawl facts on its info icon and the setup steps in the details', () => {
    const w = mount(RecommendGoalCard, {
      props: {
        rec: {
          name: 'Submitted the contact form',
          matchAttribute: 'event_action',
          pattern: 'contact_submit',
          patternType: 'exact',
          reason: 'Contact form on every page.',
          source: 'rule',
          implementationNote: 'Send the event on submit.',
          evidence: ['Seen on 4 pages.'],
        },
      },
    });

    expect(w.find('.recommendGoals-reasonText').text()).toBe('Contact form on every page.');
    expect(w.find('.recommendGoals-reasonIcon').attributes('title'))
      .toBe('Goals_RecommendWhySuggested\nSeen on 4 pages.');
    expect(w.find('.recommendGoals-evidence summary').text()).toBe('Goals_RecommendManualHowTo');
    expect(w.find('.recommendGoals-evidenceNote').text()).toBe('Send the event on submit.');
  });

  it('has no expandable section when the goal needs no setup', () => {
    const w = mount(RecommendGoalCard, {
      props: {
        rec: {
          name: 'Visited pricing page', matchAttribute: 'url', pattern: '/pricing', patternType: 'contains', reason: 'Pricing is linked from the menu.', source: 'rule', evidence: ['Linked 12 times across 6 pages.'],
        },
      },
    });

    expect(w.find('.recommendGoals-cardReason').exists()).toBe(true);
    expect(w.find('.recommendGoals-evidence').exists()).toBe(false);
  });
});
