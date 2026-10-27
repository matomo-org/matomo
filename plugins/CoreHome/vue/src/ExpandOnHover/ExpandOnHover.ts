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
  // The class, or classes, carried while open, as for ExpandOnClick.
  expandedClass?: string | string[];

  // event handlers
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
  onClickOutsideElement?: (event: MouseEvent) => void;
  onEscapeHandler?: (event: KeyboardEvent) => void;
}

function onMouseEnter(element: HTMLElement, expandedClasses: string[]) {
  element.classList.add(...expandedClasses);

  const positionElement = element.querySelector('.positionInViewport');
  if (positionElement) {
    Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}

function onMouseLeave(element: HTMLElement, expandedClasses: string[]) {
  element.classList.remove(...expandedClasses);
}

function onClickOutsideElement(element: HTMLElement, expandedClasses: string[], event: MouseEvent) {
  if (!element.contains(event.target as HTMLElement)) {
    element.classList.remove(...expandedClasses);
  }
}

function onEscapeHandler(element: HTMLElement, expandedClasses: string[], event: KeyboardEvent) {
  if (event.which === 27) {
    element.classList.remove(...expandedClasses);
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
    const expandedClasses = Array.isArray(expandedClass) ? expandedClass : [expandedClass];
    binding.value.onMouseEnter = onMouseEnter.bind(null, el, expandedClasses);
    binding.value.onMouseLeave = onMouseLeave.bind(null, el, expandedClasses);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, expandedClasses);
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, expandedClasses);

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
