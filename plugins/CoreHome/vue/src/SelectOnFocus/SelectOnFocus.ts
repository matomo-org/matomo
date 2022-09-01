/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface SelectOnFocusArgs {
  // state
  focusedElement?: HTMLElement;
  elementSupportsSelect?: boolean;

  // event handlers
  onFocusHandler?: (event: Event) => void;
  onClickHandler?: (event: MouseEvent) => void;
  onBlurHandler?: (event: Event) => void;
}

function onFocusHandler(binding: DirectiveBinding<SelectOnFocusArgs>, event: Event) {
  if (binding.value.focusedElement !== event.target) {
    binding.value.focusedElement = event.target as HTMLElement;
    window.angular.element(event.target!).select();
  }
}

function onClickHandler(event: Event) {
  // .select() + focus and blur seems to not work on pre elements
  const range = document.createRange();
  range.selectNode(event.target as Node);
  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    selection.removeAllRanges();
  }
  if (selection) {
    selection.addRange(range);
  }
}

function onBlurHandler(binding: DirectiveBinding<SelectOnFocusArgs>) {
  delete binding.value.focusedElement;
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<SelectOnFocusArgs>): void {
    const tagName = el.tagName.toLowerCase();
    binding.value.elementSupportsSelect = tagName === 'textarea';

    if (binding.value.elementSupportsSelect) {
      binding.value.onFocusHandler = onFocusHandler.bind(null, binding);
      binding.value.onBlurHandler = onBlurHandler.bind(null, binding);

      el.addEventListener('focus', binding.value.onFocusHandler);
      el.addEventListener('blur', binding.value.onBlurHandler);
    } else {
      binding.value.onClickHandler = onClickHandler;

      el.addEventListener('click', binding.value.onClickHandler);
    }
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<SelectOnFocusArgs>): void {
    if (binding.value.elementSupportsSelect) {
      el.removeEventListener('focus', binding.value.onFocusHandler!);
      el.removeEventListener('blur', binding.value.onBlurHandler!);
    } else {
      el.removeEventListener('click', binding.value.onClickHandler!);
    }
  },
};
