/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

vi.mock('CoreHome', async () => {
  const { coreHomeMock } = await import('./testCoreHomeMock');
  const mock = coreHomeMock(() => undefined);
  // a viewer on a website that has pre-processed pages
  return {
    ...mock,
    Matomo: {
      ...mock.Matomo,
      idSite: 1,
      userCurrentRole: 'view',
      transitionsHasPreprocessedPages: true,
    },
  };
});

import { flushRibbons, mountTransitionsReport } from './testTransitionsReportHarness';
import { installFakeTransitionsBackend, FakeTransitionsBackend } from './testFakeTransitionsModel';
import { stubElementRects, useSynchronousFrames } from './testMeasuredLayout';

const STORED = { preprocessed: true, preprocessedAt: '2025-03-10 04:05:06' };

describe('Transitions/TransitionsReport data source', () => {
  let backend: FakeTransitionsBackend;

  beforeEach(() => {
    stubElementRects();
    useSynchronousFrames();
    backend = installFakeTransitionsBackend({ pageviews: 100 });
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  function badge(wrapper: ReturnType<typeof mountTransitionsReport>) {
    return wrapper.find('.transitionsSourceHeader__badge');
  }

  it('should follow the parts as they are served', async () => {
    backend.report.sources = {
      internalReferrers: STORED,
      followingActions: { preprocessed: true, preprocessedAt: '2025-03-10 03:00:00' },
    };
    const wrapper = mountTransitionsReport();

    backend.respondPart('internalReferrers');
    await flushRibbons();
    expect(badge(wrapper).text()).toBe('Transitions_PreprocessedBadge');
    expect(badge(wrapper).attributes('title')).toBe('Transitions_PreprocessedBadgeTooltip:2025-03-10 04:05:06');

    backend.respondPart('followingActions');
    await flushRibbons();
    expect(badge(wrapper).attributes('title')).toBe('Transitions_PreprocessedBadgeTooltip:2025-03-10 03:00:00');

    // the referrers part was served from raw data, eg after the page's records were outdated
    backend.respondPart('externalReferrers');
    await flushRibbons();
    expect(badge(wrapper).text()).toBe('Transitions_RawDataBadge');
  });

  it('should show raw data when no part carries the flag', async () => {
    const wrapper = mountTransitionsReport();
    backend.respond();
    await flushRibbons();

    expect(badge(wrapper).text()).toBe('Transitions_RawDataBadge');
  });

  it('should drop the badge with the report when the request fails', async () => {
    const wrapper = mountTransitionsReport();
    backend.fail('NoDataForAction');
    await flushRibbons();

    expect(wrapper.find('.transitionsSourceHeader').exists()).toBe(false);
  });
});
