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
  expandedClass?: string;
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

function expandedClassOf(binding: DirectiveBinding<ExpandOnClickArgs>): string {
  return binding.value?.expandedClass || 'expanded';
}

function expand(
  element: HTMLElement,
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  element.classList.add(expandedClassOf(binding));
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
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  if (!element.classList.contains(expandedClassOf(binding))) {
    return;
  }
  element.classList.remove(expandedClassOf(binding));

  if (binding.value?.onClosed) {
    binding.value.onClosed(event);
  }
}

function onClickOnExpander(
  element: HTMLElement,
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: MouseEvent|KeyboardEvent,
) {
  if (element.classList.contains(expandedClassOf(binding))) {
    close(element, binding, event);
  } else {
    expand(element, binding, event);
  }
}

function onClickOutsideElement(
  element: HTMLElement,
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
    close(element, binding, event);
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
  binding: DirectiveBinding<ExpandOnClickArgs>,
  event: KeyboardEvent,
) {
  if (event.key === 'Escape') {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    close(element, binding, event);
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
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onClickOnExpander = onClickOnExpander.bind(null, el, binding);
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = onMouseDown.bind(null, binding);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, binding);
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
