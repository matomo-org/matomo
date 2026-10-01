/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Content transform for jQuery UI tooltips that show the `title` of the hovered element.
 *
 * Meant to be passed as the widget's `content` option, so it is called with the target element as
 * `this`.
 *
 * jQuery UI inserts what we return with `.html()`, and the browser has already decoded the
 * attribute, so the value is parsed as HTML a second time. It therefore goes through the tooltip
 * sanitizer rather than the general one, which also turns the title's line breaks into `<br />`.
 */
export default function tooltipContent(this: HTMLElement): string {
  return window.vueSanitizeTooltip(this.getAttribute('title') || '');
}
