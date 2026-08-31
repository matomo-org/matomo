/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import ReportExport, { ReportExportArgs } from './ReportExport';

const { $ } = window;

// Only whether the popover opens is under test here; the real one resolves export URLs against a
// live report and reports its own render errors.
vi.mock('./ReportExportPopover.vue', () => ({
  default: { name: 'ReportExportPopoverStub', render: () => null },
}));

describe('ReportExport directive', () => {
  let showLoading: ReturnType<typeof vi.fn>;

  const args: ReportExportArgs = {
    reportTitle: 'Device type',
    requestParams: {},
    reportFormats: { CSV: 'CSV' },
    apiMethod: 'DevicesDetection.getType',
    maxFilterLimit: 100,
  };

  beforeEach(() => {
    showLoading = vi.fn();
    (window as unknown as { Piwik_Popover: unknown }).Piwik_Popover = {
      showLoading,
      setTitle: vi.fn(),
      setContent: vi.fn(),
      onClose: vi.fn(),
      close: vi.fn(),
    };
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  function mountIconIn(html: string): HTMLElement {
    document.body.innerHTML = html;
    const icon = document.querySelector('#icon') as HTMLElement;
    ReportExport.mounted(icon, { value: args } as DirectiveBinding<ReportExportArgs>);
    return icon;
  }

  // The popover has no close button while loading, so opening it before the report is resolved
  // strands it on screen for good when there is nothing to describe.
  it('should not open the popover when the icon belongs to no single report', () => {
    const icon = mountIconIn(`
      <div class="col">
        <div class="widget">
          <div class="widgetTop"><div vue-entry="CoreHome.ReportHeader"><a id="icon"></a></div></div>
          <div class="widgetContent"></div>
        </div>
        <div class="widget">
          <div class="widgetContent"><div class="dataTable" id="neighbour"></div></div>
        </div>
      </div>`);
    $('#neighbour').data('uiControlObject', { param: { filter_limit: 25 }, numberOfSubtables: 0 });

    icon.click();

    expect(showLoading).not.toHaveBeenCalled();
  });

  // Positive control: proves the click handler runs at all, so the assertion above is about the
  // guard rather than about a listener that never fired.
  it('should open the popover for an icon inside its report', () => {
    const icon = mountIconIn(`
      <div class="card-content">
        <div class="dataTable" id="report"><a id="icon"></a></div>
      </div>`);
    $('#report').data('uiControlObject', { param: { filter_limit: 25 }, numberOfSubtables: 0 });

    icon.click();

    expect(showLoading).toHaveBeenCalledWith('Export');
  });
});
