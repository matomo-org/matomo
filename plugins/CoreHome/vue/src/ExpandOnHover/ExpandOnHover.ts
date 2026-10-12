/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import Matomo from '../Matomo/Matomo';
import DirectiveUtilities from '../directiveUtilities';

export interface ExpandOnHoverArgs {
  // input (provided by user)
  expander: string | HTMLElement,
  // The class carried while open, as for ExpandOnClick.
  expandedClass?: string;

  // event handlers
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  onClickOutsideElement?: (event: MouseEvent) => void;
  onEscapeHandler?: (event: KeyboardEvent) => void;
}

function onMouseEnter(element: HTMLElement, expandedClass: string) {
  element.classList.add(expandedClass);

  const positionElement = element.querySelector('.positionInViewport');
  if (positionElement) {
    Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}

function onMouseLeave(element: HTMLElement, expandedClass: string) {
  element.classList.remove(expandedClass);
}

function onClickOutsideElement(element: HTMLElement, expandedClass: string, event: MouseEvent) {
  if (!element.contains(event.target as HTMLElement)) {
    element.classList.remove(expandedClass);
  }
}

function onEscapeHandler(element: HTMLElement, expandedClass: string, event: KeyboardEvent) {
  if (event.which === 27) {
    element.classList.remove(expandedClass);
  }
}

const doc = document.documentElement;

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnHover: ExpandOnHover(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnHoverArgs>): void {
    const expandedClass = binding.value.expandedClass || 'expanded';
    binding.value.onMouseEnter = onMouseEnter.bind(null, el, expandedClass);
    binding.value.onMouseLeave = onMouseLeave.bind(null, el, expandedClass);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, expandedClass);
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, expandedClass);

    setTimeout(() => {
      const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        expander.addEventListener('mouseenter', binding.value.onMouseEnter!);
      }
    });

    el.addEventListener('mouseleave', binding.value.onMouseLeave);
    doc.addEventListener('keyup', binding.value.onEscapeHandler);
    doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnHoverArgs>): void {
    const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
    if (expander) {
      expander.removeEventListener('mouseenter', binding.value.onMouseEnter!);
    }
    el.removeEventListener('mouseleave', binding.value.onMouseLeave!);
    document.removeEventListener('keyup', binding.value.onEscapeHandler!);
    document.removeEventListener('mouseup', binding.value.onClickOutsideElement!);
  },
};
