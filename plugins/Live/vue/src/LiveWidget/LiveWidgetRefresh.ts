/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { MatomoUrl, AjaxHelper } from 'CoreHome';
import { DirectiveBinding } from 'vue';

interface LiveWidgetRefreshBinding {
  liveRefreshAfterMs: number;
}

const { $ } = window;

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<LiveWidgetRefreshBinding>): void {
    setTimeout(() => {
      const segment = MatomoUrl.parsed.value.segment as string;

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ($(el).find('#visitsLive') as any).liveWidget({
        interval: binding.value.liveRefreshAfterMs,
        onUpdate: () => {
          // updates the numbers of total visits in startbox
          AjaxHelper.fetch(
            {
              module: 'Live',
              action: 'ajaxTotalVisitors',
              segment,
            },
            {
              format: 'html',
            },
          ).then((r) => {
            $(el).find('#visitsTotal').replaceWith(r);
          });
        },
        maxRows: 10,
        fadeInSpeed: 600,
        dataUrlParams: {
          module: 'Live',
          action: 'getLastVisitsStart',
          segment,
        },
      });
    });
  },
};
