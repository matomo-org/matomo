/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Closes the jQuery UI tooltips bound to the elements matching the selector,
 * including tooltips whose delayed show has not fired yet, by triggering the
 * close handlers the tooltip widget binds on its targets.
 *
 * Use this when no mouse event will fire that would close a tooltip naturally,
 * eg. once an HTML5 drag has started, which suppresses mouse events entirely.
 */
export default function closeTooltips(selector: string): void {
  window.$(selector).trigger('mouseleave').trigger('focusout');
}
