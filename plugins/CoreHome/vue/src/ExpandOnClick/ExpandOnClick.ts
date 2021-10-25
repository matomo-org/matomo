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
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
export default function ExpandOnClickFactory(): ObjectDirective {
  let element: HTMLElement;
  let isMouseDown = false;
  let hasScrolled = false;

  function onExpand() {
    element.classList.toggle('expanded');

    const positionElement = element.querySelector('.dropdown.positionInViewport');
    if (positionElement) {
      Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }

  function onClickOutsideElement(event: MouseEvent) {
    const hadUsedScrollbar = isMouseDown && hasScrolled;
    isMouseDown = false;
    hasScrolled = false;

    if (hadUsedScrollbar) {
      return;
    }

    if (!element.contains(event.target as HTMLElement)) {
      element.classList.remove('expanded');
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
      isMouseDown = false;
      hasScrolled = false;
      element.classList.remove('expanded');
    }
  }

  const doc = document.documentElement;

  return {
    mounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
      element = el;

      binding.value.expander.addEventListener('click', onExpand);
      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mousedown', onMouseDown);
      doc.addEventListener('mouseup', onClickOutsideElement);
      doc.addEventListener('scroll', onScroll);
    },
    unmounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
      binding.value.expander.removeEventListener('click', onExpand);
      doc.removeEventListener('keyup', onEscapeHandler);
      doc.removeEventListener('mousedown', onMouseDown);
      doc.removeEventListener('mouseup', onClickOutsideElement);
      doc.removeEventListener('scroll', onScroll);
    },
  };
}
