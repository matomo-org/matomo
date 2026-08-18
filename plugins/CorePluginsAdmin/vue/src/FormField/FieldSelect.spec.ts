/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// Materialize builds the select at runtime through jQuery. The mock captures the options
// FieldSelect passes to formSelect, which is where the dropdown behaviour is configured.
let formSelectOptions: Record<string, any>|undefined;

const mockJQueryObject = {
  formSelect: vi.fn((options: Record<string, any>) => { formSelectOptions = options; }),
  closest: vi.fn(() => mockJQueryObject),
  find: vi.fn(() => mockJQueryObject),
  attr: vi.fn(() => mockJQueryObject),
  on: vi.fn(() => mockJQueryObject),
  off: vi.fn(() => mockJQueryObject),
};

const testWindow = window as any;
testWindow.$ = vi.fn(() => mockJQueryObject);

import FieldSelect from './FieldSelect.vue';

const availableOptions = [
  { key: 'a', value: 'Apple' },
  { key: 'b', value: 'Banana' },
];

function mountSelect(props = {}) {
  const wrapper = mount(FieldSelect as any, {
    props: { modelValue: 'a', name: 'fruit', availableOptions, ...props },
    global: { mocks: { $sanitize: (value: string) => value } },
  });
  // the component initialises Materialize from a setTimeout in mounted()
  vi.runAllTimers();
  return wrapper;
}

// the wrapper Materialize would have built, with the geometry the correction reads
function buildWrapper({ wrapperTop, panelTop, inlineTop }: {
  wrapperTop: number, panelTop: number, inlineTop: number,
}) {
  const wrapper = document.createElement('div');
  wrapper.className = 'select-wrapper';
  const panel = document.createElement('ul');
  panel.className = 'dropdown-content';
  panel.style.top = `${inlineTop}px`;
  wrapper.appendChild(panel);

  const trigger = document.createElement('input');
  trigger.className = 'select-dropdown';
  wrapper.appendChild(trigger);
  document.body.appendChild(wrapper);

  Object.defineProperty(wrapper, 'getBoundingClientRect', {
    value: () => ({ top: wrapperTop, bottom: wrapperTop + 38, height: 38 }),
  });
  Object.defineProperty(panel, 'getBoundingClientRect', {
    value: () => ({ top: panelTop, bottom: panelTop + 200, height: 200 }),
  });
  // the correction adds the wrapper's border to the gap
  Object.defineProperty(window, 'getComputedStyle', {
    writable: true,
    value: () => ({ borderTopWidth: '1px' }),
  });
  return { wrapper, panel, trigger };
}

describe('CorePluginsAdmin/FormField/FieldSelect', () => {
  beforeEach(() => {
    formSelectOptions = undefined;
    vi.clearAllMocks();
    vi.useFakeTimers();
    document.body.innerHTML = '';
    // re-assert the jQuery mock: the shared bootstrap installs the real jQuery as window.$
    testWindow.$ = vi.fn(() => mockJQueryObject);
    // run the correction inline so the assertions do not have to wait for a frame
    vi.stubGlobal('requestAnimationFrame', (cb: FrameRequestCallback) => {
      cb(0);
      return 0;
    });
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  describe('dropdown options', () => {
    it('opens the panel below the field instead of covering it', () => {
      mountSelect();
      expect(formSelectOptions?.dropdownOptions.coverTrigger).toBe(false);
    });

    it('lets a caller override coverTrigger', () => {
      mountSelect({ uiControlOptions: { dropdownOptions: { coverTrigger: true } } });
      expect(formSelectOptions?.dropdownOptions.coverTrigger).toBe(true);
    });

    it("still runs a caller's onOpenStart", () => {
      const callerOnOpenStart = vi.fn();
      mountSelect({ uiControlOptions: { dropdownOptions: { onOpenStart: callerOnOpenStart } } });

      const { trigger } = buildWrapper({ wrapperTop: 100, panelTop: 138, inlineTop: 28 });
      formSelectOptions?.dropdownOptions.onOpenStart(trigger);

      expect(callerOnOpenStart).toHaveBeenCalledTimes(1);
      expect(callerOnOpenStart).toHaveBeenCalledWith(trigger);
    });
  });

  describe('panel offset', () => {
    it('pushes the panel down when it opens below the field', () => {
      mountSelect();
      // panel starts flush with the field's bottom edge
      const { panel, trigger } = buildWrapper({ wrapperTop: 100, panelTop: 138, inlineTop: 28 });

      formSelectOptions?.dropdownOptions.onOpenStart(trigger);

      // 28 + 8px gap + 1px border
      expect(panel.style.top).toBe('37px');
    });

    it('pulls the panel up when it opens above the field', () => {
      mountSelect();
      // panel sits above the field, so the gap has to move the other way
      const { panel, trigger } = buildWrapper({ wrapperTop: 300, panelTop: 100, inlineTop: -200 });

      formSelectOptions?.dropdownOptions.onOpenStart(trigger);

      expect(panel.style.top).toBe('-209px');
    });

    it('does not accumulate when the panel is opened repeatedly', () => {
      mountSelect();
      const { panel, trigger } = buildWrapper({ wrapperTop: 100, panelTop: 138, inlineTop: 28 });

      formSelectOptions?.dropdownOptions.onOpenStart(trigger);
      expect(panel.style.top).toBe('37px');

      // Materialize resets the inline top and repositions before each open, so the
      // correction always applies to a freshly computed value rather than its own output
      panel.style.top = '28px';
      formSelectOptions?.dropdownOptions.onOpenStart(trigger);

      expect(panel.style.top).toBe('37px');
    });

    it('does nothing when the wrapper has not been built yet', () => {
      mountSelect();
      const orphan = document.createElement('input');

      expect(() => formSelectOptions?.dropdownOptions.onOpenStart(orphan)).not.toThrow();
    });
  });
});
