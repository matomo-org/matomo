/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import Matomo from '../Matomo/Matomo';
import DirectiveUtilities from '../directiveUtilities';

export interface ExpandOnClickArgs {
  // input (specified by user)
  expander: string | HTMLElement,
  // The class carried while open. Defaults to the bare `expanded` the older selectors read; a
  // block that owns its own states passes its modifier instead, so its stylesheet can nest it.
  // A list adds every class, the first one holding the state.
  expandedClass?: string | string[];
  onClosed?: (event: MouseEvent|KeyboardEvent) => void;
  onExpand?: (event: MouseEvent|KeyboardEvent) => void;

  // state
  isMouseDown?: boolean;
  hasScrolled?: boolean;

  // internal event handlers
  onClickOnExpander?: (event: MouseEvent|KeyboardEvent) => void;
  onClickOutsideElement?: (event: MouseEvent) => void;
  onScroll?: () => void;
  onMouseDown?: () => void;
  onEscapeHandler?: (event: KeyboardEvent) => void;
}

function isExpanded(element: HTMLElement, expandedClasses: string[]): boolean {
  return element.classList.contains(expandedClasses[0]);
}

function expand(
  element: HTMLElement,
  expandedClasses: string[],
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  element.classList.add(...expandedClasses);
  if (binding.value?.onExpand) {
    binding.value.onExpand(event);
  }

  const positionElement = element.querySelector('.positionInViewport');
  if (positionElement) {
    Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}

function close(
  element: HTMLElement,
  expandedClasses: string[],
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  if (!isExpanded(element, expandedClasses)) {
    return;
  }
  element.classList.remove(...expandedClasses);

  if (binding.value?.onClosed) {
    binding.value.onClosed(event);
  }
}

function onClickOnExpander(
  element: HTMLElement,
  expandedClasses: string[],
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  if (isExpanded(element, expandedClasses)) {
    close(element, expandedClasses, binding, event);
  } else {
    expand(element, expandedClasses, binding, event);
  }
}

function onClickOutsideElement(
  element: HTMLElement,
  expandedClasses: string[],
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent,
) {
  const hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
  binding.value.isMouseDown = false;
  binding.value.hasScrolled = false;

  if (hadUsedScrollbar) {
    return;
  }

  if (!element.contains(event.target as HTMLElement)) {
    close(element, expandedClasses, binding, event);
  }
}

function onScroll(binding: DirectiveBinding<ExpandOnClickArgs>) {
  binding.value.hasScrolled = true;
}

function onMouseDown(binding: DirectiveBinding<ExpandOnClickArgs>) {
  binding.value.isMouseDown = true;
  binding.value.hasScrolled = false;
}

function onEscapeHandler(
  element: HTMLElement,
  expandedClasses: string[],
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: KeyboardEvent,
) {
  if (event.key === 'Escape') {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    close(element, expandedClasses, binding, event);
  }
}

const doc = document.documentElement;

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
    const expandedClasses = DirectiveUtilities.getExpandedClasses(binding.value.expandedClass);
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onClickOnExpander = onClickOnExpander.bind(null, el, expandedClasses, binding);
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, expandedClasses, binding);
    binding.value.onMouseDown = onMouseDown.bind(null, binding);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(
      null,
      el,
      expandedClasses,
      binding,
    );
    binding.value.onScroll = onScroll.bind(null, binding);

    setTimeout(() => {
      const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        expander.addEventListener('click', binding.value.onClickOnExpander!);
      }
    });
    doc.addEventListener('keyup', binding.value.onEscapeHandler);
    doc.addEventListener('mousedown', binding.value.onMouseDown);
    doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
    const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
    if (expander) {
      doc.removeEventListener('click', binding.value.onClickOnExpander!);
    }
    doc.removeEventListener('keyup', binding.value.onEscapeHandler!);
    doc.removeEventListener('mousedown', binding.value.onMouseDown!);
    doc.removeEventListener('mouseup', binding.value.onClickOutsideElement!);
    doc.removeEventListener('scroll', binding.value.onScroll!);
  },
};
