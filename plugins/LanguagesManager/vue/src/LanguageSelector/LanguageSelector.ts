/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import ClickEvent = JQuery.ClickEvent;

const { $ } = window;

interface LanguageSelectorBinding {
  onClick?: (event: ClickEvent) => void;
}

function postLanguageChange(element: HTMLElement, event: ClickEvent) {
  const value = $(event.target).attr('value');
  if (value) {
    $(element)
      .find('#language')
      .val(value)
      .parents('form')
      .submit();
  }
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<LanguageSelectorBinding>): void {
    binding.value.onClick = postLanguageChange.bind(null, el);
    $(el).on('click', 'a[value]', binding.value.onClick!);
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<LanguageSelectorBinding>): void {
    $(el).off('click', 'a[value]', binding.value.onClick!);
  },
};
