/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, ObjectDirective } from 'vue';

interface FocusAnywhereButHereArgs {
  blur: () => void,
}

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
export default function FocusAnywhereButHere(): ObjectDirective {
  let element: HTMLElement;
  let binding: DirectiveBinding<FocusAnywhereButHereArgs>;
  let isMouseDown = false;
  let hasScrolled = false;

  function onClickOutsideElement(event: MouseEvent) {
    const hadUsedScrollbar = isMouseDown && hasScrolled;
    isMouseDown = false;
    hasScrolled = false;

    if (hadUsedScrollbar) {
      return;
    }

    if (!element.contains(event.target as HTMLElement)) {
      if (binding.value.blur) {
        binding.value.blur();
      }
    }
  }

  function onScroll() {
    hasScrolled = true;
  }

  function onMouseDown() {
    isMouseDown = true;
    hasScrolled = false;
  }

  function onEscapeHandler(event: KeyboardEvent) {
    if (event.which === 27) {
      setTimeout(() => {
        isMouseDown = false;
        hasScrolled = false;
        if (binding.value.blur) {
          binding.value.blur();
        }
      }, 0);
    }
  }

  const doc = document.documentElement;

  return {
    mounted(el: HTMLElement, b: DirectiveBinding<FocusAnywhereButHereArgs>): void {
      element = el;
      binding = b;

      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mousedown', onMouseDown);
      doc.addEventListener('mouseup', onClickOutsideElement);
      doc.addEventListener('scroll', onScroll);
    },
    unmounted(): void {
      doc.removeEventListener('keyup', onEscapeHandler);
      doc.removeEventListener('mousedown', onMouseDown);
      doc.removeEventListener('mouseup', onClickOutsideElement);
      doc.removeEventListener('scroll', onScroll);
    },
  };
}
