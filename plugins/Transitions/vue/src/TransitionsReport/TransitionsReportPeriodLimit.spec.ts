/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises } from '@vue/test-utils';

const fetchStatus = vi.hoisted(() => vi.fn());

vi.mock('CoreHome', async () => {
  const { coreHomeMock } = await import('./testCoreHomeMock');
  const mock = coreHomeMock(() => undefined);
  // an admin of the website in the URL
  return {
    ...mock,
    AjaxHelper: {
      fetch: (...args: unknown[]) => fetchStatus(...args),
      post: vi.fn(),
    },
    Matomo: {
      ...mock.Matomo,
      idSite: 1,
      userCurrentRole: 'admin',
      hasSuperUserAccess: false,
      transitionsHasPreprocessedPages: true,
    },
  };
});

import { flushRibbons, mountTransitionsReport } from './testTransitionsReportHarness';
import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects, useSynchronousFrames } from './testMeasuredLayout';

describe('Transitions/TransitionsReport beyond the period limit', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    stubElementRects();
    useSynchronousFrames();
    backend = installFakeTransitionsBackend({ pageviews: 100 });
    fetchStatus.mockReset();
    fetchStatus.mockResolvedValue({
      selected: false,
      idPage: null,
      used: 2,
      limit: 20,
      rearchiveMonths: 6,
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  it('should let an admin pre-process a page whose period is not allowed', async () => {
    const wrapper = mountTransitionsReport();
    backend.fail('PeriodNotAllowed');
    await flushRibbons();
    await flushPromises();

    expect(wrapper.find('.transitionsReport__errorTitle').text()).toContain('Transitions_PeriodNotAllowed');
    expect(wrapper.find('.transitionsSourceHeader__toggle').text())
      .toContain('Transitions_PreprocessThisPage');
    expect(wrapper.find('.transitionsSourceHeader__usage').text())
      .toBe('Transitions_PreprocessedPagesUsageShort:2|20');
    // nothing was served, so there is no source to point out
    expect(wrapper.find('.transitionsSourceHeader__badge').exists()).toBe(false);
    // the header stays from the report's start, so the status is read once
    expect(fetchStatus).toHaveBeenCalledTimes(1);
  });

  it('should not offer it for other errors', async () => {
    const wrapper = mountTransitionsReport();
    backend.fail('NoDataForAction');
    await flushRibbons();
    await flushPromises();

    expect(wrapper.find('.transitionsReport__error').exists()).toBe(true);
    expect(wrapper.find('.transitionsSourceHeader').exists()).toBe(false);
  });
});
