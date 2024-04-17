/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from './translate';
import Matomo from './Matomo/Matomo';
import { setCookie, getCookie } from './CookieHelper/CookieHelper';

const { $ } = window;

function handleZenMode() {
  let zenMode = !!parseInt(getCookie('zenMode')!, 10);
  const iconSwitcher = $('.top_controls .icon-arrowup');

  function updateZenMode() {
    if (zenMode) {
      $('body').addClass('zenMode');
      iconSwitcher.addClass('icon-arrowdown').removeClass('icon-arrowup');
      iconSwitcher.prop('title', translate('CoreHome_ExitZenMode'));
    } else {
      $('body').removeClass('zenMode');
      iconSwitcher.removeClass('icon-arrowdown').addClass('icon-arrowup');
      iconSwitcher.prop('title', translate('CoreHome_EnterZenMode'));
    }
  }

  Matomo.helper.registerShortcut('z', translate('CoreHome_ShortcutZenMode'), (event) => {
    if (event.altKey) {
      return;
    }

    zenMode = !zenMode;
    setCookie('zenMode', zenMode ? '1' : '0');
    updateZenMode();
  });

  iconSwitcher.click(() => {
    window.Mousetrap.trigger('z');
  });

  updateZenMode();
}

Matomo.on('Matomo.topControlsRendered', () => {
  handleZenMode();
});
