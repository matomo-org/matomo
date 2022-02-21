/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';

interface FocusAnywhereButHereArgs {
  // input (provided by user)
  blur: () => void,

  // state/event handlers
  isMouseDown?: boolean;
  hasScrolled?: boolean;
  onEscapeHandler?: (event: KeyboardEvent) => void;
  onMouseDown?: () => void;
  onClickOutsideElement?: (event: MouseEvent) => void;
  onScroll?: () => void;
}

function onClickOutsideElement(
  element: HTMLElement,
  binding: DirectiveBinding<FocusAnywhereButHereArgs>,
  event: MouseEvent,
) {
  const hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
  binding.value.isMouseDown = false;
  binding.value.hasScrolled = false;

  if (hadUsedScrollbar) {
    return;
  }

  if (!element.contains(event.target as HTMLElement)) {
    if (binding.value) {
      binding.value.blur();
    }
  }
}

function onScroll(element: HTMLElement, binding: DirectiveBinding<FocusAnywhereButHereArgs>) {
  binding.value.hasScrolled = true;
}

function onMouseDown(element: HTMLElement, binding: DirectiveBinding<FocusAnywhereButHereArgs>) {
  binding.value.isMouseDown = true;
  binding.value.hasScrolled = false;
}

function onEscapeHandler(
  element: HTMLElement,
  binding: DirectiveBinding<FocusAnywhereButHereArgs>,
  event: KeyboardEvent,
) {
  if (event.which === 27) {
    setTimeout(() => {
      binding.value.isMouseDown = false;
      binding.value.hasScrolled = false;
      if (binding.value.blur) {
        binding.value.blur();
      }
    }, 0);
  }
}

const doc = document.documentElement;

/**
 * Usage (in a component):
 *
 * directives: {
 *   // function call is important since we store state in this directive
 *   FocusAnywhereButHere: FocusAnywhereButHere(),
 * }
 *
 * Note: the binding data needs to be static, changes will not be handled.
 */
export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<FocusAnywhereButHereArgs>): void {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = onMouseDown.bind(null, el, binding);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, binding);
    binding.value.onScroll = onScroll.bind(null, el, binding);

    doc.addEventListener('keyup', binding.value.onEscapeHandler);
    doc.addEventListener('mousedown', binding.value.onMouseDown);
    doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<FocusAnywhereButHereArgs>): void {
    doc.removeEventListener('keyup', binding.value.onEscapeHandler!);
    doc.removeEventListener('mousedown', binding.value.onMouseDown!);
    doc.removeEventListener('mouseup', binding.value.onClickOutsideElement!);
    doc.removeEventListener('scroll', binding.value.onScroll!);
  },
};
