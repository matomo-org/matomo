/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import { translate } from '../translate';

interface ShowSensitiveDataArgs {
  sensitiveData: string;
  showCharacters?: number;
  clickElementSelector: string|HTMLElement|JQuery;
}

const { $ } = window;

/**
 * Handles visibility of sensitive data. By default data will be shown replaced with stars (*)
 * On click on the element the full data will be shown
 *
 * Configuration attributes:
 * data-show-characters          number of characters to show in clear text (defaults to 6)
 * data-click-element-selector   selector for element that will show the full data on click
 *                               (defaults to element)
 *
 * Example:
 * <div v-show-sensitive-date="some text"></div>
 */
export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<ShowSensitiveDataArgs>): void {
    const element = $(el);

    const { sensitiveData } = binding.value;
    const showCharacters = binding.value.showCharacters || 6;
    const clickElement = binding.value.clickElementSelector || element;

    let protectedData = '';
    if (showCharacters > 0) {
      protectedData += sensitiveData.slice(0, showCharacters);
    }
    protectedData += sensitiveData.slice(showCharacters).replace(/./g, '*');
    element.html(protectedData);

    function onClickHandler() {
      element.html(sensitiveData);
      $(clickElement).css({
        cursor: '',
      });
      $(clickElement).tooltip('destroy');
    }

    $(clickElement).tooltip({
      content: translate('CoreHome_ClickToSeeFullInformation'),
      items: '*',
      track: true,
    });

    $(clickElement).one('click', onClickHandler);
    $(clickElement).css({
      cursor: 'pointer',
    });
  },
};
