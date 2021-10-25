/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, ObjectDirective } from 'vue';
import Matomo from '../Matomo/Matomo';

interface ExpandOnClickArgs {
  expander: HTMLElement,
}

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnHover: ExpandOnHover(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
export default function ExpandOnHoverFactory(): ObjectDirective {
  let element: HTMLElement;

  function onMouseEnter() {
    element.classList.add('expanded');

    const positionElement = element.querySelector('.dropdown.positionInViewport');
    if (positionElement) {
      Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }

  function onMouseLeave() {
    element.classList.remove('expanded');
  }

  function onClickOutsideElement(event: MouseEvent) {
    if (!element.contains(event.target as HTMLElement)) {
      element.classList.remove('expanded');
    }
  }

  function onEscapeHandler(event: KeyboardEvent) {
    if (event.which === 27) {
      element.classList.remove('expanded');
    }
  }

  const doc = document.documentElement;

  return {
    mounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
      element = el;

      binding.value.expander.addEventListener('mouseenter', onMouseEnter);
      element.addEventListener('mouseleave', onMouseLeave);
      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mouseup', onClickOutsideElement);
    },
    unmounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
      binding.value.expander.removeEventListener('mouseenter', onMouseEnter);
      element.removeEventListener('mouseleave', onMouseLeave);
      document.removeEventListener('keyup', onEscapeHandler);
      document.removeEventListener('mouseup', onClickOutsideElement);
    },
  };
}
