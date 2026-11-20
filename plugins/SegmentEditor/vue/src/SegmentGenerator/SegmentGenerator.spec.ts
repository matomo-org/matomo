/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const { fetch } = vi.hoisted(() => {
  // the component reads jQuery from the window when it is loaded, to attach the value autocomplete
  const element: Record<string, () => unknown> = {};
  element.autocomplete = () => element;
  element.off = () => element;
  element.click = () => element;
  (window as unknown as { $: () => unknown }).$ = () => element;

  return { fetch: vi.fn(() => Promise.resolve([])) };
});

vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  AjaxHelper: { fetch },
  ActivityIndicator: {},
  Matomo: { idSite: 1 },
  MatomoLoader: {},
}));

vi.mock('CorePluginsAdmin', () => ({ Field: {} }));

vi.mock('./SegmentGenerator.store', () => ({
  default: {
    state: { value: { isLoading: false, segments: [] } },
    loadSegments: vi.fn(() => Promise.resolve([])),
  },
}));

import SegmentGenerator from './SegmentGenerator.vue';

function selectSegment(segment: string, forDataSubjectSearch: boolean) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wrapper = mount(SegmentGenerator as any, {
    props: { forDataSubjectSearch, idsite: 1 },
    global: { mocks: { $sanitize: (html: string) => html } },
  });

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  (wrapper.vm as any).updateAutocomplete({
    id: 'condition',
    segment,
    matches: '==',
    value: '',
  });
  wrapper.unmount();
}

function suggestionRequests(): string[] {
  return fetch.mock.calls
    .map((call) => (call as unknown[])[0] as { method: string, segmentName: string })
    .filter((params) => params.method === 'API.getSuggestedValuesForSegment')
    .map((params) => params.segmentName);
}

describe('SegmentEditor/SegmentGenerator', () => {
  beforeEach(() => {
    fetch.mockClear();
  });

  it.each(['userId', 'visitorId', 'visitId', 'visitIp'])(
    'does not ask for values of %s in the GDPR tools',
    (segment) => {
      selectSegment(segment, true);

      expect(suggestionRequests()).toEqual([]);
    },
  );

  it('still asks for values of the other segments in the GDPR tools', () => {
    selectSegment('browserCode', true);

    expect(suggestionRequests()).toEqual(['browserCode']);
  });

  it('still asks for values of a segment that identifies a visitor elsewhere', () => {
    selectSegment('userId', false);

    expect(suggestionRequests()).toEqual(['userId']);
  });
});
