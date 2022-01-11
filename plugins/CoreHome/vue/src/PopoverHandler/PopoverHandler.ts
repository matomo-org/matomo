/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { watch } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

const { $ } = window;

class PopoverHandler {
  constructor() {
    this.setup();
  }

  private setup() {
    watch(() => MatomoUrl.parsed.value.popover, () => this.onPopoverParamChanged());

    if (MatomoUrl.parsed.value.popover) {
      this.onPopoverParamChangedInitial();
    }
  }

  // don't initiate the handler until the page had a chance to render,
  // since some rowactions depend on what's been loaded.
  private onPopoverParamChangedInitial() {
    $(() => {
      setTimeout(() => {
        this.openOrClose();
      });
    });
  }

  private onPopoverParamChanged() {
    // make sure all popover handles were registered
    $(() => {
      this.openOrClose();
    });
  }

  private openOrClose() {
    this.close();

    // should be rather done by routing
    const popoverParam = MatomoUrl.parsed.value.popover as string;
    if (popoverParam) {
      this.open(popoverParam);
    } else {
      // the URL should only be set to an empty popover if there are no popovers in the stack.
      // to avoid avoid any strange inconsistent states, we reset the popover stack here.
      window.broadcast.resetPopoverStack();
    }
  }

  private close() {
    window.Piwik_Popover.close();
  }

  private open(thePopoverParam: string) {
    // in case the $ was encoded (e.g. when using copy&paste on urls in some browsers)
    let popoverParam = decodeURIComponent(thePopoverParam);

    // revert special encoding from broadcast.propagateNewPopoverParameter()
    popoverParam = popoverParam.replace(/\$/g, '%');
    popoverParam = decodeURIComponent(popoverParam);

    const popoverParamParts = popoverParam.split(':');
    const handlerName = popoverParamParts[0];
    popoverParamParts.shift();

    const param = popoverParamParts.join(':');
    if (typeof window.broadcast.popoverHandlers[handlerName] !== 'undefined'
      && !window.broadcast.isLoginPage()
    ) {
      window.broadcast.popoverHandlers[handlerName](param);
    }
  }
}

export default new PopoverHandler();
