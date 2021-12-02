/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import Matomo from '../Matomo/Matomo';

interface ExpandOnClickArgs {
  expander: HTMLElement,

  isMouseDown?: boolean;
  hasScrolled?: boolean;

  onExpand?: () => void;
  onClickOutsideElement?: (event: MouseEvent) => void;
  onScroll?: () => void;
  onMouseDown?: () => void;
  onEscapeHandler?: (event: KeyboardEvent) => void;
}

function onExpand(element: HTMLElement) {
  element.classList.toggle('expanded');

  const positionElement = element.querySelector('.dropdown.positionInViewport');
  if (positionElement) {
    Matomo.helper.setMarginLeftToBeInViewport(positionElement);
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
    element.classList.remove('expanded');
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
  if (event.which === 27) {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    element.classList.remove('expanded');
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
    binding.value.onExpand = onExpand.bind(null, el);
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = onMouseDown.bind(null, binding);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, binding);
    binding.value.onScroll = onScroll.bind(null, binding);

    // have to use jquery here since existing code will do $(...).click(). which apparently
    // doesn't work when using addEventListener.
    window.$(binding.value.expander).click(binding.value.onExpand);
    doc.addEventListener('keyup', binding.value.onEscapeHandler);
    doc.addEventListener('mousedown', binding.value.onMouseDown);
    doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<ExpandOnClickArgs>): void {
    binding.value.expander.removeEventListener('click', binding.value.onExpand);
    doc.removeEventListener('keyup', binding.value.onEscapeHandler);
    doc.removeEventListener('mousedown', binding.value.onMouseDown);
    doc.removeEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.removeEventListener('scroll', binding.value.onScroll);
  },
};
